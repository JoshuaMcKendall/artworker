<?php
/**
 * The template for displaying artwork content within loops
 *
 * This template can be overridden by copying it to yourtheme/artworker/content-artwork.php.
 *
 * @author  Joshua McKendall
 * @package Artworker/Templates
 * @version 1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

global $artwork;

// Ensure visibility.
if ( empty( $artwork ) ) {
	return;
}

$artwork_id = $artwork->get_id();
$size = 'large';

?>

<div id="<?php esc_attr_e( 'artwork-' . $artwork_id ); ?>" class="item artwork" data-w="<?php esc_attr_e( $artwork->get_width( $size ) ); ?>" data-h="<?php esc_attr_e( $artwork->get_height( $size ) ); ?>" data-artwork="<?php esc_attr_e( $artwork->get_data( 'json' ) ); ?>">

	<?php do_action( 'artworker_before_gallery_thumb', $artwork_id ); ?>

    <a id="<?php esc_attr_e( 'artwork-' . $artwork_id . '-link' ); ?>-link" href="<?php echo esc_url( get_permalink( $artwork_id ) ); ?>" class="artwork-link">

        <img src="<?php echo esc_url( artworker_get_default_image() ); ?>" data-src="<?php echo esc_url( $artwork->get_src( $size ) );  ?>" data-large_image="<?php echo esc_url( $artwork->get_src( 'full' ) );  ?>" data-large_image_width="<?php esc_attr_e( $artwork->get_width( 'full' ) ); ?>" 
        data-large_image_height="<?php esc_attr_e( $artwork->get_height( 'full' ) ); ?>" class="responsive-image pswp-image artwork-image lazy">    

    </a>

	<?php do_action( 'artworker_after_gallery_thumb', $artwork_id ); ?>

</div>

<noscript id="artwork-<?php echo esc_attr( $artwork_id ); ?>-noscript" <?php artworker_artwork_class( 'cell noscript', $artwork ); ?>> 

    <?php do_action( 'artworker_before_gallery_thumb_noscript', $artwork_id ); ?>

    <a href="<?php the_permalink(); ?>" class="artwork-link">

        <img src="<?php echo esc_url( $artwork->get_src( 'large' ) );  ?>" class="responsive-image">

    </a>

    <?php do_action( 'artworker_after_gallery_thumb_noscript', $artwork_id ); ?>

</noscript>