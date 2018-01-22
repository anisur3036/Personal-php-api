<?php
if (! function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (! function_exists('old')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    function old($field)
    {
        if (isset($_POST[$field])) {
            echo escape($_POST[$field]);
        }
    }
}

if (! function_exists('escape')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */
    function escape($value)
    {
        return htmlspecialchars(trim($value), ENT_QUOTES);
    }
}

if (! function_exists('vd')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  string|object  $class
     * @return string
     */

    function vd($value)
    {
        $out = var_dump($value);
        $out .= die();
        return $out;
    }
}
