<?php

namespace YoutubeImporterImportyoutube;

use YoutubeImporterImportyoutube\Helper\Embed as YIS_Helper_Embed;
use YoutubeImporterImportyoutube\Template as YIS_Template;

class AdminMenu {

  /**
   * @var AdminMenu;
   */
  protected static $_instance;

  /**
   * @return AdminMenu
   */
  public static function instance(): AdminMenu {
    if( self::$_instance === null )
      self::$_instance = new self();

    return self::$_instance;
  }

  public function setup() {
    add_management_page(
      YOUTUBE_IMPORTYOUTUBE_SECONDLINE_NAME_SHORT,
      YOUTUBE_IMPORTYOUTUBE_SECONDLINE_NAME_SHORT,
      YOUTUBE_IMPORTER_IMPORTYOUTUBE_FEED_PERMISSION_CAP,
      YOUTUBE_IMPORTER_IMPORTYOUTUBE_PREFIX,
      [ $this, '_display_management_page' ]
    );

    add_filter( "plugin_action_links_" . plugin_basename( YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_FILE_PATH ), [ $this, '_register_plugin_action_links' ] );
  }

  public function _display_management_page( $response ) {
    YIS_Template::load_template( 'tools.php' );
  }

  public function _register_plugin_action_links( $response ) :array {
    if( !is_array( $response ) )
      $response = [];

    $response[] = '<a href="tools.php?page=' . YOUTUBE_IMPORTER_IMPORTYOUTUBE_PREFIX . '">' . esc_attr__('Settings','import-youtube-videos' ) . '</a>';

    return $response;
  }

}