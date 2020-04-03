<?php

/**
 * Sets a property in the artworker_loop global.
 *
 * @since 1.0.0
 * @param string $prop Prop to set.
 * @param string $value Value to set.
 */
function artworker_set_loop_prop( $prop, $value = '' ) {
	if ( ! isset( $GLOBALS['artworker_loop'] ) ) {
		artworker_setup_loop();
	}
	$GLOBALS['artworker_loop'][ $prop ] = $value;
}

/**
 * Get the default columns setting - this is how many artworks will be shown per row in loops.
 *
 * @since 1.0.0
 * @return int
 */
function artworker_get_default_artwork_per_row() {
	$columns      = get_option( 'artworker_gallery_columns', 3 );
	$artwork_grid = artworker_get_theme_support( 'artwork_grid' );
	$min_columns  = isset( $artwork_grid['min_columns'] ) ? absint( $artwork_grid['min_columns'] ) : 0;
	$max_columns  = isset( $artwork_grid['max_columns'] ) ? absint( $artwork_grid['max_columns'] ) : 0;

	if ( $min_columns && $columns < $min_columns ) {
		$columns = $min_columns;
		update_option( 'artworker_gallery_columns', $columns );
	} elseif ( $max_columns && $columns > $max_columns ) {
		$columns = $max_columns;
		update_option( 'artworker_gallery_columns', $columns );
	}

	$columns = absint( $columns );

	return max( 1, $columns );
}

/**
 * Get the default rows setting - this is how many artwork rows will be shown in loops.
 *
 * @since 1.0.0
 * @return int
 */
function artworker_get_default_artwork_rows_per_page() {
	$rows         = absint( get_option( 'artworker_gallery_rows', 4 ) );
	$gallery_grid = artworker_get_theme_support( 'gallery_grid' );
	$min_rows     = isset( $gallery_grid['min_rows'] ) ? absint( $gallery_grid['min_rows'] ) : 0;
	$max_rows     = isset( $gallery_grid['max_rows'] ) ? absint( $gallery_grid['max_rows'] ) : 0;

	if ( $min_rows && $rows < $min_rows ) {
		$rows = $min_rows;
		update_option( 'artworker_gallery_rows', $rows );
	} elseif ( $max_rows && $rows > $max_rows ) {
		$rows = $max_rows;
		update_option( 'artworker_gallery_rows', $rows );
	}

	return $rows;
}

/**
 * Sets up the artworker_loop global from the passed args or from the main query.
 *
 * @since 1.0.0
 * @param array $args Args to pass into the global.
 */
function artworker_setup_loop( $args = array() ) {
	$default_args = array(
		'loop'         	=> 0,
		'columns'      	=> artworker_get_default_artwork_per_row(),
		'name'         	=> '',
		'is_shortcode' 	=> false,
		'is_paginated' 	=> true,
		'is_search'    	=> false,
		'total'        	=> 0,
		'total_pages'  	=> 0,
		'per_page'     	=> 0,
		'current_page' 	=> 1,
	);

	// If this is a main Artworker query, use global args as defaults.
	if ( $GLOBALS['wp_query']->get( 'artworker_query' ) ) {
		$default_args = array_merge(
			$default_args,
			array(
				'is_search'    => $GLOBALS['wp_query']->is_search(),
				'total'        => $GLOBALS['wp_query']->found_posts,
				'total_pages'  => $GLOBALS['wp_query']->max_num_pages,
				'per_page'     => $GLOBALS['wp_query']->get( 'posts_per_page' ),
				'current_page' => max( 1, $GLOBALS['wp_query']->get( 'paged', 1 ) ),
			)
		);
	}

	// Merge any existing values.
	if ( isset( $GLOBALS['artworker_loop'] ) ) {
		$default_args = array_merge( $default_args, $GLOBALS['artworker_loop'] );
	}

	$GLOBALS['artworker_loop'] = wp_parse_args( $args, $default_args );
}
add_action( 'artworker_before_gallery_loop', 'artworker_setup_loop' );

/**
 * Resets the artworker_loop global.
 *
 * @since 1.0.0
 */
function artworker_reset_loop() {
	unset( $GLOBALS['artworker_loop'] );
}
add_action( 'artworker_after_gallery_loop', 'artworker_reset_loop', 999 );

/**
 * Gets a property from the artworker_loop global.
 *
 * @since 1.0.0
 * @param string $prop Prop to get.
 * @param string $default Default if the prop does not exist.
 * @return mixed
 */
function artworker_get_loop_prop( $prop, $default = '' ) {
	artworker_setup_loop(); // Ensure gallery loop is setup.

	return isset( $GLOBALS['artworker_loop'], $GLOBALS['artworker_loop'][ $prop ] ) ? $GLOBALS['artworker_loop'][ $prop ] : $default;
}

