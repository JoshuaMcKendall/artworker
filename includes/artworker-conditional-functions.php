<?php

/**
 * is_artworker - Returns true if on a page which uses Artworker templates (gallery).
 *
 * @return bool
 */
function is_artworker() {
	return apply_filters( 'is_artworker', is_gallery() || is_artwork_taxonomy() || is_artwork() );
}

if ( ! function_exists( 'is_gallery' ) ) {

	/**
	 * is_gallery - Returns true when viewing the artwork type archive (gallery).
	 *
	 * @return bool
	 */
	function is_gallery() {
		return ( is_post_type_archive( 'artwork' ) || is_page( artworker_get_page_id( 'gallery' ) ) );
	}
}

if ( ! function_exists( 'is_artwork_taxonomy' ) ) {

	/**
	 * is_artwork_taxonomy - Returns true when viewing a artwork taxonomy archive.
	 *
	 * @return bool
	 */
	function is_artwork_taxonomy() {
		return is_tax( get_object_taxonomies( 'artwork' ) );
	}
}

if ( ! function_exists( 'is_artwork_category' ) ) {

	/**
	 * Is_artwork_category - Returns true when viewing an artwork category.
	 *
	 * @param  string $term (default: '') The term slug your checking for. Leave blank to return true on any.
	 * @return bool
	 */
	function is_artwork_category( $term = '' ) {
		return is_tax( 'artwork_cat', $term );
	}
}

if ( ! function_exists( 'is_artwork_tag' ) ) {

	/**
	 * Is_artwork_tag - Returns true when viewing a artwork tag.
	 *
	 * @param  string $term (default: '') The term slug your checking for. Leave blank to return true on any.
	 * @return bool
	 */
	function is_artwork_tag( $term = '' ) {
		return is_tax( 'artwork_tag', $term );
	}
}

if ( ! function_exists( 'is_artwork' ) ) {

	/**
	 * Is_artwork - Returns true when viewing a single artwork.
	 *
	 * @return bool
	 */
	function is_artwork() {
		return is_singular( array( 'artwork' ) );
	}
}

if ( ! function_exists( 'is_artwork_search' ) ) {

	/**
	 * is_artwork_search - Returns true when searching for artwork.
	 *
	 * @return bool
	 */
	function is_artwork_search() {

		return array_key_exists( 's', $_REQUEST ) && array_key_exists( 'ref', $_REQUEST ) && $_REQUEST['ref'] == 'artwork';

	}

}