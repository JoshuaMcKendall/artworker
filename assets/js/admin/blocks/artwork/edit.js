import classnames from 'classnames';

const { MediaUpload, PlainText, InspectorControls, BlockControls } = wp.editor;
const { RichText, BlockAlignmentToolbar, BlockIcon, InspectorAdvancedControls, MediaPlaceholder, MediaReplaceFlow, __experimentalImageURLInputUI, } = wp.blockEditor;
const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const { assign, get, filter, map, last, omit, pick, has } = lodash;
const { addFilter } = wp.hooks;
const { withNotices, TextareaControl, TextControl, Button, ToggleControl, Panel, PanelBody, PanelRow, withInstanceId, Spinner, Icon, ResizableBox, FocalPointPicker } = wp.components;
const { createElement } = wp.element;
const { createHigherOrderComponent, compose } = wp.compose;
const { Component, Fragment } = wp.element;
const { withSelect, withDispatch } = wp.data;
const { isViewportMatch } = wp.data.select( 'core/viewport' );
const { withViewportMatch } = wp.viewport;
const { isBlobURL, revokeBlobURL } = wp.blob;
const { getPath } = wp.url;

import ImageSizeControl from './image-size-control';
import ImageSize from './image-size';

import {
	MIN_SIZE,
	LINK_DESTINATION_MEDIA,
	LINK_DESTINATION_ATTACHMENT,
	ALLOWED_MEDIA_TYPES,
	DEFAULT_SIZE_SLUG,
} from './constants';

export const pickRelevantMediaFiles = ( image ) => {
	const imageProps = pick( image, [ 'alt', 'id', 'link', 'caption' ] );
	imageProps.url =
		get( image, [ 'sizes', 'large', 'url' ] ) ||
		get( image, [ 'media_details', 'sizes', 'large', 'source_url' ] ) ||
		image.url;
	return imageProps;
};

/**
 * Is the URL a temporary blob URL? A blob URL is one that is used temporarily
 * while the image is being uploaded and will not have an id yet allocated.
 *
 * @param {number=} id The id of the image.
 * @param {string=} url The url of the image.
 *
 * @return {boolean} Is the URL a Blob URL
 */
const isTemporaryImage = ( id, url ) => ! id && isBlobURL( url );

/**
 * Is the url for the image hosted externally. An externally hosted image has no id
 * and is not a blob url.
 *
 * @param {number=} id  The id of the image.
 * @param {string=} url The url of the image.
 *
 * @return {boolean} Is the url an externally hosted url?
 */
const isExternalImage = ( id, url ) => url && ! id && ! isBlobURL( url );

class Artwork extends Component {

	constructor( props ) {
		super( props );
		this.updateAlt = this.updateAlt.bind( this );
		this.updateAlignment = this.updateAlignment.bind( this );
		this.onFocusCaption = this.onFocusCaption.bind( this );
		this.onImageClick = this.onImageClick.bind( this );
		this.onSelectImage = this.onSelectImage.bind( this );
		this.updateImage = this.updateImage.bind( this );
		this.onSetHref = this.onSetHref.bind( this );
		this.onSetTitle = this.onSetTitle.bind( this );
		this.getFilename = this.getFilename.bind( this );
		this.onUploadError = this.onUploadError.bind( this );
		this.onImageError = this.onImageError.bind( this );

		this.state = {
			captionFocused: false,
		};

	}

    componentDidMount() {
        const { setAttributes, clientId, attributes, mediaUpload, noticeOperations } = this.props;
        const { blockID, id, data, url = '' } = attributes;
        const _client = clientId.substr(0, 6);
        const post_id = wp.data.select( 'core/editor' ).getCurrentPostId();
        const unique_id = `${ post_id }${ _client }`;

        if ( ! attributes.blockID ) {
            setAttributes({ blockID: unique_id });
        }


  //       setAttributes({ data: data });

		// if ( isTemporaryImage( id, url ) ) {
		// 	const file = getBlobByURL( url );

		// 	if ( file ) {
		// 		mediaUpload( {
		// 			filesList: [ file ],
		// 			onFileChange: ( [ image ] ) => {
		// 				this.onSelectImage( image );
		// 			},
		// 			allowedTypes: ALLOWED_MEDIA_TYPES,
		// 			onError: ( message ) => {
		// 				noticeOperations.createErrorNotice( message );
		// 			},
		// 		} );
		// 	}
		// }
    }

	componentDidUpdate( prevProps ) {
		const { id: prevID, url: prevURL = '' } = prevProps.attributes;
		const { id, url = '' } = this.props.attributes;

		if (
			isTemporaryImage( prevID, prevURL ) &&
			! isTemporaryImage( id, url )
		) {
			revokeBlobURL( url );
		}

		if (
			! this.props.isSelected &&
			prevProps.isSelected &&
			this.state.captionFocused
		) {
			this.setState( {
				captionFocused: false,
			} );
		}
	}