/**
 * Get classname for artworker loops.
 *
 * @since 2.6.0
 * @return string
 */
function artworker_get_loop_class() {
	$loop_index = artworker_get_loop_prop( 'loop', 0 );
	$columns    = absint( max( 1, artworker_get_loop_prop( 'columns', artworker_get_default_artwork_per_row() ) ) );

	$loop_index ++;
	artworker_set_loop_prop( 'loop', $loop_index );

	if ( 0 === ( $loop_index - 1 ) % $columns || 1 === $columns ) {
		return 'first';
	}

	if ( 0 === $loop_index % $columns ) {
		return 'last';
	}

	return '';
}

/**
 * Retrieves the classes for the post div as an array.
 *
 * @since 1.0.0
 * @param string|array           $class      One or more classes to add to the class list.
 * @param int|WP_Post|Artworker_Artwork $artwork Product ID or artwork object.
 * @return array
 */
function artworker_get_artwork_class( $class = '', $artwork = null ) {
	if ( is_null( $artwork ) && ! empty( $GLOBALS['artwork'] ) ) {
		// Product was null so pull from global.
		$artwork = $GLOBALS['artwork'];
	}

	if ( $artwork && ! is_a( $artwork, 'Artworker_Artwork' ) ) {
		// Make sure we have a valid artwork, or set to false.
		$artwork = artworker_get_artwork( $artwork );
	}

	if ( $class ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
	} else {
		$class = array();
	}

	$post_classes = array_map( 'esc_attr', $class );

	if ( ! $artwork ) {
		return $post_classes;
	}

	// Run through the post_class hook so 3rd parties using this previously can still append classes.
	// Note, to change classes you will need to use the newer artworker_post_class filter.
	// @internal This removes the artworker_artwork_post_class filter so classes are not duplicated.
	$filtered = has_filter( 'post_class', 'artworker_artwork_post_class' );

	if ( $filtered ) {
		remove_filter( 'post_class', 'artworker_artwork_post_class', 20 );
	}

	$post_classes = apply_filters( 'post_class', $post_classes, $class, $artwork->get_id() );

	if ( $filtered ) {
		add_filter( 'post_class', 'artworker_artwork_post_class', 20, 3 );
	}

	$classes = array_merge(
		$post_classes,
		array(
			'artwork',
			'type-artwork',
			'post-' . $artwork->get_id(),
			artworker_get_loop_class(),
		),
		array(),
		array()
	);

	/**
	 * Artworker Post Class filter.
	 *
	 * @since 1.0.0
	 * @param array      $class Array of CSS classes.
	 * @param Artworker_Artwork $artwork Artwork object.
	 */
	$classes = apply_filters( 'artworker_post_class', $classes, $artwork );

	return array_map( 'esc_attr', array_unique( array_filter( $classes ) ) );
}

/**
 * Display the classes for the artwork div.
 *
 * @since 3.4.0
 * @param string|array           $class      One or more classes to add to the class list.
 * @param int|WP_Post|Artworker_Artwork $artwork_id Artwork ID or artwork object.
 */
function artworker_artwork_class( $class = '', $artwork_id = null ) {
	echo 'class="' . esc_attr( implode( ' ', artworker_get_artwork_class( $class, $artwork_id ) ) ) . '"';
}

/**
 * When the_post is called, put artwork data into a global.
 *
 * @param mixed $post Post Object.
 * @return Artworker_Artwork
 */
function artworker_setup_artwork_data( $post ) {
	unset( $GLOBALS['artwork'] );

	if ( is_int( $post ) ) {
		$post = get_post( $post );
	}

	if ( empty( $post->post_type ) || ! in_array( $post->post_type, array( 'artwork' ), true ) ) {
		return;
	}

	$GLOBALS['artwork'] = artworker_get_artwork( $post );

	return $GLOBALS['artwork'];
}
add_action( 'the_post', 'artworker_setup_artwork_data' );


