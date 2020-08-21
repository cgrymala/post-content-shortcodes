/**
 * BLOCK: post-content-shortcodes/list
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

import {getAttributeValue, getDisplayPanel, getImagePanel} from '../common.js';

const {__} = wp.i18n; // Import __() from wp.i18n
const {InspectorControls} = wp.blockEditor;
const {PanelBody, CheckboxControl, BaseControl, TextControl, CustomSelectControl, RadioControl} = wp.components;
const {useState} = wp.element;
const {withState} = wp.compose;
const {registerBlockType} = wp.blocks; // Import registerBlockType() from wp.blocks
const {ServerSideRender} = wp.editor;

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

const statusOptions = [
    {key: 'publish', name: __('Published', 'post-content-shortcodes')},
    {key: 'draft', name: __('Draft', 'post-content-shortcodes')},
    {key: 'pending', name: __('Pending Review', 'post-content-shortcodes')},
    {key: 'inherit', name: __('Inherited', 'post-content-shortcodes')},
];

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
                        if (i === 'blog') {
                            let blogList = ten321__post_content_shortcodes__blocks__list.blogList;
                            for (let b in blogList) {
                                if (!blogList.hasOwnProperty(b)) {
                                    continue;
                                }

                                if ((blogList[b].key * 1) !== (tmp * 1)) {
                                    continue;
                                }

                                atts[i] = blogList[b];
                            }
                        } else if (i === 'orderby') {
                            for (let b in orderByOptions) {
                                if (!orderByOptions.hasOwnProperty(b) || tmp === null) {
                                    continue;
                                }

                                if ((orderByOptions[b].key.toLowerCase() === tmp.toLowerCase())) {
                                    atts[i] = orderByOptions[b];
                                }
                            }
                        } else if (i === 'post_status') {
                            for (let b in statusOptions) {
                                if (!statusOptions.hasOwnProperty(b) || tmp === null) {
                                    continue;
                                }

                                if (statusOptions[b].key.toLowerCase() === tmp.toLowerCase()) {
                                    atts[i] = statusOptions[b];
                                }
                            }
                        } else if (tmp !== null) {
                            switch (i) {
                                case 'order' :
                                    atts[i] = tmp.toLowerCase() === 'desc' ? 'desc' : 'asc';
                                    break;
                                case 'show_title' :
                                case 'show_image' :
                                case 'show_comments' :
                                case 'show_excerpt' :
                                case 'read_more' :
                                case 'shortcodes' :
                                case 'strip_html' :
                                case 'show_author' :
                                case 'show_date' :
                                case 'link_image' :
                                case 'ignore_protected' :
                                    atts[i] = String(tmp).toLowerCase() === 'true' || Number(tmp) === 1;
                                    break;
                                case 'post_parent' :
                                case 'image_width' :
                                case 'image_height' :
                                case 'excerpt_length' :
                                case 'numberposts' :
                                    atts[i] = Number(tmp);
                                    break;
                                default :
                                    atts[i] = tmp;
                                    break;
                            }
                        }
                    }

                    return wp.blocks.createBlock('ten321--post-content-shortcodes--blocks/list', atts);
                }
            }
        ]
    },
    attributes: ten321__post_content_shortcodes__blocks__list.reg_args.attributes,

    edit: (props) => {
        console.log('List block attributes:');
        console.log(ten321__post_content_shortcodes__blocks__list.reg_args.attributes);

        const blogOptions = ten321__post_content_shortcodes__blocks__list.blogList;
        if (blogOptions[0].key !== 0) {
            blogOptions.unshift({
                key: 0,
                name: '-- Please select a blog --',
            });
        }

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
                numberposts,
                post_status,
                ignore_protected,
            },
            setAttributes,
        } = props;

        function getFieldBlog() {
            let selected = blogOptions[0];

            if (typeof blog !== 'undefined' && blog !== null) {
                console.log('Setting a pre-selected option as blog');
                selected = blog;
            } else if (typeof ten321__post_content_shortcodes__blocks__list.currentBlog !== 'undefined') {
                console.log('Setting the "current blog" as blog');
                console.log(ten321__post_content_shortcodes__blocks__list.currentBlog);
                selected = ten321__post_content_shortcodes__blocks__list.currentBlog;
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
            let val = '';
            if (typeof post_type !== 'undefined' && post_type !== null) {
                val = post_type;
            }

            return (
                <TextControl
                    label={__('Post type:', 'post-content-shortcodes')}
                    onChange={(newVal) => {
                        setAttributes({post_type: newVal});
                    }}
                    value={val}
                />
            );
        }

        function getFieldPostParent() {
            let val = 0;
            if (typeof post_parent !== 'undefined' && post_parent !== null) {
                val = post_parent;
            }
            val = isNaN(parseInt(val)) ? val : parseInt(val);

            return (
                <div>
                    <TextControl
                        label={__('Post parent ID:', 'post-content-shortcodes')}
                        onChange={(newVal) => {
                            const val = parseInt(newVal);
                            setAttributes({post_parent: isNaN(val) ? newVal : val});
                        }}
                        value={val}
                    />
                    <p className="field-note">
                        <em>{__('Leave this blank (or set to 0) to retrieve and display all posts that match the other criteria specified.', 'post-content-shortcodes')}</em>
                    </p>
                </div>
            );
        }

        function getFieldTaxonomySlug() {
            let val = '';
            if (typeof tax_name !== 'undefined' && tax_name !== null) {
                val = tax_name;
            }

            return (
                <div>
                    <TextControl
                        label={__('Taxonomy Slug:', 'post-content-shortcodes')}
                        onChange={(newVal) => {
                            setAttributes({tax_name: newVal});
                        }}
                        value={val}
                    />
                    <p className="field-note">
                        <em>{__('If you would like to limit posts to a specific set of terms within a taxonomy, please enter the taxonomy slug above (e.g. "category", "tag", etc.)', 'post-content-shortcodes')}</em>
                    </p>
                </div>
            );
        }

        function getFieldTermSlug() {
            let val = '';
            if (typeof tax_term !== 'undefined' && tax_term !== null) {
                val = tax_term;
            }

            return (
                <div>
                    <TextControl
                        label={__('Term Slugs:', 'post-content-shortcodes')}
                        onChange={(newVal) => {
                            setAttributes({tax_term: newVal});
                        }}
                        value={val}
                    />
                    <p className="field-note">
                        <em>{__('If you would like to limit posts to a specifc set of terms within a taxonomy, please enter a space-separated list of either the term slugs or the term IDs', 'post-content-shortcodes')}</em>
                    </p>
                </div>
            );
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
                    {getFieldOrder()}
                    {getFieldNumberPosts()}
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

        function getFieldOrder() {
            let selected = orderOptions[0];
            if (typeof order !== 'undefined' && order !== null) {
                selected = order;
            }
            return (
                <RadioControl
                    label={__('In which order?', 'post-content-shortcodes')}
                    options={orderOptions}
                    selected={selected}
                    onChange={(option) => {
                        setAttributes({order: option})
                    }}/>
            );
        }

        function getFieldNumberPosts() {
            let val = -1;
            if (typeof numberposts !== 'undefined' && numberposts !== null) {
                val = numberposts;
            }
            val = isNaN(parseInt(val)) ? val : parseInt(val);

            return (
                <div>
                    <TextControl
                        label={__('How many posts should be shown?', 'post-content-shortcodes')}
                        onChange={(newVal) => {
                            const val = parseInt(newVal);
                            setAttributes({numberposts: isNaN(val) ? newVal : val});
                        }}
                        value={val}
                    />
                    <p className="field-note">
                        <em>{__('Leave this set to -1 if you would like all posts to be retrieved and displayed.', 'post-content-shortcodes')}</em>
                    </p>
                </div>
            );
        }

        function getPostInspectorPanel() {
            return (
                <InspectorControls>
                    {getAdvancedPostPanel()}
                    {getImagePanel(props)}
                    {getDisplayPanel(props)}
                </InspectorControls>
            )
        }

        function getAdvancedPostPanel() {
            return (
                <PanelBody title={__('Advanced Post Selection', 'post-content-shortcodes')}>
                    {getFieldStatus()}
                    {getFieldProtected()}
                </PanelBody>
            );
        }

        function getFieldStatus() {
            let selected = statusOptions[0];

            if (typeof post_status !== 'undefined' && post_status !== null) {
                console.log('Setting a pre-selected option as the post status');
                selected = post_status;
            }

            const [fontSize, setFontSize] = useState(selected);

            return (
                <CustomSelectControl
                    label={__('Post status:', 'post-content-shortcodes')}
                    options={statusOptions}
                    onChange={(newValue, props) => {
                        setAttributes({post_status: newValue.selectedItem});
                        return setFontSize(newValue);
                    }}
                    value={statusOptions.find((option) => option.key === fontSize.key)}
                />
            );
        }

        function getFieldProtected() {
            let checked = false;
            if (typeof ignore_protected !== 'undefined') {
                checked = ignore_protected;
            }

            const [isChecked, setChecked] = useState(checked);

            return (
                <CheckboxControl
                    label={__('Exclude password-protected posts from the list?', 'post-content-shortcodes')}
                    checked={isChecked}
                    onChange={(newValue, props) => {
                        setChecked(newValue);
                        setAttributes({ignore_protected: newValue});
                    }}
                    name="ignore_protected"
                />
            );
        }

        function getListBlock() {
            return (
                <ServerSideRender
                    block="ten321--post-content-shortcodes--blocks/list"
                    attributes={{
                        show_title: !!show_title,
                        show_image: !!show_image,
                        blog: blog,
                        image_width: isNaN(parseInt(image_width)) ? 0 : parseInt(image_width),
                        image_height: isNaN(parseInt(image_height)) ? 0 : parseInt(image_height),
                        show_comments: !!show_comments,
                        show_excerpt: !!show_excerpt,
                        excerpt_length: isNaN(parseInt(excerpt_length)) ? 0 : parseInt(excerpt_length),
                        read_more: !!read_more,
                        shortcodes: !!shortcodes,
                        strip_html: !!strip_html,
                        show_author: !!show_author,
                        show_date: !!show_date,
                        link_image: !!link_image,
                        post_type: post_type,
                        post_parent: isNaN(parseInt(post_parent)) ? 0 : parseInt(post_parent),
                        tax_name: tax_name,
                        tax_term: tax_term,
                        orderby: orderby,
                        order: order === 'desc' ? 'desc' : 'asc',
                        numberposts: isNaN(parseInt(numberposts)) ? 0 : parseInt(numberposts),
                        post_status: post_status,
                        ignore_protected: !!ignore_protected,
                    }}
                />
            );
        }

        return (
            <div className={className}>
                {isSelected &&
                <div>
                    {getPostSelectionPanel()}
                    {getPostAttributesPanel()}
                    {getPostInspectorPanel()}
                </div>
                }{!isSelected &&
            <div>
                {getListBlock()}
            </div>
            }
            </div>
        );
    }
});
