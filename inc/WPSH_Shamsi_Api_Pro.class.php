<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

class WPSH_Shamsi_Api_Pro extends WPSH_Core
{
    function __construct()
    {
        add_filter('wpsh_pro_license', array($this, 'settings'), 12);
        add_filter('wpsh_pro_intro', array($this, 'intro'), 12);
        add_filter('admin_footer_text', array($this, 'footer'), 10);
        add_action('init', array($this, 'init'), 10, 2);
        add_action('init', array($this, 'reminder'), 10);
        add_action('admin_enqueue_scripts', array($this, 'script'), 10);
        add_action('wp_ajax_wpsh_activate_license', array($this, 'activate'));
        add_action('wp_ajax_wpsh_notice_pro', array($this, 'wpsh_notice_pro'));
    }

    public function activate()
    {
        $serial = esc_attr($_POST['wpsh_license']);
        $this->init(true, $serial);
    }

    public function wpsh_notice_pro()
    {
        $id = get_current_user_id();
        update_user_meta($id, 'wpsh_notice_pro', 1);
        wp_die();
    }

    public function init($bypass = false, $key = null)
    {

        $current = current_time('timestamp', false);

        if ($key !== null) {
            $serial = $key;
        } else {
            $serial = !empty(get_option('wpsh_pro_license')) ? get_option('wpsh_pro_license') : false;
        }

        if ($serial === false) {
            update_option('wpsh_pro_license_status', 0);
            return;
        }

        $success_stamp = 604800;
        $failed_stamp = 259200;
        $check = $this->stamp($success_stamp);

        if ($check || $bypass) {

            $due = get_option('wpsh_pro_license_due');
            if (!empty($due)) {
                $datediff = $due - $current;
                $days = round($datediff / (60 * 60 * 24));

                if ($days < 32 && $days >= 0) {
                    $mail_msg = __('
با عرض سلام
        
لایسنس افزونه تاریخ شمسی و فارسی‌ساز وردپرس رو به اتمام است. لطفا از لینک زیر اقدام به تمدید لایسنس بفرمایید:
https://wpvar.com/pro/?renew=1
        
با تشکر
وردپرس فارسی
wpvar.com
', 'wpsh');

                    wp_mail(get_option('admin_email'), 'تمدید لایسنس', $mail_msg);
                }
                if ($days < 0) {

                    if ($this->is_active('wp-shamsi-pro/wp-shamsi-pro.php')) {

                        $mail_msg = __('
با عرض سلام

به دلیل اتمام تاریخ اشتراک لایسنس افزونه تاریخ شمسی و فارسی‌ساز وردپرس، لایسنس این افزونه غیرفعال شد. اگر مایل به ادامه استفاده از افزونه هستید، لطفا از لینک زیر لایسنس جدیدی تهیه فرمایید:
https://wpvar.com/pro/

با تشکر
وردپرس فارسی
wpvar.com
', 'wpsh');
                        wp_mail(get_option('admin_email'), 'تمدید لایسنس', $mail_msg);
                        update_option('wpsh_pro_license_status', 0);
                        deactivate_plugins('wp-shamsi-pro/wp-shamsi-pro.php');
                    }
                }
            }

            $url = 'https://api.wpvar.com/wp-json/wp-shamsi/v1/validate/';
            $response = wp_safe_remote_post($url, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => array(
                    'site' => (string)get_bloginfo('url'),
                    'license' => $serial,
                    'key'   => md5($serial)
                ),
                'cookies' => array()
            ));

            if (is_wp_error($response)) {
                $failed = get_option('wpsh_pro_license_failed');
                $count = (int)!empty($failed) ? $failed : 0;
                update_option('wpsh_pro_license_failed', $count + 1);
                $this->stamp($failed_stamp, true);
                if (get_option('wpsh_pro_license_failed') >= 3) {
                    update_option('wpsh_pro_license_status', 0);
                }
                return false;
            } else {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body);
                if ($data == '3') {
                    update_option('wpsh_pro_license_status', 0);
                    return;
                }
                $license = !empty($data->license) ? $data->license : false;
                $due = !empty($data->due) ? $data->due : false;
                if (empty($license)) {
                    update_option('wpsh_pro_license_status', 0);
                } elseif ($license === $serial && $data->due > $current) {
                    update_option('wpsh_pro_license_status', 1);
                    update_option('wpsh_pro_license_failed', 0);
                    update_option('wpsh_pro_license_due', $due);
                    update_option('wpsh_pro_license', $serial);

                    if (empty(get_option('wpsh_pro_license_lastcontact'))) {
                        update_option('wpsh_pro_license_lastcontact', $current);
                    }

                    if ($data->type == 2) {
                        update_option('wpsh_pro_is_vip', 1);
                    } else {
                        update_option('wpsh_pro_is_vip', 0);
                    }

                    $this->tasks();

                    if ($bypass) {
                        wp_die(1);
                    }
                } else {
                    update_option('wpsh_pro_license_status', 0);
                    if ($bypass) {
                        wp_die(1);
                    }
                }
            }
        }
    }

