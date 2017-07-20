<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

//SaFly Time-lag
if (wp_cache_get('saflytimelag', '')) {
	$safly_time_lag = wp_cache_get('saflytimelag', '');
}else {
	$saflytime      = SaFly_Curl('http://api.oranme.com/developer/saflytime.php?action=synchronize');
	$safly_time_lag = intval(time()) - intval($saflytime);
	if (empty($saflytime) || abs($safly_time_lag) <= 3) {
		$safly_time_lag = 0;
	}
	wp_cache_set('saflytimelag', $safly_time_lag, '', '0');
}

$safly_api_domain     = get_option('safly_api_domain');
$safly_api_key        = get_option('safly_api_domain_key');

$safly_api_server_url = get_option('safly_api_server_url');

$safly_options_tmp    = get_option('saflyoptions');
$safly_options        = unserialize($safly_options_tmp);

//Visitor's IP
$safly_ip_getenv = $safly_options['ip_getenv'];
$safly_ip        = SaFly_IP($safly_ip_getenv);

if ($safly_api_domain && $safly_api_key) {
	//Load $saflysalt & $saflysign
	SaFly_Make_Sign();
	if ($safly_options_tmp) {
		$safly_level      = $safly_options['level'];
		$safly_waf_server = $safly_options['saflywafserver'];
	}
}else {
	if (!$safly_api_domain) {
		$safly_api_domain = '';
	}
	if (!$safly_api_key) {
		$safly_api_key = '';
	}
}

//Get Current URL
$safly_current_url = SaFly_Current_URL();

?>