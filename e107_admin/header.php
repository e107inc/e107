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
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
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
	opener = window.open("<? echo e_ADMIN ?>htmlarea/index.php", "popup","top=50,left=100,resizable=no,width=670,height=520,scrollbars=no,menubar=no");            
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
image1 = new Image(); image1.src = "../e107_images/generic/e107.gif";
// -->
</script>
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


echo "
<div>
<table style='width:100%' cellspacing='10' cellpadding='10'>
<tr> 
<td style='width:15%; vertical-align: top;'>";

if(ADMIN == TRUE){
	if(!eregi("/admin.php", e_SELF)){
	$text = "<a href='".e_ADMIN."admin.php'>".ADLAN_52."</a><br /><a href='".e_BASE."index.php'>".ADLAN_53."</a><br /><br />";
	$text .= "º <a style='cursor: pointer; cursor: hand' onclick=\"expandit(this);\">".ADLAN_93."</a>
	<div style='display: none;'>
	<br />";
	if(getperms("H")){$text .= "<a href='".e_ADMIN."newspost.php'>".ADLAN_0."</a><br />";}
	if(getperms("7")){$text .= "<a href='".e_ADMIN."news_category.php'>".ADLAN_2."</a><br />";}
	if(getperms("1")){$text .= "<a href='".e_ADMIN."prefs.php'>".ADLAN_4."</a><br />";}
	if(getperms("2")){$text .= "<a href='".e_ADMIN."menus.php'>".ADLAN_6."</a><br />";}
	if(getperms("3")){$text .= "<a href='".e_ADMIN."administrator.php'>".ADLAN_8."</a><br />";}
	$text .= "<a href='".e_ADMIN."updateadmin.php'>".ADLAN_10."</a><br />";
	if(getperms("5")){$text .= "<a href='".e_ADMIN."forum.php'>".ADLAN_12."</a><br />";}
	if(getperms("J")){$text .= "<a href='".e_ADMIN."article.php'>".ADLAN_14."</a><br />";}
	if(getperms("L")){$text .= "<a href='".e_ADMIN."content.php'>".ADLAN_16."</a><br />";}
	if(getperms("K")){$text .= "<a href='".e_ADMIN."review.php'>".ADLAN_18."</a><br />";}
	if(getperms("I")){$text .= "<a href='".e_ADMIN."links.php'>".ADLAN_20."</a><br />";}
	if(getperms("8")){$text .= "<a href='".e_ADMIN."link_category.php'>".ADLAN_22."</a><br />";}
	if(getperms("R")){$text .= "<a href='".e_ADMIN."download.php'>".ADLAN_24."</a><br />";}
	if(getperms("Q")){$text .= "<a href='".e_ADMIN."download_category.php'>".ADLAN_26."</a><br />";}
	if(getperms("M")){$text .= "<a href='".e_ADMIN."wmessage.php'>".ADLAN_28."</a><br />";}
	if(getperms("6")){$text .= "<a href='".e_ADMIN."filemanager.php'>".ADLAN_30."</a><br />";}
	if(getperms("N")){$text .= "<a href='".e_ADMIN."submitnews.php'>".ADLAN_32."</a><br />";}
	if(getperms("4")){$text .= "<a href='".e_ADMIN."banlist.php'>".ADLAN_34."</a><br />";}
	if(getperms("4")){$text .= "<a href='".e_ADMIN."users.php'>".ADLAN_36."</a><br />";}
	if(getperms("4")){$text .= "<a href='".e_ADMIN."userclass2.php'>".ADLAN_38."</a><br />";}
	if(getperms("D")){$text .= "<a href='".e_ADMIN."banner.php'>".ADLAN_54."</a><br />";}
	if(getperms("C")){$text .= "<a href='".e_ADMIN."chatbox.php'>".ADLAN_56."</a><br />";}
	if(getperms("E")){$text .= "<a href='".e_ADMIN."newsfeed.php'>".ADLAN_62."</a><br />";}
	if(getperms("F")){$text .= "<a href='".e_ADMIN."emoticon.php'>".ADLAN_58."</a><br />";}
	if(getperms("G")){$text .= "<a href='".e_ADMIN."frontpage.php'>".ADLAN_60."</a><br />";}
	if(getperms("S")){$text .= "<a href='".e_ADMIN."log.php'>".ADLAN_64."</a><br />";}
	if(getperms("T")){$text .= "<a href='".e_ADMIN."meta.php'>".ADLAN_66."</a><br />";}
	if(getperms("0")){$text .= "<a href='".e_ADMIN."phpinfo.php'>".ADLAN_68."</a><br />";}
	if(getperms("U")){$text .= "<a href='".e_ADMIN."poll.php'>".ADLAN_70."</a><br />";}
	if(getperms("V")){$text .= "<a href='".e_ADMIN."upload.php'>".ADLAN_72."</a><br />";}
	if(getperms("9")){$text .= "<a href='".e_ADMIN."ugflag.php'>".ADLAN_40."</a><br />";}
	if(getperms("0")){$text .= "<a href='".e_ADMIN."cache.php'>".ADLAN_74."</a><br />";}
	if(getperms("2")){$text .= "<a href='".e_ADMIN."custommenu.php'>".ADLAN_42."</a><br />";}
	if(getperms("0")){$text .= "<a href='".e_ADMIN."db.php'>".ADLAN_44."</a><br />";}
	$text .= "</div><br />";
	
	$text .= "<br /><a href='".e_ADMIN."admin.php?logout'>".ADLAN_46."</a>";
	$ns -> tablerender("Admin Navigation", $text);

 }else{
	$text = "<a href='".e_ADMIN."../index.php'>".ADLAN_53."</a>";
	$ns -> tablerender("Admin Navigation", $text);
	unset($text);
 }

if($sql -> db_Select("submitnews", "*", "submitnews_auth ='0' ")){
	$text = "<div class='defaulttext' style='text-align:center'>
<b><a href='".e_ADMIN."submitnews.php'>".ADLAN_77."</a></b>
</div>
";
	$ns -> tablerender(ADLAN_32, $text);
}

if(ADMINPERMS == "0"){
	if((ADMINPWCHANGE+2592000) < time()){
		$text = "<div style='mediumtext; text-align:center'>".ADLAN_102." <a href='".e_ADMIN."updateadmin.php'>".ADLAN_103."</a></div>";
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

/*
if(SAFE_MODE){
	message_handler("ADMIN_MESSAGE", "Your server is running in safe mode, this could affect certain scripts such as the filemanager.", __LINE__, __FILE__);
}

if(OPEN_BASEDIR){
	message_handler("ADMIN_MESSAGE", "Your server is running with a basedir restriction in effect. This disallows usage of any file outside of your home directory and as such could affect certain scripts such as the filemanager.", __LINE__, __FILE__);
}
*/

echo "</td>
<td style='width:60%; vertical-align: top;'>";
?>