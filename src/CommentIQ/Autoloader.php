<?php


class CommentIQ_Autoloader
{
    /**
     * Handles autoloading of CommentIQ classes.
     *
     * @param string $class
     */
    public static function autoload($class)
    {
        if (0 !== strpos($class, 'CommentIQ')) {
            return;
        }

        if (is_file($file = dirname(__FILE__).'/../'.str_replace(array('_', "\0"), array('/', ''), $class).'.php')) {
            require $file;
        }
    }

    /**
     * Registers CommentIQ_Autoloader as an SPL autoloader.
     *
     * @param bool $prepend
     */
    public static function register($prepend = false)
    {
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            spl_autoload_register(array(new self(), 'autoload'), true, $prepend);
        } else {
            spl_autoload_register(array(new self(), 'autoload'));
        }
    }
}
