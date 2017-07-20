<?php

function SaFly_Activated()
{
	update_option('safly_api_server_url', 'http://api.oranme.com');

	$safly_options                         = array();
	//SaFly Global Setting
	$safly_options['ip_getenv']            = 'HTTP_CF_CONNECTING_IP,HTTP_CDN_REAL_IP,HTTP_X_FORWARDED_FOR,HTTP_X_REAL_IP,HTTP_CLIENT_IP,REMOTE_ADDR';
	//SaFly Interact WAF
	$safly_options['ifinteracton']         = 'on';
	$safly_options['ifwhitelistuser']      = 'on';
	$safly_options['ifhomepageoff']        = 'on';
	$safly_options['ifpostoff']            = 'on';
	$safly_options['ifspidersuaoff']       = 'on';	
	$safly_options['level']                = 'low';
	$safly_options['saflywafserver']       = 'https://ips.waf.name';
	$safly_options['whitelist_expire']     = '900';
	$safly_options['ban_expire']           = '900';
	$safly_options['postcounter']          = '6';
	$safly_options['postcounter_expire']   = '180';
	$safly_options['exclude_post_keyword'] = 'comment_parent,log,pwd';	
	$safly_options['exclude_url_keyword']  = '';	
	$safly_options['exclude_spiders_ua']   = 'Baiduspider,Googlebot,HaoSouSpider,360Spider,Sosospider,Yahoo,YodaoBot,YoudaoBot,Sogou,bingbot,ia_archiver';
	//SaFly Request Test
	$safly_options['request_test_level']   = 'low';
	$safly_options['request_test_trigger'] = 'comment_parent,log,pwd';

	$safly_serialize                       = serialize($safly_options);
	update_option('saflyoptions', $safly_serialize);

	update_option('safly_if_request_test', 'on');
	update_option('safly_if_avatar', 'off');
}

function SaFly_Deactivated()
{
	wp_cache_flush();
	//delete_option('safly_api_domain');
	//delete_option('safly_api_domain_key');
	//delete_option('safly_api_server_url');
	//delete_option('saflyoptions');
	//delete_option('safly_if_request_test');
	//delete_option('safly_if_avatar');
}

function SaFly_Interact_WAF()
{
	global $safly_api_domain, $safly_api_key, $safly_api_server_url;
	global $safly_ip, $saflysalt, $saflysign, $saflysign2, $safly_code, $safly_code_time;
	global $safly_options, $safly_options_tmp, $safly_level, $safly_waf_server, $safly_current_url;
	global $safly_processing_t1;

	if ($safly_api_domain && $safly_api_key && $safly_options_tmp) {
		//Load $saflysalt & $saflysign
		//Load SaFly Interact WAF Setting
		//Options: White-list User
		if (function_exists(is_user_logged_in)) {
			if ($safly_options['ifwhitelistuser'] == 'on' && is_user_logged_in()) {
				//White-list User
				wp_cache_set($safly_ip, '1', '', 1800);
			}
		}
		//Options: If interacton is on
		if ($safly_options['ifinteracton'] == 'on') {
			SaFly_Interact_WAF_Start();
		}
	}

}

function SaFly_Interact_WAF_Start()
{
	global $safly_api_domain, $safly_api_key, $safly_api_server_url;
	global $safly_ip, $saflysalt, $saflysign, $saflysign2, $safly_code, $safly_code_time;
	global $safly_options, $safly_options_tmp, $safly_level, $safly_waf_server, $safly_current_url;
	global $safly_processing_t1;

	if (wp_cache_get($safly_ip, '')) {
		$safly_ck = wp_cache_get($safly_ip, '');
		if ($safly_ck == '0') {
			//Ban
			exit('SaFly Interact WAF - You have been banned.');
		}elseif ($safly_ck == '1') {
			//Pass
		}
	}else {
		require_once(SaFly_DIR . 'core/safly-interact.php');
	}
}

