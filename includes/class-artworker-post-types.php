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
			'menu_icon' 		 => 'dashicons-art',
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

		$permalinks = artworker_get_permalink_structure();

		do_action( 'artworker_register_taxonomy' );

		register_taxonomy(
			'artwork_cat',
			apply_filters( 'artworker_taxonomy_objects_artwork_cat', array( 'artwork' ) ),
			apply_filters(
				'artworker_taxonomy_args_artwork_cat',
				array(
					'hierarchical'          => true,
					'update_count_callback' => '_artworker_term_recount',
					'label'                 => __( 'Categories', 'artworker' ),
					'labels'                => array(
						'name'              => __( 'Artwork categories', 'artworker' ),
						'singular_name'     => __( 'Category', 'artworker' ),
						'menu_name'         => _x( 'Categories', 'Admin menu name', 'artworker' ),
						'search_items'      => __( 'Search categories', 'artworker' ),
						'all_items'         => __( 'All categories', 'artworker' ),
						'parent_item'       => __( 'Parent category', 'artworker' ),
						'parent_item_colon' => __( 'Parent category:', 'artworker' ),
						'edit_item'         => __( 'Edit category', 'artworker' ),
						'update_item'       => __( 'Update category', 'artworker' ),
						'add_new_item'      => __( 'Add new category', 'artworker' ),
						'new_item_name'     => __( 'New category name', 'artworker' ),
						'not_found'         => __( 'No categories found', 'artworker' ),
					),
					'show_ui'               => true,
					'show_admin_column'		=> true,
					'show_in_rest'			=> true,
					'query_var'             => true,
					'capabilities'          => array(
						'manage_terms' => 'manage_artwork_terms',
						'edit_terms'   => 'edit_artwork_terms',
						'delete_terms' => 'delete_artwork_terms',
						'assign_terms' => 'assign_artwork_terms',
					),
					'rewrite'               => array(
						'slug'         => $permalinks['category_rewrite_slug'],
						'with_front'   => false,
						'hierarchical' => true,
					),
				)
			)
		);

		register_taxonomy(
			'artwork_tag',
			apply_filters( 'artworker_taxonomy_objects_artwork_tag', array( 'artwork' ) ),
			apply_filters(
				'artworker_taxonomy_args_artwork_tag',
				array(
					'hierarchical'          => false,
					'update_count_callback' => '_artworker_term_recount',
					'label'                 => __( 'Artwork tags', 'artworker' ),
					'labels'                => array(
						'name'                       => __( 'Artwork tags', 'artworker' ),
						'singular_name'              => __( 'Tag', 'artworker' ),
						'menu_name'                  => _x( 'Tags', 'Admin menu name', 'artworker' ),
						'search_items'               => __( 'Search tags', 'artworker' ),
						'all_items'                  => __( 'All tags', 'artworker' ),
						'edit_item'                  => __( 'Edit tag', 'artworker' ),
						'update_item'                => __( 'Update tag', 'artworker' ),
						'add_new_item'               => __( 'Add new tag', 'artworker' ),
						'new_item_name'              => __( 'New tag name', 'artworker' ),
						'popular_items'              => __( 'Popular tags', 'artworker' ),
						'separate_items_with_commas' => __( 'Separate tags with commas', 'artworker' ),
						'add_or_remove_items'        => __( 'Add or remove tags', 'artworker' ),
						'choose_from_most_used'      => __( 'Choose from the most used tags', 'artworker' ),
						'not_found'                  => __( 'No tags found', 'artworker' ),
					),
					'show_ui'               => true,
					'show_admin_column'		=> true,
					'show_in_rest'			=> true,
					'query_var'             => true,
					'capabilities'          => array(
						'manage_terms' => 'manage_artwork_terms',
						'edit_terms'   => 'edit_artwork_terms',
						'delete_terms' => 'delete_artwork_terms',
						'assign_terms' => 'assign_artwork_terms',
					),
					'rewrite'               => array(
						'slug'       => $permalinks['tag_rewrite_slug'],
						'with_front' => false,
					),
				)
			)
		);

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