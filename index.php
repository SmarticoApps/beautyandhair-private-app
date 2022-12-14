<?php

require_once 'functions.php';

$client_id = $_ENV['client_id'];
$shared_secret = $_ENV['shared_secret'];
$app_scopes = $_ENV['app_scopes'];
$shop = $_ENV['shop'];

if(empty($_GET['embedded'])) {
  $redirect_uri = 'https://smartico.ngrok.io/generate_token.php';
  $install_url = 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $client_id . '&scope=' . $app_scopes . '&redirect_uri=' . urlencode($redirect_uri);
  header('Location: ' . $install_url);
  exit;
}

auth($shared_secret);

echo 'App Loaded.'. PHP_EOL;

// SAVE SCRIPT
$api_endpoint = '/admin/api/2022-10/script_tags.json';
$api_query = array('script_tag' => array(
                                          'event' => 'onload',
                                          'src' => '//cdn.shopify.com/s/files/1/1410/9094/t/30/assets/product-status.js?v=76794785219450646541670960714',
                                        ));
$result = shopify_call($api_endpoint, $api_query, 'POST');

echo '<pre>';
print_r($result);
echo '</pre>';


// GET ALL SCRIPTS
$api_endpoint = '/admin/api/2022-10/script_tags.json';
$result = shopify_call($api_endpoint);

echo '<pre>';
print_r($result);
echo '</pre>';