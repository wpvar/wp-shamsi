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
    register_activation_hook(WPSH_FILE, array($this, 'init'));

    if (function_exists('wp_date')) {
      add_filter('wp_date', array($this, 'wp_shamsi'), 10, 4);
    } else {
      add_filter('date_i18n', array($this, 'wp_shamsi'), 10, 4);
    }

    add_filter('human_time_diff', array($this, 'wp_shamsi_diff'), 10, 4);
    add_action('wp_enqueue_scripts', array($this, 'script'), 999);
    add_action('login_enqueue_scripts', array($this, 'login_themes'));
    add_filter('mce_css', array($this, 'tinymce_style'), 10);

    if (!empty($this->option('translate-group')[0]['translate-target'])) {
      add_filter('gettext', array($this, 'translate'), 20, 1);
      add_filter('ngettext', array($this, 'translate'), 20, 1);
    }

    if (get_locale() == 'fa_IR' || get_locale() == 'fa_AF') {
      add_filter('wp_mail', array($this, 'email'));
    }

    //wp_update_post_data
    add_filter('wp_insert_post_data', array($this, 'save_post_date'), 99, 1);
    add_filter('wp_update_comment_data', array($this, 'save_comment_date'), 99, 1);
    add_filter('wp_checkdate', '__return_true');
  }

  /**
   * List farsi months
   *
   * Lists farsi months according to users selection: Fa or Af.
   *
   * @since 2.1.1
   *
   * @return array Array of months in farsi.
   */
  public function get_month()
  {
    $ir = array(
      '1' => "فروردین",
      '2' => "اردیبهشت",
      '3' => "خرداد",
      '4' => "تیر",
      '5' => "مرداد",
      '6' => "شهریور",
      '7' => "مهر",
      '8' => "آبان",
      '9' => "آذر",
      '10' => "دی",
      '11' => "بهمن",
      '12' => "اسفند",
    );

    $af = array(
      '1' => "حمل",
      '2' => "ثور",
      '3' => "جوزا",
      '4' => "سرطان",
      '5' => "اسد",
      '6' => "سنبله",
      '7' => "میزان",
      '8' => "عقرب",
      '9' => "قوس",
      '10' => "جدی",
      '11' => "دلو",
      '12' => "حوت",
    );

    $month = ($this->option('country-select', false, 'ir') == 'ir') ? $ir : $af;

    return $month;
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
  public function option($option, $bool = false, $default = null)
  {
    $options = get_option('wpsh');
    $valid = (!empty($options[$option])) ? true : false;

    if (!$valid) {
      return $default;
    }

    if ($bool === true) {
      if ($options[$option] == 'yes') :
        return true;
      else :
        return false;
      endif;
    } else {
      return $options[$option];
    }
  }

  /**
   * Update Options
   *
   * Function to update plugin options
   *
   * @since 2.1.0
   *
   * @param string $key Key to find.
   * @param bool $value Value to replace.
   */
  public function update($key, $value)
  {

    $option = get_option('wpsh');

    $option[$key] = $value;

    update_option('wpsh', $option);
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
    if ($this->option('persian-num', true, true) && (get_locale() == 'fa_IR' || get_locale() == 'fa_AF')) :

      wp_enqueue_script('wpsh', WPSH_URL . 'assets/js/wpsh.js', array('jquery'), WPSH_VERSION, true);

      $isShamsiInAdmin = array(
        'in_admin' => (is_admin()) ? 1 : 0,
        'base' => false
      );

      wp_localize_script('wpsh', 'isShamsiInAdmin', $isShamsiInAdmin);

    endif;

    if (get_locale() == 'fa_IR' || get_locale() == 'fa_AF') :
      wp_enqueue_style('wpsh-style', WPSH_URL . 'assets/css/wpsh_custom.css', array(), WPSH_VERSION);
      wp_add_inline_style('wpsh-style', (string)$this->option('fa-custom-css'));
    endif;

    $theme = wp_get_theme()->stylesheet;
    if (in_array($theme, $this->supported_themes())) {
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
      'twentytwentytwo', // Since 4.2.0
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

    $css = '';

    if ($theme != 'wp-admin') {

      if (!$this->pro() || !$this->option('activate-fonts', true, false)) {
        $css .= $this->font('Vazir');
      }

      if ($this->option('fa-theme', true, true)) {
        include WPSH_PATH . 'themes/' . $theme . '.theme.php';
      }
    } else {
      if (!$this->pro() || !$this->option('activate-fonts', true, false) || !$this->option('activate-font-admin', true, true)) {
        $font = $this->option('dashboard-font-default', false, 'IRANSansWeb');
        if (!$this->pro() && ($font == 'IRANSansDn' || $font == 'IRANYekanWeb')) {
          $css .= $this->font('IRANSansWeb');
        } else {
          $css .= $this->font($font);
        }
        $css .= '
          .wp-block textarea, .wp-block, .components-menu-item__item, .components-notice__content, .components-panel__body-title p, .components-card__header p, .woocommerce-order-data__heading, .woocommerce-order-data__meta, .components-base-control__field, .components-base-control__help, .components-dropdown-menu__menu, #plugin-information-title.with-banner h2, .block-editor-inserter__search-input, .components-placeholder__label, .components-placeholder__instructions{
            font-family: ' . $font . ', tahoma, sans-serif, arial, dashicons !important;
            letter-spacing: 0;
          }
        ';
        $css .= '
        h1,  h2,  h3,  h4,  h5,  h6,  p,  a,  code,  li,  ul,  strong,  select,  option,  button, p,  input, span, textarea, body {
          font-family: ' . $font . ', tahoma, sans-serif, arial, dashicons, Roboto-Regular, HelveticaNeue, sans-serif;
          letter-spacing: 0;
        }
        #yoast-snippet-preview-container * {
          font-family: Arial, Roboto-Regular, HelveticaNeue, sans-serif;
        }
        ';
        $css .= '
        .rtl .media-frame, .rtl .media-frame .search, .rtl .media-frame input[type=email], .rtl .media-frame input[type=number], .rtl .media-frame input[type=password], .rtl .media-frame input[type=search], .rtl .media-frame input[type=tel], .rtl .media-frame input[type=text], .rtl .media-frame input[type=url], .rtl .media-frame select, .rtl .media-frame textarea, .rtl .media-modal {
          font-family: ' . $font . ', tahoma, sans-serif, arial !important;
          letter-spacing: 0;
        }
        ';
        $css .= '
          body.rtl, body.rtl .press-this a.wp-switch-editor {
            font-family: ' . $font . ', tahoma, sans-serif, arial !important;
            letter-spacing: 0;
          }
        ';
        $css .= '
          .rtl #wpadminbar * {
            font-family: ' . $font . ', tahoma, sans-serif, arial;
            letter-spacing: 0;
          }    
        ';
      }
    }


    wp_enqueue_style('wpsh-theme', WPSH_URL . 'assets/css/wpsh_theme.css', array(), WPSH_VERSION);

    wp_add_inline_style('wpsh-theme', (string)$css);
  }

  /**
   * Generate fonts
   *
   * Generate farsi fonts.
   *
   * @since 3.0.1
   *
   * @param string $name font name.
   * @return string Fonts CSS code.
   */
  protected function font($name)
  {
    $path = WPSH_URL . 'assets/fonts/';

    $css = '
    @font-face {
        font-family: ' . $name . ';
        src: url(' . $path . $name . '.woff2) format("woff2");
        font-weight: normal;
        font-style: normal;
    }
    @font-face {
        font-family: ' . $name . ';
        src: url(' . $path . $name . 'Bold.woff2) format("woff2");
        font-weight: bold;
        font-style: normal;
    }
    ';

    return (string) $css;
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

    wp_enqueue_style('wpsh-admin-css', WPSH_URL . 'assets/css/wpsh_admin.css', array(), WPSH_VERSION);

    $this->themes('wp-admin');
  }

  /**
   * Chane font of Tinymce
   *
   * Chane font of Tinymce and make it farsi friendly.
   *
   * @since 2.1.0
   *
   */
  public function tinymce_style($mce_css)
  {
    if (is_rtl()) {
      $font = $this->option('dashboard-font-default', false, 'IRANSansWeb');
      if (!empty($mce_css))
        $mce_css .= ',';
      if (is_admin()) {
        $mce_css .= WPSH_URL . 'assets/fonts/wpsh_editor-' . $font . '-rtl.css';
      } else {
        $mce_css .= WPSH_URL . 'assets/fonts/wpsh_editor-Vazir-rtl.css';
      }
    }

    return $mce_css;
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
    foreach ($txts as $txt) {

      /** Do Not allow question mark */
      if ($txt['translate-source'] == (string)'?') {
        return $string;
      }

      if (!isset($txt['translate-source']) || !isset($txt['translate-target'])) :
        return $string;
      endif;

      $string = preg_replace('/\b' . $txt['translate-source'] . '\b/u', $txt['translate-target'], $string);
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

    if (!empty($args['message'])) {
      $args['message'] = str_replace('http://wp-persian.com/', 'https://wpvar.com/', $args['message']);
      $args['message'] = str_replace('WP-Persian.com', 'wpvar.com', $args['message']);
      $args['message'] = str_replace('https://wordpress.org/support/forums/', 'https://wpvar.com/support/', $args['message']);
    }

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

    if (isset($format[1])) {
      $result = (($format[0] > 0) ? '+' . $format[0] : $format[0]) . ':' . (($format[1] == 5) ? '30' : $format[1]);
    } elseif (isset($format[0]) && !isset($format[1])) {
      if ($format[0] == 0) {
        return 0;
      }
      $result = ($format[0] > 0) ? '+' . $format[0] . ':00' : $format[0] . ':00';
    } else {
      $result = 0;
    }

    return $result;
  }

  /**
   * Disable shamsi regarding lang
   *
   * If language is not set to farsi admins can choose to disable shamsi dates.
   *
   * @since 2.1.0
   *
   * @return bool true or false.
   */
  protected function no_lang_no_shamsi()
  {
    if ($this->option('activate-no-lang-no-shamsi', true, false) && get_locale() != 'fa_IR' && get_locale() != 'fa_AF') {
      return true;
    }
    return false;
  }

  /**
   * Disable shamsi dates
   *
   * Disable shamsi dates if link had set so.
   *
   * @since 3.0.0
   *
   * @return bool true or false.
   */
  protected function can_continue()
  {

    if (!$this->pro()) {
      return true;
    }

    $links = $this->option('advanced-group', false, null);
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    if (!empty($links)) {

      foreach ($links as $link) {
        if ($link['advanced-link'] === $actual_link) {
          return false;
        }
      }
    }

    return true;
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

    if (!$this->can_continue()) {
      return $date;
    }

    if ($timestamp < 0) {
      return $date;
    }

    $date = $this->normalize_date($date);

    $go = apply_filters('wpsh_date_before', true);

    if ($go !== true) {
      return $date;
    }

    /* Hook to add modify formats*/
    $format = apply_filters('wpsh_date_replace_formats', $format);

    $skip_formats = array(
      // DATE_W3C DATE_ATOM DATE_RFC3339
      'Y-m-d\TH:i:sP',
      // DATE_RSS DATE_RFC822 DATE_RFC1036 DATE_RFC1123 DATE_RFC2822
      'D, d M Y H:i:s O',
      // DATE_COOKIE DATE_RFC850
      'l, d-M-Y H:i:s T',
      // DATE_ISO8601
      'Y-m-d\TH:i:sO',
      // DATE_ISO8601 Variant
      'Y-m-d\TH:i:s',
      // DATE_ISO8601 Variant
      'Y-m-d\TH:i',
      // DATE_W3C DATE_ATOM DATE_RFC3339 Variant
      'Y-m-dTH:i:sP'
    );

    /* Hook to add custom formats to be skipped */
    $skip_formats = apply_filters('wpsh_date_skip_formats', $skip_formats);

    if (is_array($skip_formats) && in_array($format, $skip_formats)) {
      return $date;
    }

    if (!$this->option('activate-shamsi', true, true) || $this->no_lang_no_shamsi()) {
      return $date;
    }

    if ($this->option('activate-admin-shamsi', true, false) && is_admin()) {
      return $date;
    }

    if ($format == null) {
      $format = 'Y m d H:i:s';
    }

    $format = ($format == 'F j, Y') ? 'j F, Y' : $format; // Make date readable without changing default format.
    $format = str_replace(',', '', $format);
    $format = str_replace('،', '', $format);
    $format = str_replace('.', '', $format);
    $format = str_replace('S', '', $format);
    $format = str_replace('js', 'j s', $format);
    $format = str_replace('M j', 'j M', $format);
    $format = str_replace('F j', 'j F', $format);

    if ($timezone == null || $timezone == '0') {
      $timezone = $this->timezone();
    }

    if ($this->timezone() == 0) {
      $timezone = 1;
    }

    $date = ($timezone == '1') ? new WPSH_Jalali($timestamp, 'UTC') : new WPSH_Jalali($timestamp, $timezone);
    $date = $date->format($format);

    /* Deprecated since 2.0.0 */
    //$date = $this->persian_num($date);
    /* Filter returned date to extend plugins developement capacity */
    return apply_filters('wpsh_date', $date, $format, $timestamp, $timezone);
  }

  /**
   * Correct diff
   *
   * Correct human readable dates
   *
   * @since 2.1.0
   *
   * @param string $since The difference in human readable text.
   * @param int    $diff  The difference in seconds.
   * @param int    $from  Unix timestamp from which the difference begins.
   * @param int    $to    Unix timestamp to end the time difference.
   * @return string Corrected human readable date.
   */
  public function wp_shamsi_diff($since, $diff, $from, $to)
  {
    if ($from < 0 && $to === time()) {
      $from = date('Y-m-d H:i:s', $from);
      $from = $this->gregorian($from, 'Y-m-d H:i:s');
      $to = time();

      return human_time_diff(strtotime($from), $to);
    } else {
      return $since;
    }
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
    if ($check_point >= 1970) {
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
    if ($check_point >= 1970) {
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

    if ($format == null) {
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

    if (!$this->option('persian-num', true, true)) {
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
   * Validate pro
   *
   * Pro version validation.
   *
   * @since 3.0.0
   *
   * @return bool true if is validated.
   */
  protected function pro($soft = false)
  {
    $serial = !empty(get_option('wpsh_pro_license')) ? get_option('wpsh_pro_license') : false;
    $status = !empty(get_option('wpsh_pro_license_status')) && get_option('wpsh_pro_license_status') == 1 ? true : false;
    $due = !empty(get_option('wpsh_pro_license_due')) && get_option('wpsh_pro_license_due') > current_time('timestamp', false) ? true : false;
    $shamsi = WP_PLUGIN_DIR . '/wp-shamsi-pro/wp-shamsi-pro.php';
    $exists = file_exists($shamsi) ? true : false;

    if ($soft) {
      if ($serial && $status && $due) {
        return true;
      } else {
        return false;
      }
    } else {
      if (!in_array('wp-shamsi-pro/wp-shamsi-pro.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        return false;
      }
      if ($serial && $status && $due && $exists) {
        return true;
      } else {
        return false;
      }
    }
  }

  /**
   * Validate VIP
   *
   * VIP version validation.
   *
   * @since 3.0.0
   *
   * @return bool true if is validated.
   */
  protected function vip()
  {
    if (!$this->pro()) {
      return false;
    }

    $vip = get_option('wpsh_pro_is_vip');

    if (!empty($vip) && $vip == 1) {
      return true;
    }

    return false;
  }

  /**
   * Validate due
   *
   * Pro version due date.
   *
   * @since 3.0.0
   *
   * @return mixed Return in dayas or bool.
   */
  protected function pro_due($int = false)
  {

    if (!$this->pro()) {
      return null;
    }

    $reminder = 30;

    $due = get_option('wpsh_pro_license_due');
    $days = 60 * 60 * 24;
    $now = current_time('timestamp', false);
    $remain = floor(($due - $now) / $days);

    if ($int) {
      return $remain;
    } else {
      if ($remain < $reminder) {
        return true;
      } else {
        return false;
      }
    }
  }

  /**
   * $_GET Mask
   *
   * Mask and escape $_GET
   *
   * @since 2.1.0
   *
   * @param string $key Key of $_GET.
   * @param string $mode String or boolean.
   * @return mixed Escaped result or boolean.
   */
  protected function get($key, $mode = 'string')
  {
    if ($mode == 'string') {
      $get = (isset($_GET[$key])) ? esc_attr($_GET[$key]) : null;
    }

    if ($mode == 'bool') {
      $get = (isset($_GET[$key])) ? true : false;
    }

    return $get;
  }

  /**
   * $_POST Mask
   *
   * Mask and escape $_POST
   *
   * @since 2.1.0
   *
   * @param string $key Key of $_POST.
   * @param string $mode String or boolean.
   * @return mixed Escaped result or boolean.
   */
  protected function post($key, $mode = 'string')
  {
    if ($mode == 'string') {
      $post = (isset($_POST[$key])) ? esc_attr($_POST[$key]) : null;
    }

    if ($mode == 'bool') {
      $post = (isset($_POST[$key])) ? true : false;
    }

    return $post;
  }
}
