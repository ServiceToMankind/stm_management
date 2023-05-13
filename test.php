<?php


$name='pranav';
$age='20';
$mobile='1234567890';
$email='te@te.bv';
$blood_group='1';
$gender='1';
$pid='0';

    // send post request to api
    // API endpoint URL
$url = 'http://localhost/apis.stmorg.in/medical/patients/add_patient';

// Data to be sent in the POST request
$data = array(
    'name' => $name,
    'age' => $age,
    'gender' => $gender,
    'mobile' => $mobile,
    'email' => $email,
    'blood_group' => $blood_group,
    'pid' => $pid,
    // add more fields as required
);
print_r($data);
// Initialize cURL
$curl = curl_init();

// Set cURL options
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Execute the cURL request and get response
$response = curl_exec($curl);

// Check for cURL errors
if (curl_errno($curl)) {
    echo 'Error: ' . curl_error($curl);
} else {
    // Process the response data
    $data = json_decode($response, true);
    print_r($data);
    // do something with the data
}

// Close cURL
curl_close($curl);