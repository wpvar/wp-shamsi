<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Archive class
 *
 * Makes archive shamsi
 *
 * @since 2.0.0
 */
class WPSH_Archive extends WPSH_Core
{
    /**
     * Construction
     *
     * Construct WPSH_Archive class runs required hooks.
     *
     * @since 2.0.0
     *
     */
    function __construct()
    {
        if (!parent::can_continue()) {
            return;
        }

        if (parent::option('activate-shamsi-archive', true, true)) {
            add_filter('get_archives_link', array(
                $this,
                'archive'
            ), 10, 7);
        }
    }

    /**
     * Convert archives date
     *
     * Convert archives date to Shamsi aka Jalali ccalendar.
     *
     * @since 1.0.0
     *
     * @param string $list
     * @param string $url URL to archive.
     * @param string $text Archive text description.
     * @param string $format Optional. Can be 'link', 'option', 'html', or custom. Default 'html'.
     * @param string $before Optional. Content to prepend to the description. Default empty.
     * @param string $after Optional. Content to append to the description. Default empty.
     * @param bool   $selected Optional. Set to true if the current page is the selected archive page.
     * @return string HTML link content for archive.
     */
    public function archive($list, $url, $text, $format, $before, $after, $selected)
    {

        $text = strip_tags($text);
        $url = esc_url($url);
        $aria_current = $selected ? ' aria-current="page"' : '';
        $month = parent::get_month();
        $year = (int)filter_var($text, FILTER_SANITIZE_NUMBER_INT);

        if (get_locale() != 'fa_IR' && get_locale() != 'fa_AF') {
            $patterns = array(
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            );
        } else {
            $patterns = array(
                'ژانویه',
                'فوریه',
                'مارس',
                'آوریل',
                'می',
                'ژوئن',
                'جولای',
                'آگوست',
                'سپتامبر',
                'اکتبر',
                'نوامبر',
                'دسامبر'
            );
        }

        $month = array(
            $month[10] .  ' و '  . $month[11],
            $month[11] .  ' و '  . $month[12],
            $month[12] .  ' و '  . $month[1],
            $month[1] .  ' و '  . $month[2],
            $month[2] .  ' و '  . $month[3],
            $month[3] .  ' و '  . $month[4],
            $month[4] .  ' و '  . $month[5],
            $month[5] .  ' و '  . $month[6],
            $month[6] .  ' و '  . $month[7],
            $month[7] .  ' و '  . $month[8],
            $month[8] .  ' و '  . $month[9],
            $month[9] .  ' و '  . $month[10]
        );

        $farsi_month = '';
        foreach ($patterns as $key => $value) {
            if (strpos($text, $value) !== false) {
                $farsi_month .= $key;
            }
        }
        $stamp = strtotime($year . '/' . ((int)$farsi_month + 1) . '/1', time());
        $shamsi_year = parent::wp_shamsi(null, 'Y', $stamp);
        $text = $month[$farsi_month] . ' ' . $shamsi_year;

        /* Deprecated since 2.0.0 */
        //$text = $this->persian_num($text);
        if ('link' === $format) {
            $result = "\t<link rel='archives' title='" . esc_attr($text) . "' href='$url' />\n";
        } elseif ('option' === $format) {
            $selected_attr = $selected ? " selected='selected'" : '';
            $result = "\t<option value='$url'$selected_attr>$before $text $after</option>\n";
        } elseif ('html' === $format) {
            $result = "\t<li>$before<a href='$url'$aria_current>$text</a>$after</li>\n";
        } else {
            $result = "\t$before<a href='$url'$aria_current>$text</a>$after\n";
        }

        return $result;
    }
}
