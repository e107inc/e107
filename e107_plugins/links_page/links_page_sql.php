<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
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
  link_category_order int(10) unsigned NOT NULL default '0',
  link_category_class varchar(100) NOT NULL default '0',
  link_category_datestamp int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (link_category_id)
) ENGINE=MyISAM;
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
  link_datestamp int(10) unsigned NOT NULL default '0',
  link_author varchar(255) NOT NULL default '',
  PRIMARY KEY  (link_id)
) ENGINE=MyISAM;
# --------------------------------------------------------
