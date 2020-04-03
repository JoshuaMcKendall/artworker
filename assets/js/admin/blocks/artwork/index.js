import edit     from './edit';
import metadata from './block.json';

const { registerBlockType } = wp.blocks;
const { createElement, Fragment } = wp.element;
const { __ } = wp.i18n;
const { name, category, attributes } = metadata;

registerBlockType( name, {   
  title: __( 'Artwork', 'artworker' ),
  icon: 'heart',
  category: category,
  keywords: [ __( 'artwork' ), __( 'image' ), __( 'pics' ) ],
  supports: {
    anchor: true,
    html: false,
    multiple: false,
    reusable: false,
    align: ['wide', 'full'],
  },
  attributes: attributes,
  edit,
  save: props => {
    return null;
  }
} );