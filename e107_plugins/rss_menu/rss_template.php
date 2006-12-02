<?php

if (!defined('ADMIN_WIDTH')) { define("ADMIN_WIDTH", "width:98%;"); }
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:100%"); }

//rss listing
if(!isset($RSS_LIST_HEADER)){
	$RSS_LIST_HEADER = "<table class='fborder' style='".USER_WIDTH."'>
		<tr>
			<td class='fcaption' style='width:55%'>".RSS_LAN_ADMIN_4."</td>
			<td class='fcaption' style='text-align:right'>".RSS_PLUGIN_LAN_6."</td>
		</tr>";
}
if(!isset($RSS_LIST_TABLE)){
	$RSS_LIST_TABLE = "
	<tr>
		<td class='forumheader3'>{RSS_FEED}<br />
		<span class='smalltext' >{RSS_TEXT}</span>
		</td>
	<td class='forumheader3' style='text-align:right'>
    	{RSS_TYPES}
	</td>
	</tr>";
}
if(!isset($RSS_LIST_FOOTER)){
	$RSS_LIST_FOOTER = "</table>";
}

//admin : rss listing
if(!isset($RSS_ADMIN_LIST_HEADER)){
    $RSS_ADMIN_LIST_HEADER = "
    <div style='text-align:center;'>
    <form action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."' id='dataform' method='post' >
    <table class='fborder' style='".ADMIN_WIDTH."'>
    <tr>
        <td class='fcaption' style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=id,RSS_LAN_ADMIN_2}</td>
        <td class='fcaption' style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=name,RSS_LAN_ADMIN_4}</td>
        <td class='fcaption' style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=path,RSS_LAN_ADMIN_3}</td>
        <td class='fcaption' style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=url,RSS_LAN_ADMIN_5}</td>
        <td class='fcaption' style='white-space:nowrap;'>".RSS_LAN_ADMIN_12."</td>
        <td class='fcaption' style='white-space:nowrap;'>{RSS_ADMIN_CAPTION=limit,RSS_LAN_ADMIN_7}</td>
        <td class='fcaption' style='white-space:nowrap;'>".LAN_OPTIONS."</td>
    </tr>";
}
if(!isset($RSS_ADMIN_LIST_TABLE)){
	$RSS_ADMIN_LIST_TABLE = "
	<tr>
		<td class='forumheader3'>{RSS_ADMIN_ID}</td>
		<td class='forumheader3'>{RSS_ADMIN_NAME}</td>
		<td class='forumheader3'>{RSS_ADMIN_PATH}</td>
		<td class='forumheader3'>{RSS_ADMIN_URL}</td>
		<td class='forumheader3'>{RSS_ADMIN_TOPICID}</td>
		<td class='forumheader3'>{RSS_ADMIN_LIMIT}</td>
		<td class='forumheader3' style='text-align:center'>{RSS_ADMIN_OPTIONS}</td>
	</tr>";
}
if(!isset($RSS_ADMIN_LIST_FOOTER)){
	$RSS_ADMIN_LIST_FOOTER = "
	<tr>
		<td class='forumheader' colspan='7' style='text-align:center'>
			{RSS_ADMIN_LIMITBUTTON}
		</td>
	</tr>
	</table>
	</form>
	</div>";
}

