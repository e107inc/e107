<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/calendar_conf.php
|
|        Created by Cameron based on code by jalist.
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }

if(IsSet($_POST['updatesettings'])){
        $pref['eventpost_admin'] = $_POST['eventpost_admin'];
		save_prefs();
        $message = "Calendar settings updated.";
}

require_once(e_ADMIN."auth.php");

if($message){
        $ns -> tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}

$eventpost_admin = $pref['eventpost_admin'];

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td style='width:50%' class='forumheader3'>Only Admin can add new events?: </td>
<td style='width:50%' class='forumheader3'>".
($pref['eventpost_admin'] ? "<input type='checkbox' name='eventpost_admin' value='1'  checked>" : "<input type='checkbox' name='eventpost_admin' value='1'>")."
</td>
</tr>

<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updatesettings' value='Update Calendar Settings' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>Calendar Settings</div>", $text);
require_once(e_ADMIN."footer.php");

?>