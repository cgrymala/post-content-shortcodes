/**
 * BLOCK: post-content-shortcodes/list
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

import {getAttributeValue} from '../common.js';

const {__} = wp.i18n; // Import __() from wp.i18n
const {InspectorControls} = wp.blockEditor;
const {PanelBody, CheckboxControl, BaseControl, TextControl, CustomSelectControl, RadioControl} = wp.components;
const {useState} = wp.element;
const {withState} = wp.compose;
const {registerBlockType} = wp.blocks; // Import registerBlockType() from wp.blocks
const {ServerSideRender} = wp.editor;

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
registerBlockType('ten321--post-content-shortcodes--blocks/list', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __('PCS Post List Block'), // Block title.
    icon: 'list-view', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __('Post Content Shortcodes'),
        __('Content List'),
        __('Post List'),
        __('Multisite'),
        __('excerpt'),
    ],
    transforms: {
        from: [
            {
                type: 'block',
                blocks: ['core/shortcode'],
                isMatch: function ({text}) {
                    return /^\[post-list /.test(text);
                },
                transform: ({text}) => {
                    let atts = {};
                    for (let i in ten321__post_content_shortcodes__blocks__list.reg_args.transforms.attributes) {
                        if (!ten321__post_content_shortcodes__blocks__list.reg_args.transforms.attributes.hasOwnProperty(i)) {
                            continue;
                        }

                        let tmp = getAttributeValue('post-list', i, text);
                        if (tmp !== null) {
                            atts[i] = tmp;
                        }
                    }

                    return wp.blocks.createBlock('ten321--post-content-shortcodes--blocks/list', atts);
                }
            }
        ]
    },
    attributes: ten321__post_content_shortcodes__blocks__list.reg_args.attributes,

    edit: (props) => {
        const blogOptions = ten321__post_content_shortcodes__blocks__list.blogList;
        if (blogOptions[0].key !== 0) {
            blogOptions.unshift({
                key: 0,
                name: '-- Please select a blog --',
            });
        }

        const orderByOptions = [
            {key: 'post_title', name: __('Title', 'post-content-shortcodes')},
            {key: 'date', name: __('Post Date', 'post-content-shortcodes')},
            {key: 'menu_order', name: __('Menu/Page order', 'post-content-shortcodes')},
            {key: 'ID', name: __('Post ID', 'post-content-shortcodes')},
            {key: 'author', name: __('Author', 'post-content-shortcodes')},
            {key: 'modified', name: __('Post Modification Date', 'post-content-shortcodes')},
            {key: 'parent', name: __('Post Parent ID', 'post-content-shortcodes')},
            {key: 'comment_count', name: __('Number of Comments', 'post-content-shortcodes')},
            {key: 'rand', name: __('Random', 'post-content-shortcodes')},
        ];

        const orderOptions = [
            {value: 'asc', label: __('Ascending', 'post-content-shortcodes')},
            {value: 'desc', label: __('Descending', 'post-content-shortcodes')},
        ];

        const {
            className,
            isSelected,
            attributes: {
                show_title,
                show_image,
                blog,
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
                post_type,
                post_parent,
                tax_name,
                tax_term,
                orderby,
                order,
            },
            setAttributes,
        } = props;

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
                    label={__('List posts from which blog?', 'post-content-shortcodes')}
                    options={blogOptions}
                    onChange={(newValue, props) => {
                        setAttributes({blog: newValue.selectedItem});
                        return setFontSize(newValue);
                    }}
                    value={blogOptions.find((option) => option.key === fontSize.key)}
                />
            );
        }

        function getFieldPostType() {
            return (
                <TextControl
                    label={__('Post type:', 'post-content-shortcodes')}
                    onChange={(newVal) => {
                        setAttributes({post_type: parseInt(newVal)});
                    }}
                    value={post_type}
                />
            );
        }

        function getFieldPostParent() {
            return (
                <div>
                    <TextControl
                        label={__('Post parent ID:', 'post-content-shortcodes')}
                        onChange={(newVal) => {
                            setAttributes({post_parent: parseInt(newVal)});
                        }}
                        value={post_parent}
                    />
                    <p className="field-note">
                        <em>{__('Leave this blank (or set to 0) to retrieve and display all posts that match the other criteria specified.', 'post-content-shortcodes')}</em>
                    </p>
                </div>
            );
        }

        function getFieldTaxonomySlug() {
            <div>
                <TextControl
                    label={__('Taxonomy Slug:', 'post-content-shortcodes')}
                    onChange={(newVal) => {
                        setAttributes({tax_name: parseInt(newVal)});
                    }}
                    value={tax_name}
                />
                <p className="field-note">
                    <em>{__('If you would like to limit posts to a specific set of terms within a taxonomy, please enter the taxonomy slug above (e.g. "category", "tag", etc.)', 'post-content-shortcodes')}</em>
                </p>
            </div>
        }

        function getFieldTermSlug() {
            <div>
                <TextControl
                    label={__('Term Slugs:', 'post-content-shortcodes')}
                    onChange={(newVal) => {
                        setAttributes({tax_term: parseInt(newVal)});
                    }}
                    value={tax_term}
                />
                <p className="field-note">
                    <em>{__('If you would like to limit posts to a specifc set of terms within a taxonomy, please enter a space-separated list of either the term slugs or the term IDs', 'post-content-shortcodes')}</em>
                </p>
            </div>
        }

        function getPostSelectionPanel() {
            return (
                <PanelBody title={__('Post Selection', 'post-content-shortcodes')}>
                    {getFieldBlog()}
                    {getFieldPostType()}
                    {getFieldPostParent()}
                    {getFieldTaxonomySlug()}
                    {getFieldTermSlug()}
                </PanelBody>
            );
        }

        function getPostAttributesPanel() {
            return (
                <PanelBody title={__('Post Attributes', 'post-content-shortcodes')}>
                    {getFieldOrderBy()}
                    {getFieldOrder}
                </PanelBody>
            )
        }

        function getFieldOrderBy() {
            let selected = orderByOptions[0];

            if (typeof orderby !== 'undefined' && orderby !== null) {
                console.log('Setting a pre-selected option as the post order');
                selected = orderby;
            }

            const [fontSize, setFontSize] = useState(selected);

            return (
                <CustomSelectControl
                    label={__('Sort posts by:', 'post-content-shortcodes')}
                    options={orderByOptions}
                    onChange={(newValue, props) => {
                        setAttributes({orderby: newValue.selectedItem});
                        return setFontSize(newValue);
                    }}
                    value={orderByOptions.find((option) => option.key === fontSize.key)}
                />
            );
        }

        const getFieldOrder = withState( {
            option: 'asc',
        } )( ( { option, setState } ) => (
            <RadioControl
                label={__('In which order?', 'post-content-shortcodes')}
                selected={ option }
                options={ orderOptions }
                onChange={ ( option ) => { setState( { option } ) } }
            />
        ) );

        return (
            <div className={className}>
                {isSelected &&
                <div>
                    {getPostSelectionPanel()}
                    {getPostAttributesPanel()}
                    <InspectorControls>
                        <p>Placeholder</p>
                    </InspectorControls>
                </div>
                }
            </div>
        );
    }
});
