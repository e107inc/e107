<?
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/header.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $sitename; ?></title>
<link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
<?php if(file_exists(e_FILE."e107.css")){ echo "\n<link rel='stylesheet' href='".e_FILE."e107.css' />\n"; } ?>
<?php if(file_exists(e_FILE."style.css")){ echo "\n<link rel='stylesheet' href='".e_FILE."style.css' />\n"; } ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<meta http-equiv="content-style-type" content="text/css" />
<?php
echo "<script type='text/javascript' src='".e_FILE."e107.js'></script>";
if(file_exists(THEME."theme.js")){echo "<script type='text/javascript' src='".THEME."theme.js'></script>";}
if(file_exists(e_FILE."user.js")){echo "<script type='text/javascript' src='".e_FILE."user.js'></script>\n";}
?>
</head>
<body>
<?php

$ns = new e107table;


echo "<div style='text-align:center'>
<img src='".e_IMAGE."adminlogo.png' alt='Logo' />
<br />";
if(ADMIN == TRUE){
	$str = str_replace(".", "", ADMINPERMS);
	if(ADMINPERMS == "0"){
		echo ADLAN_48.": ".ADMINNAME." (".ADLAN_49.")";
	}else{
		echo ADLAN_48.": ".ADMINNAME." (".ADLAN_50.":  ".$str.")";
	}
}else{
	echo ADLAN_51." ...";
}

$adminfpage = (!$pref['adminstyle'] || $pref['adminstyle'] == "default" ? "admin.php" : $pref['adminstyle'].".php");

echo "
<div>
<table style='width:100%' cellspacing='10' cellpadding='10'>
<tr> 
<td style='width:15%; vertical-align: top;'>";

