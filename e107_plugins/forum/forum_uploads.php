<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
 */

require_once("../../class2.php");
$e107 = e107::getInstance();
if (!$e107->isInstalled('forum')) 
{
	header('Location: '.e_BASE.'index.php');
	exit;
}

if(!USER)
{
	header("location:".e_BASE.$PLUGINS_DIRECTORY."forum/forum.php");
	exit;
}
include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_uploads.php');

if(is_array($_POST['delete']))
{
	foreach(array_keys($_POST['delete']) as $fname)
	{
		$f = explode("_", $fname);
		if($f[1] == USERID)
		{
			$path = e_UPLOAD.$fname;
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
$fileList = $fi->get_files(e_UPLOAD, $mask);
if($sql->db_Select('forum_t','thread_id, thread_thread, thread_parent', "thread_thread REGEXP '.*_".USERID."_FT.*'"))
{
	$threadList = $sql->db_getList();
}

$filecount = 0;
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
			$filecount++;
			$txt .= "<tr><td class='forumheader3'><a href='".e_UPLOAD.$finfo['fname']."'>{$finfo['fname']}</a></td>";
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
if(!$filecount) {
	$ns->tablerender(FRMUP_1,FRMUP_9);
	include_once(FOOTERF);
	exit;
}

$ns->tablerender(FRMUP_1, $txt);
include_once(FOOTERF);

?>