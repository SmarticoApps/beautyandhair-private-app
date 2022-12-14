<?php

require_once 'functions.php';

$client_id = $_ENV['client_id'];
$shared_secret = $_ENV['shared_secret'];
$shop = $_ENV['shop'];

auth($shared_secret);

$query = array(
	'client_id' => $client_id,
	'client_secret' => $shared_secret,
	'code' => $_GET['code'],
);

$url = 'https://' . $shop . '/admin/oauth/access_token';

$result = curl($url, $query);

if(empty($result)) {
	die('Error: Problem getting access token from shopify oauth');
}

file_put_contents('access_token.json', json_encode($result));

$redirect_uri = 'https://' . $shop . '/admin/apps/' . $client_id;
header('Location: ' . $redirect_uri);
exit;
