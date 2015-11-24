<?php

namespace library;

# Themeloader class
# Loads themes from the themes directory

class Themeloader implements \Iterator
{
    private $theme_dir          = 'themes/';
    private $theme_ext          = 'theme, thm';
    private $theme_file         = null;
    private $theme_class        = null;

    private $themes = [];

    private $iterator_position  = 0;

    function __construct($args) 
    {
        if(!is_array($args)) return false;

        $req = ['dir', 'ext', 'file', 'class'];
        foreach ($req as $attr) 
        {
            $private_attr = 'theme_'.$attr;
            if (!isset($this->$private_attr))
            {
                if (!in_array($attr, array_keys($args)))
                    return false;
            }
            if (isset($args[$attr]))
            {
                $this->$private_attr = $args[$attr];
            } 
        }
        $this->_load_themes();
    }

    function _load_themes()
    {
        $this->themes = [];

        $themes = glob(
            "{$this->theme_dir}*.{{$this->theme_ext}}", 
            GLOB_BRACE);

        $p_exts = $this->theme_ext;
        $p_exts = preg_split('/,/i', $p_exts);
        foreach ($p_exts as &$ext) 
        {
            $ext = preg_quote('.'.trim($ext));
        }
        $p_exts = join('|', $p_exts);

        foreach($themes as $theme_dir) 
        {   
            $theme = new Theme(
                $theme_dir, $this->theme_file,
                [
                    'class' => preg_replace(
                        '/([^\/]+\/(?=[^$])|('.$p_exts.')|\/\s?$)/i',
                        '', $theme_dir)."\\".$this->theme_class,
                ]
            );
            $this->themes[] = $theme;
        }
        foreach ($this->themes as $index => $theme) {
            $result = $theme->init();
            if (!$result)
            {
                $this->themes[$index] = false;
            }
        } 


        $loaded = $this->themes;
        $this->themes = [];

        foreach ($loaded as $theme) 
        {
            if ($theme)
            {
                $this->themes[] = $theme; 
            }
        }
    }

    function get_theme_by_index($index)
    {
        if (array_key_exists(
            $index, $this->themes))
            return $this->themes[$index]->get_instance();

        return false;
    }

    # implement iterator's abstrct methods

    function rewind() 
    {
        # var_dump(__METHOD__);
        $this->iterator_position = 0;
    }

    function current() 
    {
        # var_dump(__METHOD__);
        return $this->themes[$this->iterator_position];
    }

    function key() 
    {
        # var_dump(__METHOD__);
        return $this->iterator_position;
    }

    function next() 
    {
        # var_dump(__METHOD__);
        ++$this->iterator_position;
    }

    function valid() 
    {
        # var_dump(__METHOD__);
        # var_dump($this->themes);
        return isset($this->themes[$this->iterator_position]);
    }
}

# Theme class
# Provides an interface to a generic 
# theme and its attributes

class Theme
{
    private $attribute = [
        'base_path' => null,
        'full_path' => null,

        'file' => null,
        'class' => null,
    ];

    private $instance = null;
    private $display_name = null;

    function __construct($base_path, $file, $attrs = [])
    {
        if (!isset($base_path, $file)) return false;

        is_array($attrs) or $attrs = [];

        $attrs['base_path'] = rtrim($base_path, '/').'/';
        $attrs['full_path'] = rtrim($base_path, '/').'/'.$file;
        $attrs['file'] = $file;
        isset($attrs['class']) 
            or $attrs['class'] = preg_replace(
                                    '/([^\.])*\.[^\.]/i', 
                                    ucfirst("$1"), 
                                    $file);

        foreach ($this->attribute as $name => $value) 
        {
            if (!in_array($name, array_keys($attrs)))
            {
                return false;
            }
            $this->attribute[$name] = $attrs[$name];
        }
    }

    function __call($method_name, $args)
    {
        return $this->attribute[$method_name];
    }  

    function init()
    {
        $incl = $this->full_path();

        if (file_exists($incl)) include_once $incl;

        $class = $this->class();

        if (class_exists($class)) 
        {
            $this->instance = new $class($this->base_path());

            if ((gettype($this->instance) == 'object')
                and method_exists($this->instance, 'get_meta')) 
            {
                $meta = $this->instance->get_meta();
                if (isset($meta, $meta['name']))
                {
                    $this->display_name = $meta['name'];
                }

                isset($this->display_name) 
                    or $display_name = 'Untitled';

                return true;
            }
        }
        return false;
    }

    function get_instance()
    {
        return $this->instance;
    }

    function display_name()
    {
        return $this->display_name;
    }
}

# eof