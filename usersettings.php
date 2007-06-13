<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/usersettings.php,v $
|     $Revision: 1.9 $
|     $Date: 2007-06-13 22:13:58 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

require_once("class2.php");
require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."user_extended_class.php");
$ue = new e107_user_extended;

if (isset($_POST['sub_news']))
{
    header("location:".e_BASE."submitnews.php");
    exit;
}

if (isset($_POST['sub_link'])) {
    header("location:".e_PLUGIN."links_page/links.php?submit");
    exit;
}

if (isset($_POST['sub_download'])) {
    header("location:".e_BASE."upload.php");
    exit;
}

if (isset($_POST['sub_article'])) {
    header("location:".e_BASE."subcontent.php?article");
    exit;
}

if (isset($_POST['sub_review'])) {
    header("location:".e_BASE."subcontent.php?review");
    exit;
}

if (!USER) {
    header("location:".e_BASE."index.php");
    exit;
}

if (!ADMIN && e_QUERY && e_QUERY != "update") {
    header("location:".e_BASE."usersettings.php");
    exit;
}

require_once(e_HANDLER."ren_help.php");

if(is_readable(THEME."usersettings_template.php"))
{
	include_once(THEME."usersettings_template.php");
}
else
{
	include_once(e_THEME."templates/usersettings_template.php");
}
include_once(e_FILE."shortcode/batch/usersettings_shortcodes.php");

require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);
$_uid = is_numeric(e_QUERY) ? intval(e_QUERY) : "";

require_once(HEADERF);

// Save Form Data  --------------------------------------->

$error = "";

