<?php
$eplug_admin = true;
require_once("../../class2.php");
if(!getperms("P")){header("location:".e_BASE."index.php"); exit; }
require_once(e_HANDLER."form_handler.php");
require_once(e_ADMIN."auth.php");
include_lan(e_PLUGIN."alt_auth/languages/".e_LANGUAGE."/lan_alt_auth_conf.php");

if(isset($_POST['updateprefs']))
{
	$pref['auth_method'] = $_POST['auth_method'];
	$pref['auth_noconn'] = intval($_POST['auth_noconn']);
	$pref['auth_nouser'] = intval($_POST['auth_nouser']);
	save_prefs();
	header("location:".e_SELF);
	exit;
}

$authlist[] = "e107";
$handle=opendir(e_PLUGIN."alt_auth");
while ($file = readdir($handle))
{
	if(preg_match("/^(.*)_auth\.php/",$file,$match))
	{
		$authlist[] = $match[1];
	}
}
closedir($handle);

$auth_dropdown = "<select class='tbox' name='auth_method'>\n";
foreach($authlist as $a)
{
	$s = ($pref['auth_method'] == $a) ? " SELECTED" : "";
	$auth_dropdown .= "<option {$s}>".$a."</option>\n";
}
$auth_dropdown .= "</select>\n";

if(isset($message))
{
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "
<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>

<tr>
<td style='width:70%' class='forumheader3'>".LAN_ALT_1.": </td>
<td style='width:30%; text-align:right;' class='forumheader3'>".
$auth_dropdown."
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>".LAN_ALT_6.":<br />
<div class='smalltext'>".LAN_ALT_7."</div>
</td>
<td style='width:30%; text-align:right;' class='forumheader3'>
<select class='tbox' name='auth_noconn'>";
$sel = (isset($pref['auth_noconn']) && $pref['auth_noconn'] ? "" : " selected = 'selected' ");
$text .= "<option value='0' {$sel} >".LAN_ALT_FAIL."</option>";
$sel = (isset($pref['auth_noconn']) && $pref['auth_noconn'] ? " selected = 'selected' " : "");
$text .= "<option value='1' {$sel} >".LAN_ALT_FALLBACK."</option>
</select>
</td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>".LAN_ALT_8.":<br />
<div class='smalltext'>".LAN_ALT_9."</div>
</td>
<td style='width:30%; text-align:right;' class='forumheader3'>
<select class='tbox' name='auth_nouser'>";
$sel = (isset($pref['auth_nouser']) && $pref['auth_nouser'] ? "" : " selected = 'selected' ");
$text .= "<option value='0' {$sel} >".LAN_ALT_FAIL."</option>";
$sel = (isset($pref['auth_nouser']) && $pref['auth_nouser'] ? " selected = 'selected' " : "");
$text .= "<option value='1' {$sel} >".LAN_ALT_FALLBACK."</option>
</select>
</td>
</tr>

<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader3'>
<br />
<input class='button' type='submit' name='updateprefs' value='".LAN_ALT_2."' />
</td>
</tr>

</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".LAN_ALT_3."</div>", $text);

$text="";
foreach($authlist as $a){
	if($a != 'e107'){
		$text .= LAN_ALT_4." <a href='".e_PLUGIN."alt_auth/{$a}_conf.php'>{$a}</a><br />";
	}
}

$ns -> tablerender("<div style='text-align:center'>".LAN_ALT_5."</div>", $text);

require_once(e_ADMIN."footer.php");
?>	