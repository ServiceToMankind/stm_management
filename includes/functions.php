<?php
/*
 * Errors go to the log, never to the page.
 *
 * With display_errors on, a single notice prints itself into whatever element
 * is being rendered at the time — mid-table it shunts the columns apart, and it
 * puts the server's filesystem path in front of the user. Set STM_DEBUG=1 to
 * bring them back on screen while working locally.
 */
error_reporting(E_ALL);
ini_set('display_errors', getenv('STM_DEBUG') === '1' ? '1' : '0');
ini_set('log_errors', '1');

// Point STM_API_URL at a local copy of the API to work offline; unset, which is
// how it runs in production, this is the live API.
$api_url = getenv('STM_API_URL') ?: "https://apis.stmorg.in";

/*
 * Shared key between this portal and the API.
 *
 * The portal endpoints identify the caller by the uid we send and check that
 * uid's role in the database. Set STM_PORTAL_KEY to the same value on both
 * containers and every request also carries this key, so reaching the API is
 * not by itself enough to pose as the treasurer. Leave it unset and the API
 * does not ask for it, which is how this ships.
 *
 * It lives in the environment rather than in the code because this repo is
 * public. Attached below to every outbound call, so no call site can forget it.
 */
function portal_key()
{
  $key = getenv('STM_PORTAL_KEY');
  return is_string($key) && $key !== '' ? $key : null;
}

function api_add_key($url)
{
  $key = portal_key();
  if ($key === null) return $url;
  return $url . (strpos($url, '?') === false ? '?' : '&') . 'pkey=' . urlencode($key);
}

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
  curl_setopt($ch, CURLOPT_URL, api_add_key($url));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
  curl_setopt($ch, CURLOPT_URL, api_add_key($url));
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  curl_setopt($ch, CURLOPT_TIMEOUT, 60);

  $output = curl_exec($ch);
  if (curl_errno($ch)) {
    error_log('Curl error: ' . curl_error($ch));
  }
  curl_close($ch);
  return $output;
}

/*
 * POST with file attachments.
 *
 * get_api_data_post() only carries one file, under a fixed field name. Tickets
 * and fund requests can take several proofs at once, so this builds the
 * multipart body properly: $files is whatever arrived in $_FILES['proof'] and
 * is sent on as proof[0], proof[1] and so on.
 */
function post_api_data_files($url, $postData, $field, $files)
{
  $payload = [];
  foreach ($postData as $k => $v) {
    $payload[$k] = $v;
  }

  if (!empty($files) && is_array($files['name'])) {
    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
      if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
      if (!is_uploaded_file($files['tmp_name'][$i])) continue;
      $payload[$field . '[' . $i . ']'] = new CURLFile(
        $files['tmp_name'][$i],
        $files['type'][$i] ?: 'application/octet-stream',
        $files['name'][$i]
      );
    }
  }

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, api_add_key($url));
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_TIMEOUT, 120);

  $output = curl_exec($ch);
  if (curl_errno($ch)) {
    error_log('Curl error: ' . curl_error($ch));
  }
  curl_close($ch);
  return $output;
}
?>