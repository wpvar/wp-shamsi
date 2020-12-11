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
            add_action('admin_init', array(
                $this,
                'woocommerce_action'
            ), 1000);
        }
    }

    public function woocommerce_filter($post, $arg)
    {
        if (isset($_POST["_sale_price_dates_from"]) && $post['post_type'] == 'product') {
            $_POST["_sale_price_dates_from"] = esc_attr(parent::gregorian($_POST["_sale_price_dates_from"], 'Y-m-d'));
        }
        if (isset($_POST["_sale_price_dates_to"]) && $post['post_type'] == 'product') {
            $_POST["_sale_price_dates_to"] = esc_attr(parent::gregorian($_POST["_sale_price_dates_to"], 'Y-m-d'));
        }
        if (isset($_POST["order_date"]) && $post['post_type'] == 'shop_order') {
            $_POST["order_date"] = esc_attr(parent::gregorian($_POST["order_date"], 'Y-m-d'));
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
        if (wp_script_is('jquery-ui-datepicker', 'enqueued') && ($this->screen() == 'product' || $this->screen() == 'shop_order' || $page == 'wc-reports')) {
            wp_deregister_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-datepicker', WPSH_URL . 'assets/js/wpsh_datepicker.js', array(), false, true);
        }
    }

    public function screen()
    {
        $screen = get_current_screen();

        return (string)$screen->post_type;
    }
}

new WPSH_Woo();
