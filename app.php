<?php defined('CERTMKR_START_APP') or exit; 

isset($app_theme_list) or exit('app_theme_list not set');
$app_theme_list instanceof Traversable or exit('app_theme_list not set');

# document ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8"> 
    <title>Certificate Maker</title>
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/print.css">
    <script src="js/jquery.min.js"></script>
</head>
<body>
    <div 
        class="controls">
        <div
            class="page">
            <h2>Certificate Maker</h2>
            <p
                class="desc-text">
                Certificate Maker allows you to create serialized 
                certificates on the fly. It is easily extendable through
                templates! <br>
                <span class="created-by">
                    Created by 
                    <a 
                        href="http://www.github.com/aravindanve"
                        target="_blank">Aravindan Ve</a>.
                </span>
                <span class="license-link">
                    <a 
                        href="license.txt"
                        target="_blank">
                        Copyright 2015 &bull; MIT License</a>
                </span>
            </p>
            <div 
                class="line"></div>
            <ul
                class="form-list horiz f-width">
                <li>
                    <span
                        class="label">
                        Select Template
                    </span>
                    <span
                        class="content">
                        <select
                            id="select-template"
                            autocomplete="off">
                            <option 
                                value="">--</option>
                            <?php 
                            foreach ($app_theme_list as $index => $theme) 
                            { ?>
                            <option 
                                value="<?php echo $index ; ?>">
                                <?php echo $theme->display_name(); ?></option>
                            <?php } ?>
                        </select>
                    </span>
                </li>
            </ul>
            <div 
                class="line"></div>
            <form id="widget-form" method="POST" action="">
                <span
                    id="template-widgets">
                    <p
                        class="desc-text">
                        Select a template to begin.</p>
                </span>
                <span
                    class="submit-button-wrapper">
                    <a id="apply-changes" 
                        class="bt standard">Apply Changes</a>
                    <a id="get-pdf" 
                        class="bt standard" 
                        style="margin-left: 10px;">Save PDF</a>
                    <span class="cl-b"></span>
            </form>
        </div>
    </div>
    <div 
        class="divider"></div>
    <div 
        id="certificates">
    </div>
    <?php if (isset($app_init_state_override) && 
    is_array($app_init_state_override)) { ?>
    <script>
    window.CertMaker = {};
    <?php foreach ($app_init_state_override as $cm_k => $cm_v) { ?>
    window.CertMaker["<?php echo $cm_k; ?>"] = <?php 
        if (is_string($cm_v)) {
            echo '"'.$cm_v.'"';
        } elseif (is_numeric($cm_v)) {
            echo $cm_v;
        } elseif (is_array($cm_v)) {
            echo json_encode($cm_v);
        } else {
            echo '';
        }
    ?>;
    <?php } ?>
    </script>
    <?php } ?>
    <script src="js/script.js"></script>
</body>
</html>