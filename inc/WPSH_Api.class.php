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
    function __construct()
    {
        if (is_admin()) {

            add_action('admin_notices', array(
                $this,
                'newsletter'
            ));

            add_action('admin_notices', array(
                $this,
                'stats'
            ));
            add_action('admin_init', array(
                $this,
                'newsletter_core'
            ));

            add_action('admin_init', array(
                $this,
                'update_stats'
            ));
        }

        add_action('init', array(
            $this,
            'send_stats'
        ));
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
     * API to subscribe user in newsletter
     *
     * Checks if user has permission to subscribe in new letter and if so will send data to REST API
     *
     * @since 2.1.0
     *
     * @return array Respone of endpoit.
     */
    public function newsletter_core()
    {

        $permission = $this->permission('newsletter');

        if (!$permission) {
            return false;
        }

        $email = (parent::post('wpsh_email', 'bool')) ? parent::post('wpsh_email') : (parent::get('wpsh_newsletter_settings', 'bool') ? parent::get('wpsh_newsletter_settings') : null);
        if ($email == null) {
            return null;
        }

        $user = get_current_user_id();
        $url = 'https://api.wpvar.com/wp-json/wp-shamsi/v1/newsletter/';
        $response = wp_safe_remote_post($url, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array(
                'wpsh_email' => $email,
                'wpsh_url' => get_bloginfo('url')
            ),
            'cookies' => array()
        ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $res = __('خطا: ' . $error_message, 'wpsh');
            $type = 'notice notice-error';
        } else {
            $body = wp_remote_retrieve_body($response);

            $data = json_decode($body);
            $res = $data->response;
            if ($res === 1) {
                $type = 'notice notice-success';
                update_user_meta($user, 'wpsh_newsletter_email', $email);
                update_user_meta($user, 'wpsh_newsletter_dismiss', 1);
            } else {
                $type = 'notice notice-error';
            }
        }

        if ($res === 0) {
            $message = __('درخواست معتبر نمی باشد.', 'wpsh');
        } elseif ($res === 1) {
            $message = __('اشتراک شما در خبرنامه وردپرس فارسی با موفقیت ثبت شد.', 'wpsh');
        } elseif ($res === 2) {
            $message = __('ایمیلی وارد نشده است.', 'wpsh');
        } elseif ($res === 3) {
            $message = __('ایمیل وارد شده معتبر نیست.', 'wpsh');
        } else {
            $message = $res;
        }

        $response = array(
            'message' => $message,
            'type' => $type
        );

        return $response;
    }

    /**
     * Newsletter UI
     *
     * Validates subscription status and displays subscription form
     *
     * @since 2.1.0
     *
     */
    public function newsletter()
    {

        $core = $this->newsletter_core();

        $type = isset($core['type']) ? $core['type'] : null;
        $message = isset($core['message']) ? $core['message'] : null;
        $link = get_admin_url() . 'index.php?wpsh_newsletter=dismiss';

        if (is_array($core)) : ?>

            <div class="<?php echo $type ?>">
                <p>
                    <?php echo $message ?>
                </p>
            </div> <?php
                endif;
                if ($type !== 'notice notice-success' && !parent::get('wpsh_newsletter_settings', 'bool') && parent::get('wpsh_newsletter') != 'dismiss' && $core !== false) : ?>
            <div class="notice notice-success is-dismissible">
                <div class="wpsh_newsletter">
                    <form method="POST" id="wpsh_form">
                        <h3><?php _e('خبرنامه وردپرس فارسی', 'wpsh') ?></h3>
                        <p>
                            <?php _e('برای باخبر شدن از آخرین اخبار، بروزرسانی ‎ها و آموزش ‎های وردپرس به زبان فارسی با وارد کردن ایمیل خود در فیلد زیر مشترک خبرنامه شوید.', 'wpsh') ?>
                        </p>
                        <label for="email"><?php _e('ایمیل: ', 'wpsh') ?></label>
                        <input type="email" id="wpsh_email" name="wpsh_email">
                        <button type="submit" form="wpsh_form" class="button button-primary" value="Submit"><?php _e('ثبت اشتراک', 'wpsh') ?></button>
                        <a href="<?php echo $link ?>" class="button wpsh_newsletter_dismiss"><?php _e('دیگر نشان نده', 'wpsh') ?></a>
                    </form>
                    <p id="wpsh_email_validation">
                    </p>
                </div>
            </div>
        <?php
                endif;
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
                if (parent::option('activate-stats', true, false) || get_user_meta($user, 'wpsh_stats_dismiss', true) == 1 || get_user_meta($user, 'wpsh_newsletter_dismiss', true) != 1) {
                    return false;
                }

                $link = get_admin_url() . 'index.php?wpsh_stats=dismiss'; ?>

        <div class="notice notice-success is-dismissible">
            <div class="wpsh_stats">
                <form method="POST" id="wpsh_stats_form">
                    <h3><?php _e('ارسال آمار', 'wpsh') ?></h3>
                    <p>
                        <?php _e('با کلیک برروی ارسال آمار، اطلاعات وردپرس شما در دسترس ما قرار می‌گیرد. با ارسال این اطلاعات و داده‌ها به ما کمک فراوانی می‌کنید تا افزونه را بهتر و دقیق‌تر توسعه بدهیم تا بیشترین سازگاری را با خواسته‌های جامعه وردپرس فارسی داشته باشد. برای دریافت جزئیات بیشتر و مطالعه حریم‌خصوصی به قسمت "درباره و قوانین" در <a href=" ' . get_admin_url() . 'admin.php?page=wpsh" title="تنظیمات افزونه تاریخ شمسی و فارسی ساز وردپرس">تنظیمات</a> افزونه مراجعه کنید.', 'wpsh') ?>
                    </p>
                    <input type="hidden" name="wpsh_stats" value="1">
                    <button type="submit" form="wpsh_stats_form" class="button button-primary" value="Submit"><?php _e('ارسال آمار', 'wpsh') ?></button>
                    <a href="<?php echo $link ?>" class="button wpsh_dismiss"><?php _e('دیگر نشان نده', 'wpsh') ?></a>
                </form>
            </div>
        </div>
<?php
            }
        }
