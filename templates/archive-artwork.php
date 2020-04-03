<?php

/**
 * Template for displaying artwork archive.
 *
 * This template can be overridden by copying it to yourtheme/artworker/archive-artwork.php
 *
 * @author  Joshua McKendall
 * @package Artworker/Templates
 * @version 1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

get_header();

/**
 * Hook: artworker_before_main_content.
 */
do_action( 'artworker_before_main_content' );

?>

<main id="site-content" role="main" class="artworker-artwork-archive-page">

<?php

if ( artworker_artwork_loop() ) {

	/**
	 * Hook: artworker_before_gallery_loop.
	 *
	 */
	do_action( 'artworker_before_gallery_loop' );

	artworker_artwork_loop_start();

	if ( artworker_get_loop_prop( 'total' ) ) {

		while ( have_posts() ) {

			the_post();

			/**
			 * Hook: artworker_gallery_loop.
			 */
			do_action( 'artworker_gallery_loop' );

			artworker_get_template_part( 'content', 'artwork' );

		}

	}

	artworker_artwork_loop_end();

	/**
	 * Hook: artworker_after_gallery_loop.
	 *
	 * @hooked artworker_pagination - 10
	 */
	do_action( 'artworker_after_gallery_loop' );

} else {

	/**
	 * Hook: artworker_no_artwork_found.
	 *
	 * @hooked artworker_no_artwork_found - 10
	 */
	do_action( 'artworker_no_artwork_found' );

}

?>

</main>

<?php
/**
 * Hook: artworker_after_main_content.
 */
do_action( 'artworker_after_main_content' );

get_footer();