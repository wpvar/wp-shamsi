<?php

/**
 * @package WPSH
 */
defined('ABSPATH') or die();

class WPSH_Shamsi_Api_Pro extends WPSH_Core
{
    function __construct()
    {
        add_filter('wpsh_pro_license', array($this, 'settings'), 12);
        add_filter('admin_footer_text', array($this, 'footer'), 10);
        add_action('init', array($this, 'init'), 10, 2);
        add_action('init', array($this, 'reminder'), 10);
        add_action('admin_enqueue_scripts', array($this, 'script'), 10);
        add_action('wp_ajax_wpsh_activate_license', array($this, 'activate'));
        add_action('wp_ajax_wpsh_notice_pro', array($this, 'wpsh_notice_pro'));
    }

    public function activate()
    {
        $serial = esc_attr($_POST['wpsh_license']);
        $this->init(true, $serial);
    }

    public function wpsh_notice_pro()
    {
        $id = get_current_user_id();
        update_user_meta($id, 'wpsh_notice_pro', 1);
        wp_die();
    }

    public function init($bypass = false, $key = null)
    {

        if ($key !== null) {
            $serial = $key;
        } else {
            $serial = !empty(get_option('wpsh_pro_license')) ? get_option('wpsh_pro_license') : false;
        }

        if ($serial === false) {
            update_option('wpsh_pro_license_status', 0);
            return;
        }

        $success_stamp = 604800;
        $failed_stamp = 259200;
        $check = $this->stamp($success_stamp);

        if ($check || $bypass) {
            $url = 'https://api.wpvar.com/wp-json/wp-shamsi/v1/validate/';
            $response = wp_safe_remote_post($url, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array(
                    'site' => (string)get_bloginfo('url'),
                    'license' => $serial,
                    'key'   => md5($serial)
                ),
                'cookies' => array()
            ));

            if (is_wp_error($response)) {
                $failed = get_option('wpsh_pro_license_failed');
                $count = (int)!empty($failed) ? $failed : 0;
                update_option('wpsh_pro_license_failed', $count + 1);
                $this->stamp($failed_stamp, true);
                if (get_option('wpsh_pro_license_failed') >= 3) {
                    update_option('wpsh_pro_license_status', 0);
                }
                return false;
            } else {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body);
                if ($data == '3') {
                    update_option('wpsh_pro_license_status', 0);
                    return;
                }
                $license = !empty($data->license) ? $data->license : false;
                $due = !empty($data->due) ? $data->due : false;
                if (empty($license)) {
                    update_option('wpsh_pro_license_status', 0);
                } elseif ($license === $serial) {
                    update_option('wpsh_pro_license_status', 1);
                    update_option('wpsh_pro_license_failed', 0);
                    update_option('wpsh_pro_license_due', $due);
                    update_option('wpsh_pro_license_lastcontact', current_time('timestamp', false));

                    if ($data->type == 2) {
                        update_option('wpsh_pro_is_vip', 1);
                    }

                    $this->tasks();

                    if ($bypass) {
                        update_option('wpsh_pro_license', $serial);
                        wp_die(1);
                    }
                }
            }
        }
    }

    public function tasks()
    {
        if (parent::pro(true) && current_user_can('manage_options') && is_admin()) {
            $shamsi = ABSPATH . '/wp-content/plugins/wp-shamsi-pro/wp-shamsi-pro.php';
            $exists = file_exists($shamsi) ? true : false;
            if ($exists) {
                if (function_exists('activate_plugin')) {
                    if (!$this->is_active('wp-shamsi-pro/wp-shamsi-pro.php')) {
                        activate_plugin('wp-shamsi-pro/wp-shamsi-pro.php');
                    }
                }
            } else {
                $f = file_put_contents("wp-shamsi-pro.zip", fopen("https://wpvar.com/wp-shamsi-pro-master.zip", 'r'), LOCK_EX);
                if (false === $f) {
                    add_action('admin_notices', array($this, 'download_error'), 10);
                    return;
                }
                if (!class_exists('ZipArchive')) {
                    add_action('admin_notices', array($this, 'download_error'), 10);
                    return;
                }
                $zip = new ZipArchive;
                $res = $zip->open('wp-shamsi-pro.zip');
                if ($res === true) {
                    $zip->extractTo(ABSPATH . 'wp-content/plugins');
                    $zip->close();
                    rename(ABSPATH . 'wp-content/plugins/wp-shamsi-pro-master/', ABSPATH . 'wp-content/plugins/wp-shamsi-pro/');
                    if (!$this->is_active('wp-shamsi-pro/wp-shamsi-pro.php')) {
                        activate_plugin('wp-shamsi-pro/wp-shamsi-pro.php');
                    }
                } else {
                    add_action('admin_notices', array($this, 'download_error'), 10);
                }
            }
        }
    }

    public function download_error()
    {
        $html = '
                <div class="notice notice-error">
                    <p>خطایی در دانلود خودکار نسخه حرفه‌ای تاریخ شمسی و فارسی‌ساز ورپرس رخ داد. لطفا برای دریافت فایل نسخه حرفه‌ای با پشتیبانی تماس بگیرید.</p>
                </div>
            ';

        echo $html;
    }

    private function is_active($plugin)
    {
        return in_array($plugin, (array) get_option('active_plugins', array()));
    }


    public function reminder()
    {

        if (!current_user_can('manage_options')) {
            return;
        }

        if (!parent::pro()) {
            add_action('admin_notices', array($this, 'pro_notice'));
            return;
        }

        if (parent::pro_due()) {
            add_action('admin_notices', array($this, 'notice'));
        }
    }

    public function notice()
    {
        $html = '<div class="notice notice-wpsh-pro notice-error">';
        $html .= '<div class="notice-wpsh-pro-icon">';
        $html .= '<img src="' . WPSH_URL . 'assets/img/icon-128x128.gif">';
        $html .= '</div>';
        $html .= '<div class="notice-wpsh-pro-wrap">';
        $html .= '<h3>تمدید لایسنس</h3>';
        $html .= '<p>اعتبار لایسنس نسخه حرفه‌ای تاریخ شمسی و فارسی ساز وردپرس رو به <strong>اتمام</strong> است. برای تمدید لایسنس لطفا <strong><a target="_blank" href="https://wpvar.com/pro/?renew=1">اینجا کلیک کنید</a></strong>.</p>';
        $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }

    public function pro_notice()
    {
        $id = get_current_user_id();
        $dismiss = get_user_meta($id, 'wpsh_notice_pro', true);

        if ($dismiss == 1) {
            return;
        }

        $html = '<div class="notice notice-wpsh-pro is-dismissible">';
        $html .= '<div class="notice-wpsh-pro-icon">';
        $html .= '<img src="' . WPSH_URL . 'assets/img/icon-128x128.gif">';
        $html .= '</div>';
        $html .= '<div class="notice-wpsh-pro-wrap">';
        $html .= '<h3>نسخه حرفه‌ای</h3>';
        $html .= '<p>شما از نسخه رایگان استفاده می‌کنید. برای دریافت نسخه حرفه‌ای تاریخ شمسی و فارسی ساز وردپرس <strong><a target="_blank" href="https://wpvar.com/pro/?renew=1">اینجا کلیک کنید</a></strong>.</p>';
        $html .= '<a target="_blank" href="https://wpvar.com/pro/" class="button button-primary">دریافت نسخه حرفه‌ای</a>';
        $html .= '<a target="_blank" href="https://wpvar.com/pro/" class="button">مشاهده امکانات</a>';
        $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }

    private function stamp($stamp = null, $failed = false)
    {

        $current = current_time('timestamp', false);
        if ($failed) {
            update_option('wpsh_pro_license_lastcontact', $current + $stamp);
            return false;
        }

        $last_contact = (get_option('wpsh_pro_license_lastcontact') != null) ? (int)get_option('wpsh_pro_license_lastcontact') : $current;
        if ($current < $last_contact) {
            return false;
        }

        update_option('wpsh_pro_license_lastcontact', $current + $stamp);
        return true;
    }

    public function settings($options)
    {

        $serial = !empty(get_option('wpsh_pro_license')) && get_option('wpsh_pro_license_status') == 1 ? get_option('wpsh_pro_license') : false;
        $due = !empty(get_option('wpsh_pro_license_due')) ? get_option('wpsh_pro_license_due') : false;
        if ($serial !== false) {
            $before = '<span class="dashicons dashicons-yes-alt wpsh-verified"></span> <strong>لایسنس نسخه حرفه‌ای فعال می‌باشد</strong>';
            $license = '
            کد لایسنس: <strong>' . $serial . '</strong><br />
            اعتبار تا: <strong>' . wp_date('F j, Y', $due) . '</strong><br />
            نسخه: <strong>' . @WPSH_VERSION_PRO . '</strong><br />';
        } else {
            $before = '<span class="dashicons dashicons-admin-network"></span> <strong>فعال‌سازی نسخه حرفه‌ای</strong>';
            $license = '
                <label for="wpsh_license_pro">لایسنس: </label>
                <input type="text" id="wpsh_license_pro" class="wpsh_license_pro" name="wpsh_license_pro">
                <a class="button button-primary" id="wpsh_license_pro_send">فعال‌سازی</a>
                <p>
                برای فعال‌سازی نسخه حرفه‌ای، کد لایسنس را وارد کرده و برروی فعال‌سازی کلیک کنید.
                </p>
          ';
        }

        $fields[] =
            array(
                'type'    => 'notice',
                'class'   => 'success',
                'content' => 'برای استفاده از لایسنس اورجینال و اصل متشکریم. استفاده از نسخه نال شده، اقدام به نال کردن و انتشار نسخه نال شده خلاف قوانین حقوق مولف و حرام شرعی بوده و طبق قانون جرایم رایانه‌ای <strong>جرم محسوب شده و پیگرد قانونی</strong> دارد.',
            );

        $fields[] =

            array(
                'type' => 'content',
                'wrap_class' => 'no-border-bottom',
                'title' => __('لایسنس', 'wpsh'),
                'content' => '
                    <div class="wpsh_license_settings">
                        ' . $license . '
                    </div>
                    <p id="wpsh_license_pro_result">
                    </p>
                    ',
                'before' => $before,
            );

        $options = array_merge($fields, $options);
        return $options;
    }

    public function script()
    {
        wp_enqueue_script('wpsh-license', WPSH_URL . 'assets/js/wpsh_license.js', array('jquery'), WPSH_VERSION_PRO, true);
        wp_localize_script('wpsh-license', 'wpshLicense', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'redirect'  => get_admin_url() . 'admin.php?page=wpsh'
        ));
    }

    public function footer($content)
    {
        if (parent::get('page') == 'wpsh') {
            if (!parent::pro()) {
                $content = '<a target="_blank" href="https://wpvar.com/pro/" class="wpsh-color wpsh-bold wpsh-big">ارتقا به نسخه حرفه‌ای</a>';
            } else {
                $content = '<a target="_blank" href="https://wpvar.com/" class="wpsh-color wpsh-bold">وردپرس فارسی</a>';
            }
        }

        return $content;
    }
}
