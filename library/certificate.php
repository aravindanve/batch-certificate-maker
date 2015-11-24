<?php

namespace library;

require_once 'constants.php';
require_once 'batchmaker.php';

# Certificate class 
# Provides an interface to customize
# certificate templates.

abstract class Certificate
{
    # template path
    protected $base_path            = '';

    # template meta data
    protected $meta_name            = 'Generic Certificate';
    protected $meta_description     = 'This is a generic certificate class 
                                       which templates or themes can extend.';

    # template fields
    protected $define_fields = [
        'generic_input_field',
        'another_input_field'       => ['label' => 'Label'],
        'generic_multi_field'       => [  
          
            CERTMKR_CSV_FIELD, 
            'initial' => 'Value1, Value2',
        ],
        'generic_serializer'        => CERTMKR_SERIAL_FIELD,
    ];

    # print to pdf size (mm or cm or in)
    # format: WIDTHunit*HEIGHTunit
    public $print_size = '297mm*210mm';

    # print to pdf naming convention
    # allowed: meta_name, meta_description, <template_field_name>, timestamp
    # special: for serializer only [<serializer_field_name>|delta] 
    # shows only the incremented number range instead of whole serial number
    public $print_name = '[meta_name]-batch-[timestamp]';

    # private
    private $fields = [];
    private $defaults = [
        'widget'                    => CERTMKR_INPUT_FIELD,
    ];

    function __construct($base_path = null)
    {
        isset($base_path) or $base_path = '';

        $base_path = rtrim($base_path, '/').'/';
        $this->base_path = $base_path;
    }

    # public methods

    # returns [name, description]
    function get_meta()
    {
        return [
            'name'          => $this->meta_name,
            'description'   => $this->meta_description,
        ];
    }

    # returns pdf_save_as_name
    function get_print_name($data = null)
    {
        $print_name = $this->print_name;

        # match [<keywords>]
        $matches = [];
        preg_match_all('/\[\s*(\w*(?:\|delta)?)\s*\]/i', 
            $print_name, $matches); 

        # no keywords matched
        if (empty($matches[1])) 
        {
            return preg_replace(
                '/\s+/', '_', trim($print_name));
        }

        # recognized keywords
        $replace = [
            'meta_name', 
            'meta_description', 
            'timestamp'
        ];
        $replacement = [
            $this->meta_name, 
            $this->meta_description,
            date('Y-m-d_H-i-s', time()),
        ];

        $fields = $this->get_fields(false, $data);
        $delta_fields = [];
        foreach ($fields as $name => $attrs) 
        {
            $fields[$name]['value'] = $fields[$name]['initial'];
            if ($fields[$name]['widget'] == CERTMKR_SERIAL_FIELD) 
            {
                $delta_fields[] = $name.'|delta';
            }
        }

        $repl_for = null;

        # check if replacement values available
        foreach ($matches[1] as $keyword) 
        {
            if (in_array($keyword, $replace))
            {
                continue;
            }

            # try and match fields
            if(!is_array($repl_for)) 
            {
                $batch = Batchmaker::get_batch($fields);

                foreach ($batch as $fieldset) 
                {
                    foreach ($fieldset as $field => $val) 
                    {
                        is_array($repl_for) or 
                            $repl_for[$field] = [];

                        $repl_for[$field][] = $val;
                    }
                }
                foreach ($repl_for as &$_repl_for) 
                {
                    if (empty($_repl_for)) 
                    {
                        $_repl_for = '';
                    }
                    else
                    {
                        $__repl_for = [];
                        foreach ($_repl_for as $_repl_for_val) 
                        {
                            if (!in_array($_repl_for_val, $__repl_for))
                            {
                                $__repl_for[] = $_repl_for_val;
                            }
                        }
                        $_repl_for = $__repl_for;
                        /* // leaves gaps in array
                        $_repl_for = array_unique( 
                            $_repl_for); */

                        if (count($_repl_for) > 1) 
                        {
                            $_repl_for = $_repl_for[0].
                                '-'.$_repl_for[
                                    count($_repl_for) - 1];
                        }
                        else 
                        {
                            $_repl_for = reset($_repl_for);
                        }
                    }
                }
                unset($_repl_for);
            }

            if (array_key_exists($keyword, $repl_for)) 
            {
                $replace[] = $keyword;
                $replacement[] = $repl_for[$keyword];
                continue;
            }

            # check if serializer delta flag provided
            if (in_array($keyword, $delta_fields))
            {
                $real_keyword = preg_replace('/\|delta/', 
                    '', $keyword);

                if (!array_key_exists($real_keyword, $fields)) 
                {
                    continue;
                }
                $serials = Batchmaker::_proc__serialize(
                    $fields[$real_keyword]['initial'], 
                    count($batch), true);

                $serial_ranges = [];
                $serial_ranges_str = '';

                if (!empty($serials)) 
                {
                    foreach ($serials[0] as $_k => $_serial) 
                    {
                        $serial_ranges[$_k] = $_serial;
                    }

                    if (count($serials) > 1)
                    {
                        foreach ($serials[count($serials) - 1] 
                            as $_k => $_serial) 
                        {
                            isset($serial_ranges[$_k]) 
                                or $serial_ranges[$_k] = '';

                            empty($serial_ranges[$_k])
                                or $serial_ranges[$_k] .= '-';

                            $serial_ranges[$_k] .= $_serial;
                        }
                    }
                    foreach ($serial_ranges as $_srange) 
                    {
                        $serial_ranges_str .= '('.$_srange.')';
                    }
                }
                if (!empty($serial_ranges_str)) {
                    $replace[] = $keyword;
                    $replacement[] = $serial_ranges_str;
                    continue;
                }
            }
        }

        foreach ($replace as &$_replace) 
        {
            $_replace = '/\[\s*'.
                preg_quote($_replace).'\s*\]/i';
        }
        unset($_replace);

        $print_name = preg_replace(
            $replace, $replacement, $print_name); 


        return preg_replace(
            '/\s+/', '_', trim($print_name));
    }

