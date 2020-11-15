<?php
/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Datebar class
 *
 * Adds date to admin bar
 *
 * @since 2.0.0
 */
class WPSH_Datebar extends WPSH_Core
{
    /**
     * Construction
     *
     * Construct WPSH_Datebar class.
     *
     * @since 2.0.0
     *
     */
    function __construct()
    {

        add_action('admin_bar_menu', array(
            $this,
            'date_bar'
        ) , 1000);

    }

    /**
     * Add date bar
     *
     * Add date bar to toolbar
     *
     * @since 2.0.0
     *
     * @param object $wp_admin_bar Native Object to add menus to toolbar.
     */
    public function date_bar($wp_admin_bar)
    {
        if (!parent::option('admin-bar-date', true, true) || parent::option('activate-admin-shamsi', true, false))
        {
            return;
        }

        $jdate = parent::wp_shamsi(null, 'l d F Y', time());
        $gdate = date('l d F Y', time());
        $jtime = parent::wp_shamsi(null, 'g:i a', time());
        $jintdate = parent::wp_shamsi(null, 'Y/m/d', time());
        $gintdate = date('Y/m/d', time());
        if (!current_user_can('manage_options'))
        {
            $args = array(
                'id' => 'wpsh',
                'title' => '<span class="ab-icon"></span>' . $jdate,
                'href' => home_url() ,
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'gdate_bar',
                'title' => $gdate,
                'href' => home_url() ,
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'gintdate_bar',
                'title' => $gintdate,
                'href' => home_url() ,
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'jintdate_bar',
                'title' => $jintdate,
                'href' => home_url() ,
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'wpsh_time_bar',
                'title' => 'ساعت: ' . $jtime,
                'href' => home_url() ,
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);
        }
        else
        {
            $args = array(
                'id' => 'wpsh',
                'title' => '<span class="ab-icon"></span>' . $jdate,
                'href' => admin_url() . 'admin.php?page=wpsh',
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'gdate_bar',
                'title' => $gdate,
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'gintdate_bar',
                'title' => $gintdate,
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'jintdate_bar',
                'title' => $jintdate,
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'wpsh_time_bar',
                'title' => 'ساعت: ' . $jtime,
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'wpsh_settings_bar',
                'title' => 'تنظیمات تاریخ',
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);
        }
    }

}

