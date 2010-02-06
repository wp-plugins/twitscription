<?php
/**
 * Take the user when they return from Twitter. Get access tokens.
 * Verify credentials and redirect to based on response from Twitter.
 */

/* Start session and load lib */
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('../../../wp-blog-header.php');
require_once('twitscription.php');
require('../../../wp-includes/registration.php');

//Twitscription Options
$twitscription_opt = get_option('twitscription_options');

/* If the oauth_token is old redirect to the connect page. */
if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  	$_SESSION['oauth_status'] = 'oldtoken';
	header('Location: ' . get_option('siteurl') . "/wp-login.php");
}

/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
$connection = new TwitterOAuth($twitscription_opt['consumer_key'], $twitscription_opt['consumer_secret'], $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* Request access tokens from twitter */
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

/* Save the access tokens. Normally these would be saved in a database for future use. */
$_SESSION['access_token'] = $access_token;

/* Remove no longer needed request tokens */
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

/* If HTTP response is 200 continue otherwise send to connect page to retry */
if (200 == $connection->http_code) {
	/* The user has been verified and the access tokens can be saved for future use */
	$_SESSION['status'] = 'verified';
	/* If method is set change API call made. Test is called by default. */
	$user_json = $connection->get('account/verify_credentials');
	$user_array = json_decode($user_json);
	$username = $twitscription_opt['username_prefix'] . $user_array->{'screen_name'};

	/* If enabled check if users is a current follower */
	if ( $twitscription_opt['followers_only'] = TRUE ) {
		if ( $user_array->{'screen_name'} != $twitscription_opt['twitterlogin'] ) {
			$access_allow = $connection->get('friendships/exists', array('user_a' => $user_array->{'screen_name'}, 'user_b' => $twitscription_opt['twitterlogin']));
			if ( $access_allow != 'true' ) {
				header('Location: ' . get_option('siteurl') . "/wp-login.php?twitscription_error=" . urlencode($twitscription_opt['followers_login_failure']) . "&redirect_to=${_SESSION['redirect_to']}");
				exit();	
			}
		}
	}
	
	$user = get_userdatabylogin($username);
	$user_id = $user->ID;

	/* If user doesn't exist create user */
	if ( !$user_id ) {
		$userdata = array (
			'user_pass' => wp_generate_password(),
			'user_login' => $username,
			'display_name' => $user_array->{'name'},
			'user_url' => 'http://twitter.com/'.$user_array->{'screen_name'},
			'description' => $user_array->{'description'},
		);
		wp_update_user($userdata);
	} 

	
	/* Login as user */
	$user = get_userdatabylogin($username);
	$user_id = $user->ID;
	wp_set_current_user($user_id, $username);
	wp_set_auth_cookie($user_id);
	do_action('wp_login', $username);

	/* Redirect to Original Page */
	header("Location:  ${_SESSION['redirect_to']}");

} else {

  	/* Save HTTP status for error dialog on connnect page.*/
	header('Location: ' . get_option('siteurl') . "/wp-login.php?twitscription_error=" . urlencode("Login Failed") . "&redirect_to=${_SESSION['redirect_to']}");

}