<?php
/*
Plugin Name: Twitscription
Plugin URI: http://www.natambu.com/twitscription/
Description: Enables twiter logins
Author: Natambu Obleton
Version: 0.1.1
Author URI: http://www.natambu.com
*/


//Twitscription Options
$twitscription_opt = get_option('twitscription_options');


function twitscription_redirect_put() {
	global $wpdb, $request_token, $redirect_to;
    $table = $wpdb->prefix."twitscription_referral";
	$wpdb->insert( $table, array( 'okey' => $request_token['oauth_token'], 'redirect_to' => $redirect_to, 'logtime' => 'NOW()' ), array( '%s', '%s, %s' ) );
}

//----------------------------------------------------------------------------
//	Setup Default Settings
//----------------------------------------------------------------------------

function twitscription_setup_options()
{
	global $twitscription_opt;
	
	$twitscription_version = get_option('twitscription_version'); 
	$twitscription_this_version = '0.1.1';
	
	// Check the version of Members Only
	if (empty($twitscription_version))
	{
		add_option('twitscription_version', $twitscription_this_version);
	} 
	elseif ($twitscription_version != $twitscription_this_version)
	{
		update_option('twitscription_version', $twitscription_this_version);
	}
	
	// Setup Default Options Array
	$optionarray_def = array (
		'followers_only' => FALSE,
		'twitscription' => FALSE,
		'consumer_key' => '',
		'consumer_secret' => '',
		'twitterlogin' => '',
		'username_prefix' => 'tw_',
		'followers_login_failure' => 'This site only allows followers to login using Twitter'
	);
		
	if (empty($twitscription_opt)){ //If there aren't already options for Twitscription
		add_option('twitscription_options', $optionarray_def, 'Twitscription Wordpress Plugin Options');
	}	
}

//----------------------------------------------------------------------------
//		ADMIN OPTION PAGE FUNCTIONS
//----------------------------------------------------------------------------

