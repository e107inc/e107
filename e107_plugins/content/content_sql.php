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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/content_sql.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-11 22:23:20 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
header("location:../index.php");
exit;
?>
# Table structure for table `content`
#

CREATE TABLE content (
  content_id int(10) unsigned NOT NULL auto_increment,
  content_heading tinytext NOT NULL,
  content_subheading tinytext NOT NULL,
  content_content text NOT NULL,
  content_parent int(10) unsigned NOT NULL default '0',
  content_datestamp int(10) unsigned NOT NULL default '0',
  content_author varchar(200) NOT NULL default '',
  content_comment tinyint(3) unsigned NOT NULL default '0',
  content_summary text NOT NULL,
  content_type tinyint(3) unsigned NOT NULL default '0',
  content_review_score tinyint(3) unsigned NOT NULL default '0',
  content_pe_icon tinyint(1) unsigned NOT NULL default '0',
  content_class tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (content_id)
) TYPE=MyISAM;
# --------------------------------------------------------

