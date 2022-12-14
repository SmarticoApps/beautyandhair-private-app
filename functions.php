<?php

function curl($url = '', $query = array()) {

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, count($query));
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
  $result_curl = curl_exec($ch);
  curl_close($ch);
  return json_decode($result_curl, true);

}

function getAccessToken() {
  $json = file_get_contents('access_token.json');
  $array = json_decode($json, true);
  return $array['access_token'];
}

function shopify_call($api_endpoint = '', $query = array(), $method = 'GET', $include_header = 'no') {

  global $shop;

  $access_token = getAccessToken();

	$url = 'https://' . $shop . $api_endpoint;

	if(!empty($query) && in_array($method, array('GET', 'DELETE'))) {
		$url = $url . '?' . http_build_query($query);
	}

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, TRUE);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_USERAGENT, 'Beauty&Hair');
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($curl, CURLOPT_TIMEOUT, 60);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

	$request_headers = array();

	if(!is_null($access_token))
	{
		$request_headers[] = 'X-Shopify-Access-Token: '.$access_token;
	}

	curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

	if($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
    if(is_array($query)) {
      $query = http_build_query($query);
    }
		curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
	}

	$response = curl_exec($curl);
	$error_number = curl_errno($curl);
	$error_message = curl_error($curl);
	curl_close($curl);

	if($error_number) {
		return $error_message;
	} else {
		$response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
		$response_json = json_decode($response[1], true);

		$headers = array();
		$header_data = explode("\n", $response[0]);
		$headers['Status'] = $header_data[0];
		array_shift($header_data);

		foreach($header_data as $part) {
			$h = explode(': ', $part);

			if(count($h) > 2) {
				$htmp = $h[0];
				array_shift($h);
				$htmp2 = implode(' ', $h);
				$h[0] = $htmp;
				$h[1] = $htmp2;
			}
			$headers[trim($h[0])] = trim($h[1]);
		}

    if(!empty($headers['x-shopify-shop-api-call-limit']) && $headers['x-shopify-shop-api-call-limit'] == '40/40') {
      $msg = 'CRITICAL API ERROR: Reached maximum call limit 40/40.. sleeping for 60 seconds.. Zzz';
      error_log(__FILE__ . ' : ' . $msg);
      sleep(60);
    }

    if($include_header == 'no') {
			return array('response' => $response_json);
		} elseif($include_header == 'yes') {
			return array('headers' => $headers, 'response' => $response_json);
		}
	}

}

function auth($shared_secret = '') {

  $hmac = $_GET['hmac'] ?? '';

  $params = array_diff_key($_GET, array('hmac' => ''));
  ksort($params);
  $hmac_hash = hash_hmac('sha256', http_build_query($params), $shared_secret);

  if(hash_equals($hmac, $hmac_hash) === false) {
    die('Authentication failed.');
  }
  return true;

}