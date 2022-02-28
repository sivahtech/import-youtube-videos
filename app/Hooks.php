<?php

namespace YoutubeImporterImportyoutube;

use YoutubeImporterImportyoutube\Settings;

class Hooks {

  /**
   * @var Hooks;
   */
  protected static $_instance;

  /**
   * @return Hooks
   */
  public static function instance(): Hooks {
    if( self::$_instance === null )
      self::$_instance = new self();

    return self::$_instance;
  }

  public function setup() {
    add_filter( 'wp_kses_allowed_html', [ $this, '_wp_kses_allowed_html' ], 10, 2 );
    add_action( 'admin_notices', [ $this, '_admin_notice' ] );
  }

  public function _wp_kses_allowed_html( $tags, $context ) {
    if( !in_array( $context, youtube_importer_importyoutube_supported_post_types() ) )
      return $tags;

    $tags['iframe'] = array(
      'src'             => true,
      'height'          => true,
      'width'           => true,
      'style'			  		=> true,
      'frameborder'     => true,
      'allowfullscreen' => true,
      'scrolling'		  	=> true,
      'seamless'		  	=> true,
    );

    return $tags;
  }

  public function _admin_notice() {
    if( !current_user_can( YOUTUBE_IMPORTER_IMPORTYOUTUBE_SETTINGS_PERMISSION_CAP ) )
      return;

    if( youtube_importer_importyoutube_has_premium_theme() || ( defined( 'PODCAST_IMPORTER_PRO_SECONDLINE' ) ) || ( defined( 'YOUTUBE_IMPORTER_PRO_SECONDLINE' ) ) )
      return;

    if( isset( Settings::instance()->get( '_admin_notice_dismissed_map', [] )[ get_current_user_id() ] ) )
      return;

  }

}