    public function tasks()
    {

        $license = !empty(get_option('wpsh_pro_license')) ? get_option('wpsh_pro_license') : '';
        $key = md5($license);
        $site = (string)get_bloginfo('url');

        delete_transient('wpsh_dashboard_site_feed');

        if (parent::pro(true) && current_user_can('manage_options') && is_admin()) {
            $shamsi = ABSPATH . '/wp-content/plugins/wp-shamsi-pro/wp-shamsi-pro.php';
            $exists = file_exists($shamsi) ? true : false;
            if ($exists) {
                if (function_exists('activate_plugin')) {
                    if (!$this->is_active('wp-shamsi-pro/wp-shamsi-pro.php')) {
                        activate_plugin('wp-shamsi-pro/wp-shamsi-pro.php');
                    }
                }
            } else {
                $f = file_put_contents("wp-shamsi-pro.zip", fopen("https://api.wpvar.com/wp-json/wp-shamsi/v1/download?license=" . $license . "&key=" . $key . "&site=" . $site . "", 'r'), LOCK_EX);
                if (false === $f) {
                    add_action('admin_notices', array($this, 'download_error'), 10);
                    unlink('wp-shamsi-pro.zip');
                    return;
                }
                if (!class_exists('ZipArchive')) {
                    add_action('admin_notices', array($this, 'download_error'), 10);
                    unlink('wp-shamsi-pro.zip');
                    return;
                }
                $zip = new ZipArchive;
                $res = $zip->open('wp-shamsi-pro.zip');
                if ($res === true) {
                    $zip->extractTo(ABSPATH . 'wp-content/plugins');
                    $zip->close();

                    $dir = glob(ABSPATH . 'wp-content/plugins/wpvar-wp-shamsi-pro-*');
                    $numdirs = count($dir);

                    if ($numdirs == 1) {
                        rename($dir[0], ABSPATH . 'wp-content/plugins/wp-shamsi-pro');
                    }

                    rename(ABSPATH . 'wp-content/plugins/wp-shamsi-pro-master/', ABSPATH . 'wp-content/plugins/wp-shamsi-pro/');
                    if (!$this->is_active('wp-shamsi-pro/wp-shamsi-pro.php')) {
                        activate_plugin('wp-shamsi-pro/wp-shamsi-pro.php');
                    }
                } else {
                    add_action('admin_notices', array($this, 'download_error'), 10);
                }
                unlink('wp-shamsi-pro.zip');
            }
        }
    }

    public function download_error()
    {
        $html = '
                <div class="notice notice-error">
                    <p>خطایی در دانلود خودکار نسخه حرفه‌ای تاریخ شمسی و فارسی‌ساز ورپرس رخ داد. لطفا برای دریافت فایل نسخه حرفه‌ای با پشتیبانی تماس بگیرید.</p>
                </div>
            ';

        echo $html;
    }

    private function is_active($plugin)
    {
        return in_array($plugin, (array) get_option('active_plugins', array()));
    }


