<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/plugins/usertheme.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");

$table = "CREATE TABLE ".MUSER."themes (
  theme_id int(10) unsigned NOT NULL auto_increment,
  theme_name varchar(100) NOT NULL default '',
  PRIMARY KEY  (theme_id),
  UNIQUE KEY theme_name (theme_name)
) TYPE=MyISAM;

$handle=opendir("themes/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "templates" && $file != "shared"){
		$sql -> db_Insert("themes", " 0, '$file' ");
		$dirlist[] = $file;
	}
}
closedir($handle);













?>