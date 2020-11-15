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
        add_action('wp_dashboard_setup', array(
            $this,
            'add_dashboard'
        ));
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
        add_filter('the_post', array(
            $this,
            'display_post_date'
        ) , 99, 1);

        $base = basename($_SERVER['PHP_SELF']);
        if (($base == 'post.php' && isset($_GET['post']) && isset($_GET['action']) && esc_attr($_GET['action']) == 'edit') || $base == 'post-new.php')
        {
            add_filter('gettext', array(
                $this,
                'display_post_month'
            ) , 10, 1);
        }

        if (get_locale() != 'fa_IR' && get_locale() != 'fa_AF')
        {
            add_action('admin_notices', array(
                $this,
                'no_farsi'
            ));
        }

        $zone = wp_timezone_string();

        if ((get_locale() == 'fa_IR' || get_locale() == 'fa_AF') && $zone != 'Asia/Tehran' && $zone != 'Asia/Kabul' && $zone != '+03:30' && $zone != '+04:30')
        {
            add_action('admin_notices', array(
                $this,
                'no_valid_zone'
            ));
        }

        add_filter('plugin_action_links_' . WPSH_BASE , array(
            $this,
            'add_settings_link'
        ));

        add_action('admin_enqueue_scripts', array(
            $this,
            'admin_script'
        ) , 1);

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
            $maxitems = $rss->get_item_quantity(3);
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
        $html .=
        '<div id="farsi-shotcuts" class="farsi-shotcuts">
          <ul>
            <li><a href="https://wpvar.com" target="_blank" title="وردپرس فارسی"><span class="dashicons dashicons-wordpress"></span></a></li>
            <li><a href="https://wpvar.com/downloads/themes/" target="_blank" title="قالب های وردپرس"><span class="dashicons dashicons-admin-appearance"></a></span></li>
            <li><a href="https://wpvar.com/downloads/plugins/" target="_blank" title="افزونه های وردپرس"><span class="dashicons dashicons-admin-plugins"></a></span></li>
            <li><a href="https://wpvar.com/forums/" target="_blank" title="پشتیبانی وردپرس"><span class="dashicons dashicons-sos"></span></a></li>
            <li><a href="https://wpvar.com/edu/" target="_blank" title="آموزش وردپرس"><span class="dashicons dashicons-welcome-learn-more"></a></span></li>
          </ul>
        </div>';
        echo $html;
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
     * @since 2.0.0
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
     * Gutenberg post shamsi schedule
     *
     * Converts Gutenberg Gregorian schedule to shamsi one.
     *
     * @since 2.0.0
     *
     * @param object $post post Object.
     * @return object post Object.
     */
    public function display_post_date($post)
    {
        if (!$this->block_editor())
        {
            return $post;
        }

        $gregorian_stamp = strtotime($post->post_date, time());
        $gregorian_stamp_gmt = strtotime($post->post_date, time());

        $post->post_date = parent::wp_shamsi(null, 'Y-m-d H:i:s', $gregorian_stamp, 'UTC');
        //$post->post_date_gmt = $this->wp_shamsi(null, 'Y-m-d H:i:s', $gregorian_stamp_gmt, 0);
        return $post;
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
        if (function_exists('get_current_screen'))
        {
            $screen = get_current_screen();
            if (method_exists($screen, 'is_block_editor'))
            {
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
     * @since 2.0.0
     *
     * @param string $string Translation strings.
     * @return string Translated strings.
     */
    public function display_post_month($string)
    {

        $fa = array(
            'ژانویه',
            'فوریه',
            'مارس',
            'آوریل',
            'می',
            'ژوئن',
            'جولای',
            'آگوست',
            'سپتامبر',
            'اکتبر',
            'نوامبر',
            'دسامبر'
        );
        $true_fa = array(
            'فروردین',
            'اردیبهشت',
            'خرداد',
            'تیر',
            'مرداد',
            'شهریور',
            'مهر',
            'آبان',
            'آذر',
            'دی',
            'بهمن',
            'اسفند'
        );

        $string = str_replace($fa, $true_fa, $string);

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
?>
        <div class="notice notice-warning is-dismissible">
          <p><?php _e('<strong>هشدار:</strong> بسته زبانی فارسی وردپرس فعال نیست. برای فعال سازی آن <a href="' . get_admin_url() . 'options-general.php#default_role">از این صفحه</a> زبان سایت را به <strong>فارسی</strong> تغییر دهید', 'wpsh'); ?></p>
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
        if (get_locale() == 'fa_IR')
        {
            $city = 'تهران';
        }

        if (get_locale() == 'fa_AF')
        { // Future support if fa_AF becomes available
            $city = 'کابل';
        }

?>
        <div class="notice notice-warning is-dismissible">
          <p><?php _e('<strong>توجه:</strong> برای عملکرد دقیق تر شمسی ساز، زمان محلی را <a href="' . get_admin_url() . 'options-general.php#WPLANG">از این صفحه</a> به <strong>' . $city . '</strong> تغییر دهید', 'wpsh'); ?></p>
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
     * Load JS and CSS
     *
     * Loads JS and CSS in admin interface.
     *
     * @since 1.0.0
     *
     */
    public function admin_script()
    {

        if (!parent::option('activate-admin-shamsi', true, false))
        {
            wp_enqueue_script('wpsh-admin', WPSH_URL . 'assets/js/wpsh_admin.js', array(
                'jquery'
            ) , false, true);
        }

        if (parent::option('dashboard-font', true, true)):

            parent::themes('wp-admin'); // Since 1.2.0
            wp_enqueue_style('wpsh-admin-css', WPSH_URL . 'assets/css/wpsh_admin.css');
        endif;

        if (parent::option('persian-admin-num', true, true)):
            wp_enqueue_script('wpsh', WPSH_URL . 'assets/js/wpsh.js', array(
                'jquery'
            ));
        endif;

        if (parent::option('activate-shamsi', true, true) && !parent::option('activate-admin-shamsi', true, false)):
            $js = (string)'';
            if (wp_script_is('wp-i18n'))
            {
                $js .= '
            wp.i18n.setLocaleData({
              "October": [
                "دی"
              ],
              "November": [
                "بهمن"
              ],
              "December": [
                "اسفند"
              ],
              "January": [
                "فروردین"
              ],
              "February": [
                "اردیبهشت"
              ],
              "March": [
                "خرداد"
              ],
              "April": [
                "تیر"
              ],
              "May": [
                "مرداد"
              ],
              "June": [
                "شهریور"
              ],
              "July": [
                "مهر"
              ],
              "August": [
                "آبان"
              ],
              "September": [
                "آذر"
              ]
            });
            ';
            }
            wp_add_inline_script('wpsh-admin', $js);

        endif;

    }
}

