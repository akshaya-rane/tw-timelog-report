<?php
/**
 * This file is used to get the time log from Teamwork & schedule from Float. It triggers email notification if required. 
 */

$previous_week = strtotime("-1 week +1 day");

$start_week = strtotime("last sunday midnight",$previous_week);
$end_week = strtotime("next saturday",$start_week);

$start_week = date("Y-m-d",$start_week);
$end_week = date("Y-m-d",$end_week);

$creds = json_decode(file_get_contents(__DIR__ . '/credentials.json'),true);

$float_base_url = 'https://api.float.com/v3/reports/people?start_date=' . $start_week . '&end_date=' . $end_week;
$tw_endpoint = '/projects/api/v3/time.json?startDate=' . $start_week . '&endDate=' . $end_week;
$tw_base_url =$creds['tw_base']. $tw_endpoint;
$scheduled = array();
$email_list = array();

try
{
    //Get Schedule of last week.
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

    $float_people_response = json_decode($float_people_response);

    foreach($float_people_response->people as $person)
    {
        if($person->scheduled > 0)
        {
            $scheduled[$person->people_id] = $person->scheduled;
        }
    }

    curl_close($ch);

    //Get Teamwork time logged.

    $mapping = json_decode(file_get_contents(__DIR__ . '/users.json'),true);

    foreach($scheduled as $float_person_id => $hrs)
    {

        $ch = curl_init();
        $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        );
    
        curl_setopt($ch, CURLOPT_USERPWD, $creds['tw']);  
        curl_setopt($ch, CURLOPT_URL, $tw_base_url . '&assignedToUserIds=' . intval($mapping[$float_person_id]['tw_id']));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
        $tw_time_response = curl_exec($ch);
    
        if (($tw_people_response === false) || (false === isJson($tw_time_response))) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }

        if ((false === $tw_time_response) || (false === isJson($tw_time_response)) ) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }

        $tw_time_response = json_decode($tw_time_response,true);

        $min = 0;

        foreach($tw_time_response['timelogs'] as $time_entry)
        {
            $min += $time_entry['minutes'];
        }

        $logged_time = round($min/60,2);

        $percentage_logged = round($logged_time/(min($hrs,40)) * 100,0);
        
        if( $percentage_logged <80 && ($mapping[$float_person_id]))
        {
            $email_list[] = array(
                'email' =>  $mapping[$float_person_id]['email'],
                'name' =>  $mapping[$float_person_id]['firstName'] . ' ' .  $mapping[$float_person_id]['lastName'],
                'percentage_logged' => $percentage_logged,
                'scheduled' => $hrs,
                'logged_time' => $logged_time,
            );
        }
    
        curl_close($ch);
    }

    //Send Reminder emails.

    if(!empty($email_list))
    {
        include_once(__DIR__ . '/send_email.php');
    }


}catch(Exception $e) {

    trigger_error(sprintf(
        'Curl failed with error #%d: %s',
        $e->getCode(), $e->getMessage()),
        E_USER_ERROR);

}

/**
 * Validates given string is Json.
 */
function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}