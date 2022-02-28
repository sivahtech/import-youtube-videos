<?php

namespace YoutubeImporterImportyoutube\Helper;

use YoutubeImporterImportyoutube\Helper\Importer\FeedItem as YIS_Helper_Importer_FeedItem;

class Importer {

  /**
   * @param array $meta_map
   * @return Importer
   */
  public static function from_meta_map( array $meta_map ): Importer {
    $settings = [];

    if( isset( $meta_map[ 'importyoutube_import_post_type' ] ) )
      $settings[ 'post_type' ] = $meta_map[ 'importyoutube_import_post_type' ];

    if( isset( $meta_map[ 'importyoutube_import_publish' ] ) )
      $settings[ 'post_status' ] = $meta_map[ 'importyoutube_import_publish' ];

    if( isset( $meta_map[ 'importyoutube_import_author' ] ) )
      $settings[ 'post_author' ] = $meta_map[ 'importyoutube_import_author' ];

    if( isset( $meta_map[ 'importyoutube_import_category' ] ) && is_array( $meta_map[ 'importyoutube_import_category' ] ) )
      $settings[ 'post_categories' ] = $meta_map[ 'importyoutube_import_category' ];

    if( isset( $meta_map[ 'importyoutube_import_images' ] ) )
      $settings[ 'import_images' ] = self::_meta_setting_to_bool( $meta_map[ 'importyoutube_import_images' ] );

    if( isset( $meta_map[ 'importyoutube_truncate_post' ] ) )
      $settings[ 'import_content_truncate' ] = ( $meta_map[ 'importyoutube_truncate_post' ] === '' ? false : intval( $meta_map[ 'importyoutube_truncate_post' ] ) );

    if( isset( $meta_map[ 'importyoutube_prepend_title' ] ) )
      $settings[ 'import_prepend_title' ] = $meta_map[ 'importyoutube_prepend_title' ];

    if( isset( $meta_map[ 'importyoutube_import_allow_sync' ] ) )
      $settings[ 'import_allow_sync' ] = self::_meta_setting_to_bool( $meta_map[ 'importyoutube_import_allow_sync' ] );

    if( isset( $meta_map[ 'importyoutube_import_date_from' ] ) )
      $settings[ 'import_date_from' ] = ( $meta_map[ 'importyoutube_import_date_from' ] !== '' ? $meta_map[ 'importyoutube_import_date_from' ] : false );

    if( isset( $meta_map[ 'importyoutube_parent_show' ] ) )
      $settings[ 'import_parent_show' ] = $meta_map[ 'importyoutube_parent_show' ];
	//if( isset( $meta_map[ 'importyoutube_channel_removed_id' ] ) )
     // $settings[ 'channel_removed_id' ] = $meta_map[ 'importyoutube_channel_removed_id' ];
    if( !isset( $meta_map[ 'importyoutube_import_allow_sync' ] )
        || ( isset( $meta_map[ 'importyoutube_import_allow_sync' ] ) && $meta_map[ 'importyoutube_import_allow_sync' ] ) )
      if( isset( $meta_map[ 'importyoutube_latest_timestamp' ] ) && !empty( $meta_map[ 'importyoutube_latest_timestamp' ] ) )
        $settings[ 'import_date_from' ] = intval( $meta_map[ 'importyoutube_latest_timestamp' ] );

    $settings = apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_importer_settings_from_meta_map', $settings, $meta_map );

    return new self( $meta_map[ 'importyoutube_youtube_channel_id' ], $settings );
  }

  public static function _meta_setting_to_bool( $val ): bool {
    return ( $val === 'off' ? false : ( $val === 'on' ? true : boolval( $val ) ) );
  }

  public $channel_items = [];
  public $channel_id = '';

  public $post_type       = 'videos';
  public $post_status     = 'publish';
  public $post_author     = 'admin';
  public $post_categories = [];

  public $import_allow_sync = false;
  public $import_images = false;
  public $import_content_truncate = false;
  public $import_prepend_title    = '';
  public $import_parent_show      = '';
  public $import_date_from = false;

  public $additional_settings = [];

  private $_current_feed_post_count = 0;
  private $_current_video_id_to_post_id_map = [];
  private $_current_imported_count = 0;
  private $_post_categories_import_map = [];

