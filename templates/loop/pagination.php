<?php
/**
 * Artwork Gallery Pagination
 *
 * This template can be overridden by copying it to yourtheme/artworker/loop/pagination.php.
 *
 * @author  Joshua McKendall
 * @package Artworker/Templates
 * @version 1.0.0
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit();

$total   = isset( $total ) ? $total : artworker_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : artworker_get_loop_prop( 'current_page' );

if ( $total <= 1 ) {
	return;
}

?>

<nav class="artworker-pagination" data-total="<?php esc_attr_e( $total ); ?>" data-current="<?php esc_attr_e( $current ); ?>">

	<button class="artworker-artwork-loadmore artwork-loadmore btn btn-pill btn-primary hidden">

		<?php _e( 'Load More', 'artworker' ); ?>
						
	</button>

	<noscript>
		<?php
			echo paginate_links( apply_filters( 'artworker_pagination_args', array( // WPCS: XSS ok.
				'add_args'     => false,
				'current'      => max( 1, $current ),
				'total'        => $total,
				'prev_text'    => '&larr;',
				'next_text'    => '&rarr;',
				'type'         => 'list',
				'end_size'     => 3,
				'mid_size'     => 3,
			) ) );
		?>
	</noscript>

</nav>
