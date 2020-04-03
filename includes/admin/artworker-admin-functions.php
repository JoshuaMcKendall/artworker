<?php
/**
 * Artworker Admin Functions
 *
 * @package  Artworker/Admin/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all Artworker screen ids.
 *
 * @return array
 */
function artworker_get_screen_ids() {

	$artworker_screen_id = sanitize_title( __( 'Artworker', 'artworker' ) );
	$screen_ids   = array(
		'artwork'
	);

	return apply_filters( 'artworker_screen_ids', $screen_ids );
}