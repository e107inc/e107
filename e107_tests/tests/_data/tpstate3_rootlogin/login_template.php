<?php
/** Fixture template: the v1 shape a converted theme carries at its root, marked so a test can see which file won. */

if(!isset($LOGIN_TABLE_HEADER))
{
	$LOGIN_TABLE_HEADER = "
	<div id='tpstate3-rootlogin-template'>
		<p>TPSTATE3_ROOTLOGIN_MARKER</p>";
}

if(!isset($LOGIN_TABLE))
{
	$LOGIN_TABLE = "
	<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='width:60%'>
		<tr>
			<td class='fcaption' colspan='2'>".LAN_LOGIN_4."</td>
		</tr>
		<tr>
			<td class='forumheader3' style='width:40%'>".LAN_LOGIN_1."</td>
			<td class='forumheader3' style='width:60%'>{LOGIN_TABLE_USERNAME}</td>
		</tr>
		<tr>
			<td class='forumheader3'>".LAN_LOGIN_2."</td>
			<td class='forumheader3'>{LOGIN_TABLE_PASSWORD}</td>
		</tr>
		<tr>
			<td class='forumheader' colspan='2' style='text-align:center'>{LOGIN_TABLE_SUBMIT}</td>
		</tr>
	</table>
	</form>
	</div>";
}

if(!isset($LOGIN_TABLE_FOOTER))
{
	$LOGIN_TABLE_FOOTER = "
	</div>";
}
