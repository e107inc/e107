<?php
/*
*
* Switch languages, called from class2.php
*
*/
// $Id: switch.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 
global $ADMIN_DIRECTORY;
if(IsSet($_POST['setlanguage2']) && $_POST['setlanguage2']!=""){
	$tmp_dblg = $_POST['setlanguage2'];
	if(e_MLANGDIF==1){
		$tmp_lg = false;
	}else{
		$tmp_lg = $_POST['setlanguage2'];
	}
}else if(IsSet($_POST['setlanguage']) && $_POST['setlanguage']!=""){
	if(strstr(e_SELF, $ADMIN_DIRECTORY)){$_POST['sitelanguage']=$_POST['setlanguage'];}
	if(e_MLANGDIF==1){
		if(IsSet($_POST['sitedblanguage']) && $_POST['sitedblanguage']!=""){
			$tmp_dblg = $_POST['sitedblanguage'];
		}else{
			$tmp_dblg = false;
		}
		$tmp_lg = $_POST['sitelanguage'];
	}else{
		$tmp_dblg = false;
		$tmp_lg = $_POST['sitelanguage'];
	}
	if(strstr(e_SELF, $ADMIN_DIRECTORY)){$tmp_dblg = $_POST['sitelanguage'];}
}else{
	$tmp_lg = false;
	$tmp_dblg = false;
}

/*
* 
* Switch for guests
* Using sessions or cookies in regard of site preference
* 
*/
if(!$_COOKIE[$pref['cookie_name']] && !$_SESSION[$pref['cookie_name']]){
  if($pref['user_tracking'] == "session"){
    if($tmp_lg != FALSE){$_SESSION['e107language_'.md5(SITEURL)] = $tmp_lg;}
    if($tmp_dblg != FALSE){$_SESSION['e107dblanguage_'.md5(SITEURL)] = $tmp_dblg;}
  }else{
    if($tmp_lg != FALSE){setcookie('e107language_'.md5(SITEURL),$tmp_lg,time()+86400) ;}
    if($tmp_dblg != FALSE){setcookie('e107dblanguage_'.md5(SITEURL),$tmp_dblg,time()+86400) ;}
  }
/*
* 
* define interface language for guest
* 
*/ 
  if($tmp_lg != FALSE){
    define("USERLAN", $tmp_lg);
  }else if(isset($_SESSION['e107language_'.md5(SITEURL)])){
    define("USERLAN", $_SESSION['e107language_'.md5(SITEURL)]);
  }else if(isset($_COOKIE['e107language_'.md5(SITEURL)])){
    define("USERLAN", $_COOKIE['e107language_'.md5(SITEURL)]);
  }else{
    define("USERLAN", FALSE);
  }
/*
* 
* define database language for guest
* 
*/   
  if($tmp_dblg != FALSE){
    define("USERDBLAN", $tmp_dblg);
  }else if(isset($_SESSION['e107dblanguage_'.md5(SITEURL)])){
    define("USERDBLAN", $_SESSION['e107dblanguage_'.md5(SITEURL)]);
  }else if(isset($_COOKIE['e107dblanguage_'.md5(SITEURL)])){
    define("USERDBLAN", $_COOKIE['e107dblanguage_'.md5(SITEURL)]);
  }else{
    define("USERDBLAN", FALSE);
  }
  //echo "--> ".USERDBLAN." - ".$_COOKIE['e107dblanguage_'.md5(SITEURL)];
}else{
/*
* 
* Switch for members
* Using sessions or cookies in regard of site preference
* 
*/
  if($tmp_lg!=false){
  	$user_pref['sitelanguage'] = $tmp_lg;
  }
  if($tmp_dblg!=false){
  	$user_pref['sitedblanguage'] = $tmp_dblg;
  }else{
  	unset($user_pref['sitedblanguage']);
  }
  // echo "USERPREFS DEBUG: ".$user_pref['sitelanguage']." - ".$user_pref['sitedblanguage'];
  save_prefs($user);
}
?>
