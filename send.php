<?php

/* Start session and load lib */
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('../../../wp-blog-header.php');

//Twitscription Options
$twitscription_opt = get_option('twitscription_options');

/* Create TwitterOAuth object and get request token */
$connection = new TwitterOAuth($twitscription_opt['consumer_key'], $twitscription_opt['consumer_secret']);
 
/* Get request token */
$request_token = $connection->getRequestToken(get_option('siteurl') . '/wp-content/plugins/twitscription/return.php');

/* Save request token to session */
$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
$_SESSION['redirect_to'] = $_REQUEST['redirect_to'];

/* If last connection fails don't display authorization link */
switch ($connection->http_code) {
  	case 200:
    /* Build authorize URL */
    $url = $connection->getAuthorizeURL($token);
    header('Location: ' . $url); 
    break;
  default:
	header('Location: ' . get_option('siteurl') . "/wp-login.php?twitscription_error=" . urlencode("'Could not connect to Twitter. Try again later.") . "&redirect_to=${_SESSION['redirect_to']}");
    break;
}

?>