<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * BuddyBoss Compability
 *
 * BuddyBoss Compability Class
 *
 * @since 4.0.1
 */
class WPSH_Buddyboss extends WPSH_Core
{
    function __construct()
    {
        if (!parent::option('activate-buddyboss', true, true)) {
            return;
        }
        if (class_exists('BuddyPress')) {
            add_filter('wpsh_date_before', array($this, 'buddyboss'));

            if (parent::pro()) {
                add_filter('wpsh_after_editor', array($this, 'buddyboss_msg'), 10, 1);
            }

            add_filter('wpsh_admin_bar', array($this, 'bar'));
            add_action('wp_enqueue_scripts', array($this, 'style'), 99999);
        }
    }

    public function buddyboss()
    {
        if (function_exists('bp_is_activity_component')) {
            if (bp_is_activity_component()) {
                return false;
            }
        }

        return true;
    }

    public function buddyboss_msg($content)
    {
        if (function_exists('bp_is_messages_component')) {
            if (bp_is_messages_component()) {
                return str_replace('! ', ' !', $content);
            }
        }

        return $content;
    }

    public function bar()
    {
        if (function_exists('bp_is_activity_component')) {
            if (bp_is_activity_component()) {
                return false;
            }
        }

        return true;
    }

    public function style()
    {
        $css = '
            .activity #buddypress .separator, .activity #buddypress .activity {
                display: none;
            }
        ';
        wp_add_inline_style('wpsh-style', $css);
    }
}

new WPSH_Buddyboss();