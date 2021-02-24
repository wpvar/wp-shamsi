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

                    $font = parent::option('dashboard-font-default', false, 'IRANSansWeb');

                    if ($font != 'none') {
                        if (!parent::pro() && ($font == 'IRANSansDn' || $font == 'IRANYekanWeb')) {
                            $css = parent::font('IRANSansWeb');
                        } else {
                            $css = parent::font($font);
                        }
                    } else {
                        $font = 'IRANSansWeb';
                        $css = parent::font('IRANSansWeb');
                    }

                    $css .= '
                    .elementor-control-dynamic-switcher {
                        height: 31px !important;
                    }
                    ';

                    $css .= '
                    body, div, span, p, input, code, pre, textarea, button, dl, dt, tr, ul, li, em, a
                    {
                       font-family: ' . $font . ', tahoma, sans-serif, arial, eicons;
                    }
                    ';
                    wp_add_inline_style('wpsh-elementor-style', $css);

                endif;
            });
        }
    }
}

new WPSH_Elementor();
