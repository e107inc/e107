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
if($pref['user_reg'] == 0){header("location:".e_BASE."index.php"); exit; }

if(USER){header("location:".e_BASE."index.php"); exit; }

if(e_QUERY){
	$qs = explode(".", e_QUERY);
	if($qs[0] == "activate"){
		if($sql -> db_Select("user", "*", "user_sess='".$qs[2]."' ")){
			if($row = $sql -> db_Fetch()){
				$sql -> db_Update("user", "user_ban='0', user_sess='' WHERE user_sess='".$qs[2]."' ");
				require_once(HEADERF);
				$text = LAN_401." ".SITENAME;
				$ns -> tablerender(LAN_402, $text);
				require_once(FOOTERF);
				exit;
			}
		}else{
			header("location: ".e_BASE."index.php");
			exit;
		}
	}
}

if(IsSet($_POST['register'])){
	require_once(e_HANDLER."message_handler.php");

	if(strstr($_POST['name'], "#") || strstr($_POST['name'], "=")){
		message_handler("P_ALERT", LAN_409);
		$error = TRUE;
	}

	$_POST['name'] = trim(chop(ereg_replace("&nbsp;|\#|\=", "", $_POST['name'])));
	if($_POST['name'] == "Anonymous"){
		message_handler("P_ALERT", LAN_103);
		$error = TRUE;
	}
	if(strlen($_POST['name']) > 30){ exit; }
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

	if($sql -> db_Select("user", "user_email", "user_email='".$_POST['email']."' ")){
		message_handler("P_ALERT", LAN_408);
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

		$wc = "*".substr($_POST['email'], strpos($_POST['email'], "@"));
		if($sql -> db_Select("banlist", "*", "banlist_ip='".$_POST['email']."' OR banlist_ip='$wc'")){
			exit;
		}


		$username = strip_tags($_POST['name']);
		$time=time();	
		$ip = getip();

		if($pref['user_reg_veri']){
			$key = md5(uniqid(rand(),1));
			$sql -> db_Insert("user", "0, '".$username."', '".md5($_POST['password1'])."', '$key', '".$_POST['email']."', 	'".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$_POST['birthday']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".$time."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '2', '0', '', '', '', '0', '".$_POST['realname']."', '', '', '', '' ");
			$sql -> db_Select("user", "*", "user_name='".$_POST['name']."' AND user_join='".$time."' ");
			$row = $sql -> db_Fetch();
			$id = $row['user_id'];
			
			define("RETURNADDRESS", (substr(SITEURL, -1) == "/" ? SITEURL."signup.php?activate.".$id.".".$key : SITEURL."/signup.php?activate.".$id.".".$key));
			
			$message = LAN_403.RETURNADDRESS.LAN_407." ".SITENAME."\n".SITEURL;
			require_once(e_HANDLER."mail.php");
			sendemail($_POST['email'], LAN_404." ".SITENAME, $message);
			require_once(HEADERF);
			$text = LAN_405;
			$ns -> tablerender("<div style='text-align:center'>".LAN_406."</div>", $text);
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

$qs = ($error ? "stage" : e_QUERY);

if($pref['use_coppa'] == 1 && !ereg("stage", $qs)){
	if(eregi("stage", LAN_109)){
		$text .= LAN_109."</b></div>";
	}else{
		$text .= LAN_109."<form method='post' action='signup.php?stage1'>
	<br />
	<input type='radio' name='coppa' value='0' checked> ".LAN_200."
	<input type='radio' name='coppa' value='1'> ".LAN_201."<br>
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
			$ns -> tablerender("<div style='text-align:center'>".LAN_202."</div>", "<div style='text-align:center'>".$text."</div>");
			require_once(FOOTERF);
			exit;
		}
	}
}
$text .= "<div style='text-align:center'>";
if($pref['user_reg_veri']){
	$text .=	LAN_309."<br /><br />";
}

$text .= LAN_400;
require_once(e_HANDLER."form_handler.php");
$rs = new form;
$text .= $rs -> form_open("post", e_SELF, "signupform")."
<table style='width:60%'>
<tr>
<td style='width:30%'>".LAN_7."</td>
<td style='width:70%'>
".$rs -> form_text("name", 40, "", 30)."
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
$rs ->form_radio("hideemail", 1)." Yes&nbsp;&nbsp;".$rs ->form_radio("hideemail", 0, 1)." ".LAN_200."
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