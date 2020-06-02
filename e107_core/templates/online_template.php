<?php

if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:96%"); }

// ##### ONLINE TABLE -----------------------------------------------------------------------------
if(!isset($ONLINE_TABLE_START))
{
	$ONLINE_TABLE_START = "
		<div style='text-align:center'>
		<table class='table fborder' style='".USER_WIDTH."'>
		<tr>
			<td class='forumheader' style='width:3%'>&nbsp;</td>
			<td class='forumheader' style='width:43%'>".ONLINE_EL10."</td>
			<td class='forumheader' style='width:50%'>".ONLINE_EL11."</td>
		</tr>";
}
if(!isset($ONLINE_TABLE))
{
	$ONLINE_TABLE = "
		<tr>
			<td class='forumheader3' style='width:3%;text-align:center'>{ONLINE_TABLE_ICON}</td>
			<td class='forumheader3' style='width:45%'>{ONLINE_TABLE_USERNAME}</td>
			<td class='forumheader3' style='width:50%'>{ONLINE_TABLE_LOCATION}<br /></td>
		</tr>";
}
if(!isset($ONLINE_TABLE_END))
{
	$ONLINE_TABLE_END = "
		</table>
		</div>
		<br />";
}
if (!isset($ONLINE_TABLE_MISC))
{
  $ONLINE_TABLE_MISC = ONLINE_EL1.GUESTS_ONLINE.",
		".ONLINE_EL2.MEMBERS_ONLINE." ...<br />
		<br />{ONLINE_TABLE_MOST_EVER_ONLINE}
		<br />({ONLINE_TABLE_MOST_MEMBERS_ONLINE}, {ONLINE_TABLE_MOST_GUESTS_ONLINE}) ".ONLINE_EL9." {ONLINE_TABLE_DATESTAMP}<br />
		{ONLINE_TABLE_MEMBERS_TOTAL}{ONLINE_TABLE_MEMBERS_NEWEST}";
}
// ##### ------------------------------------------------------------------------------------------


