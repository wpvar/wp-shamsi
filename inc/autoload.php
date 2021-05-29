<?php

/**
 * @package WPSH
 */
defined('ABSPATH') or die();

spl_autoload_register('wpsh_autoload');

function wpsh_autoload($className)
{
    $path = plugin_dir_path(__FILE__);
    $class = $path . $className . '.class.php';
    if (file_exists($class) && !class_exists($className)) {
        require $class;
    }
}

new WPSH_Libs();
new WPSH_Core();

if (is_admin()) {
    new WPSH_Options('wpsh');
    new WPSH_Admin();
    new WPSH_Gutenberg();
}

new WPSH_Api();
new WPSH_Datebar();
new WPSH_Archive();
new WPSH_Addons();
new WPSH_Calendar();
new WPSH_Compatibility();
new WPSH_Shamsi_Api_Pro();
new WPSH_Numignore();
new WPSH_Redirects();
new WPSH_Blocks();