<?php

/**
 * @link              https://joshuamckendall.github.io/artworker
 * @since             1.0.0
 * @package           Artworker
 *
 * @wordpress-plugin
 * Plugin Name:       Artworker
 * Plugin URI:        https://joshuamckendall.github.io/artworker
 * Description:       Adds an art post type and art gallery to WordPress.
 * Version:           1.0.0
 * Author:            Joshua McKendall
 * Author URI:        https://joshuamckendall.github.io/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       artworker
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ARTWORKER_PLUGIN_FILE' ) ) {
	define( 'ARTWORKER_PLUGIN_FILE', __FILE__ );
}

// Include the main Artworker class.
if ( ! class_exists( 'Artworker', false ) ) {
	include_once dirname( ARTWORKER_PLUGIN_FILE ) . '/includes/class-artworker.php';
}

/**
 * Returns the main instance of Artworker.
 *
 * @since  1.0.0
 * @return Artworker
 */
function Artworker() {
	return Artworker::instance();
}

$_GLOBALS['artworker'] = Artworker();