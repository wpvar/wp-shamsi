<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Settings class
 *
 * WP shamsi`s settings page
 *
 * @since 1.2.0
 */
class WPSH_Options extends WPSH_Core
{
    private $plugin_name;
    /**
     * Construction
     *
     * Construct WPSH_Options class.
     *
     * @since 1.2.0
     *
     * @param string $plugin_name Slug to run library in order to create settings page.
     */
    function __construct($plugin_name)
    {
        add_action('init', array($this, 'register'));

        if (!empty($_GET['wpsh_deactivate'])) {
            add_action('init', array($this, 'deactivate'));
        }

        $this->plugin_name = $plugin_name;
    }

    /**
     * Supported themes list
     *
     * Display themes which WP shamsi supported and makes them farsi compatible.
     *
     * @since 1.2.0
     *
     * @return string HTML list of supported themes.
     */
    public function theme_list()
    {
        $themes = parent::supported_themes();

        $result = '<br /><ul>';
        foreach ($themes as $key => $value) {
            $result .= '<li><a href="https://wpvar.com/downloads/' . $value . '/" target="_blank">' . ucfirst($value) . '</a></li>';
        }
        $result .= '</ul>';

        return $result;
    }

    /**
     * Detect conflicted plugins
     *
     * Detects conflicted plugins to generate notification.
     *
     * @since 3.2.0
     *
     * @return mixed Name of plugin or false on no conflict.
     */
    public function conflicts()
    {
        $check = (is_admin() && !empty($_GET['page']) && $_GET['page'] == 'wpsh') ? true : false;

        if ($check == false) {
            return false;
        }

        $conflicts = array(
            'wp-jalali/wp-jalali.php',
            'wp-parsidate/wp-parsidate.php',
            'persian-woocommerce/woocommerce-persian.php',
            'persian-elementor/persian-elementor.php',
            'wp-persian/wp-persian.php',
            'wp-farsi/wp-farsi.php',
            'persian-date/persian-date.php',
            'font-farsi/font-farsi.php',
            'wp-administration-style/wp-administration-style.php'
        );

        $confilct = false;
        foreach ($conflicts as $conflict) {
            if (in_array($conflict, apply_filters('active_plugins', get_option('active_plugins')))) {
                $conflict = explode('/', $conflict);
                if($conflict[0] == 'persian-woocommerce') {
                    $pwoo = get_option('PW_Options');
                    if(!empty($pwoo)) {
                        if($pwoo['enable_jalali_datepicker'] == 'yes') {
                            return('pwoo');
                        } else {
                            continue;
                        }
                    }
                }
                return $conflict[0];
            }
        }

        return $confilct;
    }

    /**
     * Deactivate plugins
     *
     * Detects conflicted plugins.
     *
     * @since 3.2.0
     */
    public function deactivate()
    {
        $plugin = (!empty($_GET['wpsh_deactivate']) ? esc_attr($_GET['wpsh_deactivate']) : false);

        if ($plugin != false) {
            deactivate_plugins($plugin . '.php');
        }

        wp_safe_redirect(get_admin_url() . 'admin.php?page=wpsh');
        exit;
    }

    /**
     * Generate download links
     *
     * Generate download links targeting to plugin pages.
     *
     * @since 2.1.2
     *
     * @param string $slug Slug in makhzan.
     * @param string $title Title of link.
     * @return string HTML generated download links.
     */
    public function makhzan_download($slug, $title = 'دانلود')
    {

        $url = 'https://wpvar.com/downloads/' . $slug . '/';
        $html = '<a href="' . $url . '" class="button button-primary" id="wpsh_downloads" target="_blank">' . $title . '</a>';

        return $html;
    }
    /**
     * Register settings menu
     *
     * Registers settings menu and created settings page with all fields and settings
     *
     * @since 1.2.0
     *
     */
    public function register()
    {

        $config_submenu = array(
            'type' => 'menu',
            'id' => $this->plugin_name,
            'menu_title' => __('وردپرس فارسی', 'wpsh'),
            'parent' => '',
            'submenu' => false,
            'title' => __('تاریخ شمسی و فارسی‌ساز وردپرس', 'wpsh'),
            'capability' => 'manage_options',
            'multilang' => false,
            'icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><defs><style>.toplevel_page_wpsh .cls-1{fill:#037cbb;}.toplevel_page_wpsh .cls-2{fill:#fff;}</style></defs><title>وردپرس فارسی</title><g id="Layer_4" data-name="Layer 4"><path fill="#a0a5aa" class="cls-1" d="M127.85,245.74h-.65c-2,0-4-.11-6.15-.22l-1.68-.08c-18.62-.9-34.92-7.63-43-11.56-1.32-.64-2.65-1.32-4.3-2.19a117.82,117.82,0,1,1,56,14.09Z"/><path class="cls-2" d="M128,18.22a109.78,109.78,0,0,1,.78,219.56l-.25,0H127.2c-1.78,0-3.7-.1-5.73-.21l-1.72-.08c-17.18-.83-32.34-7.1-39.87-10.77-1.22-.59-2.45-1.22-4-2A109.79,109.79,0,0,1,128,18.22m0-16A125.79,125.79,0,0,0,68.35,238.76c1.51.8,3,1.56,4.54,2.31,6.2,3,24.43,11.31,46.09,12.36,2.71.13,5.46.31,8.22.31a5.36,5.36,0,0,0,.8,0A125.78,125.78,0,0,0,128,2.22Z"/><path class="cls-2" d="M185.16,142.4a125.12,125.12,0,0,0-4.27-22.62,105.65,105.65,0,0,0-8.09-20.4,78.63,78.63,0,0,0-11.6-16.76Q145.8,66,127.42,66a33.1,33.1,0,0,0-16,4.18,48.49,48.49,0,0,0-14.31,12.4,71.11,71.11,0,0,0-9.42,16.62,70.89,70.89,0,0,0-5.11,17.2,87.3,87.3,0,0,0-1.12,12.49,58.06,58.06,0,0,0,.89,10.36,36.83,36.83,0,0,0,7.51,17.38q13.47,16.67,41.56,18.35c4.76.45,9,.67,12.76.67h14.71a49.68,49.68,0,0,1-8,19.74,80.33,80.33,0,0,1-17.82,19,128.28,128.28,0,0,1-27.07,16,164.06,164.06,0,0,1-33.15,10.62A125.11,125.11,0,0,0,119,253.43c2.66-1.25,5.29-2.58,7.86-4a129.84,129.84,0,0,0,21.61-14.94,110.71,110.71,0,0,0,17.69-19.06,103,103,0,0,0,12.53-22.85A97,97,0,0,0,185,166a117.2,117.2,0,0,0,.66-12.4C185.69,149.82,185.51,146.13,185.16,142.4ZM150.8,150c-2.75.05-4.58.05-5.51.05-3.82,0-7.87-.14-12.18-.4a51.41,51.41,0,0,1-5.51-.58,39.92,39.92,0,0,1-6.44-1.42,29.25,29.25,0,0,1-6.23-2.58,14.93,14.93,0,0,1-4.84-4.22,11.77,11.77,0,0,1-2.67-6.14,43.54,43.54,0,0,1-.44-5.78,53.67,53.67,0,0,1,.35-5.82A58.79,58.79,0,0,1,109.82,112a50.09,50.09,0,0,1,4.45-10.13,29.3,29.3,0,0,1,6-7.47,10.55,10.55,0,0,1,7.11-2.89,12.12,12.12,0,0,1,4.4.76,22,22,0,0,1,4,2A30.1,30.1,0,0,1,139.25,97c1.11,1,2.08,2,2.93,2.8a59.3,59.3,0,0,1,6.27,8.85A77.25,77.25,0,0,1,154,120.13a88.4,88.4,0,0,1,4,13.74,95.77,95.77,0,0,1,1.95,15.64C156.53,149.78,153.51,150,150.8,150Z"/></g></svg>')
        );

        $newsletter_email = get_user_meta(get_current_user_id(), 'wpsh_newsletter_email', true);

        if (!empty($newsletter_email) && $newsletter_email != null) {
            $newsletter = ' با ایمیل <strong>' . $newsletter_email . '</strong> مشترک خبرنامه هستید. از عضویتتان متشکریم.';
        } elseif ((parent::get('wpsh_newsletter', 'bool') && parent::get('wpsh_newsletter') == 'send') || parent::post('wpsh_email')) {
            $newsletter = '<strong>اشتراک شما با موفقیت ثبت شد. با تشکر</strong>';
        } else {
            $newsletter = '
          <label for="email">ایمیل: </label>
          <input type="email" id="wpsh_email_settings" class="wpsh_email_settings" name="wpsh_email_settings">
          <a class="button button-primary" id="wpsh_form_settings">ثبت اشتراک</a>
          <p>
          برای باخبر شدن از آخرین اخبار، به‌روزرسانی ‎ها و آموزش ‎های وردپرس به زبان فارسی با وارد کردن ایمیل خود در فیلد زیر مشترک خبرنامه شوید.
          </p>
          <p id="wpsh_email_validation_settings">
          </p>
          ';
        }

        $conflicts = $this->conflicts();

        if($conflicts == 'pwoo') {
            $conflict_text = __('<b>هشدار مهم:</b> تاریخ شمسی افزونه <b>ووکامرس فارسی</b>  فعال می‌باشد، فعال بودن شمسی‌ساز این افزونه می‌تواند باعث تداخل با وردپرس فارسی و بروز مشکل در وب‌سایت شما شود. برای شمسی‌سازی کامل وردپرس و ووکامرس و جلوگیری از تداخل، گزینه "تاریخ شمسی" افزونه ووکامرس فارسی را غیرفعال کنید ویا برای غیرفعال کردن ووکامرس فارسی <b><a href="' . get_admin_url() . 'admin.php?page=wpsh&wpsh_deactivate=persian-woocommerce/woocommerce-persian">اینجا کلیک کنید</a></b>.', 'wpsh');
        } else {
            $conflict_text = __('<b>هشدار مهم:</b> در حال حاضر افزونه‌ای با نام <b>' . $conflicts . '</b>  فعال می‌باشد، فعال بودن این افزونه می‌تواند باعث تداخل با وردپرس فارسی و بروز مشکل در وب‌سایت شما شود. برای غیرفعال کردن ' . $conflicts . ' <b><a href="' . get_admin_url() . 'admin.php?page=wpsh&wpsh_deactivate=' . $conflicts . '/' . $conflicts . '">اینجا کلیک کنید</a></b>.', 'wpsh');
        }

        if ($conflicts != false) {
            $list_conflicts = array(
                array(
                    'type' => 'notice',
                    'class' => 'danger',
                    'content' => $conflict_text,
                )
            );
        } else {
            $list_conflicts = array();
        }

        $pro = !parent::pro() ? '<strong class="wpsh-pro-intro"><a target="_blank" href="https://wpvar.com/pro/">ارتقا به نسخه حرفه‌ای</a></strong>' : (!parent::vip() ? '<strong class="wpsh-pro-intro">نسخه حرفه‌ای</strong>' : '<strong class="wpsh-pro-intro">نسخه VIP</strong>');
        $version = WPSH_VERSION . $pro;
        $license_pro = array();
        $license_pro = apply_filters('wpsh_pro_license', $license_pro);

        $general = array(
            array(
                'type' => 'content',
                'wrap_class' => 'no-border-bottom',
                'title' => __('نسخه افزونه', 'wpsh'),
                'content' => __('برای عملکرد بهتر افزونه، همیشه آن را به آخرین نسخه موجود به‌روزرسانی‌‌ کنید. <a href="' . get_admin_url() . 'plugins.php" target="_blank">فعال‌سازی به‌روزرسانی‌‌ خودکار</a>.', 'wpsh'),
                'before' => '<strong>' . $version . '</strong>',
            ),
            array(
                'type' => 'content',
                'wrap_class' => 'no-border-bottom',
                'title' => __('وردپرس', 'wpsh'),
                'content' => __('این افزونه توسط <a href="https://wpvar.com" target="_blank">wpvar.com</a> برنامه‌نویسی شده است. <br />اگر مشکلی در راه اندازی و استفاده از وردپرس ویا این افزونه دارید، برای دریافت پشتیبانی رایگان می‌توانید به <strong><a href="https://wpvar.com/forums/" target="_blank">انجمن پشتیبانی وردپرس فارسی</a></strong> مراجعه کنید.', 'wpsh'),
                'before' => '<img class="wpsh-intro_logo" src=' . WPSH_URL . 'assets/img/logo.svg /><strong>وردپرس فارسی</strong>',
            ),
            array(
                'type' => 'notice',
                'class' => 'primary',
                'content' => __('برای <strong>حمایت و کمک به توسعه</strong> این افزونه لطفا گزینه‌های زیر را تکمیل و فعال‌سازی کنید.', 'wpsh'),
            ),
            array(
                'type' => 'content',
                'wrap_class' => 'no-border-bottom',
                'title' => __('خبرنامه', 'wpsh'),
                'content' => '
                <div class="wpsh_newsletter_settings">
                      ' . $newsletter . '
                      <input type="hidden" id="wpsh_newsletter_settings" name="wpsh_newsletter_settings" value="' . get_admin_url() . 'admin.php?page=wpsh&wpsh_newsletter=send">
                </div>
                ',
                'before' => '<span class="dashicons dashicons-email"></span> <strong>خبرنامه وردپرس فارسی</strong>',
            ),
            array(
                'id' => 'activate-stats',
                'type' => 'switcher',
                'wrap_class' => 'no-border-bottom',
                'title' => __('ارسال آمار', 'wpsh'),
                'default' => 'no',
                'after' => '<p>با فعال کردن این گزینه اطلاعات وردپرس شما در دسترس ما قرار می‌گیرد. با ارسال این اطلاعات و داده‌ها به ما کمک فراوانی می‌کنید تا افزونه را بهتر و دقیق‌تر توسعه بدهیم تا بیشترین سازگاری را با خواسته‌های جامعه وردپرس فارسی داشته باشد. برای دریافت جزئیات و مطالعه حریم‌ خصوصی به قسمت "درباره و قوانین" در تنظیمات افزونه مراجعه کنید. این گزینه به صورت پیشفرض غیرفعال می‌باشد و فقط مدیران این سایت می‌توانند آن را فعال کنند.</p>',
            ),
            array(
                'type' => 'content',
                'wrap_class' => 'no-border-bottom',
                'content' => '
                <!-- wpvar.net banner -->
                <a href="https://wpvar.net/?wpsh=1" target="_blank">
                    <div class="wpvarnet-banner">
                        <img src="' . WPSH_URL . 'assets/img/wpvarnet.svg" alt="هاست وردپرس فارسی" loading="lazy"/>
                    </div>
                </a>
                ',
            ),
        );

        $fields[] = array(
            'name' => 'general',
            'title' => __('عمومی', 'wpsh'),
            'icon' => 'dashicons-dashboard',
            'fields' => array_merge($list_conflicts, $license_pro, $general)
        );

        $fields[] = array(
            'name' => 'shamsi',
            'title' => __('شمسی‌ساز', 'wpsh'),
            'icon' => 'dashicons-calendar-alt',
            'fields' => array(
                array(
                    'type' => 'notice',
                    'class' => 'primary',
                    'content' => __('درصورت غیرفعال بودن گزینه زیر، شمسی‌سازی غیرفعال شده ولی بقیه امکانات افزونه همچنان اجرا خواهند شد.', 'wpsh'),
                ),
                array(
                    'id' => 'activate-shamsi',
                    'type' => 'switcher',
                    'title' => __('شمسی‌سازی', 'wpsh'),
                    'description' => __('درصورت غیرفعال بودن، تاریخ‌ها شمسی‌سازی نخواهند شد.', 'wpsh'),
                    'default' => 'yes',
                ),
                array(
                    'id'             => 'country-select',
                    'type'           => 'select',
                    'title'          => 'کشور',
                    'description' => __('نام ماه‌های نمایش داده شده در تاریخ‌ها براساس کشور انتخاب شده تغییر خواهند کرد.', 'wpsh'),
                    'options'        => array(
                        'ir'         => 'ایران',
                        'af'         => 'افغانستان'
                    ),
                    'default'     => 'ir',
                ),
                array(
                    'id' => 'activate-shamsi-archive',
                    'type' => 'switcher',
                    'title' => __('شمسی‌سازی بایگانی', 'wpsh'),
                    'description' => __('درصورت غیرفعال بودن، بایگانی یا همان آرشیو وردپرس براساس تاریخ میلادی نمایش داده خواهند شد.', 'wpsh'),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'activate-shamsi-calendar',
                    'type' => 'switcher',
                    'title' => __('شمسی‌سازی تقویم', 'wpsh'),
                    'description' => __('درصورت فعال بودن، ابزارک و بلوک تقویم شمسی‌سازی خواهند شد.', 'wpsh'),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'admin-bar-date',
                    'type' => 'switcher',
                    'title' => __('نوار مدیریت', 'wpsh'),
                    'description' => __('درصورت فعال بودن، تاریخ و ساعت در نوار مدیریت نمایش داده خواهند شد.', 'wpsh'),
                    'default' => 'yes',
                ),
                array(
                    'type' => 'content',
                    'wrap_class' => 'no-border-bottom',
                    'title' => __('زمان محلی و ساختار', 'wpsh'),
                    'content' => __('برای تنظیم زمان محلی و ساختار تاریخ و زمان وردپرس <a href="' . get_admin_url() . 'options-general.php">اینجا کلیک کنید</a>.', 'wpsh'),
                ),
            ),
        );

        if (parent::pro()) {
            $fonts = array(
                'IRANSansWeb'  =>  'ایران سنس',
                'IRANSansDn'    =>  'ایران سنس دست‌نویس',
                'IRANYekanWeb' =>  'ایران یکان',
                'Vazir' =>   'وزیر',
                'none'  =>  'غیرفعال'
            );
        } else {
            $fonts = array(
                'IRANSansWeb'  =>  'ایران سنس',
                'Vazir' =>   'وزیر',
                'none'  =>  'غیرفعال'
            );
        }

        $fields[] = array(
            'name' => 'farsi',
            'title' => __('فارسی‌ساز', 'wpsh'),
            'icon' => 'dashicons-translation',
            'fields' => array(
                array(
                    'id' => 'persian-num',
                    'type' => 'switcher',
                    'title' => __('اعداد فارسی', 'wpsh'),
                    'description' => __('فعال‌سازی تبدیل اعداد انگلیسی به فارسی در محیط کاربری', 'wpsh'),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'persian-admin-num',
                    'type' => 'switcher',
                    'title' => __('اعداد فارسی مدیریت', 'wpsh'),
                    'description' => __('فعال‌سازی تبدیل اعداد انگلیسی به فارسی در محیط مدیریت وردپرس', 'wpsh'),
                    'default' => 'yes',
                ),
                array(
                    'type' => 'notice',
                    'class' => 'primary',
                    'content' => __('در <a href="https://wpvar.com/pro/" target="_blank" style="text-decoration: none;"><strong>نسخه حرفه‌ای و ‌‌VIP</strong></a> می‌توانید فونت "ایران یکان" و "ایران سنس دست‌نویس" را نیز انتخاب کنید.', 'wpsh'),
                ),
                array(
                    'id'    => 'dashboard-font-default',
                    'type'  => 'select',
                    'title' => __('فونت مدیریت', 'wpsh'),
                    'wrap_class' => 'no-border-bottom',
                    'description' => __('این فونت در محیط مدیریت وردپرس بارگذاری خواهد شد.', 'wpsh'),
                    'options'   => $fonts,
                    'default'     => 'IRANSansWeb',
                )
            ),
        );
        $fields[] = array(
            'name' => 'theme',
            'title' => __('پوسته', 'wpsh'),
            'icon' => 'dashicons-admin-appearance',
            'fields' => array(
                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => __('<p>تمامی پوسته‌های پیشفرض وردپرس و پوسته‌های محبوب وردپرس توسط افزونه فارسی‌سازی می‌شوند. لیست پوسته‌های پشتیبانی شده در ادامه درج شده است. پوسته‌هایی که قبلا توسط شما یا افراد دیگری فارسی‌سازی شده باشند نیاز به فعال‌سازی این گزینه ندارند. توجه داشته باشید که فقط پوسته‌های درج شده فارسی‌سازی می‌شوند. برای دانلود آنها می‌توانید روی نام پوسته کلیک کنید.</p>', 'wpsh'),
                ),
                array(
                    'type' => 'content',
                    'wrap_class' => 'no-border-bottom',
                    'title' => __('پوسته‌های پشیبانی شده', 'wpsh'),
                    'content' => $this->theme_list(),
                    'before' => __('لیست پوسته‌های فارسی‌سازی شده توسط افرونه. برای دانلود روی نام هریک کلیک کنید. این لیست در نسخه‌های آتی گسترش خواهد یافت.', 'wpsh'),
                ),
                array(
                    'id' => 'fa-theme',
                    'type' => 'switcher',
                    'title' => __('فعال‌سازی', 'wpsh'),
                    'description' => __('فعال‌سازی فارسی‌سازی خودکار برای پوسته‌ها.', 'wpsh'),
                    'default' => 'yes',
                ),
            ),
        );

        //START ADDON settings
        global $wpsh_addon;
        $addons = $wpsh_addon;

        $addons_settings = array();

        $addons_settings[] = array(
            'type' => 'notice',
            'class' => 'warning',
            'content' => __('افزودنی‌ها امکاناتی را به فارسی‌ساز وردپرس اضافه می‌کنند که بین وبمستر‌های ایرانی و مدیران وردپرس فارسی محبوب هستند. از این صفحه می‌توانید افزودنی‌ها را <strong>فعال ویا غیرفعال</strong> کنید.', 'wpsh'),
        );

        foreach ($addons as $addon => $value) {

            $extra = ($value['slug'] == 'maintenance') ? true : false;
            $wrap = ($value['slug'] == 'maintenance') ? 'no-border-bottom' : '';

            $addons_settings[] = array(
                'id' => $value['slug'],
                'wrap_class' => $wrap,
                'type' => 'switcher',
                'title' => $value['name'],
                'after' => ' <br /> ' . $value['desc'],
                'default' => ($value['is_active'] == true) ? 'yes' : 'no',
            );

            if ($extra) {
                if (!parent::pro()) {
                    $addons_settings[] = array(
                        'id'     => 'maintenance_text',
                        'type'   => 'text',
                        'title'  => 'متن در دست تعمیر',
                        'after' => ' <br /> متن حالت "در دست تعمیر" فقط برای <a href="https://wpvar.com/pro/" target="_blank" style="text-decoration: none;"><strong>نسخه‌های حرفه‌ای و VIP</strong></a> قابل ویرایش است.',
                        'attributes'     => array(
                            'placeholder' => 'سایت در دست تعمیر می‌باشد، به‌زودی برمی‌گردیم. لطفا دقایقی دیگر مجددا مراجعه فرمایید.',
                            'disabled'    => 'disabled',
                        )
                    );
                } else {
                    $addons_settings[] = array(
                        'id'     => 'maintenance_text',
                        'type'   => 'text',
                        'title'  => 'متن در دست تعمیر',
                        'after' => ' <br /> متن سفارشی برای نمایش به کاربران در حالت "در دست تعمیر".',
                        'default'   =>  'سایت در دست تعمیر می‌باشد، به‌زودی برمی‌گردیم. لطفا دقایقی دیگر مجددا مراجعه فرمایید.'
                    );
                }
            }
        }

        $fields[] = array(
            'name' => 'addons',
            'title' => __('افزودنی‌ها', 'wpsh'),
            'icon' => 'dashicons-admin-plugins',
            'fields' => $addons_settings
        );

        $fields[] = array(
            'name' => 'compatibility',
            'title' => __('سازگاری', 'wpsh'),
            'icon' => 'dashicons-hammer',
            'fields' => array(
                array(
                    'type' => 'notice',
                    'class' => 'info',
                    'content' => __('افزونه‌های زیر به طور اختصاصی با افزونه "تاریخ شمسی و فارسی‌ساز وردپرس"، <strong>سازگار</strong> شده اند ویا سازگاری آنها به‌طور دستی بررسی و تایید شده است. اگر افزونه‌های مورد استفاده شما دچار تداخل با تاریخ شمسی ویا این افزونه شده باشند، جهت اضافه کردن آن‌ها به این لیست با ما تماس بگیرید. اطلاعات تماس در صفحه "درباره" موجود می باشد. تعداد انگشت شماری از افزونه‌ها نیاز به سازگاری دارند (اکثرا به دلیل عدم استفاده از تاریخ‌های هسته وردپرس) و بقیه به طور خودکار فارسی‌سازی خواهند شد.', 'wpsh'),
                ),
                array(
                    'id' => 'activate-woocommerce',
                    'type' => 'switcher',
                    'title' => __('ووکامرس', 'wpsh'),
                    'description' => __('فروشگاه ساز ووکامرس', 'wpsh'),
                    'after'   => $this->makhzan_download('woocommerce', 'دانلود ووکامرس'),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'activate-classic-editor',
                    'type' => 'switcher',
                    'title' => __('ویرایشگر کلاسیک', 'wpsh'),
                    'description' => __('ویرایشگر کلاسیک وردپرس برای حذف ویرایشگر گوتنبرگ', 'wpsh'),
                    'after'   => $this->makhzan_download('classic-editor', 'دانلود ویرایشگر کلاسیک'),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'activate-contactform',
                    'type' => 'switcher',
                    'title' => __('فرم تماس 7 و فلامینگو', 'wpsh'),
                    'description' => __('افزونه Contact Form 7 و Flamingo', 'wpsh'),
                    'after'   => $this->makhzan_download('contact-form-7', 'دانلود افزونه تماس‌با‌ما'),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'activate-rankmath',
                    'type' => 'switcher',
                    'title' => __('رنک مث', 'wpsh'),
                    'description' => __('افزونه بهبود سئو وردپرس Rank Math', 'wpsh'),
                    'after'   => $this->makhzan_download('seo-by-rank-math', 'دانلود افزونه رنک مث'),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'activate-elementor',
                    'type' => 'switcher',
                    'title' => __('المنتور', 'wpsh'),
                    'description' => __('صفحه ساز المنتور', 'wpsh'),
                    'after'   => $this->makhzan_download('elementor', 'دانلود المنتور'),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'activate-ssl',
                    'type' => 'switcher',
                    'title' => __('SSL واقعا ساده', 'wpsh'),
                    'description' => __('فعال سازی حالت SSL دامنه', 'wpsh'),
                    'after'   => $this->makhzan_download('really-simple-ssl', 'دانلود SSL واقعا ساده'),
                    'default' => 'yes',
                ),
                array(
                    'id' => 'activate-bbpress',
                    'type' => 'switcher',
                    'title' => __('بی بی پرس', 'wpsh'),
                    'description' => __('انجمن گفتگو ساز بی بی پرس', 'wpsh'),
                    'after'   => $this->makhzan_download('bbpress', 'دانلود بی بی پرس'),
                    'default' => 'yes',
                ),
                array(
                    'type' => 'notice',
                    'class' => 'danger',
                    'content' => __('با فعال کردن گزینه زیر، شمسی‌سازی بخش مدیریت غیرفعال خواهد شد ولی محیط کاربری وردپرس همچنان شمسی‌سازی خواهد شد. این گزینه را هنگامی فعال کنید که شمسی‌ساز با افزونه‌های دیگر دچار تداخل باشد.', 'wpsh'),
                ),
                array(
                    'id' => 'activate-admin-shamsi',
                    'type' => 'switcher',
                    'title' => __('غیرفعال کردن شمسی‌سازی مدیریت', 'wpsh'),
                    'description' => __('قبل از فعال کردن این گزینه توضیحات بالا را مطالعه کنید.', 'wpsh'),
                    'default' => 'no',
                ),
                array(
                    'type' => 'notice',
                    'class' => 'danger',
                    'content' => __('با فعال کردن گزینه زیر، شمسی‌ساز درصورتی که زبانی به غیر از فارسی نصب شده باشد غیرفعال خواهد شد. این ویژگی برای وبسایت‌های چندزبانه می‌تواند مفید باشد. .', 'wpsh'),
                ),
                array(
                    'id' => 'activate-no-lang-no-shamsi',
                    'type' => 'switcher',
                    'title' => __('غیرفعال کردن شمسی‌ساز برای زبان‌های غیرفارسی', 'wpsh'),
                    'description' => __('قبل از فعال کردن این گزینه توضیحات بالا را مطالعه کنید. از بسته‌های زبانی ایران و افغانستان پشتیبانی می‌شود.', 'wpsh'),
                    'default' => 'no',
                ),
            )
        );

        $fields[] = array(
            'name' => 'translate',
            'title' => __('مترجم', 'wpsh'),
            'icon' => 'dashicons-admin-site-alt',
            'fields' => array(

                array(
                    'type' => 'group',
                    'id' => 'translate-group',
                    'title' => __('مترجم وردپرس', 'wpsh'),
                    'description' => __('برروی "ترجمه جدید" کلیک کرده و در قسمت "از" عبارتی را که می‌خواهید ترجمه شود وارد کنید (اکثرا به انگلیسی) و در قسمت "به" ترجمه خود از آن عبارت را وارد کنید.  نسبت به حروف بزرگ و کوچک و فاصله حساس می باشد.', 'wpsh'),
                    'options' => array(
                        'repeater' => true,
                        'accordion' => true,
                        'button_title' => __('ترجمه جدید', 'wpsh'),
                        'group_title' => __('ترجمه', 'wpsh'),
                        'limit' => 2000,
                        'sortable' => true,
                    ),
                    'fields' => array(

                        array(
                            'id' => 'translate-source',
                            'type' => 'textarea',
                            'title' => __('از', 'wpsh'),
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => __('متن به زبان اصلی', 'wpsh'),
                                'class' =>  'wpsh-ltr'
                            ),
                        ),
                        array(
                            'id' => 'translate-target',
                            'type' => 'textarea',
                            'title' => __('به', 'wpsh'),
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => __('ترجمه به فارسی', 'wpsh'),
                            ),
                        ),
                    ),
                ),

            ),
        );

        if(!parent::pro()) {
            $redirect_status = 'disabled';
            $redirect_status_text = array(
                    'id' => 'redirect-status-text',
                    'type'    => 'notice',
                    'class'   => 'warning',
                    'content' => 'امکان انتخاب نوع ریدایرکت فقط برای نسخه‌های حرفه‌ای و VIP مقدور است.',
            );
        } else {
            $redirect_status = 'enabled';
            $redirect_status_text = array(
                'id' => 'redirect-status-text',
                'type'    => 'notice',
                'class'   => 'warning',
                'content' => 'برای ریدایرکت همیشگی، گزینه دائمی را انتخاب کنید.',
        );
        }

        $fields[] = array(
            'name' => 'redirect',
            'title' => __('ریدایرکت', 'wpsh'),
            'icon' => 'dashicons-admin-links',
            'fields' => array(

                array(
                    'type' => 'group',
                    'id' => 'redirect-group',
                    'title' => __('ریدایرکت', 'wpsh'),
                    'description' => __('ریدایرکت صفحات وردپرس به لینک‌ جدید به صورت موقت یا دائمی. ریدایرکت‌های تعریف شده سازگار با سئو می‌باشند.', 'wpsh'),
                    'options' => array(
                        'repeater' => true,
                        'accordion' => true,
                        'button_title' => __('ریدایرکت جدید', 'wpsh'),
                        'group_title' => __('ریدایرکت', 'wpsh'),
                        'limit' => 2000,
                        'sortable' => true,
                    ),
                    'fields' => array(

                        array(
                            'id' => 'redirect-source',
                            'type' => 'textarea',
                            'title' => __('از', 'wpsh'),
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => __('برای مثال: ' . home_url() . '/redirect-from', 'wpsh'),
                                'pattern' => esc_html__('https?://.+', 'wpsh'),
                                'class' =>  'wpsh-ltr wpsh-redirect',
                                'required'  =>  'required'
                            ),
                        ),
                        array(
                            'id' => 'redirect-target',
                            'type' => 'textarea',
                            'title' => __('به', 'wpsh'),
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => __('برای مثال: ' . home_url() . '/redirect-to', 'wpsh'),
                                'pattern' => esc_html__('https?://.+', 'wpsh'),
                                'class' =>  'wpsh-ltr wpsh-redirect',
                                'required'  =>  'required'
                            ),
                        ),
                        array(
                            'id'      => 'redirect-status',
                            'type'    => 'radio',
                            'title'   => 'نوع ریدایرکت',
                            'options' => array(
                                '302'   => 'موقتی (ریدایرکت 302)',
                                '301'    => 'دائمی (ریدایرکت 301)',
                            ),
                            'class' =>  'wpsh-radio',
                            'attributes'    => array(
                                'data-radio-status'  =>  $redirect_status,
                            ),
                        ),
                        $redirect_status_text
                    ),
                ),

            ),
        );

        $fields[] = array(
            'title' => __('استایل سفارشی', 'wpsh'),
            'icon' => 'dashicons-editor-code',
            'name' => 'custom-css',
            'fields' => array(
                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => __('<p>برای وارد کردن استایل‌های سفارشی باید از کدهای CSS استفاده کنید. توجه داشته باشید که استایل‌های وارد شده در این قسمت فقط در وردپرس به زبان فارسی اجرا می‌شوند. برای مثال از این قسمت جهت رفع مشکلات پوسته خود می‌توانید استفاده کنید. مانند تغییر فونت و اندازه متن و رنگ‌ها و ...</p>', 'wpsh'),
                ),
                array(
                    'type' => 'notice',
                    'class' => 'warning',
                    'content' => __('اگر با زبان CSS آشنایی ندارید، لطفا تنظیمات این صفحه را تغییر ندهید.', 'wpsh'),
                ),
                array(
                    'id' => 'fa-custom-css',
                    'type' => 'ace_editor',
                    'title' => __('کد‌های CSS', 'wpsh'),
                    'options' => array(
                        'theme' => 'ace/theme/chrome',
                        'mode' => 'ace/mode/css',
                        'showGutter' => true,
                        'showPrintMargin' => true,
                        'enableBasicAutocompletion' => true,
                        'enableSnippets' => true,
                        'enableLiveAutocompletion' => true,
                    ),
                    'attributes' => array(
                        'style' => 'height: 300px; max-width: 700px;',
                    ),
                ),

            ),

        );

        $fields[] = array(
            'name' => 'backup',
            'title' => __('تهیه پشتیبان', 'wpsh'),
            'icon' => 'dashicons-backup',
            'fields' => array(

                array(
                    'type' => 'backup',
                    'title' => __('پشتیبان', 'wpsh'),
                    'description' => __('برای ذخیره تنظیمات افزونه وردپرس فارسی و یا انتقال این تنظیمت به وبسایت دیگر می‌توانید از افزونه پشتیبان تهیه کنید. برای آپلود پشتیبان برروی بارگذاری کلیک کنید. هشدار: بازنویسی گزینه‌ها موجب تغییر گزینه‌ها به حالت کارخانه و پیشفرض خواهد شد.', 'wpsh'),

                ),

            )
        );

        $fields[] = array(
            'name' => 'about',
            'title' => __('درباره و قوانین', 'wpsh'),
            'icon' => 'dashicons-nametag',
            'fields' => array(

                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => __('
                <h2>افزونه تاریخ شمسی و فارسی‌ساز وردپرس</h2>
                <p>افزونه تاریخ شمسی و فارسی‌ساز وردپرس با این هدف برنامه‌نویسی شده که بتوانید تنها با فعال‌سازی یک افزونه رابط کاربری وردپرس را در استاندارد ترین حالت فارسی‌سازی کنید. برای سال‌ها مشکل اصلی کاربران وردپرس، عدم محیط کاربری سازگار با زبان فارسی بوده است. اکنون تنها با نصب این افزونه وردپرس خود را از همه نظر فارسی کنید. <br />
                برخلاف نمونه‌های مشابه، این افزونه کمترین تغییر را در هسته وردپرس انجام می دهد و در نتیجه موجب افت سرعت و عملکرد وردپرس نمی‌شود و عاری از هرگونه خطا می باشد. کدهای افزونه چندمرتبه جهت افزایش عملکرد و استاندارد سازی بررسی شده اند.<br />
                از ویژگی‌های دیگر افزونه، وجود همه امکانات فارسی‌سازی در یک افزونه است. تنها با نصب این افزونه دیگر نیازی به افزونه‌های دیگر برای فارسی‌سازی سایتتان نخواهید داشت.<br /><br />
                <strong>برنامه‌نویس:</strong> علی فرجی <br />
                <strong>وبسایت:</strong> <a href="https://wpvar.com" target="_blank">وردپرس فارسی</a><br />
                <strong>ایمیل:</strong> <a href="https://wpvar.com/contact/" target="_blank">تماس با ما</a><br />
                </p>
                <h2>همکاری‌با‌ما</h2>
                <p>اگر علاقه‌مند به وردپرس و پروژه‌های بازمتن هستید و می‌خواهید در توسعه وردپرس مشارکت داشته باشید برای نقطه شروع می‌توانید <a href="https://wpvar.com/collaboration/" target="_blank">این صفحه را مطالعه کنید</a>.</p>                <h2>تشکر‌ها</h2>
                <ul>
                <li>
                با تشکر از جناب آقای <strong>سالار کامجو</strong> و وبسایت <a href="https://upgraph.ir" target="_blank">Upgraph.ir</a> جهت طراحی المان‌های گرافیک افزونه.
                </li>
                <li>
                افزونه تاریخ شمسی و فارسی‌ساز وردپرس برای بهبود رابط کاربری علاوه‌بر فونت‌های تجاری، از فونت رایگان و بازمتن <a href="https://github.com/rastikerdar/vazir-font" target="_blank">وزیر</a> استفاده می‌کند. با تشکر از جناب آقای <strong>راستی کردار</strong>.
                </li>
                </ul>
                <h2>مستندات</h2>
                <p>مستندات فنی افزونه را می‌توانید <a href="https://wpvar.com/wp-shamsi-docs/" target="_blank">از طریق این صفحه</a> مطالعه کنید.</p>
                <h2>قوانین</h2>
                <p>با ادامه استفاده از این افزونه <a href="https://wpvar.com/policy/" target="_blank">قوانین مندرج در این صفحه</a> را می‌پذیرید.</p>
                <h2>حریم خصوصی</h2>
                <p>وبسایت به معنی وبگاه، آدرس اینترنتی، وردپرس و سایت به کار برده خواهد شد.</p>
                <p>وبسایت https://wpvar.com و زیر‌دامنه‌های آن در ادامه “وبسایت ما” نام برده خواهد شد.</p>
                <p>“افزونه‌های برنامه‌نویسی شده توسط وبسایت ما” در ادامه “افزونه‌های ما” نام برده خواهد شد که شامل این افزونه نیز می‌شود. مالکیت این افزونه ها دراختیار وبسایت ما می‌باشد.
                <p>از وبسایت افرادی که به این صفحه و تنظیمات افزونه‌های ما دسترسی مدیریتی دارند “وبسایت شما” نام برده خواهد شد. دسترسی به این صفحه و تنظیمات افزونه‌های ما به معنی داشتن دسترسی مدیریت و حق مالکیت بر وبسایت می‌باشد. اگر کاربری مدیر وبسایت محسوب نمی‌شود، نباید به این صفحه دسترسی بدهید.</p>
                <p> شما مدیر وبسایت ' . get_home_url() . ' درنظر گرفته شده اید. درغیر این صورت بند فوق شامل شما نشده و حق تغییر تنظیمات افزونه و اشتراک در خبرنامه را ندارید.</p>
                <p>افزونه‌های ما هیچگونه اطلاعات و داده‌ای را بدون اجازه شما از وبسات شما جمع آوری نمی‌کند. اطلاعات جمع آوری شده درصورت اجازه شما با فعال‌سازی گزینه‌های مربوطه به دو دسته “خبرنامه” و “آمار” تقسیم می‌شوند.</p>
                <h3>خبرنامه</h3>
                <p>با وارد کردن “ایمیل” خود در خبرنامه و اشتراک در آن آخرین اخبار، به‌روزرسانی‌ها و آموزش‌ها و مباحث مرتبط با وردپرس توسط ایمیل به اطلاع شما رسانده خواهد شد.</p>
                <p>ما هم از خبرنامه‌هایی که روزانه ده‌ها ایمیل ارسال می‌کنند متنفریم! پس نگران نباشید به طور میانگین هفتگی بیش از ۱ الی ۲ ایمیل ارسال نخواهد شد.</p>
                <p>هرموقع که بخواهید، از طریق لینک موجود در ایمیل‌های ارسالی می‌توانید اشتراک خود را لغو کنید.</p>
                <p>با عضویت درخبرنامه آدرس ایمیل و آدرس وبسایت شما به وبسایت ما ارسال خواهد شد وبسایت ما و این افزونه حق دسترسی و استفاده از این داده ها را خواهد داشت.</p>
                <p>عضویت در خبرنامه به شما این امکان را می دهد تا هرچه سریع تر از به‌روزرسانی‌های وردپرس که گاها برخی به‌روزرسانی‌های امنیتی می باشند باخبر شوید پس برای حفظ امنیت سایت و مطلع شدن از آخرین اخبار وردپرس، توصیه می‌کنیم در خبرنامه مشترک شوید.</p>
                <p>با اشتراک در خبرنامه نشان می‌دهید که شرایط و ضوابط فوق الذکر را پذیرفته‌اید.</p>
                <h3>آمار</h3>
                <p>در افزونه‌های ما با فعال سازی گزینه “ارسال آمار”، با ارسال آمار وردپرس وبسایت شما به وبسایت ما، کمک فراوانی به ما در توسعه و برنامه‌نویسی این افزونه خواهید کرد.</p>
                <p>این آمار چه استفاده‌ای دارند؟ با استفاده از این آمار تصمیم میگیریم که چه امکانات و سازگاری‌هایی را در نسخه‌های آتی به افزونه‌های ما اضافه کنیم تا رضایت اکثریت را به دنبال داشته باشد.</p>
                <p>چه داده‌هایی در این آمار ارسال می‌شود؟ لیست افزونه‌های فعال و پوسته، تنظیمات افزونه، آدرس اینترنتی و نام و توضیحات و ایمیل وبسایت شما، نسخه افزونه و نسخه وردپرس، نسخه پی اچ پی وبسایت شما.</p>
                <p>با فعال‌سازی گزینه “ارسال آمار” در تنظیمات افزونه‌ها می‌پذیرید که وبسایت ما و افزونه‌های ما اجازه دسترسی و استفاده از داده های وبسایت شما را دارد.</p>
                <p>با غیرفعال‌سازی گزینه ارسال آمار، روند ارسال آمار به ما قطع خواهد شد. و هرموقع که مایل بودید خواهید توانست ارسال آمار را متوقف کنید.</p>
                <h2>فونت‌ها</h2>
                <p>فونت‌های تجاری استفاده شده در این افزونه شامل "ایران سنس"، "ایران سنس دست‌نویس" و "ایران یکان" مشمول قوانین کپی‌رایت بوده و مجوز استفاده از طرف ناشر فقط برای استفاده در محیط مدیریت وردپرس وبسایت شما صادر شده است. اجازه استفاده از این فونت‌ها در قالب و سایر بخش‌های وردپرس را بدون تهیه لایسنس از ناشر، ندارید. همچنین اجازه بازنشر فایل فونت‌ها را نخواهید داشت. عواقب حقوقی نقض این مجوز متوجه وبسایت شما می‌باشد و افزونه‌های ما و وبسایت ما هیچ‌گونه مسئولیتی ندارند.</p>
                <span>کد لایسنس فونت ایران سنس: YA7SYB</span>
                <br />
                <span>کد لایسنس فونت ایران سنس دست‌نویس: SPNNNV</span>
                <br />
                <span>کد لایسنس فونت ایران یکان: V75HKF</span>
                ', 'wpsh'),
                ),
            )
        );

        $fields[] = array(
            'name' => 'hosts',
            'title' => __('هاست وردپرس', 'wpsh'),
            'icon' => 'dashicons-database',
            'fields' => array(
                array(
                    'type' => 'content',
                    'wrap_class' => 'no-border-bottom wpvarnet',
                    'content' => __('سرعت و امنیت وردپرس علاوه بر خود برنامه، تاحد زیادی به میزبان و هاست شما هم بستگی دارد. با استفاده از خدمات هاستینگ اختصاصی وردپرس، خیال‌تان بابت سرعت و امنیت وبسایت‌خود راحت خواهد بود. همه سرورهای ما از جدیدیتری سخت‌افزارها و نرم‌افزارها بهره می‌برند و به‌صورت اختصاصی با وردپرس و ووکامرس بهینه‌سازی شده‌اند تا وردپرس با حداکثر سرعت و کیفیت اجزا شود. همچنین با تهیه هاست از وردپرس فارسی، <strong>نسخه VIP این افزونه به صورت رایگان</strong> ارائه خواهد شد. افزایش سرعت و پایداری هاست، تاثیر زیادی در بهبود سئو وب‌سایت شما خواهد داشت. برای دریافت جزئیات <a href="https://wpvar.net/?wpsh=1" target="_blank"><strong>اینجا کلیک کنید</strong></a>. <br /><br /> <strong>برخی امکانات و مشخصات:</strong>
                    <ul>
                        <li>دامنه رایگان</li>
                        <li>SSL رایگان</li>
                        <li>انتقال رایگان وب‌سایت</li>
                        <li>هارد NVMe SSD</li>
                        <li>تکنولوژی LiteSpeed</li>
                        <li>پنل هاست cPanel</li>
                        <li>پشتیبان‌گیری روزانه</li>
                        <li>امنیت و آپ‌تایم بالا</li>
                        <li>پشتیبانی حرفه‌ای</li>
                        <li>نصب خودکار وردپرس فارسی</li>
                        <li>نسخه VIP این افزونه به‌صورت رایگان</li>
                    </ul>
                    ', 'wpsh'),
                    'before' => '<strong>هاست اختصاصی وردپرس و ووکامرس</strong>',
                ),
                array(
                    'type' => 'content',
                    'wrap_class' => 'no-border-bottom',
                    'content' => '
                    <!-- wpvar.net banner -->
                    <a href="https://wpvar.net/?wpsh=1" target="_blank">
                    <div id="wpvarNetBannerChild" class="scene">
                        <div class="rocket-title">وردپرس را با بالاترین سرعت و امنیت تجربه کنید</div>
                        <div class="rocket">
                            <img src="' . WPSH_URL . 'assets/img/wpvarnet.svg" alt="موشک وردپرس فارسی" />
                        </div>
                    </div>
                    </a>
                    ',
                )
            )
        );

        $pro_intro = '';

        $fields[] = array(
            'name' => 'wpshlicense',
            'title' => __('نسخه حرفه‌ای', 'wpsh'),
            'icon' => 'dashicons-star-filled',
            'fields' => array(
                array(
                    'type' => 'notice',
                    'class' => 'wpvarnet-intro',
                    'content' => __('با تهیه هاست از <a href="https://wpvar.net/?wpsh=1" target="_blank"><strong>وردپرس فارسی</strong></a> می‌توانید نسخه حرفه‌ای و VIP افزونه را به‌صورت <strong>رایگان</strong> دریافت کنید. برای دریافت جزئیات <a href="https://wpvar.net/?wpsh=1" target="_blank"><strong>اینجا کلیک کنید</strong></a>.', 'wpsh'),
                ),
                array(
                    'type'    => 'content',
                    'content'   =>  apply_filters('wpsh_pro_intro', $pro_intro)
                )
            )
        );

        if (parent::pro()) {
            $settings_pro = array();
            $settings_pro = apply_filters('wpsh_settings_pro', $settings_pro);

            foreach ($settings_pro as $key => $value) {
                $fields[] = $value;
            }


            $options_pro = array();

            $fields[] = array(
                'name' => 'wpshpro',
                'title' => __('بیشتر', 'wpsh'),
                'icon' => 'dashicons-ellipsis',
                'fields' => apply_filters('wpsh_pro_options', $options_pro)
            );
        }
        $fields = apply_filters('wpsh_options_array', $fields);

        $options_panel = new Exopite_Simple_Options_Framework($config_submenu, $fields);
    }
}
