<?php defined('CERTMKR_AJAX') or exit; 

if (isset($_POST, $_POST['request_type']))
{
    require_once 'library/ajaxresponder.php';
    $ajax_responder = new library\Ajaxresponder();

    $ajax_responder->set_request_templates([
        'get_rendered_theme' => [
            'template_index', [
                'include_widgets',
                'populate_data',
            ]
        ],
        'make_pdf' => [
            'template_index',
            'pdf_print_url',
            ['populate_data'],
        ],
    ]);

    # check request
    $request = $ajax_responder->check_request_or_error(
        $_POST['request_type'], $_POST);

    # handle request & exit
    if ($request->name == 'get_rendered_theme')
    {   
        $theme = $themes->get_theme_by_index(
            $request->data['template_index']);

        if (!$theme)
        {
            $ajax_responder->exit_with_error(
                'invalid template_index');
        }

        $data = [];
        $populate_data = null;

        if ($request->optional) {
            # populate template with data
            if (in_array('populate_data', $request->optional)) {
                $populate_data = $request->data['populate_data'];
            }

            # include widgets
            if (in_array('include_widgets', $request->optional)) {
                require_once 'library/widgetbuilder.php';

                $data['widgets'] = library\Widgetbuilder::build_widgets(
                                        $theme->get_fields(
                                            false, $populate_data));
            }
        }

        # get markup
        $data['markup'] = $theme->render($populate_data, false);

        $ajax_responder->exit_with_response($data);
    }

    if ($request->name == 'make_pdf') {
        $theme = $themes->get_theme_by_index(
            $request->data['template_index']);

        if (!$theme or !$theme->print_size)
        {
            $ajax_responder->exit_with_error(
                'invalid template_index');
        }

        $data = [];
        $populate_data = null;

        if (!isset($phantomjs) or !file_exists($phantomjs)) {
            $ajax_responder->exit_with_error(
                'phantomjs not found');
        }
        if ($request->optional) {
            if (in_array('populate_data', $request->optional)) {
                $populate_data = $request->data['populate_data'];
            }
        }
        
        $print_name = $theme->get_print_name($populate_data);
        if (empty($print_name)) $print_name = 'certificates';

        $print_name .= '.pdf';

        $data['exec_command'] = $phantomjs.' savepdf.js '.
            '\'http'.(isset($_SERVER['HTTPS']) ? 's' : ''). 
            "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?".
            ($request->data['pdf_print_url']).'\' '.
            $theme->print_size.' \''.$print_name.'\'';

        $data['savepdf_output'] = [];
        $data['exit_code'] = null;

        $exec_result = exec($data['exec_command'],
            $data['savepdf_output'], $data['exit_code']);

        if ($data['exit_code'] != 0) {
            $ajax_responder->exit_with_error(
                'phantomjs operation failed', $data);
        } else {
            $data['outfile'] = './download/'.$print_name;
        }

        $ajax_responder->exit_with_response($data);
    }

    # unhandled request
    $ajaxresponder->exit_with_error(
        'unhandled request'); 
}

// on get request
elseif (isset($_GET, $_GET['request_type'])) {
    if ($_GET['request_type'] == 'get_rendered_theme') {
        try {
            if (!isset($_GET['template_index'])) 
                throw new Exception('template_index required');

            $theme = $themes->get_theme_by_index(
                $_GET['template_index']);

            if (!$theme)
                throw new Exception('template_index invalid');

            $populate_data = null;

            if (isset($_GET['populate_data'])) {
                $populate_data = $_GET['populate_data'];
            }

            $app_init_state_override = [
                'template_index' => $_GET['template_index'],
                'init_data' => $populate_data,
            ];

        } catch (Exception $e) {
            // 
        }
    }
}


# eof