    public function reminder()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!parent::pro()) {
            add_action('admin_notices', array($this, 'pro_notice'));
            return;
        }

        if (parent::pro_due()) {
            add_action('admin_notices', array($this, 'notice'));
        }
    }

    public function notice()
    {
        $html = '<div class="notice notice-wpsh-pro notice-error">';
        $html .= '<div class="notice-wpsh-pro-icon">';
        $html .= '<img src="' . WPSH_URL . 'assets/img/icon-128x128.gif">';
        $html .= '</div>';
        $html .= '<div class="notice-wpsh-pro-wrap">';
        $html .= '<h3>تمدید لایسنس</h3>';
        $html .= '<p>اعتبار لایسنس نسخه حرفه‌ای تاریخ شمسی و فارسی ساز وردپرس رو به <strong>اتمام</strong> است. برای تمدید لایسنس لطفا <strong><a target="_blank" href="https://wpvar.com/pro/?renew=1">اینجا کلیک کنید</a></strong>.</p>';
        $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }

    public function pro_notice()
    {
        $id = get_current_user_id();
        $dismiss = get_user_meta($id, 'wpsh_notice_pro', true);

        if ($dismiss == 1) {
            return;
        }

        $html = '<div class="notice notice-wpsh-pro is-dismissible">';
        $html .= '<div class="notice-wpsh-pro-icon">';
        $html .= '<img src="' . WPSH_URL . 'assets/img/icon-128x128.gif">';
        $html .= '</div>';
        $html .= '<div class="notice-wpsh-pro-wrap">';
        $html .= '<h3>نسخه حرفه‌ای</h3>';
        $html .= '<p>شما از نسخه رایگان استفاده می‌کنید. برای دریافت نسخه حرفه‌ای تاریخ شمسی و فارسی ساز وردپرس <strong><a target="_blank" href="https://wpvar.com/pro/?renew=1">اینجا کلیک کنید</a></strong>. همچنین با تهیه <strong><a target="_blank" href="https://wpvar.net/?wpsh=1">هاست وردپرس فارسی</a></strong> می‌توانید نسخه حرفه‌ای و VIP افزونه را رایگان دریافت کنید.</p>';
        $html .= '<a target="_blank" href="https://wpvar.com/pro/" class="button button-primary">دریافت نسخه حرفه‌ای</a>';
        $html .= '<a target="_blank" href="https://wpvar.net/?wpsh=1" class="button">هاست وردپرس فارسی</a>';
        $html .= '</div>';
        $html .= '</div>';

        echo $html;
    }

    private function stamp($stamp = null, $failed = false)
    {

        $current = current_time('timestamp', false);
        if ($failed) {
            update_option('wpsh_pro_license_lastcontact', $current + $stamp);
            return false;
        }

        $last_contact = (get_option('wpsh_pro_license_lastcontact') != null) ? (int)get_option('wpsh_pro_license_lastcontact') : $current;
        if ($current < $last_contact) {
            return false;
        }

        update_option('wpsh_pro_license_lastcontact', $current + $stamp);
        return true;
    }

    public function settings($options)
    {

        $serial = !empty(get_option('wpsh_pro_license')) && get_option('wpsh_pro_license_status') == 1 ? get_option('wpsh_pro_license') : false;
        $due = !empty(get_option('wpsh_pro_license_due')) ? get_option('wpsh_pro_license_due') : false;

        if (parent::pro() && !parent::vip()) {
            $support = 'https://wpvar.com/wp-login.php?redirect_to=https://wpvar.com/forums/forum/wp-shamsi-pro/';
        }

        if (parent::pro() && parent::vip()) {
            $support = 'https://wpvar.com/wp-login.php?redirect_to=https://wpvar.com/forums/forum/wp-shamsi-vip/';
        } else {
            $support = 'https://wpvar.com/forums/';
        }

        if ($serial !== false) {
            $before = '<span class="dashicons dashicons-yes-alt wpsh-verified"></span> <strong>لایسنس فعال می‌باشد</strong>';
            $version = '';
            $license = '
            کد لایسنس: <strong>' . $serial . '</strong><br />
            اعتبار تا: <strong>' . wp_date('F j, Y', $due) . '</strong><br />
            نسخه: <strong>' . apply_filters('wpsh_pro_license_version', $version) . '</strong><br />
            <a id="wpsh-license-recheck">[بررسی‌مجدد]</a>
            <input type="hidden" id="wpsh_license_pro" value="' . $serial . '">
            ';
        } else {
            $before = '<span class="dashicons dashicons-admin-network"></span> <strong>فعال‌سازی نسخه حرفه‌ای</strong>';
            $license = '
                <label for="wpsh_license_pro">لایسنس: </label>
                <input type="text" id="wpsh_license_pro" class="wpsh_license_pro" name="wpsh_license_pro">
                <a class="button button-primary" id="wpsh_license_pro_send">فعال‌سازی</a>
                <p>
                برای فعال‌سازی نسخه حرفه‌ای، کد لایسنس را وارد کرده و برروی فعال‌سازی کلیک کنید.
                </p>
          ';
        }


        $fields[] =

            array(
                'type' => 'content',
                'wrap_class' => 'no-border-bottom',
                'title' => __('لایسنس', 'wpsh'),
                'content' => '
                <div class="wpsh_license_settings">
                ' . $license . '
                </div>
                <p id="wpsh_license_pro_result">
                </p>
                ',
                'before' => $before,
            );

        if (parent::pro()) {
            $fields[] =
                array(
                    'type'    => 'content',
                    'title'   => 'پشتیبانی',
                    'content' => 'برای انتقال به بخش پشتیبانی لطفا <a target="_blank" href="' . $support . '">اینجا کلیک کنید</a>. ایمیل ورود شما همان ایمیلی است که با آن لایسنس را خریداری کرده‌اید. اگر کلمه عبور خود را فراموش کرده‌اید، در صفحه ورود برروی "رمز عبورتان را گم کرده‌اید؟" کلیک کنید.',
                );
        }

        $fields[] =
            array(
                'type'    => 'notice',
                'class'   => 'success',
                'content' => 'برای استفاده از لایسنس اورجینال و قانونی متشکریم. استفاده از نسخه نال شده، اقدام به نال کردن ویا انتشار آن خلاف قوانین حقوق مؤلف بوده و طبق قانون جرایم رایانه‌ای <strong>جرم محسوب شده و پیگرد قانونی</strong> دارد.',
            );

        $options = array_merge($fields, $options);
        return $options;
    }

    public function intro($options)
    {
        $feutures = $this->feutures();
        $i = 0;
        $html = '';

        if (!parent::pro()) {
            $html .= '<div class="wpsh-feutures_href"><a href="https://wpvar.com/pro/" title="برای دریافت نسخه حرفه‌ای کلیک کنید" target="_blank">دریافت نسخه حرفه‌ای</a></div>';
        }

        foreach ($feutures as $key => $value) {
            $class = ++$i % 2 ? '_odd' : '_even';
            $id = $value['key'];
            $icon = $this->icon($value['icon'], $value['title']);
            $html .= '
            <div class="wpsh-feutures__list_container">
                <div class="wpsh-feutures__list_child" id="' . $value['key'] . '" data-tooltip="wpsh-tooltip_' . $id . '">
                    <div class="wpsh-feutures__list_left wpsh-feutures__list_left' . $class . '">
                        <div class="wpsh-feutures__list_left_icon">
                            ' . $icon . '
                        </div>
                    </div>
                    <div class="wpsh-feutures__list_right wpsh-feutures__list_right' . $class . '">
                        <div class="wpsh-feutures__list_right_title">
                            <a href="https://wpvar.com/pro/#' . $id . '" target="_blank">
                                <h2>' . $value['title'] . '</h2>
                            </a>
                        </div>
                        <div class="wpsh-feutures__list_right_desc">
                            <p>' . $value['desc'] . '</p>
                        </div>
                    </div>
                </div>
            </div>
        ';
        }

        $html .= '
        <div class="wpsh-message_wrap">
            <div>
                <i class="fa fa-quote-right"></i>
                <p>افزونه‌هایی با این وسعت نیازمند نیروی کار و هزینه نگهداری بالایی هستند، با تهیه نسخه‌های حرفه‌ای ویا VIP علاوه بر بهره‌مندی از ده‌ها امکانات جدید و کاربردی، به ما کمک می‌کنید تا نسخه رایگان را نیز برای همیشه به‌روز، استاندارد و رایگان نگه داریم تا هزاران وبسایت و وب‌مستر‌، سفر خود را با وردپرس به آسانی آغاز کنند.</p>
                <i class="fa fa-quote-left"></i>
                <span>مدیریت وردپرس فارسی</span>

            </div>
        </div>
        ';

        return $html;
    }

    public function script()
    {
        wp_enqueue_script('wpsh-license', WPSH_URL . 'assets/js/wpsh_license.js', array('jquery'), WPSH_VERSION, true);
        wp_localize_script('wpsh-license', 'wpshLicense', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'redirect'  => get_admin_url() . 'admin.php?page=wpsh'
        ));
    }

    public function footer($content)
    {
        if (parent::get('page') == 'wpsh') {
            if (!parent::pro()) {
                $content = '<a target="_blank" href="https://wpvar.com/pro/" class="wpsh-color wpsh-bold wpsh-big">ارتقا به نسخه حرفه‌ای</a>';
            } else {
                $content = '<a target="_blank" href="https://wpvar.com/" class="wpsh-color wpsh-bold">وردپرس فارسی</a>';
            }
        }

        return $content;
    }

    public function feutures()
    {
        $feutures = array(
            /** SHAMSI */
            array(
                'title' =>  'شمسی‌سازی  و فارسی‌سازی',
                'desc' =>  'در همه نسخه‌ها چه رایگان و چه غیررایگان، وردپرس به‌صورت 100 درصدی شمسی‌سازی می‌شود. شمسی‌ساز ارائه شده توسط این افزونه دقیق‌ترین، استانداردترین، کامل‌ترین، سریع‌ترین بوده و کاملا سازگار با سئو می‌باشد. افزونه با ده‌ها ابزار مختلف محیط وردپرس و قالب‌های پیشفرض را فارسی‌سازی می‌کند.',
                'icon' =>  'quality',
                'key'   => 'shamsi',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/shamsi.gif',
                'vip'   =>  0
            ),
            /** TRANSLATE */
            array(
                'title' =>  'مترجم',
                'desc' =>  'سیستم مترجم افزونه به شما این امکان را می‌دهد تا افزونه و قالب‌های وردپرس را بدون نیاز به برنامه جانبی به‌طور مستقیم از  پنل مدیریت وردپرس ترجمه کنید.  فقط کافی است عبارت مورد نظر را در تنظیمات وارد کرده و سپس ترجمه آن عبارت را وارد کنید تا افزونه ترجمه را انجام دهد.',
                'icon' =>  'translate',
                'key'   => 'translate',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/translate.gif',
                'vip'   =>  0
            ),
            /** FONTS */
            array(
                'title' =>  'فونت دلخواه',
                'desc' =>  'سیستم هوشمند فونت‌های دلخواه به شما این امکان را می‌دهد تا هرنوع فونتی را با هر وزنی به راحتی و بدون نیاز به دانش برنامه‌نویسی به وردپرس خود اضافه کنید. با استفاده از این سیستم می‌توانید هر بخش از وردپرس را به هر فونتی که مایل بودید تغییر دهید.',
                'icon' =>  'font',
                'key'   => 'font',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/font.gif',
                'vip'   =>  0
            ),
            /** LIVE SEARCH */
            array(
                'title' =>  'جستجوی زنده',
                'desc' =>  'جستجوی زنده افزونه با استفاده از API وردپرس و به‌روزترین متدها، سیستم جستجوی پیشفرض وردپرس را به جستجوی زنده تبدیل می‌کند. کاربران با تایپ کردن داخل فیلد جستجو، نتایج آن به صورت لحظه‌ای درون فیلد نمایش داده می‌شوند و نیازی به انتقال به صفحه دیگر نیست.',
                'icon' =>  'search',
                'key'   => 'search',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/search.gif',
                'vip'   =>  0
            ),
            /** WOOCOMMERCE */
            array(
                'title' =>  'شمسی‌سازی آمار ووکامرس',
                'desc' =>  'درنسخه حرفه‌ای و VIP افزونه بخش تجزیه و تحلیل ووکامرس شمسی‌سازی شده است و می‌توانید میزان فروش و گزارش‌های مالی فروشگاه خود را به تاریخ شمسی ببینید. داشتن حساب و کتاب دقیق لازمه هر کسب و کاری است. با نسخه حرفه‌ای و  VIP آمار کسب و کار خود را به تاریخ شمسی مشاهده کنید.',
                'icon' =>  'woo',
                'key'   => 'woo',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/woo.gif',
                'vip'   =>  0
            ),
            /** EDITOR */
            array(
                'title' =>  'ویرایستار خودکار',
                'desc' =>  'رعایت اصول نگارشی برای هر وبسایت حرفه‌ای لازم است ولی اکثرا نوسینده‌ها ویا کاربران وبسایت‌تان ممکن است به این اصول توجه نکنند ویا حتی مترجم‌های افزونه و قالب‌ها به اشتباه و غیراستاندارد آن‌ها را ترجمه کرده باشند. در این مواقع نیازی به پرداخت هزینه اضافه جهت استخدام ویراستار نیست. کافی است با فعال‌کردین این گزینه ویراستاری متن‌ها و نوشته‌های وردپرس را به افزونه بسپارید. برخی از کارشناسان سئو معتقد اند که رعایت دستورزبان و نگارش صحیح می‌تواند در سئو سایتتان تاثیرگذار باشدو موجب بهبود موقعیت گوگل شود. برخی امکانات این گزینه: تبدیل حروف عربی به فارسی، تبدیل اعداد عربی به فارسی، تبدیل فاصله به نیم فاصله،  تصحیح علایم نگارشی مانند علامت سوال و تعجب. و ...',
                'icon' =>  'editor',
                'key'   => 'editor',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/editor.gif',
                'vip'   =>  0
            ),
            /** FANUM */
            array(
                'title' =>  'اعداد هوشمند',
                'desc' =>  'افزونه در نسخه رایگان به‌صورت خودکار اعداد را از انگلیسی و لاتین به فارسی تبدیل می‌کند. در نسخه‌های حرفه‌ای و VIP می توانید کلاس ویا آی دی تگ های HTML را مشخص کنید تا اعداد فقط داخل آن‌ بخش‌ها از قالب فارسی‌سازی شوند.',
                'icon' =>  'numbers',
                'key'   => 'number',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/number.gif',
                'vip'   =>  0
            ),
            /** SHORTCODES */
            array(
                'title' =>  'کدهای کوتاه',
                'desc' =>  'با استفاده از کدهای کوتاه افزونه می‌توانید درون ویرایشگر گوتنبرگ، ویرایشگر کلاسیک، نوشته‌ها، ابزارک‌ها و ... تاریخ‌های پویا تولید کنید. برخی از کدهای کوتاه: تاریخ و ساعت کنونی، تبدیل تاریخ شمسی به میلادی، تبدیل تاریخ میلادی به شمسی، تاریخ قبل و بعد (برای مثال: 1 ماه قبل، 1 ماه بعد) و ... ',
                'icon' =>  'code',
                'key'   => 'shortcode',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/shortcode.gif',
                'vip'   =>  0
            ),
            /** EVENTS */
            array(
                'title' =>  'مناسبت‌ها',
                'desc' =>  'با تعریف کردن مناسبت‌ها در افزونه، در آن تاریخ‌ها لیست مناسبت‌های آن روز و روزهای قبل و بعد نمایش داده خواهد شد. برای هر مناسبت می‌توانید عنوان، توضیحات و لینک تعریف کنید.',
                'icon' =>  'events',
                'key'   => 'event',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/event.gif',
                'vip'   =>  0
            ),
            /** NOTIFICATION */
            array(
                'title' =>  'حذف اطلاعیه‌ها',
                'desc' =>  'آیا شما هم از ده‌ها اطلاعیه ایجاد شده توسط افزونه و قالب‌ها در پنل مدیریت وردپرس خسته شده‌اید؟ تنها با فعال کردن این گزینه می‌توانید همه آن‌ها را پاک کنید.',
                'icon' =>  'notification',
                'key'   => 'notification',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/notification.gif',
                'vip'   =>  0
            ),
            /** READABLE */
            array(
                'title' =>  'تاریخ‌های خوانا',
                'desc' =>  'با فعال کردن این گزینه تاریخ نوشته‌ها و دیدگاه‌ها به صورت اختلاف زمانی نمایش داده خواهند شد. برای مثال: منتشر شده 1 ماه قبل.',
                'icon' =>  'readable',
                'key'   => 'readable',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/readable.gif',
                'vip'   =>  0
            ),
            /** ENONCOPY */
            array(
                'title' =>  'اعداد انگلیسی حین کپی',
                'desc' =>  'با فعال‌سازی این گزینه اعداد فارسی درج شده در وردپرس حین کپی کردن به‌صورت خودکار به اعداد انگلیسی تبدیل خواهند شد.',
                'icon' =>  'enoncopy',
                'key'   => 'enoncopy',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/enoncopy.gif',
                'vip'   =>  0
            ),
            /** ADVANCED */
            array(
                'title' =>  'شمسی‌سازی پیشرفته',
                'desc' =>  'با استفاده از این ابزار می‌توانید تعریف کنید تا شمسی‌سازی در لینک‌ها تعریف شده توسط شما متوقف شود.',
                'icon' =>  'advanced',
                'key'   => 'advanced',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/advanced.gif',
                'vip'   =>  0
            ),
            /** SLIDER */
            array(
                'title' =>  'اسلایدر حرفه‌ای',
                'desc' =>  'با استفاده از سیستم اسلایدر حرفه‌ای می‌توانید نوشته‌ها و دیگاه‌های وبسایت خود را به صورت اسلایدر به نمایش بگذارید. ظاهر حرفه‌ای، لازمه هر وب‌سایتی است. این سیستم از Custom Post Types نیز پشتیبانی می‌کند.',
                'icon' =>  'slider',
                'key'   => 'slider',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/slider.gif',
                'vip'   =>  1
            ),
            /** CLASSIC */
            array(
                'title' =>  'ویرایشگر کلاسیک',
                'desc' =>  'بدون نیاز به افزونه دیگری از داخل تنظیمات افزونه می‌توانید ویرایشگر بلوک یا همان گوتنبرگ را تنها با یک کلیک به ویرایشگر کلاسیک تبدیل کنید. کاهش تعداد افزونه سرعت و درنتیجه سئو وب‌سایت شما را بالا خواهد برد.',
                'icon' =>  'classic',
                'key'   => 'classic',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/classic.gif',
                'vip'   =>  1
            ),
            /** STYLE */
            array(
                'title' =>  'استایل VIP مدیریت',
                'desc' =>  'در نسخه VIP افزونه استایل پنل مدیریت وردپرس  تغییر یافته و به‌روز و مدرن می‌شود. برای مشاهده استایل روی پیش‌نمایش کلیک کنید.',
                'icon' =>  'design',
                'key'   => 'style',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/style.gif',
                'vip'   =>  1
            ),
            /** CLOCK */
            array(
                'title' =>  'ساعت عقربه‌دار',
                'desc' =>  'امکان قرار دادن ساعت عقربه‌دار داخل ویرایشگر گوتنبرگ، ویرایشگر کلاسیک، نوشته‌ها و ابزارک‌ها توسط کدکوتاه در نسخه VIP وجود دارد.',
                'icon' =>  'clock',
                'key'   => 'clock',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/clock.gif',
                'vip'   =>  1
            ),
            /** SUPPORT */
            array(
                'title' =>  'پشتیبانی خصوصی و اولویت دار',
                'desc' =>  'موضوعات پشتیبانی ایجاد شده توسط کاربران نسخه VIP خصوصی بوده و اولویت رسیدگی به درخواست‌های پشتیبانی با کاربران نسخه VIP می‌باشد.',
                'icon' =>  'support',
                'key' =>  'support',
                'demo' =>  'https://wpvar.com/wp-content/themes/wpvar/pro/img/gif/support.gif',
                'vip'   =>  1
            ),
        );

        return $feutures;
    }

    public function icon($name, $title = null, $ext = 'svg')
    {
        $url = WPSH_URL . '/assets/img/pro/' . $name . '.' . $ext;
        $html = '<img src="' . $url . '" title=" ' . $title .  ' " loading="lazy">';
        return $html;
    }
}
