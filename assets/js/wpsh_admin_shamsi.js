/**
 * Conver admin date to shamsi
 *
 * Makes javascript generated dates shamsi.
 *
 * @since 2.0.0
 * @copyright Copyright Ali Faraji (mail.wpvar@gmail.com) | https://wpvar.com
 *
 */
jQuery(document).ready(function() {
	jQuery(window).on('load', function() {
		function toEn(number) {
			if(number === undefined) return '';
			var str = jQuery.trim(number.toString());
			if(str === '') return '';
			str = str.replace(/۰/g, '0');
			str = str.replace(/۱/g, '1');
			str = str.replace(/۲/g, '2');
			str = str.replace(/۳/g, '3');
			str = str.replace(/۴/g, '4');
			str = str.replace(/۵/g, '5');
			str = str.replace(/۶/g, '6');
			str = str.replace(/۷/g, '7');
			str = str.replace(/۸/g, '8');
			str = str.replace(/۹/g, '9');
			return str;
		}

		function toFa(number) {
			if(number === undefined) return '';
			var str = jQuery.trim(number.toString());
			if(str === '') return '';
			str = str.replace(/0/g, '۰');
			str = str.replace(/1/g, '۱');
			str = str.replace(/2/g, '۲');
			str = str.replace(/3/g, '۳');
			str = str.replace(/4/g, '۴');
			str = str.replace(/5/g, '۵');
			str = str.replace(/6/g, '۶');
			str = str.replace(/7/g, '۷');
			str = str.replace(/8/g, '۸');
			str = str.replace(/9/g, '۹');
			return str;
		}
		/**
		 * Convert gregorian to shamsi
		 *
		 * Core function to convert dates.
		 *
		 * @since 2.0.0
		 * @copyright gregorian_to_jalali Function Copyrigh JDF.SCR.IR released under the GNU/LGPL License
		 * @copyright Modified by Ali Faraji (mail.wpvar@gmail.com) | https://wpvar.com
		 *
		 */
		function gregorian_to_jalali(gy, gm, gd) {
			var g_d_m, jy, jm, jd, gy2, days;
			g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
			gy2 = (gm > 2) ? (gy + 1) : gy;
			days = 355666 + (365 * gy) + ~~((gy2 + 3) / 4) - ~~((gy2 + 99) / 100) + ~~((gy2 + 399) / 400) + gd + g_d_m[gm - 1];
			jy = -1595 + (33 * ~~(days / 12053));
			days %= 12053;
			jy += 4 * ~~(days / 1461);
			days %= 1461;
			if(days > 365) {
				jy += ~~((days - 1) / 365);
				days = (days - 1) % 365;
			}
			if(days < 186) {
				jm = 1 + ~~(days / 31);
				jd = 1 + (days % 31);
			} else {
				jm = 7 + ~~((days - 186) / 30);
				jd = 1 + ((days - 186) % 30);
			}
			if(jd.toString().length == 1) {
				var njd = '0' + jd;
			} else {
				var njd = jd;
			}
			if(jm.toString().length == 1) {
				var njm = '0' + jm;
			} else {
				var njm = jm;
			}
			return [jy, njm, njd];
		}
		var months = ['اسفند و فروردین', 'فروردین و اردیبهشت', 'اردیبهشت و خرداد', 'خرداد و تیر', 'تیر و مرداد', 'مرداد و شهریور', 'شهریور و مهر', 'مهر و آبان', 'آبان و آذر', 'آذر و دی', 'دی و بهمن', 'بهمن و اسفند'];
		if(jQuery(".iedit").length > 0) {
			jQuery('.iedit').each(function() {
				var jj = toEn(jQuery(this).find('.jj').text());
				var mm = toEn(jQuery(this).find('.mm').text());
				var aa = toEn(jQuery(this).find('.aa').text());
				var hh = toEn(jQuery(this).find('.hh').text());
				var mn = toEn(jQuery(this).find('.mn').text());
				var ss = toEn(jQuery(this).find('.ss').text());
				var postName = toEn(jQuery(this).find('.post_name').text());
				var postTitle = toEn(jQuery(this).find('.post_title').text());
				var postPass = toEn(jQuery(this).find('.post_password').text());
				var postCat = toEn(jQuery(this).find('.post_category').text());
				var postTag = toEn(jQuery(this).find('.tags_input').text());
				var postAuthor = toEn(jQuery(this).find('.post_author').text());
				jQuery(this).find('.aa').text(aa);
				jQuery(this).find('.mm').text(mm);
				jQuery(this).find('.jj').text(jj);
				jQuery(this).find('.hh').text(hh);
				jQuery(this).find('.mn').text(mn);
				jQuery(this).find('.ss').text(ss);
				jQuery(this).find('.post_name').text(postName);
				jQuery(this).find('.post_title').text(postTitle);
				jQuery(this).find('.post_password').text(postPass);
				jQuery(this).find('.post_category').text(postCat);
				jQuery(this).find('.tags_input').text(postTag);
				jQuery(this).find('.post_author').text(postAuthor);
				var jalali = gregorian_to_jalali(parseInt(aa), parseInt(mm), parseInt(jj));
				var naa = jalali[0];
				var nmm = jalali[1];
				var njj = jalali[2];
				jQuery(this).find('.aa').text(naa);
				jQuery(this).find('.mm').text(nmm);
				jQuery(this).find('.jj').text(njj);
			});
			jQuery('.colspanchange').hover(function() {
				quickEditDateHandle();
			});
		}

		function liveShamsi(a, m, j) {
			return gregorian_to_jalali(parseInt(a), parseInt(m), parseInt(j));
		}

		function quickEditDateHandle() {
			var a = jQuery('input[name="aa"]').val();
			if(parseInt(a) < 1970 || a === '') {
				return;
			}
			var m = jQuery('select[name="mm"]').val();
			var j = jQuery('input[name="jj"]').val();
			var sh = liveShamsi(a, m, j);
			jQuery('input[name="aa"]').val(sh[0]);
			jQuery('select[name="mm"]').val(sh[1]);
			jQuery('input[name="jj"]').val(sh[2]);
		}
		if(jQuery("#timestampdiv").length > 0) {
			var jj = toEn(jQuery('#hidden_jj').val());
			var mm = toEn(jQuery('#hidden_mm').val());
			var aa = toEn(jQuery('#hidden_aa').val());
			var jalali = gregorian_to_jalali(parseInt(aa), parseInt(mm), parseInt(jj));
			var naa = jalali[0];
			var nmm = jalali[1];
			var njj = jalali[2];
			jQuery('#hidden_aa').val(naa);
			jQuery('#hidden_mm').val(nmm);
			jQuery('#hidden_jj').val(njj);
			jQuery('#aa').val(naa);
			jQuery('#mm').val(nmm);
			jQuery('#jj').val(njj);
			jQuery('#mm option').each(function() {
				if(jQuery(this).val() == nmm) {
					jQuery(this).attr("selected", "selected");
				} else {
					jQuery(this).removeAttr('selected');
				}
			});
		}

		function farsiMonth(month) {
			if(month === undefined) return '';
			var str = month;
			if(str === '') return '';
			str = str.replace('اکتبر', 'دی');
			str = str.replace('نوامبر', 'بهمن');
			str = str.replace('دسامبر', 'اسفند');
			str = str.replace('ژانویه', 'فروردین');
			str = str.replace('فوریه', 'اردیبهشت');
			str = str.replace('مارس', 'خرداد');
			str = str.replace('آوریل', 'تیر');
			str = str.replace('مه', 'مرداد');
			str = str.replace('ژوئن', 'شهریور');
			str = str.replace('جولای', 'مهر');
			str = str.replace('آگوست', 'آبان');
			str = str.replace('سپتامبر', 'آذر');
			/* Old WordPress versions */
			str = str.replace('Oct', 'دی');
			str = str.replace('Nov', 'بهمن');
			str = str.replace('Dec', 'اسفند');
			str = str.replace('Jan', 'فروردین');
			str = str.replace('Feb', 'اردیبهشت');
			str = str.replace('Mar', 'خرداد');
			str = str.replace('Apr', 'تیر');
			str = str.replace('May', 'مرداد');
			str = str.replace('Jun', 'شهریور');
			str = str.replace('Jul', 'مهر');
			str = str.replace('Aug', 'آبان');
			str = str.replace('Sep', 'آذر');
			return str;
		}
		jQuery('select[name="mm"] option').each(function() {
			var monthText = jQuery(this).text();
			var replaceMonth = farsiMonth(monthText);
			jQuery(this).text(replaceMonth);
		});
		jQuery('select[name="m"] option').each(function(index) {
			if(index > 0) {
				var year = jQuery(this).val().slice(0, 4);
				var month = jQuery(this).val().slice(4, 6);
				var day = 1;
				var jalali = gregorian_to_jalali(parseInt(year), parseInt(month), parseInt(day));
				var nyear = jalali[0];
				var nmonth = jalali[1];
				var nday = jalali[2];
				jQuery(this).text(months[parseInt(nmonth)] + ' ' + nyear);
			}
		});
		if(jQuery('.column-date').length > 0) {
			jQuery('.column-date').each(function() {
				var explode = jQuery(this).text();
				explode = explode.replace('Date', '');
				explode = explode.split(' ');
				date = explode[0].split('/');
				year = toEn(date[0]);
				month = toEn(date[1]);
				day = toEn(date[2]);
				if(jQuery.isNumeric(year) && year >= 1970) {
					if(explode[3] == 'am') {
						extention = 'ق.ظ';
					} else {
						extention = 'ب.ظ';
					}
					var jalali = gregorian_to_jalali(parseInt(year), parseInt(month), parseInt(day));
					jQuery(this).text(toFa(jalali[0]) + '/' + toFa(jalali[1]) + '/' + toFa(jalali[2]) + ' در ' + explode[2] + ' ' + extention);
				}
			});
		}
		if(jQuery('#media-attachment-date-filters').length > 0) {
			jQuery('#media-attachment-date-filters option').each(function(index) {
				if(index > 0) {
					var explode = jQuery(this).text().split(' ');
					var enMonth = {};
					enMonth['ژانویه'] = 01;
					enMonth['فوریه'] = 02;
					enMonth['مارس'] = 03;
					enMonth['آوریل'] = 04;
					enMonth['مه'] = 05;
					enMonth['ژوئن'] = 06;
					enMonth['جولای'] = 07;
					enMonth['آگوست'] = 08;
					enMonth['سپتامبر'] = 09;
					enMonth['اکتبر'] = 10;
					enMonth['نوامبر'] = 11;
					enMonth['دسامبر'] = 12;
					var year = explode[1];
					var month = enMonth[explode[0]];
					var day = 1;
					var jalali = gregorian_to_jalali(parseInt(year), parseInt(month), parseInt(day));
					var nyear = jalali[0];
					var nmonth = jalali[1];
					var nday = jalali[2];
					jQuery(this).text(months[parseInt(nmonth)] + ' ' + nyear);
				}
			});
		}
		if(jQuery('.timestamp-wrap').length > 0) {
			jQuery('.timestamp-wrap').contents().filter(function() {
				return this.nodeType == 3;
			}).each(function() {
				if(jQuery('.submitbox').length > 0) {
					this.textContent = this.textContent.replace('at', '@ ');
					this.textContent = this.textContent.replace(',', '، ');
				} else {
					this.textContent = this.textContent.replace('at', 'درساعت');
					this.textContent = this.textContent.replace(',', ' سال');
				}
			});
		}
		if(jQuery('.edit-post-post-schedule').length > 0 || jQuery('.edit-post-post-schedule__toggle').text() != 'بروز شده') {
			jQuery(window).hover(function() {
				var gutenbergDate = jQuery('.edit-post-post-schedule__toggle').text().split(' ');
				if(parseInt(gutenbergDate[2]) > 1970) {
					jQuery('.edit-post-post-schedule__toggle').remove();
					jQuery('.edit-post-post-schedule .components-dropdown').html('<button class="components-button edit-post-post-schedule__toggle is-tertiary" aria-expanded="false" type="button">بروز شده</button>');
				}
			});
		}
	});
});