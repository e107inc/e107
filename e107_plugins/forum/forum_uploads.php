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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/forum_uploads.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-02-24 20:43:00 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");

if(!USER)
{
	header("location:".e_BASE.$PLUGINS_DIRECTORY."forum/forum.php");
	exit;
}
$lan_file = e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_uploads.php';
include(file_exists($lan_file) ? $lan_file : e_PLUGIN.'forum/languages/English/lan_forum_uploads.php');

if(is_array($_POST['delete']))
{
	foreach(array_keys($_POST['delete']) as $fname)
	{
		$f = explode("_", $fname);
		if($f[1] == USERID)
		{
			$path = e_FILE."public/".$fname;
			if(unlink($path) == TRUE)
			{
				$msg = FRMUP_2.": $path";
			}
			else
			{
				$msg = FRMUP_3.": $path";
			}
		}
	}
}

include_once(e_HANDLER."file_class.php");
include_once(HEADERF);
if($msg)
{
	$ns->tablerender(FRMUP_4, $msg);
}

$fi = new e_file;
$mask = ".*_".USERID."_FT.*";
$fileList = $fi->get_files(e_FILE."public", $mask);
if($sql->db_Select('forum_t','thread_id, thread_thread, thread_parent', "thread_thread REGEXP '.*_".USERID."_FT.*'"))
{
	$threadList = $sql->db_getList();
}

if(is_array($fileList))
{
	$txt = "
	<form method='post' action='".e_SELF."'>
	<table style='width:98%'>
	<tr>
		<td class='fcaption'>".FRMUP_5."</td>
		<td class='fcaption'>".FRMUP_6."</td>
	</tr>";
	foreach($fileList as $finfo)
	{
		if($finfo['fname'])
		{
			$txt .= "<tr><td class='forumheader3'><a href='".e_FILE."public/{$finfo['fname']}'>{$finfo['fname']}</a></td>";
			$found = FALSE;
			if(is_array($threadList))
			{
				foreach($threadList as $tinfo)
				{
					if(strpos($tinfo['thread_thread'], $finfo['fname']) != FALSE)
					{
						$found = $tinfo;
						break;
					}
				}
			}
			if($found != FALSE)
			{
				if($tinfo['thread_parent'])
				{
					$txt .= "<td class='forumheader3'>".FRMUP_7.": <a href='".e_PLUGIN."forum/forum_viewtopic.php?{$tinfo['thread_id']}.post'>{$tinfo['thread_parent']}</a></td>";
				}
				else
				{
					$txt .= "<td class='forumheader3'>".FRMUP_7.": <a href='".e_PLUGIN."forum/forum_viewtopic.php?{$tinfo['thread_id']}'>{$tinfo['thread_id']}</a></td>";
				}	
			
			}
			else
			{
				$txt .= "<td class='forumheader3'>".FRMUP_8." <input class='button' type='submit' name='delete[{$finfo['fname']}]' value='".FRMUP_10."' /></td>";
			}
			$txt .= "</tr>";
		}
	}
	$txt .= "</table>";
}
else
{
	$ns->tablerender(FRMUP_1,FRMUP_9);
	include_once(FOOTERF);
	exit;
}

$ns->tablerender(FRMUP_1, $txt);
include_once(FOOTERF);

