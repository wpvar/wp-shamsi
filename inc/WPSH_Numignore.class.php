<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Ignore numbers
 *
 * Ignore numbers to be converted to farsi
 *
 * @since 3.0.2
 */
class WPSH_Numignore
{

    /**
     * Construction
     *
     * Construct Numignore class.
     *
     * @since 3.0.2
     *
     */
    function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'scripts'), 999999);
        add_action('admin_enqueue_scripts', array($this, 'scripts'), 999999);
    }

    /**
     * Initiate Numignore
     *
     * Ignore Number using javascript to always print orginal value.
     *
     * @since 3.0.2
     *
     */
    public function scripts() {
        if(function_exists('wp_add_inline_script')) {
            $js = '';

            $js .= apply_filters('wpsh_num_ignore', '');

            if ($js != '') {
                wp_enqueue_script('wpsh-numignore', WPSH_URL . 'assets/js/wpsh_numignore.js', array(
                    'jquery'
                  ), WPSH_VERSION, true);

                wp_add_inline_script( 'wpsh-numignore', $js );
            }

        }
    }
}
