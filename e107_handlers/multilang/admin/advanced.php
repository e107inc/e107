<?php
// Admin frontpage main code for multilanguage
// Original code by Lolo Irie (e107 dev Team ,http://e107.org)
// http://etalkers.org

$caption = MLAD_MENU_2;
$text = "";
$text .= $rs -> form_open("post", e_SELF."?adv");
$text .= "<table class='fborder' style='width:100%'>\n";

/*
* Display dropdown menu for languages in admin area.
*/
$text .= "<tr>\n
<td style='width:30%' class='forumheader2'>\n
".MLAD_LANadv_11."
</td>\n
<td style='width:70%' class='forumheader3'>\n
";
if($pref['e107ml_dropdownadmin']==1){
	$text .= $rs -> form_checkbox("e107ml_dropdownadmin", 1, 1);
}else{
	$text .= $rs -> form_checkbox("e107ml_dropdownadmin", 1);
}
$text .= "<br /><b class=\"smalltext\" >".MLAD_LANadv_12."</b>
</td>\n
	</tr>\n";


// Email alerts
$text .= "<tr>\n
<td style='width:30%' class='forumheader2'>\n
".MLAD_LANadv_7."
</td>\n
<td style='width:70%' class='forumheader3'>\n
";
if($pref['e107ml_mailalert']==1){
	$text .= $rs -> form_checkbox("e107ml_mailalert", 1, 1);
}else{
	$text .= $rs -> form_checkbox("e107ml_mailalert", 1);
}
$text .= "<br /><b class=\"smalltext\" >".MLAD_LANadv_9."</b>
</td>\n
	</tr>\n";
	
$text .= "<tr>\n
<td style='width:30%' class='forumheader2'>\n
".MLAD_LANadv_8."
</td>\n
<td style='width:70%' class='forumheader3'>\n
";
(($pref['e107ml_mailadress'] == SITEADMINEMAIL || !isset($pref['e107ml_mailadress'])) ?  $text .= $rs -> form_text("e107ml_mailadress", 40, SITEADMINEMAIL, 100) : $text .= $rs -> form_text("e107ml_mailadress", 40, $pref['e107ml_mailadress'], 100) );

$text .= "<br /><b class=\"smalltext\" >".MLAD_LANadv_10."</b>
</td>\n
	</tr>\n";

// Different languages for interface and content
$text .= "<tr>\n
<td style='width:30%' class='forumheader2'>\n
".MLAD_LANadv_1."
</td>\n
<td style='width:70%' class='forumheader3'>\n
";
if($pref['e107ml_2lg']==1){
	$text .= $rs -> form_checkbox("e107ml_2lg", 1, 1);
}else{
	$text .= $rs -> form_checkbox("e107ml_2lg", 1);
}
$text .= "
</td>\n
	</tr>\n";
	
/*
* Display flags icons in public site panel.
*/
$text .= "<tr>\n
<td style='width:30%' class='forumheader2'>\n
".MLAD_LANadv_13."
</td>\n
<td style='width:70%' class='forumheader3'>\n
";
if($pref['e107ml_flags']==1){
	$text .= $rs -> form_checkbox("e107ml_flags", 1, 1);
}else{
	$text .= $rs -> form_checkbox("e107ml_flags", 1);
}
$text .= "<br /><b class=\"smalltext\" >".MLAD_LANadv_14."</b>
</td>\n
	</tr>\n";
	
	
$text .= "<tr>\n
<td colspan='2' class='forumheader3' style='font-weight: bold;' >\n".MLAD_LANadv_3;
$text .= "
</td>\n
	</tr>\n";
$text .= "</table>\n";
// Buttons
$text .= "<table class='fborder' style='width:100%' >
<tr>\n
	<td style='width:100%; text-align: center;' class='forumheader' >\n
	".$rs -> form_button("submit", "e107ml_upadv", MLAD_LANadv_4, "", MLAD_LANadv_4)."
	</td>\n
</tr>\n";
$text .= "</table>\n";
$text .= $rs -> form_close();
$ns -> tablerender($caption, $text);


?>
