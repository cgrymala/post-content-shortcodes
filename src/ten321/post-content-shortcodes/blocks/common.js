const {__} = wp.i18n; // Import __() from wp.i18n
const {URLInputButton, URLInput, InspectorControls} = wp.blockEditor;
const {PanelBody, CheckboxControl, BaseControl, TextControl} = wp.components;
const {useState} = wp.element;
const {withState} = wp.compose;

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
    re = new RegExp(`\\[${tag}[^\\]]* ${att}=([^\\s]*)[\\s|\\]]`, 'im');
    result = content.match(re);
    if (result != null && result.length > 0)
        return result[1];
    return null;
};

export class PCSGetFields {
    constructor(props) {
        this.props = props;
        this.attributes = this.props.attributes;
        this.setAttributes = this.props.setAttributes;
        this.CheckboxControl = wp.components.CheckboxControl;
    }

    show_title() {
        let checked = false;
        if (typeof this.attributes.show_title !== 'undefined') {
            checked = this.attributes.show_title;
        }

        const [isChecked, setChecked] = useState(checked);

        return (
            <this.CheckboxControl
                label={__('Display the item title?', 'ten321/post-content-shortcodes')}
                checked={isChecked}
                onChange={(newValue) => {
                    setChecked(newValue);
                    this.setAttributes({show_title: newValue});
                }}
                name="show_title"
            />
        );
    }
}

export const getFieldShowTitle = function (props) {
    const {
        className,
        isSelected,
        attributes: {show_title},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof show_title !== 'undefined' && show_title !== null) {
        checked = show_title;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Display the post title?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({show_title: newValue});
            }}
            name="show_title"
        />
    );
}

function getFieldShowImage(props) {
    const {
        className,
        isSelected,
        attributes: {show_image},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof show_image !== 'undefined') {
        checked = show_image;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Display the featured image with the post?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({show_image: newValue});
            }}
            name="show_image"
        />
    );
}

function getFieldImageDimensions(props) {
    return (
        <PanelBody title={__('Image Dimensions', 'post-content-shortcodes')}>
            {getFieldImageWidth(props)} <span>x</span> {getFieldImageHeight(props)}
        </PanelBody>
    );
}

function getFieldImageWidth(props) {
    const {
        className,
        attributes: {image_width},
        setAttributes,
    } = props;

    let val = '';
    if (typeof image_width !== 'undefined' && image_width !== null) {
        val = image_width;
    }

    return (
        <TextControl
            label={__('Width: ', 'post-content-shortcodes')}
            onChange={(newVal) => {
                const val = parseInt(newVal);
                setAttributes({image_width: isNaN(val) ? newVal : val});
            }}
            value={val}
        />
    );
}

function getFieldImageHeight(props) {
    const {
        className,
        attributes: {image_height},
        setAttributes,
    } = props;

    let val = '';
    if (typeof image_height !== 'undefined' && image_height !== null) {
        val = image_height;
    }

    return (
        <TextControl
            label={__('Height: ', 'post-content-shortcodes')}
            onChange={(newVal) => {
                const val = parseInt(newVal);
                setAttributes({image_height: isNaN(val) ? newVal : val});
            }}
            value={val}
        />
    );
}

function getFieldLinkImage(props) {
    const {
        className,
        isSelected,
        attributes: {link_image},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof link_image !== 'undefined') {
        checked = link_image;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Wrap the thumbnail in a link to the post?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({link_image: newValue});
            }}
            name="link_image"
        />
    );
}

export const getImagePanel = function (props) {
    return (
        <PanelBody title={__('Image Options', 'post-content-shortcodes')}>
            {getFieldShowImage(props)}
            {getFieldImageDimensions(props)}
            {getFieldLinkImage(props)}
        </PanelBody>
    );
}

export const getFieldShowComments = function (props) {
    const {
        className,
        isSelected,
        attributes: {show_comments},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof show_comments !== 'undefined') {
        checked = show_comments;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Display comments with the post?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({show_comments: newValue});
            }}
            name="show_comments"
        />
    );
}

function getFieldShowExcerpt(props) {
    const {
        className,
        isSelected,
        attributes: {show_excerpt},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof show_excerpt !== 'undefined') {
        checked = show_excerpt;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Display an excerpt of the post content?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({show_excerpt: newValue});
            }}
            name="show_excerpt"
        />
    );
}

function getFieldExcerptLength(props) {
    const {
        className,
        attributes: {excerpt_length},
        setAttributes,
    } = props;

    let val = 0;
    if (typeof excerpt_length !== 'undefined' && excerpt_length !== null) {
        val = excerpt_length;
    }

    return (
        <TextControl
            label={__('Limit the excerpt to how many words: ', 'post-content-shortcodes')}
            onChange={(newVal) => {
                const val = parseInt(newVal);
                setAttributes({excerpt_length: isNaN(val) ? newVal : val});
            }}
            value={val}
        />
    );
}

export const getExcerptPanel = function (props) {
    return (
        <PanelBody title={__('Excerpt Options', 'post-content-shortcodes')}>
            {getFieldShowExcerpt(props)}
            {getFieldExcerptLength(props)}
            <p><em>{__('Leave set to 0 if you do not want the excerpts limited.', 'post-content-shortcodes')}</em></p>
        </PanelBody>
    )
}

export const getFieldReadMore = function (props) {
    const {
        className,
        isSelected,
        attributes: {read_more},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof read_more !== 'undefined') {
        checked = read_more;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Include a "Read more" link?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({read_more: newValue});
            }}
            name="read_more"
        />
    );
}

export const getFieldShortcodes = function (props) {
    const {
        className,
        isSelected,
        attributes: {shortcodes},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof shortcodes !== 'undefined') {
        checked = shortcodes;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Allow shortcodes inside of the excerpt?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({shortcodes: newValue});
            }}
            name="shortcodes"
        />
    );
}

export const getFieldStripHTML = function (props) {
    const {
        className,
        isSelected,
        attributes: {strip_html},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof strip_html !== 'undefined') {
        checked = strip_html;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Attempt to strip all HTML out of the excerpt?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({strip_html: newValue});
            }}
            name="strip_html"
        />
    );
}

export const getFieldShowAuthor = function (props) {
    const {
        className,
        isSelected,
        attributes: {show_author},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof show_author !== 'undefined') {
        checked = show_author;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Display the author\'s name?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({show_author: newValue});
            }}
            name="show_author"
        />
    );
}

export const getFieldShowDate = function (props) {
    const {
        className,
        isSelected,
        attributes: {show_date},
        setAttributes,
    } = props;

    let checked = false;
    if (typeof show_date !== 'undefined') {
        checked = show_date;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__('Display the publication date?', 'post-content-shortcodes')}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({show_date: newValue});
            }}
            name="show_date"
        />
    );
}
