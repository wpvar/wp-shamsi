<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

class WPSH_Shamsi_Api_Pro extends WPSH_Core
{
    public function __construct()
    {
        add_filter('wpsh_pro_license', array($this, 'settings'), 12);
        add_filter('admin_footer_text', array($this, 'footer'), 10);
        add_action('admin_enqueue_scripts', array($this, 'script'), 10);
    }

    public function settings($options)
    {
        if (parent::pro()) {
            $before = '<span class="dashicons dashicons-yes-alt wpsh-verified"></span> <strong>لایسنس فعال می‌باشد</strong>';
            $license = 'لطفا توجه داشته باشید که پشتیبانی از نسخه‌های تجاری افزونه متوقف شده است، ولی همچنان می‌توانید بدون محدودیت از آن استفاده کنید. تمرکز ما درحال حاضر برروی نسخه رایگان بوده و برنامه‌ای برای توسعه ویا ارائه پشتیبانی برای نسخه‌های تجاری درحال‌حاضر وجود ندارد. از این که با حمایت قبلی خود ما را در توسعه این افزونه یاری کردید، سپاس‌گزاریم. 🌹';
        } else {
            $before = '<span class="dashicons dashicons-admin-network"></span> <strong>فعال‌سازی</strong>';
            $license = '
                <p>
                آيا از قبل نسخه تجاری افزونه را تهیه کرده‌اید؟ برای مشاهده راهنمای جدید نحوه فعال سازی لطفا برروی <a href="https://wpvar.com/pro?ni=1" target="_blank">این لینک کلیک کنید</a>.
                </p>
          ';
        }
        $fields[] =
            array(
                'type'    => 'notice',
                'class'   => 'success',
                'content' => 'جهت تمرکز بیشتر برروی توسعه نسخه رایگان، درحال حاضر فروش نسخه‌های جدید تجاری متوقف شده است.',
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
        $nonce = wp_create_nonce('wpsh-api');
        wp_enqueue_script('wpsh-license', WPSH_URL . 'assets/js/wpsh_license.js', array('jquery'), WPSH_VERSION, true);
        wp_localize_script('wpsh-license', 'wpshLicense', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'redirect'  => get_admin_url() . 'admin.php?page=wpsh&_wpnonce=' . $nonce,
            'nonce' =>  $nonce
        ));
    }

    public function footer($content)
    {
        if (parent::get('page') == 'wpsh') {
            $content = '<a target="_blank" href="https://wpvar.com/" class="wpsh-color wpsh-bold">وردپرس فارسی</a>';
        }

        return $content;
    }

    public function icon($name, $title = null, $ext = 'svg')
    {
        $url = WPSH_URL . '/assets/img/pro/' . $name . '.' . $ext;
        $html = '<img src="' . $url . '" title=" ' . $title .  ' " loading="lazy">';
        return $html;
    }
}
