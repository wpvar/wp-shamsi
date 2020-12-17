<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Elementor Compability
 *
 * Elementor Compability Class
 *
 * @since 2.1.2
 */
class WPSH_Elementor extends WPSH_Core

{

    function __construct()
    {

        if (!parent::option('activate-elementor', true, true)) {
            return;
        }

        if (did_action('elementor/loaded')) {

            add_action('elementor/editor/before_enqueue_scripts', function () {
                if (get_locale() == 'fa_IR' || get_locale() == 'fa_AF') :
                    wp_enqueue_style('wpsh-elementor-style', WPSH_URL . 'assets/css/wpsh_custom.css', array(), WPSH_VERSION, true);
                    $css = '
                    body, div, span, p, input, code, pre, textarea, button, dl, dt, tr, ul, li, em, a
                    {
                       font-family: Vazir, tahoma, sans-serif, arial, eicons !important;
                    }
                    ';
                    wp_add_inline_style('wpsh-elementor-style', $css);

                endif;
            });
        }
    }
}

new WPSH_Elementor();
