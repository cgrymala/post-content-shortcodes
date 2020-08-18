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
    if (typeof show_title !== 'undefined') {
        checked = show_title;
    }

    const [isChecked, setChecked] = useState(checked);

    return (
        <CheckboxControl
            label={__( 'Display the post title?', 'post-content-shortcodes' )}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({show_title: newValue});
            }}
            name="show_title"
        />
    );
}

export const getFieldShowImage = function (props) {
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
            label={__( 'Display the featured image with the post?', 'post-content-shortcodes' )}
            checked={isChecked}
            onChange={(newValue, props) => {
                setChecked(newValue);
                setAttributes({show_image: newValue});
            }}
            name="show_image"
        />
    );
}

export const getFieldImageDimensions = function(props) {
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

    let current = 0;
    if (typeof image_width !== 'undefined') {
        current = image_width;
    }

    const [value, setValue] = useState(current);

    return (
        <TextControl
            label={__( 'Width: ', 'post-content-shortcodes' )}
            onChange={setValue}
            value={value}
        />
    );
}

function getFieldImageHeight(props) {
    const {
        className,
        attributes: {image_height},
        setAttributes,
    } = props;

    let current = 0;
    if (typeof image_height !== 'undefined') {
        current = image_height;
    }

    const [value, setValue] = useState(current);

    return (
        <TextControl
            label={__( 'Height: ', 'post-content-shortcodes' )}
            onChange={setValue}
            value={value}
        />
    );
}
