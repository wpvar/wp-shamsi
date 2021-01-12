<?php

/**
 * @package WPSH
 * @copyright Copyright (c) 2019 Alireza Dabiri Nejad released under MIT license.
 * @copyright https://github.com/alirdn/gutenberg-jalali-calendar/blob/master/LICENSE
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
        // Scripts.
        wp_enqueue_script(
            'gutenberg_jalali_calendar_editor_scripts',
            WPSH_URL . 'dist/gutenberg-jalali-calendar.build.js',
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
            true
        );

        // Styles.
        wp_enqueue_style(
            'gutenberg_jalali_calendar_editor_styles',
            WPSH_URL . 'dist/gutenberg-jalali-calendar.build.css',
            array('wp-edit-blocks')
        );
    }
}
