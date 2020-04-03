<?php
/**
 * The Artworker Template Loader class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The Artworker Template Loader class
 *
 * This class loads the templates for the plugin.
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
 * Template loader class.
 */
class Artworker_Template_Loader {

	/**
	 * Store the gallery page ID.
	 *
	 * @var integer
	 */
	private static $gallery_page_id = 0;

	/**
	 * Store whether we're processing artwork inside the_content filter.
	 *
	 * @var boolean
	 */
	private static $in_content_filter = false;

	/**
	 * Is Artworker support defined?
	 *
	 * @var boolean
	 */
	private static $theme_support = false;

	/**
	 * Hook in methods.
	 */
	public static function init() {
		self::$theme_support = current_theme_supports( 'artworker' );
		self::$gallery_page_id  = artworker_get_page_id( 'gallery' );

		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );
		add_action( 'wp_footer', 'artworker_pwsp_template' );
	}

	/**
	 * Load a template.
	 *
	 * Handles template usage so that we can use our own templates instead of the theme's.
	 *
	 * Templates are in the 'templates' folder. Artworker looks for theme
	 * overrides in /theme/artworker/ by default.
	 *
	 *
	 * @param string $template Template to load.
	 * @return string
	 */
	public static function template_loader( $template ) {
		if ( is_embed() ) {
			return $template;
		}

		$default_file = self::get_template_loader_default_file();

		if ( $default_file ) {
			/**
			 * Filter hook to choose which files to find before Artworker does it's own logic.
			 *
			 * @since 1.0.0
			 * @var array
			 */
			$search_files = self::get_template_loader_files( $default_file );
			$template     = locate_template( $search_files );

			if ( ! $template || ARTWORKER_TEMPLATE_DEBUG_MODE ) {
				$template = Artworker()->plugin_path() . '/templates/' . $default_file;
			}
		}

		return $template;
	}

	/**
	 * Get the default filename for a template.
	 *
	 * @since  3.0.0
	 * @return string
	 */
	private static function get_template_loader_default_file() {
		if ( is_artwork_taxonomy() ) {
			$object = get_queried_object();

			if ( is_tax( 'artwork_cat' ) || is_tax( 'artwork_tag' ) ) {
				$default_file = 'taxonomy-' . $object->taxonomy . '.php';
			} else {
				$default_file = 'archive-artwork.php';
			}
		} elseif ( is_post_type_archive( 'artwork' ) || is_page( artworker_get_page_id( 'gallery' ) ) ) {
			$default_file = 'archive-artwork.php';
		} else {
			$default_file = '';
		}
		return $default_file;
	}

	/**
	 * Get an array of filenames to search for a given template.
	 *
	 * @since  1.0.0
	 * @param  string $default_file The default file name.
	 * @return string[]
	 */
	private static function get_template_loader_files( $default_file ) {
		$templates   = apply_filters( 'artworker_template_loader_files', array(), $default_file );
		$templates[] = 'artworker.php';

		if ( is_page_template() ) {
			$templates[] = get_page_template_slug();
		}

		if ( is_singular( 'artwork' ) ) {
			$object       = get_queried_object();
			$name_decoded = urldecode( $object->post_name );
			if ( $name_decoded !== $object->post_name ) {
				$templates[] = "single-artwork-{$name_decoded}.php";
			}
			$templates[] = "single-artwork-{$object->post_name}.php";
		}

		if ( is_artwork_taxonomy() ) {
			$object      = get_queried_object();
			$templates[] = 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
			$templates[] = Artworker()->template_path() . 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
			$templates[] = 'taxonomy-' . $object->taxonomy . '.php';
			$templates[] = Artworker()->template_path() . 'taxonomy-' . $object->taxonomy . '.php';
		}

		$templates[] = $default_file;
		$templates[] = Artworker()->template_path() . $default_file;

		return array_unique( $templates );
	}

	/**
	 * Force the loading of one of the single templates instead of whatever template was about to be loaded.
	 *
	 * @since 3.3.0
	 * @param string $template Path to template.
	 * @return string
	 */
	public static function force_single_template_filter( $template ) {
		$possible_templates = array(
			'page',
			'single',
			'singular',
			'index',
		);

		foreach ( $possible_templates as $possible_template ) {
			$path = get_query_template( $possible_template );
			if ( $path ) {
				return $path;
			}
		}

		return $template;
	}

	/**
	 * Get information about the current gallery page view.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private static function get_current_gallery_view_args() {
		return (object) array(
			'page'    => absint( max( 1, absint( get_query_var( 'paged' ) ) ) ),
			'columns' => artworker_get_default_artwork_per_row(),
			'rows'    => artworker_get_default_artwork_rows_per_page(),
		);
	}


	/**
	 * Are we filtering content for unsupported themes?
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function in_content_filter() {
		return (bool) self::$in_content_filter;
	}

}

add_action( 'init', array( 'Artworker_Template_Loader', 'init' ) );
