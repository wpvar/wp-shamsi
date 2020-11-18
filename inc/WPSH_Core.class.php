<?php
/**
 * @package WPSH
 */

defined('ABSPATH') or die();

use WpshDate\WPSH_Jalali;
use WpshDate\WPSH_Date;

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
        register_activation_hook(WPSH_FILE, array(
            $this,
            'init'
        ));

        if (function_exists('wp_date'))
        {
            add_filter('wp_date', array(
                $this,
                'wp_shamsi'
            ) , 10, 4);
        }
        else
        {
            add_filter('date_i18n', array(
                $this,
                'wp_shamsi'
            ) , 10, 4);
        }

        add_action('wp_enqueue_scripts', array(
            $this,
            'script'
        ) , 999);

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

        add_filter('wp_insert_post_data', array( //wp_update_post_data
            $this,
            'save_post_date'
        ) , 99, 1);
        add_filter('wp_update_comment_data', array(
            $this,
            'save_comment_date'
        ) , 99, 1);

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
        delete_transient('dash_v2_' . md5('dashboard_primary_fa_IR'));
        delete_transient('feed_' . md5('dashboard_primary_fa_IR'));
        wp_cache_flush();

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
        if ($this->option('persian-num', true, true)):
            wp_enqueue_script('wpsh', WPSH_URL . 'assets/js/wpsh.js', array(
                'jquery'
            ));
        endif;

        if (get_locale() == 'fa_IR' || get_locale() == 'fa_AF'):
            wp_enqueue_style('wpsh-style', WPSH_URL . 'assets/css/wpsh_custom.css');
            wp_add_inline_style('wpsh-style', (string)$this->option('fa-custom-css'));
        endif;

        $theme = wp_get_theme()->stylesheet;
        if (in_array($theme, $this->supported_themes()))
        {
            $this->themes($theme);
        }

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
            'twentytwentyone', // Since 2.0.0
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
        if (!$this->option('fa-theme', true, true))
        {
            return false;
        }
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
            include WPSH_PATH . 'themes/' . $theme . '.theme.php';
        }
        else
        {
            $css .= '
            .wp-block textarea {
              font-family: Vazir, tahoma, sans-serif, arial !important;
            }
          ';
        }

        wp_enqueue_style('wpsh-theme', WPSH_URL . 'assets/css/wpsh_theme.css');

        wp_add_inline_style('wpsh-theme', (string)$css);

    }

    /**
     * Change login page style
     *
     * Make login pages style farsi friendly.
     *
     * @since 2.0.0
     *
     */
    public function login_themes()
    {

        wp_enqueue_style('wpsh-admin-css', WPSH_URL . 'assets/css/wpsh_admin.css');

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
     * @since 2.0.0
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
        $format = str_replace('+', '', $format);

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
    public function wp_shamsi($date = null, $format = null, $timestamp = null, $timezone = null)
    {

        $date = $this->normalize_date($date);

        if ($date != null)
        {
            $check_point = date('Y', strtotime($date));
            if ($check_point < 1970)
            {
                return $date;
            }
        }

        if (!$this->option('activate-shamsi', true, true))
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

        if ($timezone == null)
        {
            $timezone = $this->timezone();
        }

        $date = new WPSH_Jalali($timestamp, $timezone);
        $date = $date->format($format);

        /* Deprecated since 2.0.0 */
        //$date = $this->persian_num($date);
        /* Filter returned date to extend plugins developement capacity */
        return apply_filters('wp_jdate', $date, $format, $timestamp, $timezone);

    }

    /**
     * Post date to gregorian
     *
     * Converts any shamsi dates that wants to be stored in posts database to gregorian.
     *
     * @since 2.0.0
     *
     * @param array $data post data in array.
     * @return array new validated post in array.
     */
    public function save_post_date($data)
    {
        $check_point = date('Y', strtotime($data['post_date']));
        if ($check_point >= 1970)
        {
            return $data;
        }
        $data['post_date'] = $this->gregorian($data['post_date'], 'Y-m-d H:i:s');
        $data['post_date_gmt'] = $this->gregorian($data['post_date_gmt'], 'Y-m-d H:i:s');

        return $data;
    }

    /**
     * Comment date to gregorian
     *
     * Converts any shamsi dates that wants to be stored in comments database to gregorian.
     *
     * @since 2.0.0
     *
     * @param array $data comment data in array.
     * @return array new validated comment data in array.
     */
    public function save_comment_date($data)
    {
        $check_point = date('Y', strtotime($data['comment_date']));
        if ($check_point >= 1970)
        {
            return $data;
        }
        $data['comment_date'] = $this->gregorian($data['comment_date'], 'Y-m-d H:i:s');
        $data['comment_date_gmt'] = $this->gregorian($data['comment_date_gmt'], 'Y-m-d H:i:s');

        return $data;
    }

    /**
     * Farsi numbers to English
     *
     * Farsi numbers should not be processed within PHP or Database
     *
     * @since 2.0.0
     *
     * @param string $data Date wich we want to validate.
     * @return string new validated date.
     */
    private function normalize_date($data)
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

        $data = str_replace($fa, $en, $data);

        return $data;
    }

    /**
     * Shamsi to Gregorian
     *
     * Convert Shamsi dates to Gregorian.
     *
     * @since 2.0.0
     *
     * @param mixed $date Date to convert.
     * @param string $format Format of converted dates.
     * @return mixed Converted date.
     */
    public function gregorian($date, $format = null)
    {

        $date = $this->normalize_date($date);

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
     * @deprecated 2.0.0
     *
     * @param int $content Number to convert.
     * @return int Converted number.
     */
    private function persian_num($content) // Display dates in persian numbers evein if jQuery is not available or dates displayed in admin area

    {

        if (!$this->option('persian-num', true, true))
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

}

