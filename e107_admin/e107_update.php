<?php

require_once("../class2.php");
require_once("auth.php");

$dbupdate=array(	"611_to_612" => LAN_UPDATE_8." .611 ".LAN_UPDATE_9." .612",
						"613b_to_614" => LAN_UPDATE_8." .613b ".LAN_UPDATE_9." .614");

/*################  613b to 614  #####################*/
function updatecheck_613b_to_614(){
	global $sql;
	if($sql -> db_Select("parser") === FALSE){
		return FALSE;
	} else {
		return TRUE;
	}
}

function updatedo_613b_to_614(){
	global $sql;
	$qry = "CREATE TABLE ".MPREFIX."parser (
  parser_id int(10) unsigned NOT NULL auto_increment,
  parser_pluginname varchar(100) NOT NULL default '',
  parser_regexp varchar(100) NOT NULL default '',
  PRIMARY KEY  (parser_id)
) TYPE=MyISAM;";
	$sql -> db_Select_gen($qry);
}

/*################  611 to 612  #####################*/
function updatecheck_611_to_612(){  //If already installed return TRUE
	global $mySQLdefaultdb;
	$fields = mysql_list_fields($mySQLdefaultdb,MPREFIX."news");
	$columns = mysql_num_fields($fields);
	for ($i = 0; $i < $columns; $i++) {
	   if("news_render_type" == mysql_field_name($fields, $i)){return TRUE;}
	}
}

function updatedo_611_to_612(){
	mysql_query("ALTER TABLE ".MPREFIX."news ADD news_render_type TINYINT UNSIGNED NOT NULL ");
	mysql_query("ALTER TABLE ".MPREFIX."content CHANGE content_parent content_parent INT UNSIGNED DEFAULT '0' NOT NULL ");
}

if($_POST){
	foreach($dbupdate as $func => $rmks){
		$installed = call_user_func("updatecheck_".$func);
		if((LAN_UPDATE_4 == $_POST[$func] || $_POST['updateall']) && !$installed){
			if(function_exists("updatedo_".$func)){
				$message .=LAN_UPDATE_7." {$rmks}<br />";
				call_user_func("updatedo_".$func);
			}
		}
	}
}

if($message){
	$ns -> tablerender("&nbsp;",$message);
}

$text = "
<form method='POST' action='".e_SELF."'>
<table class='fborder'>
<tr>
	<td class='fcaption'>".LAN_UPDATE_1."</td>
	<td class='fcaption'>".LAN_UPDATE_2."</td>
</tr>
";

$updates=0;
foreach($dbupdate as $func => $rmks){
	if(function_exists("updatecheck_".$func)){
		$text .= "<tr><td class='forumheader3'>{$rmks}</td>";
		if(call_user_func("updatecheck_".$func)){
			$text .= "<td class='forumheader3'>".LAN_UPDATE_3."</td>";
		} else {
			$updates++;
			$text .= "<td class='forumheader3'><input class='button' type='submit' name='{$func}' value='".LAN_UPDATE_4."' /></td>";
		}
		$text .= "</tr>";
	}
}
if($updates){
	$text .= "
	<tr><td class='forumheader3'></td><td class='forumheader3'></td></tr>
	<tr><td class='forumheader3'>{$updates} ".LAN_UPDATE_5."</td><td class='forumheader3'><input class='button' type='submit' name='updateall' value='".LAN_UPDATE_6."' /></td></tr>";
}

$text .= "</table></form>";
$ns -> tablerender(LAN_UPDATE_10,$text);
require_once("footer.php");
