<?php
/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Contact Form 7 Compability
 *
 * Contact Form 7 and Flamingo Compability Class
 *
 * @since 2.0.3
 */
class WPSH_ContactForm extends WPSH_Core

{

    function __construct()
    {

        if (!parent::option('activate-contactform', true, true))
        {
            return;
        }

    }

}

new WPSH_ContactForm();

