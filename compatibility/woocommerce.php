<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Woocommerce Compability
 *
 * Woocommerce Compability Class
 *
 * @since 2.0.0
 */
class WPSH_Woo extends WPSH_Core

{

    function __construct()
    {

        if (!parent::option('activate-woocommerce', true, true)) {
            return;
        }
        if (class_exists('WooCommerce') && (get_locale() == 'fa_IR' || get_locale() == 'fa_AF')) {
            add_action('admin_enqueue_scripts', array(
                $this,
                'datepicker_script'
            ), 1000);

            add_filter("wp_insert_post_data", array(
                $this,
                "woocommerce_filter"
            ), 1000, 2);

            add_filter("get_post_metadata", array(
                $this,
                "meta"
            ), 10, 4);

            add_action('admin_init', array(
                $this,
                'woocommerce_action'
            ), 1000);

            add_action('admin_init', array(
                $this,
                'dates'
            ), 1000);
        }
    }

    public function woocommerce_filter($post, $arg)
    {
        if (!empty($_POST["_sale_price_dates_from"]) && $post['post_type'] == 'product') {
            $_POST["_sale_price_dates_from"] = esc_attr(parent::gregorian($_POST["_sale_price_dates_from"], 'Y-m-d'));
        }
        if (!empty($_POST["_sale_price_dates_to"]) && $post['post_type'] == 'product') {
            $_POST["_sale_price_dates_to"] = esc_attr(parent::gregorian($_POST["_sale_price_dates_to"], 'Y-m-d'));
        }
        if (!empty($_POST["order_date"]) && $post['post_type'] == 'shop_order') {
            $_POST["order_date"] = esc_attr(parent::gregorian($_POST["order_date"], 'Y-m-d'));
        }
        if (!empty($_POST["expiry_date"]) && $post['post_type'] == 'expiry_date') {
            $_POST["expiry_date"] = esc_attr(parent::gregorian($_POST["expiry_date"], 'Y-m-d'));
        }
        return $post;
    }

    public function woocommerce_action()
    {

        if (isset($_GET["start_date"]) && esc_attr($_GET["page"]) == 'wc-reports') {
            $_GET["start_date"] = esc_attr(parent::gregorian($_GET["start_date"], 'Y-m-d'));
        }
        if (isset($_GET["end_date"]) && esc_attr($_GET["page"]) == 'wc-reports') {
            $_GET["end_date"] = esc_attr(parent::gregorian($_GET["end_date"], 'Y-m-d'));
        }
    }

    public function datepicker_script()
    {
        $page = (isset($_GET["page"])) ? esc_attr($_GET["page"]) : null;
        if (wp_script_is('jquery-ui-datepicker', 'enqueued') && ($this->screen() == 'product' || $this->screen() == 'shop_order' || $this->screen() == 'shop_coupon' || $page == 'wc-reports')) {
            wp_deregister_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-datepicker', WPSH_URL . 'assets/js/wpsh_datepicker.js', array(), false, true);
            wp_localize_script('jquery-ui-datepicker', 'listFarsiMonth', parent::get_month());
        }
    }

    public function screen()
    {
        $screen = get_current_screen();

        return (string)$screen->post_type;
    }

    public function dates()
    {
        $type = isset($_POST['post_type']) && $_POST['post_type'] == 'shop_coupon' ? true : false;
        if (!empty($_POST["expiry_date"]) && $type) {
            $_POST["expiry_date"] = parent::gregorian(esc_attr($_POST["expiry_date"]), 'Y-m-d');
        }
    }

    function meta($metadata, $object_id, $meta_key, $single)
    {
        $action = isset($_GET['action']) && $_GET['action'] == 'edit' ? true : false;
        $post = isset($_GET['action']) ? true : false;
        if ($meta_key == 'date_expires' && !empty($meta_key) && $action && $post) {

            global $wpdb;
            $value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'date_expires'", esc_attr($_GET['post'])));

            if (!empty($value)) {
                $shamsi = parent::wp_shamsi(null, 'Y-m-d', $value);

                return $shamsi;
            }
        }
    }
}

new WPSH_Woo();
