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
        add_action('init', array(
            $this,
            'register'
        ));
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
        foreach ($themes as $key => $value)
        {
            $result .= '<li><a href="https://wordpress.org/themes/' . $value . '" target="_blank" rel="bofollow">' . ucfirst($value) . '</a></li>';
        }
        $result .= '</ul>';

        return $result;
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
            'menu_title' => __('وردپرس فارسی', 'wpsh') ,
            'parent' => '',
            'submenu' => false,
            'title' => __('افزونه تاریخ شمسی و فارسی ساز وردپرس', 'wpsh') ,
            'capability' => 'manage_options',
            'multilang' => false,
            'icon' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><defs><style>.toplevel_page_wpsh .cls-1{fill:#037cbb;}.toplevel_page_wpsh .cls-2{fill:#fff;}</style></defs><title>وردپرس فارسی</title><g id="Layer_4" data-name="Layer 4"><path fill="#a0a5aa" class="cls-1" d="M127.85,245.74h-.65c-2,0-4-.11-6.15-.22l-1.68-.08c-18.62-.9-34.92-7.63-43-11.56-1.32-.64-2.65-1.32-4.3-2.19a117.82,117.82,0,1,1,56,14.09Z"/><path class="cls-2" d="M128,18.22a109.78,109.78,0,0,1,.78,219.56l-.25,0H127.2c-1.78,0-3.7-.1-5.73-.21l-1.72-.08c-17.18-.83-32.34-7.1-39.87-10.77-1.22-.59-2.45-1.22-4-2A109.79,109.79,0,0,1,128,18.22m0-16A125.79,125.79,0,0,0,68.35,238.76c1.51.8,3,1.56,4.54,2.31,6.2,3,24.43,11.31,46.09,12.36,2.71.13,5.46.31,8.22.31a5.36,5.36,0,0,0,.8,0A125.78,125.78,0,0,0,128,2.22Z"/><path class="cls-2" d="M185.16,142.4a125.12,125.12,0,0,0-4.27-22.62,105.65,105.65,0,0,0-8.09-20.4,78.63,78.63,0,0,0-11.6-16.76Q145.8,66,127.42,66a33.1,33.1,0,0,0-16,4.18,48.49,48.49,0,0,0-14.31,12.4,71.11,71.11,0,0,0-9.42,16.62,70.89,70.89,0,0,0-5.11,17.2,87.3,87.3,0,0,0-1.12,12.49,58.06,58.06,0,0,0,.89,10.36,36.83,36.83,0,0,0,7.51,17.38q13.47,16.67,41.56,18.35c4.76.45,9,.67,12.76.67h14.71a49.68,49.68,0,0,1-8,19.74,80.33,80.33,0,0,1-17.82,19,128.28,128.28,0,0,1-27.07,16,164.06,164.06,0,0,1-33.15,10.62A125.11,125.11,0,0,0,119,253.43c2.66-1.25,5.29-2.58,7.86-4a129.84,129.84,0,0,0,21.61-14.94,110.71,110.71,0,0,0,17.69-19.06,103,103,0,0,0,12.53-22.85A97,97,0,0,0,185,166a117.2,117.2,0,0,0,.66-12.4C185.69,149.82,185.51,146.13,185.16,142.4ZM150.8,150c-2.75.05-4.58.05-5.51.05-3.82,0-7.87-.14-12.18-.4a51.41,51.41,0,0,1-5.51-.58,39.92,39.92,0,0,1-6.44-1.42,29.25,29.25,0,0,1-6.23-2.58,14.93,14.93,0,0,1-4.84-4.22,11.77,11.77,0,0,1-2.67-6.14,43.54,43.54,0,0,1-.44-5.78,53.67,53.67,0,0,1,.35-5.82A58.79,58.79,0,0,1,109.82,112a50.09,50.09,0,0,1,4.45-10.13,29.3,29.3,0,0,1,6-7.47,10.55,10.55,0,0,1,7.11-2.89,12.12,12.12,0,0,1,4.4.76,22,22,0,0,1,4,2A30.1,30.1,0,0,1,139.25,97c1.11,1,2.08,2,2.93,2.8a59.3,59.3,0,0,1,6.27,8.85A77.25,77.25,0,0,1,154,120.13a88.4,88.4,0,0,1,4,13.74,95.77,95.77,0,0,1,1.95,15.64C156.53,149.78,153.51,150,150.8,150Z"/></g></svg>')
        );

        $fields[] = array(
            'name' => 'shamsi',
            'title' => __('شمسی ساز', 'wpsh') ,
            'icon' => 'dashicons-calendar-alt',
            'fields' => array(
                array(
                    'type' => 'content',
                    'wrap_class' => 'no-border-bottom',
                    'title' => __('نسخه افزونه', 'wpsh') ,
                    'content' => __('برای عملکرد بهتر افزونه، همیشه آن را به آخرین نسخه موجود بروزرسانی کنید.', 'wpsh') ,
                    'before' => '<strong>' . WPSH_VERSION . '</strong>',
                ) ,
                array(
                    'type' => 'content',
                    'wrap_class' => 'no-border-bottom',
                    'title' => __('پشتیبانی', 'wpsh') ,
                    'content' => __('برای دریافت پشتیبانی می توانید به <a href="https://wpvar.com">wpvar.com</a> مراجعه کنید', 'wpsh') ,
                    'before' => '<img style="margin: 0 0 -7px 3px;" src=' . WPSH_URL . 'assets/img/logo.png /><strong>وردپرس فارسی</strong>',
                ) ,
                array(
                    'type' => 'notice',
                    'class' => 'info',
                    'content' => __('درصورت غیرفعال کردن گزینه زیر شمسی سازی غیرفعال شده ولی بقیه امکانات افزونه همچنان اجرا خواهند شد.', 'wpsh') ,
                ) ,
                array(
                    'id' => 'activate-shamsi',
                    'type' => 'switcher',
                    'title' => __('شمسی سازی', 'wpsh') ,
                    'description' => __('درصورت غیرفعال کردن تاریخ ها شمسی سازی نخواهند شد.', 'wpsh') ,
                    'default' => 'yes',
                ) ,
                array(
                    'id' => 'activate-shamsi-archive',
                    'type' => 'switcher',
                    'title' => __('شمسی سازی بایگانی', 'wpsh') ,
                    'description' => __('درصورت غیرفعال کردن، بایگانی یا همان آرشیو وردپرس براساس تاریخ میلادی نمایش داده خواهند شد.', 'wpsh') ,
                    'default' => 'yes',
                ) ,
                array(
                    'type' => 'content',
                    'wrap_class' => 'no-border-bottom',
                    'title' => __('زمان محلی و ساختار', 'wpsh') ,
                    'content' => __('برای تنظیم زمان محلی و ساختار تاریخ و زمان وردپرس <a href="' . get_admin_url() . 'options-general.php">اینجا کلیک کنید.</a>', 'wpsh') ,
                ) ,
            ) ,
        );

        $fields[] = array(
            'name' => 'farsi',
            'title' => __('فارسی ساز', 'wpsh') ,
            'icon' => 'dashicons-translation',
            'fields' => array(
                array(
                    'id' => 'persian-num',
                    'type' => 'switcher',
                    'title' => __('اعداد فارسی', 'wpsh') ,
                    'description' => __('فعال سازی تبدیل اعداد انگلیسی به فارسی در محیط کاربری', 'wpsh') ,
                    'default' => 'yes',
                ) ,
                array(
                    'id' => 'persian-admin-num',
                    'type' => 'switcher',
                    'title' => __('اعداد فارسی مدیریت', 'wpsh') ,
                    'description' => __('فعال سازی تبدیل اعداد انگلیسی به فارسی در محیط مدیریت وردپرس', 'wpsh') ,
                    'default' => 'yes',
                ) ,
                array(
                    'id' => 'dashboard-font',
                    'type' => 'switcher',
                    'title' => __('اصلاح فونت بخش مدیریت', 'wpsh') ,
                    'description' => __('درصورت فعال سازی این گزینه، فونت بخش مدیریت وردپرس جهت سازگاری با حروف فارسی تغییر خواهد یافت', 'wpsh') ,
                    'default' => 'yes',
                ) ,
            ) ,
        );
        $fields[] = array(
            'name' => 'translate',
            'title' => __('مترجم', 'wpsh') ,
            'icon' => 'dashicons-admin-site-alt',
            'fields' => array(

                array(
                    'type' => 'group',
                    'id' => 'translate-group',
                    'title' => __('مترجم وردپرس', 'wpsh') ,
                    'description' => __('برروی "ترجمه جدید" کلیک کرده و در قسمت "از" عبارتی را که می خواهید ترجمه شود وارد کنید (اکثرا به انگلیسی) و در قسمت "به" ترجمه خود از آن عبارت را وارد کنید.  نسبت به حروف بزرگ و کوچک و فاصله حساس می باشد.', 'wpsh') ,
                    'options' => array(
                        'repeater' => true,
                        'accordion' => true,
                        'button_title' => __('ترجمه جدید', 'wpsh') ,
                        'group_title' => __('ترجمه', 'wpsh') ,
                        'limit' => 2000,
                        'sortable' => true,
                    ) ,
                    'fields' => array(

                        array(
                            'id' => 'translate-source',
                            'type' => 'textarea',
                            'title' => __('از', 'wpsh') ,
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => __('متن به زبان اصلی', 'wpsh') ,
                            ) ,
                        ) ,
                        array(
                            'id' => 'translate-target',
                            'type' => 'textarea',
                            'title' => __('به', 'plugin-name') ,
                            'attributes' => array(
                                'data-title' => 'title',
                                'placeholder' => __('ترجمه به فارسی', 'wpsh') ,
                            ) ,
                        ) ,
                    ) ,
                ) ,

            ) ,
        );
        $fields[] = array(
            'name' => 'theme',
            'title' => __('پوسته', 'wpsh') ,
            'icon' => 'dashicons-admin-appearance',
            'fields' => array(
                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => __('<p>تمامی پوسته های پیشفرض وردپرس و پوسته های محبوب وردپرس توسط افزونه فارسی سازی می شوند. لیست پوسته های پشتیبانی شده در ادامه درج شده است. پوسته هایی که قبلا توسط شما یا افراد دیگری فارسی سازی شده باشند نیاز به فعال سازی این گزینه ندارند. توجه داشته باشید که فقط پوسته های درج شده فارسی سازی می شوند. برای دانلود آنها می توانید روی نام پوسته کلیک کنید.</p>', 'wpsh') ,
                ) ,
                array(
                    'type' => 'content',
                    'wrap_class' => 'no-border-bottom',
                    'title' => __('پوسته های پشیبانی شده', 'wpsh') ,
                    'content' => $this->theme_list() ,
                    'before' => __('لیست پوسته های فارسی سازی شده توسط افرونه. برای دانلود روی نام هریک کلیک کنید. این لیست در نسخه های آتی گسترش خواهد یافت', 'wpsh') ,
                ) ,
                array(
                    'id' => 'fa-theme',
                    'type' => 'switcher',
                    'title' => __('فعال سازی', 'wpsh') ,
                    'description' => __('فعال سازی فارسی سازی خودکار برای پوسته ها', 'wpsh') ,
                    'default' => 'yes',
                ) ,
            ) ,
        );
        $fields[] = array(
            'title' => __('استایل سفارشی', 'wpsh') ,
            'icon' => 'fa fa-code',
            'name' => 'custom-css',
            'fields' => array(
                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => __('<p>برای وارد کردن استایل های سفارشی باید از کدهای CSS استفاده کنید. توجه داشته باشید که استایل های وارد شده در این قسمت فقط در وردپرس به زبان فارسی اجرا می شوند. برای مثال از این قسمت جهت رفع مشکلات پوسته خود می توانید استفاده کنید. مانند تغییر فونت و اندازه متن و رنگ ها و ...</p>', 'wpsh') ,
                ) ,
                array(
                    'type' => 'notice',
                    'class' => 'warning',
                    'content' => __('اگر با زبان CSS آشنایی ندارید، لطفا تنظیمات این صفحه را تغییر ندهید.', 'wpsh') ,
                ) ,
                array(
                    'id' => 'fa-custom-css',
                    'type' => 'ace_editor',
                    'title' => __('کد های CSS', 'wpsh') ,
                    'options' => array(
                        'theme' => 'ace/theme/chrome',
                        'mode' => 'ace/mode/css',
                        'showGutter' => true,
                        'showPrintMargin' => true,
                        'enableBasicAutocompletion' => true,
                        'enableSnippets' => true,
                        'enableLiveAutocompletion' => true,
                    ) ,
                    'attributes' => array(
                        'style' => 'height: 300px; max-width: 700px;',
                    ) ,
                ) ,

            ) ,

        );

        //START ADDON settings
        global $wpsh_addon;
        $addons = $wpsh_addon;

        $addons_settings = array();

        $addons_settings[] = array(
            'type' => 'notice',
            'class' => 'warning',
            'content' => __('افزودنی ها امکاناتی را به فارسی ساز وردپرس اضافه می کنند که بین وبمستر های ایرانی و مدیران وردپرس فارسی محبوب هستند. از این صفحه می توانید افزودنی ها را <strong>فعال ویا غیرفعال</strong> کنید.', 'wpsh') ,
        );

        foreach ($addons as $addon => $value)
        {

            $addons_settings[] = array(
                'id' => $value['slug'],
                'type' => 'switcher',
                'title' => $value['name'],
                'description' => $value['desc'] . '<br /><div class="plugin-version-author-uri">نسخه ' . $value['version'] . ' | نویسنده: ' . $value['author'] . ' | <a href="' . $value['website'] . '" target="_blank" style="text-decoration:none;">وبسایت</a>
                 | <a href="' . $value['addon_home'] . '" target="_blank" style="text-decoration:none;">جزئیات</a></div>',
                'default' => ($value['is_active'] == true) ? 'yes' : 'no',

            );
        }
        $addons_settings[] = array(
            'type' => 'notice',
            'class' => 'info',
            'content' => __('اگر برنامه نویس وردپرس هستید، می توانید با برنامه نویسی افزودنی ها، آن ها را با نام خود و لینک به وبسایتتان منتشر کنید. <a href="https://gist.github.com/alifaraji/4168948df1b09e9713cfadb75cad0ce3" target="_blank">اطلاعات بیشتر و نمونه کد.</a>', 'wpsh') ,
        );

        $fields[] = array(
            'name' => 'addons',
            'title' => __('افزودنی ها', 'wpsh') ,
            'icon' => 'dashicons-admin-plugins',
            'fields' => $addons_settings
        );
        $fields[] = array(
            'name' => 'backup',
            'title' => __('تهیه پشتیبان', 'wpsh') ,
            'icon' => 'dashicons-backup',
            'fields' => array(

                array(
                    'type' => 'backup',
                    'title' => __('پشتیبان', 'wpsh') ,
                    'description' => __('برای ذخیره تنظیمات افزونه وردپرس فارسی و یا انتقال این تنظیمت به وبسایت دیگر می توانید از افزونه پشتیبان تهیه کنید. برای آپلود پشتیبان برروی بارگذاری کلیک کنید. هشدار: بازنویسی گزینه ها موجب تغییر گزینه ها به حالت کارخانه و پیشفرض خواهد شد.', 'wpsh') ,

                ) ,

            )
        );
        $fields[] = array(
            'name' => 'more',
            'title' => __('بیشتر', 'wpsh') ,
            'icon' => 'dashicons-bell',
            'fields' => array(

                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => __('
                  <div class="plugin-card plugin-card-my-aparat" style="width: 100%;">
                  		<div class="plugin-card-top">
                  				<div class="name column-name">
                  					<h3>
                  						<a href="https://wpvar.com/my-aparat/" style="text-decoration: none;" target="_blank" class="thickbox open-plugin-details-modal">
                  						My Aparat – آپارات من
                              <img src="https://ps.w.org/my-aparat/assets/icon-256x256.gif" class="plugin-icon" alt="">
                  						</a>
                  					</h3>
                  				</div>
                  				<div class="action-links">
                  					<ul class="plugin-action-buttons"><li><a href="https://wpvar.com/my-aparat/" target="_blank" class="thickbox open-plugin-details-modal" style="text-decoration: none;">جزئیات بیشتر و دانلود</a></li></ul>				</div>
                  				<div class="desc column-description">
                  					<p>افزونه آپارات من به طور خودکار ویدیو های ارسال شده در کانال آپارات را در وردپرس منتشر می کند. با استفاده از افزونه بخش جدیدی با نام ویدیو ها در وردپرس ایجاد می شود که ویدیو های آپارات به ور خودکار نمایش داده می شوند. کاربران می توانند در ویدیو ها دیدگاه ارسال کرده و براساس دسته بندی ویدیو ها را جستجو کنند. و ده ها امکانات دیگر ...</p>
                  					<p class="authors"> <cite>بدست <a href="https://wpvar.com/">wpvar.com</a></cite></p>
                  				</div>
                  			</div>

                  		</div>
                  ', 'wpsh') ,
                ) ,
                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => __('
                  <div class="plugin-card plugin-card-wp-randomize" style="width: 100%;">
                  		<div class="plugin-card-top">
                  				<div class="name column-name">
                  					<h3>
                  						<a href="https://wordpress.org/plugins/wp-randomize/" style="text-decoration: none;" target="_blank" class="thickbox open-plugin-details-modal">
                  						ابزارک نمایش تصادفی دسته ها
                              <img src="https://ps.w.org/wp-randomize/assets/icon-256x256.png" class="plugin-icon" alt="">
                  						</a>
                  					</h3>
                  				</div>
                  				<div class="action-links">
                  					<ul class="plugin-action-buttons"><li><a href="https://wordpress.org/plugins/wp-randomize/" target="_blank" class="thickbox open-plugin-details-modal" style="text-decoration: none;">جزئیات بیشتر و دانلود</a></li></ul>				</div>
                  				<div class="desc column-description">
                  					<p>ابزارک نمایش تصادفی دسته ها، دسته های انتخاب شده در سایدبار وبسایت همراه با نوشته های آن دسته به طور تصادفی به نمایش گذاشته می شوند. امکان تغییر قالب و رنگ بندی ابزارک با تنظیمات پیشرفته تعبیه شده وجود دارد...</p>
                  					<p class="authors"> <cite>بدست <a href="https://wpvar.com/">wpvar.com</a></cite></p>
                  				</div>
                  			</div>

                  		</div>
                  ', 'wpsh') ,
                ) ,

            )
        );
        $fields[] = array(
            'name' => 'about',
            'title' => __('درباره', 'wpsh') ,
            'icon' => 'dashicons-nametag',
            'fields' => array(

                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => __('
                  <h2>افزونه تاریخ شمسی و فارسی ساز وردپرس</h2>
                  <p>افزونه تاریخ شمسی و فارسی ساز وردپرس با این هدف برنامه نویسی شده که بتوانید تنها با فعال سازی یک افزونه رابط کاربری وردپرس را در استاندارد ترین حالت فارسی سازی کنید. برای سال ها مشکل اصلی کاربران وردپرس، عدم محیط کاربری سازگار با زبان فارسی بوده است. اکنون تنها با نصب این افزونه وردپرس خود را از همه نظر فارسی کنید. <br />
                  برخلاف نمونه های مشابه، این افزونه کمترین تغییر را در هسته وردپرس انجام می دهد و در نتیجه موجب افت سرعت و عملکرد وردپرس نمی شود و عاری از هرگونه خطا می باشد. کدهای افزونه چندمرتبه جهت افزایش عملکرد و استاندارد سازی بررسی شده اند.<br />
                  از ویژگی های دیگر افزونه، وجود همه امکانات فارسی سازی در یک افزونه است. تنها با نصب این افزونه دیگر نیازی به افزونه های دیگر برای فارسی سازی سایتتان نخواهید داشت.<br /><br />
                  <strong>برنامه نویس:</strong> علی فرجی <br />
                  <strong>وبسایت:</strong> <a href="https://wpvar.com" target="_blank">وردپرس فارسی</a>
                  </p>
                  <h2>تشکر ها</h2>
                  <ul>
                  <li>
                  با تشکر از جناب آقای <strong>سالار کامجو</strong> و وبسایت <a href="https://upgraph.ir" target="_blank">Upgraph.ir</a> جهت طراحی المان های گرافیک افزونه.
                  </li>
                  <li>
                  افزونه تاریخ شمسی و فارسی ساز وردپرس برای بهبود رابط کاربری از فونت <a href="https://github.com/rastikerdar/vazir-font" target="_blank">وزیر</a> استفاده می کند. با تشکر از جناب آقای <strong>راستی کردار</strong>.
                  </li>
                  </ul>
                  <h2>ثبت افزودنی</h2>
                  <p>"افزونه تاریخ شمسی و فارسی ساز وردپرس" از سیستم ماژولار جهت ثبت افزودنی ها استفاده می کند. اگر برنامه نویس وردپرس هستید، می توانید با برنامه نویسی افزودنی ها برای این افزونه، آن ها را با نام خود و لینک به وبسایتتان منتشر کنید.</p>
	                <p style="text-align: center;"><strong><a href="https://gist.github.com/alifaraji/4168948df1b09e9713cfadb75cad0ce3" target="_blank" style="text-decoration: none;">اطلاعات بیشتر و نمونه کد</a></strong></p>
                  ', 'wpsh') ,
                ) ,
            )
        );
        $fields[] = array(
            'name' => 'donate',
            'title' => __('حمایت', 'wpsh') ,
            'icon' => 'dashicons-heart',
            'fields' => array(

                array(
                    'type' => 'content',
                    'class' => 'class-name',
                    'content' => __('
                  <h2>حمایت از ما</h2>
                  <p>با معرفی افزونه ما در وبسایت/وبلاگ خود می توانید از ما حمایت کنید. همچنین با دنبال کردن ما در شبکه اینستاگرام از آخرین اخبار و مطالب وبسایت وردپرس فارسی و افزونه ها مطلع شوید.
 	                <br /><br /> <strong>اینستاگرام</strong> <a href="https://instagram.com/wpvar" target="_blank">@wpvar</a> <br />
                  <strong>وبسایت:</strong> <a href="https://wpvar.com" target="_blank">وردپرس فارسی</a>
                  </p>
                  <h2>حمایت مالی</h2>
                  <p>صدها ساعت زمان و انرژی صرف برنامه نویسی این افزونه شده. اگه ازش خوشت اومده می تونی برامون یه قهوه بخری! :) <br /><br />
	                <div style="text-align: center;"><span style="color:#ca4a1f;" class="exopite-sof-nav-icon dashicons-before dashicons-heart"></span><a href="https://payping.ir/@wpvar" target="_blank" rel="nofollow" style="font-size: 16px;">حمایت مالی از ما</a><br />
                  <a href="https://payping.ir/@wpvar" target="_blank" rel="nofollow"><img src="' . WPSH_URL . 'assets/img/qr.png" /></a></div>
	                </p>
                  ', 'wpsh') ,
                ) ,
            )
        );
        $options_panel = new Exopite_Simple_Options_Framework($config_submenu, $fields);
    }
}

new WPSH_Options('wpsh');