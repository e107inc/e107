<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/signup.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
if($pref['user_reg'][1] == 0){header("location:".e_HTTP."index.php"); exit; }

if(USER){header("location:".e_HTTP."index.php"); exit; }

if(e_QUERY != ""){
	$qs = explode(".", e_QUERY);
	if($qs[0] == "activate"){
		if($sql -> db_Select("user", "*", "user_sess='".$qs[2]."' ")){
			if($row = $sql -> db_Fetch()){
				$sql -> db_Update("user", "user_ban='0', user_sess='' WHERE user_sess='".$qs[2]."' ");
				require_once(HEADERF);
				$text = "Your account has now been activated, please log in from the login box.<br />Thankyou for registering at ".SITENAME;
				$ns -> tablerender("Registration activated", $text);
				require_once(FOOTERF);
				exit;
			}
		}else{
			header("location: ".e_HTTP."index.php");
			exit;
		}
	}
}

if(IsSet($_POST['register'])){
	require_once(e_BASE."classes/message_handler.php");
	$_POST['name'] = trim(chop($_POST['name']));
	if($_POST['name'] == "Anonymous"){
		message_handler("P_ALERT", LAN_104);
		$error = TRUE;
	}
	if($sql -> db_Select("user", "*", "user_name='".$_POST['name']."' ")){
		message_handler("P_ALERT", LAN_104);
		$error = TRUE;
	}	
	if($_POST['password1'] != $_POST['password2']){
		message_handler("P_ALERT", LAN_105);
		$error = TRUE;
	}
	if($_POST['name'] == "" || $_POST['password1'] =="" || $_POST['password2'] = ""){
		message_handler("P_ALERT", LAN_185);
		$error = TRUE;
	}
	 if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['email'])){
		 message_handler("P_ALERT", LAN_106);
		 $error = TRUE;
	 }
	 if (preg_match('#^www\.#si', $_POST['website'])) {
		$_POST['website'] = "http://$homepage";
	}else if (!preg_match('#^[a-z0-9]+://#si', $_POST['website'])){
		$_POST['website'] = ""; 
    }
	if(!$error){
		$fp = new floodprotect;
		if($fp -> flood("user", "user_join") == FALSE){
			header("location:index.php");
			exit;
		}

		if($sql -> db_Select("user", "*", "user_email='".$_POST['email']."' AND user_ban='1' ")){
			exit;
		}

		$username = strip_tags($_POST['name']);
		$time=time();	
		$ip = getip();

		if($pref['user_reg_veri'][1]){
			$key = md5(uniqid(rand(),1));
			$sql -> db_Insert("user", "0, '".$username."', '".md5($_POST['password1'])."', '$key', '".$_POST['email']."', 	'".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$_POST['birthday']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".$time."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '2', '0', '', '', '', '0', '".$_POST['realname']."', '', '', '', '' ");
			$sql -> db_Select("user", "*", "user_name='".$_POST['name']."' AND user_join='".$time."' ");
			$row = $sql -> db_Fetch();
			$id = $row['user_id'];
			
			$returnaddress = (substr(SITEURL, -1) == "/" ? SITEURL."signup.php?activate.".$id.".".$key : SITEURL."/signup.php?activate.".$id.".".$key);
			echo $returnaddress;
			exit;


			$message = "Welcome to ".SITENAME."\nYour registration has been received and created with the following login information ...\n\nUsername: ".$_POST['name']."\nPassword: ".$_POST['password1']."\n\nYour account is currently marked as being inactive, to activate your account please go to the following link ...\n\n".$returnaddress."\n\nPlease keep this email for your own information as your password has been encrypted and cannot be retrieved if you misplace or forget it. You can however request a new password if this happens.\n\nThanks for your registration.\n\nFrom ".SITENAME."\n".SITEURL;

			require_once("classes/mail.php");
			sendemail($_POST['email'], "Registration details for ".SITENAME, $message);

			require_once(HEADERF);
			$text = "This stage of registation is complete, you will be receiving a confirmation email containing your login details, please follow the link in the email to complete the signup process and activate your account.";
			$ns -> tablerender("<div style='text-align:center'>Thankyou!</div>", $text);
			require_once(FOOTERF);
			exit;
		}else{
			require_once(HEADERF);
			$sql -> db_Insert("user", "0, '".$username."', '".md5($_POST['password1'])."', '$key', '".$_POST['email']."', 	'".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$_POST['birthday']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".$time."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '0', '".$_POST['realname']."', '', '', '', '' ");
			$ns -> tablerender("<div style='text-align:center'>Thankyou!</div>", LAN_107);
			require_once(FOOTERF);
			exit;
		}
	}
}

require_once(HEADERF);
/*
if($error){
	$ns -> tablerender("<div style='text-align:center'>".LAN_20."</div>", $error);
	require_once(FOOTERF);
	exit;
}
*/
$qs = ($error ? "stage" : e_QUERY);

if($pref['use_coppa'][1] == 1 && !ereg("stage", $qs)){
	if(eregi("stage", LAN_109)){
		$text .= LAN_109."</b></div>";
	}else{
		$text .= LAN_109."<form method='post' action='signup.php?stage1'>
	<br />
	<input type='radio' name='coppa' value='0' checked> No
	<input type='radio' name='coppa' value='1'> Yes<br>
	<br />
	<input class='button' type='submit' name='newver' value='".LAN_399."' />
	</form>
	</div>";
	}

	$ns -> tablerender("<div style='text-align:center'>".LAN_110."</div>", $text);
	require_once(FOOTERF);
	exit;
}

if(!$website){
	$website = "http://";
}

if(!eregi("stage", LAN_109)){
	if(IsSet($_POST['newver'])){
		if(!$_POST['coppa']){
			$text = "Unable to proceed.";
			$ns -> tablerender("<div style='text-align:center'>Registration failed</div>", "<div style='text-align:center'>".$text."</div>");
			require_once(FOOTERF);
			exit;
		}
	}
}
$text .= "<div style='text-align:center'>";
if($pref['user_reg_veri'][1]){
	$text .=	LAN_309."<br /><br />";
}



$text .= LAN_400;

require_once(e_BASE."classes/form_handler.php");
$rs = new form;

$text .= $rs -> form_open("post", e_SELF, "signupform")."
<table style='width:60%'>
<tr>
<td style='width:30%'>".LAN_7."</td>
<td style='width:70%'>

".$rs -> form_text("name", 40, "", 100)."

</td>
</tr>

<tr>
<td style='width:30%'>".LAN_17."</td>
<td style='width:70%'>

".$rs -> form_password("password1", 40, "", 20)."


</td>
</tr>

<tr>
<td style='width:30%'>".LAN_111."</td>
<td style='width:70%'>

".$rs -> form_password("password2", 40, "", 20)."

</td>
</tr>

<tr>
<td style='width:30%'>".LAN_112."</td>
<td style='width:70%'>

".$rs -> form_text("email", 60, "", 100)."

</td>
</tr>

<tr>
<td style='width:30%'>".LAN_113."</td>
<td style='width:70%'>".




$rs ->form_radio("hideemail", 1)." Yes&nbsp;&nbsp;".$rs ->form_radio("hideemail", 0, 1)." No

</td>
</tr>
<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center'>
<br />
<input class='button' type='submit' name='register' value='".LAN_123."' />
<br />
</td>
</tr>
</table>
</form>
</div>
";

$ns -> tablerender(LAN_123, $text);

require_once(FOOTERF);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function timezone(){
	/*
	# Render style table
	# - parameters		none
	# - return				timezone arrays
	# - scope					public
	*/
	global $timezone, $timearea;
	$timezone = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "GMT", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
	$timearea = array("International DateLine West", "Samoa", "Hawaii", "Alaska", "Pacific Time (US and Canada)", "Mountain Time (US and Canada)", "Central Time (US and Canada), Central America", "Eastern Time (US and Canada)", "Atlantic Time (Canada)", "Greenland, Brasilia, Buenos Aires, Georgetown", "Mid-Atlantic", "Azores", "GMT - UK, Ireland, Lisbon", "West Central Africa, Western Europe", "Greece, Egypt, parts of Africa", "Russia, Baghdad, Kuwait, Nairobi", "Abu Dhabi, Kabul", "Islamabad, Karachi", "Astana, Dhaka", "Bangkok, Rangoon", "Hong Kong, Singapore, Perth, Beijing", "Tokyo, Seoul", "Brisbane, Canberra, Sydney, Melbourne", "Soloman Islands", "New Zealand", "Nuku'alofa");
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

?>
<script type="text/javascript">
function addtext(sc){
	document.signupform.image.value = sc;
}
</script>