<?php
  $current_tab = sanitize_text_field( ( $_GET['tab'] ?? null ) );
  $tabs = [
    'import'  => [
      'title'     => __( "Channel Details", 'importyoutube-youtube-importer' ),
      'template'  => 'importer-form.php'
    ]
  ];

  if( isset( $_GET[ 'post_id' ] ) && $current_tab === 'edit' )
    $tabs[ 'edit' ] = [
      'title'     => sprintf( __( "Edit Feed %s", 'importyoutube-youtube-importer' ), get_the_title( intval( $_GET[ 'post_id' ] ) ) ),
      'template'  => 'importer-form.php'
    ];

  $tabs[ 'settings' ] = [
    'title'     => __( "Api Settings", 'importyoutube-youtube-importer' ),
    'template'  => 'settings.php'
  ];

     

  $tabs = apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_tools_tabs', $tabs );

  if( $current_tab !== 'settings' && YoutubeImporterImportyoutube\Settings::instance()->get( 'youtube_api_key' ) === '' )
    $current_tab = 'settings';

  if( !isset( $tabs[ $current_tab ] ) )
    $current_tab = imyp_array_key_first( $tabs );

?><div class="wrap youtube-importer-import_youtube">
  <h1>
    <span><?php echo esc_html__('Import Viedo From Youtube Channel', 'importyoutube-youtube-importer' );?></span>
    
  </h1>

  <nav class="nav-tab-wrapper">
    <?php foreach( $tabs as $tab_alias => $tab_information ) : ?>
      <a href="tools.php?page=<?php echo YOUTUBE_IMPORTER_IMPORTYOUTUBE_PREFIX; ?>&tab=<?php echo esc_attr($tab_alias) . ( $tab_alias === 'edit' ? '&post_id=' . intval( $_GET[ 'post_id' ] ) : '' ); ?>"
         class="nav-tab<?php echo $tab_alias === $current_tab ? ' nav-tab-active' : '' ?>">
        <?php echo esc_html( $tab_information[ 'title' ] ); ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <?php
    if( isset( $tabs[ $current_tab ][ 'template' ] ) )
      youtube_importer_importyoutube_load_template( $tabs[ $current_tab ][ 'template' ] );
    else if( isset( $tabs[ $current_tab ][ 'content' ] ) )
      echo wp_kses_post($tabs[ $current_tab ][ 'content' ]);
  ?>
</div>