function SaFly_IP($safly_ip_getenv)
{
	$safly_ip_getenv = explode(',', $safly_ip_getenv);
	foreach ($safly_ip_getenv as $value) {
		if (getenv($value)) {
			$safly_ip    = getenv($value);
			$ip_if_comma = strstr($safly_ip, ',');
			if ($ip_if_comma) {
				$ip_if_comma = explode(',', $ip_if_comma);
				$safly_ip    = $ip_if_comma['0'];
			}
			return $safly_ip;
		}
	}
	if (empty($safly_ip)) {
		return 'SaFly Unknown IP.';
	}
}

function SaFly_Make_Sign()
{
	global $safly_api_domain, $safly_api_key, $safly_time_lag;
	global $saflysalt, $saflysign, $saflysign2;
	//SaFly Cloud API Sign 2016-03-27 - TIME AUTH
	$time       = time() - $safly_time_lag;
	$subtime    = intval(substr($time, 0, 8));
	$saltstr    = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
	$salt       = substr($saltstr, 0, 6);
	$sign       = md5($safly_api_domain . $safly_api_key . $subtime . $salt, FALSE);
	$sign2      = md5($safly_api_domain . $safly_api_key . $subtime . $salt . 'one-off', FALSE);
	$saflysalt  = $salt;
	$saflysign  = $sign;
	$saflysign2 = $sign2;
}

