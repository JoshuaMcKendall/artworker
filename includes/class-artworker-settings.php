<?php
/**
 * Artworker Settings class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * This class handles all the plugin settings.
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

class Artworker_Settings { 

	/**
	 * $_options
	 * @var null
	 */
	public $_options = null;

	/**
	 * prefix option name
	 * @var string
	 */
	public $_prefix = 'artworker';

	/**
	 * _instance
	 * @var null
	 */
	static $_instance = null;

	public function __construct( $prefix = null ) {
		if ( $prefix )
			$this->_prefix = $prefix;

		// load options
		if ( !$this->_options )
			$this->_options = $this->options();
	}

	public function __get( $id = null ) {
		$settings = apply_filters( 'artworker_settings_field', array() );

		if ( isset( $settings[$id] ) ) {
			return $settings[$id];
		}
	}

	/**
	 * options load options
	 * @return array || null
	 */
	protected function options() {

		if ( is_array( get_option( $this->_prefix, null ) ) ) {
			return call_user_func_array( 'array_merge', get_option( $this->_prefix, null ) );
		}

		return get_option( $this->_prefix, null );
	}

	/**
	 * get_name_field
	 *
	 * @param  $name of field option
	 *
	 * @return string name field
	 */
	public function get_field_name( $name = null ) {
		if ( !$this->_prefix || !$name )
			return;

		return $this->_prefix . '[' . $name . ']';
	}

	/**
	 * get_name_field
	 *
	 * @param  $name of field option
	 *
	 * @return string name field
	 */
	public function get_field_id( $name = null, $default = null ) {
		if ( !$this->_prefix || !$name )
			return;

		return $this->_prefix . '_' . $name;
	}

	/**
	 * get option value
	 *
	 * @param  $name
	 *
	 * @return option value. array, string, boolean
	 */
	public function get( $name = null, $default = null ) {
		if ( !$this->_options )
			$this->_options = $this->options();

		if ( $name && isset( $this->_options[$name] ) )
			return $this->_options[$name];

		return $default;
	}

	/**
	 * get option value
	 *
	 * @param  $name
	 *
	 * @return option value. array, string, boolean
	 */
	public function set( $name = null, $default = null ) {
		if ( !$this->_options )
			$this->_options = $this->options();

		if ( $name && isset( $this->_options[$name] ) )
			return $this->_options[$name];

		return $default;
	}

	/**
	 * instance
	 *
	 * @param  $prefix
	 *
	 * @return object class
	 */
	public static function instance( $prefix = null ) {

		if ( !empty( self::$_instance[$prefix] ) ) {

			return $GLOBALS['artworker_settings'] = self::$_instance[$prefix];
		}

		return $GLOBALS['artworker_settings'] = self::$_instance[$prefix] = new self( $prefix );
	}

}
