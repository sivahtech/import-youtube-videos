<?php

namespace YoutubeImporterImportyoutube\Helper\Importer;

use YoutubeImporterImportyoutube\Helper\Importer as YIS_Helper_Importer;

class FeedItem {

  /**
   * @var YIS_Helper_Importer
   */
  public $importer;
  public $feed_item;

  public $current_post_id = 0;
  public $current_post_information = [
    'post_author'  => '',
    'post_content' => '',
    'post_date'    => '',
    'post_excerpt' => '',
    'post_status'  => '',
    'post_type'    => '',
    'post_title'   => '',
  ];

  public $youtube_video_id;

  public $player_url        = '';
  public $player_embed_html = '';

  /**
   * @param YIS_Helper_Importer $importer
   * @param $feed_item
   */
  public function __construct( YIS_Helper_Importer $importer, $feed_item ) {
    $this->importer = $importer;
    $this->feed_item = $feed_item;
    $this->youtube_video_id = $this->feed_item[ 'id' ];

    global $wpdb;

    $this->current_post_id = intval($wpdb->get_var('SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE ( meta_key = "importyoutube_imported_youtube_video_id" AND meta_value = ' . $wpdb->prepare( "%s", $this->youtube_video_id ) . ')' ) );

    $this->current_post_information[ 'post_author' ] = $this->importer->post_author;
    $this->current_post_information[ 'post_date' ]   = date( 'Y-m-d H:i:s', ( strtotime( $this->feed_item[ 'snippet' ][ 'publishedAt' ] ) < current_time('timestamp') ? strtotime( $this->feed_item[ 'snippet' ][ 'publishedAt' ] ) : current_time('timestamp') ) );
    $this->current_post_information[ 'post_type' ]   = $this->importer->post_type;
    $this->current_post_information[ 'post_status' ] = $this->importer->post_status;
    $this->current_post_information[ 'post_title' ]  = sanitize_text_field($this->feed_item[ 'snippet' ][ 'title' ] );

    $this->player_url = 'https://www.youtube.com/watch?v=' . $this->youtube_video_id;
    $this->player_embed_html = '';

    if( isset( $GLOBALS['wp_embed'] ) ) {
      $usecache_status = $GLOBALS['wp_embed']->usecache;

      $GLOBALS['wp_embed']->usecache = false;

      $this->player_embed_html = $GLOBALS['wp_embed']->autoembed( $this->player_url );

      $GLOBALS['wp_embed']->usecache = $usecache_status;
    }

    if( $this->importer->import_prepend_title !== '' )
      $this->current_post_information[ 'post_title' ] = $this->importer->import_prepend_title . ' ' . $this->current_post_information[ 'post_title' ];

    if( $this->current_post_id === 0 )
      $this->current_post_id = \post_exists( $this->current_post_information[ 'post_title' ], "", "", $this->importer->post_type );

    $this->current_post_information[ 'post_excerpt' ] = wp_trim_excerpt(sanitize_textarea_field( $this->feed_item[ 'snippet' ][ 'description' ] ) );

    $this->current_post_information[ 'post_content' ] = '';

    if( $this->importer->import_content_truncate === false || $this->importer->import_content_truncate >= 1 ) {
      $this->current_post_information[ 'post_content' ] = sanitize_textarea_field( $this->feed_item[ 'snippet' ][ 'description' ] );

      if ( $this->importer->import_content_truncate !== false )
        $this->current_post_information[ 'post_content' ] = substr($this->current_post_information[ 'post_content' ], 0, $this->importer->import_content_truncate );
    }

    if( apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_feed_item_has_player_in_content', true, $this ) )
      if ( !youtube_importer_importyoutube_has_premium_theme() ) {
        $this->current_post_information[ 'post_content' ] = $this->player_embed_html . '<br>' . $this->current_post_information[ 'post_content' ];
      }
  }

  public function import() {
    $this->current_post_id = wp_insert_post( $this->current_post_information );

    if ( is_wp_error( $this->current_post_id ) )
      return $this->current_post_id;

    $this->_set_post_information();

    return true;
  }

  public function sync() {
    if( $this->current_post_id === 0 )
      return false;

    $response = wp_update_post( [
      'ID' => $this->current_post_id
    ] + $this->current_post_information, true );

    if( is_wp_error( $response ) )
      return $response;

    $this->_set_post_information();

    return true;
  }

  private function _set_post_information() {
    update_post_meta( $this->current_post_id, 'importyoutube_imported_youtube_video_id', $this->youtube_video_id );
    if ( youtube_importer_importyoutube_has_premium_theme() ) {
      update_post_meta( $this->current_post_id, 'importyoutube_themes_external_embed', $this->player_embed_html );
    }

    $categories_map = apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_feed_item_import_category_map', $this->importer->get_post_categories_import_map(), $this );

    foreach( $categories_map as $taxonomy => $taxonomy_tags )
      wp_set_post_terms( $this->current_post_id, $taxonomy_tags, $taxonomy, false );

    if( !empty( $this->importer->import_parent_show ) )
      update_post_meta( $this->current_post_id, 'importyoutube_themes_parent_show_post', $this->importer->import_parent_show );

    if( apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_feed_item_import_images', ( isset( $this->importer->import_images ) && $this->importer->import_images ), $this ) )
      $this->_handle_image_import();

    do_action( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_feed_item_imported', $this );
  }

  private function _handle_image_import() {
    if( !isset( $this->feed_item[ 'snippet'][ 'thumbnails' ] ) )
      return;

    $image_path = false;

    if( isset( $this->feed_item[ 'snippet'][ 'thumbnails' ][ 'maxres' ] ) )
      $image_path = $this->feed_item[ 'snippet'][ 'thumbnails' ][ 'maxres' ][ 'url' ];
    else if( isset( $this->feed_item[ 'snippet'][ 'thumbnails' ][ 'high' ] ) )
      $image_path = $this->feed_item[ 'snippet'][ 'thumbnails' ][ 'high' ][ 'url' ];
    else if( isset( $this->feed_item[ 'snippet'][ 'thumbnails' ][ 'medium' ] ) )
      $image_path = $this->feed_item[ 'snippet'][ 'thumbnails' ][ 'medium' ][ 'url' ];
    else if( isset( $this->feed_item[ 'snippet'][ 'thumbnails' ][ 'default' ] ) )
      $image_path = $this->feed_item[ 'snippet'][ 'thumbnails' ][ 'default' ][ 'url' ];

    if( empty( $image_path ) )
      return;

    as_enqueue_async_action( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_scheduler_image_sync', [ $this->current_post_id, $image_path ], YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS );
  }

}