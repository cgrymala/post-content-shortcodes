/**
 * BLOCK: post-content-shortcodes/content
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

import {getAttributeValue, getFieldShowTitle, PCSGetFields} from '../common.js';

const {__} = wp.i18n; // Import __() from wp.i18n
const {URLInputButton, URLInput, InspectorControls} = wp.blockEditor;
const {PanelBody, CheckboxControl, BaseControl, TextControl} = wp.components;
const {useState} = wp.element;
const {withState} = wp.compose;
const {registerBlockType} = wp.blocks; // Import registerBlockType() from wp.blocks

/**
 * Register: aa Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType('ten321--post-content-shortcodes--blocks/content', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __('PCS Post Content Block'), // Block title.
    icon: 'format-aside', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __('Post Content Shortcodes'),
        __('Multisite'),
        __('excerpt'),
    ],
    transforms: {
        from: [
            {
                type: 'block',
                blocks: ['core/shortcode'],
                isMatch: function ({text}) {
                    return /^\[post-content /.test(text);
                },
                transform: ({text}) => {
                    let atts = {};
                    for (let i in ten321__post_content_shortcodes__blocks__content.reg_args.transforms.attributes) {
                        if (!ten321__post_content_shortcodes__blocks__content.reg_args.transforms.attributes.hasOwnProperty(i)) {
                            continue;
                        }

                        let tmp = getAttributeValue('post-content', i, text);
                        if (tmp !== null) {
                            atts[i] = tmp;
                        }
                    }

                    return wp.blocks.createBlock('ten321--post-content-shortcodes--blocks/content', atts);
                }
            }
        ]
    },
    attributes: ten321__post_content_shortcodes__blocks__content.reg_args.attributes,

    edit: (props) => {
        const {
            className,
            isSelected,
            attributes,
            setAttributes,
        } = props;

        const fields = new PCSGetFields(props);

        function getDisplayPanel() {
            return (
                <PanelBody title={__('Display Settings', 'ten321/post-content-shortcodes')}>
                    {getFieldShowTitle(props)}
                </PanelBody>
            );
        }

        return (
            <div>
                <p>This will eventually be a PCS Content Block</p>
                {isSelected &&
                <InspectorControls>
                    {getDisplayPanel()}
                </InspectorControls>
                }
            </div>
        );
    }
});
