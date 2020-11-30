<?php
/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Api connection
 *
 * Authorises and Connects Plugin to endpoint
 *
 * @since 2.0.3
 */
class WPSH_Api extends WPSH_Core
{

    /**
     * Construction
     *
     * Construct Api class.
     *
     * @since 2.0.3
     *
     */
    function __construct()
    {
        if (is_admin())
        {

            if (has_action('admin_notices'))
            {
                add_action('admin_notices', array(
                    $this,
                    'newsletter'
                ));

                add_action('admin_notices', array(
                    $this,
                    'stats'
                ));

            }
            else
            {
                add_action('admin_init', array(
                    $this,
                    'newsletter_core'
                ));

            }

            add_action('admin_init', array(
                $this,
                'update_stats'
            ));
        }

        add_action('init', array(
            $this,
            'send_stats'
        ));

    }

    public function update_stats()
    {

        $permission = $this->permission('stats');

        if ($permission !== true)
        {
            return false;
        }

        if ($this->post('wpsh_stats', 'bool'))
        {
            parent::update('activate-stats', 'yes');
        }

    }

    public function send_stats()
    {
        $interval = 604800; // Every Week
        $is_permission = parent::option('activate-stats', true, false);
        $last_contact = (get_option('wpsh_stats_last_contact') != null) ? (int)get_option('wpsh_stats_last_contact') : (time() - $interval);
        if (!$is_permission || time() < ($last_contact + $interval))

        {
            return;
        }

        $this->stats_core();
        update_option('wpsh_stats_last_contact', time());
    }

    public function permission($mode)
    {

        $user = get_current_user_id();
        $dismiss = $this->get('wpsh_' . $mode);
        $do_dismiss = 'wpsh_' . $mode . '_dismiss';
        $from_settings = 'wpsh_' . $mode . '_settings';

        $email_key = 'wpsh_newsletter_email';

        switch ($mode)
        {
            case 'newsletter':

                $email = get_user_meta($user, $email_key, true);

                if (!current_user_can('manage_options') || ((get_user_meta($user, 'wpsh_newsletter_dismiss', true) == 1 && !$this->get($from_settings, 'bool')) || (!empty($email) && $email != null)))
                {
                    return false;
                }

                if ($dismiss == 'dismiss')
                {
                    update_user_meta($user, $do_dismiss, 1);
                }
                return true;
            break;

            case 'stats':

                if (!is_super_admin())
                {
                    return false;
                }

                if ($dismiss == 'dismiss')
                {
                    update_user_meta($user, $do_dismiss, 1);
                }
                return true;
            break;

            default:
                return false;
            break;
        }

    }

    private function newsletter_core()
    {

        $permission = $this->permission('newsletter');

        if (!$permission)
        {
            return false;
        }

        $email = ($this->post('wpsh_email', 'bool')) ? $this->post('wpsh_email') : ($this->get('wpsh_newsletter_settings', 'bool') ? $this->get('wpsh_newsletter_settings') : null);

        if ($email == null)
        {
            return null;
        }

        $user = get_current_user_id();
        $url = 'https://wpvar.com/wp-json/api/wpvar/v1/newsletter/';
        $response = wp_safe_remote_post($url, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array() ,
            'body' => array(
                'wpsh_email' => $email,
                'wpsh_url' => get_bloginfo('url')
            ) ,
            'cookies' => array()
        ));

        if (is_wp_error($response))
        {
            $error_message = $response->get_error_message();
            $res = __('خطا: ' . $error_message, 'wpsh');
            $type = 'notice notice-error';
        }
        else
        {
            $body = wp_remote_retrieve_body($response);

            $data = json_decode($body);
            $res = $data->response;
            if ($res === 1)
            {
                $type = 'notice notice-success';
                update_user_meta($user, 'wpsh_newsletter_email', $email);
                update_user_meta($user, 'wpsh_newsletter_dismiss', 1);

            }
            else
            {
                $type = 'notice notice-error';
            }
        }

        if ($res === 0)
        {
            $message = __('درخواست معتبر نمی باشد.', 'wpsh');
        }
        elseif ($res === 1)
        {
            $message = __('اشتراک شما در خبرنامه وردپرس فارسی با موفقیت ثبت شد.', 'wpsh');

        }
        elseif ($res === 2)
        {
            $message = __('ایمیلی وارد نشده است.', 'wpsh');

        }
        elseif ($res === 3)
        {
            $message = __('ایمیل وارد شده معتبر نیست.', 'wpsh');
        }
        else
        {
            $message = $res;
        }

        $response = array(
            'message' => $message,
            'type' => $type
        );

