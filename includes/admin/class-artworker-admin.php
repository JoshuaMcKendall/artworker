<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes/admin/
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Artworker
 * @subpackage Artworker/admin
 * @author     Joshua McKendall <artworker@joshuamckendall.com>
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

class Artworker_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct( ) {

		/**
		 * Fires when Artworker admin has been initialized.
		 *
		 * @since 1.0.0
		 *
		 * @param Artworker_Admin object.
		 */
		do_action( 'artworker_admin_init', $this );

		$this->_includes();

		add_filter( 'display_post_states', array( $this, 'artworker_add_custom_post_states' ) );

	}

	private function _includes() {

		include ARTWORKER_INCLUDE_PATH . 'admin/artworker-admin-functions.php';
		include ARTWORKER_INCLUDE_PATH . 'admin/class-artworker-admin-settings.php';
		include ARTWORKER_INCLUDE_PATH . 'admin/class-artworker-admin-assets.php';

	}


	public function artworker_add_custom_post_states( $states ) {
	    global $post;

	    // get saved project page ID
	    $art_gallery_page_id = artworker_get_page_id( 'gallery' );

	    // add our custom state after the post title only,
	    // if post-type is "page",
	    // "$post->ID" matches the "$art_gallery_page_id",
	    // and "$art_gallery_page_id" is not "0"
	    if( 'page' == get_post_type( $post->ID ) && $post->ID == $art_gallery_page_id && $art_gallery_page_id != '0') {
	        $states[] = __('Art Gallery Page', 'artworker');
	    }

	    return $states;
	}


}

return new Artworker_Admin();