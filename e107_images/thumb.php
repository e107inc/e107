<?php

/*
 Usage: simple replace your <img src='filename.jpg'
 with
 <img src='".e_IMAGE."thumb.php?filename.jpg+size)"'>

*/

require_once("../class2.php");
require_once(e_HANDLER."resize_handler.php");

  if (e_QUERY){
    $tmp = explode("+",e_QUERY);
    if(eregi("home",$tmp[0]) || eregi($_SERVER['DOCUMENT_ROOT'],$tmp[0])){
    $source = $tmp[0];
    }else{
    $source = "../".str_replace("../","",$tmp[0]);
    }

    $newsize = $tmp[1];
    if(!resize_image($source, "stdout", $newsize)){
    echo "Couldn't find: ".$source;
    }

}
?>