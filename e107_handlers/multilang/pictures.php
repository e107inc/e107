<?php

/*
*
* Functions to manage pictures with mutlilanguage support
*
* eMLANG_path() : Allow to display pictures in regard of theme/language used.
*
*/
// $Id: pictures.php,v 1.2 2004-10-10 21:20:07 loloirie Exp $ 

function eMLANG_path($file_name,$sub_folder){  
  if (file_exists(THEME.$sub_folder."/".e_LANGUAGE."/".$file_name)){
    $imgpath = THEME.$sub_folder."/".e_LANGUAGE."/".$file_name;
  }else if (file_exists(THEME.$sub_folder."/".$file_name)){
    $imgpath = THEME.$sub_folder."/".$file_name;
  }else if (file_exists(e_IMAGE.$sub_folder."/".e_LANGUAGE."/".$file_name)){
    $imgpath = e_IMAGE.$sub_folder."/".e_LANGUAGE."/".$file_name;
  }else{
    $imgpath = e_IMAGE.$sub_folder."/".$file_name;
  }
  return $imgpath;
}

?>
