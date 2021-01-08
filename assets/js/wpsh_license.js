jQuery(document).ready(function () {
    jQuery('#wpsh_license_pro_send').on('click', function (e) {
        e.preventDefault();
        jQuery('#wpsh_license_pro_result').hide();
        jQuery("#wpsh_license_pro_send").attr("disabled", true);
        var license = jQuery('#wpsh_license_pro').val();

        if(license == '') {
            jQuery('#wpsh_license_pro_result').html('کد لایسنس را وارد نکرده‌اید.');
            jQuery('#wpsh_license_pro_result').show('fast');
            jQuery("#wpsh_license_pro_send").attr("disabled", false);
            return false;
        }

        jQuery.ajax({
            url: wpshLicense.ajaxurl,
            type: 'POST',
            data: { action: 'wpsh_activate_license', wpsh_license: license },
            success: function (response) {
                jQuery("#wpsh_license_pro_send").attr("disabled", false);
                if(parseInt(response) == 1) {
                    jQuery('#wpsh_license_pro_result').css('color', '#154605');
                    jQuery('.wpsh_license_settings').hide('fast');
                    jQuery('#wpsh_license_pro_result').html('لایسنس با موفقیت فعال شد.');
                    jQuery('#wpsh_license_pro_result').show('fast');
                    window.location.replace(wpshLicense.redirect);
                }
                if(parseInt(response) == 0) {
                    jQuery('#wpsh_license_pro_result').html('کد لایسنس تایید نشد.');
                    jQuery('#wpsh_license_pro_result').show('fast');
                }
            },
            error: function (xhr) {
                jQuery("#wpsh_license_pro_send").attr("disabled", false);
                jQuery('#wpsh_license_pro_result').html('خطایی در فعال‌سازی رخ داد.');
                jQuery('#wpsh_license_pro_result').show('fast');
            },
        });
    });
});