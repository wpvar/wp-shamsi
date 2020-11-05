<?php
/**
 * @package WPSH
 */
defined('ABSPATH') or die();

/**
 * Disable Admin
 *
 * Class to Disable Access to wp-admin
 *
 * @since 1.4.0
 */
class WPSH_Disable_Admin_Addon extends WPSH_Addons
// You can use WPSH_Core class as well

{

    function __construct()
    {
        global $wpsh_addon;

        // نامک افزودنی - به انگلیسی
        $slug = 'disable_admin';
        // نسخه افزودنی
        $version = '1.0.0';
        // نام افزودنی
        $name = __('غیرفعال کردن دسترسی به مدیریت', 'wpsh');
        // توضیحات افزودنی
        $desc = __('درصورت فعال کردن، دسترسی کاربران با نقش مشترک به محیط مدیریت وردپرس یا همان آدرس "wp-admin" محدود خواهد شد.', 'wpsh');
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

        if (!parent::validate($slug, $is_active))
        {
            return false;
            die();

        }

        if (is_admin())
        {
            add_filter('admin_init', array(
                $this,
                'admin'
            ));
        }

    }

    public function admin($bar)
    {
      $user = wp_get_current_user();
      $disallowed_roles = array(
          'subscriber'
      );
      if (is_admin() && array_intersect($disallowed_roles, $user->roles) && !defined( 'DOING_AJAX' ))
      {
          wp_redirect(home_url());
          exit;
      }
    }

}

new WPSH_Disable_Admin_Addon();