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
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="content-style-type" content="text/css" />
    <script type="text/javascript">
      <!--
function textCounter(field,cntfield) {
	cntfield.value = field.value.length;
}
if(document.getElementById&&!document.all){ns6=1;}else{ns6=0;}
var agtbrw=navigator.userAgent.toLowerCase();
var operaaa=(agtbrw.indexOf('opera')!=-1);
var head="display:''";
var folder='';
function expandit(curobj){
if(ns6==1||operaaa==true){
	folder=curobj.nextSibling.nextSibling.style;
}else{
	folder=document.all[curobj.sourceIndex+1].style;
}

if (folder.display=="none"){folder.display="";}else{folder.display="none";}
}

function urljump(url){
	top.window.location = url; 
}

function openwindow() {
	opener = window.open("htmlarea/index.php", "popup","top=100,left=100,resizable=no,width=670,height=520,scrollbars=no,menubar=no");
}
function setCheckboxes(the_form, do_check){
	var elts = (typeof(document.forms[the_form].elements['perms[]']) != 'undefined') ? document.forms[the_form].elements['perms[]'] : document.forms[the_form].elements['perms[]'];
    var elts_cnt  = (typeof(elts.length) != 'undefined') ? elts.length : 0;
    if(elts_cnt){
		for(var i = 0; i < elts_cnt; i++){
			elts[i].checked = do_check;
        }
	}else{
		elts.checked        = do_check;
    }
	return true;
}

// -->
    </script>
  </head>
<body>
<?php

$ns = new table;

$alogo = (eregi("menu_config", e_SELF) ? "../logo.png" : "logo.png");

echo "
<div style=\"text-align:center\">";
if($admin_logo == "1"){
	echo "<table style=\"width:100%\" cellspacing=\"0\" cellpadding=\"0\">
	<tr>
	<td style=\"background-color:#E2E2E2; text-align:".$logo_align."\">
	\n<img src=\"$alogo\" alt=\"Logo\" />
	</td></tr>
	<tr>
	<td style=\"background-color:#000\"></td>
	</tr>
	<tr>
	<td style=\"background-color:#fff\"></td>
	</tr>
	<tr>
	<td style=\"background-color:#ccc\">&nbsp;";
	if(ADMIN == TRUE){
		$str = str_replace(".", "", ADMINPERMS);
		if(ADMINPERMS == "0"){
			echo "Logged in: ".ADMINNAME." (Main Site Administrator)";
		}else{
			echo "Logged in: ".ADMINNAME." (levels: ".$str.")";
		}
	}else{
		echo "Login required to progress to secure admin area ...";
	}
	echo "</td>
	</tr>
	<tr>
	<td style=\"background-color:#000\"></td>
	</tr>
	</table>";
}else{
	echo "<img src=\"$alogo\" alt=\"Logo\" />
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
}

echo "<table style=\"width:100%\" cellspacing=\"10\" cellpadding=\"10\">
<tr> 
<td style=\"width:15%; vertical-align: top;\">";

