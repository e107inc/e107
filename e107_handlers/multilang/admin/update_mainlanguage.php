<?php

if(e_MLANG!=1){
  $pref['sitelanguage'] = $_POST['sitemainlanguage'];
  save_prefs();
}else if($_POST['sitemainlanguage']!=$pref['sitelanguage']){
  $tmp_mlang = ($pref['e107ml_flag'] == 1 ? 1 : 0 );
  $old_pref1 = $pref['sitelanguage'];
  $pref['sitelanguage'] = $_POST['sitemainlanguage'];
  if($_POST['sitemainlanguage'] != $pref['sitelang_init']){
    
    // Backup precedent preferences for default language
    // Backup prefs for initial language if not yet existing
    if(!$sql -> db_Select("core", "e107_name", "e107_name='bak_ml_".$pref['sitelang_init']."'")){
      save_prefs();
      $sql -> db_Select("core","e107_value","e107_name='pref'");
      list($tmp) = $sql -> db_Fetch();
      $sql -> db_Insert("core","'bak_ml_".$pref['sitelang_init']."', '".$tmp."'");
      unset($tmp);
    }else{
      // Backup prefs for other languages
      unset($pref['sitelanguage']);
      save_prefs("core",-1,"ml_".$old_pref1);
      $pref['sitelanguage'] = $_POST['sitemainlanguage'];;
      unset($tmp, $tmp_pref);
    }
    
    // Update default prefs with multilanguage prefs for the new site language and delete row as mutlilanguage prefs
    if($sql -> db_Select("core", "e107_value", "e107_name='ml_".$_POST['sitemainlanguage']."'")){
      list($tmp) = $sql -> db_Fetch();
      $tmp = stripslashes($tmp);
      $pref = unserialize($tmp);
    }
    
    ($tmp_mlang == 1 ? $pref['e107ml_flag'] = 1 : "" );
    save_prefs();
    
    // Delete main languages in the list of extra languages available for ML
    $result2 = @mysql_list_tables($mySQLdefaultdb);
    $table_ml_arr = array();
    if(DEBUG_ML){echo "<b>List of existing tables:</b> <br />";}
    $dblanlist = array();
    
    while ($row2 = mysql_fetch_row($result2)){
    	$table_ml_arr[] = $row2[0];
    	// Store all existing tables for multilanguage
    	$cc = 0;
    	while($lanlist[$cc]){
    		if(strstr($row2[0],strtolower($lanlist[$cc])) && $lanlist[$cc] != $_POST['sitemainlanguage']){
    			if(!$dblanlist[$lanlist[$cc]]){$dblanlist[$lanlist[$cc]] = "0";}
    			$tab_lg = explode(strtolower($lanlist[$cc]),$row2[0]);
    			$dblanlist[$lanlist[$cc]] .= "^".substr($tab_lg[1], 1);
    			if(DEBUG_ML){echo $dblanlist["Danish"]."<br />";}
    		}
    		$cc++;
    	}
    }
    $sql -> db_Update("core","e107_value='".$tmp_insml."' WHERE e107_name='e107_ml'");    
    if($sql -> db_Select("core", "e107_value", "e107_name='ml_".$_POST['sitemainlanguage']."'")){
      // Delete old prefs for this language
      $sql -> db_Delete("core","e107_name='ml_".$_POST['sitemainlanguage']."'");
    }
  }else{
    
    // Backup prefs for precedent language used as site language
    if(!$sql -> db_Select("core", "e107_value", "e107_name='ml_".$old_pref1."'")){
      unset($pref['sitelanguage'], $pref['e107ml_flag']);
      save_prefs("core",-1,"ml_".$old_pref1);
      $pref['sitelanguage'] = $old_pref1;
    }

    // Restore backup for initial language if existing and delete backup
    if($sql -> db_Select("core", "e107_value", "e107_name='bak_ml_".$pref['sitelang_init']."'")){
      list($tmp) = $sql -> db_Fetch();
      $tmp = stripslashes($tmp);
      $pref=unserialize($tmp);
      foreach($pref as $key => $prefvalue){
              $pref[$key] = textparse::formtparev($prefvalue);
      }
      $pref['sitelanguage'] = $_POST['sitemainlanguage'];
      ($tmp_mlang == 1 ? $pref['e107ml_flag'] = 1 : "" );
      save_prefs();
      $sql -> db_Delete("core","e107_name='bak_ml_".$pref['sitelang_init']."'");
    }
  }
  header("location: ".e_SELF);
}

?>
