<?php

/**
 * Artworker Template Hooks
 *
 * @link       https://github.com/JoshuaMcKendall/Illustrator-Plugin/includes/
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * This file sets up all the template hooks.
 *
 * @since      1.0.0
 * @package    Artworker
 * @subpackage Artworker/includes
 * @author     Joshua McKendall <artworker@joshuamckendall.com>
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

add_action( 'artworker_after_gallery_loop', 'artworker_pagination', 10 );

add_filter( 'body_class', 'artworker_add_body_class', 10, 2 );