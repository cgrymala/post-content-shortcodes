const { URLInputButton, URLInput, InspectorControls } = wp.blockEditor;
const { FormToggle, PanelBody, CheckboxControl, BaseControl, TextControl } = wp.components;
const { useState } = wp.element;
const { withState } = wp.compose;

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

export const getFieldShowTitle = function(val) {
    return withState( {
        checked: val,
    } )( ( { checked, setState } ) => (
        <FormToggle
            checked={ checked }
            onChange={ () => setState( state => ( { show_title: ! state.checked } ) ) }
        />
    ) );
}
