<?php

/**
 * The Artwork Factory class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The artwork factory class.
 *
 * This class builds the artwork object.
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
 * Product factory class.
 */
class Artworker_Artwork_Factory {

	/**
	 * Get artwork.
	 *
	 * @param mixed $artwork_id Artworker_Artwork|WP_Post|int|bool $artwork Artwork instance, post instance, numeric or false to use global $post.
	 *
	 * @return Artworker_Artwork|bool Artwork object or false if the artwork cannot be loaded.
	 */
	public function get_artwork( $artwork_id = false ) {
		$artwork_id = $this->get_artwork_id( $artwork_id );

		if ( ! $artwork_id ) {
			return false;
		}

		try {
			return new Artworker_Artwork( $artwork_id );
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Get the artwork ID depending on what was passed.
	 *
	 * @since  3.0.0
	 * @param  Artworker_Artwork|WP_Post|int|bool $artwork Artwork instance, post instance, numeric or false to use global $post.
	 * @return int|bool false on failure
	 */
	private function get_artwork_id( $artwork ) {
		global $post;

		if ( false === $artwork && isset( $post, $post->ID ) && 'artwork' === get_post_type( $post->ID ) ) {
			return absint( $post->ID );
		} elseif ( is_numeric( $artwork ) ) {
			return $artwork;
		} elseif ( $artwork instanceof Artworker_Artwork ) {
			return $artwork->get_id();
		} elseif ( ! empty( $artwork->ID ) ) {
			return $artwork->ID;
		} else {
			return false;
		}
	}
}