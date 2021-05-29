<?php

/**
 * @package WPSH
 * @copyright Copyright (c) 2019 Alireza Dabiri Nejad released under MIT license.
 * @copyright Copyright (c) 2021 Modified by Ali Faraji (mail.wpvar@gmail.com) | https://wpvar.com modifications released under GPLV3.
 */

defined('ABSPATH') or die();

/**
 * Gutenberg Calendar
 *
 * Add shamsi Gutenberg calendar
 *
 * @since 3.0.0
 */
class WPSH_Gutenberg extends WPSH_Core
{

    /**
     * Construction
     *
     * Construct Gutenberg Calendar class.
     *
     * @since 3.0.0
     *
     */
    function __construct()
    {
        if (!parent::option('activate-shamsi', true, true) || parent::no_lang_no_shamsi() || parent::option('activate-admin-shamsi', true, false)) {
            return;
        }
        add_action('enqueue_block_editor_assets', array($this, 'gutenberg_jalali_calendar_editor_assets'));
    }

    /**
     * Enqueue Gutenberg Jalali Calendar assets for backend editor.
     *
     * @uses {wp-plugins}
     * @uses {wp-i18n} to internationalize the block's text.
     * @uses {wp-compose}
     * @uses {wp-components}
     * @uses {wp-element} for WP Element abstraction — structure of blocks.
     * @uses {wp-editor} for WP editor styles.
     * @uses {wp-edit-post} to internationalize the block's text.
     * @uses {wp-data}
     * @uses {wp-date}
     * @since 1.0.0
     */
    public function gutenberg_jalali_calendar_editor_assets()
    {

        wp_enqueue_script(
            'wpsh-gjc',
            WPSH_URL . 'assets/js/wpsh_gutenberg.js',
            array(
                'wp-plugins',
                'wp-i18n',
                'wp-compose',
                'wp-components',
                'wp-element',
                'wp-editor',
                'wp-edit-post',
                'wp-data',
                'wp-date'
            ),
            WPSH_VERSION,
            true
        );

        wp_enqueue_style(
            'wpsh-gjc',
            WPSH_URL . 'assets/css/wpsh_gutenberg.css',
            array('wp-edit-blocks'),
            WPSH_VERSION
        );

        $css = '
        .edit-post-post-schedule {
            display: none;
          }
        ';

        wp_add_inline_style('wpsh-gjc', $css);

    }
}