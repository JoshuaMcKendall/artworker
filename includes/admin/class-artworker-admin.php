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

		add_filter( 'manage_artwork_posts_columns', array( $this, 'set_artwork_image_column' ) );
		add_action( 'manage_artwork_posts_custom_column' , array( $this, 'render_artwork_column' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'style_artwork_column' ) );

		add_filter( 'display_post_states', array( $this, 'artworker_add_custom_post_states' ) );

	}

	private function _includes() {

		include ARTWORKER_INCLUDE_PATH . 'admin/artworker-admin-functions.php';
		include ARTWORKER_INCLUDE_PATH . 'admin/class-artworker-admin-settings.php';
		include ARTWORKER_INCLUDE_PATH . 'admin/class-artworker-admin-assets.php';

	}

	public function style_artwork_column() {
		echo '<style type="text/css">';
		echo '.wp-list-table .column-artwork {
			  	width: 100px;
			  }
			  a.artwork.artwork-image {
				display: block;
			    position: relative;
			    z-index: 999;
			    background: #eee;
			    width: 90px;
			    height: 90px;
			  }
			  a.artwork.artwork-image::before {
			    font-family: "dashicons";
			    content: "\f309";
			    display: inline-flex;
			    width: 90px;
			    height: 90px;
			    top: 0;
			    left: 0;
			    position: absolute;
			    z-index: -99;
			    text-align: center;
			    justify-content: center;
			    align-items: center;
			    color: #ccc;
			    font-size: 30px;
			  }
			  ';
		echo '</style>';		
	}

	public function set_artwork_image_column( $columns ) {

	    $columns = array(
			'cb' => $columns['cb'],
			'artwork' => __( 'Artwork', 'artworker' ),
			'title' => $columns['title'],
			'author' => $columns['author'],
			'taxonomy-artwork_cat' => $columns['taxonomy-artwork_cat'],
			'taxonomy-artwork_tag' => $columns['taxonomy-artwork_tag'],
			'comments' => $columns['comments'],
			'date' => $columns['date']
	    );

	    return $columns;		
	}

	public function render_artwork_column( $column, $post_id ) {

		if( $column != 'artwork' )
			return $column;

		$artwork_image_id = get_post_thumbnail_id( $post_id );
		$artwork_data = wp_get_attachment_image_src( $artwork_image_id, 'thumbnail' );
		$artwork = artworker_get_default_image();
		$classes = 'artwork artwork-image';


		if( $artwork_data )
			$artwork = $artwork_data[0];

		$image = '<img src="'. esc_url( $artwork ) .'" alt="Art thumbnail" title="" width="90" height="90" />';

		echo '<a href="'. esc_url( get_edit_post_link( $post_id ) ) .'" class="'. esc_attr( $classes ) .'" >' . $image . '</a>';

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