  public function __construct( $channel_id, $settings = array() ) {
    $this->channel_id = $channel_id;

    foreach( $settings as $k => $v ) {
      if( !isset( $this->$k ) ) {
        $this->additional_settings[ $k ] = $v;
        continue;
      }

      $this->$k = $v;
    }

    if( $this->import_date_from !== false )
      $this->import_date_from = !is_numeric( $this->import_date_from ) ? strtotime( $this->import_date_from ) : intval( $this->import_date_from );

    if( !function_exists( 'post_exists' ) )
      require_once(ABSPATH . 'wp-admin/includes/post.php' );
  }
  public function check_current_feed()
  {
	do_action( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_importer_before_import_current_feed', $this );

    set_time_limit(360);
	if( empty( $this->channel_items ) ) {
      $this->channel_items = Youtube::get_items( $this->channel_id, 99999, '', $this->import_date_from );

      $this->_current_feed_post_count = count( $this->channel_items );

      if( empty( $this->channel_items ) )
        return false;
    }

    if( $this->_current_feed_post_count === 0 )
      return false;
  
  return $this->channel_items;
  
  }
  public function import_current_feed() {
    do_action( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_importer_before_import_current_feed', $this );

    set_time_limit(360);

    if( empty( $this->channel_items ) ) {
      $this->channel_items = Youtube::get_items( $this->channel_id, 99999, '', $this->import_date_from );

      $this->_current_feed_post_count = count( $this->channel_items );

      if( empty( $this->channel_items ) )
        return false;
    }

    if( $this->_current_feed_post_count === 0 )
      return false;

    if( empty( $this->_post_categories_import_map ) ) {
      foreach( $this->post_categories as $post_category ) {
        $term = get_term( $post_category );

        if( !isset( $term->taxonomy ) )
          continue;

        if( !isset( $this->_post_categories_import_map[ $term->taxonomy ] ) )
          $this->_post_categories_import_map[ $term->taxonomy ] = [];

        $this->_post_categories_import_map[ $term->taxonomy ][] = $post_category;
      }
    }

    $this->_current_imported_count = 0;

    $synced_count  = 0;
    $skipped_count = 0;
    $skipped_missing_id_count = 0;
    $additional_errors = [];
	
	$remvedarray= explode(',', $_REQUEST['channel_removed_id']);

	if($_REQUEST['channel_removed_id'] !=''){
    foreach ( $this->channel_items as $index => $channel_item ) {
	if (in_array(trim($channel_item[ 'id' ]), $remvedarray)){	
	
      if( $this->import_date_from !== false ) {
        if( strtotime( $channel_item[ 'snippet' ][ 'publishedAt' ] ) < $this->import_date_from ) {
          $skipped_count++;
          continue;
        }
      }

      if( !isset( $channel_item[ 'id' ] ) ) {
        $additional_errors[] = sprintf( __( "Missing 'id' param for an entry, at index %s", 'import-youtube-videos' ), $index );

        $skipped_missing_id_count++;
        continue;
      }

      $feedItemInstance = new YIS_Helper_Importer_FeedItem( $this, $channel_item );

      if( $feedItemInstance->current_post_id !== 0 ) {
        if( $this->import_allow_sync ) {
          $sync_response = $feedItemInstance->sync();

          if( is_wp_error( $sync_response ) ) {
            $additional_errors[] = $sync_response->get_error_message();

            unset( $feedItemInstance );

            continue;
          }

          $synced_count++;
        }

        $this->_current_video_id_to_post_id_map[ $feedItemInstance->youtube_video_id ] = $feedItemInstance->current_post_id;

        unset( $feedItemInstance );

        continue;
      }
	  	
      $import_response = $feedItemInstance->import();

      if( is_wp_error( $import_response ) ) {
        $additional_errors[] = $import_response->get_error_message();

        unset( $feedItemInstance );

        continue;
      }

      $this->_current_video_id_to_post_id_map[ $feedItemInstance->youtube_video_id ] = $feedItemInstance->current_post_id;
      $this->_current_imported_count++;

      unset( $feedItemInstance );
	}
    }
	}else{
		
		foreach ( $this->channel_items as $index => $channel_item ) {
		
	
      if( $this->import_date_from !== false ) {
        if( strtotime( $channel_item[ 'snippet' ][ 'publishedAt' ] ) < $this->import_date_from ) {
          $skipped_count++;
          continue;
        }
      }

      if( !isset( $channel_item[ 'id' ] ) ) {
        $additional_errors[] = sprintf( __( "Missing 'id' param for an entry, at index %s", 'import-youtube-videos' ), $index );

        $skipped_missing_id_count++;
        continue;
      }

      $feedItemInstance = new YIS_Helper_Importer_FeedItem( $this, $channel_item );

      if( $feedItemInstance->current_post_id !== 0 ) {
        if( $this->import_allow_sync ) {
          $sync_response = $feedItemInstance->sync();

          if( is_wp_error( $sync_response ) ) {
            $additional_errors[] = $sync_response->get_error_message();

            unset( $feedItemInstance );

            continue;
          }

          $synced_count++;
        }

        $this->_current_video_id_to_post_id_map[ $feedItemInstance->youtube_video_id ] = $feedItemInstance->current_post_id;

        unset( $feedItemInstance );

        continue;
      }
	  	
      $import_response = $feedItemInstance->import();

      if( is_wp_error( $import_response ) ) {
        $additional_errors[] = $import_response->get_error_message();

        unset( $feedItemInstance );

        continue;
      }

      $this->_current_video_id_to_post_id_map[ $feedItemInstance->youtube_video_id ] = $feedItemInstance->current_post_id;
      $this->_current_imported_count++;

      unset( $feedItemInstance );
	
    }
		
	}

    do_action( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_importer_after_import_current_feed', $this );

    return [
      'post_count'     => $this->_current_feed_post_count - $skipped_missing_id_count,
      'skipped_count'     => $skipped_count,
      'synced_count'      => $synced_count,
      'imported_count'    => count( $this->_current_video_id_to_post_id_map ),
      'current_import'    => $this->_current_imported_count,
      'additional_errors' => $additional_errors,
      'latest_timestamp'  => ( !empty( $this->channel_items ) ? strtotime( $this->channel_items[ imyp_array_key_first( $this->channel_items ) ][ 'snippet' ][ 'publishedAt' ] ) : false )
    ];
  }

  public function get_post_categories_import_map() :array {
    return $this->_post_categories_import_map;
  }


}
