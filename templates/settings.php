<?php if( isset( $_POST[ 'youtube_api_key' ] ) && strpos( $_POST[ 'youtube_api_key' ], '****' ) === false ) : ?>
  <?php $api_key = sanitize_text_field( $_POST[ 'youtube_api_key' ] ); ?>

  <?php if( YoutubeImporterImportyoutube\Helper\Youtube::is_valid_api_key( $api_key ) ) : ?>
    <?php YoutubeImporterImportyoutube\Settings::instance()->update( 'youtube_api_key', $api_key ); ?>
  <?php else: ?>
    <div data-importyoutube-import-notification="warning">
      <?php echo esc_html__( 'A valid youtube API Key is required in order for this plugin to work.', 'import-youtube-videos' ); ?>
    </div>
  <?php endif; ?>

<?php else : ?>
  <?php if( YoutubeImporterImportyoutube\Settings::instance()->get( 'youtube_api_key' ) === '' ) : ?>
    <div data-importyoutube-import-notification="warning">
      <?php echo esc_html__( 'A valid youtube API Key is required in order for this plugin to work.', 'import-youtube-videos' ); ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<br/>

<div class="main-container-importyoutube">
  <form method="POST" class="importyoutube_settings_form">
    <?php youtube_importer_importyoutube_load_template( '_form-field.php', [ 'data' => [
        'label'           => __( 'API Key', 'import-youtube-videos' ),
        'name'            => 'youtube_api_key',
        'type'            => 'text',
        'description'     => ( ( ( !isset( $api_key ) && YoutubeImporterImportyoutube\Settings::instance()->get('youtube_api_key' ) !== ''  )
                              || ( isset( $api_key ) && $api_key === YoutubeImporterImportyoutube\Settings::instance()->get('youtube_api_key' ) )
                            ) ? '<p class="valid-key-flag">' . __( "Success: API Key is Valid", "import-youtube-videos") . '</p>' : '' ) .
                            "<p></p>",
        'value'           => ( isset( $api_key ) ? $api_key : youtube_importer_importyoutube_mask( YoutubeImporterImportyoutube\Settings::instance()->get('youtube_api_key' ) ) )
    ] ] ); ?>

    <br/>

    <input class="button button-primary" type="submit" name="save_settings" value="<?php echo esc_attr( __( "Save Settings", 'import-youtube-videos' ) ); ?>"/>
  </form>
</div>