<?php
/**
 * Artworker Artwork Data Store CPT class
 *
 * @link https://github.com/JoshuaMcKendall/artworker/tree/master/includes/data-stroes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * This class handles data storage for Artworker Artwork.
 *
 * @since      1.0.0
 * @package    Artworker
 * @subpackage Artworker/includes
 * @author     Joshua McKendall <artworker@joshuamckendall.com>
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

/**
 * Artworker Artwork Data Store: Stored in CPT.
 *
 * @version  1.0.0
 */
class Artworker_Artwork_Data_Store_CPT extends Artworker_Data_Store_WP implements Artworker_Object_Data_Store_Interface {

	/**
	 * Method to read a artwork from the database.
	 *
	 * @param Artworker_Artwork $artwork Artwork object.
	 * @throws Exception If invalid artwork.
	 */
	public function read( &$artwork ) {
		
		$post_object = get_post( $artwork->get_id() );

		if ( ! $artwork->get_id() || ! $post_object || 'artwork' !== $post_object->post_type ) {
			throw new Exception( __( 'Invalid artwork.', 'artworker' ) );
		}

		$artwork->set_props(
			array(
				'name'              => $post_object->post_title,
				'slug'              => $post_object->post_name,
				'date_created'      => $post_object->post_date_gmt,
				'date_modified'     => $post_object->post_modified_gmt,
				'status'            => $post_object->post_status,
				'description'       => $post_object->post_content,
				'short_description' => $post_object->post_excerpt,
				'parent_id'         => $post_object->post_parent,
				'menu_order'        => $post_object->menu_order,
				'post_password'     => $post_object->post_password,
				'comments_open'   	=> 'open' === $post_object->comment_status,
			)
		);

		do_action( 'artworker_artwork_read', $artwork->get_id() );
	}

	/**
	 * Returns an array of artwork.
	 *
	 * @param  array $args Args to pass to Artworker_Artwork_Query().
	 * @return array|object
	 * @see artworker_get_artworks
	 */
	public function get_artworks( $args = array() ) {
		$query = new Artworker_Artwork_Query( $args );
		return $query->get_artworks();
	}

	/**
	 * Get valid WP_Query args from a WC_Product_Query's query variables.
	 *
	 * @since 1.0.0
	 * @param array $query_vars Query vars from a WC_Product_Query.
	 * @return array
	 */
	protected function get_wp_query_args( $query_vars ) {

		// Map query vars to ones that get_wp_query_args or WP_Query recognize.
		$key_mapping = array(
			'status'         => 'post_status',
			'page'           => 'paged',
			'include'        => 'post__in',
		);

		foreach ( $key_mapping as $query_key => $db_key ) {
			if ( isset( $query_vars[ $query_key ] ) ) {
				$query_vars[ $db_key ] = $query_vars[ $query_key ];
				unset( $query_vars[ $query_key ] );
			}
		}

		$wp_query_args = parent::get_wp_query_args( $query_vars );

		if ( ! isset( $wp_query_args['date_query'] ) ) {
			$wp_query_args['date_query'] = array();
		}
		if ( ! isset( $wp_query_args['meta_query'] ) ) {
			$wp_query_args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}


		$wp_query_args['post_type']   = 'artwork';

		// Handle artwork categories.
		if ( ! empty( $query_vars['category'] ) ) {
			$wp_query_args['tax_query'][] = array(
				'taxonomy' => 'artwork_cat',
				'field'    => 'slug',
				'terms'    => $query_vars['category'],
			);
		}

		// Handle artwork tags.
		if ( ! empty( $query_vars['tag'] ) ) {
			unset( $wp_query_args['tag'] );
			$wp_query_args['tax_query'][] = array(
				'taxonomy' => 'artwork_tag',
				'field'    => 'slug',
				'terms'    => $query_vars['tag'],
			);
		}

		// Handle date queries.
		$date_queries = array(
			'date_created'      => 'post_date',
			'date_modified'     => 'post_modified',
		);
		foreach ( $date_queries as $query_var_key => $db_key ) {
			if ( isset( $query_vars[ $query_var_key ] ) && '' !== $query_vars[ $query_var_key ] ) {

				// Remove any existing meta queries for the same keys to prevent conflicts.
				$existing_queries = wp_list_pluck( $wp_query_args['meta_query'], 'key', true );
				foreach ( $existing_queries as $query_index => $query_contents ) {
					unset( $wp_query_args['meta_query'][ $query_index ] );
				}

				$wp_query_args = $this->parse_date_for_wp_query( $query_vars[ $query_var_key ], $db_key, $wp_query_args );
			}
		}

		// Handle paginate.
		if ( ! isset( $query_vars['paginate'] ) || ! $query_vars['paginate'] ) {
			$wp_query_args['no_found_rows'] = true;
		}

		// Handle reviews_allowed.
		if ( isset( $query_vars['reviews_allowed'] ) && is_bool( $query_vars['reviews_allowed'] ) ) {
			add_filter( 'posts_where', array( $this, 'reviews_allowed_query_where' ), 10, 2 );
		}

		// Handle orderby.
		if ( isset( $query_vars['orderby'] ) && 'include' === $query_vars['orderby'] ) {
			$wp_query_args['orderby'] = 'post__in';
		}

		return apply_filters( 'artworker_artwork_data_store_cpt_get_artworks_query', $wp_query_args, $query_vars, $this );
	}


	/**
	 * Query for Artworks matching specific criteria.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_vars Query vars from a Artworker_Artwork_Query.
	 *
	 * @return array|object
	 */
	public function query( $query_vars ) {
		$args = $this->get_wp_query_args( $query_vars );

		if ( ! empty( $args['errors'] ) ) {
			$query = (object) array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			);
		} else {
			$query = new WP_Query( $args );
		}

		if ( isset( $query_vars['return'] ) && 'objects' === $query_vars['return'] && ! empty( $query->posts ) ) {
			// Prime caches before grabbing objects.
			update_post_caches( $query->posts, 'artwork' );
		}

		$artworks = ( isset( $query_vars['return'] ) && 'ids' === $query_vars['return'] ) ? $query->posts : array_filter( array_map( 'artworker_get_artwork', $query->posts ) );

		if ( isset( $query_vars['paginate'] ) && $query_vars['paginate'] ) {
			return (object) array(
				'artworks'      => $artworks,
				'total'         => $query->found_posts,
				'max_num_pages' => $query->max_num_pages,
			);
		}

		return $artworks;
	}

}
