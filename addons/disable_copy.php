<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Disable Copy Addon
 *
 * Class to Disable Copy from wordpress
 *
 * @since 1.2.1
 */
class WPSH_Addon_Disable_Copy extends WPSH_Addons
// You can use WPSH_Core class as well

{

    function __construct()
    {
        global $wpsh_addon;

        // نامک افزودنی - به انگلیسی
        $slug = 'disable_copy';
        // نسخه افزودنی
        $version = '1.0.0';
        // نام افزودنی
        $name = __('غیرفعال کردن کلیک راست', 'wpsh');
        // توضیحات افزودنی
        $desc = __('درصورت فعال کردن این گزینه، کلیک راست و امکان کپی کردن محتوا از محیط کاربری وردپرس حذف خواهد شد', 'wpsh');
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

        add_action('wp_enqueue_scripts', array(
            $this,
            'script'
        ));
    }

    public function script()
    {
        wp_enqueue_script('wpsh-addons', WPSH_URL . 'assets/js/wpsh_addons.js', array(
            'jquery'
        ), WPSH_VERSION, true);
        wp_add_inline_script('wpsh-addons', '
          jQuery(document).bind("copy", function(e) {
              e.preventDefault();
              });
          jQuery(document).bind("cut", function() {
              e.preventDefault();
          });
          jQuery(document).bind("contextmenu", function(e) {
              e.preventDefault();
          });
      ');
    }
}

new WPSH_Addon_Disable_Copy();
