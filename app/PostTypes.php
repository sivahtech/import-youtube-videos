<?php

namespace YoutubeImporterImportyoutube;

class PostTypes {

  /**
   * @var PostTypes;
   */
  protected static $_instance;

  /**
   * @return PostTypes
   */
  public static function instance(): PostTypes {
    if( self::$_instance === null )
      self::$_instance = new self();

    return self::$_instance;
  }

  public function setup() {
    register_post_type(
      YOUTUBE_IMPORTER_IMPORTYOUTUBE_POST_TYPE_IMPORT,
      [
        'labels' => [
          'name'          => __( 'YouTube Imports', 'import-youtube-videos' ),
          'singular_name' => __( 'YouTube Import', 'import-youtube-videos' )
        ],
        'public'              => true,
        'has_archive'         => false,
        'supports'            => [ 'title' ],
        'can_export'          => false,
        'exclude_from_search' => true,
        'show_in_admin_bar'   => false,
        'show_in_nav_menus'   => false,
        'publicly_queryable'  => false,
      ]
    );

    add_action( 'admin_menu', [ $this, '_admin_menu' ] );
  }

  public function _admin_menu() {
    if( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'trash' )
      return;

    if( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] === YOUTUBE_IMPORTER_IMPORTYOUTUBE_POST_TYPE_IMPORT )
      return;

    remove_menu_page( 'edit.php?post_type=' . YOUTUBE_IMPORTER_IMPORTYOUTUBE_POST_TYPE_IMPORT );
  }

}