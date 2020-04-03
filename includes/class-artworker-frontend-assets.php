<?php

/**
 * The Artworker Frontend Assets class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The Artworker Frontend Assets class
 *
 * This class loads the front-end assets for the plugin.
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

class Artworker_Frontend_Assets extends Artworker_Assets {

	/**
	 * Register scripts
	 * @since 1.4.1.4
	 */
	public static function init() {
		add_action( 'artworker_before_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	public static function get_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		return apply_filters(
			'artworker_enqueue_scripts',
			array(
				'jquery-unveil'	=> array(

					'src'		=> ARTWORKER_ASSET_URI . 'js/public/jquery.unveil'. $suffix .'.js',
					'deps'		=> array( 'jquery' ),
					'version'	=> '1.0.0',
					'in_footer'	=> false,
					'register'	=> true
				),
				'jquery-flex-images'	=> array(

					'src'		=> ARTWORKER_ASSET_URI . 'js/public/jquery.flex-images'. $suffix .'.js',
					'deps'		=> array( 'jquery', 'jquery-unveil' ),
					'version'	=> '1.0.4',
					'in_footer'	=> false,
					'register'	=> true
				),
				'photoswipe'                 => array(

					'src'     => ARTWORKER_ASSET_URI . 'js/public/photoswipe' . $suffix . '.js',
					'deps'    => array(),
					'version' => '4.1.3',
					'in_footer'	=> false,
					'register'	=> true
				),
				'photoswipe-ui-default'      => array(

					'src'     => ARTWORKER_ASSET_URI . 'js/public/photoswipe-ui-default' . $suffix . '.js',
					'deps'    => array( 'photoswipe' ),
					'version' => '4.1.3',
					'in_footer'	=> false,
					'register'	=> true
				),
				'artworker'	=> array(

					'src'		=> ARTWORKER_ASSET_URI . 'js/public/artworker'. $suffix .'.js',
					'deps'		=> array( 'jquery-unveil', 'photoswipe', 'jquery-flex-images' ),
					'version'	=> ARTWORKER_VERSION,
					'in_footer'	=> false,
					'register'	=> true
				),
			)
		);
	}

	public static function get_styles() {
		return apply_filters(
			'artworker_enqueue_styles',
			array(
				'artworker'	=> array(
					'src'		=> ARTWORKER_ASSET_URI . 'css/public/artworker/style.css',
					'deps'		=> array(),
					'version'	=> ARTWORKER_VERSION,
					'has_rtl'	=> false,
					'register'	=> true,

				)
			)
		);
	}

	/**
	 * Register scripts
	 *
	 * @param type $hook
	 */
	public static function register_assets( $hook ) {

		$scripts 	= self::get_scripts();
		$styles 	= self::get_styles();

		foreach ( $scripts as $name => $props ) {
			parent::register_script( $name, $props['src'], $props['deps'], $props['version'], $props['register'] );
			parent::localize_script( $name, 'Artworker_Data', self::get_script_data( $name ) );
		}

		foreach ( $styles as $name => $props ) {
			parent::register_style( $name, $props['src'], $props['deps'], $props['version'], $props['has_rtl'], $props['register'] );
		}

	}

	/**
	 * Return data for script handles.
	 *
	 * @param  string $handle Script handle the data will be attached to.
	 * @return array|bool
	 */
	private static function get_script_data( $handle ) {

		switch ( $handle ) {
			case 'artworker':
				$params = array(
					'ajax_url'    		=> Artworker()->ajax_url(),
					'posts_per_page'	=> get_option( 'artwork_count' ),
					'default_image'		=> artworker_get_default_image()

				);
				break;
			case 'artworker_art_gallery':
				$params = array(
					'photoswipe_options'        => apply_filters(
						'artworker_art_gallery_photoswipe_options',
						array(
							'shareEl'               => false,
							'closeOnScroll'         => false,
							'history'               => false,
							'hideAnimationDuration' => 0,
							'showAnimationDuration' => 0,
						)
					),
				);
				break;
			
			default:
				$params = array(
					'ajax_url'    => Artworker()->ajax_url(),
				);
		}

		return apply_filters( 'artworker_get_script_data', $params, $handle );

	}

}

Artworker_Frontend_Assets::init();
