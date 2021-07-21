<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Admin class
 *
 * Class to handle dates in admin interface
 *
 * @since 2.0.0
 */
class WPSH_Admin extends WPSH_Core
{
    /**
     * Construction
     *
     * Construct WPSH_Admin class runs required hooks.
     *
     * @since 2.0.0
     *
     */
    function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard'));
        add_filter('dashboard_secondary_link', array($this, 'wpsh_dashboard_link'));
        add_filter('dashboard_secondary_feed', array($this, 'wpsh_dashboard_feed'));
        add_action('current_screen', array($this, 'farsi_support'));

        if (!function_exists('wp_date')) {
            add_filter('get_the_time', array($this, 'post_time'), 10, 3);
        }

        if (get_locale() != 'fa_IR' && get_locale() != 'fa_AF') {
            add_action('admin_notices', array($this, 'no_farsi'), 12);
        }

        if (function_exists('wp_timezone_string')) {
            $zone = wp_timezone_string();
            if ((get_locale() == 'fa_IR' || get_locale() == 'fa_AF') && $zone != 'Asia/Tehran' && $zone != 'Asia/Kabul' && $zone != '+03:30' && $zone != '+04:30') {
                add_action('admin_notices', array($this, 'no_valid_zone'), 12);
            }
        }

        add_filter('plugin_action_links_' . WPSH_BASE, array($this, 'add_settings_link'));
        add_filter('plugin_row_meta', array($this, 'add_settings_meta'), 10, 4);
        add_action('admin_enqueue_scripts', array($this, 'admin_script'), 1);
        add_action('admin_head', array($this, 'remove_notices'), 10);
    }

    /**
     * No admin_notices
     *
     * Do not display notices inside plugins settings.
     *
     * @since 2.1.2
     *
     */
    public function remove_notices()
    {
        $page = isset($_GET['page']) ? esc_attr($_GET['page']) : false;

        if ($page !== false && $page === 'wpsh') {
            remove_all_actions('admin_notices');
        }
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
        add_meta_box('rss_dashboard', 'وردپرس فارسی', array($this, 'dashboard'), 'dashboard', 'normal', 'high');
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
        $transient = get_transient('wpsh_dashboard_site_feed_' . WPSH_VERSION);

        if (!WP_DEBUG && $transient) {
            echo $transient;
            return;
        }

        include_once(ABSPATH . WPINC . '/feed.php');

        $rss = fetch_feed('https://wpvar.com/feed');
        $maxitems = (int)0;

        if (!is_wp_error($rss)) :
            $maxitems = $rss->get_item_quantity(3);
            $rss_items = $rss->get_items(0, $maxitems);
            $rss_title = '<a href="' . $rss->get_permalink() . '" target="_blank">' . strtoupper($rss->get_title()) . '</a>';
        endif;

        $html = '<div><a href="https://wpvar.com/courses/?wpsh=1" class="wpsh-ribbon" target="_blank">دوره‌های آموزشی<span class="dashicons dashicons-external"></span></a></div>';

        $html .= '<div class="rss-widget">';
        if (parent::pro_due() && current_user_can('manage_options')) {
            $html .= '<div class="wpsh-pro-due-widget">';
            $html .= '<p>اعتبار لایسنس نسخه حرفه‌ای تاریخ شمسی و فارسی ساز وردپرس رو به <strong>اتمام</strong> است. برای تمدید لایسنس لطفا <strong><a target="_blank" href="https://wpvar.com/pro/?renew=1">اینجا کلیک کنید</a></strong>.</p>';
            $html .= '</div>';
        }
        $html .= '<ul>';

        if ($maxitems == 0) {
            $html .= '<li>یافت نشد</li>';
        } else {
            foreach ($rss_items as $item) :
                $item_date = human_time_diff($item->get_date('U'), current_time('timestamp')) . ' پیش';
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
        $html .= '<div id="farsi-shortcuts" class="farsi-shortcuts">
          <ul>
            <li><a href="https://wpvar.com" target="_blank" title="وردپرس فارسی"><span class="dashicons dashicons-wordpress"></span></a></li>
            <li><a href="https://wpvar.com/downloads/themes/" target="_blank" title="قالب های وردپرس"><span class="dashicons dashicons-admin-appearance"></a></span></li>
            <li><a href="https://wpvar.com/downloads/plugins/" target="_blank" title="افزونه های وردپرس"><span class="dashicons dashicons-admin-plugins"></a></span></li>
            <li><a href="https://wpvar.com/forums/" target="_blank" title="پشتیبانی وردپرس"><span class="dashicons dashicons-sos"></span></a></li>
            <li><a href="https://wpvar.com/courses/?wpshc=1" target="_blank" title="دوره‌های آموزشی"><span class="dashicons dashicons-welcome-learn-more"></a></span></li>
          </ul>
        </div>';
        if (!parent::pro() && current_user_can('manage_options')) {
            $html .= '<a target="_blank" href="https://wpvar.com/pro/" class="wpsh-pro-widget"><div>';
            $html .= '<p><strong>ارتقا به نسخه حرفه‌ای</strong></p>';
            $html .= '</div></a>';
        }

        echo $html;
        set_transient('wpsh_dashboard_site_feed_' . WPSH_VERSION, $html, 12 * HOUR_IN_SECONDS);
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
     * @since 2.0.0
     *
     */
    public function farsi_support()
    {
        $screen = get_current_screen();
        $valid = (parent::get('page', 'bool')) ? true : false;

        if (!$valid) {
            $screen->add_help_tab(array(
                'id' => 'farsi-support',
                'title' => 'پشتیبانی فارسی',
                'content' => '<p>همیشه می توانید از طریق <strong><a href="https://wpvar.com/forums/" target="_blank">این لینک</a></strong> سوالات خود را در <strong><a href="https://wpvar.com/forums/" target="_blank">انجمن فارسی</a></strong> وردپرس بپرسید. همچنین می توانید <strong><a href="https://wpvar.com/courses/?wpshc=1" target="_blank">دوه‌های آموزشی</a></strong> را که حاوی آموزش‌های ویدیوی و تصویری است، مطالعه کنید.</p>
                              <p><a href="https://wpvar.com" class="button button-primary">وردپرس فارسی</a> <a href="https://wpvar.com/forums/" class="button">انجمن پشتیبانی</a> <a href="https://wpvar.com/courses/?wpshc=1" class="button">دوره‌های آموزشی</a></p>',
                'priority' => 11,
            ));
        }
    }

    /**
     * Gutenberg post shamsi schedule
     *
     * Converts Gutenberg Gregorian schedule to shamsi one.
     *
     * @deprecated 3.0.0
     *
     * @param object $post post Object.
     * @return object post Object.
     */
    public function display_post_date($post)
    {
        if (!$this->block_editor()) {
            return $post;
        }

        $gregorian_stamp = strtotime($post->post_date, time());
        //$gregorian_stamp_gmt = strtotime($post->post_date, time());
        $post->post_date = parent::wp_shamsi(null, 'Y-m-d H:i:s', $gregorian_stamp, 'UTC');
        //$post->post_date_gmt = $this->wp_shamsi(null, 'Y-m-d H:i:s', $gregorian_stamp_gmt, 0);
        return $post;
    }

    /**
     * Modify local time
     *
     * In older versions of WordPress this function will correct UTC time
     *
     * @since 2.0.1
     *
     * @param string $the_time Input time string.
     * @param string $format Format of date.
     * @param mixed $post Post ID or Object.
     * @return string Modified and correct date regarding to timezone.
     */
    public function post_time($the_time, $format, $post)
    {
        $post = get_post($post);

        $gregorian_stamp = strtotime($post->post_date, time());
        $date = parent::wp_shamsi(null, $format, $gregorian_stamp, 'UTC');

        return $date;
    }

    /**
     * Check if block editor is active
     *
     * Returns true if block editor is currently has been loaded.
     *
     * @since 2.0.0
     *
     * @return bool true or false.
     */
    public function block_editor()
    {
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if (method_exists($screen, 'is_block_editor')) {
                return ($screen->is_block_editor() == 1) ? true : false;
            }
        }
        return false;
    }

    /**
     * Gutenberg post farsi month
     *
     * Filter to translate english month to farsi in gutenberg editor
     *
     * @deprecated 3.0.0
     *
     * @param string $string Translation strings.
     * @return string Translated strings.
     */
    public function display_post_month($string)
    {

        $month = parent::get_month();

        $fa = array(
            '/\bژانویه\b/u',
            '/\bفوریه\b/u',
            '/\bمارس\b/u',
            '/\bآوریل\b/u',
            '/\bمی\b/u',
            '/\bژوئن\b/u',
            '/\bجولای\b/u',
            '/\bآگوست\b/u',
            '/\سپتامبر\b/u',
            '/\bاکتبر\b/u',
            '/\bنوامبر\b/u',
            '/\bدسامبر\b/u'
        );

        $true_fa = array(
            $month[1],
            $month[2],
            $month[3],
            $month[4],
            $month[5],
            $month[6],
            $month[7],
            $month[8],
            $month[9],
            $month[10],
            $month[11],
            $month[12]

        );

        $string = preg_replace($fa, $true_fa, $string);

        $fa_retro = array(
            'مرداد ',
            'مرداد‌',
            'اسد ',
            'اسد‌‌'
        );

        $true_fa_retro = array(
            'می ',
            'می‌',
            'می ',
            'می‌'
        );

        $string = str_replace($fa_retro, $true_fa_retro, $string);

        return $string;
    }

    /**
     * Farsi not installed
     *
     * Triggers warning that Farsi language pack is not installed in wordpress.
     *
     * @since 1.2.0
     *
     */
    public function no_farsi()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $user_id = get_current_user_id();
        $link = get_admin_url() . 'index.php?wpsh_lang_notice=dismiss';

        if (parent::get('wpsh_lang_notice', 'bool') && parent::get('wpsh_lang_notice') == 'dismiss') {
            update_user_meta($user_id, 'wpsh_lang_notice', 1);
        }

        if (get_user_meta($user_id, 'wpsh_lang_notice', true) == 1) {
            return;
        } ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php _e('<strong>هشدار:</strong> بسته زبانی فارسی وردپرس فعال نیست. برای فعال سازی آن <a href="' . get_admin_url() . 'options-general.php#default_role">از این صفحه</a> زبان سایت را به <strong>فارسی</strong> تغییر دهید', 'wpsh'); ?>
            </p>
            <a href="<?php echo $link ?>" class="button wpsh_dismiss"><?php _e('دیگر نشان نده', 'wpsh') ?></a>
        </div>
    <?php
    }

    /**
     * Timezone is not set to farsi countries
     *
     * Triggers warning When timezone is not set to Tehran or Kabul
     *
     * @since 2.0.0
     *
     */
    public function no_valid_zone()
    {

        if (!current_user_can('manage_options')) {
            return;
        }

        $user_id = get_current_user_id();
        $link = get_admin_url() . 'index.php?wpsh_timezone_notice=dismiss';

        if (parent::get('wpsh_timezone_notice', 'bool') && parent::get('wpsh_timezone_notice') == 'dismiss') {
            update_user_meta($user_id, 'wpsh_timezone_notice', 1);
        }

        if (get_user_meta($user_id, 'wpsh_timezone_notice', true) == 1) {
            return;
        }

        if (get_locale() == 'fa_IR') {
            $city = 'تهران';
        }

        if (get_locale() == 'fa_AF') { // Future support if fa_AF becomes available
            $city = 'کابل';
        } ?>

        <div class="notice notice-warning is-dismissible">
            <p>
                <?php _e('<strong>توجه:</strong> برای عملکرد دقیق تر شمسی ساز، زمان محلی را <a href="' . get_admin_url() . 'options-general.php#WPLANG">از این صفحه</a> به <strong>' . $city . '</strong> تغییر دهید', 'wpsh'); ?>
            </p>
            <a href="<?php echo $link ?>" class="button wpsh_dismiss"><?php _e('دیگر نشان نده', 'wpsh') ?></a>
        </div>
<?php
    }

    /**
     * Settings shortcut
     *
     * Adds settings page shortcut link to plugins page.
     *
     * @since 1.2.0
     *
     * @param array $links array of existing links.
     * @return array Link to settings page.
     */
    public function add_settings_link($links)
    {

        $pro = '<a href="https://wpvar.com/pro/" target="_blank" class="wpsh-color wpsh-bold wpsh-star">' . __('نسخه حرفه‌ای', 'wpsh') . '</a>';
        $settings_link = '<a href="' . get_admin_url() . 'admin.php?page=wpsh">' . __('تنظیمات', 'wpsh') . '</a>';
        $forum_link = '<a href="https://wpvar.com/forums/" target="_blank">' . __('انجمن پشتیبانی', 'wpsh') . '</a>';


        if (!parent::pro()) {
            array_push($links, $pro, $settings_link, $forum_link);
        } else {
            array_push($links, $settings_link, $forum_link);
        }

        return $links;
    }

    /**
     * Settings meta
     *
     * Adds meta to plugins setting row.
     *
     * @since 3.0.0
     *
     * @param array $plugin_meta An array of the plugin's metadata
     * @param string $plugin_file Path to the plugin file
     * @param array $plugin_data An array of plugin data
     * @param string $status Status of the plugin
     * @return array Modified meta.
     */
    public function add_settings_meta($plugin_meta, $plugin_file, $plugin_data, $status)
    {
        if ($plugin_file == WPSH_BASE) {
            $plugin_meta[] = '<a href="https://wpvar.com/" target="_blank">' . __('وردپرس فارسی', 'wpsh') . '</a>';
            $plugin_meta[] = '<a href="https://wpvar.com/courses/?wpshc=1" class="wpvar-courses" target="_blank">' . __('دوره‌های آموزشی', 'wpsh') . '</a>';
        }

        return $plugin_meta;
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

        wp_enqueue_script('wpsh-admin-core', WPSH_URL . 'assets/js/wpsh_admin.js', array(
            'jquery'
        ), WPSH_VERSION, true);

        wp_enqueue_style('wpsh-admin-css-core', WPSH_URL . 'assets/css/wpsh_admin.css', array(), WPSH_VERSION);

        if (parent::option('activate-shamsi', true, true) && !parent::option('activate-admin-shamsi', true, false) && !parent::no_lang_no_shamsi()) {
            wp_enqueue_script('wpsh-admin', WPSH_URL . 'assets/js/wpsh_admin_shamsi.js', array(
                'jquery'
            ), WPSH_VERSION, true);
            wp_localize_script('wpsh-admin', 'listFarsiMonth', parent::get_month());

            $wpshSignature = array(
                'signature' =>  md5('%wpsh%')
            );
            wp_localize_script('wpsh-gjc', 'wpshSignature', $wpshSignature);
        }
        if (parent::option('dashboard-font-default', false, 'IRANSansWeb') != 'none') :
            parent::themes('wp-admin'); // Since 1.2.0
            wp_enqueue_style('wpsh-admin-css', WPSH_URL . 'assets/css/wpsh_admin_shamsi.css', array(), WPSH_VERSION);
        endif;

        if (parent::option('persian-admin-num', true, true) && (get_locale() == 'fa_IR' || get_locale() == 'fa_AF')) :
            wp_enqueue_script('wpsh', WPSH_URL . 'assets/js/wpsh.js', array(
                'jquery'
            ), WPSH_VERSION, true);

            $base = basename($_SERVER['PHP_SELF']);
            $isShamsiInAdmin = array(
                'in_admin' => (is_admin()) ? 1 : 0,
                'base' => $base
            );

            wp_localize_script('wpsh', 'isShamsiInAdmin', $isShamsiInAdmin);

        endif;
    }
}
