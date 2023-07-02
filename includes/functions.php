<?php
function pr($arr){
    echo'<pre>';
    print_r($arr);
}
function prx($arr){
    echo'<pre>';
    print_r($arr);
    die();
}

function get_api_data($url){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}
function get_api_data_post($url, $postData, $fileData = '') {
  $ch = curl_init();

  // Prepare the POST data
  if ($fileData == '') {
      $postData = http_build_query($postData);
  } else {
      $postData = array_merge($postData, array('prescriptionfile' => new CURLFile($fileData['tmp_name'], $fileData['type'], $fileData['name'])));
  }

  // Set the CURL options
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); // Updated to pass $postData directly
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $output = curl_exec($ch);
  curl_close($ch);
  return $output;
}


?>