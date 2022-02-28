<?php
/**
 * Plugin Name:       Import Youtube Videos
 * Description:       A simple YouTube video importer plugin .
 * Version:           1.0.1
 * Author:            Sivahtech
 * License:           GPL-2.0+
 * Text Domain:       import-youtube
*/

if ( ! defined( 'WPINC' ) )
	die;

define( 'YOUTUBE_IMPORTER_IMPORTYOUTUBE_VERSION', '1.0.1' );
define( "YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_FILE_PATH", __FILE__ );
define( "YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_PATH", dirname( YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_FILE_PATH ) );
define( "YOUTUBE_IMPORTER_IMPORTYOUTUBE_PLUGIN_IDENTIFIER", ltrim( str_ireplace( dirname( YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_PATH ), '', YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_FILE_PATH ), '/' ) );

define( "YOUTUBE_IMPORTER_IMPORTYOUTUBE_EXAMPLE_CHANNEL_ID", "UCxCxlu6_VkHBqql706Mmc3A" );

require_once YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_PATH . "/autoload.php";
require_once YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_PATH . "/definitions.php";
require_once YOUTUBE_IMPORTER_IMPORTYOUTUBE_BASE_PATH . "/functions.php";


// Various Hooks & Additions.
YoutubeImporterImportyoutube\Hooks::instance()->setup();

// Post Types
add_action( 'init', [ YoutubeImporterImportyoutube\PostTypes::instance(), 'setup' ] );

// RestAPI
add_action( 'rest_api_init', [ YoutubeImporterImportyoutube\RestAPI::instance(), 'setup' ] );

// General Functionality
add_action( 'plugins_loaded', [ YoutubeImporterImportyoutube\Controller::instance(), 'setup' ] );

if ( is_admin() ) {
  add_action( 'admin_menu', [ YoutubeImporterImportyoutube\AdminMenu::instance(), 'setup' ] );
  add_action( 'admin_enqueue_scripts', [ YoutubeImporterImportyoutube\AdminAssets::instance(), 'setup' ] );
}

register_deactivation_hook( __FILE__, function() {
  as_unschedule_action( YOUTUBE_IMPORTER_IMPORTYOUTUBE_ALIAS . '_scheduler_feeds_sync' );
} );
function imyt_custom_post_type() {
 

    $labels = array(
        'name'                => _x( 'Video', 'Post Type General Name', 'importyoutube' ),
        'singular_name'       => _x( 'Video', 'Post Type Singular Name', 'importyoutube' ),
        'menu_name'           => __( 'Video', 'importyoutube' ),
        'parent_item_colon'   => __( 'Parent Video', 'importyoutube' ),
        'all_items'           => __( 'All Video', 'importyoutube' ),
        'view_item'           => __( 'View Video', 'importyoutube' ),
        'add_new_item'        => __( 'Add New Video', 'importyoutube' ),
        'add_new'             => __( 'Add New', 'importyoutube' ),
        'edit_item'           => __( 'Edit Video', 'importyoutube' ),
        'update_item'         => __( 'Update Video', 'importyoutube' ),
        'search_items'        => __( 'Search Video', 'importyoutube' ),
        'not_found'           => __( 'Not Found', 'importyoutube' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'importyoutube' ),
    );
     
	$args = array(
        'label'               => __( 'Videos', 'importyoutube' ),
        'description'         => __( 'Video news and reviews', 'importyoutube' ),
        'labels'              => $labels,
        
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        
        
       
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
 
    );
     
    // Registering your Custom Post Type
    register_post_type( 'videos', $args );
 
}
 

 
add_action( 'init', 'imyt_custom_post_type', 0 );

add_action( 'init', 'imyt_create_categories_hierarchical_taxonomy', 0 );
 

 
function imyt_create_categories_hierarchical_taxonomy() {
 

 
  $labels = array(
    'name' => _x( 'Categories', 'taxonomy general name' ),
    'singular_name' => _x( 'Categories', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Categories' ),
    'all_items' => __( 'All Categories' ),
    'parent_item' => __( 'Parent Categories' ),
    'parent_item_colon' => __( 'Parent Categories:' ),
    'edit_item' => __( 'Edit Categories' ), 
    'update_item' => __( 'Update Categories' ),
    'add_new_item' => __( 'Add New Categories' ),
    'new_item_name' => __( 'New Categories Name' ),
    'menu_name' => __( 'Categories' ),
  );    
 

  register_taxonomy('categories',array('videos'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'categories' ),
  ));
 
}
