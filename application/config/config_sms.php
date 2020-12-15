<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['sms'] = TRUE;

define('SMS_API_URL', 'http://smsaspirotrans.aspirotechnologies.com/http-api.php');
$query_string  = 'username=APEXCO';
$query_string .= '&password=APEXCO@374';
$query_string .= '&senderid=EASYAP';
$query_string .= '&route=2';
$config['sms_query_string'] = $query_string;