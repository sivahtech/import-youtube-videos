<?php

namespace YoutubeImporterImportyoutube;

class AdminAssets {

  /**
   * @var AdminAssets;
   */
  protected static $_instance;

  /**
   * @return AdminAssets
   */
  public static function instance(): AdminAssets {
    if( self::$_instance === null )
      self::$_instance = new self();

    return self::$_instance;
  }

  public function setup( $hook ) {
    if( $hook === 'tools_page_' . YOUTUBE_IMPORTER_IMPORTYOUTUBE_PREFIX ) {
      wp_enqueue_media();
      wp_enqueue_script( 'media-grid' );
      wp_enqueue_script( 'media' );
      wp_enqueue_style( 'media');
      wp_enqueue_script( 'media-upload' );
      wp_enqueue_script( 'thickbox' );
    }

    wp_register_style( 'youtube_importer_admin_styles', plugins_url( '/assets/css/admin.css', YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_FILE_PATH ), false, YOUTUBE_IMPORTER_IMPORTYOUTUBE_VERSION );
    wp_enqueue_style( 'youtube_importer_admin_styles' );

    wp_register_script('youtube_importer_admin_scripts', plugins_url('/assets/js/admin.js', YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_FILE_PATH), false, YOUTUBE_IMPORTER_IMPORTYOUTUBE_VERSION, true);

    wp_localize_script( 'youtube_importer_admin_scripts', 'youtube_import_settings', [
      'rest_url'    => esc_url_raw( rest_url() ),
      'rest_nonce'  => wp_create_nonce( 'wp_rest' ),
      'loader_icon' => plugins_url('/assets/loader-icon.png', YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_FILE_PATH )
    ] );

    wp_enqueue_script('youtube_importer_admin_scripts');
  }

}