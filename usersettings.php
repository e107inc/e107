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
|     $Source: /cvs_backup/e107_0.7/usersettings.php,v $
|     $Revision: 1.40 $
|     $Date: 2005-07-02 16:18:14 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

require_once("class2.php");
require_once(e_HANDLER."user_extended_class.php");

$ue = new e107_user_extended;

if (isset($_POST['sub_news'])) {
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
if (!USER && !ADMIN) {
    header("location:".e_BASE."index.php");
    exit;
}
require_once(e_HANDLER."ren_help.php");

if (e_QUERY && !ADMIN) {
    header("location:".e_BASE."usersettings.php");
    exit;
}

require_once(e_HANDLER."calendar/calendar_class.php");
$cal = new DHTML_Calendar(true);
$_uid = is_numeric(e_QUERY) ? intval(e_QUERY) : "";

if(getperms("4") && eregi(str_replace("../","",e_ADMIN),$_SERVER['HTTP_REFERER']) || $_POST['adminmode'] == 1)
{
	require_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_users.php");

	function usersettings_adminmenu()
	{
        $action = "main";
    	// ##### Display options ---------------------------------------------------------------------------------------------------------
		$var['main']['text'] = USRLAN_71;
		$var['main']['link'] = e_ADMIN."users.php";

		$var['create']['text'] = USRLAN_72;
		$var['create']['link'] = e_ADMIN."users.php?create";

		$var['prune']['text'] = USRLAN_73;
		$var['prune']['link'] = e_ADMIN."users.php?prune";

		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_ADMIN."users.php?options";

	  	show_admin_menu(USRLAN_76, $action, $var);
	}
	$ADMINAREA = TRUE;
}

$signupval = explode(".", $pref['signup_options']);

// Save Form Data  --------------------------------------->

if (isset($_POST['updatesettings']))
{
	if ($_uid && ADMIN)
	{
		$inp = $_uid;
		$remflag = TRUE;
	}
	else
	{
		$inp = USERID;
	}

	$_POST['image'] = str_replace(array('\'', '"', '(', ')'), '', $_POST['image']);   // these are invalid anyways, so why allow them? (XSS Fix)
	// check prefs for required fields =================================.

	$signup_title = array(LAN_308, LAN_120, LAN_121, LAN_122);
	$signup_name = array("realname", "signature", "image", "user_timezone");

	if ($_POST['image'] && $size = getimagesize($_POST['image'])) {
		$avwidth = $size[0];
		$avheight = $size[1];
		$avmsg = "";

		$pref['im_width'] = ($pref['im_width']) ? $pref['im_width'] : 120;
		$pref['im_height'] = ($pref['im_height']) ? $pref['im_height'] : 100;
		if ($avwidth > $pref['im_width']) {
			$avmsg .= LAN_USET_1."<br />".LAN_USET_2.": {$pref['im_width']}<br /><br />";
		}
		if ($avheight > $pref['im_height']) {
			$avmsg .= LAN_USET_3."<br />".LAN_USET_4.": {$pref['im_height']}";
		}
		if ($avmsg) {
			$_POST['image'] = "";
			$messsage = $avmsg;
		}
	}

	for ($i = 0; $i < count($signup_title); $i++)
	{
		$postvalue = $signup_name[$i];
		if ($signupval[$i] == 2 && $_POST[$postvalue] == "" && !$_uid)
		{
			$error .= LAN_SIGNUP_6.$signup_title[$i].LAN_SIGNUP_7."\\n";
		}
	}

	if($sql->db_Select('user_extended_struct'))	{
		while($row = $sql->db_Fetch()) {
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
		if($val == '' && $extList[$key]['user_extended_struct_required'] == TRUE && !$_uid)
		{
			$error .= LAN_SIGNUP_6.substr($key,5)." ".LAN_SIGNUP_7."\\n";
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
	if($ue_fields)
	{
		$hidden_fields = implode("^", array_keys($_POST['hide']));
		if($hidden_fields != "")
		{
			$hidden_fields = "^".$hidden_fields."^";
		}
		$ue_fields .= ", user_hidden_fields = '".$hidden_fields."'";
	}

	// ====================================================================

	if ($_POST['password1'] != $_POST['password2']) {
		$error .= LAN_105."\\n";
	}

	if (strlen($_POST['password1']) < $pref['signup_pass_len'] && $_POST['password1'] != "") {
		$error .= LAN_SIGNUP_4.$pref['signup_pass_len'].LAN_SIGNUP_5."\\n";
		$password1 = "";
		$password2 = "";
	}

	if ($_POST['password1'] == "" || $_POST['password2'] == "") {
		$password = $_POST['_pw'];
	} else {
		$password = md5($_POST['password1']);
	}

	if (!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['email'])) {
	  	$error .= LAN_106."\\n";
	}

	if ($sql->db_Select("user", "user_name, user_email", "user_email='".$_POST['email']."' AND user_id !=".USERID."' ")) {
	  	$error .= LAN_408."\\n";
	}

	$username = strip_tags($_POST['username']);
	$loginname = strip_tags($_POST['loginname']);
	if (!$loginname) {
		$row = $sql -> db_Fetch();
		$loginname = $row['user_name'];
	}
	if ($file_userfile['error'] != 4) {
		require_once(e_HANDLER."upload_handler.php");
		require_once(e_HANDLER."resize_handler.php");
		if ($uploaded = file_upload(e_FILE."public/avatars/", "avatar")) {
			if ($uploaded[0]['name'] && $pref['avatar_upload']) {
				// avatar uploaded
				$_POST['image'] = "-upload-".$uploaded[0]['name'];
				if (!resize_image(e_FILE."public/avatars/".$uploaded[0]['name'], e_FILE."public/avatars/".$uploaded[0]['name'], "avatar")) {
					unset($message);
					$error .= RESIZE_NOT_SUPPORTED."\\n";
					@unlink(e_FILE."public/avatars/".$uploaded[0]['name']);
				}
			}
			if ($uploaded[1]['name'] || (!$pref['avatar_upload'] && $uploaded[0]['name'])) {
				// photograph uploaded
				$user_sess = ($pref['avatar_upload'] ? $uploaded[1]['name'] : $uploaded[0]['name']);
				resize_image(e_FILE."public/avatars/".$user_sess, e_FILE."public/avatars/".$user_sess, 180);
			}
		}
	}

	if (!$user_sess)
	{
		$user_sess = $_POST['_user_sess'];
	}

	if (!$error)
	{
		$_POST['signature'] = $tp->toDB($_POST['signature']);
		$_POST['realname'] = $tp->toDB($_POST['realname']);

		$new_customtitle = "";

		if(isset($_POST['customtitle']) && ($pref['forum_user_customtitle'] || ADMIN))
		{
			$new_customtitle = ", user_customtitle = '".$tp->toDB($_POST['customtitle'])."' ";
		}
		unset($_POST['password1']);
		unset($_POST['password2']);

		$ret = $e_event->trigger("preuserset", $_POST);
		if(trim($_POST['user_xup']) != "")
		{
			if($sql->db_Select('user', 'user_xup', "user_id = '{$inp}'"))
			{
				$row = $sql->db_Fetch();
				$update_xup = ($row['user_xup'] != $_POST['user_xup']) ? TRUE : FALSE;
			}
		}

		if ($ret=='')
		{
			$sql->db_Update("user", "user_name='$username', user_password='$password', user_sess='$user_sess', user_email='".$_POST['email']."', user_signature='".$_POST['signature']."', user_image='".$_POST['image']."', user_timezone='".$_POST['user_timezone']."', user_hideemail='".$_POST['hideemail']."', user_login='".$_POST['realname']."' {$new_customtitle}, user_xup='".$_POST['user_xup']."' WHERE user_id='".$inp."' ");

			if(ADMIN && getperms("4")){
				$sql -> db_Update("user", "user_loginname='$loginname' WHERE user_id='$inp' ");
			}

			if($ue_fields) {
				$sql->db_Select_gen("INSERT INTO #user_extended (user_extended_id) values ('{$inp}')");
				$sql->db_Update("user_extended", $ue_fields." WHERE user_extended_id = '{$inp}'");
			}

			// Update Userclass =======
			if ($_POST['usrclass']) {
				if(is_array($_POST['usrclass'])) {
					if(count($_POST['usrclass'] == 1)) {
						$nid = $_POST['usrclass'][0];
					} else {
						$nid = explode(',',array_dif($_POST['usrclass'],array('')));
					}
				} else {
					$nid = $_POST['usrclass'];
				}
				$sql->db_Update("user", "user_class='$nid' WHERE user_id='".USERID."' ");
			}

			if($update_xup == TRUE)
			{
				require_once(e_HANDLER."login.php");
				userlogin::update_xup($inp, $_POST['user_xup']);
			}

			$e_event->trigger("postuserset", $_POST);

			// =======================

			if(e_QUERY == "update") {
            	Header("Location: index.php");
			}
			if($ADMINAREA && !$error) {
            	Header("Location: ".$_POST['adminreturn']);
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
// -------------------

if($ADMINAREA)
{
  	require_once(e_ADMIN."auth.php");
}
else
{
	require_once(HEADERF);
}

if(isset($message))
{
	$ns->tablerender($caption, $message);
}

// ---------------------
if ($error)
{
	require_once(e_HANDLER."message_handler.php");
	message_handler("P_ALERT", $error);
	$adref = $_POST['adminreturn'];
}

$uuid = ($_uid) ? $_uid : USERID;

$qry = "
SELECT u.*, ue.* FROM #user AS u
LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id
WHERE u.user_id='{$uuid}'
";

$sql->db_Select_gen($qry);
$curVal=$sql->db_Fetch();

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

$text .= "<div style='text-align:center'>
	<table style='width:auto' class='fborder'>

	<tr>
	<td colspan='2' class='forumheader'>".LAN_418."</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_7."<br /><span class='smalltext'>".LAN_8."</span></td>
	<td style='width:60%' class='forumheader2'>". $rs->form_text("username", 20, $curVal['user_name'], 100, "tbox")."</td>
	</tr>
	";

	if (ADMIN && getperms("4")) {
		$text .= "<tr>
		<td style='width:40%' class='forumheader3'>".LAN_9."<br /><span class='smalltext'>".LAN_10."</span></td>
		<td style='width:60%' class='forumheader2'>". $rs->form_text("loginname", 20, $curVal['user_loginname'], 100, "tbox")."</td>
		</tr>
		";
	} else {
		$text .= "<tr>
		<td style='width:40%' class='forumheader3'>".LAN_9."<br /><span class='smalltext'>".LAN_11."</span></td>
		<td style='width:60%' class='forumheader2'>". $curVal['user_loginname']."</td>
		</tr>
		";
	}

	$text .= "<tr>
	<td style='width:40%' class='forumheader3'>".LAN_308.req($signupval[0])."</td>
	<td style='width:60%' class='forumheader2'>
	".$rs->form_text("realname", 40, $curVal['user_login'], 100)."
	</td>
	</tr>";
if ($pref['forum_user_customtitle'] || ADMIN) {
	$text .= "
		<tr>
		<td style='width:40%' class='forumheader3'>".LAN_CUSTOMTITLE."</td>
		<td style='width:60%' class='forumheader2'>
		".$rs->form_text("customtitle", 40, $curVal['user_customtitle'], 100)."
		</td>
		</tr>";
}

$text .= "
	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_152."<br /><span class='smalltext'>".LAN_401."</span></td>
	<td style='width:60%' class='forumheader2'>
	".$rs->form_password("password1", 40, "", 20);
if ($pref['signup_pass_len']) {
	$text .= "<br /><span class='smalltext'>  (".LAN_SIGNUP_1." {$pref['signup_pass_len']} ".LAN_SIGNUP_2.")</span>";
}
$text .= "
	</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_153."<br /><span class='smalltext'>".LAN_401."</span></td>
	<td style='width:60%' class='forumheader2'>
	".$rs->form_password("password2", 40, "", 20)."
	</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_112."</td>
	<td style='width:60%' class='forumheader2'>
	".$rs->form_text("email", 40, $curVal['user_email'], 100)."
	</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_113."<br /><span class='smalltext'>".LAN_114."</span></td>
	<td style='width:60%' class='forumheader2'><span class='defaulttext'>". ($curVal['user_hideemail'] ? $rs->form_radio("hideemail", 1, 1)." ".LAN_416."&nbsp;&nbsp;".$rs->form_radio("hideemail", 0)." ".LAN_417 : $rs->form_radio("hideemail", 1)." ".LAN_416."&nbsp;&nbsp;".$rs->form_radio("hideemail", 0, 1)." ".LAN_417)."</span>
	<br />
	</td>
	</tr>";


// -------------------------------------------------------------
// public userclass subcription.
if ($sql->db_Select("userclass_classes", "*", "userclass_editclass =0"))
{
	$hide = "";
	$text .= "
		<tr>
		<td style='width:40%;vertical-align:top' class='forumheader3'>".LAN_USET_5.":
		<br /><span class='smalltext'>".LAN_USET_6."</span>
		</td>
		<td style='width:60%' class='forumheader2'>";
	$text .= "<table style='width:95%'>";
	$sql->db_Select("userclass_classes", "*", "userclass_id !='' order by userclass_name");
	while ($row3 = $sql->db_Fetch())
	{
		if ($row3['userclass_editclass'] == 0)
		{
			$inclass = check_class($row3['userclass_id'], $curVal['user_class']) ? "checked='checked'" : "";
			if(isset($_POST))
			{
				$inclass = in_array($row3['userclass_id'], $_POST['usrclass']);
			}
			$frm_checked = $inclass ? "checked='checked'" : "";
			$text .= "<tr><td class='defaulttext'>";
			$text .= "<input type='checkbox' name='usrclass[]' value='{$row3['userclass_id']}' $frm_checked />\n";
			$text .= $tp->toHTML($row3['userclass_name'],"","defs")."</td>";
			$text .= "<td class='smalltext'>".$tp->toHTML($row3['userclass_description'],"","defs")."</td>";
			$text .= "</tr>\n";
		}
		else
		{
			$hide .= check_class($row3['userclass_id'], $curVal['user_class']) ? "<input type='hidden' name='usrclass[]' value='{$row3['userclass_id']}' />\n" : "";
		}
	}
	$text .= "</table>\n";
	$text .= $hide;
	$text .= "</td></tr>\n";
}

// ---------------------------------------------------

	$qry = "
	SELECT f.*, c.user_extended_struct_name AS category_name, c.user_extended_struct_id AS category_id FROM #user_extended_struct as f
	LEFT JOIN #user_extended_struct as c ON f.user_extended_struct_parent = c.user_extended_struct_id
	WHERE f.user_extended_struct_applicable IN (".USERCLASS_LIST.")
	AND f.user_extended_struct_write IN (".USERCLASS_LIST.")
	AND ((c.user_extended_struct_applicable IN (".USERCLASS_LIST.")
	AND c.user_extended_struct_write IN (".USERCLASS_LIST."))
	OR f.user_extended_struct_parent = 0)
	AND f.user_extended_struct_type > 0
	ORDER BY c.user_extended_struct_order ASC, f.user_extended_struct_order ASC
	";

	if($sql->db_Select_gen($qry))
	{
		$fieldList = $sql->db_getList();
		unset($prev_cat);
		foreach($fieldList as $f)
		{
			if(!isset($prev_cat) || $f['category_id'] != $prev_cat)
			{
				$catname = ($f['category_name'] == null) ? LAN_USET_7 : $f['category_name'];
				$text .= "<tr><td colspan='2' class='forumheader'>{$catname}</td></tr>";
			}
			$prev_cat = $f['category_id'];
			$fname = "user_".$f['user_extended_struct_name'];
			$uVal = str_replace(chr(1), "", $curVal[$fname]);
			$text .= "
				<tr>
					<td style='width:40%' class='forumheader3'>".$tp->toHTML($f['user_extended_struct_text'],"","emotes_off defs");
                if($f['user_extended_struct_required']){
                  $text .= req(2);
				}
				$text .="</td>
					<td style='width:60%' class='forumheader3'>".$ue->user_extended_edit($f, $uVal);
					$parms = explode("^,^",$f['user_extended_struct_parms']);
					if($parms[3])
					{
						$chk = (strpos($curVal['user_hidden_fields'], "^".$fname."^") === FALSE) ? FALSE : TRUE;
						if(isset($_POST))
						{
							$chk = isset($_POST['hide'][$fname]);
						}
						$text .= "&nbsp;&nbsp;".$ue->user_extended_hide($f, $chk);
					}
					$text .= "
					</td>
				</tr>
			";
		}
	}

$signature = $tp->toForm($curVal['user_signature']);
$text .= "
	<tr><td colspan='2' class='forumheader'>".LAN_USET_8."</td></tr>
	<tr>
	<td style='width:40%;vertical-align:top' class='forumheader3'>".LAN_120.req($signupval[7])."</td>
	<td style='width:60%' class='forumheader2'>
	<textarea class='tbox' name='signature' cols='58' rows='4' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$signature</textarea>
	<br />
	".display_help("", 2)."
	</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_122.req($signupval[9])."</td>
	<td style='width:60%' class='forumheader2'>
	<select name='user_timezone' class='tbox'>\n";
timezone();
$count = 0;
while ($timezone[$count])
{
	if ($timezone[$count] == $curVal['user_timezone'])
	{
		$text .= "<option value='".$timezone[$count]."' selected='selected'>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
	}
	else
	{
		$text .= "<option value='".$timezone[$count]."'>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
	}
	$count++;
}

$text .= "</select>
	</td>
	</tr>

	<tr>
	<td colspan='2' class='forumheader'>".LAN_420."</td>
	</tr>

	<tr>
	<td colspan='2' class='forumheader3' style='text-align:center'>".LAN_404.($pref['im_width'] || $pref['im_height'] ? "<br />".($pref['im_width'] ? MAX_AVWIDTH.$pref['im_width']." pixels. " : "").($pref['im_height'] ? MAX_AVHEIGHT.$pref['im_height']." pixels." : "") : "")."</td>
	</tr>


	<tr>
	<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_422.req($signupval[8])."<br /><span class='smalltext'>".LAN_423."</span></td>
	<td style='width:60%' class='forumheader2'>
	<input class='tbox' type='text' name='image' size='60' value='".$curVal['user_image']."' maxlength='100' />
	</td>
	</tr>

	<tr>
	<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_421."<br /><span class='smalltext'>".LAN_424."</span></td>
	<td style='width:60%' class='forumheader2'>
	<input class='button' type ='button' style=' cursor:hand' size='30' value='".LAN_403."' onclick='expandit(this)' />
	<div style='display:none' >";
$avatarlist[0] = "";
$handle = opendir(e_IMAGE."avatars/");
while ($file = readdir($handle))
{
	if ($file != "." && $file != ".." && $file != "index.html" && $file != "CVS") {
		$avatarlist[] = $file;
	}
}
closedir($handle);

for($c = 1; $c <= (count($avatarlist)-1); $c++)
{
	$text .= "<a href='javascript:addtext_us(\"$avatarlist[$c]\")'><img src='".e_IMAGE."avatars/".$avatarlist[$c]."' style='border:0' alt='' /></a> ";
}

$text .= "<br />
	</div>
	</td>
	</tr>";

if ($pref['avatar_upload'] && FILE_UPLOADS)
{
	$text .= "<tr>
		<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_415."<br /></td>
		<td style='width:60%' class='forumheader2'>
		<input class='tbox' name='file_userfile[]' type='file' size='47' />
		</td>
		</tr>";
}

if ($pref['photo_upload'] && FILE_UPLOADS)
{
	$text .= "<tr>
		<td colspan='2' class='forumheader'>".LAN_425."</td>
		</tr>

		<tr>
		<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_414."<br /><span class='smalltext'>".LAN_426."</span></td>
		<td style='width:60%' class='forumheader2'>
		<input class='tbox' name='file_userfile[]' type='file' size='47' />
		</td>
		</tr>";
}

if(isset($pref['xup_enabled']) && $pref['xup_enabled'] ==1)
{
	$text .= "
	<tr>
	<td colspan='2' class='forumheader'>".LAN_435."</td>
	</tr>
	<tr>
	<td style='width:20%; vertical-align:top' class='forumheader3'>".LAN_433."<br /><span class='smalltext'><a href='http://e107.org/generate_xup.php' rel-'external'>".LAN_434."</a></span></td>
	<td style='width:80%' class='forumheader2'>
	<input class='tbox' type='text' name='user_xup' size='50' value='{$curVal['user_xup']}' maxlength='100' />
	</td>
	</tr>
	";
}

$text .= "

	<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><input class='button' type='submit' name='updatesettings' value='".LAN_154."' /></td>
	</tr>
	</table>
	</div><div>";

if($ADMINAREA)
{
    $text .= "<input type='hidden' name='adminmode' value='1' />\n";
	$ref = ($adref) ? $adref : str_replace("main", "uset", $_SERVER['HTTP_REFERER']);
	$text .= "<input type='hidden' name='adminreturn' value='".$ref."' />";
}
$text .= "
	<input type='hidden' name='_uid' value='$_uid' />
	<input type='hidden' name='_pw' value='".$curVal['user_password']."' />
	<input type='hidden' name='_user_sess' value='".$curVal['user_sess']."' /></div>
	</form>
	";

$ns->tablerender(LAN_155, $text);
if($ADMINAREA)
{
	require_once(e_ADMIN."footer.php");
}
else
{
	require_once(FOOTERF);
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function timezone() {
	/*
	# Render style table
	# - parameters                none
	# - return                                timezone arrays
	# - scope                                        public
	*/
	global $timezone, $timearea;
	$timezone = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "GMT", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
	$timearea = array("International DateLine West", "Samoa", "Hawaii", "Alaska", "Pacific Time (US and Canada)", "Mountain Time (US and Canada)", "Central Time (US and Canada), Central America", "Eastern Time (US and Canada)", "Atlantic Time (Canada)", "Greenland, Brasilia, Buenos Aires, Georgetown", "Mid-Atlantic", "Azores", "GMT - UK, Ireland, Lisbon", "West Central Africa, Western Europe", "Greece, Egypt, parts of Africa", "Russia, Baghdad, Kuwait, Nairobi", "Abu Dhabi, Kabul", "Islamabad, Karachi", "Astana, Dhaka", "Bangkok, Rangoon", "Hong Kong, Singapore, Perth, Beijing", "Tokyo, Seoul", "Brisbane, Canberra, Sydney, Melbourne", "Soloman Islands", "New Zealand", "Nuku'alofa");
}

function req($field)
{
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