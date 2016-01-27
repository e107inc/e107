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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/poll/oldpolls.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

require_once("../../class2.php");
if (!e107::isInstalled('poll'))
{
	e107::redirect();
	exit;
}
require_once(HEADERF);
require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
$gen = new convert;
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

include_lan(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");

if(e_QUERY)
{
	$query = "SELECT p.*, u.user_id, u.user_name FROM #polls AS p
	LEFT JOIN #user AS u ON p.poll_admin_id = u.user_id
	WHERE p.poll_type=1 AND p.poll_id=".intval(e_QUERY);

	if($sql->db_Select_gen($query))
	{

		$row = $sql -> db_Fetch();
		extract($row);

		$optionArray = explode(chr(1), $poll_options);
		$optionArray = array_slice($optionArray, 0, -1);
		$voteArray = explode(chr(1), $poll_votes);
		$voteArray = array_slice($voteArray, 0, -1);

		$voteTotal = array_sum($voteArray);
		$percentage = array();
		foreach($voteArray as $votes)
		{
			$percentage[] = round(($votes/$voteTotal) * 100, 2);
		}

		$start_datestamp = $gen->convert_date($poll_datestamp, "long");
		$end_datestamp = $gen->convert_date($poll_end_datestamp, "long");
		//<a href='".e_BASE."user.php?id.{$user_id}'>".$user_name."</a>
		$uparams = array('id' => $user_id, 'name' => $user_name);
		$link = e107::getUrl()->create('user/profile/view', $uparams);
		$userlink = "<a href='".$link."'>".$user_name."</a>";
		$text = "<table style='".USER_WIDTH."'>
		<tr>
		<td colspan='2' class='mediumtext' style='text-align:center'>
		<b>".$tp -> toHTML($poll_title,TRUE,'TITLE')."</b>
		<div class='smalltext'>".POLLAN_35." ".$userlink."<br /> ".POLLAN_37." ".$start_datestamp." ".POLLAN_38." ".$end_datestamp.".<br />".POLLAN_26.": {$voteTotal}</div>
		<br />

		</td>
		</tr>";

		$count = 0;

		$barl = (file_exists(THEME."images/barl.png") ? THEME."images/barl.png" : e_PLUGIN."poll/images/barl.png");
		$barr = (file_exists(THEME."images/barr.png") ? THEME."images/barr.png" : e_PLUGIN."poll/images/barr.png");
		$bar = (file_exists(THEME."images/bar.png") ? THEME."images/bar.png" : e_PLUGIN."poll/images/bar.png");

		foreach($optionArray as $option)
		{
			$text .= "
			<tr>
			<td style='width:40%; text-align: right' class='mediumtext'><b>".$tp -> toHTML($option, TRUE, 'TITLE')."</b>&nbsp;&nbsp;</td>
			<td class='smalltext'>
			<div style='background-image: url($barl); width: 5px; height: 14px; float: left;'>
			</div>
			<div style='background-image: url($bar); width: ".(floor($percentage[$count]) != 100 ? floor($percentage[$count]) : 95)."%; height: 14px; float: left;'>
			</div>
			<div style='background-image: url($barr); width: 5px; height: 14px; float: left;'>
			</div>
			".$percentage[$count]."% [".POLLAN_31.": ".$voteArray[$count]."]
			</td>
			</tr>\n";
			$count++;

		}

		$query = "SELECT c.*, u.* FROM #comments AS c
		LEFT JOIN #user AS u ON FLOOR(SUBSTR(c.comment_author,1,INSTR(c.comment_author,'.')-1))=u.user_id
		WHERE comment_item_id=".intval($poll_id)." AND comment_type=4 ORDER BY comment_datestamp";
		if ($comment_total = $sql->db_Select_gen($query) !== FALSE)
		{
			$text .= "<tr><td colspan='2'><br /><br />";
			while ($row = $sql->db_Fetch()) {
				$text .= $cobj->render_comment($row);
			}
			$text .= "</td></tr>";
		}

		$text .= "</table>";
		$ns->tablerender(POLL_ADLAN01." #".$poll_id, $text);
	}
}

$query = "SELECT p.*, u.user_name FROM #polls AS p
LEFT JOIN #user AS u ON p.poll_admin_id = u.user_id
WHERE p.poll_type=1
ORDER BY p.poll_datestamp DESC";

if(!$sql->db_Select_gen($query))
{
	$ns->tablerender(POLLAN_28, "<div style='text-align:center'>".POLLAN_33."</div>");
	require_once(FOOTERF);
	exit;
}

$array = $sql -> db_getList();
$oldpollArray = array_slice($array, 1);

if(!count($oldpollArray))
{
	$ns->tablerender(POLLAN_28, "<div style='text-align:center'>".POLLAN_33."</div>");
	require_once(FOOTERF);
	exit;
}

$text = "<table class='fborder' style='".USER_WIDTH."'>
<tr>
<td class='fcaption' style='width: 55%;'>".POLLAN_34."</td>
<td class='fcaption' style='width: 15%;'>".POLLAN_35."</td>
<td class='fcaption' style='width: 30%;'>".POLLAN_36."</td>
</tr>\n";

if (!is_object($tp->e_bb))
{
	require_once(e_HANDLER.'bbcode_handler.php');
	$tp->e_bb = new e_bbcode;
}

foreach($oldpollArray as $oldpoll)
{
	extract($oldpoll);
	$from = $gen->convert_date($poll_datestamp, "short");
	$to = $gen->convert_date($poll_end_datestamp, "short");

	$poll_title = $tp->e_bb->parseBBCodes($poll_title, 0,TRUE,TRUE);		// Strip bbcodes
//<a href='".e_BASE."user.php?id.{$poll_admin_id}'>{$user_name}</a>
	$uparams = array('id' => $poll_admin_id, 'name' => $user_name);
	$link = e107::getUrl()->create('user/profile/view', $uparams);
	$userlink = "<a href='".$link."'>".$user_name."</a>";
	$text .= "<tr>
	<td class='forumheader3' style='width: 55%;'><a href='".e_SELF."?{$poll_id}'>{$poll_title}</a></td>
	<td class='forumheader3' style='width: 15%;'>".$userlink."</td>
	<td class='forumheader3' style='width: 30%;'>{$from} ".POLLAN_38." {$to}</td>
	</tr>\n";
}

$text .= "</table>";
$ns->tablerender(POLLAN_28, $text);
require_once(FOOTERF);

?>
