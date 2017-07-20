<?php
/*
   +----------------------------------------------------------------------+
   | SaFly Cloud API - SaFly Request Test                                 |
   +----------------------------------------------------------------------+
   | Copyright (c) 2011-2016 The SaFly Group                              |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.8.x of the SaFly license,   |
   | that is available through the world-wide-web at the following url:   |
   | http://www.safly.org/category/agreements/                            |
   | If you are unable to obtain it through the world-wide-web, please    |
   | send a note to license@safly.org, so we can mail you a copy          |
   | immediately.                                                         |
   +----------------------------------------------------------------------+
   | SaFly Cloud API SaFly Request Test 2016-08-17,                       |
   | API Doc: https://blog.safly.org/safly-request-test/                  |
   +----------------------------------------------------------------------+
   | Authors: SaFly Abyss.C <abyss@safly.org>                             |
   |          DOLPH <dolph@safly.org>                                     |
   |          DOLXU <dolxu@safly.org>                                     |
   +----------------------------------------------------------------------+
*/

/* SaFly Request Test */

function SaFly_Request_Test()
{
	global $safly_api_domain, $safly_api_key, $saflyip, $safly_api_server_url;
	global $saflysalt, $saflysign;
	global $request_test_level, $request_test_trigger;

	require_once(dirname(dirname(__FILE__)) . '/wrapper.php');

	if ($safly_api_domain && $safly_api_key) {
		//Continue if the API is set
		if (SaFly_Isset_REQUEST_Keyword($request_test_trigger)) {
			//Meeting the conditions, trigger SaFly Request Test
			$safly_request_test_api = "{$safly_api_server_url}/api/saflyrequesttest/?level={$request_test_level}&apidomain={$safly_api_domain}&salt={$saflysalt}&sign={$saflysign}";
			$safly_request_array    = array_unique(array_merge($_POST, $_GET));
			//CURL
			$safly_responsetmp      = SaFly_Curl_Post($safly_request_test_api, $safly_request_array);
			//Handle the return
			$safly_response         = json_decode($safly_responsetmp);
			if ($safly_response->code == '000202') {
				//Requests contain the suspect features
				//Please modify the return information according to your business logics
				header('HTTP/1.1 400 Bad Request');
				header('Content-Type: text/plain');
				exit('SaFly Request Test - Bad Request');
			}
		}
	}

}

add_action('plugins_loaded', 'SaFly_Request_Test', '1');

?>