<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Gravity Forms Compability
 *
 * We try to prevent fatal errors, in future shamsi support will be added.
 *
 * @since 4.0.1
 */
class WPSH_GravityForms extends WPSH_Core
{
    function __construct()
    {
        if (!parent::option('activate-gravityforms', true, true)) {
            return;
        }
        if (class_exists('GFForms')) {
            add_action('wp_enqueue_scripts', array($this, 'style'), 99999);
        }
    }

    public function style()
    {
        $css = '
            .gform_wrapper {
                display: block !important;
            }
        ';
        wp_add_inline_style('wpsh-style', $css);
    }
}

new WPSH_GravityForms();