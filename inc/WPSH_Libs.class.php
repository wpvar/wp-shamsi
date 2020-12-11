<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Libs class
 *
 * Run libraries
 *
 * @since 2.0.0
 */
class WPSH_Libs
{
    /**
     * Construction
     *
     * Requires Libraries.
     *
     * @since 2.0.0
     *
     */
    function __construct()
    {

        if (!class_exists('WPSH_DateAbstract')) {
            require_once WPSH_PATH . 'lib/Date/DateAbstract.php';
        }
        if (!class_exists('WPSH_Jalali')) {
            require_once WPSH_PATH . 'lib/Date/Jalali.php';
        }
        if (!class_exists('WPSH_Date')) {
            require_once WPSH_PATH . 'lib/Date/Date.php';
        }
        if (!class_exists('Exopite_Simple_Options_Framework') && is_admin()) {
            require_once WPSH_PATH . 'lib/Options/options-class.php';
        }
    }
}
