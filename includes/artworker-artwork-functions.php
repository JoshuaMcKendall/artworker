<?php

/**
 * Main function for returning artwork, uses the Artworker_Artwork_Factory class.
 *
 * This function should only be called after 'init' action is finished, as there might be taxonomies that are getting
 * registered during the init action.
 *
 * @since 1.0.0
 *
 * @param mixed $the_artwork Post object or post ID of the artwork.
 *
 * @return Artworker_Artwork|null|false
 */
function artworker_get_artwork( $the_artwork = false ) {
	if ( ! did_action( 'artworker_init' ) || ! did_action( 'artworker_after_register_taxonomy' ) || ! did_action( 'artworker_after_register_post_type' ) ) {
		/* translators: 1: artworker_get_artwork 2: artworker_init 3: artworker_after_register_taxonomy 4: artworker_after_register_post_type */
		_doing_it_wrong( __FUNCTION__, sprintf( __( '%1$s should not be called before the %2$s, %3$s and %4$s actions have finished.', 'artworker' ), 'artworker_get_artwork', 'artworker_init', 'artworker_after_register_taxonomy', 'artworker_after_register_post_type' ), '1.0.0' );
		return false;
	}

	return Artworker()->artwork_factory->get_artwork( $the_artwork );
}

/**
 * Standard way of retrieving artwork based on certain parameters.
 *
 * This function should be used for artwork retrieval so that we have a data agnostic
 * way to get a list of artwork.
 *
 * @since  1.0.0
 * @param  array $args Array of args (above).
 * @return array|stdClass Number of pages and an array of artwork objects if
 *                             paginate is true, or just an array of values.
 */
function artworker_get_artworks( $args ) {
	$query = new Artworker_Artwork_Query( $args );
	return $query->get_artworks();
}