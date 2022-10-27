<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Api connection
 *
 * Authorises and Connects Plugin to endpoint
 *
 * @since 2.1.0
 */
class WPSH_Api extends WPSH_Core
{
    /**
     * Construction
     *
     * Construct Api class.
     *
     * @since 2.1.0
     *
     */
    public function __construct()
    {
        if (is_admin()) {
            // @deprecated 4.2.0
            // add_action('admin_notices', array($this, 'newsletter'), 11);
            add_action('admin_notices', array($this, 'stats'), 11);
            // @deprecated 4.2.0
            // add_action('admin_init', array($this, 'newsletter_core'));
            add_action('admin_init', array($this, 'update_stats'));
        }

        add_action('init', array($this, 'send_stats'));
    }

    /**
     * Update stats activation setting
     *
     * Update database if user gives access to sending stats, Users can always Opt-out using settings
     *
     * @since 2.1.0
     *
     */
    public function update_stats()
    {
        $permission = $this->permission('stats');

        if ($permission !== true) {
            return false;
        }

        if (parent::post('wpsh_stats', 'bool')) {
            parent::update('activate-stats', 'yes');
        }
    }

    /**
     * Sending stats
     *
     * If pesmission has been granted by user, This function will trigger sendig stats
     *
     * @since 2.1.0
     *
     */
    public function send_stats()
    {
        $interval = 604800; // Every Week
        $failed_interval = 86400; // Every Day
        $is_permission = parent::option('activate-stats', true, false); // Default is False, Do not run function without permission
        $last_contact = (get_option('wpsh_stats_last_contact') != null) ? (int)get_option('wpsh_stats_last_contact') : time();
        if (!$is_permission || time() < $last_contact) {
            return;
        }

        $action = $this->stats_core();

        if ($action === false) {
            update_option('wpsh_stats_last_contact', time() + $failed_interval);
            return;
        }

        update_option('wpsh_stats_last_contact', time() + $interval);
    }

    /**
     * Permission check
     *
     * Checks if user has permission to access api functions
     *
     * @since 2.1.0
     *
     * @param string $mode Type of request.
     * @return bool Escaped Returns true if user has permission.
     */
    public function permission($mode)
    {
        $user = get_current_user_id();
        $dismiss = parent::get('wpsh_' . $mode);
        $do_dismiss = 'wpsh_' . $mode . '_dismiss';
        $from_settings = 'wpsh_' . $mode . '_settings';

        $email_key = 'wpsh_newsletter_email';

        switch ($mode) {
            case 'newsletter':

                $email = get_user_meta($user, $email_key, true);

                if (!current_user_can('manage_options') || ((get_user_meta($user, 'wpsh_newsletter_dismiss', true) == 1 && !parent::get($from_settings, 'bool')) || (!empty($email) && $email != null))) {
                    return false;
                }

                if ($dismiss == 'dismiss') {
                    update_user_meta($user, $do_dismiss, 1);
                }
                return true;
                break;

            case 'stats':

                if (!is_super_admin()) {
                    return false;
                }

                if ($dismiss == 'dismiss') {
                    update_user_meta($user, $do_dismiss, 1);
                }
                return true;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * API to send stats
     *
     * Function to send stats
     *
     * @since 2.1.0
     *
     * @return bool True on success.
     */
    private function stats_core()
    {
        $phpversion = phpversion();
        $url = 'https://api.wpvar.com/wp-json/wp-shamsi/v1/stats/';
        $response = wp_safe_remote_post($url, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array(
                        'wpsh_url' => (string)get_bloginfo('url'),
                        'wpsh_name' => (string)get_bloginfo('name'),
                        'wpsh_description' => (string)get_bloginfo('description'),
                        'wpsh_admin_email' => (string)get_bloginfo('admin_email'),
                        'wpsh_version' => (string)get_bloginfo('version'),
                        'wpsh_language' => (string)get_bloginfo('language'),
                        'wpsh_plugins' => json_encode(get_option('active_plugins')),
                        'wpsh_theme' => (string)wp_get_theme(),
                        'wpsh_php' => (string)$phpversion,
                        'wpsh_shamsi_version' => WPSH_VERSION,
                        'wpsh_shamsi_data' => json_encode(get_option('wpsh'))

                    ),
                    'cookies' => array()
                ));

        if (is_wp_error($response)) {
            return false;
        }

        return true;
    }

    /**
     * Stats UI
     *
     * Validates permission and displays sending stats form
     *
     * @since 2.1.0
     *
     */
    public function stats()
    {
        $user = get_current_user_id();
        if (parent::option('activate-stats', true, false) || get_user_meta($user, 'wpsh_stats_dismiss', true) == 1) {
            return false;
        }

        $link = get_admin_url() . 'index.php?wpsh_stats=dismiss'; ?>

        <div class="notice notice-success is-dismissible">
            <div class="wpsh_stats">
                <form method="POST" id="wpsh_stats_form">
                    <h3><?php _e('ارسال آمار', 'wpsh') ?>
                    </h3>
                    <p>
                        <?php _e('با کلیک برروی ارسال آمار، اطلاعات وردپرس شما در دسترس ما قرار می‌گیرد. با ارسال این اطلاعات و داده‌ها به ما کمک فراوانی می‌کنید تا افزونه را بهتر و دقیق‌تر توسعه بدهیم تا بیشترین سازگاری را با خواسته‌های جامعه وردپرس فارسی داشته باشد. برای دریافت جزئیات بیشتر و مطالعه حریم‌خصوصی به قسمت "درباره و قوانین" در <a href=" ' . get_admin_url() . 'admin.php?page=wpsh" title="تنظیمات افزونه تاریخ شمسی و فارسی ساز وردپرس">تنظیمات</a> افزونه مراجعه کنید.', 'wpsh') ?>
                    </p>
                    <input type="hidden" name="wpsh_stats" value="1">
                    <button type="submit" form="wpsh_stats_form" class="button button-primary" value="Submit"><?php _e('ارسال آمار', 'wpsh') ?></button>
                    <a href="<?php echo esc_url($link) ?>"
                        class="button wpsh_dismiss"><?php _e('دیگر نشان نده', 'wpsh') ?></a>
                </form>
            </div>
        </div>
    <?php
    }
}
