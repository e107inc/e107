<?php
/*
*
* Frontend Panel
* Include this file and use the function e107ml_panel()
* attribute1: mysql tablename
* Echo the XHTML Code
*
*/
// $Id: ml_panel.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 

function e107ml_panel($maintable = "news"){
	global $list_e107lang, $tables_lang, $pref;
	$ml_panel = "<div id=\"mlpanel\" class=\"mlpanel\" >";
	if($pref['e107ml_flags'] != 1){$ml_panel .= "<div class=\"dd\" ><img alt=\"\" src=\"".e_IMAGE."generic/ml_logo2.png\" style=\"width: 130px; height: 18px;\" /></div>";}
	$ml_panel .= "<a href=\"#\" onclick=\"document.getElementById('setlanguage2').value='".e_LAN."';document.getElementById('form_ml_panel').submit();\" >";
 
  if($pref['e107ml_flags'] == 1 && file_exists(e_IMAGE."generic/flags/".e_LAN.".png")){
        $ml_panel .= "<img class=\"e107_flags\" src=\"".e_IMAGE."generic/flags/".e_LAN.".png\" />";
      }else{
        $ml_panel .= e_LAN;
      }
  $ml_panel .= "</a>";
	for($i=0;$i<e_MLANGNBR;$i++){
		if((e_MLANGDIF==1 && in_array($maintable, $tables_lang[$list_e107lang[$i]])) || e_MLANGDIF!=1){
    	if($i<e_MLANGNBR && $pref['e107ml_flags'] != 1){$ml_panel .= " - ";}
			$ml_panel .= "<a href=\"#\" onclick=\"document.getElementById('setlanguage2').value='".$list_e107lang[$i]."';document.getElementById('form_ml_panel').submit();\" >";
      if($pref['e107ml_flags'] == 1 && file_exists(e_IMAGE."generic/flags/".$list_e107lang[$i].".png")){
        $ml_panel .= "<img class=\"e107_flags\" src=\"".e_IMAGE."generic/flags/".$list_e107lang[$i].".png\" />";
      }else{
        $ml_panel .= $list_e107lang[$i];
      }
      $ml_panel .=  "</a>";
		}
	}
	$ml_panel .= "<br />
	</div>
	<form method=\"post\" action=\"".e_SELF.(e_QUERY ? "?".e_QUERY : "" )."\" id=\"form_ml_panel\" >
	<div>
  <input type=\"hidden\" id=\"setlanguage2\" name=\"setlanguage2\" value=\"\" />
	</div>
  </form>";
	echo $ml_panel;
}



?>
