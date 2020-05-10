<?php
/**
 * Artworker Ajax class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * This class defines all code necessary to handle AJAX requests.
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

/**
 * Ajax Process
 */
class Artworker_Ajax {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		self::add_ajax_events();
	}

	public static function add_ajax_events() {

		$actions = apply_filters( 'artworker_ajax_actions', array(

			'get_artworks' => array( 

				'nopriv'			=> false,
				'callback'			=> array( __CLASS__, 'get_artworks' )

			)

		) );

		foreach ( $actions as $slug => $action ) {

			if( ! array_key_exists( 'nopriv', $action ) )
				continue;

			if( ! is_bool( $action['nopriv'] ) )
				continue;

			if( ! array_key_exists( 'callback', $action ) )
				continue;

			if( ! is_callable( $action['callback'] ) )
				continue;

			add_action( 'wp_ajax_' . $slug, $action['callback'] );

			if ( $action['nopriv'] ) {

				add_action( 'wp_ajax_nopriv_' . $slug, array( __CLASS__, 'must_login' ) );

			} else {

				add_action( 'wp_ajax_nopriv_' . $slug, $action['callback'] );			

			}

		}

	}

	public static function get_artworks() {

		if( ! wp_doing_ajax() )
			return;

		$html = '';
		$items = array();
		$status = 'error';
		$message = __( 'No artwork', 'artworker' );
		$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$query_args = array(
			'post_type'			=> 'artwork',
			'orderby'			=> 'date',
			'order'				=> 'DESC',
			'post_status'		=> 'publish',
			'posts_per_page' 	=> get_option( 'artwork_count' ), 
			'paged'				=> $paged, 
			'meta'				=> array(
				array(
					'key' 		=> '_thumbnail_id',
					'compare' 	=> 'EXISTS'
				)
			)
			
		);
		
		$query = new WP_Query( $query_args );

		if( $query->have_posts() ) {

			while ( $query->have_posts() ) {
				$query->the_post();

				global $artwork;

				$items[] = array(
					'title'	=> get_the_title(),
					'src'	=> $artwork->get_src(),
					'w'		=> $artwork->get_width(),
					'h'		=> $artwork->get_height()
				);

				ob_start();

				artworker_get_template_part( 'content', 'artwork' );

				$html .= ob_get_clean();
			}

			if( ! empty( $html ) ) {
				$message = '';
				$status = 'success';
			}
				

		}

		$json = array(

			'html'		=> $html,
			'items'		=> $items,
			'status'	=> $status,
			'message'	=> $message

		);


		wp_send_json( $json );

		die();

	}

	// ajax nopriv: user is not signin
	public static function must_login() {
		wp_send_json( array(
			'status'  => false,
			'message' => sprintf( __( 'You Must <a href="%s">Login</a>', 'artworker' ), wp_login_url() )
		) );
		die();
	}

}

// initialize ajax class process
Artworker_Ajax::init();