	updateAlt( newAlt ) {
		this.props.setAttributes( { alt: newAlt } );
	}

	updateAlignment( nextAlign ) {
		const extraUpdatedAttributes =
			[ 'wide', 'full' ].indexOf( nextAlign ) !== -1
				? { width: undefined, height: undefined }
				: {};
		this.props.setAttributes( {
			...extraUpdatedAttributes,
			align: nextAlign,
		} );
	}

	onImageClick() {
		if ( this.state.captionFocused ) {
			this.setState( {
				captionFocused: false,
			} );
		}
	}

	onSelectImage( media ) {
		if ( ! media || ! media.url ) {
			this.props.setAttributes( {
				url: undefined,
				width: undefined,
				height: undefined,
				alt: undefined,
				id: undefined,
				title: undefined,
				caption: undefined,
				data: undefined,
			} );
			return;
		}

		const {
			id,
			url,
			alt,
			width,
			height,
			caption,
			linkDestination,
			data,
		} = this.props.attributes;

		let mediaAttributes = pickRelevantMediaFiles( media );

		// If the current image is temporary but an alt text was meanwhile written by the user,
		// make sure the text is not overwritten.
		if ( isTemporaryImage( id, url ) ) {
			if ( alt ) {
				mediaAttributes = omit( mediaAttributes, [ 'alt' ] );
			}
		}

		// If a caption text was meanwhile written by the user,
		// make sure the text is not overwritten by empty captions
		if ( caption && ! get( mediaAttributes, [ 'caption' ] ) ) {
			mediaAttributes = omit( mediaAttributes, [ 'caption' ] );
		}

		let additionalAttributes;
		// Reset the dimension attributes if changing to a different image.
		if ( ! media.id || media.id !== id ) {
			additionalAttributes = {
				width: undefined,
				height: undefined,
				sizeSlug: DEFAULT_SIZE_SLUG,
			};
		} else {
			// Keep the same url when selecting the same file, so "Image Size" option is not changed.
			additionalAttributes = { url };
		}

		console.log( media );

		// if ( media ) {
		// 	const mediaSize = applyFilters(
		// 		'editor.PostFeaturedImage.imageSize',
		// 		'post-thumbnail',
		// 		media.id,
		// 		currentPostId
		// 	);
		// 	if ( has( media, [ 'media_details', 'sizes', mediaSize ] ) ) {
		// 		// use mediaSize when available
		// 		mediaWidth = media.media_details.sizes[ mediaSize ].width;
		// 		mediaHeight = media.media_details.sizes[ mediaSize ].height;
		// 		mediaSourceUrl = media.media_details.sizes[ mediaSize ].source_url;
		// 	} else {
		// 		// get fallbackMediaSize if mediaSize is not available
		// 		const fallbackMediaSize = applyFilters(
		// 			'editor.PostFeaturedImage.imageSize',
		// 			'thumbnail',
		// 			media.id,
		// 			currentPostId
		// 		);
		// 		if (
		// 			has( media, [ 'media_details', 'sizes', fallbackMediaSize ] )
		// 		) {
		// 			// use fallbackMediaSize when mediaSize is not available
		// 			mediaWidth =
		// 				media.media_details.sizes[ fallbackMediaSize ].width;
		// 			mediaHeight =
		// 				media.media_details.sizes[ fallbackMediaSize ].height;
		// 			mediaSourceUrl =
		// 				media.media_details.sizes[ fallbackMediaSize ].source_url;
		// 		} else {
		// 			// use full image size when mediaFallbackSize and mediaSize are not available
		// 			mediaWidth = media.media_details.width;
		// 			mediaHeight = media.media_details.height;
		// 			mediaSourceUrl = media.source_url;
		// 		}
		// 	}
		// }

	}

	updateImage( sizeSlug ) {
		const { image } = this.props;

		const url = get( image, [
			'media_details',
			'sizes',
			sizeSlug,
			'source_url',
		] );
		if ( ! url ) {
			return null;
		}

		this.props.setAttributes( {
			url,
			width: undefined,
			height: undefined,
			sizeSlug,
		} );
	}

	onSetHref( props ) {
		this.props.setAttributes( props );
	}

	onSetTitle( value ) {
		// This is the HTML title attribute, separate from the media object title
		this.props.setAttributes( { title: value } );
	}

	getFilename( url ) {
		const path = getPath( url );
		if ( path ) {
			return last( path.split( '/' ) );
		}
	}

	onFocusCaption() {
		if ( ! this.state.captionFocused ) {
			this.setState( {
				captionFocused: true,
			} );
		}
	}

	onUploadError( message ) {
		const { noticeOperations } = this.props;
		noticeOperations.removeAllNotices();
		noticeOperations.createErrorNotice( message );
	}

