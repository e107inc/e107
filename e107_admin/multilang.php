<?php
/*
Multilanguage system for e107 by Lolo Irie, e107 Dev Team
Thanks to all active members of the e107 project (dev team, testers...). ;)
|	©Lolo Irie 2004
|	http://etalkers.org
|	e107 is an original CMS from Jalist: http://e107.org
*/


// List required tables for each e107 module

@ini_set("max_execution_time","600");
@set_time_limit(600);

define("DEBUG_ML",FALSE);
define("DEBUG_ML1",TRUE);
require_once("../class2.php");

// Update from .6xxx
if(!isset($pref['sitelang_init'])){
  $pref['sitelang_init'] = $pref['sitelanguage'];
  save_prefs();
}

$tb_dbtype = array();
$tb_dbtype['Comments'] = array("comments");
$tb_dbtype['Content'] = array("content");
$tb_dbtype['Download'] = array("download","download_category");
$tb_dbtype['Links'] = array("links","link_category");
$tb_dbtype['News'] = array("news","news_category");
$tb_dbtype['Welcome Message'] = array("wmessage");

if(!getperms("ML")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");

require_once(e_HANDLER."form_handler.php");
$rs = new form;
$aj = new textparse;
$menuactiv = 0;
if(e_QUERY){
    if(e_QUERY!="menuactiv"){
		$tmp = explode(".", e_QUERY);
    	$action = $tmp[0];
    	if($action != "lancheck"){
		 	$sub_action = $tmp[1];
	    	$id = $tmp[2];
	    	unset($tmp);
	 	}
	 }else{
	 	$menuactiv = 1;
	 } 
}

// Get all existing languages for interface
$handle=opendir(e_LANGUAGEDIR);
$nbrlan=0;
while ($file = readdir($handle)){
	if(!strstr($file,".") && $file!= $pref['sitelanguage']){
		$lanlist[] = $file;
		$id[] = substr(md5($file),0,12);
		$nbrlan++;
	}
}
closedir($handle);


// Frontpage
if(!e_QUERY || e_QUERY=="menuactiv"){
	if($pref['maintainance_flag']==1){
		$message .= MLAD_LAN_19;
	}else{
		$message .= MLAD_LAN_17;
	}
	if($menuactiv==1){$message .= MLAD_LAN_40;}
}
//echo "XXX ".$pref['sitelang_init'];
// Update main language
if(isset($_POST['sitemainlanguage'])){
  require_once(e_HANDLER."multilang/admin/update_mainlanguage.php");
  /*
  header("location: ".e_SELF.(e_QUERY ? "?".e_QUERY : "" ));
  exit;
  */
}

// Update advanced options
if(IsSet($_POST['e107ml_upadv'])){
	$pref['e107ml_2lg'] = $_POST['e107ml_2lg'];
	$pref['e107ml_mailalert'] = $_POST['e107ml_mailalert'];
	$pref['e107ml_dropdownadmin'] = $_POST['e107ml_dropdownadmin'];
	$pref['e107ml_flags'] = $_POST['e107ml_flags'];
	( $_POST['e107ml_mailadress']=="" ? $pref['e107ml_mailadress'] = SITEADMINEMAIL : $pref['e107ml_mailadress'] = $_POST['e107ml_mailadress'] );
	save_prefs();
	$capmessage = MLAD_LANadv_6;
	$message = MLAD_LANadv_5;
}

// Update general options
if(IsSet($_POST['e107ml_update']) || IsSet($_POST['e107ml_delete'])){
	if(IsSet($_POST['e107ml_delete'])){
		$ml_delete = 1;
	}else{
		$ml_delete = 0;
	}
	// List tables to create
	$create_ml_news = 0;
	unset($tnews, $tarticles, $treviews, $tcontent);
	$message .= "<b>".MLAD_LAN_21."</b><br />";
	$i=0;
	$tmp_message = "";
	$tmp_table_ml = "";
	$tnews = array();
	$ttnews = array();
	while($_POST['ml_option'][$i]){
		//echo "<br />".$_POST['ml_option'][$i]."<br />";
		$table_ml[$i] = explode("~", $_POST['ml_option'][$i]);
		if(DEBUG_ML){echo "<br /><br /><b>ml_option value</b>: ".$_POST['ml_option'][$i]."<br />";}
		if($tmp_table_ml != $table_ml[$i][1]){$cr_news = 0;}
		$tmp_table_ml = $table_ml[$i][1];
		
		for($j=0;$j<$nbrlan;$j++){
			if($id[$j]==$table_ml[$i][1]){
				if(DEBUG_ML){echo "<br />table_ml[{$i}][1]: ".$table_ml[$i][1];}
				$lang_ins[$i] = MPREFIX.$lanlist[$j]."_";
				// 
				$tnews[] = $lang_ins[$i];
				if(DEBUG_ML){echo "<br />tnews[{$i}]: ".$lang_ins[$i]."<br />";}
				if(strstr($table_ml[$i][0],"^")){
					$tt = explode("^",$table_ml[$i][0]);
					for($k=0;$k<count($tt);$k++){
						$ttnews[$lang_ins[$i]][$cr_news] .= MPREFIX.$tt[$k];
						if(DEBUG_ML){echo "<br />Value (ttnews{$lang_ins[$i]}{$cr_news}): ".$ttnews[$lang_ins[$i]][$k];}
						$create_ml_news++;
						$cr_news++;
					}
				}else{
					$ttnews[$lang_ins[$i]][$cr_news] = MPREFIX.$table_ml[$i][0];
					if(DEBUG_ML){echo "<br />Simple value (ttnews{$lang_ins[$i]}{$cr_news}): ".$ttnews[$lang_ins[$i]][0];}
					$create_ml_news++;
					$cr_news++;
				}
				break;
			}
		}
		
		if(strstr($table_ml[$i][0],"^")){
			$tmp_ml = explode("^",$table_ml[$i][0]);
			for($k=0;$k<count($tmp_ml);$k++){
				$tmp_message .= strtolower($lang_ins[$i]).$tmp_ml[$k]."<br />";
			}
			unset($tmp_ml);
		}else{
			$tmp_message .= strtolower($lang_ins[$i]).$table_ml[$i][0]."<br />";
		}
		
		$i++;
	}
	$tmp_message .= "<br />";

	
	// Duplicate tables
	if($create_ml_news > 0){
		$tmp2_message = "";
		require_once(e_HANDLER."multilang/create_tables.php");
		//global $yy;
    if(DEBUG_ML){echo "<br>Variables for e107_sqldupli: ".count($ttnews)."-".count($tnews)."<br>";}
		$drop = 0;
		($_POST['drop']==1? $drop=1 : "" );
		if($create_ml_news > 0){
			$tmp2_message .= "##....e107....##<br />".e107_sqldupli($ttnews, $tnews,$drop,$ml_delete)."<br />##....e107....##<br />";
		}
		if($tmp2_message != "##....e107....##<br /><br />".MLAD_LAN_49."<br />".MLAD_LAN_50."<br /><br />##....e107....##<br />"){
      $message .= "<b>".MLAD_LAN_44."</b><br />
    	<br />
    	".MLAD_LAN_45."<a href=\"javascript:void(0);\" onclick=\"expandit(this);\" >".MLAD_LAN_46."</a>\n
    	<div style=\"display: none;\" >
    	<br />
    	".MLAD_LAN_47."
    	<br>
    	".MLAD_LAN_48."
    	<br />
    	<br />".$tmp2_message."</div>";
    }else{
      $message .= "<br />".MLAD_LAN_49."<br />".MLAD_LAN_50."<br />";
    }
	}
	
	// Message displayed
	if(IsSet($_POST['e107ml_update'])){
		$tmp = ( $create_ml_news != 1 ? MLAD_LAN_22 : MLAD_LAN_20);
	}else if(IsSet($_POST['e107ml_delete'])){
		$tmp = ( $create_ml_news != 1 ? MLAD_LAN_35 : MLAD_LAN_36);
	}
	if($tmp2_message != "##....e107....##<br /><br />".MLAD_LAN_49."<br />".MLAD_LAN_50."<br /><br />##....e107....##<br />"){
    $message .= "<br /><br /><b>".$tmp."</b><br />".$tmp_message ;
  }
	
	$pref['e107ml_flag'] = 1;
	save_prefs();
}


if(IsSet($_POST['act_ml']) && $_POST['act_ml']==0){
	$pref['e107ml_flag'] = 0;
	save_prefs();
	header("location: ".e_SELF);
}

if($_POST['act_ml']==1){
	$pref['e107ml_flag'] = 1;
	save_prefs();
	// Install the menu if not present
	if($sql -> db_Select("menus", "menu_id", "menu_name='userlanguage_menu'")){
		$menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='1' ");
		$sql -> db_Update("menus", "menu_location='1', menu_order='".($menu_count+1)."' WHERE menu_name='userlanguage_menu' ");
	}
	header("location: ".e_SELF."?menuactiv");
}

// Get all existing languages for content
$result2 = @mysql_list_tables($mySQLdefaultdb);
$table_ml_arr = array();
if(DEBUG_ML){echo "<b>List of existing tables:</b> <br />";}
$dblanlist = array();

while ($row2 = mysql_fetch_row($result2)){
	$table_ml_arr[] = $row2[0];
	// Store all existing tables for multilanguage
	$cc = 0;
	while($lanlist[$cc]){
		if(strstr($row2[0],strtolower($lanlist[$cc])) && $lanlist[$cc] != $pref['sitelanguage']){
			if(!$dblanlist[$lanlist[$cc]]){$dblanlist[$lanlist[$cc]] = "0";}
			$tab_lg = explode(strtolower($lanlist[$cc]),$row2[0]);
			$dblanlist[$lanlist[$cc]] .= "^".substr($tab_lg[1], 1);
			if(DEBUG_ML){echo $dblanlist["Danish"]."<br />";}
		}
		$cc++;
	}
}

// Display existing tables for multilanguage
if(e_MLANG && $menuactiv != 1 && $sub_action != 'no'){
  unset($e107_key, $e107_value);
  $i=0;
  /*
  foreach($dblanlist as $e107_key => $e107_value){
  	if(DEBUG_ML){echo "<br />LANG: ".$e107_key." - TABLE: ".$e107_value;}
  	// Save/Update existing languages list
  	if($e107_key != $pref['sitelanguage']){
      if(!$sql -> db_Select("core","*","e107_name='ml_".$e107_key."' OR e107_name='bak_ml_".$e107_key."' ") && $_POST['sitemainlanguage']!=$e107_key){
    		$sql -> db_Insert("core","'ml_".$e107_key."','0'");
    	}
  	}
  }
  */
  $tmp_insml = serialize($dblanlist);
    if(!$sql -> db_Insert("core","'e107_ml','".$tmp_insml."'")){
    	$sql -> db_Update("core","e107_value='".$tmp_insml."' WHERE e107_name='e107_ml'");
    }
}
if(e_QUERY && $menuactiv==1){header("location: ".e_SELF."?db.no");}

// Frontpage messages
if(!e_QUERY || e_QUERY=="menuactiv"){

	// Help for multilanguage
	$caption = MLAD_LAN_37;
	$text = "<div style=\"width: 100%; text-align: center;\" ><img alt=\"\" src=\"".( e_MLANG==1 ? e_IMAGE."generic/ml_logo1.png" : e_IMAGE."generic/ml_logo1b.png" )."\" style=\"width: 180px; height: 74px;\" /></div>
  <img src=\"".e_IMAGE."generic/warning.png\" alt=\"Warning\" style=\"width: 17px; height: 17px; margin: 0px 5px 0px 0px; vertical-align: middle;\" /><a href=\"javascript:void(0);\" onclick=\"expandit('ml_help')\" >".MLAD_LAN_38."</a>\n
	<div style=\"display: none;\" id=\"ml_help\" >
	<br />
	".MLAD_LAN_39."
	</div>
	";
	$ns -> tablerender($caption, $text);

	// Start XHTML form for multilanguage options
	$message .= $rs -> form_open("post", e_SELF);
	$message .= "<p>";
	$message .= ( $pref['e107ml_flag']==1 ? MLAD_LAN_24 : MLAD_LAN_23);
	
	$message .= $rs -> form_hidden("act_ml", ( $pref['e107ml_flag']==1 ? 0 : 1 ));
	
	$message .= "</p>";
	
	$message .= "<p>";
	$message .= $rs -> form_button("submit", "act_ml_update", ( $pref['e107ml_flag'] != 1 ? MLAD_LAN_25 : MLAD_LAN_16 ), "", MLAD_LAN_25);
	$message .= "</p>";
	
	$message .= $rs -> form_close();
	// End XHTML form for multilanguage options
	$capmessage = "<b>".MLAD_LAN_18."</b> ".($pref['e107ml_flag'] == 1 ? MLAD_LAN_2 : MLAD_LAN_3 );
}

// Display messages
if(isset($message)){$ns -> tablerender($capmessage, $message);}

// Frontpage 
if(!e_QUERY || e_QUERY=="menuactiv"){	
	// Start XHTML form for list of languages
	require_once(e_HANDLER."multilang/admin/frontpage.php");
	// End XHTML form
}

// Advanced options
if($action=="adv"){
	// Start XHTML form for advanced options
	require_once(e_HANDLER."multilang/admin/advanced.php");
	// End XHTML form
}

// Database management
if($action=="db"){
	// Start XHTML form for advanced options
	require_once(e_HANDLER."multilang/admin/db.php");
	// End XHTML form
}

// Database management
if($action=="lancheck"){
	// Start XHTML form for advanced options
	require_once(e_HANDLER."multilang/admin/lancheck.php");
	// End XHTML form
}

function show_options($action){
        global $sql;
        if($action==""){$action="main";}
        // ##### Display options ---------------------------------------------------------------------------------------------------------
        $var['main']['text']=MLAD_MENU_1;
        $var['main']['link']=e_SELF;
        if(e_MLANG==1){
            $var['db']['text']=MLAD_MENU_3;
            $var['db']['link']=e_SELF."?db";
			$var['adv']['text']=MLAD_MENU_2;
            $var['adv']['link']=e_SELF."?adv";
			
			
			
        }
		$var['lancheck']['text']=MLAD_MENU_4;
        $var['lancheck']['link']=e_SELF."?lancheck";
        show_admin_menu(MLAD_MENU_0,$action,$var);
}

function multilang_adminmenu(){
        global $action;
        show_options($action);
}


require_once("footer.php");

?>
