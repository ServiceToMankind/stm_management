<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$api_url = "https://apis.stmorg.in";
// localhost
// $api_url = "http://localhost/apis-stm";

function pr($arr)
{
  echo '<pre>';
  print_r($arr);
}

function prx($arr)
{
  echo '<pre>';
  print_r($arr);
  die();
}

function get_api_data($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $output = curl_exec($ch);
  if (curl_errno($ch)) {
    error_log('Curl error: ' . curl_error($ch));
  }
  curl_close($ch);
  return $output;
}

function get_api_data_post($url, $postData, $fileData = '')
{
  $ch = curl_init();

  // Prepare the POST data
  if ($fileData == '') {
    $postData = http_build_query($postData);
  } else {
    $postData = array_merge($postData, array('prescriptionfile' => new CURLFile($fileData['tmp_name'], $fileData['type'], $fileData['name'])));
  }

  // Set the CURL options
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

  $output = curl_exec($ch);
  if (curl_errno($ch)) {
    error_log('Curl error: ' . curl_error($ch));
  }
  curl_close($ch);
  return $output;
}
?>