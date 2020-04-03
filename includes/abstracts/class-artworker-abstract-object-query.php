<?php

/**
 * The Abstract Artworker Object Query class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The abstract artworker object query query class.
 *
 * This class builds the artworker object query.
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
 * Abstract object query class.
 */
abstract class Artworker_Object_Query {

	/**
	 * Stores query data.
	 *
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Create a new query.
	 *
	 * @param array $args Criteria to query on in a format similar to WP_Query.
	 */
	public function __construct( $args = array() ) {
		$this->query_vars = wp_parse_args( $args, $this->get_default_query_vars() );
	}

	/**
	 * Get the current query vars.
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return $this->query_vars;
	}

	/**
	 * Get the value of a query variable.
	 *
	 * @param string $query_var Query variable to get value for.
	 * @param mixed  $default Default value if query variable is not set.
	 * @return mixed Query variable value if set, otherwise default.
	 */
	public function get( $query_var, $default = '' ) {
		if ( isset( $this->query_vars[ $query_var ] ) ) {
			return $this->query_vars[ $query_var ];
		}
		return $default;
	}

	/**
	 * Set a query variable.
	 *
	 * @param string $query_var Query variable to set.
	 * @param mixed  $value Value to set for query variable.
	 */
	public function set( $query_var, $value ) {
		$this->query_vars[ $query_var ] = $value;
	}

	/**
	 * Get the default allowed query vars.
	 *
	 * @return array
	 */
	protected function get_default_query_vars() {

		return array(
			'name'           => '',
			'parent'         => '',
			'parent_exclude' => '',
			'exclude'        => '',

			'limit'          => get_option( 'posts_per_page' ),
			'page'           => 1,
			'offset'         => '',
			'paginate'       => false,

			'order'          => 'DESC',
			'orderby'        => 'date',

			'return'         => 'objects',
		);
	}
}
