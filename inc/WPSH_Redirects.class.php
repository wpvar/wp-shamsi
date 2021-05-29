<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Redirects
 *
 * Redirect wordpress web pages
 *
 * @since 3.2.0
 */
class WPSH_Redirects extends WPSH_Core
{

    /**
     * Construction
     *
     * Construct Redirects class.
     *
     * @since 3.2.0
     *
     */
    function __construct()
    {
        add_action('template_redirect', array($this, 'redirect'), 1);
    }

    /**
     * Initiate Redirects
     *
     * Redirect web pages according to selected http status.
     *
     * @since 3.2.0
     *
     */
    public function redirect()
    {
        $redirects = (array)parent::option('redirect-group');
        if(empty($redirects)) {
            return false;
        }
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        foreach ($redirects as $redirect) {
            if (!empty($redirect['redirect-source']) && !empty($redirect['redirect-target'])) {
                if (parent::pro()) {
                    if(empty($redirect['redirect-status'])) {
                        $status = '302';
                    } else {
                        $status = $redirect['redirect-status'];
                    }
                } else {
                    $status = '302';
                }
                if ($actual_link == $redirect['redirect-source']) {
                    wp_redirect($redirect['redirect-target'], $status);
                    exit();
                }
            }
        }
    }
}
