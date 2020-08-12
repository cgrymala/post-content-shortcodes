/**
 * BLOCK: post-content-shortcodes/list
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks

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
registerBlockType( 'ten321--post-content-shortcodes--blocks/list', {
    // Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
    title: __( 'PCS Post List Block' ), // Block title.
    icon: 'list-view', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
    keywords: [
        __( 'Post Content Shortcodes' ),
        __( 'Content List' ),
        __( 'Post List' ),
        __( 'Multisite' ),
        __( 'excerpt' ),
    ],
    transforms: ten321__post_content_shortcodes__blocks__list.transforms,
    attributes: ten321__post_content_shortcodes__blocks__list.attributes,

    edit: (props) => {
        return (
            <p>This will eventually be a PCS List Block</p>
        );
    }
} );
