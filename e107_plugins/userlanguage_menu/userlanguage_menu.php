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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/userlanguage_menu/userlanguage_menu.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-10-10 21:38:01 $
|     $Author: loloirie $
+----------------------------------------------------------------------------+
*/
global $list_e107lang;

$handle=opendir(e_LANGUAGEDIR);
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "/"){
		$lanlist[] = $file;
	}
}
closedir($handle);

$defaultlan = $pref['sitelanguage'];

$totalct = $sql -> db_Select("user", "user_prefs", "user_prefs REGEXP('sitelanguage') OR user_prefs REGEXP('sitedblanguage')");
while ($row = $sql -> db_Fetch()){
	$up = unserialize($row['user_prefs']);
	if(isset($up['sitelanguage'])){
		$lancount[$up['sitelanguage']]++;
	}
	if(isset($up['sitedblanguage'])){
		$dblancount[$up['sitedblanguage']]++;
	}
	unset($up['sitelanguage'],$up['sitedblanguage']);
}

$defaultusers = $sql -> db_Count("user");
$lancount[$defaultlan] += ($defaultusers - count($lancount));
if($pref['e107ml_2lg']==1){
	$dblancount[$defaultlan] += ($defaultusers - count($dblancount));
}

$text = "
<div style='text-align:center'>
<form method='post' action='".e_SELF."' id='userlanguage_menu' ><div>";

// Content + Interface
if(e_MLANGDIF==1){
	$text .= UTHEME_MENU_L2."<br />";
}

// Interface
$counter = 0;
if ($pref['e107ml_flags'] != 1){
  $text .= "<select id='sitelanguage' name='sitelanguage' class='tbox'>";
  while($lanlist[$counter]){
  	$text .= "<option value='".$lanlist[$counter]."' ";
  	if(($lanlist[$counter] == USERLAN) || (USERLAN == FALSE && $lanlist[$counter] == $defaultlan)){
  		$text .= "selected='selected'";
  	}
  	$text .= ">".($lanlist[$counter] == $defaultlan ? "[ ".$lanlist[$counter]." ]" : $lanlist[$counter])." (users: ".($lancount[$lanlist[$counter]] ? $lancount[$lanlist[$counter]] : "0").")</option>\n";
  	$counter++;
  }
  $text .= "</select>";
}else{
	 while($lanlist[$counter]){
			$text .= "<a href=\"#\" onclick=\"document.getElementById('sitelanguage').value='".$lanlist[$counter]."';document.getElementById('userlanguage_menu').submit();\" >";
      if(file_exists(e_IMAGE."generic/flags/".$lanlist[$counter].".png")){
        $text .= "<img class=\"e107_flags\" src=\"".e_IMAGE."generic/flags/".$lanlist[$counter].".png\" />";
      }else{
        $text .= $lanlist[$counter]." ";
      }
      $text .=  "</a>";
      $counter++;
	}
	$text .= "<input type=\"hidden\" id=\"sitelanguage\" name=\"sitelanguage\" value=\"\" />";
}
// Content
if(e_MLANGDIF==1){
  $text .= "<hr />";
	$text .= UTHEME_MENU_L4."<br />";
	if ($pref['e107ml_flags'] != 1){
    $text .= "<select id='sitedblanguage' name='sitedblanguage' class='tbox'>";
  	$counter = 0;
  	$text .= "<option value='".$defaultlan."' ";
  	if($defaultlan == USERDBLAN){
  		$text .= "selected='selected'";
  	}
  	$text .= ">[ ".$defaultlan." ] (users: ".($dblancount[$defaultlan] ? $dblancount[$defaultlan] : "0").")</option>\n";
  
  	while($list_e107lang[$counter]){
  		$text .= "<option value='".$list_e107lang[$counter]."' ";
  		if($list_e107lang[$counter] == USERDBLAN){
  			$text .= "selected='selected'";
  		}
  		$text .= ">".$list_e107lang[$counter]." (users: ".($dblancount[$list_e107lang[$counter]] ? $dblancount[$list_e107lang[$counter]] : "0").")</option>\n";
  		$counter++;
  	}
  	$text .= "</select>";
  }else{
    $counter = 0;
    $text .= "<a href=\"#\" onclick=\"document.getElementById('sitedblanguage').value='".$list_e107lang[$counter]."';document.getElementById('userlanguage_menu').submit();\" >";
    if(file_exists(e_IMAGE."generic/flags/".$defaultlan.".png")){
      $text .= "<img class=\"e107_flags\" src='".e_IMAGE."generic/flags/".$defaultlan.".png' />\n";
    }else{
      $text .= $defaultlan." ";
    }
    $text .= "</a>";
  	while($list_e107lang[$counter]){
  		$text .= "<a href=\"#\" onclick=\"document.getElementById('sitedblanguage').value='".$list_e107lang[$counter]."';document.getElementById('userlanguage_menu').submit();\" >";
      if(file_exists(e_IMAGE."generic/flags/".$list_e107lang[$counter].".png")){
        $text .= "<img class=\"e107_flags\" src='".e_IMAGE."generic/flags/".$list_e107lang[$counter].".png' />\n";
      }else{
        $text .= $list_e107lang[$counter];
      }
      $text .= "</a>".(file_exists(e_IMAGE."generic/flags/".$list_e107lang[$counter].".png") ? "" : " " );
      $counter++;
  	}
  	$text .= "<input type=\"hidden\" id=\"sitedblanguage\" name=\"sitelanguage\" value=\"\" />";
  }
}

$text .= "<br /><br />";
if ($pref['e107ml_flags'] != 1){$text .= "<input class='button' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."' />";}
$text .= "</div>
</form>
</div>";

$ns -> tablerender(UTHEME_MENU_L3, $text);

?>
