<?php

namespace library;

# Ajaxresponder class
# Processes widget input fields and returns 
# a dataset for batch rendering.

class Ajaxresponder 
{
    private $request_templates = [];

    function __construct() {}

    function exit_with_response($data = null)
    {
        isset($data) or $data = [];
        is_array($data) or $data = [];

        isset($data['error']) or $data['error'] = false;
        isset($data['message']) or $data['message'] = 'success';
        isset($data['markup']) or $data['markup'] = '';

        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    function exit_with_error($message = null, $data = null)
    {
        isset($message) or $message = 'error';

        $this->exit_with_response([
            'error' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    # format ['request_name' => 
    #   ['required_1', 'required_2', ['optional_1']]
    # ]
    function set_request_templates($templates)
    {
        is_array($templates) or $templates = [];
        $this->request_templates = $templates;
    }

    function check_request_or_error($request_name, $args)
    {
        $templates = $this->request_templates;

        # checks
        if (!isset($templates, $templates[$request_name]))
        {
            $this->exit_with_error('bad request');
        }
        if (!is_array($templates[$request_name]))
        {
            $this->exit_with_error('bad template');
        }
        if (!is_array($args))
        {
            $this->exit_with_error('bad args');
        }

        # parameters
        $required = [];
        $optional = [];

        foreach ($templates[$request_name] as $param) 
        {
            if (is_string($param))
            {
                $required[] = $param;
            }
            elseif (is_array($param))
            {
                foreach ($param as $opt) 
                {
                    if (is_string($opt))
                        $optional[] = $opt;
                }
            }
        }

        foreach ($required as $param) 
        {
            if (!in_array($param, array_keys($args)))
            {
                $this->exit_with_error("{$param} missing");
            }
        }

        $has_optional = [];
        foreach ($optional as $param) 
        {
            if (in_array($param, array_keys($args)))
            {
                $has_optional[] = $param;
            }
        }
        return (object) [
            'name' => $request_name,
            'optional' => (empty($has_optional)? false : $has_optional),
            'data' => $args,
        ];
    }
}


# eof