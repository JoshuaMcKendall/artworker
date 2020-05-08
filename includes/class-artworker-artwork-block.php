<?php

/**
 * The Artwork Block class
 *
 * @link       https://github.com/JoshuaMcKendall/artworker/tree/master/includes
 * @since      1.0.0
 *
 * @package    Artworker
 * @subpackage Artworker/includes
 */

/**
 * The artwork block class.
 *
 * This class builds artwork block object.
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
 * Artworker_Artwork_Block class.
 */
class Artworker_Artwork_Block extends Artworker_Abstract_Dynamic_Block {
	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'artwork-block';

	public function __construct() {
		add_action( 'init', array( $this, 'register_block_type' ) );
	}

	/**
	 * Registers the block type with WordPress.
	 */
	public function register_block_type() {

		$suffix 	  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '.min';

		wp_register_style( 'artworker-' . $this->block_name . '-styles', ARTWORKER_ASSET_URI . 'css/frontend/blocks/artwork/style.css' );

		wp_register_style( 'artworker-' . $this->block_name . '-edit-styles', ARTWORKER_ASSET_URI . 'css/admin/blocks/artwork/styles.css' );

		wp_register_script( 'artworker-' . $this->block_name, ARTWORKER_ASSET_URI . 'js/admin/blocks/artwork/block' . $suffix . '.js', array(
			'wp-blocks', 
			'wp-element', 
			'wp-editor'
		) );

	    register_meta( 'post', 'artworker/artwork-data', array(
	    	'object_subtype'	=> 'artwork',
	        'show_in_rest' 		=> true,
	        'single' 			=> true,
	        'type' 				=> 'string',
	        'auth_callback' 	=> function() {

    			return true;

			}
	    ) );

		register_block_type(
			$this->namespace . '/' . $this->block_name,
			array(
				'render_callback' => array( $this, 'render' ),
				'editor_script'   => 'artworker-' . $this->block_name,
				'editor_style'    => 'artworker-' . $this->block_name . '-edit-styles',
			)
		);
	}

	public function get_attributes() {

		$attributes = array( 

					'align'	=> array(
						'type'		=> 'string',
					),
					'url'	=> array(
						'type'		=> 'string',
						'source'	=> 'attribute',
						'selector'	=> 'img',
						'attribute'	=> 'src'
					),
					'alt'	=> array(
						'type'		=> 'string',
						'source'	=> 'attribute',
						'selector'	=> 'img',
						'attribute'	=> 'alt',
						'default'	=> ''
					),
					'caption'	=> array(
						'type'		=> 'string',
						'source'	=> 'html',
						'selector'	=> 'figcaption'
					),
					'title'	=> array(
						'type'		=> 'string',
						'source'	=> 'attribute',
						'selector'	=> 'img',
						'attribute'	=> 'title'
					),
					'href'	=> array(
						'type'		=> 'boolean',
						'source'	=> 'attribute',
						'selector'	=> 'figure > a',
						'attribute'	=> 'href'
					),
					'rel'	=> array(
						'type'		=> 'string',
						'source'	=> 'attribute',
						'selector'	=> 'figure > a',
						'attribute'	=> 'rel'
					),
					'linkClass'		=> array(
						'type'		=> 'string',
						'source'	=> 'attribute',
						'selector'	=> 'figure > a',
						'attribute'	=> 'class'
					),
					'id'	=> array(
						'type'		=> 'number',
					),
					'width'	=> array(
						'type'		=> 'number',
					),
					'height'	=> array(
						'type'		=> 'number',
					),
					'sizeSlug'	=> array(
						'type'		=> 'string',
					),
					'linkDestination'	=> array(
						'type'		=> 'string',
						'default'	=> 'none'
					),
					'linkTarget'	=> array(
						'type'		=> 'string',
						'source'	=> 'attribute',
						'selector'	=> 'figure > a',
						'attribute'	=> 'target'
					),
					'data'	=> array(
						'type'		=> 'string',
						'source'	=> 'meta',
						'meta'		=> 'artworker/artwork-data'
					),
		);

		return apply_filters( 'artworker_artwork_attributes', $attributes );

	}

	/**
	 * Append frontend scripts when rendering the Product Categories List block.
	 *
	 * @param array  $attributes Block attributes. Default empty array.
	 * @param string $content    Block content. Default empty string.
	 * @return string Rendered block type output.
	 */
	public function render( $attributes = array(), $content = '' ) {

		do_action( 'artworker_before_render_artwork' );

		$post_id = get_the_ID();
		$artwork = artworker_get_artwork( $post_id );
		$artwork_id = $artwork->get_artwork_id();
		$artwork_identifier = 'artwork-' . $artwork_id;
		$width = $artwork->get_width();
		$height = $artwork->get_height();
		$classes = 'item artwork';
		$caption = array_key_exists( 'caption', $attributes ) ? $attributes['caption'] : '';
		$align = array_key_exists( 'align', $attributes ) ? $attributes['align'] : '';

		if( ! empty( $align ) ) {

			switch ( $align ) {
				case 'wide':
					$classes .= ' alignwide';
					break;

				case 'full':
					$classes .= ' alignfull';
					break;
			}

		}

		$args = array(

			'artwork' 				=> $artwork,
			'artwork_id'			=> $artwork_id,
			'artwork_identifier'	=> $artwork_identifier,
			'width'					=> $width,
			'height'				=> $height,
			'caption'				=> $caption,
			'classes'				=> $classes,

		);

		return artworker_get_template_content( 'artwork-block.php', $args );

	}
}

new Artworker_Artwork_Block();
