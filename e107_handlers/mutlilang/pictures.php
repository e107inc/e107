<?php

/*
*
* Functions to manage spictures with mutlilanguage support
*
* eMLANG_path() : Allow to display pictures in regard of theme/language used.
*
*/


function eMLANG_path($file_name,$sub_folder){
  $imgpath = e_IMAGE.$sub_folder."/".$file_name;
  if (file_exists(e_IMAGE.$sub_folder."/".e_LANGUAGE."/".$file_name)){
   $imgpath = e_IMAGE.$sub_folder."/".e_LANGUAGE."/".$file_name;
  }
  if (file_exists(THEME.$sub_folder."/".$file_name)){
   $imgpath = THEME.$sub_folder."/".$file_name;
  }
  if (file_exists(THEME.$sub_folder."/".e_LANGUAGE."/".$file_name)){
   $imgpath = THEME.$sub_folder."/".e_LANGUAGE."/".$file_name;
  }
  return $imgpath;
}

?>
