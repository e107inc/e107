<?php

/*
 Usage: simply replace your <img src='filename.jpg'
 with
 <img src='".e_IMAGE."thumb.php?filename.jpg+size"' />
 or
 <img src='".e_IMAGE."thumb.php?<full path to file>/filename.jpg+size"' />
 eg <img src='".e_IMAGE."thumb.php?home/images/myfilename.jpg+100)"' />

*/

require_once("../class2.php");
require_once(e_HANDLER."resize_handler.php");

    if (e_QUERY){
        $tmp = explode("+",rawurldecode(e_QUERY));
        if(preg_match("#^/#",$tmp[0]) || preg_match("#.:#",$tmp[0])){
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