<?php
/*
*
* Initialize the mutlilanguage stuff, called from class2.php
*
* e_MLANG = multilanguage status (1: activated or FALSE: not running)
* e_MLANGNBR = count all available languages for database translation
* e_MLANGDIF = different languages available for interface and content (1: allowed or FALSE: not allowed)
*
*/
// $Id: init.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 

if(USER){
	define("e_MLANG",1);
}
if($sql -> db_Select("core", "*", "e107_name='e107_ml'")){
	$ml_row = $sql -> db_Fetch();
	extract ($ml_row);
	$tmp_e107lang = unserialize($ml_row[1]);
}else{
	$tmp_e107lang = array();
}
define("e_MLANGNBR",count($tmp_e107lang));

for($c=0;$c<e_MLANGNBR;$c++){
    $list_e107lang[$c] = key($tmp_e107lang);
    $tmp = array();
    $tables_lang[$list_e107lang[$c]] = array();
    $tmp = explode("^",$tmp_e107lang[$list_e107lang[$c]]);
    for($i=1;$i<count($tmp);$i++){
      $tables_lang[$list_e107lang[$c]][] = $tmp[$i];
      //echo "X".$tables_lang[$list_e107lang[$c]][0];
    }
  	next($tmp_e107lang);
}
//unset($switch_mainlang);

// Next lines used for test only
//$list_e107lang = array("French","German","Russian");
//

if($pref['e107ml_2lg']==1){
	define("e_MLANGDIF",1);
}else{
	define("e_MLANGDIF",FALSE);
}




?>
