<?php
/**
 * This file is used to map Teamwork Users with Float Users.
 */

$float_base_url = 'https://api.float.com/v3/people';
$mapping = array();

try
{
    //Get Float Users.

    $creds = json_decode(file_get_contents(__DIR__ . '/credentials.json'),true);

    $ch = curl_init();
    $headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $creds['float'] 
    );

    curl_setopt($ch, CURLOPT_URL, $float_base_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $float_people_response = curl_exec($ch);

    if ((false === $float_people_response) || (false === isJson($float_people_response)) ) {
        throw new Exception(curl_error($ch), curl_errno($ch));
    }

    $float_people_response = json_decode($float_people_response,true);

    $emails = array_column($float_people_response, 'email');

    curl_close($ch);

    //Get Teamwork Users.

    $tw_endpoint = '/projects/api/v3/people.json';
    $tw_base_url =$creds['tw_base']. $tw_endpoint;

    $ch = curl_init();
    $headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
    );

    curl_setopt($ch, CURLOPT_USERPWD, $creds['tw']);  
    curl_setopt($ch, CURLOPT_URL, $tw_base_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $tw_people_response = curl_exec($ch);

    if (($tw_people_response === false) || (false === isJson($tw_people_response))) {
        throw new Exception(curl_error($ch), curl_errno($ch));
    }

    curl_close($ch);

    $tw_people_response = json_decode($tw_people_response,true);

    foreach($tw_people_response['people'] as $person)
    {
        $float_pos = array_search($person['email'],$emails);

        if(false !== $float_pos)
        {
            $mapping[$float_people_response[$float_pos]['people_id']] = array(
                'email' => $person['email'],
                'firstName' => $person['firstName'],
                'lastName' => $person['lastName'],
                'tw_id' =>  $person['id'],
            );
        }
    }

    if(false !== file_put_contents(__DIR__ . '/users.json', json_encode($mapping)))
    {
        echo 'User mapping saved successfully.';
    } 



}catch(Exception $e) {

    trigger_error(sprintf(
        'Curl failed with error #%d: %s',
        $e->getCode(), $e->getMessage()),
        E_USER_ERROR);

}

/**
 * Validates if given input is valid json string.
 * $string string Input string to validate
 * Returns boolean
 */
function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}