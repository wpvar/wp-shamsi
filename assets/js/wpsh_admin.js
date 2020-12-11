/**
 * Wordpress admin area scripts
 *
 * WP Shamsi wordpress admin area acripts.
 *
 * @since 2.0.3
 * @copyright Copyright Ali Faraji (mail.wpvar@gmail.com) | https://wpvar.com
 *
 */
jQuery(document).ready(function () {
  function WpshValidateEmail(email, selector) {
    selector.hide();
    var regex = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,6}|[0-9]{1,3})(\]?)$/;
    var result = regex.test(email) ? true : false;
    if (!result) {
      selector.text('ایمیل وارد شده معتبر نیست');
      selector.show(250, function () {
        selector.slideDown(250);
      });
      return true;
    }
    return false;
  }
  jQuery('.wpsh_newsletter_dismiss').click(function () {
    return confirm("آیا از عدم اشتراک در خبرنامه مطمئن هستید؟");
  });
  jQuery('.wpsh_dismiss').click(function () {
    return confirm("آیا مطمئن هستید؟");
  });
  jQuery('#wpsh_form').on('submit', function (e) {
    var email = jQuery('.wpsh_newsletter input[type="email"]').val();
    var selector = jQuery('#wpsh_email_validation');
    if (WpshValidateEmail(email, selector)) {
      e.preventDefault();
    }
    else {
      this.submit();
    }
  });
  jQuery('#wpsh_form_settings').click(function (e) {
    var email = jQuery('#wpsh_email_settings').val();
    var selector = jQuery('#wpsh_email_validation_settings');
    if (WpshValidateEmail(email, selector)) {
      e.preventDefault();
    } else {
      var link = jQuery('#wpsh_newsletter_settings').val();
      var email = jQuery('#wpsh_email_settings').val();
      window.location.replace(link + '&wpsh_newsletter_settings=' + email);
      e.preventDefault();
    }
  });
  jQuery('#wpsh_email_settings').keypress(function (e) {
    var key = e.which;
    if (key == 13) {
      jQuery('#wpsh_form_settings').click();
      e.preventDefault();
      return false;
    }
  });

});
