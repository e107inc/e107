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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/admin_config.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-10 00:34:25 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }

$lan_file = e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php";
include(file_exists($lan_file) ? $lan_file : e_PLUGIN."calendar_menu/languages/English.php");


if(IsSet($_POST['updatesettings'])){
        $pref['eventpost_admin'] = $_POST['eventpost_admin'];
                save_prefs();
        $message = EC_LAN_75; // "Calendar settings updated.";
}

require_once(e_ADMIN."auth.php");

if($message){
        $ns -> tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td style='width:40%' class='forumheader3'>".EC_LAN_76." </td>
<td style='width:60%' class='forumheader3'>".
r_userclass("eventpost_admin", $pref['eventpost_admin'], $mode="off")
."
</td>
</tr>

<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updatesettings' value='".EC_LAN_77."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".EC_LAN_78."</div>", $text);
require_once(e_ADMIN."footer.php");

?>