	onImageError() {
		// Check if there's an embed block that handles this URL.
		const embedBlock = createUpgradedEmbedBlock( { attributes: { url } } );
		if ( undefined !== embedBlock ) {
			this.props.onReplace( embedBlock );
		}
	}

	getImageSizeOptions() {
		const { imageSizes, image } = this.props;
		return map(
			filter( imageSizes, ( { slug } ) =>
				get( image, [ 'media_details', 'sizes', slug, 'source_url' ] )
			),
			( { name, slug } ) => ( { value: slug, label: name } )
		);
	}

	render() {

		const {
			attributes,
			setAttributes,
			isLargeViewport,
			isSelected,
			className,
			maxWidth,
			noticeUI,
			isRTL,
			onResizeStart,
			onResizeStop,
			currentPostId,
			featuredImageId,
			onUpdateImage,
			onDropImage,
			onRemoveImage,
			image,
			postType,
		} = this.props;
		const {
			alt,
			caption,
			focalPoint,
			align,
			id,
			href,
			rel,
			linkClass,
			linkDestination,
			title,
			width,
			height,
			linkTarget,
			sizeSlug,
		} = attributes;

		console.log( 'IMAGE: ', image );
		let url = null;

		if( has( image, 'source_url' ) ) {
			url = image.source_url;
		}

		const icon = () => (
		    <Icon
		        icon={ () => (
		            <svg>
		                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM5 4.5h14c.3 0 .5.2.5.5v8.4l-3-2.9c-.3-.3-.8-.3-1 0L11.9 14 9 12c-.3-.2-.6-.2-.8 0l-3.6 2.6V5c-.1-.3.1-.5.4-.5zm14 15H5c-.3 0-.5-.2-.5-.5v-2.4l4.1-3 3 1.9c.3.2.7.2.9-.1L16 12l3.5 3.4V19c0 .3-.2.5-.5.5z" />
		            </svg>
		        ) }
		    />
		);

		const src = undefined;

		const labels = {
			title: ! url ? __( 'Artwork' ) : __( 'Edit artwork' ),
			instructions: __(
				'Upload artwork as an image file, pick one from your media library.'
			),
		};

		const mediaPreview = !! url && (
			<img
				alt={ __( 'Edit artwork' ) }
				title={ __( 'Edit artwork' ) }
				className={ 'edit-image-preview' }
				src={ url }
			/>
		);

		// const needsAlignmentWrapper = [ 'center', 'left', 'right' ].includes(
		// 	align
		// );

		const getInspectorControls = ( imageWidth, imageHeight ) => {
				<div>
					<InspectorControls>
						<PanelBody title={ __( 'Artwork settings', 'artworker' ) }>
							<TextareaControl
								label={ __( 'Alt text (alternative text)' ) }
								value={ alt }
								onChange={ this.updateAlt }
							/>
							<ImageSizeControl
								onChangeImage={ this.updateImage }
								onChange={ ( value ) => setAttributes( value ) }
								slug={ sizeSlug }
								width={ width }
								height={ height }
								imageSizeOptions={ imageSizeOptions }
								isResizable={ isResizable }
								imageWidth={ imageWidth }
								imageHeight={ imageHeight }
							/>
						</PanelBody>
					</InspectorControls>
					<InspectorAdvancedControls>
						<TextControl
							label={ __( 'Title attribute' ) }
							value={ title || '' }
							onChange={ this.onSetTitle }
						/>
					</InspectorAdvancedControls>
				</div>
		};

		const mediaPlaceholder = (
			<MediaPlaceholder
				icon={ <BlockIcon icon={ icon } /> }
				labels={ labels }
				onSelect={ onUpdateImage }
				notices={ noticeUI }
				onError={ this.onUploadError }
				accept="image/*"
				allowedTypes={ ALLOWED_MEDIA_TYPES }
				value={ { id, src } }
				mediaPreview={ mediaPreview }
				disableMediaButtons={ url }
			/>
		);

		const classes = classnames( className, {
			'is-transient': isBlobURL( url ),
			'is-resized': !! width || !! height,
			'is-focused': isSelected,
			[ `size-${ sizeSlug }` ]: sizeSlug,
			[ `align${ align }` ]: align,
		} );

		const isResizable =
			[ 'wide', 'full' ].indexOf( align ) === -1 && isLargeViewport;

		const imageSizeOptions = this.getImageSizeOptions();

		const controls = (
			<BlockControls>
				{ url && (
					<MediaReplaceFlow
						mediaId={ id }
						mediaURL={ url }
						allowedTypes={ ALLOWED_MEDIA_TYPES }
						accept="image/*"
						onSelect={ onUpdateImage }
						onError={ this.onUploadError }
					/>
				) }
			</BlockControls>
		);

		const filename = this.getFilename( url );
		let defaultedAlt;
		if ( alt ) {
			defaultedAlt = alt;
		} else if ( filename ) {
			defaultedAlt = sprintf(
				__(
					'This image has an empty alt attribute; its file name is %s'
				),
				filename
			);
		} else {
			defaultedAlt = __(
				'This image has an empty alt attribute'
			);
		}

		return (
			<Fragment>

				{ controls }

					<figure className={ classes }>

						<ImageSize src={ url } dirtynessTrigger={ align }>
							{ ( sizes ) => {
								const {
									imageWidthWithinContainer,
									imageHeightWithinContainer,
									imageWidth,
									imageHeight,
								} = sizes;


								if( url ) {

									return (									

										<div style={ { width, height } }>

											<InspectorControls>
												<PanelBody title={ __( 'Artwork settings', 'artworker' ) }>
													<FocalPointPicker
														label={ __( 'Focal point picker' ) }
														url={ url }
														value={ focalPoint }
														onChange={ ( newFocalPoint ) =>
															setAttributes( {
																focalPoint: newFocalPoint,
															} )
														}
													/>
													<TextareaControl
														label={ __( 'Alt text (alternative text)' ) }
														value={ alt }
														onChange={ this.updateAlt }
													/>
													<ImageSizeControl
														onChangeImage={ this.updateImage }
														onChange={ ( value ) => setAttributes( value ) }
														slug={ sizeSlug }
														width={ width }
														height={ height }
														imageSizeOptions={ imageSizeOptions }
														isResizable={ isResizable }
														imageWidth={ imageWidth }
														imageHeight={ imageHeight }
													/>
												</PanelBody>
											</InspectorControls>
											<InspectorAdvancedControls>
												<TextControl
													label={ __( 'Title attribute' ) }
													value={ title || '' }
													onChange={ this.onSetTitle }
												/>
											</InspectorAdvancedControls>

											<img
												src={ url }
												alt={ defaultedAlt }
												onClick={ this.onImageClick }
												onError={ () =>
													this.onImageError( url )
												}
											/>
											{ isBlobURL( url ) && <Spinner /> }
										</div>
									);

								} else {

									return (

										<div></div>

									);

								}

							} }
						</ImageSize>

						{ ( ! RichText.isEmpty( caption ) || isSelected ) && (
							<RichText
								tagName="figcaption"
								placeholder={ __( 'Write caption…' ) }
								value={ caption }
								unstableOnFocus={ this.onFocusCaption }
								onChange={ ( value ) =>
									setAttributes( { caption: value } )
								}
								isSelected={ this.state.captionFocused }
								inlineToolbar
							/>
						) }

						{ mediaPlaceholder }

					</figure>

			</Fragment>
		);

	}

}

