<?php
if (!defined('e107_INIT'))
{
	exit;
}

/**
 * we need a full wide page, no sidebar,
 * where place the Facebook Invite Friends Flash Object,
 * this Object can't be styled so... put it in CUSTOMPAGES
 *
 * but we can use a popup page instead ?
 */

if (USER_AREA == TRUE)
{
	//TODO use popup window for 'invite friends'.
	// $CUSTOMPAGES = array_push(explode(' ', $CUSTOMPAGES), ' facebook.php');	
	
	$fb = e107::getSingleton('e_facebook',e_PLUGIN.'facebook/facebook_function.php');
	
	include_once (e_PLUGIN.'facebook/facebook_function.php');
	
	//echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.0.3/prototype.js"></script>';
	
	echo '<link rel="stylesheet" href="'.e_PLUGIN.'facebook/facebook.css" type="text/css" />';
	
	/**
	 * if we are in comment.php page add "publish_to_facebook" checkbox to the form
	 *
	 */
	
	if (((e_PAGE == "comment.php") || (eregi('extend', e_QUERY))) && ($fb->fb_uid))
	{
		
		echo '<script type="text/javascript">
	document.observe("dom:loaded", function() {

	var commentbutton = document.getElementsByName("commentsubmit")[0];
  
  commentbutton.insert( {"after" : " <img src=\"http://static.ak.fbcdn.net/images/icons/favicon.gif\" /><input type=\"checkbox\" name=\"publish_to_facebook\" checked /> Publish Comment to Facebook"}) }); </script>';
		
		onloadRegister('facebook_show_feed_checkbox();');
		
		/**
		 * Simple Pure Javascript code , do same thing of prototype!
		 *
		 */
		
		/*
		 echo '
		 var commentbutton = document.getElementsByName("commentsubmit")[0];
		 
		 var checkbox = document.createElement("input");
		 checkbox.type = "checkbox";
		 checkbox.name = "publish_to_facebook";
		 checkbox.defaultChecked = true;
		 
		 var txt = document.createTextNode("Publish Comment to Facebook");
		 
		 var img = document.createElement("img");
		 img.setAttribute("src", "http://static.ak.fbcdn.net/images/icons/favicon.gif");
		 img.setAttribute("alt", "");
		 img.setAttribute("style", "padding-left:5px");
		 
		 commentbutton.parentNode.insertBefore(img,commentbutton.nextSibling);
		 img.parentNode.insertBefore(checkbox,img.nextSibling);
		 checkbox.parentNode.insertBefore(txt,checkbox.nextSibling);
		 ';
		 
		 */

		/**
		 * if we are in the signup page add the Facebook Connect Button
		 *
		 */
	
	}
	elseif (e_PAGE == "signup.php")
	{
		echo '<script type="text/javascript">
		document.observe("dom:loaded", function() {
		$("signupform").insert(
		  {"before" : "<center>OR | Login by using Facebook<br /><br /><a href=\"#\" onclick=\"FB.Connect.requireSession(); return false;\" ><img id=\"fb_login_image\" src=\"http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif\" alt=\"Connect\"/></a><fb:login-button size=\"medium\" background=\"light\" length=\"long\" onlogin=\"facebook_onlogin_ready();\"></fb:login-button><br /><br /></center>"})
		  
		});	
	</script>';
	}

}
?>