<?php
/*
*
* Buttons panel (Submit buttons) in admin area
* Include this file and use the function e107ml_adpanel()
* attribute1: display dropdown menu
* attribute2: array type button(s)
* attribute3: array HTML id/name button(s)
* attribute4: array value button(s)
* attribute5: array class button(s)
* attribute6: array javascript event for button(s)
* attribute7: array title button(s)
* 
* Return the XHTML Code or an error if arrays not correctly defined
*
*/
// $Id: ml_adpanel.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 

function e107ml_adpanel($ddlg=1,$buttyp,$butnam,$butval,$butclass,$butjs,$buttitle){
	
	global $list_e107lang, $rs, $pref, $tables_lang;
	// $ddlg = dropdown menu languages 0=hidden 1=displayed
	// $buttyp = array label button(s)
	// $butnam = array HTML id/name button(s)
	// $butval = array label button(s)
	// $butclass = array class button(s)
	// $butjs = array javascript event for button(s)
	// $buttitle = array title button(s)
	$nb_but = count($buttyp);
	// check arrays
	
	if($nb_but==count($butnam) && $nb_but==count($butval) && $nb_but==count($butclass) && $nb_but==count($butjs) && $nb_but==count($buttitle)){
		// Define default values
		for($i=0;$i<count($buttyp);$i++){
			if($buttyp[$i]==""){
				$buttyp[$i] = "submit";
			}
			if($butnam[$i]==""){
				$butnam[$i] = "autobut{$i}";
			}
			if($butval[$i]==""){
				$butval[$i] = "Submit";
			}
			if($butclass[$i]==""){
				$butclass[$i] = "button";
			}
		}
		$panel_html = "<div id=\"mlpanel\" class=\"admlpanel\" >";
		
		// Display drop down menu for languages
		if($ddlg==1){
			$panel_html .= e107ml_ddlg();
		}
		// Display buttons
		for($i=0;$i<$nb_but;$i++){
			$panel_html .= $rs -> form_button($buttyp[$i], $butnam[$i], $butval[$i], $butjs[$i],"",$buttitle[$i]);
		}
			
		$panel_html .= "</div>";
		return $panel_html;
	}
	// arrays not right defined to use this function
	else{
		return "Error, please contact the e107 Dev Team using the bugtracker on e107.org or etalkers.org";
	}
}


function e107ml_ddlg(){
	global $list_e107lang, $rs, $pref, $tables_lang;
	if(strstr(e_SELF,"newspost.ph")){
    $maintable = "news";
  }else if(strstr(e_SELF,"article.ph") || strstr(e_SELF,"content.ph") || strstr(e_SELF,"review.ph")){
    $maintable = "content";
  }else if(strstr(e_SELF,"links.ph")){
    $maintable = "links";
  }else if(strstr(e_SELF,"prefs.ph")){
    $maintable = 1;
  }else{
    return;
    exit;
  }
	$dd = "<span ".( $pref['e107ml_dropdownadmin']!=1 ? "style='display: none;'" : "" ).">";
  $dd .= $rs -> form_select_open("list_lang");
	$dd .= (e_LAN == e_DBLANGUAGE ? $rs -> form_option(e_LAN, 1) : $rs -> form_option(e_LAN, 0));
	for($i=0; $i<e_MLANGNBR; $i++){
    if(in_array($maintable, $tables_lang[$list_e107lang[$i]]) || $maintable == 1){
      $dd .= ($list_e107lang[$i] == e_DBLANGUAGE ? $rs -> form_option($list_e107lang[$i], 1) : $rs -> form_option($list_e107lang[$i], 0));
    }
  }
	
	$dd .= $rs -> form_select_close()."</span>";
	
	return $dd;
}

?>
