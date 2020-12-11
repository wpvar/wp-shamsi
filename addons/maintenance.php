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
        $desc = __('در صورت فعال کردن حالت تعمیر، سایت فقط برای مدیران در دسترس خواهد بود و سایرین با پیغام سایت در دست تعمیر است مواجه خواهند شد. برای مواقعی که تغییراتی در وردپرس ویا قالب انجام میدهید فعال کردن این گزینه سودمند است.  این افزودنی سازگار با سئو می باشد', 'wpsh');
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

        add_filter('get_header', array(
            $this,
            'maintenance'
        ));
    }

    public function maintenance()
    {
        if (!current_user_can('activate_plugins') || !is_user_logged_in()) {
            $path = WPSH_URL . 'assets/fonts/';

            $css = '
                @font-face {
                    font-family: Vazir;
                    src: url(' . $path . 'Vazir.ttf) format("truetype");
                    font-weight: normal;
                    font-style: normal;
                }
                @font-face {
                    font-family: Vazir;
                    src: url(' . $path . 'Vazir-Bold.ttf) format("truetype");
                    font-weight: bold;
                    font-style: normal;
                }
                body {
                  font-family: Vazir, sans-serif, tahoma, arial;
                }
                ';

            wp_die(__('<style>' . $css . '</style><h1>در دست تعمیر</h1> <br /> سایت در دست تعمیر می باشد، به زودی برمیگردیم. لطفا دقایقی دیگر مجددا مراجعه فرمایید.', 'wpsh'), get_bloginfo('name') . ' - ' . __('در دست تعمیر', 'wpsh'), array(
                'response' => '503'
            ));
        }
    }
}

new WPSH_Maintenance_Addon();
