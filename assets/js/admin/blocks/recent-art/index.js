import edit from './edit';

const { registerBlockType } = wp.blocks;
const { createElement, Fragment } = wp.element;
const { __ } = wp.i18n;

registerBlockType('artworker/art-gallery-block', {   
  title: __( 'Art Gallery', 'artworker' ),
  icon: 'heart',
  category: 'widgets',
  supports: {
  	anchor: true,
  	align: true
  },
  attributes: {
	  loadmore_label: {
	  	default: __( 'Load More', 'artworker' ),
	  	type: 'string',
	  	selector: '.loadmore-btn'
	  },
	  art_count: {
	  	default: 24,
	  	type: 'integer',
        source: 'meta',
        meta: 'artworker/art-count'
	  },
	  truncate: {
	  	default: false,
	  	type: 'boolean'
	  },
	  max_rows: {
	  	default: null,
	  	type: 'integer'
	  },
	  gallery_id: {
	  	type: 'string',
	  	selector: '.artworker-art-gallery-id',
	  }
	},
	edit,
	save: props => {
		return null;
	}
} );