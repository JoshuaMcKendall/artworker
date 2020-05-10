<?php

/**
 * The main Artworker class that sets everything up.
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The main Artworker class.
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
 * Main Artworker Class.
 *
 * @class Artworker
 */
final class Artworker {

	/**
	 * Artworker version.
	 *
	 * @var string
	 */
	public $version = '1.0.1';

	/**
	 * The single instance of the class.
	 *
	 * @var Artworker
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Artworker Session instance.
	 *
	 * @var Artworker_Session
	 * @since 1.0.0
	 */
	public $session = null;

	/**
	 * Artworker Settings instance.
	 *
	 * @var Artworker_Settings
	 * @since 1.0.0
	 */
	public $settings = null;

	/**
	 * Query instance.
	 *
	 * @var Artworker_Query
	 */
	public $query = null;

	/**
	 * Artwork factory instance.
	 *
	 * @var Artworker_Artwork_Factory
	 */
	public $artwork_factory = null;

	/**
	 * Main Artworker Instance.
	 *
	 * Ensures only one instance of Artworker is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Artworker()
	 * @return Artworker - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'artworker' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'artworker' ), '1.0.0' );
	}

	/**
	 * Artworker Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * When WP has loaded all plugins, trigger the `artworker_loaded` hook.
	 *
	 * This ensures `artworker_loaded` is called only after all other plugins
	 * are loaded.
	 *
	 * @since 1.0.0
	 */
	public function on_plugins_loaded() {
		do_action( 'artworker_loaded' );		
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}		
	}

	/**
	 * Define Artworker Constants.
	 */
	private function define_constants() {

		$this->define( 'ARTWORKER_ABSPATH', dirname( ARTWORKER_PLUGIN_FILE ) . '/' );
		$this->define( 'ARTWORKER_URI', plugin_dir_url( ARTWORKER_PLUGIN_FILE ) );
		$this->define( 'ARTWORKER_VERSION', $this->version );
		$this->define( 'ARTWORKER_PLUGIN_BASENAME', plugin_basename( ARTWORKER_PLUGIN_FILE ) );
		$this->define( 'ARTWORKER_TEMPLATE_DEBUG_MODE', false );
		$this->define( 'ARTWORKER_INCLUDE_PATH', ARTWORKER_ABSPATH . 'includes/' );
		$this->define( 'ARTWORKER_ASSET_PATH', ARTWORKER_ABSPATH . 'assets/' );
		$this->define( 'ARTWORKER_TEMPLATE_PATH', ARTWORKER_ABSPATH . 'templates/' );
		$this->define( 'ARTWORKER_INCLUDE_URI', ARTWORKER_URI . 'includes/' );
		$this->define( 'ARTWORKER_ASSET_URI', ARTWORKER_URI . 'assets/' );
		$this->define( 'ARTWORKER_TEMPLATE_URI', ARTWORKER_URI . 'templates/' );

	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		/**
		 * Interfaces.
		 */
		include_once ARTWORKER_INCLUDE_PATH . 'interfaces/class-artworker-object-data-store-interface.php';

		/**
		 * Include abstract files.
		 */
		include_once ARTWORKER_INCLUDE_PATH . 'abstracts/class-artworker-abstract-dynamic-block.php';
		include_once ARTWORKER_INCLUDE_PATH . 'abstracts/class-artworker-abstract-data.php';
		include_once ARTWORKER_INCLUDE_PATH . 'abstracts/class-artworker-abstract-object-query.php';

		/**
		 * Include core files.
		 */
		include_once ARTWORKER_INCLUDE_PATH . 'artworker-core-functions.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-post-types.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-assets.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-ajax.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-settings.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-session.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-query.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-artwork.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-artwork-query.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-artwork-block.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-artwork-factory.php';
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-template-loader.php';

		/**
		 * Include data stores.
		 */
		include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-data-store.php';
		include_once ARTWORKER_INCLUDE_PATH . 'data-stores/class-artworker-data-store-wp.php';
		include_once ARTWORKER_INCLUDE_PATH . 'data-stores/class-artworker-artwork-data-store-cpt.php';

		if( $this->is_request( 'admin' ) ) {
			include_once ARTWORKER_INCLUDE_PATH . 'admin/class-artworker-admin.php';
		}

		if( $this->is_request( 'frontend' ) ) {
			include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-frontend-assets.php';
			include_once ARTWORKER_INCLUDE_PATH . 'class-artworker-request-handler.php';
			include_once ARTWORKER_INCLUDE_PATH . 'artworker-template-hooks.php';
		}

		$this->query 		= new Artworker_Query();

	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( ARTWORKER_PLUGIN_FILE ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'artworker_template_path', 'artworker/' );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * Init Artworker when WordPress Initialises.
	 */
	public function init() {
		// Before init action.
		do_action( 'before_artworker_init' );

		// Load class instances.
		$this->settings 		= new Artworker_Settings();
		$this->session 			= new Artworker_Session();
		$this->artwork_factory  = new Artworker_Artwork_Factory();

		// Init action.
		do_action( 'artworker_init' );
	}

}