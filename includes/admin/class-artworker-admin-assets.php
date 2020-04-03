<?php

/**
 * The Artworker Admin Assets class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes/admin/
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The Artworker Admin Assets class
 *
 * This class loads the admin assets for Artworker.
 *
 * @since      1.0.0
 * @package    Artworker
 * @subpackage Artworker/includes
 * @author     Joshua McKendall <artworker@joshuamckendall.com>
 */

defined( 'ABSPATH' ) || exit;

class Artworker_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
	}

	/**
	 * Admin styles
	 *
	 */
	public static function admin_styles() {
		
	}

	/**
	 * Admin scripts
	 *
	 */
	public static function admin_scripts() {
		global $wp_query, $pagenow, $post;

		$suffix 	  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';


	}

}

return new Artworker_Admin_Assets();