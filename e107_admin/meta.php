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
|     $Source: /cvs_backup/e107_0.7/e107_admin/meta.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-01-18 16:11:32 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("C")){ header("location:".e_BASE."index.php"); exit; }
$e_sub_cat = 'meta';
require_once("auth.php");

if(isset($_POST['metasubmit'])){
    $aj = new textparse;
    $pref['meta_tag'] = $aj -> formtpa($_POST['meta'], "admin");
    save_prefs();
    $message = METLAN_1;
}

if($message){
    $ns -> tablerender(METLAN_4, "<div
style='text-align:center'>".METLAN_1.".</div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' id='dataform'>
<table style='".ADMIN_WIDTH."' class='fborder'>
<tr>

<td style='width:30%' class='forumheader3'>".METLAN_2.": </td>
<td style='width:70%' class='forumheader3'>
<textarea class='tbox' id='meta' name='meta' cols='70'
rows='10'>".$pref['meta_tag']."</textarea>
<br />";
$text .= <<< EOT
<input class="button" type="button" value="description"
onclick="addtext2('<meta name=\'description\' content=\'
EOT;
$text .= METLAN_5;
$text .= <<< EOT
\' />')" />
<input class="button" type="button" value="keywords"
onclick="addtext2('<meta name=\'keywords\' content=\'
EOT;
$text .= METLAN_6;
$text .= <<< EOT
\' />')" />
<input class="button" type="button" value="copyright"
onclick="addtext2('<meta name=\'copyright\' content=\'
EOT;
$text .= METLAN_7;
$text .= <<< EOT
\' />')" />
EOT;
$text .= "</td>
</tr>

<tr><td colspan='2' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='metasubmit'
value='".METLAN_3."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender(METLAN_8, $text);

function headerjs(){
$headerjs = "<script type=\"text/javascript\">
function addtext2(str){
    document.getElementById('meta').value += str;
}
</script>\n";
return $headerjs;
}

?>

<?php
require_once("footer.php");
?>