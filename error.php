<?php
require_once("class2.php");
require_once(HEADERF);

switch(e_QUERY){
	case 401:
		$text = "<div class='installe'>".LAN_1."</div><br /><div class='installh'>".LAN_2."</div><br /><div class='smalltext'>".LAN_3."</div>
		<br /><div class='installh'>".LAN_2."<a href='index.php'>".LAN_20."</a>";
	break;
	case 403:
		$text = "<div class='installe'>".LAN_4."</div><br /><div class='installh'>".LAN_5."</div><br /><div class='smalltext'>".LAN_6."</div>
		<br /><div class='installh'>".LAN_2."<a href='index.php'>".LAN_20."</a>";
	break;
	case 404:
		$text = "<div class='installe'>".LAN_7."</div><br /><div class='installh'>".LAN_8."</div><br /><div class='smalltext'>".LAN_9."</div>
		<br /><div class='installh'>".LAN_2."<a href='index.php'>".LAN_20."</a>";
	break;
	case 500:
		$text = "<div class='installe'>".LAN_10."</div><br /><div class='installh'>".LAN_11."</div><br /><div class='smalltext'>".LAN_12."</div>
		<br /><div class='installh'>".LAN_2."<a href='index.php'>".LAN_20."</a>";
	break;
	default:
		$text = "<div class='installe'>".LAN_13."</div><br /><div class='installh'>".LAN_14."</div><br /><div class='smalltext'>".LAN_15."</div>
		<br /><div class='installh'>".LAN_2."<a href='index.php'>".LAN_20."</a>";
}
$ns -> tablerender(PAGE_NAME." ".e_QUERY, $text);
require_once(FOOTERF);
?>