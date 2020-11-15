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
        if (parent::option('activate-shamsi-archive', true, true))
        {
            add_filter('get_archives_link', array(
                $this,
                'archive'
            ) , 10, 7);
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

        $year = (int)filter_var($text, FILTER_SANITIZE_NUMBER_INT);
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
        $month = array(
            'دی و بهمن',
            'بهمن و اسفند',
            'اسفند و فروردین',
            'فروردین و اردیبهشت',
            'اردیبهشت و خرداد',
            'خرداد و تیر',
            'تیر و مرداد',
            'مرداد و شهریور',
            'شهریور و مهر',
            'مهر و آبان',
            'آبان و آذر',
            'آذر و دی'
        );
        $farsi_month = '';
        foreach ($patterns as $key => $value)
        {
            if (strpos($text, $value) !== false)
            {
                $farsi_month .= $key;
            }
        }
        $stamp = strtotime($year . '/' . ($farsi_month + 1) . '/1', time());
        $shamsi_year = parent::wp_shamsi(null, 'Y', $stamp);
        $text = $month[$farsi_month] . ' ' . $shamsi_year;

        /* Deprecated since 2.0.0 */
        //$text = $this->persian_num($text);
        if ('link' === $format)
        {
            $result = "\t<link rel='archives' title='" . esc_attr($text) . "' href='$url' />\n";
        }
        elseif ('option' === $format)
        {
            $selected_attr = $selected ? " selected='selected'" : '';
            $result = "\t<option value='$url'$selected_attr>$before $text $after</option>\n";
        }
        elseif ('html' === $format)
        {
            $result = "\t<li>$before<a href='$url'$aria_current>$text</a>$after</li>\n";
        }
        else
        {
            $result = "\t$before<a href='$url'$aria_current>$text</a>$after\n";
        }

        return $result;
    }

}

