<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
$eplug_admin = true;
require_once("../../class2.php");
if(!getperms("P")){header("location:".e_BASE."index.php"); exit; }
require_once(e_HANDLER."form_handler.php");
require_once(e_ADMIN."auth.php");
include_lan(e_PLUGIN."alt_auth/languages/".e_LANGUAGE."/lan_alt_auth_conf.php");
define("ALT_AUTH_ACTION", "main");
require_once(e_PLUGIN."alt_auth/alt_auth_adminmenu.php");

if(isset($_POST['updateprefs']))
{
	$pref['auth_method'] = $_POST['auth_method'];
	$pref['auth_noconn'] = intval($_POST['auth_noconn']);
	$pref['auth_nouser'] = intval($_POST['auth_nouser']);
	save_prefs();
	header("location:".e_SELF);
	exit;
}

$authlist = alt_auth_get_authlist();

$auth_dropdown = "<select class='tbox' name='auth_method'>\n";
foreach($authlist as $a)
{
	$s = ($pref['auth_method'] == $a) ? "selected='selected'" : "";
	$auth_dropdown .= "<option value='{$a}' {$s}>".$a."</option>\n";
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


require_once(e_ADMIN."footer.php");

function alt_auth_conf_adminmenu()
{
	alt_auth_adminmenu();
}


?>	