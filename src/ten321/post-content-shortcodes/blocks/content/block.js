/**
 * BLOCK: post-content-shortcodes/content
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

import {
    getAttributeValue,
    getFieldShowTitle,
    PCSGetFields,
    getImagePanel,
    getExcerptPanel,
    getFieldShowComments,
    getFieldReadMore, getFieldShortcodes, getFieldStripHTML, getFieldShowAuthor, getFieldShowDate
} from '../common.js';

const {__} = wp.i18n; // Import __() from wp.i18n
const {URLInputButton, URLInput, InspectorControls} = wp.blockEditor;
const {PanelBody, CheckboxControl, BaseControl, TextControl, CustomSelectControl, NumberControl} = wp.components;
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
        const blogOptions = ten321__post_content_shortcodes__blocks__content.blogList;
        if (blogOptions[0].key !== 0) {
            blogOptions.unshift({
                key: 0,
                name: '-- Please select a blog --',
            });
        }

        console.log(props.attributes);
        console.log(ten321__post_content_shortcodes__blocks__content.reg_args.attributes);

        const {
            className,
            isSelected,
            attributes: {
                show_title,
                show_image,
                blog,
                id,
                post_name,
                image_width,
                image_height,
                show_comments,
                show_excerpt,
                excerpt_length,
                read_more,
                shortcodes,
                strip_html,
                show_author,
                show_date,
                link_image,
            },
            setAttributes,
        } = props;

        console.log(blogOptions);
        console.log(blog);

        function getDisplayPanel() {
            return (
                <PanelBody title={__('Display Options', 'post-content-shortcodes')}>
                    {getFieldShowTitle(props)}
                    {getFieldShowComments(props)}
                    {getExcerptPanel(props)}
                    {getFieldReadMore(props)}
                    {getFieldShortcodes(props)}
                    {getFieldStripHTML(props)}
                    {getFieldShowAuthor(props)}
                    {getFieldShowDate(props)}
                </PanelBody>
            )
        }

        function getFieldBlog() {
            let selected = blogOptions[0];

            if (typeof blog !== 'undefined' && blog !== null) {
                console.log('Setting a pre-selected option as blog');
                selected = blog;
            } else if (typeof ten321__post_content_shortcodes__blocks__content.currentBlog !== 'undefined') {
                console.log('Setting the "current blog" as blog');
                console.log(ten321__post_content_shortcodes__blocks__content.currentBlog);
                selected = ten321__post_content_shortcodes__blocks__content.currentBlog;
            }

            const [fontSize, setFontSize] = useState(selected);

            return (
                <CustomSelectControl
                    label={__('Show post from which blog?', 'post-content-shortcodes')}
                    options={blogOptions}
                    onChange={(newValue, props) => {
                        setAttributes({blog: newValue.selectedItem});
                        return setFontSize(newValue);
                    }}
                    value={blogOptions.find((option) => option.key === fontSize.key)}
                />
            );
        }

        function getFieldBlogSelect() {
            if (typeof ten321__post_content_shortcodes__blocks__content.blogList === 'undefined') {
                return;
            }

            let selected = blogOptions[0];

            if (typeof blog !== 'undefined' && blog !== null) {
                console.log('Setting a pre-selected option as blog');
                selected = blog;
            } else if (typeof ten321__post_content_shortcodes__blocks__content.currentBlog !== 'undefined') {
                console.log('Setting the "current blog" as blog');
                selected = ten321__post_content_shortcodes__blocks__content.currentBlog;
            }

            console.log(selected);

            const [fontSize, setFontSize] = useState(selected);

            return (
                <CustomSelectControl
                    label={__('Show post from which blog?', 'post-content-shortcodes')}
                    options={blogOptions}
                    onChange={(newValue, props) => {
                        setAttributes({blog: newValue.selectedItem});
                        return setFontSize(newValue);
                    }}
                    value={blogOptions.find((option) => option.key === fontSize.key)}
                />
            );
        }

        function getFieldPostID() {
            return (
                <TextControl
                    label={__('Post ID:', 'post-content-shortcodes')}
                    onChange={(newVal) => {
                        setAttributes({id: parseInt(newVal)});
                    }}
                    value={id}
                />
            );
        }

        function getFieldPostName() {
            return (
                <TextControl
                    label={__('Post Name (slug):', 'post-content-shortcodes')}
                    className="widefat"
                    onChange={(newVal) => {
                        setAttributes({post_name: newVal});
                    }}
                    value={post_name}
                />
            );
        }

        return (
            <div className={props.className}>
                <p>This will eventually be a PCS Content Block</p>
                {isSelected &&
                <div className="editor-controls">
                    {getFieldBlog()}
                    <PanelBody title={__('Post Selection', 'post-content-shortcodes')}>
                        {getFieldPostID()}
                        <p>OR</p>
                        {getFieldPostName()}
                    </PanelBody>
                    <InspectorControls>
                        {getImagePanel(props)}
                        {getDisplayPanel()}
                    </InspectorControls>
                </div>
                }
            </div>
        );
    }
});
