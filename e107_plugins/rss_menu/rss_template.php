<?php

if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:100%"); }

// Rss listing
if(!isset($RSS_LIST_HEADER))
{
	$RSS_LIST_HEADER = "<table class='table table-striped table-bordered fborder' style='".USER_WIDTH."'>
		<tr>
			<th class='fcaption' style='width:70%'> </th>
			<th class='fcaption' style='text-align:right'>".RSS_PLUGIN_LAN_6."</th>
		</tr>";
}
if(!isset($RSS_LIST_TABLE))
{
	$RSS_LIST_TABLE = "
	<tr>
		<td class='forumheader3'>{RSS_FEED}<br />
		<span class='smalltext' ><small>{RSS_TEXT}</small></span>
		</td>
	<td class='forumheader3' style='text-align:right'>
    	{RSS_TYPES}
	</td>
	</tr>";
}
if(!isset($RSS_LIST_FOOTER))
{
	$RSS_LIST_FOOTER = "</table>";
}