//admin : rss create/edit
if(!isset($RSS_ADMIN_CREATE_TABLE)){
	$RSS_ADMIN_CREATE_TABLE = "
	<div style='text-align:center;'>
	<form action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."' id='dataform' method='post' >
	<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
		<td class='forumheader3' style='width:12%'>".RSS_LAN_ADMIN_4."</td>
		<td class='forumheader3'>{RSS_ADMIN_FORM_NAME}</td>
	</tr>
	<tr>
		<td class='forumheader3'>".RSS_LAN_ADMIN_5."</td>
		<td class='forumheader3'>{RSS_ADMIN_FORM_URL}</td>
	</tr>
	<tr>
		<td class='forumheader3'>".RSS_LAN_ADMIN_12."</td>
		<td class='forumheader3'>{RSS_ADMIN_FORM_TOPICID}</td>
	</tr>
	<tr>
		<td class='forumheader3'>".RSS_LAN_ADMIN_3."</td>
		<td class='forumheader3'>{RSS_ADMIN_FORM_PATH}</td>
	</tr>
	<tr>
		<td class='forumheader3'>".RSS_LAN_ADMIN_6."</td>
		<td class='forumheader3'>{RSS_ADMIN_FORM_TEXT}</td>
	</tr>
	<tr>
		<td class='forumheader3'>".RSS_LAN_ADMIN_7."</td>
		<td class='forumheader3'>{RSS_ADMIN_FORM_LIMIT}</td>
	</tr>
	<tr>
		<td class='forumheader3'>".RSS_LAN_ADMIN_8."</td>
		<td class='forumheader3'>{RSS_ADMIN_FORM_CLASS}</td>
	</tr>
	<tr>
		<td class='forumheader' colspan='2' style='text-align:center;'>{RSS_ADMIN_FORM_CREATEBUTTON}</td>
	</tr>
	</table>
	</form>
	</div>";
}

//admin : rss options
if(!isset($RSS_ADMIN_OPTIONS_TABLE)){
	$RSS_ADMIN_OPTIONS_TABLE = "
	<div style='text-align:center;'>
	<form action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."' id='dataform' method='post' >
	<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
		<td class='fcaption'>".LAN_OPTIONS."</td>
		<td class='fcaption'>".RSS_LAN_ADMIN_14."</td>
	</tr>
	<tr>
		<td class='forumheader3'>".RSS_LAN_ADMIN_13."</td>
		<td class='forumheader3'>
			<input type='checkbox' name='rss_othernews' value='1' ".($pref['rss_othernews'] == 1 ? " checked='checked' " : "")." />
		</td>
	</tr>
	<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='updatesettings' value='".LAN_SAVE."' />
		</td>
	</tr>
	</table>
	</form>
	</div>";
}

//admin : rss import
if(!isset($RSS_ADMIN_IMPORT_HEADER)){
	$RSS_ADMIN_IMPORT_HEADER = "
	<div style='text-align:center;'>
	<form action='".e_SELF."' id='imlistform' method='post' >
	<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr><td class='fcaption' colspan='5'>".RSS_LAN_ADMIN_15."</td></tr>
	<tr>
		<td class='fcaption'>".RSS_LAN_ADMIN_16."</td>
		<td class='fcaption'>".RSS_LAN_ADMIN_3."</td>
		<td class='fcaption'>".RSS_LAN_ADMIN_4."</td>
		<td class='fcaption'>".RSS_LAN_ADMIN_5."</td>
		<td class='fcaption'>".RSS_LAN_ADMIN_12."</td>
	</tr>";
}
if(!isset($RSS_ADMIN_IMPORT_TABLE)){
	$RSS_ADMIN_IMPORT_TABLE = "
	<tr>
		<td class='forumheader3'>{RSS_ADMIN_IMPORT_CHECK}</td>
		<td class='forumheader3'>{RSS_ADMIN_IMPORT_PATH}</td>
		<td class='forumheader3'><b>{RSS_ADMIN_IMPORT_NAME}</b><br />{RSS_ADMIN_IMPORT_TEXT}</td>
		<td class='forumheader3'>{RSS_ADMIN_IMPORT_URL}</td>
		<td class='forumheader3'>{RSS_ADMIN_IMPORT_TOPICID}</td>
	</tr>";
}

if(!isset($RSS_ADMIN_IMPORT_FOOTER)){
	$RSS_ADMIN_IMPORT_FOOTER = "
	<tr style='vertical-align:top'>
		<td colspan='5' style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='import_rss' value='".RSS_LAN_ADMIN_17."' />
		</td>
	</tr>
	</table>
	</form>
	</div>";
}

?>