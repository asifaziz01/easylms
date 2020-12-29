<?php
defined('BASEPATH') OR exit('No direct script access allowed');


function _AT_TEXT ($code='', $type='', $lang='en') {
	
	$data = array ();
	
	$data['en'] = array ('msg'=>array(
			'INVALID_CREDENTIALS'=>'The username/password combination is incorrect',
			'INVALID_USERNAME'=>'We don\'t recongnise you. Please check your username',
			'INVALID_PASSWORD'=>'You have entered a wrong password',
			'INVALID_CAPTCHA'=>'You have entered a wrong captcha',
			'MAX_ATTEMPTS_REACHED'=>'Too many retry attempts. Try again after '.(LOCK_TIME/60).' minutes',
			'LOGIN_ERROR'=>'There was some error. Please retry again',
			'ACCOUNT_DISABLED'=>'Your account is disabled or pending for admin approval. Try again later',
			'LOGIN_SUCCESSFUL'=>'Login successful. Loading your dashboard...',
			'VALIDATION_ERROR'=>validation_errors (),
			),
		);
		
	return $data[$lang][$type][$code];
}

function print_pre($object){
	$object = print_r($object, true);
	print_r("<pre style=\"white-space: pre-wrap;\">$object</pre>");
}
function excerpt($text, $limit) {
	if (str_word_count($text, 0) > $limit) {
		$words = str_word_count($text, 2);
		$pos = array_keys($words);
		$text = substr($text, 0, $pos[$limit]) . '&hellip;';
	}
	return $text;
}
function getYoutubeEmbedUrl($url){
    $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
    $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))(\w+)/i';
    if (preg_match($longUrlRegex, $url, $matches)) {
        $youtube_id = $matches[count($matches) - 1];
    }
    if (preg_match($shortUrlRegex, $url, $matches)) {
        $youtube_id = $matches[count($matches) - 1];
    }
    if(isset($youtube_id)){
    	return 'https://www.youtube.com/embed/' . $youtube_id ;
    }else{
    	return null;
    }
}