const { apiFetch } = wp;
const { RichText, MediaUpload, PlainText, InspectorControls, BlockControls } = wp.editor;
const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { assign } = lodash;
const { addFilter } = wp.hooks;
const { TextControl, Button, ToggleControl, Panel, PanelBody, PanelRow, withInstanceId, Spinner } = wp.components;
const { createElement } = wp.element;
const { createHigherOrderComponent, compose } = wp.compose;
const { Component, Fragment } = wp.element;
const { registerStore, withSelect } = wp.data;

class ArtGallery extends Component {

	constructor( props ) {
		super(props);
		this.onTruncate = this.onTruncate.bind(this);

	}

    componentDidMount() {
        const { setAttributes, clientId, attributes, attributes: { gallery_id } } = this.props;
        const _client = clientId.substr(0,6);
        const post_id = wp.data.select( 'core/editor' ).getCurrentPostId();
        const unique_id = `${ post_id }${ _client }`;

        if ( ! attributes.gallery_id ) {
            setAttributes({ gallery_id: unique_id });
        }
    }

	onTruncate() {
		const { setAttributes, attributes: { truncate } } = this.props;
		setAttributes( { truncate: ! truncate } );
	}

	render() {

		const { name, attributes, recipients, isSelectedBlockInRoot, setAttributes, attributes: { gallery_id, className, animation, enablePosition, selectPosition, positionXaxis, positionYaxis, globalZindex, hideTablet, hideMobile, globalCss, interaction } } = this.props;

		return (
			<Fragment>
	  			<InspectorControls>
					<PanelBody
							title={ __( 'Gallery Settings', 'artworker' ) }
							initialOpen={ true }
						>
						<PanelRow>
				  			<ToggleControl 
				  				label="Truncate Art Gallery" 
				  				checked={ attributes.truncate } 
				  				onChange={ this.onTruncate } 
				  			/> 
			  			</PanelRow>
		  			</PanelBody>
	  			</InspectorControls>
				<div id={ `art-gallery-${attributes.gallery_id}` } className="art-gallery" data-gallery-id={ `${attributes.gallery_id}` }>
					<PlainText
						className="wp-block-button__link artworker-art-gallery__button submit-btn"
						onChange={ content => setAttributes({ loadmore_label: content }) }
						value={ attributes.loadmore_label }
					/>
					<input
						className="artworker-art-gallery-id"
						type="hidden"
						value={ `${attributes.gallery_id}` }
					 />
				</div>
			</Fragment>
		);

	}

}

export default compose([
	withSelect( ( select, ownProps ) => {
        const { clientId } = ownProps;
        const { getBlock, isBlockSelected, hasSelectedInnerBlock } = select('core/block-editor');

		return {

			clientId: clientId,
            block: getBlock( clientId ),
            isSelectedBlockInRoot: isBlockSelected( clientId ) || hasSelectedInnerBlock( clientId, true ),

		};

	} )
])( ArtGallery )
