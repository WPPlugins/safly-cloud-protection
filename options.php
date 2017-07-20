<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

//Dashboard
function safly_plugin_menu_page()
{
	add_options_page('SaFly Cloud Protection', 'SaFly Cloud Protection', 'manage_options', 'safly-protection', 'safly_plugin_menu_page_add');
}
function safly_plugin_menu_page_add()
{
	//Prevent users without the right permissions from accessing things
	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	global $safly_api_domain, $safly_api_key, $safly_api_server_url, $safly_ip_getenv;
	global $safly_options_tmp, $safly_options, $safly_time_lag;
	global $safly_ip, $safly_level, $saflysalt, $saflysign;
	global $SaFly_Options_Update_Notice;
	//Update Notice
	if (!empty($SaFly_Options_Update_Notice)) {
		echo $SaFly_Options_Update_Notice;
	}
	/* SaFly Global Setting */

	/* SaFly Interact WAF */
	if ($safly_api_domain && $safly_api_key && $safly_options_tmp) {
		$safly_code_timet1 = microtime(TRUE);
		$safly_code        = SaFly_Get_API_Code('true');
		$safly_code_timet2 = microtime(TRUE);
		$safly_code_time   = round($safly_code_timet2 - $safly_code_timet1, 3);
	}else {
		$safly_code        = 'Undefined.';
		$safly_code_time   = 'Undefined.';
	}
	if (wp_cache_get($safly_ip, '') == '1') {
		$safly_if_white_listed = 'Yes';
	}else {
		$safly_if_white_listed = 'No';
	}
	$safly_wp_ip_cache = wp_cache_get('saflyip', '');
	if (empty($safly_wp_ip_cache)) {
		$safly_wp_ip_cache = 'Undefined.';
	}
	//Form Checked
	if ($safly_options['ifinteracton'] == 'on') {
		$safly_if_interacton = ' checked="checked"';
	}
	if ($safly_options['ifwhitelistuser'] == 'on') {
		$safly_if_whitelist_user = ' checked="checked"';
	}
	if ($safly_options['ifhomepageoff'] == 'on') {
		$safly_if_homepage_off = ' checked="checked"';
	}
	if ($safly_options['ifpostoff'] == 'on') {
		$safly_if_post_off = ' checked="checked"';
	}
	if ($safly_options['ifspidersuaoff'] == 'on') {
		$safly_if_spiders_ua_off = ' checked="checked"';
	}
	//Form Radio
	$safly_options_level = $safly_options['level'];
	if ($safly_options_level == 'low') {
		$safly_radio = '<input type="radio" checked="checked" name="level" value="low" />Low&nbsp;<input type="radio" name="level" value="medium" />Medium&nbsp;<input type="radio" name="level" value="high" />High&nbsp;&nbsp;&nbsp;';
	}elseif ($safly_options_level == 'medium') {
		$safly_radio = '<input type="radio" name="level" value="low" />Low&nbsp;<input type="radio" checked="checked" name="level" value="medium" />Medium&nbsp;<input type="radio" name="level" value="high" />High&nbsp;&nbsp;&nbsp;';
	}elseif ($safly_options_level == 'high') {
		$safly_radio = '<input type="radio" checked="checked" name="level" value="low" />Low&nbsp;<input type="radio" name="level" value="medium" />Medium&nbsp;<input type="radio" checked="checked" name="level" value="high" />High&nbsp;&nbsp;&nbsp;';
	}else {
		$safly_radio = 'Invalid level.';
	}
	/* SaFly Request Test */
	if (get_option('safly_if_request_test')) {
		if (get_option('safly_if_request_test') == 'on') {
			$safly_if_request_test = ' checked="checked"';
		}
	}
	//Form Radio
	$safly_request_test_level = $safly_options['request_test_level'];
	if ($safly_request_test_level == 'low') {
		$safly_radio_request_test = '<input type="radio" checked="checked" name="request_test_level" value="low" />Low&nbsp;<input type="radio" name="request_test_level" value="medium" />Medium&nbsp;<input type="radio" name="request_test_level" value="high" />High&nbsp;&nbsp;&nbsp;';
	}elseif ($safly_request_test_level == 'medium') {
		$safly_radio_request_test = '<input type="radio" name="request_test_level" value="low" />Low&nbsp;<input type="radio" checked="checked" name="request_test_level" value="medium" />Medium&nbsp;<input type="radio" name="request_test_level" value="high" />High&nbsp;&nbsp;&nbsp;';
	}elseif ($safly_request_test_level == 'high') {
		$safly_radio_request_test = '<input type="radio" checked="checked" name="request_test_level" value="low" />Low&nbsp;<input type="radio" name="request_test_level" value="medium" />Medium&nbsp;<input type="radio" checked="checked" name="request_test_level" value="high" />High&nbsp;&nbsp;&nbsp;';
	}else {
		$safly_radio_request_test = 'Invalid level.';
	}	
	/* SaFly Avatar */
	if (get_option('safly_if_avatar')) {
		if (get_option('safly_if_avatar') == 'on') {
			$safly_if_avatar = ' checked="checked"';
		}
	}
	echo '
	<h1>SaFly Cloud Protection</h1>
	<p>Notice:<br>1. 如果您被拦截而无法管理您的网站，请在插件目录重命名 \'safly-cloud-protection\' 以停用插件或更换访问 IP。<br>2. 建议每次更新插件完毕后 Reset 以获取最新设置。</p>
	<p>Shortcut links:<br>官方网站: <a target=\'_blank\' href="https://www.safly.org/">https://www.safly.org/</a><br>客户中心: <a target=\'_blank\' href="https://juice.oranme.com/">https://juice.oranme.com/</a><br>API 文档: <a target=\'_blank\' href="https://blog.safly.org/category/innovate/apidoc/">https://blog.safly.org/category/innovate/apidoc/</a></p>
	<p>
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
		<table class="form-table">

			<!-- SaFly Global Setting -->
			<br><h2><a target=\'_blank\' href="https://juice.oranme.com/wp-admin/admin.php?page=saflyorgmyapi">Global Setting</a></h2>
			<tr valign="top"><th scope="row"><label>API Domain</label></th><td><input type="text" name="safly_api_domain" value="' . $safly_api_domain . '" class="regular-text" /><span class="description">请输入您在 <a href="https://juice.oranme.com/wp-admin/admin.php?page=saflyorgmyapi" target="_blank">OranMe JUICE</a> 注册的 API Domain</span></td></tr>
			<tr valign="top"><th scope="row"><label>API KEY</label></th><td><input type="text" name="safly_api_key" value="' . $safly_api_key . '" class="regular-text" /><span class="description">请输入您在 <a href="https://juice.oranme.com/wp-admin/admin.php?page=saflyorgmyapi" target="_blank">OranMe JUICE</a> 注册的 API KEY</span></td></tr>
			<tr valign="top"><th scope="row"><label>API Server</label></th><td><input type="text" name="saflyapiserverurl" value="' . $safly_api_server_url . '" class="regular-text" /><span class="description">使用的 SaFly Cloud API 服务器</span></td></tr>
			<tr valign="top"><th scope="row"><label>IP Variable</label></th><td><input type="text" name="ip_getenv" value="' . $safly_ip_getenv . '" class="regular-text" /><span class="description">从指定全局变量中获取访客 IP, 按填入顺序表示优先级。请根据自身实际情况增减，不正确的设置可能导致 IP 欺诈等事件的发生。使用 \',\' 分隔。</span></td></tr>
			<tr valign="top"><th scope="row"><label>My IP</label></th><td>' . $safly_ip . '<span class="description">&nbsp;&nbsp;&nbsp;当前用户 IP</span></td></tr>			
		</table>

		<table class="form-table">	
			<!-- SaFly Interact WAF™ -->
			<br><h2><a target=\'_blank\' href="https://blog.safly.org/safly-interact-waf/">SaFly Interact WAF™</a></h2>
			<tr valign="top"><th scope="row"><label>Enable SaFly Interact WAF™</label></th><td><input type="checkbox" name="ifinteracton" value="on"' . $safly_if_interacton . ' /><span class="description">勾选后启用 SaFly Interact WAF™</span></td></tr>
			<tr valign="top"><th scope="row"><label>If Whitelist Users</label></th><td><input type="checkbox" name="ifwhitelistuser" value="on"' . $safly_if_whitelist_user . ' /><span class="description">勾选后将已登录用户永久加入白名单缓存</span></td></tr>
			<tr valign="top"><th scope="row"><label>If Homepage Off</label></th><td><input type="checkbox" name="ifhomepageoff" value="on"' . $safly_if_homepage_off . ' /><span class="description">勾选后首页不会发生 Mitigate 跳转，这对提升用户体验很有帮助。Notice: 请保证网站首页路径为 /(index.php), 或者已定义常量 WP_SITEURL 或 WP_HOME 。</span></td></tr>
			<tr valign="top"><th scope="row"><label>If Spiders UA Off</label></th><td><input type="checkbox" name="ifspidersuaoff" value="on"' . $safly_if_spiders_ua_off . ' /><span class="description">勾选后对指定 Spiders UA  禁用 Mitigate 服务，避免了小概率的误拦搜索引擎的问题。我们不建议您勾选此选项，因为它有被欺骗的安全风险。SaFly Interact WAF™ 会自动加载 <a target=\'_blank\' href="https://blog.safly.org/safly-spider-analyse/">SaFly Spider Analyse</a>, 本身就可以正确并安全地放行大部分知名搜索引擎。</span></td></tr>
			<tr valign="top"><th scope="row"><label>If POST Off</label></th><td><input type="checkbox" name="ifpostoff" value="on"' . $safly_if_post_off . ' /><span class="description">勾选后对登录表单、注册表单、评论表单禁用 Mitigate 服务，避免了小概率的无法评论、登录等问题。SaFly Cloud Protection 采用计数白名单，即非法嫌疑的 POST 请求仍会被拦截。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Security Level</label></th><td>' . $safly_radio . '<span class="description">防御安全等级</span></td></tr>
			<tr valign="top"><th scope="row"><label>Whitelist Expiration</label></th><td><input type="number" name="whitelist_expire" value="' . $safly_options['whitelist_expire'] . '" /><span class="description">IP 白名单过期时间 (seconds), \'0\' 为永不过期。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Blacklist Expiration</label></th><td><input type="number" name="ban_expire" value="' . $safly_options['ban_expire'] . '" /><span class="description">IP 黑名单过期时间 (seconds), \'0\' 为永不过期。</span></td></tr>
			<tr valign="top"><th scope="row"><label>POST Counter</label></th><td><input type="number" name="postcounter" value="' . $safly_options['postcounter'] . '" /><span class="description">POST 计数白名单 (times)</span></td></tr>
			<tr valign="top"><th scope="row"><label>POST Counter Expiration</label></th><td><input type="number" name="postcounter_expire" value="' . $safly_options['postcounter_expire'] . '" /><span class="description">POST 计数白名单失效时间 (seconds), \'0\' 为永不过期。</span></td></tr>
			<tr valign="top"><th scope="row"><label>WAF Server</label></th><td><input type="text" name="saflywafserver" value="' . $safly_options['saflywafserver'] . '" class="regular-text" /><span class="description">使用的 WAF 服务器</span></td></tr>
			<tr valign="top"><th scope="row"><label>Excluded Spiders UA</label></th><td><input type="text" name="exclude_spiders_ua" value="' . $safly_options['exclude_spiders_ua'] . '" class="regular-text" /><span class="description">当访客 User Agent 中包含指定参数且 ifspidersuaoff 勾选时，禁用 Mitigate 服务。使用 \',\' 分隔。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Excluded REQUEST Keyword</label></th><td><input type="text" name="exclude_post_keyword" value="' . $safly_options['exclude_post_keyword'] . '" class="regular-text" /><span class="description">当 $_REQUEST 中包含指定参数且 ifpostoff 勾选时，禁用 Mitigate 服务。使用 \',\' 分隔。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Excluded URL Keyword</label></th><td><input type="text" name="exclude_url_keyword" value="' . $safly_options['exclude_url_keyword'] . '" class="regular-text" /><span class="description">当 URL 中包含指定参数时，禁用 Mitigate 服务。使用 \',\' 分隔。</span></td></tr>	
			<tr valign="top"><th scope="row"><label>SaFly Server Time-lag</label></th><td>' . $safly_time_lag . '<span class="description">&nbsp;&nbsp;&nbsp;WordPress 缓存中的 Time-lag, 用于 Make Sign</span></td></tr>
			<tr valign="top"><th scope="row"><label>Current Code</label></th><td>' . $safly_code . '<span class="description">&nbsp;&nbsp;&nbsp;当前 API 返回值</span></td></tr>
			<tr valign="top"><th scope="row"><label>API Curl Time</label></th><td>' . $safly_code_time . '<span class="description">&nbsp;&nbsp;&nbsp;API Curl 消耗的时间</span></td></tr>
			<tr valign="top"><th scope="row"><label>Whether I am white-listed</label></th><td>' . $safly_if_white_listed . '<span class="description">&nbsp;&nbsp;&nbsp;当前用户是否被白名单</span></td></tr>
		</table>	

		<table class="form-table">
			<!-- SaFly Request Test -->	
			<br><h2><a target=\'_blank\' href="https://blog.safly.org/safly-request-test/">SaFly Request Test</a></h2>
			<tr valign="top"><th scope="row"><label>Enable SaFly Request Test</label></th><td><input type="checkbox" name="saflyifrequesttest" value="on"' . $safly_if_request_test . ' /><span class="description">勾选后启用 SaFly Request Test</span></td></tr>
			<tr valign="top"><th scope="row"><label>Security Level</label></th><td>' . $safly_radio_request_test . '<span class="description">防御安全等级</span></td></tr>
			<tr valign="top"><th scope="row"><label>Triggers</label></th><td><input type="text" name="request_test_trigger" value="' . $safly_options['request_test_trigger'] . '" class="regular-text" /><span class="description">当 $_REQUEST 中包含指定参数时，触发 SaFly Request Test。使用 \',\' 分隔。</span></td></tr>
		</table>	

		<table class="form-table">
			<!-- SaFly Avatar -->
			<br><h2><a target=\'_blank\' href="https://juice.oranme.com/wp-admin/admin.php?page=avatar1">SaFly Avatar</a></h2>
			<tr valign="top"><th scope="row"><label>Enable SaFly Avatar</label></th><td><input type="checkbox" name="ifsaflyavatar" value="on"' . $safly_if_avatar . ' /><span class="description">勾选后启用 SaFly Avatar</span></td></tr>
		</table>			

			';			
	wp_nonce_field();
	echo'
		<p class="submit">
			<input name="saflysave" type="submit" class="button-primary" value="Save Changes and Empty Cache" />
			<input name="saflyreset" type="submit" class="button-secondary" value="Reset" />
		</p>
		</form>
	</p>
	';
}

