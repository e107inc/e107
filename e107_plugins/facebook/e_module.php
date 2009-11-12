<?php
//error_reporting(E_ALL);




if (e_ADMIN_AREA !== TRUE)
{

	
	
	e107::getEvent()->register('logout',array('e_facebook','fb_logout'),e_PLUGIN.'facebook/facebook_function.php');
	global $fb;
	
	$fb = e107::getSingleton('e_facebook',e_PLUGIN.'facebook/facebook_function.php');
	include_once (e_PLUGIN.'facebook/facebook_function.php');
	
	if (function_exists('prevent_cache_headers'))
	{
		prevent_cache_headers();
	}

	define(XMLNS, "xmlns:fb='http://www.facebook.com/2008/fbml'");
	
	global $pref;
	
	if ($pref['disable_emailcheck'] == 0) // Ensure "Make entering an email address optional" is setted to "ON";
	{
		$pref['disable_emailcheck'] = 1;
		save_prefs();
	}
	
	if (e_QUERY == 'facebook') // when clicked it inserts a new User in e107.
	{
		Fb_Connect_Me();
	}
	
	if (e_QUERY == 'login') // simple Re-Login after logged out from e107
	{
		$fb->fb_login(); // Fb_LogIn();
	}
		
	if (e_QUERY == 'logout') // simulate Facebook logOut when logged out from e107
	{
		// Fb_LogOut();
	}
	
	if (e_QUERY == 'facebook_switch')
	{
		Switch_Facebook_User();
	}

	if (USERID &&  (e_QUERY == 'facebook_link') && $fb->fb_uid) //  
	{
		// $fb->Add_Facebook_Connect_User('', USERID);
		$fb->addFacebookUser();
	}
	
	if (e_QUERY == 'facebook_delete')
	{
		
		Delete_Duplicate_Facebook_User();
	
	}


	function theme_foot()
	{
		global $fb;
		/**
		 * the init js needs to be at the bottom of the document, within the </body> tag
		 * this is so that any xfbml elements are already rendered by the time the xfbml
		 * rendering takes over. otherwise, it might miss some elements in the doc.
		 *
		 */
		
		global $onload_js;
		
		$text .= render_facebook_init_js($fb->fb_uid);
		// Print out all onload function calls
		
		if ($onload_js)
		{
			
			$text .= '<script type="text/javascript">'.'window.onload = function() { '.$onload_js.' };'.'</script>';
		
		}
		return $text;
	
	}
	
	/**
	 *
	 * Facebook Deprecated get Feed Story trough Template Bundle 2009
	 *
	 
	 function getTemplateData() {
	 
	 $template_data = array(
	 'post_title' => $_POST[ 'subject' ],
	 'body' => $_POST[ 'comment' ],
	 'body_short' => $_POST[ 'comment' ],
	 'post_permalink' => e_SELF,
	 'blogname' => SITENAME,
	 'blogdesc' => SITEDESCRIPTION,
	 'siteurl' => SITEURLBASE);
	 
	 return $template_data;
	 }
	 
	 */
	 
	 
	 /**
	 * get Feed Story infos to send to Facebook
	 *
	 * the new way FB.Connect.streamPublish();
	 *
	 */


	function getStreamToPublish()
	{
		//global $pref;
		//$stream = facebook_client()->api_client->stream_get('','','','','',''.$pref[ 'Facebook_App-Bundle' ].'','');
		
		// $stream = facebook_client()->api_client->stream_publish($_POST[ 'comment' ]);
		
		return $_POST['comment'];
	}
	
	/**
	 * if comment is submitted and "publish_to_facebook" is checked send a copy to Facebook
	 *
	 */
	
	if (isset($_POST['commentsubmit']) && ($_POST['publish_to_facebook'] == true))
	{
		
		register_feed_form_js();
	
	}

}
?>