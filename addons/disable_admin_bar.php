<?php

/**
 * @package WPSH
 */
defined('ABSPATH') or die();

/**
 * Disable Admin bar
 *
 * Class to Disable wordpress Admin bar
 *
 * @since 1.2.1
 */
class WPSH_Disable_Admin_Bar_Addon extends WPSH_Addons
// You can use WPSH_Core class as well

{

    function __construct()
    {
        global $wpsh_addon;

        // نامک افزودنی - به انگلیسی
        $slug = 'disable_admin_bar';
        // نسخه افزودنی
        $version = '1.0.0';
        // نام افزودنی
        $name = __('غیرفعال کردن نوار مدیریت', 'wpsh');
        // توضیحات افزودنی
        $desc = __('درصورت فعال کردن این گزینه نوار مدیریت در محیط کاربری وردپرس و قالب نمایش داده نخواهد شد', 'wpsh');
        // نام نویسنده افزودنی
        $author = 'علی فرجی';
        // وبسایت نویسنده افزودنی
        $website = 'https://wpvar.com';
        // صفحه معرفی افزودنی برای کسب اطلاعات بیشتر
        $addon_home = 'https://wpvar.com/wp-shamsi';
        // آیا افزودنی به صورت پیشفرض فعال باشد
        $is_active = false;

        $wpsh_addon[] = array(
            'slug' => $slug,
            'version' => $version,
            'name' => $name,
            'desc' => $desc,
            'author' => $author,
            'website' => $website,
            'addon_home' => $addon_home,
            'is_active' => $is_active,

        );

        if (!parent::validate($slug, $is_active)) {
            return false;
            die();
        }

        if (!is_admin()) {
            add_filter('show_admin_bar', array(
                $this,
                'admin_bar'
            ));
        }
    }

    public function admin_bar($bar)
    {
        return false;
    }
}

new WPSH_Disable_Admin_Bar_Addon();
