<?php
/**
 * Plugin Name:       تاریخ شمسی و فارسی ساز وردپرس
 * Plugin URI:        https://wpvar.com/wp-shamsi
 * Description:       تبدیل تاریخ وردپرس به هجری شمسی براساس تقویم ایران و فارسی سازی رابط کاربری وردپرس
 * Version:           1.4.0
 * Requires at least: 4
 * Requires PHP:      5.3
 * Author:            wpvar.com
 * Author URI:        https://wpvar.com/
 * License:           GNU Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wpsh
 * @package WPSH
 */

/* Setting up WP shamsi */
defined('ABSPATH') or die();

define('WPSH_URL', plugin_dir_url(__FILE__));
define('WPSH_PATH', plugin_dir_path(__FILE__));

define('WPSH_VERSION', '1.4.0 بتا'); //.VERSION !!
if (!class_exists('WPSH_DateAbstract'))
{
    require_once plugin_dir_path(__FILE__) . 'lib/Date/DateAbstract.php';
}
if (!class_exists('WPSH_Jalali'))
{
    require_once plugin_dir_path(__FILE__) . 'lib/Date/Jalali.php';
}
if (!class_exists('WPSH_Date'))
{
    require_once plugin_dir_path(__FILE__) . 'lib/Date/Date.php';
}
if (!class_exists('WPSH_Addons'))
{
    require_once plugin_dir_path(__FILE__) . 'inc/WPSH_Addons.class.php';
}
foreach (glob(WPSH_PATH . 'compatibility/*.php') as $filename)
{
    include_once $filename;

}
if (is_admin())
{

    require_once plugin_dir_path(__FILE__) . 'lib/Options/options-class.php';
    require_once plugin_dir_path(__FILE__) . 'inc/WPSH_Options.class.php';

}

use WpshDate\WPSH_Jalali;
use WpshDate\WPSH_Date;

if (!class_exists('WPSH_Calendar'))
{
    require_once plugin_dir_path(__FILE__) . 'inc/WPSH_Calendar.class.php';
}

/**
 * Core class
 *
 * Main class that runs WP Shamsi
 *
 * @since 1.0.0
 */
class WPSH_Core
{
    /**
     * Construction
     *
     * Construct WPSH_Core class runs required hooks.
     *
     * @since 1.0.0
     *
     */
    function __construct()
    {

        register_activation_hook(__FILE__, array(
            $this,
            'init'
        ));

        if (function_exists('wp_date'))
        {
            add_filter('wp_date', array(
                $this,
                'wp_shamsi'
            ) , 10, 3);
        }
        else
        {
            add_filter('date_i18n', array(
                $this,
                'wp_shamsi'
            ) , 10, 3);
        }

        if ($this->option('activate-shamsi-archive', true))
        {
            add_filter('get_archives_link', array(
                $this,
                'archive'
            ) , 10, 7);
        }

        if (is_admin())
        {
            add_action('wp_dashboard_setup', array(
                $this,
                'add_dashboard'
            ));
        }

        add_action('wp_enqueue_scripts', array(
            $this,
            'script'
        ) , 11);

        add_action('admin_enqueue_scripts', array(
            $this,
            'admin_script'
        ) , 1);

        add_action('login_enqueue_scripts', array(
            $this,
            'login_themes'
        ));

        if (!empty($this->option('translate-group') [0]['translate-target']))
        {
            add_filter('gettext', array(
                $this,
                'translate'
            ) , 20, 1);
            add_filter('ngettext', array(
                $this,
                'translate'
            ) , 20, 1);
        }

        if (get_locale() == 'fa_IR' || get_locale() == 'fa_AF')
        {
            add_filter('wp_mail', array(
                $this,
                'email'
            ));
        }

        add_filter('plugin_action_links_' . plugin_basename(__FILE__) , array(
            $this,
            'add_settings_link'
        ));

        if (get_locale() != 'fa_IR' && get_locale() != 'fa_AF')
        {
            add_action('admin_notices', array(
                $this,
                'no_farsi'
            ));
        }

        if (get_locale() == 'fa_IR' || get_locale() == 'fa_AF' || is_admin())
        {
            add_filter('dashboard_secondary_link', array(
                $this,
                'wpsh_dashboard_link'
            ));

            add_filter('dashboard_secondary_feed', array(
                $this,
                'wpsh_dashboard_feed'
            ));

            add_action('current_screen', array(
                $this,
                'farsi_support'
            ));
            add_action('admin_bar_menu', array(
                $this,
                'date_bar'
            ) , 1000);
        }

    }

