<?php 

# Widget Class
# Builds widgets for templates

class Widgets {

    static $markup = [];
    static $form = '';
    static $invalid = '';

    static function build_widgets($list)
    {
        isset($list) or $list = [];
        is_array($list) or $list = [];

        $formatted_markup = [];
        foreach ($list as $name => $type) 
        {
            $formatted_markup[$name] = self::get_markup($type, $name);
        }
        
        # build form
        $form = self::$form;
        $formatted_form = '';
        foreach ($formatted_markup as $name => $fm) 
        {
            # make label
            $unf_label = preg_split('/_/i', $name);
            $formatted_label = '';
            foreach ($unf_label as $unf_label_part) 
            {
                $formatted_label .= ucfirst($unf_label_part).' ';
            }

            $formatted_form .= sprintf(
                                    $form, $fm, 
                                    trim($formatted_label));
        }
        return $formatted_form;
    }

    static function get_markup($type, $name, $id=null)
    {   
        isset($id) or $id = $name;
        if (isset(self::$markup[$type]))
            $markup = self::$markup[$type];
        isset($markup) or $markup = self::$invalid;
        return sprintf($markup, $name, $id);
    }

}

function _serializer_markup()
{
    # turn on output buffering
    ob_start();
?>
    <input 
        name="%1$s" id="%2$s" 
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

function _commaseparator_markup()
{
    # turn on output buffering
    ob_start();
?>
    <input 
        name="%1$s" id="%2$s" 
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
Widgets::$invalid = '<span data-widget="invalid_widget"></span>';
Widgets::$markup = [
    'input' => '<input name="%1$s" id="%2$s">',
    'textarea' => '<textarea name="%1$s" id="%2$s"></textarea>',
    'serializer' => _serializer_markup(),
    'commaseparator' => _commaseparator_markup(),

];
Widgets::$form = _widget_form();