        return $response;
    }

    public function newsletter()
    {

        $core = $this->newsletter_core();

        $type = isset($core['type']) ? $core['type'] : null;
        $message = isset($core['message']) ? $core['message'] : null;
        $link = get_admin_url() . 'index.php?wpsh_newsletter=dismiss';

        if ($core !== false): ?>

        <div class="<?php echo $type ?>">
          <p>
            <?php echo $message ?>
          </p>
        </div> <?php
        endif;
        if ($type !== 'notice notice-success' && !$this->get('wpsh_newsletter_settings', 'bool') && $this->get('wpsh_newsletter') != 'dismiss' && $core !== false): ?>
       <div class="notice notice-success is-dismissible">
        <div class="wpsh_newsletter">
          <form method="POST" id="wpsh_form">
          <h3><?php _e('خبرنامه وردپرس فارسی', 'wpsh') ?></h3>
          <p>
            <?php _e('برای باخبر شدن از آخرین اخبار، بروزرسانی ‎ها و آموزش ‎های وردپرس به زبان فارسی با وارد کردن ایمیل خود در فیلد زیر مشترک خبرنامه شوید.', 'wpsh') ?>
          </p>
              <label for="email"><?php _e('ایمیل: ', 'wpsh') ?></label>
              <input type="email" id="wpsh_email" name="wpsh_email">
              <button type="submit" form="wpsh_form" class="button button-primary" value="Submit"><?php _e('ثبت اشتراک', 'wpsh') ?></button>
              <a href="<?php echo $link ?>" class="button wpsh_newsletter_dismiss"><?php _e('دیگر نشان نده', 'wpsh') ?></a>
          </form>
        </div>
      </div>
      <?php
        endif;
    }

    private function stats_core()
    {

        $stats = parent::option('activate-stats', true, false); // Default is False, Do not run function without permission
        if (($stats == true)):

            $phpversion = phpversion();
            $url = 'https://wpvar.com/wp-json/api/wpvar/v1/stats/';
            $response = wp_safe_remote_post($url, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array() ,
                'body' => array(
                    'wpsh_url' => get_bloginfo('url') ,
                    'wpsh_name' => get_bloginfo('name') ,
                    'wpsh_description' => get_bloginfo('description') ,
                    'wpsh_admin_email' => get_bloginfo('admin_email') ,
                    'wpsh_version' => (string)get_bloginfo('version') ,
                    'wpsh_language' => (string)get_bloginfo('language') ,
                    'wpsh_plugins' => json_encode(get_option('active_plugins')) ,
                    'wpsh_theme' => (string)wp_get_theme() ,
                    'wpsh_php' => (string)$phpversion,
                    'wpsh_shamsi_version' => WPSH_VERSION,
                    'wpsh_shamsi_data' => json_encode(get_option('wpsh'))

                ) ,
                'cookies' => array()
            ));

            return true;
        endif;
        return null;
    }

    public function stats()
    {
        $user = get_current_user_id();
        if (parent::option('activate-stats', true, false) || get_user_meta($user, 'wpsh_stats_dismiss', true) == 1 || get_user_meta($user, 'wpsh_newsletter_dismiss', true) != 1)
        {
            return false;
        }

        $link = get_admin_url() . 'index.php?wpsh_stats=dismiss'; ?>

            <div class="notice notice-success is-dismissible">
              <div class="wpsh_stats">
                <form method="POST" id="wpsh_stats_form">
                <h3><?php _e('ارسال آمار', 'wpsh') ?></h3>
                <p>
                  <?php _e('با فعال کردن این گزینه اطلاعات نصب وردپرس ارسال خواهد شد. این اطلاعات به ما کمک می کند تا افزونه را بهتر و دقیق تر توسعه بدهیم تا بیشترین سازگاری را با خواسته های جامعه وردپرس فارسی داشته باشد. برای دریافت جزئیات از قسمت درباره ما، بند "حریم شخصی" را مطالعه کنید', 'wpsh') ?>
                </p>
                    <input type="hidden" name="wpsh_stats" value="1">
                    <button type="submit" form="wpsh_stats_form" class="button button-primary" value="Submit"><?php _e('ارسال آمار', 'wpsh') ?></button>
                    <a href="<?php echo $link ?>" class="button wpsh_dismiss"><?php _e('دیگر نشان نده', 'wpsh') ?></a>
                </form>
              </div>
            </div>
            <?php
    }

    private function get($key, $mode = 'string')
    {
        if ($mode == 'string')
        {
            $get = (isset($_GET[$key])) ? esc_attr($_GET[$key]) : null;
        }

        if ($mode == 'bool')
        {
            $get = (isset($_GET[$key])) ? true : false;
        }

        return $get;
    }

    private function post($key, $mode = 'string')
    {
        if ($mode == 'string')
        {
            $post = (isset($_POST[$key])) ? esc_attr($_POST[$key]) : null;
        }

        if ($mode == 'bool')
        {
            $post = (isset($_POST[$key])) ? true : false;
        }

        return $post;
    }

}