    /**
     * Activation Hook function
     *
     * Runs when plugin is activated.
     *
     * @since 1.0.0
     *
     */
    public function init()
    {

        update_option('start_of_week', 6);
        load_plugin_textdomain('wpsh');

    }

    /**
     * Farsi not installed
     *
     * Triggers warning that Farsi language pack is not installed in wordpress.
     *
     * @since 1.2.0
     *
     */
    function no_farsi()
    {
?>
        <div class="notice notice-warning is-dismissible">
          <p><?php _e('<strong>هشدار:</strong> بسته زبانی فارسی وردپرس فعال نیست. برای فعال سازی آن <a href="' . get_admin_url() . 'options-general.php">از این صفحه</a> زبان سایت را به <strong>فارسی</strong> تغییر دهید', 'wpsh'); ?></p>
        </div>
        <?php
    }

    /**
     * Settings shortcut
     *
     * Adds settings page shortcit link to plugins page.
     *
     * @since 1.2.0
     *
     * @param array $links array of existing links.
     * @return array Link to settings page.
     */
    public function add_settings_link($links)
    {

        $settings_link = '<a href="' . get_admin_url() . 'admin.php?page=wpsh">' . __('تنظیمات', 'wpsh') . '</a>';
        $wpvar = '<a href="https://wpvar.com/edu/" target="_blank">' . __('آموزش وردپرس', 'wpsh') . '</a>';
        $forum_link = '<a href="https://wpvar.com/forums/" target="_blank">' . __('انجمن پشتیبانی', 'wpsh') . '</a>';

        array_push($links, $settings_link, $forum_link, $wpvar);
        return $links;

    }

    /**
     * Get options
     *
     * Function to render get_option with required modifications.
     *
     * @since 1.2.0
     *
     * @param string $option Option name.
     * @param bool $bool Requested option is boolean or not.
     * @return mixed Result of get_option.
     */
    public function option($option, $bool = false, $default = true)
    {
        $options = get_option('wpsh');

        if (!isset($options[$option]) && $default === true)
        {
            return true;
        }
        if (!isset($options[$option]) && $default === false)
        {
            return false;
        }
        if ($bool === true)
        {
            if ($options[$option] == 'yes'):
                return true;
            else:
                return false;
            endif;
        }
        else
        {
            return $options[$option];
        }
    }

    /**
     * Load JS and CSS
     *
     * Loads JS and CSS in user interface.
     *
     * @since 1.0.0
     *
     */
    public function script()
    {
        if ($this->option('persian-num', true)):
            wp_enqueue_script('wpsh-num', plugin_dir_url(__FILE__) . 'assets/js/wpsh_num.js', array(
                'jquery'
            ));
        endif;

        if (get_locale() == 'fa_IR' || get_locale() == 'fa_AF'):
            wp_enqueue_style('wpsh-style', plugin_dir_url(__FILE__) . 'assets/css/wpsh_custom.css');
            wp_add_inline_style('wpsh-style', (string)$this->option('fa-custom-css'));
        endif;

        $theme = wp_get_theme()->stylesheet;
        if (in_array($theme, $this->supported_themes()))
        {
            $this->themes($theme);
        }

    }

    /**
     * Load JS and CSS
     *
     * Loads JS and CSS in admin interface.
     *
     * @since 1.0.0
     *
     */
    public function admin_script()
    {
        if ($this->option('dashboard-font', true)):

            $this->themes('wp-admin'); // Since 1.2.0
            wp_enqueue_style('wpsh-admin-css', plugin_dir_url(__FILE__) . 'assets/css/wpsh_admin.css');
        endif;

        if ($this->option('persian-admin-num', true)):
            wp_enqueue_script('wpsh-num', plugin_dir_url(__FILE__) . 'assets/js/wpsh_num.js', array(
                'jquery'
            ));
        endif;
    }

    /**
     * Supported themes list
     *
     * Display themes which WP shamsi supported and makes them farsi compatible.
     * @see WPSH_Options
     *
     * @since 1.2.0
     *
     * @return array array of supported themes.
     */
    public function supported_themes()
    {
        $themes = array(
            'twentytwentyone', // Since 1.4.0
            'twentytwenty', // Since 1.2.0
            'twentynineteen', // Since 1.2.0
            'astra', // Since 1.2.0
            'hello-elementor', // Since 1.2.0
            'oceanwp', // Since 1.2.0
            'storefront', // Since 1.2.0
            'shapely', // Since 1.2.0
            'newstore', // Since 1.2.0

        );

        return $themes;
    }

