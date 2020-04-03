<?php

/**
 * The Artwork Query class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The artwork query class.
 *
 * This class builds the artwork query.
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

/**
 * Artwork query class.
 */
class Artworker_Artwork_Query extends Artworker_Object_Query {

	/**
	 * Valid query vars for artworks.
	 *
	 * @return array
	 */
	protected function get_default_query_vars() {
		return array_merge(
			parent::get_default_query_vars(),
			array(
				'status'            => array( 'draft', 'pending', 'private', 'publish' ),
				'limit'             => get_option( 'artwork_count' ),
				'include'           => array(),
				'date_created'      => '',
				'date_modified'     => '',
				'featured'          => '',
				'visibility'        => '',
				'category'          => array(),
				'tag'               => array()
			)
		);
	}

	/**
	 * Get artworks matching the current query vars.
	 *
	 * @return array|object of Artworker_Artwork objects
	 */
	public function get_artworks() {
		$args    = apply_filters( 'artworker_artwork_object_query_args', $this->get_query_vars() );
		$results = Artworker_Data_Store::load( 'artwork' )->query( $args );
		return apply_filters( 'artworker_artwork_object_query', $results, $args );
	}
}
