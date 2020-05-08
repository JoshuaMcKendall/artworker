<?php

/**
 * Template for displaying single artwork block.
 *
 * This template can be overridden by copying it to yourtheme/artworker/artwork-block.php
 *
 * @author  Joshua McKendall
 * @package Artworker/Templates
 * @version 1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

?>

<?php do_action( 'artworker_before_artwork', $artwork_id ); ?>

<figure id="<?php esc_attr_e( $artwork_identifier ); ?>" class="<?php esc_attr_e( $classes ); ?>" >

	<img src="<?php echo esc_url( artworker_get_default_image() ); ?>" data-src="<?php echo esc_url( $artwork->get_src( 'large' ) );  ?>" data-full_image="<?php echo esc_url( $artwork->get_src() );  ?>" data-full_image_w="<?php esc_attr_e( $width ); ?>" data-full_image_h="<?php esc_attr_e( $height ); ?>" data-id="<?php esc_attr_e( $artwork_id ); ?>" data-title="<?php esc_attr_e( get_the_title() ); ?>" class="responsive-image pswp-image artwork-image lazy artwork-block-image"> 

	<noscript class="cell noscript"> 

	    <?php do_action( 'artworker_before_artwork_noscript', $artwork_id ); ?>

 		<img src="<?php echo esc_url( $artwork->get_src() );  ?>" class="artwork-block-noscript-image">

	    <?php do_action( 'artworker_after_artwork_noscript', $artwork_id ); ?>

	</noscript>  

	<figcaption class="caption">
		
		<?php echo $caption; ?>

	</figcaption>

</figure>

<?php do_action( 'artworker_after_artwork', $artwork_id ); ?>