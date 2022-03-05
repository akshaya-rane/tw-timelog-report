<?php
/**
 * This file is used to send email notifications. 
 */

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
//Alias the League Google OAuth2 provider class
use League\OAuth2\Client\Provider\Google;
require '../vendor/autoload.php';

$creds = json_decode(file_get_contents(__DIR__ . '/credentials.json'),true);

$summary_row ='';
$total = 0;

require 'PHPMailer/vendor/autoload.php';

    $mail = new PHPMailer;
    $mail->isSMTP();
    //Enable SMTP debugging
    //SMTP::DEBUG_OFF = off (for production use)
    //SMTP::DEBUG_CLIENT = client messages
    //SMTP::DEBUG_SERVER = client and server messages
    $mail->SMTPDebug = SMTP::DEBUG_OFF;

    $mail->Host = "smtp.gmail.com";
    $mail->Port = 465; // TLS only

    //Set the encryption mechanism to use:
    // - SMTPS (implicit TLS on port 465) or
    // - STARTTLS (explicit TLS on port 587)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

    $mail->SMTPAuth = true;
    //Set AuthType to use XOAUTH2
    $mail->AuthType = 'XOAUTH2';

    //Fill in authentication details here
    //Either the gmail account owner, or the user that gave consent
    $email = $creds['reminder_email'];
    $clientId = $creds['client_id'];
    $clientSecret = $creds['client_secret'];

    //Obtained by configuring and running get_oauth_token.php
    //after setting up an app in Google Developer Console.
    $refreshToken = $creds['refresh_token'];

    //Create a new OAuth2 provider instance
    $provider = new Google(
        [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]
    );

    //Pass the OAuth provider instance to PHPMailer
    $mail->setOAuth(
        new OAuth(
            [
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => $email,
            ]
        )
    );


foreach($email_list as $single_email_entity)
{
    $name = $single_email_entity['name'];
    $percentage_logged = $single_email_entity['percentage_logged'];
    $scheduled = $single_email_entity['scheduled'];
    $logged_time = $single_email_entity['logged_time'];
    $total += ($scheduled - $logged_time);

    $mail->setFrom($email, 'TimeLog');
    $mail->addAddress($single_email_entity['email'], $name);
    $mail->Subject = 'TimeLog Reminder week - ' . $start_week . " -- ". $end_week;
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->CharSet = PHPMailer::CHARSET_UTF8;

    ob_start();
    include(__DIR__ . '/template/index.php');
    $content = ob_get_contents();
    $mail->msgHTML(ob_get_contents()); //Read an HTML message body from an external file, convert referenced images to embedded,

    ob_end_clean();

    $mail->AltBody = 'Last week Time log reminder';  
}

if(!$mail->send()){
    echo "Mailer Error: " . $mail->ErrorInfo;
 }else{
     echo "Message sent!";
 }