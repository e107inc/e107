<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/cache.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("0")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");

if(IsSet($_POST['submit_cache'])){
	$pref['cachestatus'] = $_POST['cachestatus'];
	save_prefs();
	$sql -> db_Delete("cache");
	$message = CACLAN_4;
}

if(IsSet($_POST['empty_cache'])){
	$sql -> db_Delete("cache");
	$message = CACLAN_6;
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td style='width:30%' class='forumheader3'>".CACLAN_1."?</td>
<td style='width:70%' class='forumheader3'>".
($pref['cachestatus'] ? "<input type='checkbox' name='cachestatus' value='1' checked='checked' />" : "<input type='checkbox' name='cachestatus' value='1'>")."</td>
</tr>

<tr style='vertical-align:top'> 
<td colspan='2' style='text-align:center' class='forumheader'>

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