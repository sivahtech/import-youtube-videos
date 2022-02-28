<?php
if ( ! function_exists( 'imyp_array_key_first' ) ) {

  
  function imyp_array_key_first( $array ) :string {
    foreach ( $array as $key => $unused ) {
      return $key;
    }

    return '';
  }

}

if (! function_exists("imyp_array_key_last")) {
  function imyp_array_key_last($array) {
    if (!is_array($array) || empty($array)) {
      return NULL;
    }

    return array_keys($array)[count($array)-1];
  }
}

function youtube_importer_importyoutube_mask( $string ) {
  $response = substr( $string, 0, 5 );

  for( $i = 5; $i <= strlen( $string ); $i++ )
    $response .= '*';

  return $response;
}

if( !function_exists( 'youtube_importer_importyoutube_redirect' ) ) {

  function youtube_importer_importyoutube_redirect( $location, $status = 302 ) {
    wp_redirect( $location, $status, YOUTUBE_IMPORTYOUTUBE_SECONDLINE_NAME );
    exit;
  }

}

if( !function_exists( 'youtube_importer_importyoutube_load_template' ) ) {

  /**
   * @param $template_name
   * @param array $args
   * @param string $template_path
   * @param string $default_path
   */
  function youtube_importer_importyoutube_load_template( $template_name, $args = [], $template_path = '', $default_path = '' ) {
    YoutubeImporterImportyoutube\Template::load_template( $template_name, $args, $template_path, $default_path );
  }

}

if( !function_exists( 'youtube_importer_importyoutube_has_premium_theme' ) ) {

  function youtube_importer_importyoutube_has_premium_theme() :bool {
    return function_exists('importyoutube_themes_setup');
  }

}

if( !function_exists( 'youtube_importer_importyoutube_supported_post_types' ) ) {

  function youtube_importer_importyoutube_supported_post_types() :array {
    $response = [ 'videos' ];

    return apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_supported_post_types', $response );
  }

}

if( !function_exists( 'youtube_importer_importyoutube_default_post_type' ) ) {

  function youtube_importer_importyoutube_default_post_type() :string {
    return apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_default_post_type', 'videos' );
  }

}

if( !function_exists( 'youtube_importer_importyoutube_post_type_id_title_assoc' ) ) {

  function youtube_importer_importyoutube_post_type_id_title_assoc( string $post_type ) :array {
    $shows = get_posts( [
      'post_type'   => $post_type,
      'numberposts' => 9999,
    ] );

    $response = [];

    foreach ( $shows as $post )
      $response[ $post->ID ] = $post->post_title;

    return $response;
  }

}

if( !function_exists( 'youtube_importer_importyoutube_feed_limit_reached' ) ) {

  function youtube_importer_importyoutube_feed_limit_reached() :bool {
    return count( get_posts( [ 'post_type' => YOUTUBE_IMPORTER_IMPORTYOUTUBE_POST_TYPE_IMPORT, 'fields' => 'ids' ] ) ) >= youtube_importer_importyoutube_feed_cron_limit();
  }

}

if( !function_exists( 'youtube_importer_importyoutube_feed_cron_limit' ) ) {

  function youtube_importer_importyoutube_feed_cron_limit() {
    return apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_feed_cron_limit', 1 );
  }

}

if( !function_exists( 'youtube_importer_importyoutube_get_taxonomy_type_select_definition' ) ) {

  function youtube_importer_importyoutube_get_taxonomy_type_select_definition( $post_types, $hierarchical = true ) :array {
    $hierarchical = boolval( $hierarchical );

    $taxonomies_post_type_map = [];

    foreach( $post_types as $post_type ) {
      $post_type_taxonomies = get_object_taxonomies( $post_type );

      foreach( $post_type_taxonomies as $post_type_taxonomy ) {
        if( isset( $taxonomies_post_type_map[ $post_type_taxonomy ] ) ) {
          $taxonomies_post_type_map[ $post_type_taxonomy ][] = $post_type;

          continue;
        }

        $taxonomy_information = get_taxonomy( $post_type_taxonomy );

        if( boolval( $taxonomy_information->hierarchical ) !== $hierarchical )
          continue;

        $taxonomies_post_type_map[ $post_type_taxonomy ] = [ $post_type ];
      }
    }

    if( empty( $taxonomies_post_type_map ) )
      return [];

    $response = [];

    foreach( $taxonomies_post_type_map as $taxonomy => $post_types ) {
      $response[ $taxonomy ] = [
        'data-post-types' => implode( ' ', $post_types ),
        'label'           => $taxonomy
      ];
    }

    return $response;
  }

}

if( !function_exists( 'youtube_importer_importyoutube_get_taxonomies_select_definition' ) ) {

  function youtube_importer_importyoutube_get_taxonomies_select_definition( $post_types, $hierarchical = true ) :array {
    $hierarchical = boolval( $hierarchical );

    $taxonomies_post_type_map = [];

    foreach( $post_types as $post_type ) {
      $post_type_taxonomies = get_object_taxonomies( $post_type );

      foreach( $post_type_taxonomies as $post_type_taxonomy ) {
        if( isset( $taxonomies_post_type_map[ $post_type_taxonomy ] ) ) {
          $taxonomies_post_type_map[ $post_type_taxonomy ][] = $post_type;

          continue;
        }

        $taxonomy_information = get_taxonomy( $post_type_taxonomy );

        if( boolval( $taxonomy_information->hierarchical ) !== $hierarchical )
          continue;

        $taxonomies_post_type_map[ $post_type_taxonomy ] = [ $post_type ];
      }
    }

    if( empty( $taxonomies_post_type_map ) )
      return [];

    $response = [];

    foreach( $taxonomies_post_type_map as $taxonomy => $post_types ) {
      $categories = get_categories([ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );

      foreach( $categories as $category ) {
        $response[ $category->term_id ] = [
          'data-post-types' => implode( ' ', $post_types ),
          'label'           => ( count( $post_types ) >= 2 ? $taxonomy . ' - ' : '' ) . $category->name
        ];
      }
    }

    return $response;
  }

}

if( !function_exists( 'youtube_importer_importyoutube_utility_selected' ) ) {

  /**
   * @param $selected
   * @param bool $current
   * @param bool $echo
   * @return string
   */
  function youtube_importer_importyoutube_utility_selected( $selected, $current = true, $echo = true ) {
    if( is_array( $selected ) )
      return selected( 1, in_array( $current, $selected ), $echo );

    return selected( $selected, $current, $echo );
  }

}
