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
                    *,
                    :after,
                    :before,
                    #elementor-panel,
                    #elementor-panel-elements-search-input,
                    #elementor-panel-saver-button-publish,
                    .elementor-loading-title,
                    .elementor-add-section-drag-title,
                    .e-global__confirm-input-wrapper input,
                    .elementor-editor-element-settings,
                    .elementor-sortable-helper .title  {
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
