<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/meta.php
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
if(!getperms("C")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");

if(IsSet($_POST['metasubmit'])){
	$aj = new textparse;
	$pref['meta_tag'] = $aj -> formtpa($_POST['meta'], "admin");
	save_prefs();
	$message = METLAN_1;
}

if($message){
	$ns -> tablerender("Updated", "<div style='text-align:center'>".METLAN_1.".</div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='dataform'>
<table style='width:85%' class='fborder'>
<tr>

<td style='width:30%' class='forumheader3'>".METLAN_2.": </td>
<td style='width:70%' class='forumheader3'>
<textarea class='tbox' name='meta' cols='70' rows='10'>".$pref['meta_tag']."</textarea>
<br />";
$text .= <<< EOT
<input class="button" type="button" value="description" onclick="addtext2('<meta name=\'description\' content=\'type your description here\' />')">
<input class="button" type="button" value="keywords" onclick="addtext2('<meta name=\'keywords\' content=\'type, a, list, of, your, keywords, here\' />')">
<input class="button" type="button" value="copyright" onclick="addtext2('<meta name=\'copyright\' content=\'type your copyright info here\' />')">
EOT;
$text .= "</td>
</tr>

<td colspan='2' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='metasubmit' value='".METLAN_3."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("Meta Tags", $text);
?>
<script type="text/javascript">
function addtext2(str){
	document.dataform.meta.value += str;
}
</script>
<?php
require_once("footer.php");
?>
