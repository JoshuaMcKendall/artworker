<?php

require ARTWORKER_ABSPATH . 'includes/artworker-conditional-functions.php';
require ARTWORKER_ABSPATH . 'includes/artworker-template-functions.php';
require ARTWORKER_ABSPATH . 'includes/artworker-artwork-functions.php';

/**
 * Define a constant if it is not already defined.
 *
 * @since 1.0.0
 * @param string $name  Constant name.
 * @param mixed  $value Value.
 */
function artworker_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Return "theme support" values from the current theme, if set.
 *
 * @since  3.3.0
 * @param  string $prop Name of prop (or key::subkey for arrays of props) if you want a specific value. Leave blank to get all props as an array.
 * @param  mixed  $default Optional value to return if the theme does not declare support for a prop.
 * @return mixed  Value of prop(s).
 */
function artworker_get_theme_support( $prop = '', $default = null ) {
	$theme_support = get_theme_support( 'artworker' );
	$theme_support = is_array( $theme_support ) ? $theme_support[0] : false;

	if ( ! $theme_support ) {
		return $default;
	}

	if ( $prop ) {
		$prop_stack = explode( '::', $prop );
		$prop_key   = array_shift( $prop_stack );

		if ( isset( $theme_support[ $prop_key ] ) ) {
			$value = $theme_support[ $prop_key ];

			if ( count( $prop_stack ) ) {
				foreach ( $prop_stack as $prop_key ) {
					if ( is_array( $value ) && isset( $value[ $prop_key ] ) ) {
						$value = $value[ $prop_key ];
					} else {
						$value = $default;
						break;
					}
				}
			}
		} else {
			$value = $default;
		}

		return $value;
	}

	return $theme_support;
}

/**
 * Get permalink settings for things like artwork and taxonomies.
 *
 * As of 1.0.0, the permalink settings are stored to the option instead of
 * being blank and inheritting from the locale. This speeds up page loading
 * times by negating the need to switch locales on each page load.
 *
 * This is more inline with WP core behavior which does not localize slugs.
 *
 * @since  1.0.0
 * @return array
 */
function artworker_get_permalink_structure() {
	$saved_permalinks = (array) get_option( 'artworker_permalinks', array() );
	$permalinks       = wp_parse_args(
		array_filter( $saved_permalinks ),
		array(
			'artwork_base'           => _x( 'gallery', 'slug', 'artworker' ),
			'category_base'          => _x( 'artwork-category', 'slug', 'artworker' ),
			'tag_base'               => _x( 'artwork-tag', 'slug', 'artworker' ),
			'attribute_base'         => '',
			'use_verbose_page_rules' => false,
		)
	);

	if ( $saved_permalinks !== $permalinks ) {
		update_option( 'artworker_permalinks', $permalinks );
	}

	$permalinks['artwork_rewrite_slug']   = untrailingslashit( $permalinks['artwork_base'] );
	$permalinks['category_rewrite_slug']  = untrailingslashit( $permalinks['category_base'] );
	$permalinks['tag_rewrite_slug']       = untrailingslashit( $permalinks['tag_base'] );
	$permalinks['attribute_rewrite_slug'] = untrailingslashit( $permalinks['attribute_base'] );

	return $permalinks;
}

/**
 * Get page id from admin settings page
 *
 * @param string $name
 *
 * @return int
 */
if ( ! function_exists( 'artworker_get_page_id' ) ) {

	function artworker_get_page_id( $name ) {

		$page_id = 0;
		
		if( $name == 'gallery' ) {

			$page_id = get_option('artwork_archive_page');

		}

		if ( function_exists( 'icl_object_id' ) ) {
			$page_id = icl_object_id( $page_id, 'page', false, ICL_LANGUAGE_CODE );
		}

		return apply_filters( 'artworker_get_page_id', absint( $page_id ), $name );
	}

}

/**
 * Get static page for Artworker page by name.
 *
 * @param string $key
 *
 * @return string
 */
if ( ! function_exists( 'artworker_get_page_link' ) ) {

	function artworker_get_page_link( $name = 'gallery' ) {
		$page_id = artworker_get_page_id( $name );
		$link    = '';

		if ( get_post_status( $page_id ) == 'publish' ) {
			$permalink = trailingslashit( get_permalink( $page_id ) );
			$link      = $permalink;
		}

		return apply_filters( 'artworker_get_page_link', trailingslashit( $link ), $page_id );
	}

}


/**
 * Get current URL user is viewing.
 *
 * @return string
 */
