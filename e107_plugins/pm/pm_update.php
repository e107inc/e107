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

if (!defined('e107_INIT')) { exit; }

set_time_limit(300);

include_lan(e_PLUGIN."pm/languages/admin/".e_LANGUAGE.".php");

$sql->db_Update("plugin", "plugin_name=".ADLAN_PM." WHERE plugin_path='pm'");

require_once(e_HANDLER.'plugin_class.php');
$plugin = new e107plugin;

$sql -> db_Select_gen("
CREATE TABLE ".MPREFIX."private_msg (
pm_id int(10) unsigned NOT NULL auto_increment,
pm_from int(10) unsigned NOT NULL default '0',
pm_to varchar(250) NOT NULL default '',
pm_sent int(10) unsigned NOT NULL default '0',
pm_read int(10) unsigned NOT NULL default '0',
pm_subject text NOT NULL,
pm_text text NOT NULL,
pm_sent_del tinyint(1) unsigned NOT NULL default '0',
pm_read_del tinyint(1) unsigned NOT NULL default '0',
pm_attachments text NOT NULL,
pm_option varchar(250) NOT NULL default '',
pm_size int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (pm_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;");
			
$sql -> db_Select_gen("
CREATE TABLE ".MPREFIX."private_msg_block (
pm_block_id int(10) unsigned NOT NULL auto_increment,
pm_block_from int(10) unsigned NOT NULL default '0',
pm_block_to int(10) unsigned NOT NULL default '0',
pm_block_datestamp int(10) unsigned NOT NULL default '0',
pm_block_count int(10) unsigned NOT NULL default '0',
PRIMARY KEY  (pm_block_id)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;");

$plugin -> manage_plugin_prefs('add', 'plug_sc', 'pm', 'SENDPM');

pm_convert();

function pm_convert()
{
	global $sql, $uinfo;
	$sql2 = new db;
	$count = 0;
	if($sql->db_Select("pm_messages","*"))
	{
		while($row = $sql->db_Fetch())
		{
			$from = pm_convert_uid($row['pm_from_user']);
			$to = pm_convert_uid($row['pm_to_user']);
			$size = strlen($row['pm_message']);
			if($sql2->db_Insert("private_msg", "0, '".intval($from)."', '{$to}', '".intval($row['pm_sent_datestamp'])."', '".intval($row['pm_rcv_datestamp'])."', '{$row['pm_subject']}', '{$row['pm_message']}', '0', '0', '', '', '".intval($size)."'"))
			{
				//Insertion of new PM successful, delete old
				$sql2->db_Delete("pm_messages", "pm_id='{$row['pm_id']}'");
				$count++;
			}
		}
	}
}

function pm_convert_uid($name)
{
	global $uinfo, $tp;
	$sqlu = new db;
	$name = trim($name);
	if(!array_key_exists($uinfo[$name]))
	{
		if($sqlu->db_Select("user", "user_id", "user_name LIKE '".$tp -> todb($name, TRUE)."'"))
		{
			$row = $sqlu->db_Fetch();
			$uinfo[$name] = $row['user_id'];
		}
		else
		{
			if($sqlu->db_Select("user", "user_id", "user_loginname LIKE '".$tp -> todb($name, TRUE)."'"))
			{
				$row = $sqlu->db_Fetch();
				$uinfo[$name] = $row['user_id'];
			}
			else
			{
				return FALSE;
			}
		}
	}
	return $uinfo[$name];
}

?>