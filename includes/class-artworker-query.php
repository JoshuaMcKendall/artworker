<?php

/**
 * The Artworker Query class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The artworker query class.
 *
 * Contains the query functions for Artworker which alter the front-end post queries and loops
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

class Artworker_Query {

	/**
	 * Query vars to add to wp.
	 *
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Reference to the main artwork query on the page.
	 *
	 * @var array
	 */
	private static $artwork_query;

	/**
	 * Stores chosen attributes.
	 *
	 * @var array
	 */
	private static $_chosen_attributes;

	/**
	 * Constructor for the query class. Hooks in methods.
	 */
	public function __construct() {

		if ( ! is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'get_errors' ), 20 );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
			add_filter( 'post_type_archive_title', array( $this, 'gallery_title' ), 10, 2 );
			add_filter( 'nav_menu_css_class', array( $this, 'gallery_nav_classes' ), 10, 2 );
		}

		$this->init_query_vars();
	}

	/**
	 * Get any errors from querystring.
	 */
	public function get_errors() {
		$error = ! empty( $_GET['artworker_error'] ) ? sanitize_text_field( wp_unslash( $_GET['artworker_error'] ) ) : ''; // WPCS: input var ok, CSRF ok.

		if ( $error && ! artworker_has_notice( $error, 'error' ) ) {
			artworker_add_notice( $error, 'error' );
		}
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = array();
	}

	/**
	 * Add query vars.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key;
		}
		return $vars;
	}

	/**
	 * Get query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'artworker_get_query_vars', $this->query_vars );
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// Map query vars to their keys, or get them if endpoints are not supported.
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) { // WPCS: input var ok, CSRF ok.
				$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) ); // WPCS: input var ok, CSRF ok.
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}

	/**
	 * Are we currently on the front page?
	 *
	 * @param WP_Query $q Query instance.
	 * @return bool
	 */
	private function is_showing_page_on_front( $q ) {
		return ( $q->is_home() && ! $q->is_posts_page ) && 'page' === get_option( 'show_on_front' );
	}

	/**
	 * Is the front page a page we define?
	 *
	 * @param int $page_id Page ID.
	 * @return bool
	 */
	private function page_on_front_is( $page_id ) {
		return absint( get_option( 'page_on_front' ) ) === absint( $page_id );
	}

	/**
	 * Hook into pre_get_posts to do the main artwork query.
	 *
	 * @param WP_Query $q Query instance.
	 */
	public function pre_get_posts( $q ) {
		// We only want to affect the main query.
		if ( ! $q->is_main_query() ) {
			return;
		}

		// Fixes for queries on static homepages.
		if ( $this->is_showing_page_on_front( $q ) ) {

			// Fix for endpoints on the homepage.
			if ( ! $this->page_on_front_is( $q->get( 'page_id' ) ) ) {
				$_query = wp_parse_args( $q->query );
				if ( ! empty( $_query ) && array_intersect( array_keys( $_query ), array_keys( $this->get_query_vars() ) ) ) {
					$q->is_page     = true;
					$q->is_home     = false;
					$q->is_singular = true;
					$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
					add_filter( 'redirect_canonical', '__return_false' );
				}
			}

			// When orderby is set, WordPress shows posts on the front-page. Get around that here.
			if ( $this->page_on_front_is( artworker_get_page_id( 'gallery' ) ) ) {
				$_query = wp_parse_args( $q->query );
				if ( empty( $_query ) || ! array_diff( array_keys( $_query ), array( 'preview', 'page', 'paged', 'cpage', 'orderby' ) ) ) {
					$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
					$q->is_page = true;
					$q->is_home = false;

					// WP supporting themes show post type archive.
					if ( current_theme_supports( 'artworker' ) ) {
						$q->set( 'post_type', 'artwork' );
					} else {
						$q->is_singular = true;
					}
				}
			} elseif ( ! empty( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
				$q->set( 'page_id', (int) get_option( 'page_on_front' ) );
				$q->is_page = true;
				$q->is_home = false;
				$q->is_singular = true;
			}
		}

		// Fix artwork feeds.
		if ( $q->is_feed() && $q->is_post_type_archive( 'artwork' ) ) {
			$q->is_comment_feed = false;
		}

		// Special check for gallery with the ARTWORK POST TYPE ARCHIVE on front.
		if ( $q->is_page() && 'page' === get_option( 'show_on_front' ) && absint( $q->get( 'page_id' ) ) ===  artworker_get_page_id( 'gallery' ) ) {
			// This is a front-page gallery.
			$q->set( 'post_type', 'artwork' );
			$q->set( 'page_id', '' );

			if ( isset( $q->query['paged'] ) ) {
				$q->set( 'paged', $q->query['paged'] );
			}

			// Define a variable so we know this is the front page gallery later on.
			artworker_maybe_define_constant( 'GALLERY_IS_ON_FRONT', true );

			// Get the actual WP page to avoid errors and let us use is_front_page().
			// This is hacky but works. Awaiting https://core.trac.wordpress.org/ticket/21096.
			global $wp_post_types;

			$gallery_page = get_post( artworker_get_page_id( 'gallery' ) );

			$wp_post_types['artwork']->ID         = $gallery_page->ID;
			$wp_post_types['artwork']->post_title = $gallery_page->post_title;
			$wp_post_types['artwork']->post_name  = $gallery_page->post_name;
			$wp_post_types['artwork']->post_type  = $gallery_page->post_type;
			$wp_post_types['artwork']->ancestors  = get_ancestors( $gallery_page->ID, $gallery_page->post_type );

			// Fix conditional Functions like is_front_page.
			$q->is_singular          = false;
			$q->is_post_type_archive = true;
			$q->is_archive           = true;
			$q->is_page              = true;

			// Remove post type archive name from front page title tag.
			add_filter( 'post_type_archive_title', '__return_empty_string', 5 );

		} elseif ( ! $q->is_post_type_archive( 'artwork' ) && ! $q->is_tax( get_object_taxonomies( 'artwork' ) ) ) {
			// Only apply to artwork categories, the artwork post archive, the gallery page, artwork tags.
			return;
		}

		$this->artwork_query( $q );
	}

	/**
	 * Query artwork, applying sorting/ordering etc.
	 * This applies to the main WordPress loop.
	 *
	 * @param WP_Query $q Query instance.
	 */
	public function artwork_query( $q ) {
		if ( ! is_feed() ) {
			$q->set( 'orderby', 'date' );
			$q->set( 'order', 'DESC' );
		}

		// Query vars that affect posts shown.
		$q->set( 'meta_query', $this->get_meta_query( $q->get( 'meta_query' ), true ) );
		$q->set( 'tax_query', $this->get_tax_query( $q->get( 'tax_query' ), true ) );
		$q->set( 'artworker_query', 'artwork_query' );
		$q->set( 'post__in', array_unique( (array) apply_filters( 'loop_gallery_post_in', array() ) ) );

		// Work out how many artworks to query.
		$q->set( 'posts_per_page', $q->get( 'posts_per_page' ) ? $q->get( 'posts_per_page' ) : apply_filters( 'loop_gallery_per_page', artworker_get_artwork_per_page() ) );

		// Store reference to this query.
		self::$artwork_query = $q;

		do_action( 'artworker_artwork_query', $q, $this );
	}

	/**
	 * Remove the query.
	 */
	public function remove_artwork_query() {
		remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	/**
	 * Appends meta queries to an array.
	 *
	 * @param  array $meta_query Meta query.
	 * @param  bool  $main_query If is main query.
	 * @return array
	 */
	public function get_meta_query( $meta_query = array(), $main_query = false ) {
		if ( ! is_array( $meta_query ) ) {
			$meta_query = array();
		}
		return array_filter( apply_filters( 'artworker_artwork_query_meta_query', $meta_query, $this ) );
	}

	/**
	 * Appends tax queries to an array.
	 *
	 * @param  array $tax_query  Tax query.
	 * @param  bool  $main_query If is main query.
	 * @return array
	 */
	public function get_tax_query( $tax_query = array(), $main_query = false ) {
		if ( ! is_array( $tax_query ) ) {
			$tax_query = array(
				'relation' => 'AND',
			);
		}

		return array_filter( apply_filters( 'artworker_artwork_query_tax_query', $tax_query, $this ) );
	}

	/**
	 * Get the main query which artwork queries ran against.
	 *
	 * @return array
	 */
	public static function get_main_query() {
		return self::$artwork_query;
	}

	/**
	 * Get the tax query which was used by the main query.
	 *
	 * @return array
	 */
	public static function get_main_tax_query() {
		$tax_query = isset( self::$artwork_query->tax_query, self::$artwork_query->tax_query->queries ) ? self::$artwork_query->tax_query->queries : array();

		return $tax_query;
	}

	/**
	 * Get the meta query which was used by the main query.
	 *
	 * @return array
	 */
	public static function get_main_meta_query() {
		$args       = self::$artwork_query->query_vars;
		$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

		return $meta_query;
	}

	/**
	 * Based on WP_Query::parse_search
	 */
	public static function get_main_search_query_sql() {
		global $wpdb;

		$args         = self::$artwork_query->query_vars;
		$search_terms = isset( $args['search_terms'] ) ? $args['search_terms'] : array();
		$sql          = array();

		foreach ( $search_terms as $term ) {
			// Terms prefixed with '-' should be excluded.
			$include = '-' !== substr( $term, 0, 1 );

			if ( $include ) {
				$like_op  = 'LIKE';
				$andor_op = 'OR';
			} else {
				$like_op  = 'NOT LIKE';
				$andor_op = 'AND';
				$term     = substr( $term, 1 );
			}

			$like  = '%' . $wpdb->esc_like( $term ) . '%';
			$sql[] = $wpdb->prepare( "(($wpdb->posts.post_title $like_op %s) $andor_op ($wpdb->posts.post_excerpt $like_op %s) $andor_op ($wpdb->posts.post_content $like_op %s))", $like, $like, $like ); // unprepared SQL ok.
		}

		if ( ! empty( $sql ) && ! is_user_logged_in() ) {
			$sql[] = "($wpdb->posts.post_password = '')";
		}

		return implode( ' AND ', $sql );
	}

	/**
	 * Adds the current_page_parent class to the nav link of the page that the gallery is set on.
	 *
	 * @return array
	 */
	public function gallery_nav_classes( $classes, $item ) {

    	$custom_post_type = 'artwork';

        if( ( is_post_type_archive( $custom_post_type) || is_singular( $custom_post_type ) ) && get_post_meta( $item->ID, '_menu_item_object_id', true ) == get_option( 'page_for_posts' ) ) {

            $classes = array_diff( $classes, array( 'current_page_parent', 'current-menu-item', 'current_page_item' ) );

        }

        if( ( is_post_type_archive( $custom_post_type) || is_singular( $custom_post_type ) ) && get_post_meta( $item->ID, '_menu_item_object_id', true ) == get_option( 'artwork_archive_page' ) ) {

        	$classes[] = 'current_page_parent';
        	$classes[] = 'current-menu-item';
        	$classes[] = 'current_page_item';

        }

        return $classes;

	}

	public function gallery_title( $post_type_name, $post_type ) {

		$artwork_post_type = 'artwork';

		if( $post_type !== $artwork_post_type ) 
			return $title;

		$gallery_page_id = artworker_get_page_id( 'gallery' );
		$gallery_page = get_post( $gallery_page_id );

		if( empty( $gallery_page ) || $gallery_page_id === 0 )
			return $post_type_name;

		return $gallery_page->post_title;



	}

}