// security update added by que
if(ADMIN == TRUE){
 if(!eregi("/admin.php", $_SERVER['PHP_SELF'])){
	 $text = "<a href=\"".e_ADMIN."admin.php\">".ADLAN_52."</a>
<br />

".(eregi("menu_config", e_SELF) ? "<a href=\"../../index.php\">".ADLAN_53."</a>" : "<a href=\"../index.php\">".ADLAN_53."</a>")."

<br />
<br />";

if(getperms("H")){
	$text .= "<a href=\"".e_ADMIN."newspost.php\">".ADLAN_0."</a><br />";
}
if(getperms("7")){
	$text .= "<a href=\"".e_ADMIN."news_category.php\">".ADLAN_2."</a><br />";
}
if(getperms("1")){
	$text .= "<a href=\"".e_ADMIN."prefs.php\">".ADLAN_4."</a><br />";
}
if(getperms("2")){
	$text .= "<a href=\"".e_ADMIN."menus.php\">".ADLAN_6."</a><br />";
}
if(getperms("3")){
	$text .= "<a href=\"".e_ADMIN."administrator.php\">".ADLAN_8."</a><br />";
}
$text .= "<a href=\"".e_ADMIN."updateadmin.php\">".ADLAN_10."</a><br />";
if(getperms("5")){
	$text .= "<a href=\"".e_ADMIN."forum.php\">".ADLAN_12."</a><br />";
}
if(getperms("J")){
	$text .= "<a href=\"".e_ADMIN."article.php\">".ADLAN_14."</a><br />";
}
if(getperms("l")){
	$text .= "<a href=\"".e_ADMIN."content.php\">".ADLAN_16."</a><br />";
}
if(getperms("K")){
	$text .= "<a href=\"".e_ADMIN."review.php\">".ADLAN_18."</a><br />";
}
if(getperms("I")){
	$text .= "<a href=\"".e_ADMIN."links.php\">".ADLAN_20."</a><br />";
}
if(getperms("8")){
	$text .= "<a href=\"".e_ADMIN."link_category.php\">".ADLAN_22."</a><br />";
}
if(getperms("R")){
	$text .= "<a href=\"".e_ADMIN."download.php\">".ADLAN_24."</a><br />";
}
if(getperms("Q")){
	$text .= "<a href=\"".e_ADMIN."download_category.php\">".ADLAN_26."</a><br />";
}
if(getperms("M")){
	$text .= "<a href=\"".e_ADMIN."wmessage.php\">".ADLAN_28."</a><br />";
}

if(getperms("6")){
	$text .= "<a href=\"".e_ADMIN."filemanager.php\">".ADLAN_30."</a><br />";
}
if(getperms("N")){
	$text .= "<a href=\"".e_ADMIN."submitnews.php\">".ADLAN_32."</a><br />";
}


if(getperms("4")){
	$text .= "<a href=\"".e_ADMIN."banlist.php\">".ADLAN_34."</a><br />";
}

if(getperms("4")){
	$text .= "<a href=\"".e_ADMIN."users.php\">".ADLAN_36."</a><br />";
}

if(getperms("4")){
	$text .= "<a href=\"".e_ADMIN."userclass2.php\">".ADLAN_38."</a><br />";
}

if(getperms("9")){
	$text .= "<a href=\"".e_ADMIN."ugflag.php\">".ADLAN_40."</a><br />";
}

if(getperms("2")){
	$text .= "<a href=\"".e_ADMIN."custommenu.php\">".ADLAN_42."</a><br />";
}

if(getperms("0")){
	$text .= "<a href=\"".e_ADMIN."db.php\">".ADLAN_44."</a><br />";
}

	$text .= "<a href=\"".e_ADMIN."admin.php?logout\">".ADLAN_46."</a>";
	$ns -> tablerender("Admin Navigation", $text);

 }else{
	$text = "<a href=\"".e_ADMIN."../index.php\">".ADLAN_53."</a>";
	$ns -> tablerender("Admin Navigation", $text);
	unset($text);
 }

if($sql -> db_Select("submitnews", "*", "submitnews_auth ='0' ")){
	$text = "<div class=\"defaulttext\" style=\"text-align:center\">
<b>".ADLAN_54.".</b>
</div>
<a href=\"".e_ADMIN."submitnews.php\">".ADLAN_55."</a> ".ADLAN_56;
	$ns -> tablerender(ADLAN_57, $text);
}

if(ADMINPERMS == "0"){
	if((ADMINPWCHANGE+2592000) < time()){
		$text = "<div style=\"mediumtext; text-align:center\">".ADLAN_58." - <a href=\"".e_ADMIN."updateadmin.php\">".ADLAN_59."</a></div>";
		$ns -> tablerender(ADLAN_60, $text);
	}
 }

if(!eregi("menu_config", e_SELF)){
	$handle=opendir("help/");
		$text = "";
		while ($file = readdir($handle)){	
			if($file != "." && $file != ".."){
				 if(eregi($file, e_SELF)){
					require_once("help/".$file);
				 }
			}
		}
		closedir($handle);
	}
}
/*
$helppath = $_SERVER['DOCUMENT_ROOT'].e_ADMIN."help/";
if(!eregi("menu_config", e_SELF)){
	$handle=opendir($helppath);
		$text = "";
		while ($file = readdir($handle)){	
			if($file != "." && $file != ".."){
				 if(eregi($file, e_SELF)){
					require_once($helppath.$file);
				 }
			}
		}
		closedir($handle);
	}
}
*/
echo "<br />";
if(!MAGIC_QUOTES_GPC){
	message_handler("ADMIN_MESSAGE", "The PHP setting 'MAGIC_QUOTES_GPC' is set to 0 (off), this could cause problems with text containing apostrophes or quotation marks not being entered into the database. To resolve, either edit your php.ini file and set the magic_quotes_gpc entry to 'On', or if your site is on a remote server use a .htaccess file to set the value (see http://e107.org/faq.php for an explanatiion on how to do this). If you cannot do either of these things try opening your config.php file in a text editor and uncommenting the define(\"MQ\", TRUE); line (remove the //).", __LINE__, __FILE__);
}

if(SAFE_MODE){
	message_handler("ADMIN_MESSAGE", "Your server is running in safe mode, this could affect certain scripts such as the filemanager.", __LINE__, __FILE__);
}

if(OPEN_BASEDIR){
	message_handler("ADMIN_MESSAGE", "Your server is running with a basedir restriction in effect. This disallows usage of any file outside of your home directory and as such could affect certain scripts such as the filemanager.", __LINE__, __FILE__);
}
echo "</td>
<td style='width:60%; vertical-align: top;'>";
?>