function twitscription_options_page()
{
	global $wpdb, $wpversion;

	if (isset($_POST['submit']) ) {
	
		if ($_POST['one_time_view_ip'] == 1)
		{
			
			$one_time_view_ip = md5($_SERVER['REMOTE_ADDR']);
		}
		else
		{
			$one_time_view_ip = NULL;
		}

		// Options Array Update
		$optionarray_update = array (
			'followers_only' => $_POST['followers_only'],
			'twitscription' => $_POST['twitscription'],
			'consumer_key' => $_POST['consumer_key'],
			'consumer_secret' => $_POST['consumer_secret'],
			'twitterlogin' => $_POST['twitterlogin'],
			'username_prefix' => $_POST['username_prefix'],
			'followers_login_failure' => $_POST['followers_login_failure']
			
		);
		
		update_option('twitscription_options', $optionarray_update);
	}
	
	// Get Options
	$optionarray_def = get_option('twitscription_options');
	
	
		?>
			<div class="wrap">
			<h2>Twitscription Options</h2>
			<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__); ?>&updated=true">
			<fieldset class="options" style="border: none">
			<p>
			Checking the <em>Followers Only</em> option below will enable twitter login for followers only. If a visitor is not a follower, 
			they will be redirected to the WordPress login page or a page that you can specify. Once logged in they can be redirected back to the page that they originally requested if you choose to.
			</p>
			<table width="100%" <?php $wpversion >= 2.5 ? _e('class="form-table"') : _e('cellspacing="2" cellpadding="5" class="editform"'); ?> >
				<tr valign="top">
					<th width="200px" scope="row">Enabled?</th>
					<td width="100px"><input name="twitscription" type="checkbox" id="twitscription_inp" value="1" <?php checked('1', $optionarray_def['twitscription']); ?>"  /></td>
					<td><span style="color: #555; font-size: .85em;">Enable Twitter Subscriber Authentication</span></td>
				</tr>
				<tr valign="top">
					<th width="200px" scope="row">Twitter Id</th>
					<td width="100px"><input type="text" name="twitterlogin" id="twitterlogin_inp" value="<?php echo $optionarray_def['twitterlogin']; ?>" size="35" /></td>
					<td><span style="color: #555; font-size: .85em;">Your Twitter Login</span></td>
				</tr>

				<tr valign="top">
					<th width="200px" scope="row">Followers Only?</th>
					<td width="100px"><input name="followers_only" type="checkbox" id="followers_only_inp" value="1" <?php checked('1', $optionarray_def['followers_only']); ?>"  /></td>
					<td><span style="color: #555; font-size: .85em;">Choose between twitter login for Followers only</span></td>
				</tr>
			</table>
			</p>
			
			<h3>Oauth Settings</h3>
			<table width="100%" <?php $wpversion >= 2.5 ? _e('class="form-table"') : _e('cellspacing="2" cellpadding="5" class="editform"'); ?> >
				<tr valign="top">
					<th scope="row">CONSUMER KEY</th>
					<td><input type="text" name="consumer_key" id="consumer_key_inp" value="<?php echo $optionarray_def['consumer_key']; ?>" size="35" /></td>
					<td><span style="color: #555; font-size: .85em;">Enter consumer_key from twitter</span></td>
				</tr>
				<tr valign="top">
					<th scope="row">CONSUMER SECRET</th>
					<td><input type="text" name="consumer_secret" id="consumer_secret_inp" value="<?php echo $optionarray_def['consumer_secret']; ?>" size="35" /></td>
					<td><span style="color: #555; font-size: .85em;">Enter consumer_secret from twitter</span></td>
				</tr>

			</table>
		
			<h3>Advanced Settings</h3>
			<table width="100%" <?php $wpversion >= 2.5 ? _e('class="form-table"') : _e('cellspacing="2" cellpadding="5" class="editform"'); ?> >
				<tr valign="top">
					<th scope="row">Username Prefix</th>
					<td><input type="text" name="username_prefix" id="username_prefix_inp" value="<?php echo $optionarray_def['username_prefix']; ?>" size="35" /></td>
					<td><span style="color: #555; font-size: .85em;">Prefix for Twitter accounts</span></td>
				</tr>
				<tr valign="top">
					<th scope="row">Non-Followers Login Failure Message</th>
					<td><input type="text" name="followers_login_failure" id="followers_login_failure_inp" value="<?php echo $optionarray_def['followers_login_failure']; ?>" size="35" /></td>
					<td><span style="color: #555; font-size: .85em;">Text Message</span></td>
				</tr>

			</table>
		
			

			</fieldset>
		
			<p />
			<div class="submit">
				<input type="submit" name="submit" value="<?php _e('Update Options') ?> &raquo;" />
			</div>
			</form>	
<?php
}

function twitscription_login_message() {
	global $redirect_to;
	$message = "<center><a href=\"wp-content/plugins/twitscription/send.php?redirect_to=${redirect_to}\"><img src=\"wp-content/plugins/twitscription/images/lighter.png\"></a></center><br>";
	return $message;
}


function twitscription_login_errors() {
	$self = basename( $GLOBALS['pagenow'] );
		
	if ($self != 'wp-login.php') return;

	if ($_REQUEST['twitscription_error']) {
		global $error;
		$error = $_REQUEST['twitscription_error'];
	}
}


//--------------------------------------------------------------------------
//	Add Admin Page
//--------------------------------------------------------------------------

function twitscription_add_options_page()
{
	if (function_exists('add_options_page'))
	{
		add_options_page('Twitscription', 'Twitscription', 8, basename(__FILE__), 'twitscription_options_page');
	}
}


//----------------------------------------------------------------------------
//		WORDPRESS FILTERS AND ACTIONS
//----------------------------------------------------------------------------

add_action('admin_menu', 'twitscription_add_options_page');
twitscription_setup_options();

if($twitscription_opt['twitscription'] == TRUE ) {
	add_filter('login_message', 'twitscription_login_message');
	add_action( 'init', 'twitscription_login_errors' );
}


?>
