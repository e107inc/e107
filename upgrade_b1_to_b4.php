<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/template.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);


mysql_query("ALTER TABLE `".MUSER."content` CHANGE `content_parent` `content_summary` TEXT NOT NULL");
mysql_query("ALTER TABLE `".MUSER."_menus` ADD `menu_class` TINYINT UNSIGNED NOT NULL");
mysql_query("CREATE TABLE ".MUSER."banner (
  banner_id int(10) unsigned NOT NULL auto_increment,
  banner_clientname varchar(100) NOT NULL default '',
  banner_clientlogin varchar(20) NOT NULL default '',
  banner_clientpassword varchar(50) NOT NULL default '',
  banner_image varchar(150) NOT NULL default '',
  banner_clickurl varchar(150) NOT NULL default '',
  banner_impurchased int(10) unsigned NOT NULL default '0',
  banner_startdate int(10) unsigned NOT NULL default '0',
  banner_enddate int(10) unsigned NOT NULL default '0',
  banner_active tinyint(1) unsigned NOT NULL default '0',
  banner_clicks int(10) unsigned NOT NULL default '0',
  banner_impressions int(10) unsigned NOT NULL default '0',
  banner_ip text NOT NULL,
  banner_campaign varchar(150) NOT NULL default '',
  PRIMARY KEY  (banner_id)
) TYPE=MyISAM;");

mysql_query("INSERT INTO ".MUSER."banner VALUES (1, 'e107', 'e107login', 'e107password', 'e107.jpg', 'http://e107.org', 0, 0, 0, 1, 0, 0, '', 'campaign_one')");



echo "<div style=\"text-align:center\"><b>Core updated to v5.4 beta 4.</b></div>";




require_once(FOOTERF);
?>