<?php
/**
 * The Artworker Admin Settings class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes/admin/
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The Artworker Admin Settings class
 *
 * This class loads the admin settings for Artworker.
 *
 * @since      1.0.0
 * @package    Artworker
 * @subpackage Artworker/includes
 * @author     Joshua McKendall <artworker@joshuamckendall.com>
 */

defined( 'ABSPATH' ) || exit;

class Artworker_Admin_Settings {

	private static $messages = array();

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function register_settings() {

	   // register our setting
	    register_setting( 
	        'reading', // option group "reading", default WP group
	        'artwork_archive_page', // option name
	        array(
		        'type' => 'string', 
		        'sanitize_callback' => 'sanitize_text_field',
		        'default' => NULL,
	    	) 
	    );

	    register_setting( 
	        'reading', // option group "reading", default WP group
	        'artwork_count', // option name
			array(
		        'type' => 'number', 
		        'sanitize_callback' => 'sanitize_text_field',
		        'default' => NULL,
	    	)
	    );

	    // add our new setting
	    add_settings_field(
	        'artwork_archive_page', // ID
	        __('Art Gallery Page', 'artworker'), // Title
	        array( __CLASS__, 'artworker_archive_page_setting_callback_function' ), // Callback
	        'reading', // page
	        'default', // section
	        array( 'label_for' => 'artwork_archive_page' )
	    );	

	    add_settings_field(
	        'artwork_count', // ID
	        __('Gallery pages show at most', 'artworker'), // Title
	        array( __CLASS__, 'artworker_artworks_count_setting_callback_function' ), // Callback
	        'reading', // page
	        'default', // section
	        array( 'label_for' => 'artwork_count' )
	    );		

	}

	public static function artworker_archive_page_setting_callback_function( $args ){
	    // get saved project page ID
	    $artworks_page_id = get_option('artwork_archive_page');

	    // get all pages
	    $args = array(
	        'posts_per_page'   => -1,
	        'orderby'          => 'name',
	        'order'            => 'ASC',
	        'post_type'        => 'page',
	    );
	    $items = get_posts( $args );

	    echo '<select id="artwork_archive_page" name="artwork_archive_page">';
	    // empty option as default
	    echo '<option value="0">'.__('— Select —', 'wordpress').'</option>';

	    // foreach page we create an option element, with the post-ID as value
	    foreach($items as $item) {

	        // add selected to the option if value is the same as $project_page_id
	        $selected = ($artworks_page_id == $item->ID) ? 'selected="selected"' : '';

	        echo '<option value="'.$item->ID.'" '.$selected.'>'.$item->post_title.'</option>';
	    }

	    echo '</select>';
	}


	public static function artworker_artworks_count_setting_callback_function( $args ) {

		$artwork_count = get_option('artwork_count');
	    $artwork_count = ( isset( $artwork_count ) && ! empty( $artwork_count ) ) ? esc_attr( $artwork_count ) : 24;

	    echo '<input type="number" min="1" max="999" id="artwork_count" name="artwork_count" value="'. $artwork_count .'"> items	';	

	}

}

Artworker_Admin_Settings::init();
