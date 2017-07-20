<?php
/*
Plugin Name: SaFly Cloud Protection
Plugin URI: https://www.safly.org
Description: A secure plug-in which helps you be away from being collected, brute force attack and so on, Based on SaFly Cloud API, Designed by SaFly.ORG™. 多方位保护您的 WordPress，基于 SaFly Interact WAF™ 创新技术。
Version: 1.7.3
Author: SaFly.ORG™
Author URI: https://www.safly.org
License: GPLv2 or later
Copyright: Designed by SaFly.ORG™
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 3
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2011-2016 SaFly, Inc.
*/

//ini_set('display_errors', 'On');

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

//SaFly Cloud Protection - Processing time
$safly_processing_t1 = microtime(TRUE);

define('SaFly_VERSION', '1.7.3');
define('SaFly_DIR', plugin_dir_path(__FILE__));
define('SaFly_URL', plugin_dir_url(__FILE__));
define('SaFly_INC', 'safly', TRUE);

//require_once(ABSPATH . 'wp-includes/pluggable.php');
require_once(SaFly_DIR . 'wrapper.php');
require_once(SaFly_DIR . 'variables.php');

/* Activation & Deactivation */
register_activation_hook(__FILE__, 'SaFly_Activated');
register_deactivation_hook(__FILE__, 'SaFly_Deactivated');

/* SaFly Interact WAF */
add_action('plugins_loaded', 'SaFly_Interact_WAF', '1');

/* SaFly Avatar */
if (get_option('safly_if_avatar')) {
	if (get_option('safly_if_avatar') == 'on') {
		require_once(SaFly_DIR . 'core/safly-avatar.php');
	}
}

/* SaFly Request Test */
if (get_option('safly_if_request_test')) {
	if (get_option('safly_if_request_test') == 'on') {
		$request_test_level   = $safly_options['request_test_level'];
		$request_test_trigger = $safly_options['request_test_trigger'];
		require_once(SaFly_DIR . 'core/safly-request-test.php');
	}
}

/* SaFly Options */
require_once(SaFly_DIR . 'options.php');

/* SaFly Cloud Protection - Processing time */
$safly_processing_t2   = microtime(TRUE);
$safly_processing_time = round($safly_processing_t2 - $safly_processing_t1, 3);
add_action('wp_footer', 'SaFly_add_Footer_Processing_Time');

?>