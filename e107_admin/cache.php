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
|     $Source: /cvs_backup/e107_0.7/e107_admin/cache.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:20 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("0")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
require_once(e_HANDLER."cache_handler.php");
$ec = new ecache;

if(IsSet($_POST['submit_cache'])){
        $pref['cachestatus'] = $_POST['cachestatus'];
        save_prefs();
        $ec -> clear();
        $message = CACLAN_4;
}

if(IsSet($_POST['empty_cache'])){
        $ec -> clear();
        $message = CACLAN_6;
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td style='width:98' class='forumheader'>".CACLAN_1."</td>
</tr>
<tr>
<td style='width:98%' class='forumheader3'>";
$text .= (!$pref['cachestatus']) ? "<input class='tbox' type='radio' name='cachestatus' value='0' checked='checked' />" : "<input type='radio' name='cachestatus' value='0' />";
$text .=CACLAN_7."
</td>
</tr>

<tr>
<td style='width:98%' class='forumheader3'>";
$text .= ('1' == $pref['cachestatus']) ? "<input class='tbox' type='radio' name='cachestatus' value='1' checked='checked' />" : "<input type='radio' name='cachestatus' value='1' />";
$text .=CACLAN_8."
</td>
</tr>

<tr>
<td style='width:98%' class='forumheader3'>";
if(is_writable(e_FILE."cache")){
        $text .= ('2' == $pref['cachestatus']) ? "<input class='tbox' type='radio' name='cachestatus' value='2' checked='checked' />" : "<input type='radio' name='cachestatus' value='2' />";
        $text .= CACLAN_9;
} else {
        $text .= CACLAN_9."<br /><br /><b>".CACLAN_10."</b>";
}
$text .= "</td>
</tr>

<tr style='vertical-align:top'>
<td style='text-align:center' class='forumheader'>

<input class='button' type='submit' name='submit_cache' value='".CACLAN_2."' />
<input class='button' type='submit' name='empty_cache' value='".CACLAN_5."' />

</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".CACLAN_3."</div>", $text);

require_once("footer.php");
?>