<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Kolkata');

require_once 'library/themeloader.php';
require_once 'library/batchmaker.php';
require_once 'library/certificate.php';

# themes dir
$themes_dir             = 'themes/';

# theme structure
$theme_file             = 'certificate.php';
$theme_class            = 'Certificate';

# phantomjs path
# phantomjs can be found here at `http://phantomjs.org/download.html`
$phantomjs = '/Users/aravindanve/bin/phantomjs';

$themes = new library\Themeloader([
    'file'      => $theme_file,
    'class'     => $theme_class,
]);

# turn on for ajax tests
$ajax_request_simulate = false;

$ajax_request_test_case = 1;

if ($ajax_request_simulate) 
{
    if ($ajax_request_test_case == 1)
    {
        global $_POST;
        $_POST = [];
        $_POST['ajax'] = 'get_rendered_theme';
        $_POST['template_index'] = 0;
        $_POST['include_widgets'] = true;
    }
}

defined('CERTMKR_AJAX') 
    or define('CERTMKR_AJAX', 1);

# request handler
include 'request.php';

defined('CERTMKR_START_APP') 
    or define('CERTMKR_START_APP', 1);

$app_theme_list = $themes;

# start app
include 'app.php';




# eof

























