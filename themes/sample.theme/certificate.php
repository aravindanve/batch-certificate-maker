<?php 

namespace sample 
{   
    class Certificate extends \library\Certificate
    {
        var $meta_name          = 'Sample Theme';
        var $meta_description   = 'This is a sample certificate template';

        var $define_fields      = [
            'candidate_name'        => [CERTMKR_CSV_FIELD, 'initial' => 'Candidate Name'],
            'certification_name'    => ['initial' => 'Certification Name'],
            'company_name',
            'date_of_issue',
            'valid_until',
            'identification_code'   => [CERTMKR_SERIAL_FIELD, 'initial' => 'ACN2014P0TMP[0001+]SF'],
        ];

        var $print_size = '297mm*210mm';
        var $print_name = '[meta_name]-[company_name]-[timestamp]-[identification_code|delta]';

        function before_render()
        {
            $base_path = $this->base_path;
            include 'html-includes.php';
        }

        function render_fieldset($fieldset)
        {
            # resources
            $base_path = $this->base_path;

            extract($fieldset, EXTR_SKIP); 
            include 'markup.php';
        }
    }
}

# eof
