jQuery(document).ready(function() {

    jQuery('.wpsh_newsletter_dismiss').click(function() {
      return confirm("آیا از عدم اشتراک در خبرنامه مطمئن هستید؟");
    });

    jQuery('.wpsh_dismiss').click(function() {
      return confirm("آیا مطمئن هستید؟");
    });

    jQuery('#wpsh_form_settings').click(function(e) {
      var link = jQuery('#wpsh_newsletter_settings').val();
      var email = jQuery('#wpsh_email_settings').val();

      window.location.replace(link + '&wpsh_newsletter_settings=' + email);
      e.preventDefault();
    });

});