<?php

/**
 * The Post Types class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The artworker post types class.
 *
 * This class defines the post types and relevant taxonomies.
 *
 * @since      1.0.0
 * @package    Artworker
 * @subpackage Artworker/includes
 * @author     Joshua McKendall <artworker@joshuamckendall.com>
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

class Artworker_Post_Types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'artwork' ) ) {
			return;
		}

		do_action( 'artworker_register_post_type' );

		$labels = array(
			'name'               => _x( 'Artwork', 'post type general name', 'artworker' ),
			'singular_name'      => _x( 'Artwork', 'post type singular name', 'artworker' ),
			'menu_name'          => _x( 'Artwork', 'admin menu', 'artworker' ),
			'name_admin_bar'     => _x( 'Artwork', 'add new on admin bar', 'artworker' ),
			'add_new'            => _x( 'Add New', 'book', 'artworker' ),
			'add_new_item'       => __( 'Add New Artwork', 'artworker' ),
			'new_item'           => __( 'New Artwork', 'artworker' ),
			'edit_item'          => __( 'Edit Artwork', 'artworker' ),
			'view_item'          => __( 'View Artwork', 'artworker' ),
			'all_items'          => __( 'All Artwork', 'artworker' ),
			'search_items'       => __( 'Search Artwork', 'artworker' ),
			'parent_item_colon'  => __( 'Parent Artwork:', 'artworker' ),
			'not_found'          => __( 'No artwork found.', 'artworker' ),
			'not_found_in_trash' => __( 'No artwork found in Trash.', 'artworker' )
		);

		$artwork_page_id = get_option( 'artworker_art_gallery_page_id' );
		$artwork_page_slug = ( $artwork_page_id ) ? get_post_field( 'post_name', $artwork_page_id ) : _x( 'gallery', 'slug', 'artworker' );

		if ( $artwork_page_slug ) {
			$has_archive = $artwork_page_id && get_post( $artwork_page_id ) ? urldecode( get_page_uri( $artwork_page_id ) ) : 'gallery';
		} else {
			$has_archive = false;
		}

		$args = array(
			'labels'             => $labels,
	                'description'        => __( 'Description.', 'artworker' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $artwork_page_slug, 'with_front' => false, 'feeds' => true ), 
			'capability_type'    => 'post',
			'has_archive'        => $has_archive,//$has_archive,
			'hierarchical'       => false,
			'menu_position'      => 4,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ),
			'show_in_rest'       => true,
			'show_in_nav_menus'  => true,
			'template'           => array( 
				array( 'artworker/artwork-block' ),
				array( 'core/paragraph', array(
					'placeholder'	=> 'Write about this artwork'
				) )
			),
			'template_lock'      => 'all',
		);

		register_post_type( 'artwork', $args );

		do_action( 'artworker_after_register_post_type' );

	}
	/**
	 * Register core taxonomies.
	 */
	public static function register_taxonomies() {

		if ( ! is_blog_installed() ) {
			return;
		}

		do_action( 'artworker_register_taxonomy' );


		do_action( 'artworker_after_register_taxonomy' );
	}


	/**
	 * Flush rewrite rules.
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

}

Artworker_Post_Types::init();