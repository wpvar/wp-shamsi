/**
 * Conver admin date to shamsi
 *
 * Makes javascript generated dates shamsi.
 *
 * @since 2.0.0
 * @copyright Copyright Ali Faraji (mail.wpvar@gmail.com) | https://wpvar.com
 *
 */
jQuery(document).ready(function () {

  var months = [
    listFarsiMonth[12] + ' و ' + listFarsiMonth[1],
    listFarsiMonth[1] + ' و ' + listFarsiMonth[2],
    listFarsiMonth[2] + ' و ' + listFarsiMonth[3],
    listFarsiMonth[3] + ' و ' + listFarsiMonth[4],
    listFarsiMonth[4] + ' و ' + listFarsiMonth[5],
    listFarsiMonth[5] + ' و ' + listFarsiMonth[6],
    listFarsiMonth[6] + ' و ' + listFarsiMonth[7],
    listFarsiMonth[7] + ' و ' + listFarsiMonth[8],
    listFarsiMonth[8] + ' و ' + listFarsiMonth[9],
    listFarsiMonth[9] + ' و ' + listFarsiMonth[10],
    listFarsiMonth[10] + ' و ' + listFarsiMonth[11],
    listFarsiMonth[11] + ' و ' + listFarsiMonth[12]
  ];

  if (jQuery(".iedit").length > 0) {
    jQuery('.iedit').each(function () {
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
    jQuery('.colspanchange').on('mouseenter', function () {
      quickEditDateHandle();
    });
  }

  function liveShamsi(a, m, j) {
    return gregorian_to_jalali(parseInt(a), parseInt(m), parseInt(j));
  }

  function quickEditDateHandle() {
    var a = jQuery('input[name="aa"]').val();
    if (parseInt(a) < 1970 || a === '') {
      return;
    }
    var m = jQuery('select[name="mm"]').val();
    var j = jQuery('input[name="jj"]').val();
    var sh = liveShamsi(a, m, j);
    jQuery('input[name="aa"]').val(sh[0]);
    jQuery('select[name="mm"]').val(sh[1]);
    jQuery('input[name="jj"]').val(sh[2]);
  }
  if (jQuery("#timestampdiv").length > 0) {
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
    jQuery('#mm option').each(function () {
      if (jQuery(this).val() == nmm) {
        jQuery(this).attr("selected", "selected");
      } else {
        jQuery(this).removeAttr('selected');
      }
    });
  }

  jQuery('select[name="mm"] option').each(function () {
    var monthText = jQuery(this).text();
    var replaceMonth = farsiMonth(monthText);
    jQuery(this).text(replaceMonth);
  });
  jQuery('select[name="m"] option').each(function (index) {
    if (index > 0) {
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
  if (jQuery('.column-date').length > 0) {
    jQuery('.column-date').each(function () {
      var explode = jQuery(this).text();
      explode = explode.replace('Date', '');
      explode = explode.split(' ');
      date = explode[0].split('/');
      year = toEn(date[0]);
      month = toEn(date[1]);
      day = toEn(date[2]);
      if (jQuery.isNumeric(year) && year >= 1970) {
        if (explode[3] == 'am') {
          extention = 'ق.ظ';
        } else {
          extention = 'ب.ظ';
        }
        var jalali = gregorian_to_jalali(parseInt(year), parseInt(month), parseInt(day));
        jQuery(this).text(toFa(jalali[0]) + '/' + toFa(jalali[1]) + '/' + toFa(jalali[2]) + ' در ' + explode[2] + ' ' + extention);
      }
    });
  }
  if (jQuery('.column-expiry_date').length > 0) {
    jQuery('.expiry_date').each(function () {
      var explode = jQuery(this).text();
      explode = explode.replace('،', '');
      explode = explode.replace(',', '');
      explode = explode.split(' ');
      jQuery(this).text(explode[1] + ' ' + farsiMonth(explode[0]) + ' ' + ' ' + explode[2]);
    });
  }
  if (jQuery('#media-attachment-date-filters').length > 0) {
    jQuery('#media-attachment-date-filters option').each(function (index) {
      if (index > 0) {
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
  if (jQuery('.timestamp-wrap').length > 0) {
    jQuery('.save-timestamp').on('click', function () {
      wpshUpdateStamp();
    });
    jQuery('#post').on('submit', function () {
      jQuery('#timestamp').html('<span>درحال به‌روزرسانی...</span>');
    });
    wpshTimestampTasks(false);
    jQuery('#timestampdiv input').on('change', function () {
      wpshTimestampTasks(true);
    });
    jQuery('.timestamp-wrap').contents().filter(function () {
      return this.nodeType == 3;
    }).each(function () {
      if (jQuery('.submitbox').length > 0) {
        this.textContent = this.textContent.replace('at', '@ ');
        this.textContent = this.textContent.replace(',', '، ');
      } else {
        this.textContent = this.textContent.replace('at', 'درساعت');
        this.textContent = this.textContent.replace(',', ' سال');
      }
    });
  }
  jQuery('.edit-post-post-schedule').html('');
});

function toEn(number) {
  if (number === undefined) return '';
  var str = jQuery.trim(number.toString());
  if (str === '') return '';
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
  if (number === undefined) return '';
  var str = jQuery.trim(number.toString());
  if (str === '') return '';
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

function wpshTimestampTasks(change) {
  jQuery('input[name="jj"]').attr('id', 'njj');
  jQuery('select[name="mm"]').attr('id', 'nmm');
  jQuery('input[name="aa"]').attr('id', 'naa');
  jQuery('input[name="jj"]').attr('name', 'njj');
  jQuery('select[name="mm"]').attr('name', 'nmm');
  jQuery('input[name="aa"]').attr('name', 'naa');

  jQuery('input[name="hidden_jj"]').attr('id', 'hidden_njj');
  jQuery('input[name="hidden_mm"]').attr('id', 'hidden_nmm');
  jQuery('input[name="hidden_aa"]').attr('id', 'hidden_naa');
  jQuery('input[name="hidden_jj"]').attr('name', 'hidden_njj');
  jQuery('input[name="hidden_mm"]').attr('name', 'hidden_nmm');
  jQuery('input[name="hidden_aa"]').attr('name', 'hidden_naa');

  jQuery('input[class="aa"]').attr('name', 'aa');
  jQuery('input[class="mm"]').attr('name', 'mm');
  jQuery('input[class="jj"]').attr('name', 'jj');
  jQuery('input[class="hidden_aa"]').attr('name', 'hidden_aa');
  jQuery('input[class="hidden_mm"]').attr('name', 'hidden_mm');
  jQuery('input[class="hidden_jj"]').attr('name', 'hidden_jj');

  jQuery('input[class="aa"]').attr('id', 'aa');
  jQuery('input[class="mm"]').attr('id', 'mm');
  jQuery('input[class="jj"]').attr('id', 'jj');
  jQuery('input[class="hidden_aa"]').attr('id', 'hidden_aa');
  jQuery('input[class="hidden_mm"]').attr('id', 'hidden_mm');
  jQuery('input[class="hidden_jj"]').attr('id', 'hidden_jj');

  var to_gregorian = jalali_to_gregorian(parseInt(jQuery('#naa').val()), parseInt(jQuery('#nmm').val()), parseInt(jQuery('#njj').val()));
  var to_gregorian_hidden = jalali_to_gregorian(parseInt(jQuery('#hidden_naa').val()), parseInt(jQuery('#hidden_nmm').val()), parseInt(jQuery('#hidden_njj').val()));
  if (change) {
    jQuery('#jj').val(to_gregorian[2]);
    jQuery('#hidden_jj').val(to_gregorian_hidden[2]);
    jQuery('#mm').val(to_gregorian[1]);
    jQuery('#hidden_mm').val(to_gregorian_hidden[1]);
    jQuery('#aa').val(to_gregorian[0]);
    jQuery('#hidden_aa').val(to_gregorian_hidden[0]);
  } else {
    jQuery('.timestamp-wrap').append('<input type="hidden" id="jj" name="jj" class="jj" value="' + to_gregorian[2] + '">');
    jQuery('.timestamp-wrap').append('<input type="hidden" id="hidden_jj" name="hidden_jj" class="hidden_jj" value="' + to_gregorian_hidden[2] + '">');
    jQuery('.timestamp-wrap').append('<input type="hidden" id="mm" name="mm" class="mm" value="' + to_gregorian[1] + '">');
    jQuery('.timestamp-wrap').append('<input type="hidden" id="hidden_mm" name="hidden_mm" class="hidden_mm" value="' + to_gregorian_hidden[1] + '">');
    jQuery('.timestamp-wrap').append('<input type="hidden" id="aa" name="aa" class="aa" value="' + to_gregorian[0] + '">');
    jQuery('.timestamp-wrap').append('<input type="hidden" id="hidden_aa" name="hidden_aa" class="hidden_aa" value="' + to_gregorian_hidden[0] + '">');
  }
}

function wpshUpdateStamp() {
  var aastamp = jQuery('#naa').val();
  var mmstamp = farsiMonth(jQuery('#nmm').find(':selected').data('text'));
  var jjstamp = jQuery('#njj').val();
  var hhstamp = jQuery('#hh').val();
  var mnstamp = jQuery('#mn').val();

  jQuery('#timestamp b').html(jjstamp + ' ' + mmstamp + ' ' + aastamp + ' ساعت ' + hhstamp + ':' + mnstamp);
}

function farsiMonth(month) {
  if (month === undefined) return '';
  var str = month;
  if (str === '') return '';
  str = str.replace('اکتبر', listFarsiMonth[10]);
  str = str.replace('نوامبر', listFarsiMonth[11]);
  str = str.replace('دسامبر', listFarsiMonth[12]);
  str = str.replace('ژانویه', listFarsiMonth[1]);
  str = str.replace('فوریه', listFarsiMonth[2]);
  str = str.replace('مارس', listFarsiMonth[3]);
  str = str.replace('آوریل', listFarsiMonth[4]);
  str = str.replace('مه', listFarsiMonth[5]);
  str = str.replace('ژوئن', listFarsiMonth[6]);
  str = str.replace('جولای', listFarsiMonth[7]);
  str = str.replace('آگوست', listFarsiMonth[8]);
  str = str.replace('سپتامبر', listFarsiMonth[9]);
  /* Old WordPress versions */
  str = str.replace('Oct', listFarsiMonth[10]);
  str = str.replace('Nov', listFarsiMonth[11]);
  str = str.replace('Dec', listFarsiMonth[12]);
  str = str.replace('Jan', listFarsiMonth[1]);
  str = str.replace('Feb', listFarsiMonth[2]);
  str = str.replace('Mar', listFarsiMonth[3]);
  str = str.replace('Apr', listFarsiMonth[4]);
  str = str.replace('May', listFarsiMonth[5]);
  str = str.replace('Jun', listFarsiMonth[6]);
  str = str.replace('Jul', listFarsiMonth[7]);
  str = str.replace('Aug', listFarsiMonth[8]);
  str = str.replace('Sep', listFarsiMonth[9]);
  return str;
}

/**
 * Convert gregorian to shamsi
 *
 * Core function to convert dates.
 *
 * @since 2.0.0
 * @copyright gregorian_to_jalali and jalali_to_gregorian Function Copyrigh JDF.SCR.IR released under the GNU/LGPL License
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
  if (days > 365) {
    jy += ~~((days - 1) / 365);
    days = (days - 1) % 365;
  }
  if (days < 186) {
    jm = 1 + ~~(days / 31);
    jd = 1 + (days % 31);
  } else {
    jm = 7 + ~~((days - 186) / 30);
    jd = 1 + ((days - 186) % 30);
  }
  if (jd.toString().length == 1) {
    var njd = '0' + jd;
  } else {
    var njd = jd;
  }
  if (jm.toString().length == 1) {
    var njm = '0' + jm;
  } else {
    var njm = jm;
  }
  return [jy, njm, njd];
}

function jalali_to_gregorian(jy, jm, jd) {
  var sal_a, gy, gm, gd, days;
  jy += 1595;
  days = -355668 + (365 * jy) + (~~(jy / 33) * 8) + ~~(((jy % 33) + 3) / 4) + jd + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
  gy = 400 * ~~(days / 146097);
  days %= 146097;
  if (days > 36524) {
    gy += 100 * ~~(--days / 36524);
    days %= 36524;
    if (days >= 365) days++;
  }
  gy += 4 * ~~(days / 1461);
  days %= 1461;
  if (days > 365) {
    gy += ~~((days - 1) / 365);
    days = (days - 1) % 365;
  }
  gd = days + 1;
  sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
  for (gm = 0; gm < 13 && gd > sal_a[gm]; gm++) gd -= sal_a[gm];
  if (gd.toString().length == 1) {
    var ngd = '0' + gd;
  } else {
    var ngd = gd;
  }
  if (gm.toString().length == 1) {
    var ngm = '0' + gm;
  } else {
    var ngm = gm;
  }
  return [gy, ngm, ngd];
}