function safly_load_options()
{

	global $safly_options;

	/* SaFly Options CSRF */
	if (isset($_POST['saflysave']) || isset($_POST['saflyreset'])) {
		//Prevent users without the right permissions from accessing things
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		//Options Nonces
		check_admin_referer();
	}

	/* SaFly Setting */
	if (isset($_POST['saflysave'])) {

		/* SaFly Global Setting */
		//Update the API KEY
		$safly_api_domain = trim($_POST['safly_api_domain']);
		$safly_api_key    = trim($_POST['safly_api_key']);
		SaFly_Options_If_API_Domain($safly_api_domain);
		SaFly_API_Key_VALIDATE($safly_api_key);
		update_option('safly_api_domain', $safly_api_domain);
		update_option('safly_api_domain_key', $safly_api_key);
		//Update the API Server
		$safly_api_server_url = $_POST['saflyapiserverurl'];
		SaFly_Options_If_API_Server($safly_api_server_url);
		update_option('safly_api_server_url', $safly_api_server_url);
		//Update the trigger
		$safly_ip_getenv = $_POST['ip_getenv'];
		SaFly_Trigger_VALIDATE($safly_ip_getenv);
		$safly_options['ip_getenv'] = $safly_ip_getenv;
		/* SaFly Interact WAF Setting Page */
		//Empty Cache
		wp_cache_flush();
		//If the Interact WAF is on
		if ($_POST['ifinteracton'] == 'on') {
			$safly_options['ifinteracton'] = 'on';
		}else {
			$safly_options['ifinteracton'] = 'off';
		}
		//If user white-listing is on
		if ($_POST['ifwhitelistuser'] == 'on') {
			$safly_options['ifwhitelistuser'] = 'on';
		}else {
			//Remove the current white-list
			//wp_cache_flush();
			$safly_options['ifwhitelistuser'] = 'off';
		}
		//If in homepage turned off
		if ($_POST['ifhomepageoff'] == 'on') {
			$safly_options['ifhomepageoff'] = 'on';
		}else {
			$safly_options['ifhomepageoff'] = 'off';
		}
		//If POST turned off
		if ($_POST['ifpostoff'] == 'on') {
			$safly_options['ifpostoff'] = 'on';
		}else {
			$safly_options['ifpostoff'] = 'off';
		}
		//If Spiders UA turned Off
		if ($_POST['ifspidersuaoff'] == 'on') {
			$safly_options['ifspidersuaoff'] = 'on';
		}else {
			$safly_options['ifspidersuaoff'] = 'off';
		}	
		//Update the normal setting
		$safly_options_level          = $_POST['level'];
		$safly_options_saflywafserver = $_POST['saflywafserver'];
		SaFly_Level_VALIDATE($safly_options_level);
		SaFly_Options_If_API_Server($safly_options_saflywafserver, 'Invalid WAF Server.');		
		$safly_options['level']          = $safly_options_level;
		$safly_options['saflywafserver'] = $safly_options_saflywafserver;
		//Excluded Keywords
		$safly_exclude_url_keyword             = $_POST['exclude_url_keyword'];
		$safly_exclude_post_keyword            = $_POST['exclude_post_keyword'];
		$safly_exclude_spiders_ua              = $_POST['exclude_spiders_ua'];
		SaFly_Trigger_VALIDATE($safly_exclude_url_keyword . $safly_exclude_post_keyword . $safly_exclude_spiders_ua);
		$safly_options['exclude_url_keyword']  = $safly_exclude_url_keyword;
		$safly_options['exclude_post_keyword'] = $safly_exclude_post_keyword;
		$safly_options['exclude_spiders_ua']   = $safly_exclude_spiders_ua;
		//Some Numbers
		$safly_whitelist_expire              = $_POST['whitelist_expire'];
		$safly_ban_expire                    = $_POST['ban_expire'];
		$safly_postcounter                   = $_POST['postcounter'];
		$safly_postcounter_expire            = $_POST['postcounter_expire'];
		SaFly_Number_VALIDATE($safly_whitelist_expire . $safly_ban_expire . $safly_postcounter . $safly_postcounter_expire);
		$safly_options['whitelist_expire']   = $safly_whitelist_expire;
		$safly_options['ban_expire']         = $safly_ban_expire;
		$safly_options['postcounter']        = $safly_postcounter;
		$safly_options['postcounter_expire'] = $safly_postcounter_expire;

		/* SaFly Request Test Setting Page */
		if ($_POST['saflyifrequesttest'] == 'on') {
			update_option('safly_if_request_test', 'on');
		}else {
			delete_option('safly_if_request_test');
		}
		$safly_request_test_level              = $_POST['request_test_level'];
		$safly_request_test_trigger            = $_POST['request_test_trigger'];
		SaFly_Level_VALIDATE($safly_request_test_level);		
		SaFly_Trigger_VALIDATE($safly_request_test_trigger);		
		$safly_options['request_test_level']   = $safly_request_test_level;
		$safly_options['request_test_trigger'] = $safly_request_test_trigger;

		/* SaFly Avatar Setting Page */
		if (isset($_POST['saflysave'])) {
			if ($_POST['ifsaflyavatar'] == 'on') {
				update_option('safly_if_avatar', 'on');
			}else {
				delete_option('safly_if_avatar');
			}
		}

		/* UPDATE SaFly Options */
		$safly_serialize = serialize($safly_options);
		update_option('saflyoptions', $safly_serialize);		
	}
	//If Reset Button
	if (isset($_POST['saflyreset'])) {
		wp_cache_flush();
		delete_option('safly_api_domain');
		delete_option('safly_api_domain_key');
		delete_option('safly_api_server_url');
		delete_option('saflyoptions');
		delete_option('safly_if_request_test');
		delete_option('safly_if_avatar');
		SaFly_Activated();
	}
	//Notice
	SaFly_Options_Update_Notice();	

	/* SaFly Dashboard */
	add_action('admin_menu', 'safly_plugin_menu_page');

}

function SaFly_Options_Update_Notice()
{
	global $SaFly_Options_Update_Notice;
	if (isset($_POST['saflysave']) || isset($_POST['saflyreset'])) {
		//Notice
		ob_start();
		$location = base64_decode(SaFly_Current_URL()) . '&saflynotice=on';
		header("location: $location");
		ob_end_flush();
		exit;
	}
	if (isset($_GET['saflynotice'])) {
		$SaFly_Options_Update_Notice = '<div class="updated"><p>Settings updated successfully!</p></div>';
	}
}

add_action('init', 'safly_load_options');

?>