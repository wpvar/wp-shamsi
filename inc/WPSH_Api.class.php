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
class WPSH_Api
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
        add_action('admin_notices', array(
            $this,
            'newsletter'
        ));
    }

    public function newsletter()
    {

        if (!current_user_can('manage_options'))
        {
            return;
        }

        $user_id = get_current_user_id();

        if (isset($_GET['wpsh_newsletter']) && $_GET['wpsh_newsletter'] == 'dismiss')
        {
            update_user_meta($user_id, 'wpsh_newsletter_dismiss', 1);
        }

        if (get_user_meta($user_id, 'wpsh_newsletter_dismiss', true) == 1)
        {
            return;
        }

        if (isset($_POST['wpsh_email'])): ?>
      <?php
            $url = 'https://wpvar.com/wp-json/api/wpvar/v1/newsletter/';
            $response = wp_safe_remote_post($url, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array() ,
                'body' => array(
                    'wpsh_email' => esc_attr($_POST['wpsh_email']) ,
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
                    update_user_meta($user_id, 'wpsh_newsletter_dismiss', 1);
                }
                else
                {
                    $type = 'notice notice-error';
                }
            }
?>
        <div class="<?php echo $type ?>">
          <p>
            <?php
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
            echo $message;
?>
          </p>
        </div>
        <?php
        else:

            $link = get_admin_url() . 'index.php?wpsh_newsletter=dismiss';
?>
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
}

