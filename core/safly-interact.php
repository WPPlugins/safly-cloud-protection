<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/* Load Options */
if ($safly_options['ifhomepageoff'] == 'on' && SaFly_is_Home()) { //Homepage
	//Pass
}elseif ($safly_options['ifspidersuaoff'] == 'on' && SaFly_If_Spiders($safly_options['exclude_spiders_ua'])) {
	//Excluded Spiders UA
	//Pass
	$SaFly_If_Footer = '0';
}elseif (SaFly_Exclude_Keyword(base64_decode(SaFly_Current_URL()), $safly_options['exclude_url_keyword'])) {
	//Excluded keywords
	//Pass
}elseif ($safly_options['ifpostoff'] == 'on' && intval(wp_cache_get($safly_ip . 'postcounter', '')) <= intval($safly_options['postcounter'])) {
	if (SaFly_Isset_REQUEST_Keyword($safly_options['exclude_post_keyword'])) {
		//Excluded POST Keywords
		//Post Counter to prevent malicious submissions
		$safly_post_counter = intval(wp_cache_get($safly_ip . 'postcounter', '')) + 1;
		wp_cache_set($safly_ip . 'postcounter', $safly_post_counter, '', intval($safly_options['postcounter_expire']));
		//Pass
	}else {
		//Location
		$SaFly_If_Location = '1';
	}
}else {
	//Location
	$SaFly_If_Location = '1';
}
/* Advance Deductions */
//Current URL: $safly_current_url
//Curl to get the code
$safly_code = SaFly_Get_API_Code();
if ($safly_code == '000105') {
	//Whitelist Permanently
	wp_cache_set($safly_ip, '1', '', 0);
}elseif ($safly_code == '000103') {
	if (isset($SaFly_If_Location) && $SaFly_If_Location == '1') {
		//Location
		header("Location: {$safly_waf_server}/waf/safly-interact-waf.php?uri={$safly_current_url}&apidomain={$safly_api_domain}&salt={$saflysalt}&sign={$saflysign2}&one-off=enable");
		exit;
	}
}elseif ($safly_code == '000101') {
	//Pass
}elseif ($safly_code == '000104') {
	//Whitelist Temporarily
	wp_cache_set($safly_ip, '1', '', intval($safly_options['whitelist_expire']));
}elseif ($safly_code == '000102') {
	//Ban
	wp_cache_set($safly_ip, '0', '', intval($safly_options['ban_expire']));
	exit('SaFly Interact WAF');
}

/* Pages Adding */
if (isset($SaFly_If_Footer) && $SaFly_If_Footer == '0') {
	//No Footer Added
}else {
	add_action('wp_footer', 'SaFly_add_Footer_Frames');
}

?>