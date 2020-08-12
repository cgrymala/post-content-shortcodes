/**
 * BLOCK: post-content-shortcodes/content
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

const {__} = wp.i18n; // Import __() from wp.i18n
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

/**
 * Get the value for a shortcode attribute, whether it's enclosed in double quotes, single
 * quotes, or no quotes.
 *
 * @param  {string} tag     The shortcode name
 * @param  {string} att     The attribute name
 * @param  {string} content The text which includes the shortcode
 *
 * @return {string}         The attribute value or an empty string.
 */
export const getAttributeValue = function (tag, att, content) {
    // In string literals, slashes need to be double escaped
    //
    //    Match  attribute="value"
    //    \[tag[^\]]*      matches opening of shortcode tag
    //    att="([^"]*)"    captures value inside " and "
    var re = new RegExp(`\\[${tag}[^\\]]* ${att}="([^"]*)"`, 'im');
    var result = content.match(re);
    if (result != null && result.length > 0)
        return result[1];

    //    Match  attribute='value'
    //    \[tag[^\]]*      matches opening of shortcode tag
    //    att="([^"]*)"    captures value inside ' and ''
    re = new RegExp(`\\[${tag}[^\\]]* ${att}='([^']*)'`, 'im');
    result = content.match(re);
    if (result != null && result.length > 0)
        return result[1];

    //    Match  attribute=value
    //    \[tag[^\]]*      matches opening of shortcode tag
    //    att="([^"]*)"    captures a shortcode value provided without
    //                     quotes, as in [me color=green]
    re = new RegExp(`\\[${tag}[^\\]]* ${att}=([^\\s]*)\\s`, 'im');
    result = content.match(re);
    if (result != null && result.length > 0)
        return result[1];
    return null;
};

let transformArgs = {};
for (let i in ten321__post_content_shortcodes__blocks__content.reg_args.transforms.attributes) {
    if (!ten321__post_content_shortcodes__blocks__content.reg_args.transforms.attributes.hasOwnProperty(i)) {
        continue;
    }
    transformArgs[i] = {
        type: ten321__post_content_shortcodes__blocks__content.reg_args.transforms.attributes[i].type,
        shortcode: attributes => attributes.named[i]
    }
}

ten321__post_content_shortcodes__blocks__content.reg_args.transforms.attributes = transformArgs;

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
                    console.log( 'Text found inside post-content shortcode:' );
                    console.log( text );

                    const atts = {
                        id: getAttributeValue('post-content', 'id', text),
                        post_type: getAttributeValue('post-content', 'post-type', text),
                        order: getAttributeValue('post-content', 'order', text),
                        orderby: getAttributeValue('post-content', 'orderby', text),
                        numberposts: getAttributeValue('post-content', 'numberposts', text),
                        blog: getAttributeValue('post-content', 'blog', text),
                        excerpt_length: getAttributeValue('post-content', 'excerpt_length', text)
                    };

                    const shortcodeAttributes = {};

                    for (let i in atts) {
                        if (!atts.hasOwnProperty(i)) {
                            continue;
                        }

                        if (atts[i] !== null) {
                            shortcodeAttributes[i] = atts[i];
                        }
                    }

                    return wp.blocks.createBlock('ten321--post-content-shortcodes--blocks/content', shortcodeAttributes);
                }
            }
        ]
    },
    attributes: ten321__post_content_shortcodes__blocks__content.reg_args.attributes,

    edit: (props) => {
        return (
            <p>This will be a PCS Post Content Block eventually</p>
        );
    }
});
