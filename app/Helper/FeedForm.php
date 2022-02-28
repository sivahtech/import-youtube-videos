<?php

namespace YoutubeImporterImportyoutube\Helper;

class FeedForm {

  public static function get_for_render( $post_id = null ) :array {
    $field_definitions = self::field_definitions();
    $response = [];

    foreach( $field_definitions as $key => $field_definition ) {
      if( !isset( $field_definition[ 'storage' ] ) )
        continue;

      if( isset( $field_definition[ 'views' ] ) ) {
        if( !in_array( 'add', $field_definition[ 'views' ] ) && !is_numeric( $post_id ) )
          continue;

        if( !in_array( 'edit', $field_definition[ 'views' ] ) && is_numeric( $post_id ) )
          continue;

        unset( $field_definition[ 'views' ] );
      }

      $storage = $field_definition[ 'storage' ];

      unset( $field_definition[ 'storage' ] );

      if( is_numeric( $post_id ) ) {
        $field_definition[ 'value' ] = null;

        if( $storage[ 'type' ] === 'meta' )
          $field_definition[ 'value' ] = get_post_meta( $post_id, $storage[ 'meta' ], ( $storage['meta_is_single'] ?? true ) );

        if( isset( $field_definition[ 'options' ] )
            && !is_array( $field_definition[ 'value' ] )
            && !isset( $field_definition[ 'options' ][ $field_definition[ 'value' ] ] )
            && !empty( $field_definition[ 'value' ] )
        )
          $field_definition[ 'options' ] = [ $field_definition[ 'value' ] => $field_definition[ 'value' ] ] + $field_definition[ 'options' ];
      }

      if( ( !isset( $field_definition[ 'value' ] ) || $field_definition[ 'value' ] === null ) && isset( $field_definition[ 'default' ] ) )
        $field_definition[ 'value' ] = $field_definition[ 'default' ];

      unset( $field_definition[ 'default' ] );

      $response[ $key ] = $field_definition;
    }

    return apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_feed_form_for_render', $response, $field_definitions );
  }

  public static function field_definitions() :array {
    $response = [];

    $response[ 'channel_id' ] = [
      'label'       => __( 'YouTube Channel ID', 'import-youtube-videos' ),
      'name'        => 'channel_id',
      'type'        => 'text',
      'required'    => 1,
      'placeholder' => YOUTUBE_IMPORTER_IMPORTYOUTUBE_EXAMPLE_CHANNEL_ID,
      'storage'     => [
        'type'  => 'meta',
        'meta'  => 'importyoutube_youtube_channel_id'
      ]
    ];

    $post_type_options = [];

    foreach( youtube_importer_importyoutube_supported_post_types() as $post_type )
      $post_type_options[ $post_type ] = get_post_type_object( $post_type )->labels->singular_name;

    $response[ 'post_type' ] = [
      'label'       => __( 'Select Post Type', 'import-youtube-videos' ),
      'name'        => 'post_type',
      'type'        => 'select',
      'options'     => $post_type_options,
      'default'     => youtube_importer_importyoutube_default_post_type(),
      'storage'     => [
        'type'  => 'meta',
        'meta'  => 'importyoutube_import_post_type'
      ]
    ];

    $response[ 'post_status' ] = [
      'label'       => __( 'Select Status', 'import-youtube-videos' ),
      'name'        => 'post_status',
      'type'        => 'select',
      'options'     => [
        'publish' => __( 'Publish', 'import-youtube-videos' ),
        'draft'   => __( 'Save as Draft', 'import-youtube-videos' )
      ],
      'storage'     => [
        'type'  => 'meta',
        'meta'  => 'importyoutube_import_publish'
      ]
    ];

    $response[ 'post_author' ] = [
      'label'       => __( 'Select Post Author', 'import-youtube-videos' ),
      'name'        => 'post_author',
      'type'        => 'wp_dropdown_users',
      'storage'     => [
        'type'  => 'meta',
        'meta'  => 'importyoutube_import_author'
      ]
    ];

    $response[ 'post_taxonomies' ] = [
      'label'       => __( 'Select Post Category (or Categories)', 'import-youtube-videos' ),
      'name'        => 'post_taxonomies',
      'type'        => 'multiple_select',
      'options'     => youtube_importer_importyoutube_get_taxonomies_select_definition( array_keys( $post_type_options ), true ),
      'storage'     => [
        'type'  => 'meta',
        'meta'  => 'importyoutube_import_category'
      ]
    ];
	$response[ 'import_date_from' ] = [
      'label'           => __( 'Date Limit', 'import-youtube-videos' ),
      'name'            => 'import_date_from',
      'type'            => 'date',
      'placeholder'     => __( '01-01-2022', 'import-youtube-videos' ),
      'description'     => __( 'Optional: only import posts after a certain date.', 'import-youtube-videos' ),
      'storage'         => [
        'type'  => 'meta',
        'meta'  => 'importyoutube_import_date_from'
      ]
    ];
	
	$response[ 'channel_removed_id' ] = [
      'label'           => '',
      'name'            => 'channel_removed_id',
      'type'            => 'hidden',
      'placeholder'     => '',
     
      'storage'         => [
        'type'  => 'meta',
        'meta'  => 'importyoutube_channel_removed_id'
      ]
    ];

    return apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_feed_form_definitions', $response );
  }

  public static function request_data_to_meta_map( $request_data ) :array {
    $field_definitions = self::field_definitions();
    $response = [];

    foreach( $field_definitions as $field_definition ) {
      if( !isset( $field_definition[ 'storage' ][ 'meta' ] ) )
        continue;

      if( isset( $request_data[ $field_definition[ 'name' ] ] ) ) {
        if( is_array( $request_data[ $field_definition[ 'name' ] ] ) ) {
          $response[ $field_definition[ 'storage' ][ 'meta' ] ] = array_map( 'intval', $request_data[ $field_definition[ 'name' ] ] );
          continue;
        }

        $response[ $field_definition[ 'storage' ][ 'meta' ] ] = sanitize_text_field( $request_data[ $field_definition[ 'name' ] ] );
        continue;
      }

      if( isset( $field_definition[ 'default' ] ) )
        $request_data[ $field_definition[ 'storage' ][ 'meta' ] ] = $field_definition[ 'default' ];
      else if( isset( $field_definition[ 'value_unchecked' ] ) )
        $request_data[ $field_definition[ 'storage' ][ 'meta' ] ] = $field_definition[ 'value_unchecked' ];
    }

    return apply_filters( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_feed_form_request_data_to_meta_map', $response, $request_data, $field_definitions );
  }

}