if ( ! function_exists( 'artworker_get_loop_display_mode' ) ) {

	/**
	 * See what is going to display in the loop.
	 *
	 * @since 1.0.0
	 * @return string Either artwork, subcategories, or both, based on current page.
	 */
	function artworker_get_loop_display_mode() {
		// Only return artworks when filtering things.
		if ( artworker_get_loop_prop( 'is_search' ) ) {
			return 'artwork';
		}

		$parent_id    = 0;
		$display_type = '';

		if ( is_gallery() ) {
			$display_type = get_option( 'artworker_gallery_page_display', '' );
		} elseif ( is_artwork_category() ) {
			$parent_id    = get_queried_object_id();
			$display_type = get_term_meta( $parent_id, 'display_type', true );
			$display_type = '' === $display_type ? get_option( 'artworker_category_archive_display', '' ) : $display_type;
		}

		if ( ( ! is_gallery() || 'subcategories' !== $display_type ) && 1 < artworker_get_loop_prop( 'current_page' ) ) {
			return 'artwork';
		}

		// Ensure valid value.
		if ( '' === $display_type || ! in_array( $display_type, array( 'artwork', 'subcategories', 'both' ), true ) ) {
			$display_type = 'artwork';
		}

		// If we're showing categories, ensure we actually have something to show.
		if ( in_array( $display_type, array( 'subcategories', 'both' ), true ) ) {
			$subcategories = artworker_get_artwork_subcategories( $parent_id );

			if ( empty( $subcategories ) ) {
				$display_type = 'artwork';
			}
		}

		return $display_type;
	}
}

/**
 * Should the Artworker loop be displayed?
 *
 * This will return true if we have posts (artwork) or if we have subcats to display.
 *
 * @since 1.0.0
 * @return bool
 */
function artworker_artwork_loop() {
	return have_posts() || 'artwork' !== artworker_get_loop_display_mode();
}

if ( ! function_exists( 'artworker_page_title' ) ) {

	/**
	 * Page Title function.
	 *
	 * @param  bool $echo Should echo title.
	 * @return string
	 */
	function artworker_page_title( $echo = true ) {

		if ( is_search() ) {
			/* translators: %s: search query */
			$page_title = sprintf( __( 'Search results: &ldquo;%s&rdquo;', 'artworker' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				/* translators: %s: page number */
				$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'artworker' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_tax() ) {

			$page_title = single_term_title( '', false );

		} else {

			$gallery_page_id = artworker_get_page_id( 'gallery' );
			$page_title   = get_the_title( $gallery_page_id );

		}

		$page_title = apply_filters( 'artworker_page_title', $page_title );

		if ( $echo ) {
			echo $page_title; // WPCS: XSS ok.
		} else {
			return $page_title;
		}
	}
}

if ( ! function_exists( 'artworker_artwork_loop_start' ) ) {

	/**
	 * Output the start of a artwork loop. By default this is a UL.
	 *
	 * @param bool $echo Should echo?.
	 * @return string
	 */
	function artworker_artwork_loop_start( $echo = true ) {
		ob_start();

		artworker_set_loop_prop( 'loop', 0 );

		artworker_get_template( 'loop/loop-start.php', array(), true );

		$loop_start = apply_filters( 'artworker_artwork_loop_start', ob_get_clean() );

		if ( $echo ) {
			echo $loop_start; // WPCS: XSS ok.
		} else {
			return $loop_start;
		}
	}
}

if ( ! function_exists( 'artworker_artwork_loop_end' ) ) {

	/**
	 * Output the end of a artwork loop. By default this is a UL.
	 *
	 * @param bool $echo Should echo?.
	 * @return string
	 */
	function artworker_artwork_loop_end( $echo = true ) {
		ob_start();

		artworker_get_template( 'loop/loop-end.php', array(), true );

		$loop_end = apply_filters( 'artworker_artwork_loop_end', ob_get_clean() );

		if ( $echo ) {
			echo $loop_end; // WPCS: XSS ok.
		} else {
			return $loop_end;
		}
	}
}

if ( ! function_exists( 'artworker_pagination' ) ) {

	/**
	 * Output the pagination.
	 */
	function artworker_pagination() {
		if ( ! artworker_get_loop_prop( 'is_paginated' ) ) {
			return;
		}

		$args = array(
			'total'   => artworker_get_loop_prop( 'total_pages' ),
			'current' => artworker_get_loop_prop( 'current_page' ),
		);

		artworker_get_template( 'loop/pagination.php', $args, true );
	}
}

/**
 * Adds the artworker class to the body tag.
 *
 * @return array
 */
if( ! function_exists( 'artworker_add_body_class' ) ) {

	function artworker_add_body_class( $classes, $class ) {

		if( ! is_artworker() )
			return $classes;

		$classes[] = 'artworker';
    	return $classes;

	}

}

/**
 * Gets the photoswipe template.
 *
 * @return string
 */
if( ! function_exists( 'artworker_get_pwsp_template' ) ) {

	function artworker_get_pwsp_template() {

		return artworker_get_template_content( 'photoswipe.php' );

	}

}

/**
 * Renders the photoswipe template.
 *
 * @return array
 */
if( ! function_exists( 'artworker_pwsp_template' ) ) {

	function artworker_pwsp_template() {

		echo artworker_get_pwsp_template();

	}

}