if ( ! function_exists( 'artworker_get_current_url' ) ) {

	function artworker_get_current_url() {
		static $current_url;
		if ( ! $current_url ) {
			$url = untrailingslashit( $_SERVER['REQUEST_URI'] );
			if ( ! preg_match( '!^https?!', $url ) ) {
				$siteurl    = trailingslashit( get_home_url() /* SITE_URL */ );
				$home_query = '';

				if ( strpos( $siteurl, '?' ) !== false ) {
					$parts      = explode( '?', $siteurl );
					$home_query = $parts[1];
					$siteurl    = $parts[0];
				}

				if ( $home_query ) {
					parse_str( untrailingslashit( $home_query ), $home_query );
					$url = add_query_arg( $home_query, $url );
				}

				$segs1 = explode( '/', $siteurl );
				$segs2 = explode( '/', $url );

				if ( $removed = array_intersect( $segs1, $segs2 ) ) {
					if ( $segs2 = array_diff( $segs2, $removed ) ) {
						$current_url = $siteurl . join( '/', $segs2 );
						if ( strpos( $current_url, '?' ) === false ) {
							$current_url = trailingslashit( $current_url );
						}
					}
				}
			}
		}

		return $current_url;
	}

}

/**
 * Remove unneeded characters in an URL
 *
 * @param string $url
 * @param bool $trailingslashit
 *
 * @return string
 */
if ( ! function_exists( 'artworker_sanitize_url' ) ) {

	function artworker_sanitize_url( $url, $trailingslashit = true ) {
		if ( $url ) {
			preg_match( '!(https?://)?(.*)!', $url, $matches );
			$url_without_http = $matches[2];
			$url_without_http = preg_replace( '![/]+!', '/', $url_without_http );
			$url              = $matches[1] . $url_without_http;

			return ( $trailingslashit && strpos( $url, '?' ) === false ) ? trailingslashit( $url ) : untrailingslashit( $url );
		}

		return $url;
	}

}

/**
 * Compares an url with current URL user is viewing
 *
 * @param string $url
 *
 * @return bool
 */
if ( ! function_exists( 'artworker_is_current_url' ) ) {

	function artworker_is_current_url( $url ) {

		$current_url = artworker_get_current_url();

		return ( $current_url && $url ) && strcmp( $current_url, artworker_sanitize_url( $url ) ) == 0;

	}

}

if ( ! function_exists( 'artworker_get_template' ) ) {

	function artworker_get_template( $template_name, $args = array(), $load = false, $template_path = '', $default_path = '' ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}
  
		$located = artworker_locate_template( $template_name, $template_path, $default_path );

		if ( ! file_exists( $located ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '2.1' );
  
			return;
		}
		// Allow 3rd party plugin filter template file from their plugin
		$located = apply_filters( 'artworker_get_template', $located, $template_name, $args, $template_path, $default_path );

		do_action( 'artworker_before_template_part', $template_name, $template_path, $located, $args );

		if ( $load && '' != $located ) {

			include( $located );

		} else {

			return $located;
			
		}

		do_action( 'artworker_after_template_part', $template_name, $template_path, $located, $args );
	}

} 

if ( ! function_exists( 'artworker_template_path' ) ) {

	function artworker_template_path() {
		return apply_filters( 'artworker_template_path', 'artworker' );
	}

}

if ( ! function_exists( 'artworker_get_template_part' ) ) {

	function artworker_get_template_part( $slug, $name = '' ) {
		$cache_key = sanitize_key( implode( '-', array( 'template-part', $slug, $name, ARTWORKER_VERSION ) ) );
		$template  = (string) wp_cache_get( $cache_key, 'artworker' );

		if ( ! $template ) {
			if ( $name ) {
				$template = ARTWORKER_TEMPLATE_DEBUG_MODE ? '' : locate_template(
					array(
						"{$slug}-{$name}.php",
						Artworker()->template_path() . "{$slug}-{$name}.php",
					)
				);

				if ( ! $template ) {
					$fallback = Artworker()->plugin_path() . "/templates/{$slug}-{$name}.php";
					$template = file_exists( $fallback ) ? $fallback : '';
				}
			}

			if ( ! $template ) {
				// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/artworker/slug.php.
				$template = ARTWORKER_TEMPLATE_DEBUG_MODE ? '' : locate_template(
					array(
						"{$slug}.php",
						Artworker()->template_path() . "{$slug}.php",
					)
				);
			}

			wp_cache_set( $cache_key, $template, 'artworker' );
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'artworker_get_template_part', $template, $slug, $name );

		if ( $template ) {
			load_template( $template, false );
		}
	}

}

if ( ! function_exists( 'artworker_get_template_content' ) ) {
	function artworker_get_template_content( $template_name, $args = array(), $load = true, $template_path = '', $default_path = '' ) {
		ob_start();
		artworker_get_template( $template_name, $args, $load, $template_path, $default_path );

		return ob_get_clean();
	}
}

if ( ! function_exists( 'artworker_locate_template' ) ) {

	function artworker_locate_template( $template_name, $template_path = '', $default_path = '' ) {

		if ( ! $template_path ) {
			$template_path = artworker_template_path();
		}

		if ( ! $default_path ) {
			$default_path = ARTWORKER_TEMPLATE_PATH;
		}

		$template = null;
		// Look within passed path within the theme - this is priority
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name
			)
		);
		// Get default template
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Return what we found
		return apply_filters( 'artworker_locate_template', $template, $template_name, $template_path );
	}

}