    /**
     * Make supported themes farsi compatible.
     *
     * Detects wether current theme is supported by WP Shamsi and if so applies css changes and font modifications.
     * @see  supported_themes/WPSH_Core
     *
     * @since 1.2.0
     *
     * @param string $theme Name of current theme.
     */
    public function themes($theme)
    {
        if (!$this->option('fa-theme', true))
        {
            return false;
        }
        $path = plugin_dir_url(__FILE__) . 'assets/fonts/';
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
        @font-face {
            font-family: Vazir;
            src: url(' . $path . 'Vazir-Black.ttf) format("truetype");
            font-weight: 900;
            font-style: normal;
        }
        @font-face {
            font-family: Vazir;
            src: url(' . $path . 'Vazir-Medium.ttf) format("truetype");
            font-weight: 500;
            font-style: normal;
        }
        @font-face {
            font-family: Vazir;
            src: url(' . $path . 'Vazir-Light.ttf) format("truetype");
            font-weight: 300;
            font-style: normal;
        }
        @font-face {
            font-family: Vazir;
            src: url(' . $path . 'Vazir-Thin.ttf) format("truetype");
            font-weight: 100;
            font-style: normal;
        }
        ';
        if ($theme != 'wp-admin')
        {
            include plugin_dir_path(__FILE__) . 'themes/' . $theme . '.theme.php';
        }
        else
        {
            $css .= '
            .wp-block textarea {
              font-family: Vazir, tahoma, sans-serif, arial !important;
            }
          ';
        }

        wp_enqueue_style('wpsh-theme', plugin_dir_url(__FILE__) . 'assets/css/wpsh_theme.css');

        wp_add_inline_style('wpsh-theme', (string)$css);

    }

    /**
     * Change login page style
     *
     * Make login pages style farsi friendly.
     *
     * @since 1.4.0
     *
     */
    public function login_themes() {

      wp_enqueue_style('wpsh-admin-css', plugin_dir_url(__FILE__) . 'assets/css/wpsh_admin.css');

      $this->themes('wp-admin');
      
    }

    /**
     * Translate strings
     *
     * Function to translate strings to farsi.
     *
     * @since 1.2.0
     *
     * @param string $string String to translate.
     * @return string Translated string.
     */
    public function translate($string)
    {

        $txts = (array)$this->option('translate-group');
        foreach ($txts as $txt)
        {

            if (!isset($txt['translate-source']) || !isset($txt['translate-target'])):
                return $string;
            endif;

            $string = str_ireplace($txt['translate-source'], $txt['translate-target'], $string);
        }
        return $string;
    }

    /**
     * Filter outgoing mails
     *
     * Filters mails to change and update support site
     *
     * @since 1.4.0
     *
     * @param array $args Email function arguments.
     * @return array Returns filtered arguments.
     */
    public function email($args)
    {

        $args['message'] = str_ireplace('http://wp-persian.com/', 'https://wpvar.com/', $args['message']);
        $args['message'] = str_ireplace('WP-Persian.com', 'wpvar.com', $args['message']);

        return $args;
    }

    /**
     * Get Timezone
     *
     * Gets Correct timezone.
     *
     * @since 1.0.0
     *
     * @return string Timezone format.
     */
    private function timezone()
    {
        $utc = (wp_timezone_override_offset()) ? wp_timezone_override_offset() : get_option('gmt_offset');

        $format = explode('.', $utc);

        if (isset($format[1]))
        {
            $result = (($format[0] > 0) ? '+' . $format[0] : $format[0]) . ':' . (($format[1] == 5) ? '30' : $format[1]);
        }
        elseif (isset($format[0]) && !isset($format[1]))
        {
            if ($format[0] == 0)
            {
                return 0;
            }
            $result = ($format[0] > 0) ? '+' . $format[0] . ':00' : $format[0] . ':00';
        }
        else
        {
            $result = 0;
        }

        return $result;
    }

    /**
     * Make dates Jalali aka Shamsi
     *
     * Convert wordpress dates to Shamsi aka Jalali dates.
     *
     * @since 1.0.0
     *
     * @param string $date Date to convert.
     * @param string $format format of dates.
     * @param int $timestamp Date in timestamp.
     * @return mixed converted date.
     */
    public function wp_shamsi($date = null, $format = null, $timestamp = null)
    {

        if (!$this->option('activate-shamsi', true))
        {
            return $date;
        }

        if ($this->option('activate-admin-shamsi', true, false) && is_admin())
        {
            return $date;
        }

        if ($format == null)
        {
            $format = 'Y m d H:i:s';
        }

        $format = ($format == 'F j, Y') ? 'j F, Y' : $format; // Make date readable without changing default format.
        $format = str_replace(',', '', $format);
        $format = str_replace('،', '', $format);
        $format = str_replace('.', '', $format);
        $format = str_replace('S', '', $format);
        $format = str_replace('js', 'j s', $format);

        $date = new WPSH_Jalali($timestamp, $this->timezone());
        $date = $date->format($format);

        /* Deprecated since 1.4.0 */
        //$date = $this->persian_num($date);
        /* Filter returned date to extend plugins developement capacity */
        return apply_filters('wp_jdate', $date, $format, $timestamp);

    }

