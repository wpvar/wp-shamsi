<?php

/**
 * Plugin Name:       تاریخ شمسی و فارسی ساز وردپرس
 * Plugin URI:        https://wpvar.com/wp-shamsi
 * Description:       تبدیل تاریخ وردپرس به هجری شمسی براساس تقویم ایران و فارسی سازی رابط کاربری وردپرس
 * Version:           3.1.0
 * Requires at least: 4.0
 * Requires PHP:      5.5
 * Author:            wpvar.com
 * Author URI:        https://wpvar.com/
 * License:           GNU Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wpsh
 * @package WPSH
 */

defined('ABSPATH') or die();

define('WPSH_URL', plugin_dir_url(__FILE__));
define('WPSH_PATH', plugin_dir_path(__FILE__));
define('WPSH_BASE', plugin_basename(__FILE__));
define('WPSH_FILE', __FILE__);
define('WPSH_VERSION', '3.1.0');

/* Setting up WP shamsi */
require_once 'inc/autoload.php';
