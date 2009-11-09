<?php
$eplug_admin = TRUE;
require_once ("../../class2.php");
if (!getperms("P"))
{
	header("location:".e_BASE."index.php");
	exit();
}

// version 0.8
/*if(!plugInstalled('facebook'))
{
	header("location:".e_BASE."index.php");
	exit();	
}*/



require_once (e_ADMIN."auth.php");

$fb = new facebook_admin;
	
	
if (isset($_POST['save-settings']))
{
 	$fb->save_settings();
	$fb->message = "<div style='text-align:center;padding:20px'>Preferences Updated!</div>";

}

$fb->pref_form();

require_once (e_ADMIN."footer.php");	


class facebook_admin
{
	
	var $message;
	
	function save_settings()
	{
		global $pref;
		$pref['Facebook_Api-Key'] = $_POST['Facebook_Api-Key'];
		$pref['Facebook_Secret-Key'] = $_POST['Facebook_Secret-Key'];
		$pref['Facebook_App-Bundle'] = $_POST['Facebook_App-Bundle'];
		save_prefs(); // uses toDB() automatically
	}
	
	
	
	
	function pref_form()
	{
		global $ns,$pref;
		
		$text = ""; // Remove Notice in PHP. 
		
	//	if (($pref['Facebook_Api-Key'] == '') || ($pref['Facebook_Secret-Key'] == ''))
		{
			$text .= $this->display_help();
		}
		
		$text .= '
	
		<form method="post" action="'.e_SELF.'" class="fborder admin-form">
		<div><img src="'.e_PLUGIN.'facebook/images/facebooklogo.gif" alt="" /> </div>
		<div style="clear:both"></div>
		<table cellpadding="0" cellspacing="0" class="adminform" >
		 <colgroup span="2" >
		  <col class = "col-label" style="width:30%" />
		  <col class = "col-control" style="width:70%" />
		  </colgroup >
		  <tr>
			<td class="forumheader3">Facebook Application ID</td>
			<td class="forumheader3">
			<input class="tbox" type="text"  id="Facebook_App-Bundle" name="Facebook_App-Bundle" value="'.$pref['Facebook_App-Bundle'].'" /> 
			</td>
		</tr>
		<tr>
			<td class="forumheader3">Facebook API Key</td> 
		  	<td class="forumheader3"> 
			<input class="tbox" type="text" id="Facebook_Api-Key" name="Facebook_Api-Key" value="'.$pref['Facebook_Api-Key'].'" />
		   	</td>
		</tr>
					
		<tr>
			<td class="forumheader3">Facebook Secret Key</td>
			<td class="forumheader3"> 
			<input class="tbox" type="text"  id="Facebook_Secret-Key" name="Facebook_Secret-Key" value="'.$pref['Facebook_Secret-Key'].'" />
			</td>
		</tr>
				

		
		<tr>
		<td class="forumheader buttons-bar" style="text-align:center" colspan="2">
		<input class="button" type="submit" name="save-settings" id="save-settings" value="Save Settings" />
		</td>
		</tr>
		</table></form>';
		
		$ns->tablerender("Facebook Connect :: General Settings", $this->message.$text);
	}



	
	function display_help()
	{
		global $ns,$pref;
		
		$text = '
		<div style="padding:20px">
		<h2>Setting Up Your Application and Getting an API Key</h2> 
		<table style="'.ADMIN_WIDTH.'">
		<tr>
	    <td> 			
			<p>If you don\'t already have a Facebook Platform API key for your site, create an application with the <a href="http://www.facebook.com/developers" class="external text" title="http://www.facebook.com/developers" rel="nofollow">Facebook Developer application</a>.
			</p><p><b>Note:</b> Even if you have created an application and received an API key, you should review steps 1.4 through 1.7 and make sure your application settings are appropriate.   
			</p> 
			
			<ol><li>1. Go to <a href="http://www.facebook.com/developers/createapp.php" class="external free" title="http://www.facebook.com/developers/createapp.php" rel="nofollow">http://www.facebook.com/developers/createapp.php</a> to create a new application.
			</li><li>2. Enter a name for your application in the <b>Application Name</b> field.
			</li><li>3. Accept the <a href="http://developers.facebook.com/terms.php" class="external text" title="http://developers.facebook.com/terms.php" rel="nofollow">Developer Terms of Service</a>, then click <b>Save Changes</b>.
			</li><li>4. On the <b>Basic</b> tab, keep all of the defaults.
			</li><li>5. Take note of the <b>API Key</b>, you\'ll need this shortly.<br/> 
			</li><li>6. Click the <b>Connect</b> tab. Set <b>Connect URL</b> to the top-level directory of the site where you plan to implement Facebook Connect (this is usually your domain, like <a href="http://www.example.com" class="external free" title="http://www.example.com" rel="nofollow">http://www.example.com</a>, but could also be a subdirectory).
			</li><li>7. You should include a logo that appears on the Facebook Connect dialog. Next to <b>Facebook Connect Logo</b>, click <b>Change your Facebook Connect logo</b> and browse to an image file. The logo can be up to 99 pixels wide by 22 pixels tall, and must be in JPG, GIF, or PNG format.
			</li><li>8. If you plan to implement Facebook Connect across a number of subdomains of your site (for example, foo.example.com and bar.example.com), you need to enter a <b><a href="/index.php/Base_Domain" title="Base Domain">Base Domain</a></b> (which would be example.com in this case). Specifying a base domain allows you to make calls using the <a href="/index.php/PHP" title="PHP">PHP</a> and <a href="/index.php/JavaScript_Client_Library" title="JavaScript Client Library">JavaScript</a> client libraries as well as get and store session information for any subdomain of the base domain. For more information about subdomains, see <a href="/index.php/Supporting_Subdomains_In_Facebook_Connect" title="Supporting Subdomains In Facebook Connect">Supporting Subdomains In Facebook Connect</a>.
			</li><li>9. Click <b>Save Changes</b>.
			</li></ol> 
		</td>
		</tr>
		 </table>
		 </div>';
		  
		return $text;
	}
}


?>