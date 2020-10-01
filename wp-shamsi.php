<?php
/**
 * Plugin Name:       WP Shamsi
 * Plugin URI:        https://wpvar.com/wp-shamsi
 * Description:       تبدیل تاریخ وردپرس به هجری شمسی براساس تقویم ایران و فارسی سازی رابط کاربری وردپرس
 * Version:           1.0.0
 * Requires at least: 4
 * Requires PHP:      5.3
 * Author:            wpvar.com
 * Author URI:        https://wpvar.com/
 * License:           GNU Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wpsh
 * @package WPSH
 */

defined('ABSPATH') or die();

if (!class_exists('DateAbstract')) {
    require_once plugin_dir_path(__FILE__) . 'lib/Date/DateAbstract.php';
}
if (!class_exists('Jalali')) {
    require_once plugin_dir_path(__FILE__) . 'lib/Date/Jalali.php';
}

use WpshDate\WPSH_Jalali;

class WPSH_Core
{
    function __construct()
    {
      global $gmt;

        register_activation_hook(__FILE__, array(
            $this,
            'init'
        ));

        if (function_exists('wp_date')) {
            add_filter('wp_date', array(
                $this,
                'wp_shamsi'
            ), 10, 3);
        } else {
            add_filter('date_i18n', array(
                $this,
                'wp_shamsi'
            ), 10, 3);
        }

        add_filter('get_archives_link', array(
            $this,
            'archive'
        ), 10, 7);


        add_action('wp_dashboard_setup', array(
            $this,
            'add_dashboard'
        ));

        add_action('wp_enqueue_scripts', array(
            $this,
            'script'
        ), 11);

        add_action('admin_enqueue_scripts', array(
            $this,
            'admin_script'
        ), 1);

    }

    public function init()
    {

        update_option('start_of_week', 6);

    }

    public function script()
    {
        wp_enqueue_script('wpsh-num', plugin_dir_url(__FILE__) . 'assets/js/wpsh_num.js');
    }

    public function admin_script()
    {
        wp_enqueue_style('wpsh-admin-css', plugin_dir_url(__FILE__) . 'assets/css/wpsh_admin.css');
    }

    private function timezone()
    {
      $utc = (wp_timezone_override_offset()) ? wp_timezone_override_offset() : get_option('gmt_offset');

      $format = explode('.', $utc);

      if(isset($format[1]))
      {
        $result = (($format[0] > 0) ? '+' . $format[0] : $format[0]) . ':' . (($format[1] == 5) ? '30' : $format[1]);
      } elseif(isset($format[0]) && ! isset($format[1]))
      {
        $result = ($format[0] > 0) ? '+' . $format[0] : $format[0];
      } else
      {
        $result = 0;
      }

      return $result;
    }

    public function wp_shamsi($date, $format, $timestamp)
    {
        $format = ($format == 'F j, Y') ? 'j F, Y' : $format; // Make date readable without changing default format.

        $format = str_replace(',', '', $format);
        $date   = new WPSH_Jalali($timestamp, $this->timezone());
        $date   = $date->format($format);
        $date   = $this->persian_num($date);

        return apply_filters('wp_jdate', $date); // Filter returned date to extend plugins developement capacity
    }

    private function persian_num($content) // Display dates in persian numbers evein if jQuery is not available or dates displayed in admin area
    {
        $fa = array(
            '۰',
            '۱',
            '۲',
            '۳',
            '۴',
            '۵',
            '۶',
            '۷',
            '۸',
            '۹'
        );

        $en = array(
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9'
        );


        $result = str_replace($en, $fa, $content);

        return $result;
    }

    public function archive($list, $url, $text, $format, $before, $after, $selected)
    {

        $patterns_en = array(
            '/January/',
            '/February/',
            '/March/',
            '/April/',
            '/May/',
            '/June/',
            '/July/',
            '/August/',
            '/September/',
            '/October/',
            '/November/',
            '/December/'
        );

        $patterns = array(
            '/ژانویه/',
            '/فوریه/',
            '/مارس/',
            '/آوریل/',
            '/می/',
            '/ژوئن/',
            '/جولای/',
            '/آگوست/',
            '/سپتامبر/',
            '/اکتبر/',
            '/نوامبر/',
            '/دسامبر/'
        );

        $text         = strip_tags($text);
        $url          = esc_url($url);
        $aria_current = $selected ? ' aria-current="page"' : '';

        $text = preg_replace($patterns_en, '', $text);
        $text = preg_replace($patterns, '', $text);

        $year  = substr((int) filter_var($text, FILTER_SANITIZE_NUMBER_INT), -4);
        $month = substr((int) filter_var($list, FILTER_SANITIZE_NUMBER_INT), -6, -4);

        $date = new WPSH_Jalali(strtotime($year . '/' . $month . '/1'));
        $date = $date->format('F Y');

        $text = str_replace($year, $date, $text);
        $text = $this->persian_num($text);

        if ('link' === $format) {
            $result = "\t<link rel='archives' title='" . esc_attr($text) . "' href='$url' />\n";
        } elseif ('option' === $format) {
            $selected_attr = $selected ? " selected='selected'" : '';
            $result        = "\t<option value='$url'$selected_attr>$before $text $after</option>\n";
        } elseif ('html' === $format) {
            $result = "\t<li>$before<a href='$url'$aria_current>$text</a>$after</li>\n";
        } else {
            $result = "\t$before<a href='$url'$aria_current>$text</a>$after\n";
        }

        return $result;
    }

    public function add_dashboard()
    {
        add_meta_box('rss_dashboard', 'آموزش وردپرس فارسی', array(
            $this,
            'dashboard'
        ), 'dashboard', 'normal', 'high');
    }

    public function dashboard()
    {
        include_once(ABSPATH . WPINC . '/feed.php');

        $rss = fetch_feed('https://wpvar.com/feed');

        if (!is_wp_error($rss)):
            $maxitems  = $rss->get_item_quantity(5);
            $rss_items = $rss->get_items(0, $maxitems);
            $rss_title = '<a href="' . $rss->get_permalink() . '" target="_blank">' . strtoupper($rss->get_title()) . '</a>';
        endif;

        echo '<div class="rss-widget">';
        echo '<ul>';

        if ($maxitems == 0) {
            echo '<li>یافت نشد</li>';
        } else {
            foreach ($rss_items as $item):
                $item_date = human_time_diff($item->get_date('U'), current_time('timestamp')) . ' پیش';
                echo '<li>';
                echo '<a href="' . esc_url($item->get_permalink()) . '" title="' . $item_date . '">';
                echo '<strong>' . esc_html($item->get_title()) . '</strong>';
                echo '</a>';
                echo ' <span class="rss-date">' . $item_date . '</span><br />';
                $content = $item->get_content();
                $content = wp_html_excerpt($content, 120) . ' ...';
                echo $content;
                echo '</li>';
            endforeach;
        }
        echo '</ul></div>';
    }
}

new WPSH_Core;