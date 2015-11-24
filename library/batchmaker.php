<?php

namespace library;

require_once 'constants.php';

# Batchmaker static class
# Processes widget input fields and returns 
# a dataset for batch rendering.

class Batchmaker
{
    private static $errors = [];

    static function get_errors()
    {
        return self::$errors;
    }

    static function get_clean_errors()
    {
        $errors = self::$errors;
        self::clean_errors();
        return $errors;
    }

    static function clean_errors()
    {
        self::$errors = [];
    }

    static function get_batch($fields)
    {
        $default_values = [];

        # check fields
        foreach ($fields as $name => $attr) 
        {
            if (!is_array($attr)
                or !isset($attr['value'], $attr['widget'])) 
            {
                self::$errors[] = 'required attributes missing';
                return false;
            }    
            $default_values[$name] = $attr['value'];
        }

        $processed = [];
        $serializable_fields = [];

        # process fields
        foreach ($fields as $name => $attr) 
        {
            $proc_method = '_proc_'.$attr['widget'];

            if (method_exists(get_class(), $proc_method))
            {
                $processed[$name] = self::$proc_method($attr['value']);
            }
            else
            {
                $processed[$name] = [
                    $attr['value']
                ];
            }

            # serializable?
            if ($attr['widget'] == CERTMKR_SERIAL_FIELD)
            {
                $serializable_fields[] = $name;
            }
        }

        # equalize fields
        $count = 0;

        foreach ($processed as $field_values) 
        {
            $count > count($field_values) 
                or $count = count($field_values);
        }

        foreach ($processed as &$field_values) 
        {
            for ($i = 0; $i < $count; $i++)
            {
                isset($field_values[$i])
                    or $field_values[$i] = '';
            }
        }
        unset($field_values);

        # serialize with count
        foreach ($serializable_fields as $name) 
        {
            $processed[$name] = self::_proc__serialize(
                                    $processed[$name][0], $count);
        }

        # make batch
        $batch = [];

        for ($i = 0; $i < $count; $i++)
        {
            # copy values
            $batch[$i] = array_merge([], $default_values);

            foreach ($processed as $name => $values) 
            {
                if (strlen($values[$i]))
                {
                    $batch[$i][$name] = $values[$i];
                }
            }
        }
        return $batch;
    }

    # field processors
    static function _proc_csvfield($value)
    {
        isset($value) or $value = '';

        $field_values = preg_split('/,/i', $value);
        $real_values = [];
        foreach ($field_values as $_value) 
        {
            $_value = trim($_value);
            
            # fix for consecutive commas 
            # in input such as ",,,,"
            if (strlen($_value)) {
                $real_values[] = $_value;
            }
        }
        return $real_values;
    }

    # non standard field processors
    static function _proc__serialize($pattern, $count = 0, $delta = false)
    {
        is_string($pattern) or $pattern = '[0001]';
        $count = $count+0;

        # debug
        // $pattern = 'ksrgbrh[35235]srfbfn3423-ksrgbrh[ 0005 - ]srfbfn3423';

        preg_match_all(
            '/\[\s*(?P<start>[0-9]+)\s*(?P<opr>[\+\-]?)\s*\]/i', 
            $pattern, $matches);

        $serials = [];

        if (!empty($matches['start']))
        {
            foreach ($matches['start'] as $index => $match) 
            {
                $opr = $matches['opr'][$index];

                $serial_length = strlen($match);
                $padding = '';

                for ($i = 0; $i < $serial_length; $i++)
                {
                    $padding .= '0';
                }

                $start = $match+0;

                if ($opr == '-')
                {
                    $max = preg_replace('/0/i', '9', $padding);

                    # decrement
                    for ($i = 0; $i < $count; $i++)
                    {
                        $curr = $start - $i;
                        if ($curr < 0)
                        {
                            $curr = $max + ($curr + 1);
                        }
                        $curr = substr(
                            $padding.$curr, 
                            (-1)*$serial_length);

                        if ($delta) 
                        {
                            isset($serials[$i]) 
                                or $serials[$i] = [];

                            $serials[$i][] = $curr;
                        } 
                        else 
                        {
                            isset($serials[$i]) 
                                or $serials[$i] = $pattern;

                            $p_start = preg_quote($match);
                            $p_opr = preg_quote($opr);

                            $serials[$i] = preg_replace(
                                "/\[\s*$p_start\s*$p_opr\s*\]/i", 
                                $curr, $serials[$i]);
                        }
                    }
                }
                else
                {  
                    # increment
                    for ($i = 0; $i < $count; $i++)
                    {
                        $curr = $start + $i;
                        $curr = $curr.'';

                        if (strlen($curr) < $serial_length)
                        {
                            $curr = substr($padding.$curr,
                                        (-1)*$serial_length);
                        }

                        if ($delta)
                        {
                            isset($serials[$i]) 
                                or $serials[$i] = [];

                            $serials[$i][] = $curr;
                        }
                        else 
                        {
                            isset($serials[$i]) 
                                or $serials[$i] = $pattern;

                            $p_start = preg_quote($match);
                            $p_opr = preg_quote($opr);

                            $serials[$i] = preg_replace(
                                "/\[\s*$p_start\s*$p_opr\s*\]/i", 
                                $curr , $serials[$i]);
                            
                        }
                    }
                }
            }
        }
        else
        {
            for ($i = 0; $i < $count; $i++)
            {
                $serials[$i] = $pattern;
            }
        }
        return $serials;

        /* echo '<pre>';
        var_dump($serials); die; */
    }
}

# eof