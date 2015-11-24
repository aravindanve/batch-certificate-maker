<?php 
# dimensions in mm
$width = 297;
$height = 210;
?>
<!-- certificate -->
<svg 
    version="1.1" 
    class="rl01A" 
    x="0px" 
    y="0px" 
    width="<?php echo $width; ?>mm" 
    height="<?php echo $height; ?>mm"
    xmlns="http://www.w3.org/2000/svg" 
    xmlns:xlink="http://www.w3.org/1999/xlink" 
    xmlns:re="re:is/for/comments">
    <rect 
        x="0" y="0" 
        width="<?php echo $width; ?>mm" 
        height="<?php echo $height; ?>mm" 
        style="fill: #FEF8E0" 
        re:comment="background"></rect>
    <image 
        x="0" y="0" 
        width="<?php echo $width; ?>mm" 
        height="<?php echo $height; ?>mm" 
        xlink:href="<?php echo $base_path.'template.svg'; ?>" 
        re:comment="frame"></image>
    <g>
        <rect 
            x="10mm" y="10mm" 
            width="<?php echo $width - 20; ?>mm" 
            height="<?php echo $height - 20; ?>mm" 
            style="fill: #f00; opacity: 0;" 
            re:comment="background"></rect>
        <text
            x="<?php echo $width/2; ?>mm" 
            y="74mm"
            text-anchor="middle">This certificate is awarded to</text>
        <text
            class="candidate-name"
            x="<?php echo $width/2; ?>mm" 
            y="<?php echo ($height/2) - 13; ?>mm"
            text-anchor="middle"><?php echo $candidate_name; ?></text>
        <text
            y="114mm"
            width="100mm"
            text-anchor="middle">
                <tspan 
                    x="<?php echo $width/2; ?>mm" 
                    dy="0">
                    for successful completion of</tspan>
                <tspan 
                    x="<?php echo $width/2; ?>mm" 
                    dy="1.6em">
                    the Awesome Certification program on</tspan>
                <tspan 
                    x="<?php echo $width/2; ?>mm" 
                    dy="1.6em">
                    <?php echo $certification_name; ?>.</tspan>
        </text>
        <text
            class="signatory"
            y="176mm"
            width="100mm"
            text-anchor="middle">
                <tspan 
                    x="<?php echo ($width/2) - 64; ?>mm" 
                    dy="0">
                    Authorized Signatory</tspan>
                <tspan
                    class="sub" 
                    x="<?php echo ($width/2) - 64; ?>mm" 
                    dy="1.6em">
                    Issuing Company</tspan>
        </text>
        <text
            class="signatory"
            y="176mm"
            width="100mm"
            text-anchor="middle">
                <tspan 
                    x="<?php echo ($width/2) + 64; ?>mm" 
                    dy="0">
                    Authorized Signatory</tspan>
                <tspan
                    class="sub" 
                    x="<?php echo ($width/2) + 64; ?>mm" 
                    dy="1.6em">
                    <?php echo $company_name; ?></tspan>
        </text>
        <image 
            x="<?php echo ($width/2) - 13; ?>mm"
            y="158mm"
            width="26mm" 
            height="26mm" 
            xlink:href="<?php echo $base_path; ?>images/company-logo.png" 
            re:comment="frame"></image>
        <text
            class="information"
            y="146mm"
            width="100mm"
            text-anchor="start">
                <tspan 
                    x="24mm" 
                    dy="0">
                    Date of Issue</tspan>
                <tspan 
                    class="date"
                    x="50mm" 
                    dy="0">
                    <?php echo $date_of_issue; ?></tspan>
                <tspan
                    class="sub" 
                    x="24mm" 
                    dy="1.6em">
                    Valid Until</tspan>
                <tspan 
                    class="date"
                    x="50mm" 
                    dy="0">
                    <?php echo $valid_until; ?></tspan>
                <tspan
                    class="sub" 
                    x="24mm" 
                    dy="1.6em">
                    Identification Code</tspan>
                <tspan 
                    class="code"
                    x="24mm" 
                    dy="1.6em">
                    <?php echo $identification_code; ?></tspan>
        </text>
        <text
            class="footer-text"
            x="<?php echo $width/2; ?>mm" 
            y="205mm"
            text-anchor="middle">
            Issued by Example Company, Rogue Nation. 
            (email) support@example.com (phone) 1800 0000 000
        </text>
    </g>
</svg>
<!-- /certificate -->