<?php
$eplug_admin = true;
require_once("../../class2.php");
if(!getperms("P")){header("location:".e_BASE."index.php"); exit; }
require_once(e_HANDLER."form_handler.php");
require_once(e_ADMIN."auth.php");


if(isset($_POST['updateprefs'])){
	$aj = new textparse;
	$pref['auth_method'] = $_POST['auth_method'];
	save_prefs();
	header("location:".e_SELF);
	exit;
}

$authlist[] = "e107";
$handle=opendir(e_PLUGIN."alt_auth");
while ($file = readdir($handle)){
	if(preg_match("/^(.*)_auth\.php/",$file,$match)){
		$authlist[] = $match[1];
	}
}
closedir($handle);

$auth_dropdown = "
<tr>
<td style='width:70%' class='forumheader3'>Current authorization type: </td>
<td style='width:30%; text-align:right;' class='forumheader3'>";
$auth_dropdown .= "<select class='tbox' name='auth_method'>\n";
foreach($authlist as $a){
	$s = ($pref['auth_method'] == $a) ? " SELECTED" : "";
	$auth_dropdown .= "<option {$s}>".$a."</option>\n";
}
$auth_dropdown .= "</select>\n";
$auth_dropdown .= "</td></tr>";


if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:95%' class='fborder' cellspacing='1' cellpadding='0'>
<tr>

</tr>
".$auth_dropdown."
<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader3'>
<br />
<input class='button' type='submit' name='updateprefs' value='Update settings' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>Choose Alternate Authorizaion Type</div>", $text);


$text="";
foreach($authlist as $a){
	if($a != 'e107'){
		$text .= "Configure parameters for <a href='".e_PLUGIN."alt_auth/{$a}_conf.php'>{$a}</a><br />";
	}
}

$ns -> tablerender("<div style='text-align:center'>Configure authorization parameters</div>", $text);


require_once(e_ADMIN."footer.php");
?>	