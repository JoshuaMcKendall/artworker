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
$height = $artwork->get_height();
$width = $artwork->get_width();

?>

<div id="<?php esc_attr_e( 'artwork-' . $artwork_id ); ?>" class="item artwork" style="width: <?php esc_attr_e( $width ); ?>; height: <?php esc_attr_e( $height ); ?>;">

	<?php do_action( 'artworker_before_gallery_thumb', $artwork_id ); ?>

    <a id="<?php esc_attr_e( 'artwork-' . $artwork_id . '-link' ); ?>" href="<?php echo esc_url( get_permalink( $artwork_id ) ); ?>" class="artwork-link">

        <img src="<?php echo esc_url( artworker_get_default_image() ); ?>" data-src="<?php echo esc_url( $artwork->get_src( 'large' ) );  ?>" data-full_image="<?php echo esc_url( $artwork->get_src() );  ?>" data-full_image_w="<?php esc_attr_e( $width ); ?>" data-full_image_h="<?php esc_attr_e( $height ); ?>" data-title="<?php esc_attr_e( get_the_title() ); ?>" class="responsive-image pswp-image artwork-image lazy">    

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