if (isset($_POST['updatesettings']))
{
	if(!varsettrue($pref['auth_method']) || $pref['auth_method'] == '>e107')
	{
		$pref['auth_method'] = 'e107';
	}

	if($pref['auth_method'] != 'e107')
	{
		$_POST['password1'] = '';
		$_POST['password2'] = '';
	}

	if ($_uid && ADMIN)
	{	// Admin logged in and editing another user's settings - so editing a different ID
		$inp = $_uid;
		$remflag = TRUE;
	}
	else
	{	// Current user logged in - use their ID
		$inp = USERID;
	}
	$_POST['image'] = str_replace(array('\'', '"', '(', ')'), '', $_POST['image']);   // these are invalid anyway, so why allow them? (XSS Fix)

	// check prefs for required fields =================================.

	if ($_POST['image'] && $size = getimagesize($_POST['image'])) {
		$avwidth = $size[0];
		$avheight = $size[1];
		$avmsg = "";

		$pref['im_width'] = ($pref['im_width']) ? $pref['im_width'] : 120;
		$pref['im_height'] = ($pref['im_height']) ? $pref['im_height'] : 100;
		if ($avwidth > $pref['im_width']) {
			$avmsg .= LAN_USET_1." ($avwidth)<br />".LAN_USET_2.": {$pref['im_width']}<br /><br />";
		}
		if ($avheight > $pref['im_height']) {
			$avmsg .= LAN_USET_3." ($avheight)<br />".LAN_USET_4.": {$pref['im_height']}";
		}
		if ($avmsg) {
			$_POST['image'] = "";
			$error = $avmsg;
		}

	}

	$signup_option_title = array(LAN_308, LAN_120, LAN_121, LAN_122, LAN_USET_6);
	$signup_option_names = array("realname", "signature", "image", "timezone", "class");

	foreach($signup_option_names as $key => $value)
	{  // Check required signup fields 
		if ($pref['signup_option_'.$value] == 2 && !$_POST[$value] && !$_uid)
		{
			$error .= LAN_SIGNUP_6.$signup_option_title[$key].LAN_SIGNUP_7."\\n";
		}
    }


// Login Name checks
	if (isset($_POST['loginname']))
	{  // Only check if its been edited
	  $temp_name = trim(preg_replace('/&nbsp;|\#|\=|\$/', "", strip_tags($_POST['loginname'])));
	  if ($temp_name != $_POST['loginname'])
	  {
		$error .= LAN_USET_13."\\n";
	  }
	  // Check if login name exceeds maximum allowed length
	  if (strlen($temp_name) > varset($pref['loginname_maxlength'],30))
	  {
	    $error .= LAN_USET_14."\\n";
	}
	  $_POST['loginname'] = $temp_name;
	}
	

// Password checks
	$pwreset = "";
	if ($_POST['password1'] != $_POST['password2']) {
		$error .= LAN_105."\\n";
	}
	else
	{
		if(trim($_POST['password1']) != "")
		{
			$pwreset = "user_password = '".md5(trim($_POST['password1']))."', ";
		}
	}

	if(isset($pref['signup_disallow_text']))
	{
		$tmp = explode(",", $pref['signup_disallow_text']);
		foreach($tmp as $disallow){
			if(strstr($_POST['username'], $disallow)){
				$error .= LAN_USET_11."\\n";
			}
		}
	}

	if (strlen(trim($_POST['password1'])) < $pref['signup_pass_len'] && trim($_POST['password1']) != "") {
		$error .= LAN_SIGNUP_4.$pref['signup_pass_len'].LAN_SIGNUP_5."\\n";
		$password1 = "";
		$password2 = "";
	}

	if (isset($pref['disable_emailcheck']) && $pref['disable_emailcheck']==1)
	{
	} else {
		if (!check_email($_POST['email']))
		{
	  		$error .= LAN_106."\\n";
		}
	}

	// Check for duplicate of email address
	if ($sql->db_Select("user", "user_name, user_email", "user_email='".$tp -> toDB($_POST['email'])."' AND user_id !='".$inp."' ")) 
	{
	  	$error .= LAN_408."\\n";
	}


// Display name checks
	if (isset($_POST['username']))
	{
	  // Impose a minimum length on display name
	  $username = trim(strip_tags($_POST['username']));
	  if (strlen($username) < 2)
	  {
		$error .= LAN_USET_12."\\n";
	  }
	  if (strlen($username) > varset($pref['displayname_maxlength'],15))
	  {
		$error .= LAN_USET_15."\\n";
	  }
	}


	$user_sess = "";
	if ($file_userfile['error'] != 4)
	{
		require_once(e_HANDLER."upload_handler.php");
		require_once(e_HANDLER."resize_handler.php");

		if ($uploaded = file_upload(e_FILE."public/avatars/", "avatar"))
		{
			if ($uploaded[0]['name'] && $pref['avatar_upload'])
			{
				// avatar uploaded
				$_POST['image'] = "-upload-".$uploaded[0]['name'];
				if (!resize_image(e_FILE."public/avatars/".$uploaded[0]['name'], e_FILE."public/avatars/".$uploaded[0]['name'], "avatar"))
				{
					unset($message);
					$error .= RESIZE_NOT_SUPPORTED."\\n";
					@unlink(e_FILE."public/avatars/".$uploaded[0]['name']);
				}
			}
			if ($uploaded[1]['name'] || (!$pref['avatar_upload'] && $uploaded[0]['name']))
			{
				// photograph uploaded
				$user_sess = ($pref['avatar_upload'] ? $uploaded[1]['name'] : $uploaded[0]['name']);
				resize_image(e_FILE."public/avatars/".$user_sess, e_FILE."public/avatars/".$user_sess, 180);
			}
		}
	}

	if ($user_sess != "")
	{
		$sesschange = "user_sess = '".$tp->toDB($user_sess)."', ";
	}

    // Validate Extended User Fields.
	if($_POST['ue'])
	{
		if($sql->db_Select('user_extended_struct'))	{
			while($row = $sql->db_Fetch())
			{
				$extList["user_".$row['user_extended_struct_name']] = $row;
			}
		}

		$ue_fields = "";
		foreach($_POST['ue'] as $key => $val)
		{
			$err = false;
			$parms = explode("^,^", $extList[$key]['user_extended_struct_parms']);
			$regex = $tp->toText($parms[1]);
			$regexfail = $tp->toText($parms[2]);
    		if(defined($regexfail)) {$regexfail = constant($regexfail);}
	  		if($val == '' && $extList[$key]['user_extended_struct_required'] == 1 && !$_uid)
			{
         		$error .= LAN_SIGNUP_6.($tp->toHtml($extList[$key]['user_extended_struct_text'],FALSE,"defs"))." ".LAN_SIGNUP_7."\\n";
	    		$err = TRUE;
			}
			if($regex != "" && $val != "")
			{
				if(!preg_match($regex, $val))
				{
               		$error .= $regexfail."\\n";
         			$err = TRUE;
	         	}
			}
			if(!$err)
			{
				$val = $tp->toDB($val);
				$ue_fields .= ($ue_fields) ? ", " : "";
				$ue_fields .= $key."='".$val."'";
				}
		}
    }



	if (!$error)
	{
		unset($_POST['password1']);
		unset($_POST['password2']);

		$ret = $e_event->trigger("preuserset", $_POST);
		if(trim($_POST['user_xup']) != "")
		{
			if($sql->db_Select('user', 'user_xup', "user_id = '".intval($inp)."'"))
			{
				$row = $sql->db_Fetch();
				$update_xup = ($row['user_xup'] != $_POST['user_xup']) ? TRUE : FALSE;
			}
		}

		if ($ret == '')
		{
			$udata = get_user_data($inp);
			$peer = ($inp == USERID ? false : true);
			$loginname = strip_tags($_POST['loginname']);

			if (!$loginname)
			{
				$sql->db_Select("user", "user_loginname", "user_id='".intval($inp)."'");
				$row = $sql -> db_Fetch();
				$loginname = $row['user_loginname'];
			}
			else
			{
				if(!check_class($pref['displayname_class'], $udata['user_class'], $peer))
				{
					$new_username = "user_name = '{$loginname}', ";
					$username = $loginname;
				}
			}

			if (isset($_POST['username']) && check_class($pref['displayname_class']))
			{
				$username = strip_tags($_POST['username']);
				$username = $tp->toDB(substr($username, 0, $pref['displayname_maxlength']));
				$new_username = "user_name = '{$username}', ";
			}


			$_POST['signature'] = $tp->toDB($_POST['signature']);
			$_POST['realname'] = $tp->toDB($_POST['realname']);

			$new_customtitle = "";

			if(isset($_POST['customtitle']) && ($pref['forum_user_customtitle'] || ADMIN))
			{
				$new_customtitle = ", user_customtitle = '".$tp->toDB($_POST['customtitle'])."' ";
			}


			if($ue_fields)
			{
				$hidden_fields = implode("^", array_keys($_POST['hide']));
				if($hidden_fields != "")
				{
					$hidden_fields = "^".$hidden_fields."^";
				}
				$ue_fields .= ", user_hidden_fields = '".$hidden_fields."'";
			}

			$sql->db_Update("user", "{$new_username} {$pwreset} {$sesschange} user_email='".$tp -> toDB($_POST['email'])."', user_signature='".$_POST['signature']."', user_image='".$tp -> toDB($_POST['image'])."', user_timezone='".$tp -> toDB($_POST['timezone'])."', user_hideemail='".$tp -> toDB($_POST['hideemail'])."', user_login='".$_POST['realname']."' {$new_customtitle}, user_xup='".$tp -> toDB($_POST['user_xup'])."' WHERE user_id='".intval($inp)."' ");
			// If user has changed display name, update the record in the online table
			if(isset($username) && ($username != USERNAME) && !$_uid)
			{
				$sql->db_Update("online", "online_user_id = '".USERID.".".$username."' WHERE online_user_id = '".USERID.".".USERNAME."'");
			}

			if(ADMIN && getperms("4"))
			{
				$sql -> db_Update("user", "user_loginname='".$tp -> toDB($loginname)."' WHERE user_id='".intval($inp)."' ");
			}

			if($ue_fields)
			{
				$sql->db_Select_gen("INSERT INTO #user_extended (user_extended_id, user_hidden_fields) values ('".intval($inp)."', '')");
				$sql->db_Update("user_extended", $ue_fields." WHERE user_extended_id = '".intval($inp)."'");
			}

			// Update Userclass =======
			if (!$_uid && $sql->db_Select("userclass_classes", "*", "userclass_editclass IN (".USERCLASS_LIST.")"))
			{
				$ucList = $sql->db_getList();
				if ($sql->db_Select("user", "user_class", "user_id = '".intval($inp)."'"))
				{
					$row = $sql->db_Fetch();
					$cur_classes = explode(",", $row['user_class']);
					$newclist = array_flip($cur_classes);
					foreach($ucList as $c)
					{
						$cid = $c['userclass_id'];
						if(!in_array($cid, $_POST['class']))
						{
							unset($newclist[$cid]);
						}
						else
						{
							$newclist[$cid] = 1;
						}
					}
					$newclist = array_keys($newclist);
					$nid = implode(',', array_diff($newclist, array('')));
					$sql->db_Update("user", "user_class='".$nid."' WHERE user_id='".intval($inp)."'");
				}
			}

			if($update_xup == TRUE)
			{
				require_once(e_HANDLER."login.php");
				userlogin::update_xup($inp, $_POST['user_xup']);
			}

			$e_event->trigger("postuserset", $_POST);

			// =======================

			if(e_QUERY == "update") {
            	header("Location: index.php");
			}
			$message = "<div style='text-align:center'>".LAN_150."</div>";
			$caption = LAN_151;
		} else {
			$message = "<div style='text-align:center'>".$ret."</div>";
			$caption = LAN_151;
		}
		unset($_POST);
	}
}

