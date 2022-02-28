<?php

namespace YoutubeImporterImportyoutube\RestAPI;

use WP_Error;
use YoutubeImporterImportyoutube\Settings as YIS_Settings;
use YoutubeImporterImportyoutube\Helper\FeedForm as YIS_Helper_FeedForm;
use YoutubeImporterImportyoutube\Helper\Importer as YIS_Helper_Importer;
use YoutubeImporterImportyoutube\Helper\Youtube as YIS_Helper_Youtube;

class Response {

  public static function admin_dismiss_notice( $request_data ) {
    $current_notice_dismiss_map = YIS_Settings::instance()->get( '_admin_notice_dismissed_map' );

    $current_notice_dismiss_map[ get_current_user_id() ] = time();

    YIS_Settings::instance()->update( '_admin_notice_dismissed_map', $current_notice_dismiss_map );

    return rest_ensure_response( true );
  }
  public static function check_feed( $request ) {
    $request_data = $request->get_params();
    $messages = [];

    $meta_map = YIS_Helper_FeedForm::request_data_to_meta_map( $request_data );

    $importer = YIS_Helper_Importer::from_meta_map( $meta_map );

    $check_current_feed = $importer->check_current_feed();
	
	$allimages='';
	if(!empty($check_current_feed)){
		foreach($check_current_feed as $index => $channel_item){
			if( strtotime( $channel_item[ 'snippet' ][ 'publishedAt' ] ) > strtotime($_REQUEST['import_date_from']) ) {	
				$allimages.='<div class="youtubevideo" id="remove'.$channel_item['id'].'">
				<div class="youtubevideimage"><img src="'.$channel_item['snippet']['thumbnails']['high']['url'].'"/></div>
				<div class="youtubevideimagetitle"><h2>'.$channel_item['snippet']['title'].'</h2></div>
				<input type="checkbox" class="removefromexport" data-id='.$channel_item['id'].'>
				</div>';
			}
		}
	}

    if( $check_current_feed === false ) {
      return rest_ensure_response( [
        'messages'  => [
          [
            'type'    => 'danger',
            'message' => __( "Invalid Channel ID or the API quota has been reached.", 'import-youtube-videos' )
          ]
        ]
      ] );
    }

    if( empty($check_current_feed) ) {
        $messages[] = [
          'type'    => 'empty',
          'message' => __('Success! Re-synced ', 'import-youtube-videos' ) . $import_current_feed[ 'synced_count' ] . __( " previously imported posts.", 'import-youtube-videos' )
        ];
      } else {
        $messages[] = [
          'type'    => 'Success',
          'message' => $allimages,
        ];
      }
    

    
    return rest_ensure_response( [
      'messages'  => $messages
    ] );
  }		
  public static function import_feed( $request ) {
    $request_data = $request->get_params();
    $messages = [];

    $meta_map = YIS_Helper_FeedForm::request_data_to_meta_map( $request_data );

    $importer = YIS_Helper_Importer::from_meta_map( $meta_map );

    $import_current_feed = $importer->import_current_feed();

    if( $import_current_feed === false ) {
      return rest_ensure_response( [
        'messages'  => [
          [
            'type'    => 'danger',
            'message' => __( "Invalid Channel ID or the API quota has been reached.", 'import-youtube-videos' )
          ]
        ]
      ] );
    }

    if( $import_current_feed[ 'current_import' ] == 0 && $import_current_feed[ 'post_count' ] != 0) {
      if( $import_current_feed[ 'synced_count' ] !== 0 ) {
        $messages[] = [
          'type'    => 'success',
          'message' => __('Success! Re-synced ', 'import-youtube-videos' ) . $import_current_feed[ 'synced_count' ] . __( " previously imported posts.", 'import-youtube-videos' )
        ];
      } else {
        $messages[] = [
          'type'    => 'danger',
          'message' => __( 'No new posts to import - all posts already exist in WordPress!', 'import-youtube-videos' ) . '<br/>' .
            __('If you have existing draft, private or trashed posts with the same title as your posts, delete those and run the importer again.', 'import-youtube-videos')
        ];
      }
    } elseif ( $import_current_feed[ 'post_count' ] == 0) { // No posts existing within feed.
      $messages[] = [
        'type'    => 'danger',
        'message' => __( 'Error! Your feed does not contain any items.', 'import-youtube-videos' )
      ];
    } else {
      $messages[] = [
        'type'    => 'success',
        'message' => __('Success! Imported ', 'import-youtube-videos') . $import_current_feed[ 'current_import' ] .
                     __(' out of ', 'import-youtube-videos') . $import_current_feed[ 'post_count' ] . __(' posts', 'import-youtube-videos' ) . '.' .
                    ( $import_current_feed[ 'synced_count' ] !== 0 ? ' ' . $import_current_feed[ 'synced_count' ] . __( " previously imported posts re-synced", 'import-youtube-videos' ) : '' )
      ];
    }

    if( isset( $import_current_feed[ 'additional_errors' ] ) && is_array( $import_current_feed[ 'additional_errors' ] ) )
      foreach( $import_current_feed[ 'additional_errors' ] as $additional_error )
        $messages[] = [
          'type'    => 'danger',
          'message' => $additional_error
        ];

    if( isset( $request_data[ 'post_id' ] ) ) {
      foreach( $meta_map as $k => $v )
        update_post_meta( intval( $request_data[ 'post_id' ] ), $k, $v );

      if( isset( $import_current_feed[ 'latest_timestamp' ] ) && !empty( $import_current_feed[ 'latest_timestamp' ] ) )
        update_post_meta( intval( $request_data[ 'post_id' ] ), "importyoutube_latest_timestamp", $import_current_feed[ 'latest_timestamp' ] );
    } else if( isset( $meta_map[ 'importyoutube_import_continuous' ] ) && $meta_map[ 'importyoutube_import_continuous' ] == 'on' ) {
      $channel_title = YIS_Helper_Youtube::get_channel_title( $importer->channel_id );

      if( 0 === post_exists( $channel_title, "", "", YOUTUBE_IMPORTER_IMPORTYOUTUBE_POST_TYPE_IMPORT )) {
        $import_post = [
          'post_title'   => $channel_title,
          'post_type'    => YOUTUBE_IMPORTER_IMPORTYOUTUBE_POST_TYPE_IMPORT,
          'post_status'  => 'publish',
        ];
        $post_import_id = wp_insert_post( $import_post );

        foreach( $meta_map as $k => $v )
          update_post_meta( $post_import_id, $k, $v );

        if( isset( $import_current_feed[ 'latest_timestamp' ] ) && !empty( $import_current_feed[ 'latest_timestamp' ] ) )
          update_post_meta( $post_import_id, "importyoutube_latest_timestamp", $import_current_feed[ 'latest_timestamp' ] );

      } else {
        $messages[] = [
          'type'    => 'danger',
          'message' => __('This youtube is already being scheduled for import. Delete the previous schedule to create a new one.', 'import-youtube-videos' )
        ];
      }
    }

    return rest_ensure_response( [
      'messages'  => $messages
    ] );
  }

  public static function sync_feed( $request ) {
    $all_meta = get_post_meta( intval( $request[ 'id' ] ) );
    $meta_map = [];

    foreach( $all_meta as $k => $v ) {
      if( is_array( $v ) && count( $v ) === 1 )
        $v = maybe_unserialize( $v[ 0 ] );

      $meta_map[ $k ] = $v;
    }

    // Maybe deleted after queued, need to ensure it's fine.
    if( isset( $meta_map[ 'importyoutube_youtube_channel_id' ] ) ) {
      $importer = YIS_Helper_Importer::from_meta_map( $meta_map );
      $response = $importer->import_current_feed();

      if( isset( $response[ 'latest_timestamp' ] ) && !empty( $response[ 'latest_timestamp' ] ) )
        update_post_meta( intval( $request[ 'id' ] ), "importyoutube_latest_timestamp", $response[ 'latest_timestamp' ] );
    }

    return rest_ensure_response( true );
  }

}