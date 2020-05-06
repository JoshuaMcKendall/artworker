<?php
/**
 * Artwork Loop Start
 *
 * This template can be overridden by copying it to yourtheme/artworker/loop/loop-start.php.
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

<div id="artwork-gallery" class="artworker-artwork-gallery columns-<?php esc_attr_e( artworker_get_loop_prop( 'columns' ) ); ?> grid alignfull">