if( ! function_exists('artworker_add_notice') ) {

	function artworker_add_notice( $message, $type = 'default', $location = null, $code = null ) {

		if ( ! $message ) {
			return;
		}

		$notices = Artworker()->session->get( 'notices', array() );

		if ( ! isset( $notices[ $type ] ) ) {
			$notices[ $type ] = array();
		}

		$notices[ $type ] = array(

			'type'		=> $type,
			'code'		=> $code,
			'location'	=> $location,
			'message'	=> $message

		);

		Artworker()->session->set( 'notices', $notices );
	}

}

if ( ! function_exists( 'artworker_get_notice' ) ) {

	function artworker_get_notice( $type = null ) {

		if ( $type ) {

			$notices = Artworker()->session->get( 'notices', array() );

			return isset( $notices[ $type ] ) ? $notices[ $type ] : array();

		}

	}

}

if ( ! function_exists( 'artworker_has_notice' ) ) {

	function artworker_has_notice( $type = null ) {

		if ( $type ) {

			$notices = Artworker()->session->get( 'notices', array() );

			return isset( $notices[ $type ] );

		}

	}

}

if ( ! function_exists( 'artworker_print_notices' ) ) {

	function artworker_print_notices() {

		if ( $notices = Artworker()->session->get( 'notices', array() ) ) {
			ob_start();
			artworker_get_template( 'notices/messages.php', array( 'notices' => $notices ) );
			$html = ob_get_clean();
			echo $html;
			Artworker()->session->set( 'notices', array() );
		}
 
	}

} 

if ( ! function_exists( 'artworker_print_notice' ) ) {

	function artworker_print_notice( $type = 'default', $message ) {

		artworker_get_template( "notices/{$type}.php", array(

			'messages' => array( apply_filters( 'artworker_add_message_' . $type, $message ) )

		) );

	}
 
}

if( ! function_exists('artworker_get_gallery_json_items') ) {

	function artworker_get_gallery_json_items( $query ) {

		$items = array();

		if( $query->have_posts() ) {

			while ( $query->have_posts() ) {

				$query->the_post();

				$id = get_the_ID();
				$artwork_id = get_post_meta( $id, 'artworker/artwork-id', true );

				$artwork_medium = wp_get_attachment_image_src( $artwork_id, 'medium' );
				$artwork_medium_src = $artwork_medium[0];

				$artwork_full = wp_get_attachment_image_src( $artwork_id, 'full' );
				$artwork_full_src = $artwork_full[0];
				$artwork_full_width = $artwork_full[1];
				$artwork_full_height = $artwork_full[2];		


				array_push( $items, array(

					'src' 		=> $artwork_full_src, 
					'w' 		=> $artwork_full_width, 
					'h' 		=> $artwork_full_height, 
					'msrc' 		=> $artwork_medium_src, 
					'title' 	=> get_the_title()

				 ) );

			} 

		}

		$gallery_items = json_encode( $items );
		

		return apply_filters( 'artworker_json_gallery_items', $gallery_items );

	}

}

if( ! function_exists('artworker_get_default_image') ) {

	function artworker_get_default_image() {

		$default_image = ARTWORKER_ASSET_URI . 'img/blank.gif';

		return apply_filters( 'artworker_default_image', $default_image );

	}

}

if( ! function_exists('artworker_get_gallery_thumb') ) {

	function artworker_get_gallery_thumb( $id, $args = array() ) {

		$defaults = apply_filters( 'artworker_default_artwork_args', array(

			'thumb_size'	=> 'large'

		) );

		$args = wp_parse_args( $args, $defaults );

		$gallery_thumb_id = 'artwork-' . $id;
		$default_image = artworker_get_default_image();
		$artwork_id = get_post_meta( $id, 'artworker/artwork-id', true );
		$artwork_thumb = wp_get_attachment_image_src( $artwork_id, $args['thumb_size'] );
		$artwork_url = get_permalink( $id );
		$artwork_src = $artwork_thumb[0];
		$artwork_width = $artwork_thumb[1];
		$artwork_height = $artwork_thumb[2];
		$artwork_classes = 'responsive-image pswp-image';
		$gallery_thumb_html = '	
			<div id="%1$s" class="item" data-w="%2$s" data-h="%3$s">

				<a id="%1$s-link" href="%4$s" class="artwork-link">

					<img src="%5$s" data-src="%6$s" class="%7$s">	

				</a>

			</div>
		';

		$gallery_thumb = sprintf( $gallery_thumb_html, $gallery_thumb_id, $artwork_width, $artwork_height, $artwork_url, $default_image, $artwork_src, $artwork_classes );

		return apply_filters( 'artworker_gallery_thumb', $gallery_thumb );

	}

}

if( ! function_exists('artworker_gallery_thumb') ) {

	function artworker_gallery_thumb( $id, $args = array() ) {

		echo artworker_get_gallery_thumb( $id, $args );

	}

}

if( ! function_exists('artworker_get_artwork_per_page') ) {

	function artworker_get_artwork_per_page() {

		return get_option( 'artwork_count' );

	}

}

