<?php 

namespace library;

require_once 'constants.php';

# Widgetbuilder Class
# Builds widgets for templates

class Widgetbuilder {

    static $markup = [];
    static $form = '';
    static $invalid = '';

    static function build_widgets($list)
    {
        isset($list) or $list = [];
        is_array($list) or $list = [];

        # build form
        $form = self::$form;
        $formatted_form = '';

        foreach ($list as $name => $attr) 
        {
            $elem = self::get_markup(
                $attr['widget'], $name, null, $attr['initial']
            );
            $formatted_form .= sprintf($form, $elem, $attr['label']);
        }
        return $formatted_form;
    }

    static function get_markup($type, $name, $id=null, $value=null)
    {   
        isset($id) or $id = $name;
        if (isset(self::$markup[$type])) 
        {
            $markup = self::$markup[$type];
        }
        isset($markup) 
            or $markup = self::$invalid;

        return sprintf($markup, $name, $id, htmlentities($value));
    }

}

function _serializer_markup()
{
    # turn on output buffering
    ob_start();
?>
    <input 
        name="%1$s" id="%2$s" value="%3$s"
        placeholder="Serializer" 
        data-widget="serializer">
    <span 
        class="widget-icon">
        <i 
            class="fa fa-sort-numeric-asc"></i>
    </span>
    <span class="cl-b"></span>
    <p class="special-syntax-doc">
        <span class="xh">Serializer Information</span>
        This field automatically assigns serial numbers to your 
        certificates by incrementing the specified value. You 
        can use this syntax <b>SOMEXYZ<i>[001+]</i>XY89Z</b>, 
        it will produce serial numbers such as SOMEXYZ<i>001</i>XY89Z, 
        SOMEXYZ<i>002</i>XY89Z, SOMEXYZ<i>003</i>XY89Z ...
    </p>
    <span class="cl-b"></span>
<?php 
    # get output buffer and clean
    return ob_get_clean();

}

function _csvfield_markup()
{
    # turn on output buffering
    ob_start();
?>
    <input 
        name="%1$s" id="%2$s" value="%3$s"
        placeholder="Comma Separated Inputs" 
        data-widget="serializer">
    <span 
        class="widget-icon">
        <i 
            class="fa fa-files-o"></i>
    </span>
    <span class="cl-b"></span>
    <p class="special-syntax-doc">
        <span class="xh">Comma Separated Inputs</span>
        This field takes in multiple values in the form of comma 
        separated values and automatically creates a certificate 
        for each value, useful when batch naming certificates. 
        For instance, you can enter values in the following format.
        <b>Abhishek Kumar, Animesh Rai, Aravindan Ve, </b>...
    </p>
    <span class="cl-b"></span>
<?php 
    # get output buffer and clean
    return ob_get_clean();

}

function _widget_form() 
{
    # turn on output buffering
    ob_start();
?>
<!-- widgetform -->
<ul class="form-list horiz f-width">
    <li>
        <span class="label">%2$s</span>
        <span class="content">%1$s</span>
    </li>
</ul>
<!-- /widgetform -->
<?php 
    # get output buffer and clean
    return ob_get_clean();
}

# initialize markup
Widgetbuilder::$invalid = '<span data-widget="invalid_widget"></span>';
Widgetbuilder::$markup = [
    CERTMKR_INPUT_FIELD => '<input name="%1$s" id="%2$s" value="%3$s">',
    CERTMKR_TEXTAREA_FIELD => '<textarea name="%1$s" id="%2$s" value="%3$s"></textarea>',
    CERTMKR_SERIAL_FIELD => _serializer_markup(),
    CERTMKR_CSV_FIELD => _csvfield_markup(),

];
Widgetbuilder::$form = _widget_form();


