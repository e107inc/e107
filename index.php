<?php
/*
+ ----------------------------------------------------------------------------+
     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107/index.php,v $
|     $Revision: 1.5 $
|     $Date: 2004-08-14 01:48:48 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

require_once("class2.php");
if($pref['membersonly_enabled'] && !USER){
   header("location: ".e_BASE.e_LOGIN);
        exit;
}

if(!$pref['frontpage'] || $pref['frontpage_type'] == "splash"){
        header("location: ".e_BASE."news.php");
        exit;
}else if(is_numeric($pref['frontpage'])){
        header("location: ".e_BASE."content.php?article.".$pref['frontpage'].".255");
        exit;
}else if(eregi("http", $pref['frontpage'])){
        header("location: ".e_BASE.$pref['frontpage']);
        exit;
}else{
        header("location: ".e_BASE.$pref['frontpage'].".php");
        exit;
}
?>