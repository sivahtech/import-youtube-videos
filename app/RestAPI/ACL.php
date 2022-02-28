<?php

namespace YoutubeImporterImportyoutube\RestAPI;

use WP_Error;

class ACL {

  public static function admin_dismiss_notice() {
    if ( !current_user_can( YOUTUBE_IMPORTER_IMPORTYOUTUBE_SETTINGS_PERMISSION_CAP ) ) {
      return new WP_Error(
        'rest_forbidden',
        sprintf( __( 'You are not allowed to %s.', 'import-youtube-videos' ), YOUTUBE_IMPORTER_IMPORTYOUTUBE_SETTINGS_PERMISSION_CAP ),
        [
          'status' => 401
        ]
      );
    }

    return true;
  }

  public static function import_feed() {
    if ( !current_user_can( YOUTUBE_IMPORTER_IMPORTYOUTUBE_SETTINGS_PERMISSION_CAP ) ) {
      return new WP_Error(
        'rest_forbidden',
        sprintf( __( 'You are not allowed to %s.', 'import-youtube-videos' ), YOUTUBE_IMPORTER_IMPORTYOUTUBE_SETTINGS_PERMISSION_CAP ),
        [
          'status' => 401
        ]
      );
    }

    return true;
  }
  public static function check_feed() {
    if ( !current_user_can( YOUTUBE_IMPORTER_IMPORTYOUTUBE_SETTINGS_PERMISSION_CAP ) ) {
      return new WP_Error(
        'rest_forbidden',
        sprintf( __( 'You are not allowed to %s.', 'import-youtube-videos' ), YOUTUBE_IMPORTER_IMPORTYOUTUBE_SETTINGS_PERMISSION_CAP ),
        [
          'status' => 401
        ]
      );
    }

    return true;
  }

  public static function sync_feed() {
    if ( !current_user_can( YOUTUBE_IMPORTER_IMPORTYOUTUBE_SETTINGS_PERMISSION_CAP ) ) {
      return new WP_Error(
        'rest_forbidden',
        sprintf( __( 'You are not allowed to %s.', 'import-youtube-videos' ), YOUTUBE_IMPORTER_IMPORTYOUTUBE_SETTINGS_PERMISSION_CAP ),
        [
          'status' => 401
        ]
      );
    }

    return true;
  }

}