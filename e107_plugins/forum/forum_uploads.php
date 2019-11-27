<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("../../class2.php");
$e107 = e107::getInstance();
if (!$e107->isInstalled('forum')) 
{
	e107::redirect();
	exit;
}

if(!USER)
{
	header("location:".e_PLUGIN."forum/forum.php");
	exit;
}

e107::lan('forum', "front", true);



if(is_array($_POST['delete']))
{
	foreach(array_keys($_POST['delete']) as $fname)
	{
		$f = explode("_", $fname);
		if($f[1] == USERID)
		{
			$path = e_UPLOAD.e107::getParser()->filter($fname,'w');
			if(unlink($path) == TRUE)
			{
				$msg = LAN_FORUM_7002.": $path";
			}
			else
			{
				$msg = LAN_FORUM_7003.": $path";
			}
		}
	}
}

include_once(e_HANDLER."file_class.php");
include_once(HEADERF);
if($msg)
{
	$ns->tablerender(LAN_FORUM_7004, $msg);
}

$fi = new e_file;
$mask = ".*_".USERID."_FT.*";
$fileList = $fi->get_files(e_UPLOAD, $mask);
if($sql->db_Select('forum_thread','thread_id, thread_thread, thread_parent', "thread_thread REGEXP '.*_".USERID."_FT.*'")) // FIXME new forum db structure
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
		<td class='fcaption'>".LAN_FORUM_7006."</td>
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
					$txt .= "<td class='forumheader3'>".LAN_FORUM_7007.": <a href='".e_PLUGIN."forum/forum_viewtopic.php?{$tinfo['thread_id']}.post'>{$tinfo['thread_parent']}</a></td>";
				}
				else
				{
					$txt .= "<td class='forumheader3'>".LAN_FORUM_7007.": <a href='".e_PLUGIN."forum/forum_viewtopic.php?{$tinfo['thread_id']}'>{$tinfo['thread_id']}</a></td>";
				}
			
			}
			else
			{
				$txt .= "<td class='forumheader3'>".LAN_FORUM_7008." <input class='btn btn-default btn-secondary button' type='submit' name='delete[{$finfo['fname']}]' value='".LAN_DELETE."' /></td>";
			}
			$txt .= "</tr>";
		}
	}
	$txt .= "</table>";
}
if(!$filecount) {
	$ns->tablerender(LAN_FORUM_7001,LAN_FORUM_7009);
	include_once(FOOTERF);
	exit;
}

$ns->tablerender(LAN_FORUM_7001, $txt);
include_once(FOOTERF);

?>