if ($error)
{
	require_once(e_HANDLER."message_handler.php");
	message_handler("P_ALERT", $error);
	$adref = $_POST['adminreturn'];
}
// -------------------

if(isset($message))
{
	$ns->tablerender($caption, $message);
}

// ---------------------


$uuid = ($_uid) ? $_uid : USERID;

$qry = "
SELECT u.*, ue.* FROM #user AS u
LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id
WHERE u.user_id='".intval($uuid)."'
";

$sql->db_Select_gen($qry);
$curVal=$sql->db_Fetch();
$tmp = ($curVal['user_class'] != "" ? explode(",", $curVal['user_class']) : "");
$tmp[] = e_UC_MEMBER;
$tmp[] = e_UC_READONLY;
$tmp[] = e_UC_PUBLIC;
if($curVal['user_admin'] == 1)
{
	$tmp[] = e_UC_ADMIN;
}
$curVal['userclass_list'] = implode(",", $tmp);

if($_POST)
{     // Fix for all the values being lost when an error occurred.
	foreach($_POST as $key => $val)
	{
		$curVal["user_".$key] = $val;
	}
	foreach($_POST['ue'] as $key => $val)
	{
		$curVal[$key] = $val;
	}
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

$text = (e_QUERY ? $rs->form_open("post", e_SELF."?".e_QUERY, "dataform", "", " enctype='multipart/form-data'") : $rs->form_open("post", e_SELF, "dataform", "", " enctype='multipart/form-data'"));

if(e_QUERY == "update")
{
	$text .= "<div class='fborder' style='text-align:center'><br />".str_replace("*","<span style='color:red'>*</span>",LAN_USET_9)."<br />".LAN_USET_10."<br /><br /></div>";
}

$text .= $tp->parseTemplate($USERSETTINGS_EDIT, TRUE, $usersettings_shortcodes);
$text .= "<div>";

$text .= "
	<input type='hidden' name='_uid' value='$_uid' />
	</div>
	</form>
	";

$ns->tablerender(LAN_155, $text);
require_once(FOOTERF);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function req($field) {
	global $pref;
	if ($field == 2)
	{
		$ret = "<span style='text-align:right;font-size:15px; color:red'> *</span>";
	}
	else
	{
		$ret = "";
	}
	return $ret;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function headerjs() {
	global $cal;
	$script = "<script type=\"text/javascript\">
		function addtext_us(sc){
		document.getElementById('dataform').image.value = sc;
		}

		</script>\n";

	$script .= $cal->load_files();
	return $script;
}
?>