    # returns [name => [widget, label, initial], ...]
    function get_fields($reload = false, $fill_initial = null)
    {
        if (empty($this->fields) or $reload) 
        {
            $this->fields = $this->_get_fields();
        }
        $fields = $this->fields;

        is_array($fill_initial) or $fill_initial = [];

        foreach ($fill_initial as $name => $value) 
        {
            if (isset($fields, $fields[$name])) 
            {
                $fields[$name]['initial'] = $value;
            }
        }
        
        return $fields;
    }

    # returns true and renders output
    # or returns markup when output is false
    function render($data = null, $output = true)
    {
        $fields = $this->get_fields();

        # add value attr
        foreach ($fields as &$field) 
        {
            $field['value'] = $field['initial'];
        }

        is_array($data) or $data = [];

        $to_render = [];

        foreach ($data as $name => $value) 
        {
            if (!in_array($name, array_keys($fields))) continue;
            $to_render[$name] = ['value' => $value];
        }

        # get other attributes
        foreach ($to_render as $name => $attr) 
        {
            $to_render[$name] = array_merge(
                array_merge([], $fields[$name]), 
                $to_render[$name]);
        }

        # get missing fields
        $to_render = array_merge([], $fields, $to_render);

        # process fields and make batch
        $batch = Batchmaker::get_batch($to_render);
        // die(var_dump($batch));

        return $this->_render($batch, $output);
    }

    # abstract methods

    # render_fieldset 
    # is called for each fieldset in the batch
    # with fieldset, an extractable array of
    # fieldnames and values for use in the 
    # theme markup file
    #
    # example usage
    # function render_fieldset($fieldset)
    # {
    #     // define resources
    #     $base_path = $this->base_path;
    #     $some_resource = $base_path.'image.svg';
    #     
    #     // extract fields
    #     extract($fieldset, EXTR_SKIP); 
    #     
    #     include 'markup.php';
    # }
    #
    abstract public function render_fieldset($fieldset);

    # optional methods to override

    # include common markup here such as styles
    protected function before_render() {}
    protected function after_render() {}
    
    # internal methods
    protected function _render($batch, $output = true)
    {
        $buffer = '';

        if (!$output) ob_start();

        # before render hook
        $this->before_render();

        # render batch
        foreach ($batch as $fieldset) 
        {
            # call render fieldset
            $this->render_fieldset($fieldset);
        }

        # after render hook
        $this->after_render();

        if (!$output) 
        { 
            return ob_get_clean();
        }

        return true;
    }

    protected function _get_fields()
    {
        $fields = []; 

        $default_widget = $this->defaults['widget'];

        foreach ($this->define_fields as $key => $value) 
        {
            if (is_integer($key))
            {
                $name = $value;
                $fields[$name] = [];
            }
            else
            {
                $name = $key;
                $fields[$name] = [];

                if (is_array($value))
                {
                    foreach ($value as $sub_key => $sub_value) 
                    {
                        if (is_integer($sub_key))
                        {
                            # widget
                            $fields[$name]['widget'] = $sub_value;
                        }
                        elseif ($sub_key == 'label')
                        {
                            # label
                            $fields[$name]['label'] = $sub_value;
                        }
                        elseif ($sub_key == 'initial')
                        {
                            # initial
                            $fields[$name]['initial'] = $sub_value;
                        }
                    }
                } 
                else 
                {
                    # widget
                    $fields[$name]['widget'] = $value;
                }

            }

            # if not set
            # set widget, label and initial 

            isset($fields[$name]['widget']) 
                or $fields[$name]['widget'] = $default_widget;

            isset($fields[$name]['label']) 
                or $fields[$name]['label'] = trim(
                    preg_replace(
                        '/_/i' , ' ',
                        preg_replace_callback(
                            '/([^_]+_|[^_]+$)/i', 
                            function($m) {
                                return ucfirst($m[1]);
                            }, $name)));

            isset($fields[$name]['initial'])
                or $fields[$name]['initial'] = '';
        }
        return $fields;
    }

}



# eof

