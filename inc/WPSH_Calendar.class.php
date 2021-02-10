<?php

/**
 * @package WPSH
 */

defined('ABSPATH') or die();

/**
 * Shamsi Calendar widghet and block class
 *
 * Runs Shamsi Calendar widghet and block. Converts calendars to shamsi.
 *
 * @since 2.0.0
 */
class WPSH_Calendar extends WPSH_Core
{

    /**
     * Construction
     *
     * Construct Calendar class.
     *
     * @since 2.0.0
     *
     */
    function __construct()
    {

        if (!parent::can_continue()) {
            return;
        }

        if ((get_locale() == 'fa_IR' || get_locale() == 'fa_AF') && parent::option('activate-shamsi', true, true) && parent::option('activate-shamsi-calendar', true, true) && !parent::no_lang_no_shamsi()) {
            add_filter('get_calendar', array(
                $this,
                'calendar'
            ), 10, 2);
        }
    }

    /**
     * Filter original calendar
     *
     * Filters georgian calendar and create shamsi one based on that.
     *
     * @since 2.0.0
     *
     * @param mixed $calendar_output HTML input.
     * @return mixed HTML output.
     */
    public function calendar($calendar_output)
    {
        $calendar_output = $this->get_jcalendar();
        return $calendar_output;
    }

