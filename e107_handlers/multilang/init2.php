<?php
/*
*
* Initialize the mutlilanguage stuff, called from class2.php
*
* e_DBLANGUAGE = language user for db content ()
*/
// $Id: init2.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 
if(e_MLANGDIF==1){
	if(USERDBLAN){
		define("e_DBLANGUAGE",USERDBLAN);
	}else if(USERLAN){
		define("e_DBLANGUAGE",USERLAN);
	}else{
		define("e_DBLANGUAGE",e_LANGUAGE);
	}
}else{
	if(USERLAN){
		define("e_DBLANGUAGE",USERLAN);
	}else{
		define("e_DBLANGUAGE",e_LANGUAGE);
	}
}

if(e_DBLANGUAGE != e_LAN){
  $tmp_val = 0;
  if(e_MLANGDIF==1 && $sql -> db_Select("core", "*", "e107_name='ml_".e_DBLANGUAGE."'")){
    $tmp_val = 1;
  }else if($sql -> db_Select("core", "*", "e107_name='ml_".e_LANGUAGE."'")){
    $tmp_val = 1;
  }
}

if($tmp_val == 1){
  global $sysprefs;
  require_once(e_HANDLER."pref_class.php");
  $sysprefs = new prefs;
  $tmp = $sysprefs -> get('ml_".e_DBLANGUAGE."');
  $pref = unserialize($tmp);
  
  if(!is_array($pref)){
  	$pref= $sysprefs -> getArray('ml_".e_DBLANGUAGE."');
  }
}

// Fixed preferences as defined variables
define("e_MLANG_ALERT",($pref['e107ml_mailalert'] ? TRUE : FALSE ));
define("e_MLANG_EMAILALERT",$pref['e107ml_mailadress']);
//define("",$pref['']);

?>