if(ADMIN == TRUE){
	
	if(!strstr(e_SELF, "/".$adminfpage) || strstr(e_SELF, "/".$adminfpage."?")){
	$text = "<a href='".e_ADMIN_L.$adminfpage."'>".ADLAN_52."</a><br /><a href='".e_BASE."index.php'>".ADLAN_53."</a><br /><br />";
	$text .= "º <a style='cursor: pointer; cursor: hand' onclick=\"expandit(this);\">".ADLAN_93."</a>
	<div style='display: none;'>
	<br />";

	if(getperms("3")){$text .= "<a href='".e_ADMIN_L."administrator.php'>".ADLAN_8."</a><br />";}
	$text .= "<a href='".e_ADMIN_L."updateadmin.php'>".ADLAN_10."</a><br />";
	if(getperms("J")){$text .= "<a href='".e_ADMIN_L."article.php'>".ADLAN_14."</a><br />";}
	if(getperms("4")){$text .= "<a href='".e_ADMIN_L."banlist.php'>".ADLAN_34."</a><br />";}
	if(getperms("D")){$text .= "<a href='".e_ADMIN_L."banner.php'>".ADLAN_54."</a><br />";}
	if(getperms("0")){$text .= "<a href='".e_ADMIN_L."cache.php'>".ADLAN_74."</a><br />";}
	if(getperms("C")){$text .= "<a href='".e_ADMIN_L."chatbox.php'>".ADLAN_56."</a><br />";}
	if(getperms("L")){$text .= "<a href='".e_ADMIN_L."content.php'>".ADLAN_16."</a><br />";}
	if(getperms("2")){$text .= "<a href='".e_ADMIN_L."custommenu.php'>".ADLAN_42."</a><br />";}
	if(getperms("0")){$text .= "<a href='".e_ADMIN_L."db.php'>".ADLAN_44."</a><br />";}
	if(getperms("R")){$text .= "<a href='".e_ADMIN_L."download.php'>".ADLAN_24."</a><br />";}
	if(getperms("F")){$text .= "<a href='".e_ADMIN_L."emoticon.php'>".ADLAN_58."</a><br />";}
	if(getperms("6")){$text .= "<a href='".e_ADMIN_L."filemanager.php'>".ADLAN_30."</a><br />";}
	if(getperms("5")){$text .= "<a href='".e_ADMIN_L."forum.php'>".ADLAN_12."</a><br />";}
	if(getperms("G")){$text .= "<a href='".e_ADMIN_L."frontpage.php'>".ADLAN_60."</a><br />";}
	if(getperms("4")){$text .= "<a href='".e_ADMIN_L."image.php'>".ADLAN_105."</a><br />";}
	if(getperms("I")){$text .= "<a href='".e_ADMIN_L."links.php'>".ADLAN_20."</a><br />";}
	if(getperms("S")){$text .= "<a href='".e_ADMIN_L."log.php'>".ADLAN_64."</a><br />";}
	if(getperms("9")){$text .= "<a href='".e_ADMIN_L."ugflag.php'>".ADLAN_40."</a><br />";}
	if(getperms("2")){$text .= "<a href='".e_ADMIN_L."menus.php'>".ADLAN_6."</a><br />";}
	if(getperms("T")){$text .= "<a href='".e_ADMIN_L."meta.php'>".ADLAN_66."</a><br />";}
	if(getperms("H")){$text .= "<a href='".e_ADMIN_L."newspost.php'>".ADLAN_0."</a><br />";}
	if(getperms("E")){$text .= "<a href='".e_ADMIN_L."newsfeed.php'>".ADLAN_62."</a><br />";}
	if(getperms("0")){$text .= "<a href='".e_ADMIN_L."phpinfo.php'>".ADLAN_68."</a><br />";}
	if(getperms("U")){$text .= "<a href='".e_ADMIN_L."poll.php'>".ADLAN_70."</a><br />";}
	if(getperms("1")){$text .= "<a href='".e_ADMIN_L."prefs.php'>".ADLAN_4."</a><br />";}
	if(getperms("V")){$text .= "<a href='".e_ADMIN_L."upload.php'>".ADLAN_72."</a><br />";}
	if(getperms("K")){$text .= "<a href='".e_ADMIN_L."review.php'>".ADLAN_18."</a><br />";}
	if(getperms("4")){$text .= "<a href='".e_ADMIN_L."users.php'>".ADLAN_36."</a><br />";}
	if(getperms("4")){$text .= "<a href='".e_ADMIN_L."userclass2.php'>".ADLAN_38."</a><br />";}
	if(getperms("M")){$text .= "<a href='".e_ADMIN_L."wmessage.php'>".ADLAN_28."</a><br />";}
	$text .= "</div><br />";
	
	$text .= "<br /><a href='".e_ADMIN_L."admin.php?logout'>".ADLAN_46."</a>";
	$ns -> tablerender("Admin Navigation", $text);

 }else{
	$text = "<a href='".e_ADMIN_L."../index.php'>".ADLAN_53."</a>";
	$ns -> tablerender("Admin Navigation", $text);
	unset($text);
 }

if(ADMINPERMS == "0"){
	if((ADMINPWCHANGE+2592000) < time()){
		$text = "<div style='mediumtext; text-align:center'>".ADLAN_102." <a href='".e_ADMIN_L."updateadmin.php'>".ADLAN_103."</a></div>";
		$ns -> tablerender(ADLAN_104, $text);
	}
 }

$handle=opendir(e_ADMIN."help/");
	$text = "";
	while(false !== ($file = readdir($handle))){
		if($file != "." && $file != ".."){
			 if(eregi($file, e_SELF)){
				require_once("help/".$file);
			 }
		}
	}
	closedir($handle);
}

$plugpath = e_PLUGIN.substr(strrchr(substr(e_SELF, 0, strrpos(e_SELF, "/")), "/"), 1)."/help.php"; 
if(file_exists($plugpath)){
	require_once($plugpath);
}

echo "<br />";


if(!FILE_UPLOADS){
	message_handler("ADMIN_MESSAGE", "Your server does not allow HTTP file uploads so it will not be possible for your users to uploads avatars/files etc. To rectify this set file_uploads to On in your php.ini and restart your server. If you dont have access to your php.ini contact your hosts.", __LINE__, __FILE__);
}
/*
if(OPEN_BASEDIR){
	message_handler("ADMIN_MESSAGE", "Your server is running with a basedir restriction in effect. This disallows usage of any file outside of your home directory and as such could affect certain scripts such as the filemanager.", __LINE__, __FILE__);
}
*/

echo "</td>
<td style='width:60%; vertical-align: top;'>";
?>