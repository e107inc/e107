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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/links_page/links_page_sql.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-29 20:22:08 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
header("location:../index.php");
exit;
?>
# Table structure for table `links_page_cat`
#
CREATE TABLE links_page_cat (
  link_category_id int(10) unsigned NOT NULL auto_increment,
  link_category_name varchar(100) NOT NULL default '',
  link_category_description varchar(250) NOT NULL default '',
  link_category_icon varchar(100) NOT NULL default '',
  link_category_order varchar(100) NOT NULL default '0',
  link_category_class varchar(100) NOT NULL default '0',
  link_category_datestamp int(10) unsigned NOT NULL default '',
  PRIMARY KEY  (link_category_id)
) TYPE=MyISAM;
# --------------------------------------------------------

# Table structure for table `links_page`
#
CREATE TABLE links_page (
  link_id int(10) unsigned NOT NULL auto_increment,
  link_name varchar(100) NOT NULL default '',
  link_url varchar(200) NOT NULL default '',
  link_description text NOT NULL,
  link_button varchar(100) NOT NULL default '',
  link_category tinyint(3) unsigned NOT NULL default '0',
  link_order int(10) unsigned NOT NULL default '0',
  link_refer int(10) unsigned NOT NULL default '0',
  link_open tinyint(1) unsigned NOT NULL default '0',
  link_class tinyint(3) unsigned NOT NULL default '0',
  link_datestamp int(10) unsigned NOT NULL default '',
  PRIMARY KEY  (link_id)
) TYPE=MyISAM;
# --------------------------------------------------------