    /**
     * Shamsi to Gregorian
     *
     * Convert Shamsi dates to Gregorian.
     *
     * @since 1.4.0
     *
     * @param mixed $date Date to convert.
     * @param string $format Format of converted dates.
     * @return mixed Converted date.
     */
    public function gregorian($date, $format = null)
    {

        if ($format == null)
        {
            $format = 'Y m d H:i:s';
        }

        $date = new WPSH_Jalali($date, $this->timezone());
        $date = $date->tog()
            ->format($format);

        return $date;
    }

    /**
     * Latin numbers to Farsi
     *
     * Before showing dates converts its latin numbers to farsi.
     *
     * @deprecated 1.4.0
     *
     * @param int $content Number to convert.
     * @return int Converted number.
     */
    private function persian_num($content) // Display dates in persian numbers evein if jQuery is not available or dates displayed in admin area

    {
        if (!$this->option('persian-num', true))
        {
            return $content;
        }
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

    /**
     * Convert archives date
     *
     * Convert archives date to Shamsi aka Jalali ccalendar.
     *
     * @since 1.0.0
     *
     * @param string $list
     * @param string $url URL to archive.
     * @param string $text Archive text description.
     * @param string $format Optional. Can be 'link', 'option', 'html', or custom. Default 'html'.
     * @param string $before Optional. Content to prepend to the description. Default empty.
     * @param string $after Optional. Content to append to the description. Default empty.
     * @param bool   $selected Optional. Set to true if the current page is the selected archive page.
     * @return string HTML link content for archive.
     */
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

        $text = strip_tags($text);
        $url = esc_url($url);
        $aria_current = $selected ? ' aria-current="page"' : '';

        $text = preg_replace($patterns_en, '', $text);
        $text = preg_replace($patterns, '', $text);

        $year = substr((int)filter_var($text, FILTER_SANITIZE_NUMBER_INT) , -4);
        $month = substr((int)filter_var($list, FILTER_SANITIZE_NUMBER_INT) , -6, -4);

        $date = new WPSH_Jalali(strtotime($year . '/' . $month . '/1'));
        $date = $date->format('F Y');

        $text = str_replace($year, $date, $text);

        /* Deprecated since 1.4.0 */
        //$text = $this->persian_num($text);
        if ('link' === $format)
        {
            $result = "\t<link rel='archives' title='" . esc_attr($text) . "' href='$url' />\n";
        }
        elseif ('option' === $format)
        {
            $selected_attr = $selected ? " selected='selected'" : '';
            $result = "\t<option value='$url'$selected_attr>$before $text $after</option>\n";
        }
        elseif ('html' === $format)
        {
            $result = "\t<li>$before<a href='$url'$aria_current>$text</a>$after</li>\n";
        }
        else
        {
            $result = "\t$before<a href='$url'$aria_current>$text</a>$after\n";
        }

        return $result;
    }

    /**
     * Registering dashboard
     *
     * Registering WP Shamsi dashboard.
     *
     * @since 1.0.0
     *
     */
    public function add_dashboard()
    {
        add_meta_box('rss_dashboard', 'وردپرس فارسی', array(
            $this,
            'dashboard'
        ) , 'dashboard', 'normal', 'high');
    }

    /**
     * Dashboard content
     *
     * Make WP Shamsi dahboard.
     *
     * @since 1.0.0
     *
     */
    public function dashboard()
    {
        $transient = get_transient('wpsh_dashboard_site_feed');

        if (!WP_DEBUG && $transient)
        {
            echo $transient;
            return;
        }

        include_once (ABSPATH . WPINC . '/feed.php');

        $rss = fetch_feed('https://wpvar.com/feed');

        if (!is_wp_error($rss)):
            $maxitems = $rss->get_item_quantity(5);
            $rss_items = $rss->get_items(0, $maxitems);
            $rss_title = '<a href="' . $rss->get_permalink() . '" target="_blank">' . strtoupper($rss->get_title()) . '</a>';
        endif;

        $html = '<div class="rss-widget">';
        $html .= '<ul>';

        if ($maxitems == 0)
        {
            $html .= '<li>یافت نشد</li>';
        }
        else
        {
            foreach ($rss_items as $item):
                $item_date = human_time_diff($item->get_date('U') , current_time('timestamp')) . ' پیش';
                $html .= '<li>';
                $html .= '<a href="' . esc_url($item->get_permalink()) . '" title="' . $item_date . '">';
                $html .= '<strong>' . esc_html($item->get_title()) . '</strong>';
                $html .= '</a>';
                $html .= ' <span class="rss-date">' . $item_date . '</span><br />';
                $content = $item->get_content();
                $content = wp_html_excerpt($content, 120) . ' ...';
                $html .= $content;
                $html .= '</li>';
            endforeach;
        }
        $html .= '</ul></div>';
        set_transient('wpsh_dashboard_site_feed', $html, 12 * HOUR_IN_SECONDS);
    }

