/**

 __          _______     _____ _    _          __  __  _____ _____
 \ \        / /  __ \   / ____| |  | |   /\   |  \/  |/ ____|_   _|
  \ \  /\  / /| |__) | | (___ | |__| |  /  \  | \  / | (___   | |
   \ \/  \/ / |  ___/   \___ \|  __  | / /\ \ | |\/| |\___ \  | |
	\  /\  /  | |       ____) | |  | |/ ____ \| |  | |____) |_| |_
     \/  \/   |_|      |_____/|_|  |_/_/    \_\_|  |_|_____/|_____|


 */

function wpshIgnoreToEn(number) {
    if (number === undefined) return "";
    var str = jQuery.trim(number.toString());
    if (str === "") return "";
    str = str.replace(/۰/g, "0");
    str = str.replace(/۱/g, "1");
    str = str.replace(/۲/g, "2");
    str = str.replace(/۳/g, "3");
    str = str.replace(/۴/g, "4");
    str = str.replace(/۵/g, "5");
    str = str.replace(/۶/g, "6");
    str = str.replace(/۷/g, "7");
    str = str.replace(/۸/g, "8");
    str = str.replace(/۹/g, "9");
    return str;
}

function wpshNumIgnore(exists, el, live) {
    if (jQuery(exists).length > 0) {
        if (typeof wpshNumbersPro === "undefined") {
            jQuery(document).ready(function () {
                jQuery(el).each(function () {
                    wpshWooStar = wpshIgnoreToEn(jQuery(this).html());
                    jQuery(this).html(wpshWooStar);
                });
            });
        } else {
            if (wpshNumbersPro["live"] == 1 && live == 1) {
                jQuery(document).on("mouseover", function () {
                    jQuery(el).each(function () {
                        wpshWooStar = wpshIgnoreToEn(jQuery(this).html());
                        jQuery(this).html(wpshWooStar);
                    });
                });
            } else {
                jQuery(document).ready(function () {
                    jQuery(el).each(function () {
                        wpshWooStar = wpshIgnoreToEn(jQuery(this).html());
                        jQuery(this).html(wpshWooStar);
                    });
                });
            }
        }
    }
}