<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Compatibility class
 *
 * Makes dates generated via third party plugins shamsi
 *
 * @since 2.0.0
 */
class WPSH_Compatibility
{
    /**
     * Construction
     *
     * Construct WPSH_Compatibility class.
     *
     * @since 2.0.0
     *
     */
    function __construct()
    {

        foreach (glob(WPSH_PATH . 'compatibility/*.php') as $filename) {
            include_once $filename;
        }
    }
}
