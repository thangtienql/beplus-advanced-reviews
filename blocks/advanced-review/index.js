/**
 * Editor script for the Advanced Reviews block.
 * WordPress loads this via editorScript in block.json.
 *
 * @package BePlusAdvancedReviews
 */

( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var ToggleControl = wp.components.ToggleControl;
	var RangeControl = wp.components.RangeControl;
	var el = wp.element.createElement;
	var __ = wp.i18n.__;

	var blockJson = {
		apiVersion: 3,
		name: 'beplus-advanced-reviews/advanced-review',
		title: 'Advanced Reviews',
		category: 'beplus-advanced-reviews',
		icon: 'star-filled',
		description: 'Modern WooCommerce product reviews with images, star distribution, filtering, and load more.',
		textdomain: 'beplus-advanced-reviews',
		attributes: {
			showDistribution: { type: 'boolean', default: true },
			showFilterBar: { type: 'boolean', default: true },
			showSubmitForm: { type: 'boolean', default: true },
			showImages: { type: 'boolean', default: true },
			showAvatar: { type: 'boolean', default: true },
			reviewsPerLoad: { type: 'number', default: 10 },
			enableLazyLoad: { type: 'boolean', default: true }
		},
		supports: { html: false, align: ['wide', 'full'] }
	};

	registerBlockType( blockJson.name, {
		title: blockJson.title,
		icon: blockJson.icon,
		category: blockJson.category,
		attributes: blockJson.attributes,
		supports: blockJson.supports,
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( {
				className: 'beplus-advanced-reviews beplus-advanced-reviews--editor-preview'
			} );

			return el(
				wp.element.Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Display Options', 'beplus-advanced-reviews' ) },
						el( ToggleControl, {
							label: __( 'Show star distribution', 'beplus-advanced-reviews' ),
							checked: attributes.showDistribution,
							onChange: function ( val ) { setAttributes( { showDistribution: val } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Show filter bar', 'beplus-advanced-reviews' ),
							checked: attributes.showFilterBar,
							onChange: function ( val ) { setAttributes( { showFilterBar: val } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Show submit form', 'beplus-advanced-reviews' ),
							checked: attributes.showSubmitForm,
							onChange: function ( val ) { setAttributes( { showSubmitForm: val } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Show images in reviews', 'beplus-advanced-reviews' ),
							checked: attributes.showImages,
							onChange: function ( val ) { setAttributes( { showImages: val } ); }
						} ),
						el( ToggleControl, {
							label: __( 'Show reviewer avatar', 'beplus-advanced-reviews' ),
							checked: attributes.showAvatar,
							onChange: function ( val ) { setAttributes( { showAvatar: val } ); }
						} ),
						el( RangeControl, {
							label: __( 'Reviews per load', 'beplus-advanced-reviews' ),
							value: attributes.reviewsPerLoad,
							onChange: function ( val ) { setAttributes( { reviewsPerLoad: val } ); },
							min: 1,
							max: 50
						} ),
						el( ToggleControl, {
							label: __( 'Enable lazy load', 'beplus-advanced-reviews' ),
							checked: attributes.enableLazyLoad,
							onChange: function ( val ) { setAttributes( { enableLazyLoad: val } ); }
						} )
					)
				),
				el( 'div', blockProps,
					el( 'div', { className: 'beplus-advanced-reviews__editor-placeholder' },
						el( 'span', { className: 'dashicons dashicons-star-filled' } ),
						el( 'h3', null, __( 'Advanced Reviews', 'beplus-advanced-reviews' ) ),
						el( 'p', null, __( 'Reviews will display here on the product page.', 'beplus-advanced-reviews' ) )
					)
				)
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
