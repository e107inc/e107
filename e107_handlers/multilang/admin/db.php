<?php
// Admin create/delete tables main code for multilanguage
// Original code by Lolo Irie (e107 dev Team ,http://e107.org)
// http://etalkers.org

$caption = MLAD_LAN_1;
$text = MLAD_LAN_4."<br /><br />";

$text .= $rs -> form_open("post", e_SELF."?db");

for($i=0;$i<$nbrlan;$i++){
	// Language Name
	$text .= "<div><br /><a href=\"javascript:void(0);\" onclick=\"expandit('conf_".$id[$i]."')\" ><b>".$lanlist[$i]."</b></a><br /></div>";

	// Language Config
	$text .= "<div style=\"display: none;\" id=\"conf_".$id[$i]."\" >\n\n";
	$text .= "<table class='fborder' style='width:100%'>\n";
	// News
	$text .= "<tr>\n
		<td style='width:30%' class='forumheader2'>\n
		".MLAD_LAN_5."
		</td>\n
		<td style='width:70%' class='forumheader3'>\n
		";
	$tab_check = MPREFIX.strtolower($lanlist[$i])."_news";
	/*
	if(in_array($tab_check,$table_ml_arr)){
		$text .= $rs -> form_checkbox("ml_option[]", "news^news_category~".$id[$i],1);
	}else{*/
		$text .= "<input type=\"checkbox\" name=\"ml_option[]\" value=\"news^news_category~".$id[$i]."\" />";
    //$rs -> form_checkbox("", "news^news_category~".$id[$i]);
	//}
	$text .= "
		</td>\n
	</tr>\n";
	
	// Articles, Reviews, Content
	$text .= "<tr>\n
		<td style='width:30%' class='forumheader2'>\n
		".MLAD_LAN_6."
		</td>\n
		<td style='width:70%' class='forumheader3'>\n
		";
	$tab_check = MPREFIX.strtolower($lanlist[$i])."_content";
	$text .= "<input type=\"checkbox\" name=\"ml_option[]\" value=\"content~".$id[$i]."\" />";
  //$rs -> form_checkbox("ml_option[]", "content~".$id[$i]);
	$text .= "
		</td>\n
	</tr>\n";
	
	// Links
	$text .= "<tr>\n
		<td style='width:30%' class='forumheader2'>\n
		".MLAD_LAN_9."
		</td>\n
		<td style='width:70%' class='forumheader3'>\n
		";
	$tab_check = MPREFIX.strtolower($lanlist[$i])."_links";
	$text .= "<input type=\"checkbox\" name=\"ml_option[]\" value=\"links^link_category~".$id[$i]."\" />";
  //$rs -> form_checkbox("ml_option[]", "content~".$id[$i]);
	$text .= "
		</td>\n
	</tr>\n";
	
	$text .= "</table>\n";
	//$text .= $rs -> form_close();
	$text .= "<br />\n</div>\n";
}

// Drop tables ?
$text .= "<div>".MLAD_LAN_29." ".$rs -> form_checkbox("drop", 1)."<br />\n
<b class=\"smalltext\" >".MLAD_LAN_30."</b>\n";
$text .= "<br /><br />";	
// Drop tables ?
$text .= MLAD_LAN_32." ".$rs -> form_checkbox("confirm", 1)."<br />\n
<b class=\"smalltext\" >".MLAD_LAN_33."</b></div>\n";


// Buttons
	$text .= "<table class='fborder' style='width:100%' >
	<tr>\n
		<td style='width:100%; text-align: center;' class='forumheader' >\n
		".$rs -> form_button("submit", "e107ml_update", MLAD_LAN_15, "", MLAD_LAN_15)." ".$rs -> form_button("submit", "e107ml_delete", MLAD_LAN_34, "", MLAD_LAN_34)."
		</td>\n
	</tr>\n";
	$text .= "</table>\n";

$text .= $rs -> form_close();
$ns -> tablerender($caption, $text);


?>