function SaFly_Curl($url)
{
	$safly_ch = curl_init();
	curl_setopt($safly_ch, CURLOPT_URL, $url);
	curl_setopt($safly_ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($safly_ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($safly_ch, CURLOPT_HEADER, 0);
	$safly_output = curl_exec($safly_ch);
	curl_close($safly_ch);
	return $safly_output;
}

function SaFly_Curl_Post($url, $array)
{
	$safly_ch = curl_init();
	curl_setopt($safly_ch, CURLOPT_URL, $url);
	curl_setopt($safly_ch, CURLOPT_SSL_VERIFYPEER, FALSE); //no-check-certificate
	curl_setopt($safly_ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($safly_ch, CURLOPT_POST, TRUE);
	curl_setopt($safly_ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($safly_ch, CURLOPT_POSTFIELDS, $array);
	$safly_output = curl_exec($safly_ch);
	curl_close($safly_ch);
	return $safly_output;
}

function SaFly_Current_URL()
{
	if (!SaFly_is_SSL()) {
		$safly_current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}else {
		$safly_current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
	$safly_current_url = base64_encode($safly_current_url);
	return $safly_current_url;
}

function SaFly_is_Home()
{
	$SaFly_Current_URL = base64_decode(SaFly_Current_URL());
	if (defined('WP_SITEURL') && $SaFly_Current_URL = WP_SITEURL) {
		return TRUE;
	}
	if (defined('WP_HOME') && $SaFly_Current_URL = WP_HOME) {
		return TRUE;
	}
	if ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/index.php') {
		return TRUE;
	}
	return FALSE;
}

function SaFly_Exclude_Keyword($str, $option)
{
	if (!empty($option)) {
		$option = explode(',', $option);
		foreach ($option as $value) {
			$option_tmp = strstr($str, $value);
			if ($option_tmp) {
				return TRUE;
			}
		}
	}
	return FALSE;
}

function SaFly_Isset_REQUEST_Keyword($option)
{
	if (!empty($option)) {
		$option = explode(',', $option);
		foreach ($option as $value) {
			if (isset($_REQUEST[$value])) {
				return TRUE;
			}
		}
	}
	return FALSE;
}

function SaFly_is_SSL()
{
	if (isset($_SERVER['HTTPS'])) {
		if ('on' == strtolower($_SERVER['HTTPS'])) {
			return TRUE;
		}
		if ('1' == $_SERVER['HTTPS']) {
			return TRUE;
		}
	}elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
		return TRUE;
	}
	return FALSE;
}

function SaFly_Get_API_Code($debug = 'false')
{
	global $safly_api_domain, $saflysalt, $saflysign;
	global $safly_api_server_url, $safly_ip, $safly_level, $safly_current_url;
	$safly_interact_api = "{$safly_api_server_url}/api/saflyinteract/?ip={$safly_ip}&level={$safly_level}&uri={$safly_current_url}&debug={$debug}&apidomain={$safly_api_domain}&salt={$saflysalt}&sign={$saflysign}&verification-type=time";
	$safly_output_tmp   = SaFly_Curl($safly_interact_api);
	$safly_output       = json_decode($safly_output_tmp);
	return $safly_output->code;
}

function SaFly_add_Footer_Frames()
{
	global $safly_api_domain, $saflysalt, $saflysign2;
	global $safly_waf_server, $safly_current_url;
	echo "<iframe src='{$safly_waf_server}/waf/saflyframes.php?uri={$safly_current_url}&apidomain={$safly_api_domain}&salt={$saflysalt}&sign={$saflysign2}&one-off=enable&verification-type=time' style='display:none;'></iframe>";
}

function SaFly_add_Footer_Processing_Time()
{
	global $safly_processing_time;
	echo "<!-- SaFly Cloud Protection - Processing time: {$safly_processing_time} seconds. -->";
}

function SaFly_If_Spiders($spiders_ua)
{
	if(empty($_SERVER['HTTP_USER_AGENT'])) {
		$user_agent = 'UNDEFINED_AGENT';
	}else {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
	}
	//$spiders_ua = 'Baiduspider,Googlebot,HaoSouSpider,360Spider,Sosospider,Yahoo,YodaoBot,YoudaoBot,Sogou,bingbot,ia_archiver';
	$user_agent = strtolower($user_agent);
	$spiders_ua = strtolower($spiders_ua);
	//Judge
	$spiders_ua = explode(',', $spiders_ua);
	foreach ($spiders_ua as $value) {
		$spiders_ua_tmp = strstr($user_agent, $value);
		if ($spiders_ua_tmp) {
			return TRUE;
		}
	}
	return FALSE;
}

function SaFly_API_Key_VALIDATE($api_key)
{
	if (!empty($api_key) && !preg_match('/^(\w){32}$/', $api_key)) {
		wp_die('Invalid API KEY.', 'SaFly Cloud Protection');
	}
}

function SaFly_Options_If_API_Server($str, $notice = 'Invalid API Server.')
{
	if (!empty($str)) {
		if (function_exists(esc_url)) {
			$str = esc_url($str);
		}
		$tempu = parse_url($str);
		$str   = $tempu['host'];
		$api_server_tmp  = strstr($str, 'oranme.com');
		$api_server_tmp2 = strstr($str, 'waf.name');
		if (!$api_server_tmp && !$api_server_tmp2) {
			wp_die($notice, 'SaFly Cloud Protection');
		}
	}
	//return $str;
}

function SaFly_Options_If_API_Domain($str, $notice = 'Invalid API Domain.')
{
	if (!empty($str)) {
		if (function_exists(esc_url)) {
			$str   = esc_url($str);
			$tempu = parse_url($str);
			$str   = $tempu['host'];
		}
		if (!preg_match('/(\w){1,63}(\.(\w){1,63}){1,5}$/', $str)) {
			wp_die($notice, 'SaFly Cloud Protection');
		}elseif (strlen($str) > 50) {
			wp_die($notice, 'SaFly Cloud Protection');
		}
	}
	return $str;
}

function SaFly_Trigger_VALIDATE($trigger)
{
	if (!preg_match('/^(\w)+(,(\w)+)*$/i', $trigger)) {
		wp_die('Invalid Triggers.', 'SaFly Cloud Protection');
	}
}

function SaFly_Level_VALIDATE($level)
{
	if ($level != ('low' || 'medium' || 'high')) {
		wp_die('Level: only low or medium or high.', 'SaFly Cloud Protection');
	}
}

function SaFly_Number_VALIDATE($number)
{
	if (!empty($number)) {
		if (!preg_match('/^(\w)+$/', $number)) {
			wp_die('Invalid Numbers.', 'SaFly Cloud Protection');
		}
	}
}

?>