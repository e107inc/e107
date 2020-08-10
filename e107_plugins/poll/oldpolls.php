<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
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

if(!defined("USER_WIDTH") && !deftrue('BOOTSTRAP'))
{
	define("USER_WIDTH","width:95%");
}

e107::includeLan(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");

if(e_QUERY)
{

	require_once('poll_class.php');

	$query = "SELECT p.*, u.user_id, u.user_name FROM #polls AS p
	LEFT JOIN #user AS u ON p.poll_admin_id = u.user_id
	WHERE p.poll_type=1 AND p.poll_id=".intval(e_QUERY);

	if($sql->gen($query))
	{
		$pl = new poll;
		$row = $sql ->fetch();

		$start_datestamp    = $tp->toDate($row['poll_datestamp'], "long");
		$end_datestamp      = $tp->toDate($row['poll_end_datestamp'], "long");
		$uparams            = array('id' => $row['user_id'], 'name' => $row['user_name']);
		$link               = e107::getUrl()->create('user/profile/view', $uparams);
		$userlink           = "<a href='".$link."'>".$row['user_name']."</a>";


		$text = $pl->render_poll($row, 'forum', 'oldpolls',true);


		$text .= "
		<div class='smalltext text-right'>
		<small>".LAN_POSTED_BY." ".$userlink."<br /> ".$tp->lanVars(POLLAN_50, array('x'=>$start_datestamp, 'y'=> $end_datestamp))."</small></div>
		";



	/*	$count = 0;

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
			".$percentage[$count]."% [".POL
			LAN_31.": ".$voteArray[$count]."]
			</td>
			</tr>\n";
			$count++;

		}
*/
		$text .= e107::getComment()->compose_comment('poll', 'comment', intval( $row['poll_id'] ), null, '', false, 'html');
		$ns->tablerender(LAN_PLUGIN_POLL_NAME." #".$row['poll_id'], $text);
		echo "<hr />";
	}
}


	// Render List of Polls.


	$query = "SELECT p.*, u.user_name FROM #polls AS p
	LEFT JOIN #user AS u ON p.poll_admin_id = u.user_id
	WHERE p.poll_type=1
	ORDER BY p.poll_datestamp DESC";

	if(!$array = $sql->retrieve($query,true))
	{
		$ns->tablerender(POLLAN_28, "<div style='text-align:center'>".LAN_NO_RECORDS_FOUND."</div>");
		require_once(FOOTERF);
		exit;
	}

	$array = array_slice($array, 1);

	if(empty($array))
	{
		$ns->tablerender(POLLAN_28, "<div style='text-align:center'>".LAN_NO_RECORDS_FOUND."</div>");
		require_once(FOOTERF);
		exit;
	}

	$text = "<table class='table fborder' style='".USER_WIDTH."'>
	<colgroup>
		<col style='width: 55%;' />
		<col style='width: 15%;' />
		<col style='width: 30%;' />
	<thead>
	<tr>
	<th class='fcaption'>".LAN_TITLE."</th>
	<th class='fcaption'>".LAN_POSTED_BY."</th>
	<th class='fcaption'>".LAN_ACTIVE."</th>
	</tr></thead><tbody>\n";


	foreach($array as $row)
	{

		$from = $tp->toDate($row['poll_datestamp'], "short");
		$to = $tp->toDate($row['poll_end_datestamp'], "short");

		$poll_title = $tp->toHTML($row['poll_title'], true, 'TITLE');

		$uparams = array('id' => $row['poll_admin_id'], 'name' => $row['user_name']);

		$link = e107::getUrl()->create('user/profile/view', $uparams);


		$userlink = "<a href='".$link."'>".$row['user_name']."</a>";
		$text .= "<tr>
		<td class='forumheader3' style='width: 55%;'><a href='".e_SELF."?".$row['poll_id']."'>{$poll_title}</a></td>
		<td class='forumheader3' style='width: 15%;'>".$userlink."</td>
		<td class='forumheader3' style='width: 30%;'>".$tp->lanVars(POLLAN_50, array('x'=>$from, 'y'=> $to))."</td>
		</tr>\n";
	}

	$text .= "</tbody></table>";
	e107::getRender()->tablerender(POLLAN_28, $text);
	require_once(FOOTERF);


