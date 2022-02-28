<?php

namespace YoutubeImporterImportyoutube\Helper;

use YoutubeImporterImportyoutube\Settings as YIS_Settings;

class Youtube {

  public static function is_valid_api_key( $api_key ) {
    return !empty( self::get_items( YOUTUBE_IMPORTER_IMPORTYOUTUBE_EXAMPLE_CHANNEL_ID, 10, $api_key ) );
  }

  public static function get_channel_title( $channel_id, $api_key = null ) {
    if( empty( $api_key ) )
      $api_key = YIS_Settings::instance()->get( 'youtube_api_key' );

    $request_url = add_query_arg(  [
      'key'        => $api_key,
      'part'       => urlencode( 'snippet' ),
      'id'         => $channel_id
    ], 'https://youtube.googleapis.com/youtube/v3/channels' );

    $response = wp_remote_get( $request_url );

    if( is_wp_error( $response ) )
      return sprintf( __( "%s - Cannot find channel name", 'import-youtube-videos' ), $channel_id );

    $response = wp_remote_retrieve_body( $response );
    $response = json_decode( $response, true );

    if( !isset( $response[ 'items' ] ) )
      return sprintf( __( "%s - Cannot find channel name", 'import-youtube-videos' ), $channel_id );

    return $response[ 'items' ][ 0 ][ 'snippet' ][ 'title' ];
  }

  /**
   * Youtube API Limits to a maximum of 500 results for a channel, and 50 per request.
   * Will leverage the query to filter a date before to get even more if the number is higher.
   *
   * @param $channel_id
   * @param int $total_max_results
   * @param null $api_key
   * @param false|int $max_timestamp_from
   * @return array
   */
  public static function get_items( $channel_id, $total_max_results = 500, $api_key = null, $max_timestamp_from = false ) {
    if( empty( $api_key ) )
      $api_key = YIS_Settings::instance()->get( 'youtube_api_key' );

    $request_args = [
      'key'        => $api_key,
      'part'       => urlencode( 'snippet,id' ),
      'maxResults' => 50,
      'channelId'  => $channel_id,
      'order'      => 'date'
    ];
    $items = [];

    while( count( $items ) < $total_max_results ) {
      $current_items = self::_search_items($request_args,( $total_max_results > 500 ? 500 : $total_max_results ), $max_timestamp_from );

      if( count( $current_items ) < 500 ) {
        $items = array_merge( $items, $current_items );
        break;
      }

      $request_args[ 'publishedBefore' ] = $current_items[ imyp_array_key_last( $current_items ) ][ 'snippet' ][ 'publishedAt' ];
      unset( $current_items[ imyp_array_key_last( $current_items ) ] );
      $items = array_merge( $items, $current_items );

      // Stop doing queries if there's limitations on date.
      if( $max_timestamp_from !== false && strtotime( $request_args[ 'publishedBefore' ] ) < $max_timestamp_from )
        break;
    }

    $video_ids = [];

    foreach( $items as $item ) {
      if( !isset( $item[ 'id' ][ 'videoId' ] ) )
        continue;

      $video_ids[] = $item[ 'id' ][ 'videoId' ];
    }

    $items_with_all_information = [];

    while( !empty( $video_ids ) ) {
      $request_args = [
        'key'   => $api_key,
        'part'  => urlencode( 'snippet,id' ),
        'id'    => implode( ",", array_splice( $video_ids, 0, 45 ) )
      ];

      $response = wp_remote_get( add_query_arg( $request_args, 'https://youtube.googleapis.com/youtube/v3/videos' ) );

      if( is_wp_error( $response ) )
        continue;

      $response = wp_remote_retrieve_body( $response );
      $response = json_decode( $response, true );

      if( !isset( $response[ 'items' ] ) )
        continue;

      $items_with_all_information = array_merge( $items_with_all_information, $response[ 'items' ] );
    }

    return $items_with_all_information;
  }

  private static function _search_items( $request_args, $total_max_results, $max_timestamp_from = false ) {
    $base_url = add_query_arg( $request_args, 'https://youtube.googleapis.com/youtube/v3/search' );
    $nextPageToken = null;

    $query_limit = 10;
    $items = [];

    while( $nextPageToken !== false && $query_limit > 0 ) {
      $query_limit--;

      $url = $base_url;

      if( $nextPageToken !== null )
        $url .= '&pageToken=' . $nextPageToken;

      $response = wp_remote_get( $url );

      if( is_wp_error( $response ) ) {
        $nextPageToken = false;
        continue;
      }

      $response = wp_remote_retrieve_body( $response );
      $response = json_decode( $response, true );

      if( !isset( $response[ 'items' ] ) )  {
        $nextPageToken = false;
        continue;
      }

      $items = array_merge( $items, $response[ 'items' ] );

      if( $max_timestamp_from !== false && strtotime( $response[ 'items' ][ imyp_array_key_last( $response[ 'items' ] ) ][ 'snippet' ][ 'publishedAt' ] ) < $max_timestamp_from ) {
        $nextPageToken = false;
        continue;
      }

      if( isset( $response[ 'nextPageToken' ] ) )
        $nextPageToken = $response[ 'nextPageToken' ];
      else
        $nextPageToken = false;

      if( count( $items ) >= $total_max_results )
        break;
    }

    return $items;
  }

}