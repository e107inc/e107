<?php
// Admin frontpage main code for multilanguage
// Original code by Lolo Irie (e107 dev Team ,http://e107.org)
// http://etalkers.org

// Main language
$caption = MLAD_LAN_41;
$text = "<br />
<img src=\"".e_IMAGE."generic/warning.png\" alt=\"Warning\" style=\"width: 17px; height: 17px; margin: 0px 5px 0px 0px; vertical-align: middle;\" /><b class=\"smalltext\" >".MLAD_LAN_43."</b>\n
<br /><br />\n
<form method=\"post\" action=\"".e_SELF."\" id=\"mainlangform\" >\n
<table style='width:100%' class='fborder' cellspacing='1' cellpadding='0'>\n
<tr>\n

<td style='width:50%' class='forumheader3'>".MLAD_LAN_42."</td>\n
<td style='width:50%; text-align:right' class='forumheader3'>\n


<select name='sitemainlanguage' class='tbox'>\n";
$handle=opendir(e_LANGUAGEDIR);
while ($file = readdir($handle)){
        if($file != "." && $file != ".." && $file != "/"){
                $lanlist2[] = $file;
        }
}
closedir($handle);
$counter = 0;
$sellan = eregi_replace("lan_*.php", "", $pref['sitelanguage']);
while(IsSet($lanlist2[$counter])){
        if($lanlist2[$counter] == $sellan){
                $text .= "<option selected='selected'>".$lanlist2[$counter]."</option>\n";
        }else{
                $text .= "<option>".$lanlist2[$counter]."</option>\n";
        }
        $counter++;
}
$text .= "</select> <input type=\"submit\" id=\"butsubmit\" class=\"button\" value=\"".MLAD_LANadv_4."\" />\n
</td>\n
</tr>\n
</table>\n
</form>
";
$ns -> tablerender($caption, $text);

if(count($dblanlist)>0){
	
	$caption = MLAD_LAN_31;
	$text = "";
	$text .= MLAD_LAN_27."<br /><br />";
	$text .= ( e_MLANG==1 ? " ".MLAD_LAN_26 : "");
	
	$text .= "<br /><br />";
	foreach($dblanlist as $mlkey => $mlval){
		
		
		// Language Name
		$text .= "<a href=\"javascript:void(0);\" onclick=\"expandit('cf_".$mlkey."')\" ><b>".$mlkey."</b></a><br /><br />";
		
		// Language Config
		$text .= "<div style=\"display: none;\" id=\"cf_".$mlkey."\" >\n\n";
		
		$tmp_val = explode("^",$mlval);
		for($ii=1;$ii<count($tmp_val);$ii++){
			$text .= "<b>".MPREFIX.strtolower($mlkey)."_".$tmp_val[$ii]."</b>";
			foreach($tb_dbtype as $kkey => $vval){
				if(in_array($tmp_val[$ii],$vval)){
					$text .= MLAD_LAN_28."<b>".$kkey."</b><br />";
				}
			}
		}
		
		$text .= "<br /><br />\n</div>\n";
	
	}
	$ns -> tablerender($caption, $text);

}

?>
