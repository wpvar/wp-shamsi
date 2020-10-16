<?php
/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Price Addon
 *
 * Class to register and run Addon
 *
 * @since 1.2.0
 */
class WPSH_Addon_Woocommerce_Price extends WPSH_Addons
// You can use WPSH_Core class as well

{

    function __construct()
    {
        global $wpsh_addon;

        // نامک افزودنی - به انگلیسی
        $slug = 'woo_price';
        // نسخه افزودنی
        $version = '1.0.0';
        // نام افزودنی
        $name = __('فارسی سازی قیمت ووکامرس', 'wpsh');
        // توضیحات افزودنی
        $desc = __('تبدیل قیمت های ووکامرس به رایگان درصورت 0 بودن یا خالی بودن آن ها', 'wpsh');
        // نام نویسنده افزودنی
        $author = 'علی فرجی';
        // وبسایت نویسنده افزودنی
        $website = 'https://wpvar.com';
        // صفحه معرفی افزودنی برای کسب اطلاعات بیشتر
        $addon_home = 'https://wpvar.com/wp-shamsi';

        $wpsh_addon[] = array(
            'slug' => $slug,
            'version' => $version,
            'name' => $name,
            'desc' => $desc,
            'author' => $author,
            'website' => $website,
            'addon_home' => $addon_home,

        );

        if (!parent::validate($slug))
        {
            return false;
            die();
        }
        if (class_exists('WooCommerce'))
        {
            add_filter('woocommerce_get_price_html', array(
                $this,
                'addon'
            ) , 10, 2);
        }

    }

    public function addon($price, $product)
    {
        if ($product->get_price() == 0)
        {
            if ($product->is_on_sale() && $product->get_regular_price())
            {
                $regular_price = wc_get_price_to_display($product, array(
                    'qty' => 1,
                    'price' => $product->get_regular_price()
                ));

                $price = wc_format_price_range($regular_price, __('رایگان', 'woocommerce'));
            }
            else
            {
                $price = '<span class="amount">' . __('رایگان', 'woocommerce') . '</span>';
            }
        }

        return $price;
    }
}

new WPSH_Addon_Woocommerce_Price();