export default compose([

	withDispatch( ( dispatch, { noticeOperations }, { select } ) => {
		const { toggleSelection } = dispatch( 'core/block-editor' );
		const { editPost } = dispatch( 'core/editor' );
		const { removeEditorPanel } = dispatch( 'core/edit-post' );

		//removeEditorPanel( 'featured-image' );

		return {
			onResizeStart: () => toggleSelection( false ),
			onResizeStop: () => toggleSelection( true ),
			onUpdateImage( image ) {
				editPost( { featured_media: image.id } );
			},
			onDropImage( filesList ) {
				select( 'core/block-editor' )
					.getSettings()
					.mediaUpload( {
						allowedTypes: [ 'image' ],
						filesList,
						onFileChange( [ image ] ) {
							editPost( { featured_media: image.id } );
						},
						onError( message ) {
							noticeOperations.removeAllNotices();
							noticeOperations.createErrorNotice( message );
						},
					} );
			},
			onRemoveImage() {
				editPost( { featured_media: 0 } );
			},
		};
	} ),
	withSelect( ( select, props ) => {
		const { getMedia, getPostType } = select( 'core' );
		const { getCurrentPostId, getEditedPostAttribute } = select(
			'core/editor'
		);
		const featuredImageId = getEditedPostAttribute( 'featured_media' );
		const { getSettings } = select( 'core/block-editor' );
		const {
			attributes: { id, data },
			isSelected,
		} = props;
		const { mediaUpload, imageSizes, isRTL, maxWidth } = getSettings();

	    var title;

	    if (typeof select("core/editor").getPostEdits().title !== 'undefined') {
	        title = select("core/editor").getPostEdits().title;
	    } else {
	        title = select("core/editor").getCurrentPost().title;
	    }

		return {
			image: featuredImageId ? getMedia( featuredImageId ) : null,
			currentPostId: getCurrentPostId(),
			postType: getPostType( getEditedPostAttribute( 'type' ) ),
			featuredImageId,
			maxWidth,
			isRTL,
			imageSizes,
			mediaUpload,
			title
		};
	} ),
	withNotices,
])( Artwork )
