<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Artworker
 * @subpackage Artworker/includes
 * @author     Joshua McKendall <artworker@joshuamckendall.com>
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

/**
 * Artworker_Assets class
 */
class Artworker_Assets {

	/**
	 * styles
	 * @var type array
	 */
	private static $_styles = array();

	/**
	 * scripts
	 * @var type array
	 */
	private static $_scripts = array();

	/**
	 * localize
	 * @var type array
	 */
	private static $_localize_scripts = array();

	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * register script
	 */
	protected static function register_script( $handle = '', $src = '', $deps = array( 'jquery' ), $ver = false, $register = true, $in_footer = true ) {

		if( ! $register )
			return;

		self::$_scripts[$handle] = array( $handle, self::_get_file_uri( $src ), $deps, $ver, $in_footer );
	}

	/**
	 * register style
	 */
	protected static function register_style( $handle, $src, $deps = array(), $ver = false, $media = 'all', $has_rtl = false, $register = true ) {

		if( ! $register )
			return;

		self::$_styles[$handle] = array( $handle, self::_get_file_uri( $src ), $deps, $ver, $media );

		if( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 * localize_script scripts
	 *
	 * @param type $handle
	 * @param type $name
	 * @param type $data
	 */
	protected static function localize_script( $handle, $name, $data ) {
		self::$_localize_scripts[$handle] = array( $handle, $name, $data );
	}

	/**
	 * frontend enqueue scripts
	 */
	public static function enqueue_scripts( $hook ) {
		/**
		 * Before enqueue scripts
		 */
		do_action( 'artworker_before_enqueue_scripts', $hook );

		wp_enqueue_script( 'jquery' );

		if ( self::$_scripts ) {
			foreach ( self::$_scripts as $handle => $param ) {
				call_user_func_array( 'wp_register_script', $param );
				if ( array_key_exists( $handle, self::$_localize_scripts ) ) {
					call_user_func_array( 'wp_localize_script', self::$_localize_scripts[$handle] );
				}
				wp_enqueue_script( $handle );
			}
		}

		if ( self::$_styles ) {
			foreach ( self::$_styles as $handle => $param ) {
				call_user_func_array( 'wp_register_style', $param );
				wp_enqueue_style( $handle );
			}
		}

		/**
		 * After enqueue scripts
		 */
		do_action( 'artworker_after_enqueue_scripts', $hook );
	}

	/**
	 * Get file uri.
	 * if WP_DEBUG is FALSE will load minify file
	 */
	private static function _get_file_uri( $uri = '' ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			return $uri;
		}
		$file      = self::_get_path_by_uri( $uri );
		$file_name = basename( $file );
		$parse     = explode( '.', $file_name );
		/**
		 * file's extension '.js' or '.css'
		 */
		$file_type = end( $parse );
		if ( in_array( 'min', $parse ) ) {
			return $uri;
		}

		array_pop( $parse );
		$parse[]  = 'min';
		$parse[]  = $file_type;
		$new_file = implode( '.', $parse );

		$new_uri  = str_replace( $file_name, $new_file, $uri );
		$new_path = self::_get_path_by_uri( $new_uri );
		if ( file_exists( $new_path ) ) {
			return $new_uri;
		}
		return $uri;
	}

	/**
	 * get file path by uri
	 *
	 * @param type $uri
	 */
	public static function _get_path_by_uri( $uri = '' ) {
		$base_url = trailingslashit( ARTWORKER_URI );
		$path     = trailingslashit( ARTWORKER_ABSPATH );

		/**
		 * file path
		 */
		return str_replace( $base_url, $path, $uri );
	}

}


Artworker_Assets::init();
