<?php

// Basic textparse function for flat files
// Copyright: e107Dev Team (http://e107.org)

class e107_basicparse{
  
  function e107in_basic($text){
    $search = array("\"");
    $replace = array("&quot;");
    $text = str_replace($search, $replace, stripslashes($text));
    
    return $text;
  }
  
  function e107out_basic($text){
    $search = array("&quot;");
    $replace = array("\"");
    $text = str_replace($search, $replace, $text);
    //$text = addslashes($text);
    return $text;
  }


}

?>
