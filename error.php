<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107/error.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-01-05 10:35:42 $
|     $Author: pholzmann $
+----------------------------------------------------------------------------+
*/



require_once("class2.php");
require_once(HEADERF);

$errFrom=$_SERVER['HTTP_REFERER'];
$errTo  =$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

switch(e_QUERY){
        case 401:
                $text = "<div class='installe'>".LAN_1."</div><br /><div class='installh'>".LAN_2."</div><br /><div class='smalltext'>".LAN_3."</div>
                <br /><div class='installh'>".LAN_2."<a href='index.php'>".LAN_20."</a></div>";
        break;
        case 403:
                $text = "<div class='installe'>".LAN_4."</div><br /><div class='installh'>".LAN_5."</div><br /><div class='smalltext'>".LAN_6."</div>
                <br /><div class='installh'>".LAN_2."<a href='index.php'>".LAN_20."</a></div>";
        break;
        default:
        case 404:
                $text = "<div class='installe'>".LAN_7."</div><br /><div class='installh'>".LAN_8."</div><br /><div class='smalltext'>".LAN_9."</div>
                <br /><div class='installh'>".LAN_2."
                <br />Your unsuccessful attempt to access <b>{$errTo}</b> has been recorded.";
                if (strlen($errFrom)) $text .= "<br />Apparently, you were referred here by $errFrom -- Unfortunately, there's an obsolete or just plain wrong link at that address.";
                $text .="<br /> <a href='index.php'>".LAN_20."</a></div>";
        break;
        case 500:
                $text = "<div class='installe'>".LAN_10."</div><br /><div class='installh'>".LAN_11."</div><br /><div class='smalltext'>".LAN_12."</div>
                <br /><div class='installh'>".LAN_2."<a href='index.php'>".LAN_20."</a></div>";
        break;
                $text = "<div class='installe'>".LAN_13." (".$_SERVER['QUERY_STRING'].")</div><br /><div class='installh'>".LAN_14."</div><br /><div class='smalltext'>".LAN_15."</div>
                <br /><div class='installh'>".LAN_2."<a href='index.php'>".LAN_20."</a></div>";
}
$ns -> tablerender(PAGE_NAME." ".e_QUERY, $text);
require_once(FOOTERF);
?>