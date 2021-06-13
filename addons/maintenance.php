<?php

/**
 * @package WPSH
 */
defined('ABSPATH') or die();

/**
 * Maintenance mode
 *
 * Class to activate maintenance mode
 *
 * @since 2.0.0
 */
class WPSH_Maintenance_Addon extends WPSH_Addons
// You can use WPSH_Core class as well

{

    function __construct()
    {
        global $wpsh_addon;

        // نامک افزودنی - به انگلیسی
        $slug = 'maintenance';
        // نسخه افزودنی
        $version = '1.0.0';
        // نام افزودنی
        $name = __('در دست تعمیر', 'wpsh');
        // توضیحات افزودنی
        $desc = __('در صورت فعال کردن حالت تعمیر، سایت فقط برای مدیران در دسترس خواهد بود و سایرین با پیغام سایت در دست تعمیر است مواجه خواهند شد. برای مواقعی که تغییراتی در وردپرس ویا قالب انجام میدهید فعال کردن این گزینه سودمند است.  این افزودنی سازگار با سئو می باشد.', 'wpsh');
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

        add_filter('get_header', array($this, 'maintenance'));
    }

    public function maintenance()
    {
        $user = wp_get_current_user();
        if ((!current_user_can('activate_plugins') && !in_array( 'shop_manager', (array) $user->roles )) || !is_user_logged_in()) {

            $font = parent::option('dashboard-font-default', false, 'IRANSansWeb');

            if($font != 'none') {
                $css = parent::font($font);
            } else {
                $css = '';
            }

            $css .= '
                body {
                    font-family: ' . $font . ', tahoma, arial;
                }
            ';

            if (parent::pro()) {
                $text = parent::option('maintenance_text', false, 'سایت در دست تعمیر می‌باشد، به‌زودی برمی‌گردیم. لطفا دقایقی دیگر مجددا مراجعه فرمایید.');
            } else {
                $text = 'سایت در دست تعمیر می‌باشد، به‌زودی برمی‌گردیم. لطفا دقایقی دیگر مجددا مراجعه فرمایید.';
            }
            wp_die(__('<style>' . $css . '</style><h1>در دست تعمیر</h1> <br />' . $text, 'wpsh'), get_bloginfo('name') . ' - ' . __('در دست تعمیر', 'wpsh'), array(
                'response' => '503'
            ));
        }
    }
}

new WPSH_Maintenance_Addon();
