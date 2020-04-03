<?php

/**
 * The Artwork class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The artwork class.
 *
 * This class builds the artwork dynamic block.
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

class Artworker_Artwork {

	/**
	 * ID for artwork post.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID for the artwork image.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $artwork_id = 0;

	/**
	 * Name for this artwork.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $name = '';

	/**
	 * Slug for this object.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Title for this object.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * Height of image.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $height = '';

	/**
	 * Width of image.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $width = '';

	/**
	 * src of image.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $src = '';

	/**
	 * sizes of image.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $sizes = array();

	/**
	 * Data of artwork.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $data = array();

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'artwork';

	/**
	 * Cache group.
	 *
	 * @var string
	 */
	protected $cache_group = 'artwork';


	public function __construct( $artwork ) {

		if ( is_numeric( $artwork ) && $artwork > 0 ) {
			$this->set_id( $artwork );
		} elseif ( $artwork instanceof self ) {
			$this->set_id( absint( $artwork->get_id() ) );
		} elseif ( ! empty( $artwork->ID ) ) {
			$this->set_id( absint( $artwork->ID ) );
		}

		$this->set_data( get_post_meta( $this->id, 'artworker/artwork-data', true ) );
		$this->set_src( $this->data['src'] );
		$this->set_artwork_id( $this->data['id'] );
		$this->set_sizes( $this->data['sizes'] );
		$this->set_height( $this->data['height'] );
		$this->set_width( $this->data['width'] );

	}

	/**
	 * Set ID.
	 *
	 * @since 1.0.0
	 * @param int $id ID.
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Set ID.
	 *
	 * @since 1.0.0
	 * @param int $id ID.
	 */
	public function set_artwork_id( $artwork_id = 0 ) {
		$this->artwork_id = absint( $artwork_id );
	}

	/**
	 * Set data.
	 *
	 * @since 1.0.0
	 * @param string $data.
	 */
	public function set_data( $data = '', $assoc = true ) {
		$this->data = json_decode( $data, $assoc );
	}

	/**
	 * Set src.
	 *
	 * @since 1.0.0
	 * @param string $src.
	 */
	public function set_src( $src = '' ) {
		$this->src = (string) $src;
	}

	/**
	 * Set height.
	 *
	 * @since 1.0.0
	 * @param int $height.
	 */
	public function set_height( $height = 0 ) {
		$this->height = absint( $height );
	}

	/**
	 * Set width.
	 *
	 * @since 1.0.0
	 * @param int $width.
	 */
	public function set_width( $width = 0 ) {
		$this->width = absint( $width );
	}

	/**
	 * Set width.
	 *
	 * @since 1.0.0
	 * @param int $width.
	 */
	public function set_sizes( $sizes = array() ) {
		$this->sizes = is_array( $sizes ) ? (array) $sizes : array();
	}

	/**
	 * Returns the unique ID for this object.
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Returns the unique ID for the artwork attachment.
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_artwork_id() {
		return $this->artwork_id;
	}

	/**
	 * Get artwork name.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get artwork slug.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Get artwork title.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Get artwork height.
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_height( $size = '' ) {
		$size = $this->get_size( $size );
		$height = $this->height;

		if( is_array( $size ) && array_key_exists( 'height', $size ) )
			$height = $size['height'];

		return $height;
	}

	/**
	 * Get artwork width.
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_width( $size = '' ) {
		$size = $this->get_size( $size );
		$width = $this->width;

		if( is_array( $size ) && array_key_exists( 'width', $size ) )
			$width = $size['width'];

		return $width;
	}

	/**
	 * Get artwork sizes.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_sizes() {
		return $this->sizes;
	}

	/**
	 * Get artwork size.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_size( $size ) {
		if( array_key_exists( $size, $this->sizes ) ) {
			$size = $this->sizes[ $size ];
		}

		return $size ? $size : false;
	}

	/**
	 * Get post type.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Get src.
	 *
	 * @since  1.0.0
	 * @param $size 
	 * @return string
	 */
	public function get_src( $src_size = '' ) {
		$size 	= $this->get_size( $src_size );
		$src 	= $this->src;
			
		if( is_array( $size ) && array_key_exists( 'url', $size ) )
			$src = $size['url'];

		if( empty( $src ) || ! is_string( $src ) )
			$src = '';

		return $src;
	}

	/**
	 * Get data.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_data( $format = 'array' ) {
		switch ( $format ) {
			case 'array':
				$data = $this->data;
				break;
			case 'json':
				$data = json_encode( $this->data );
				break;
			default:
				$data = $this->data;
				break;
		}

		return $data;
	}
}