    /**
     * Get shamsi class from parent
     *
     * Function to make accessing parent class easy.
     *
     * @since 2.0.0
     *
     * @param mixed $date Georgian dates.
     * @param string $format Format of date.
     * @param int $timestamp Timestamp.
     * @return mixed Shamsi dates.
     */
    public function shamsi($date = null, $format = null, $timestamp = null)
    {

        if ($timestamp != null) {
            $result = parent::wp_shamsi(null, $format, $timestamp);
        } else {
            $result = parent::wp_shamsi($date, $format, null);
        }

        return $result;
    }
    /**
     * Create Shamsi calendar
     *
     * Created Shamsi calendar based on georgian one.
     *
     * @since 2.0.0
     *
     * @param bool $initial Whether to use initial calendar names. Default true.
     * @param bool $echo    Whether to display the calendar output. Default true.
     * @return void|string Void if `$echo` argument is true, calendar HTML if `$echo` is false.
     */
    function get_jcalendar($initial = true, $echo = true)
    {
        global $wpdb, $m, $monthnum, $year, $wp_locale, $posts, $previous, $next;

        if (!in_the_loop()) {
            $echo = false;
        }

        $key = md5($m . $monthnum . $year);
        $cache = wp_cache_get('get_jcalendar', 'calendar');

        if ($cache && is_array($cache) && isset($cache[$key])) {
            $output = apply_filters('get_jcalendar', $cache[$key]);
            if ($echo) {
                echo $output;
                return;
            }

            return $output;
        }

        if (!is_array($cache)) {
            $cache = array();
        }

        if (!$posts) {
            $gotsome = $wpdb->get_var("SELECT 1 as test FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' LIMIT 1");
            if (!$gotsome) {
                $cache[$key] = '';
                wp_cache_set('get_jcalendar', $cache, 'calendar');
                return;
            }
        }

        if (isset($_GET['w'])) {
            $w = (int)$_GET['w'];
        }

        $week_begins = (int)get_option('start_of_week');

        if (!empty($monthnum) && !empty($year)) {
            $thismonth = zeroise(intval($monthnum), 2);
            $thisyear = (int)$year;
        } elseif (!empty($w)) {
            $thisyear = (int)substr($m, 0, 4);
            $d = (($w - 1) * 7) + 6;
            $thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')");
        } elseif (!empty($m)) {
            $thisyear = (int)substr($m, 0, 4);
            if (strlen($m) < 6) {
                $thismonth = '01';
            } else {
                $thismonth = zeroise((int)substr($m, 4, 2), 2);
            }
        } else {
            $thisyear = current_time('Y');
            $thismonth = current_time('m');
        }

        $unixmonth = mktime(0, 0, 0, $thismonth, 1, $thisyear);

        $last_day = gmdate('t', $unixmonth);

        $previous = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
    		FROM $wpdb->posts
    		WHERE post_date < '$thisyear-$thismonth-01 00:00:00'
    		AND post_type = 'post' AND post_status = 'publish'
    			ORDER BY post_date DESC
    			LIMIT 1");

        $next = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
    		FROM $wpdb->posts
    		WHERE post_date > '$thisyear-$thismonth-{$last_day} 23:59:58'
    		AND post_type = 'post' AND post_status = 'publish'
    			ORDER BY post_date ASC
    			LIMIT 1");

        $calendar_caption = _x('%1$s %2$s', 'calendar caption');

        $mstamp = mktime(0, 0, 0, $thismonth, 1, $thisyear);
        $famonth = $this->shamsi(null, 'F', $mstamp);

        $calendar_output = '<table id="wp-calendar" class="wp-calendar-table">
    	<caption>' . sprintf($calendar_caption, $famonth, $this->shamsi(gmdate('Y', $unixmonth), 'Y')) . '</caption>
    	<thead>
    	<tr>';

        $myweek = array();

        for ($wdcount = 0; $wdcount <= 6; $wdcount++) {
            $myweek[] = $wp_locale->get_weekday(($wdcount + $week_begins) % 7);
        }

        foreach ($myweek as $wd) {
            $day_name = $initial ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
            $wd = esc_attr($wd);
            $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
        }

        $calendar_output .= '
    	</tr>
    	</thead>
    	<tbody>
    	<tr>';

        $daywithpost = array();

        $dayswithposts = $wpdb->get_results("SELECT DISTINCT DAYOFMONTH(post_date)
    		FROM $wpdb->posts WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:01'
    		AND post_type = 'post' AND post_status = 'publish'
    		AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'", ARRAY_N);

        if ($dayswithposts) {
            foreach ((array)$dayswithposts as $daywith) {
                $daywithpost[] = (int)$daywith[0];
            }
        }

        $pad = calendar_week_mod(gmdate('w', $unixmonth) - $week_begins);
        if (0 != $pad) {
            $calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr($pad) . '" class="pad">&nbsp;</td>';
        }

        $monthnum = ($this->shamsi(null, 'm', $mstamp) < 7) ? 31 : 30;

        $newrow = false;
        $daysinmonth = $monthnum;

        for ($day = 1; $day <= $daysinmonth; ++$day) {

            $jstamp = mktime(0, 0, 0, $thismonth, $day, $thisyear);

            $faday = $this->shamsi(null, 'd', $jstamp);

            if (isset($newrow) && $newrow) {
                $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
            }
            $newrow = false;

            if (current_time('j') == $day && current_time('m') == $thismonth && current_time('Y') == $thisyear) {
                $calendar_output .= '<td id="today">';
            } else {
                $calendar_output .= '<td>';
            }

            if (in_array($day, $daywithpost, true)) {
                $date_format = gmdate(_x('F j, Y', 'daily archives date format'), strtotime("{$thisyear}-{$thismonth}-{$day}"));

                $label = sprintf(__('Posts published on %s'), $this->shamsi($date_format, _x('F j, Y', 'daily archives date format')));
                $calendar_output .= sprintf('<a href="%s" aria-label="%s">%s</a>', get_day_link($thisyear, $thismonth, $day), esc_attr($label), $faday);
            } else {
                $calendar_output .= $faday;
            }

            $calendar_output .= '</td>';

            if (6 == calendar_week_mod(gmdate('w', mktime(0, 0, 0, $thismonth, $day, $thisyear)) - $week_begins)) {
                $newrow = true;
            }
        }

        $pad = 7 - calendar_week_mod(gmdate('w', mktime(0, 0, 0, $thismonth, $day, $thisyear)) - $week_begins);
        if (0 != $pad && 7 != $pad) {
            $calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr($pad) . '">&nbsp;</td>';
        }

        $calendar_output .= "\n\t</tr>\n\t</tbody>";

        $calendar_output .= "\n\t</table>";

        $calendar_output .= '<nav aria-label="' . __('Previous and next months') . '" class="wp-calendar-nav">';

        if ($previous) {
            $calendar_output .= "\n\t\t" . '<span class="wp-calendar-nav-prev"><a href="' . get_month_link($previous->year, $previous->month) . '">&laquo; ' . $this->shamsi(null, 'F', mktime(0, 0, 0, $thismonth - 1, 1, $thisyear)) . '</a></span>';
        } else {
            $calendar_output .= "\n\t\t" . '<span class="wp-calendar-nav-prev">&nbsp;</span>';
        }

        $calendar_output .= "\n\t\t" . '<span class="pad">&nbsp;</span>';

        if ($next) {
            $calendar_output .= "\n\t\t" . '<span class="wp-calendar-nav-next"><a href="' . get_month_link($next->year, $next->month) . '">' . $this->shamsi(null, 'F', mktime(0, 0, 0, $thismonth + 1, 1, $thisyear)) . ' &raquo;</a></span>';
        } else {
            $calendar_output .= "\n\t\t" . '<span class="wp-calendar-nav-next">&nbsp;</span>';
        }

        $calendar_output .= '
    	</nav>';

        $cache[$key] = $calendar_output;
        wp_cache_set('get_jcalendar', $cache, 'calendar');
        if ($echo) {
            /**
             * Filters the HTML Shamsi calendar output.
             *
             * @since 2.0.0
             *
             * @param string $calendar_output HTML output of the calendar.
             */
            echo apply_filters('get_jcalendar', $calendar_output);
            return;
        }
        return apply_filters('get_jcalendar', $calendar_output);
    }
}
