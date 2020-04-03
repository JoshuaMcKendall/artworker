<?php 

/**
 * The Artworker Object Data Store Interface
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes/interfaces
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The artworker object data store interface.
 *
 * This class builds the artworker object data store interface.
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
 * Artworker Data Store Interface
 *
 * @version  1.0.0
 */
interface Artworker_Object_Data_Store_Interface {

	/**
	 * Method to read a record. Creates a new Artworker_Data based object.
	 *
	 * @param Artworker_Data $data Data object.
	 */
	public function read( &$data );

}
