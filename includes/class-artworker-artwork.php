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
	 * Size for this object.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $size = '';

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


	public function __construct( $artwork, $size  = 'full' ) {

		if ( is_numeric( $artwork ) && $artwork > 0 ) {
			$this->set_id( $artwork );
		} elseif ( $artwork instanceof self ) {
			$this->set_id( absint( $artwork->get_id() ) );
		} elseif ( ! empty( $artwork->ID ) ) {
			$this->set_id( absint( $artwork->ID ) );
		}

		$artwork_image_id = get_post_thumbnail_id( $this->id );
		$artwork_data = wp_get_attachment_image_src( $artwork_image_id, $size );

		$this->set_data( $artwork_data );
		$this->set_artwork_id( $artwork_image_id );
		$this->set_size( $size );
		$this->set_src( $this->data[0] );
		$this->set_width( $this->data[1] );
		$this->set_height( $this->data[2] );
		

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
	public function set_data( $data = array() ) {
		$this->data = $data;
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
	 * Set size.
	 *
	 * @since 1.0.0
	 * @param string $size.
	 */
	public function set_size( $size = '' ) {
		$this->size = (string) $size;
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
	public function get_height() {
		return $this->height;
	}

	/**
	 * Get artwork width.
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_width() {
		return $this->width;
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
	public function get_src( $src_size = 'full' ) {

		$src = $this->src;

		if( $src_size != $this->size || empty( $src ) )
			$artwork = wp_get_attachment_image_src( $this->artwork_id, $src_size );
			
		if( ! empty( $artwork ) && is_array( $artwork ) )
			$src = $artwork[0];

		if( empty( $src ) || ! is_string( $src ) )
			$src = '';

		if( empty( $this->src ) && $src_size == $this->size )
			$this->set_src( $src );

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