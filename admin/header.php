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
var ns6=document.getElementById&&!document.all?1:0
var head="display:''"
var folder=''
function expandit(curobj){
folder=ns6?curobj.nextSibling.nextSibling.style:document.all[curobj.sourceIndex+1].style
if (folder.display=="none")
folder.display=""
else
folder.display="none"
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
echo "
<div style=\"text-align:center\">";
if($admin_logo == "1"){
	echo "<table style=\"width:100%\" cellspacing=\"0\" cellpadding=\"0\">
	<tr>
	<td style=\"background-color:#E2E2E2; text-align:".$logo_align."\">
	\n<img src=\"logo.png\" alt=\"Logo\" />
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
	echo "<img src=\"logo".$admin_logo.".png\" alt=\"Logo\" />
	<br />";
	if(ADMIN == TRUE){
		$str = str_replace(".", "", ADMINPERMS);
		if(ADMINPERMS == "0"){
			echo "Logged in: ".ADMINNAME." (Main Site Administrator)";
		}else{
			echo "Logged in: ".ADMINNAME." (levels:  ".$str.")";
		}
	}else{
		echo "Login required to progress to secure admin area ...";
	}
}

echo "<table style=\"width:100%\" cellspacing=\"10\" cellpadding=\"10\">
<tr> 
<td style=\"width:15%; vertical-align: top;\">";

// security update added by que
if(ADMIN == TRUE){
 if(!eregi("/admin.php", $_SERVER['PHP_SELF'])){
	 $text = "<a href=\"admin.php\">Admin Front Page</a>
<br />
<a href=\"../index.php\">Leave Admin Area</a>
<br />
<br />";

if(getperms("H")){
	$text .= "<a href=\"newspost.php\">News</a><br />";
}
if(getperms("7")){
	$text .= "<a href=\"news_category.php\">News Categories</a><br />";
}
if(getperms("1")){
	$text .= "<a href=\"prefs.php\">Preferences</a><br />";
}
if(getperms("2")){
	$text .= "<a href=\"menus.php\">Menus</a><br />";
}
if(getperms("3")){
	$text .= "<a href=\"administrator.php\">Administrators</a><br />";
}
$text .= "<a href=\"updateadmin.php\">Update admin password</a><br />";
if(getperms("5")){
	$text .= "<a href=\"forum.php\">Forums</a><br />";
}
if(getperms("J")){
	$text .= "<a href=\"article.php\">Articles</a><br />";
}
if(getperms("l")){
	$text .= "<a href=\"content.php\">Content</a><br />";
}
if(getperms("K")){
	$text .= "<a href=\"review.php\">Reviews</a><br />";
}
if(getperms("I")){
	$text .= "<a href=\"links.php\">Links</a><br />";
}
if(getperms("8")){
	$text .= "<a href=\"link_category.php\">Link Categories</a><br />";
}
if(getperms("Q")){
	$text .= "<a href=\"download.php\">Downloads</a><br />";
}
if(getperms("R")){
	$text .= "<a href=\"download_category.php\">Download Categories</a><br />";
}
if(getperms("M")){
	$text .= "<a href=\"wmessage.php\">Welcome Message</a><br />";
}

if(getperms("6")){
	$text .= "<a href=\"filemanager.php\">File Manager</a><br />";
}
if(getperms("N")){
	$text .= "<a href=\"submitnews.php\">Submitted News</a><br />";
}
if(getperms("4")){
	$text .= "<a href=\"users.php\">Users</a><br />";
}

	$text .= "<a href=\"admin.php?logout\">Logout</a>";
	$ns -> tablerender("Admin Navigation", $text);

 }else{
	$text = "<a href=\"../index.php\">Leave Admin Area</a>";
	$ns -> tablerender("Admin Navigation", $text);
	unset($text);
 }

if($sql -> db_Select("submitnews", "*", "submitnews_auth ='0' ")){
	$text = "<div class=\"defaulttext\" style=\"text-align:center\">
<b>You have had a news item submitted.</b>
</div>
Please click <a href=\"submitnews.php\">here</a> to review.";
	$ns -> tablerender("Story Submitted", $text);
}

if(ADMINPERMS == "0"){
	if((ADMINPWCHANGE+2592000) < time()){
		$text = "<div style=\"mediumtext; text-align:center\">It has been more than 30 days since you changed the main administrator password - <a href=\"updateadmin.php\">Click here to change it now</a></div>";
		$ns -> tablerender("Security", $text);
	}
 }

$handle=opendir("help/");
	$text = "";
	while ($file = readdir($handle)){	
		if($file != "." && $file != ".."){
//			 echo "help/".$file."<br />";
			 if(eregi($file, e_SELF)){
				require_once("help/".$file);
			 }
		}
	}
	closedir($handle);
}

?>
<br />
</td>
<td style="width:60%; vertical-align: top;">