    /**
     * Filter new dashboard
     *
     * Display recent wordpress news in farsi
     *
     * @since 1.2.1
     *
     * @param string $link link to filter.
     * @return string Filtered link.
     */
    public function wpsh_dashboard_link($link)
    {

        $link = 'https://wpvar.com';

        return $link;
    }

    /**
     * Filter new dashboard feed
     *
     * Display recent wordpress news feed in farsi
     *
     * @since 1.2.1
     *
     * @param string $link link to filter.
     * @return string Filtered feed results.
     */
    public function wpsh_dashboard_feed($link)
    {

        $link = 'https://wpvar.com/post-categories/planet/feed/';

        return $link;
    }

    /**
     * Add farsi support link
     *
     * Add link to free wordpress support forums if locale is farsi.
     *
     * @since 1.4.0
     *
     */
    public function farsi_support()
    {
        $screen = get_current_screen();
        $valid = (isset($_GET['page']) ? true : false);

        if (!$valid)
        {
            $screen->add_help_tab(array(
                'id' => 'farsi-support',
                'title' => 'پشتیبانی فارسی',
                'content' => '<p>همیشه می توانید از طریق <strong><a href="https://wpvar.com/forums/" target="_blank">این لینک</a></strong> سوالات خود را در <strong><a href="https://wpvar.com/forums/" target="_blank">انجمن فارسی</a></strong> وردپرس بپرسید. همچنین می توانید <strong><a href="https://wpvar.com/edu/" target="_blank">بخش آموزش وردپرس</a></strong> را که دارای آموزش های تصویری است، مطالعه کنید.</p>
                              <p><a href="https://wpvar.com" class="button button-primary">وردپرس فارسی</a> <a href="https://wpvar.com/forums/" class="button">انجمن پشتیبانی</a> <a href="https://wpvar.com/edu/" class="button">آموزش وردپرس</a></p>',
                'priority' => 11,
            ));
        }
    }

    /**
     * Add date bar
     *
     * Add date bar to toolbar
     *
     * @since 1.4.0
     *
     * @param object $wp_admin_bar Native Object to add menus to toolbar.
     */
    public function date_bar($wp_admin_bar)
    {
        if (!$this->option('admin-bar-date', true))
        {
            return;
        }

        $jdate = $this->wp_shamsi(null, 'l d F Y', time());
        $gdate = date('l d F Y', time());
        $jtime = $this->wp_shamsi(null, 'g:i a', time());
        $jintdate = $this->wp_shamsi(null, 'Y/m/d', time());
        $gintdate = date('Y/m/d', time());
        if (!current_user_can('manage_options'))
        {
            $args = array(
                'id' => 'wpsh',
                'title' => '<span class="ab-icon"></span>' . $jdate,
                'href' => home_url() ,
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'gdate_bar',
                'title' => $gdate,
                'href' => home_url() ,
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'gintdate_bar',
                'title' => $gintdate,
                'href' => home_url() ,
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'jintdate_bar',
                'title' => $jintdate,
                'href' => home_url() ,
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'wpsh_time_bar',
                'title' => 'ساعت: ' . $jtime,
                'href' => home_url() ,
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);
        }
        else
        {
            $args = array(
                'id' => 'wpsh',
                'title' => '<span class="ab-icon"></span>' . $jdate,
                'href' => admin_url() . 'admin.php?page=wpsh',
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'gdate_bar',
                'title' => $gdate,
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'gintdate_bar',
                'title' => $gintdate,
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'jintdate_bar',
                'title' => $jintdate,
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'wpsh_time_bar',
                'title' => 'ساعت: ' . $jtime,
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);

            $args = array(
                'id' => 'wpsh_settings_bar',
                'title' => 'تنظیمات تاریخ',
                'href' => admin_url() . 'admin.php?page=wpsh',
                'parent' => 'wpsh'
            );
            $wp_admin_bar->add_node($args);
        }
    }
}

new WPSH_Core;

