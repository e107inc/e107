<?php
/*
*
* Admin panel
* Include this file and use the function e107ml_adpanel2()
*
* Echo the XHTML Code or an error if arrays not correctly defined
*
*/
// $Id: ml_adpanel2.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 

function e107ml_adpanel2(){
  if(strstr(e_SELF,"newspost.ph")){
    $maintable = "news";
  }else if(strstr(e_SELF,"article.ph") || strstr(e_SELF,"content.ph") || strstr(e_SELF,"review.ph")){
    $maintable = "content";
  }else if(strstr(e_SELF,"links.ph")){
    $maintable = "links";
  }else{
    return;
    exit;
  }
	global $list_e107lang, $tables_lang;
	$ml_panel = "<div id=\"mladpanel2\" style=\"text-align: left; margin: 0px 0px 0px 5px;\" >";
	$ml_panel .= "<div class=\"dd\" ><img src=\"".e_IMAGE."generic/ml_logo2.png\" style=\"width: 130px; height: 18px;\" alt=\"\" /></div>";
	$ml_panel .= "<a href=\"#\" onclick=\"document.getElementById('setlanguage').value='".e_LAN."';document.getElementById('form_ml_adpanel2').submit();\" >".e_LAN."</a>";
	for($i=0;$i<e_MLANGNBR;$i++){
		if(in_array($maintable, $tables_lang[$list_e107lang[$i]])){
    //$ml_panel .= "<br />".e_LANGUAGEDIR.$user_pref['sitelanguage']."/lan_".e_PAGE;
		//if(e_MLANGDIF==1 || (e_MLANGDIF!=1 && (file_exists(e_LANGUAGEDIR.$list_e107lang[$i]."/admin/lan_".e_PAGE)))){
			if($i<e_MLANGNBR){$ml_panel .= " - ";}
			$ml_panel .= "<a href=\"#\" onclick=\"document.getElementById('setlanguage').value='".$list_e107lang[$i]."';document.getElementById('form_ml_adpanel2').submit();\" >".$list_e107lang[$i]."</a>";
		//}
    /*else if(e_MLANGDIF==1 || (e_MLANGDIF!=1 && (!file_exists(e_LANGUAGEDIR.$list_e107lang[$i]."/admin/lan_".e_PAGE)))){
			if($i<e_MLANGNBR){$ml_panel .= " - ";}
			$ml_panel .= "<a href=\"javascript:void(0);\" onclick=\"document.getElementById('setlanguage').value='".$list_e107lang[$i]."';document.getElementById('form_ml_adpanel2').submit();\" >".$list_e107lang[$i]."</a> <a href=\"#\" title=\"File missing, page displayed in english, but  database content in ".$list_e107lang[$i]."\" >?</a>";
		}*/
		}
	}
	$ml_panel .= "<br /></div>
	<form method=\"post\" action=\"".e_SELF."\" id=\"form_ml_adpanel2\" >\n
	<div>
  <input type=\"hidden\" id=\"setlanguage\" name=\"setlanguage\" value=\"\" />\n
	</div>
  </form>
	";
	echo $ml_panel;
}



?>
