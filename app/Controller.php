<?php

namespace YoutubeImporterImportyoutube;

class Controller {

  /**
   * @var Controller;
   */
  protected static $_instance;

  /**
   * @return Controller
   */
  public static function instance(): Controller {
    if( self::$_instance === null )
      self::$_instance = new self();

    return self::$_instance;
  }

  public function setup() {
    load_plugin_textdomain( 'import-youtube-videos', false, YOUTUBE_IMPORTER_IMPORTYOUTUBE_LANGUAGE_DIRECTORY );
  }

}