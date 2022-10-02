jQuery(document).ready(function () {
    jQuery('#wpsh_license_pro_send').on('click', function (e) {
        e.preventDefault();
        wpsh_license_pro_send();
    });
    jQuery('#wpsh-license-recheck').on('click', function (e) {
        e.preventDefault();
        wpsh_license_pro_send();
    });
});

function wpsh_license_pro_send() {
    jQuery('#wpsh_license_pro_result').hide();
    jQuery('#wpsh_license_pro_send').attr('disabled', true);
    jQuery('#wpsh_license_pro_send').text('لطفا صبر کنید...');
    jQuery('#wpsh-license-recheck').text('لطفا صبر کنید...');

    var license = jQuery('#wpsh_license_pro').val();

    if (license == '') {
        jQuery('#wpsh_license_pro_result').html('کد لایسنس را وارد نکرده‌اید.');
        jQuery('#wpsh_license_pro_result').show('fast');
        jQuery('#wpsh_license_pro_send').attr('disabled', false);
        jQuery('#wpsh_license_pro_send').text('فعال‌سازی');
        jQuery('#wpsh-license-recheck').text('[بررسی‌مجدد]');
        return false;
    }

    jQuery.ajax({
        url: wpshLicense.ajaxurl,
        type: 'POST',
        data: { action: 'wpsh_activate_license', wpsh_license: license, wpsh_license_nonce: wpshLicense.nonce },
        success: function (response) {
            jQuery('#wpsh_license_pro_send').attr('disabled', false);
            jQuery('#wpsh_license_pro_send').text('فعال‌سازی');
            jQuery('#wpsh-license-recheck').text('[بررسی‌مجدد]');
            if (parseInt(response) == 1) {
                jQuery('#wpsh_license_pro_result').css('color', '#154605');
                jQuery('.wpsh_license_settings').hide('fast');
                jQuery('#wpsh_license_pro_result').html('لایسنس با موفقیت فعال شد.');
                jQuery('#wpsh_license_pro_result').show('fast');
                window.location.replace(wpshLicense.redirect);
            }
            if (parseInt(response) == 0) {
                jQuery('#wpsh_license_pro_result').html('کد لایسنس تایید نشد.');
                jQuery('#wpsh_license_pro_result').show('fast');
            }
            if (parseInt(response) == 7) {
                jQuery('#wpsh_license_pro_result').html('تاریخ اعتبار لایسنس به اتمام رسیده است');
                jQuery('#wpsh_license_pro_result').show('fast');
            }
            if (parseInt(response) == 8) {
                jQuery('#wpsh_license_pro_result').html('امنیت و اصالت درخواست ثبت شده تایید نشد');
                jQuery('#wpsh_license_pro_result').show('fast');
            }
        },
        error: function (xhr) {
            jQuery('#wpsh_license_pro_send').attr('disabled', false);
            jQuery('#wpsh_license_pro_send').text('فعال‌سازی');
            jQuery('#wpsh-license-recheck').text('[بررسی‌مجدد]');
            jQuery('#wpsh_license_pro_result').html('خطایی در فعال‌سازی رخ داد.');
            jQuery('#wpsh_license_pro_result').show('fast');
        },
    });
}