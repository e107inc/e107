<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/header.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
if(file_exists(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php")){@include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php");}else{@include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php");}

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
				$var['x']['text']=ADLAN_52;
				$var['x']['link']=e_ADMIN.$adminfpage;
			
				$var['y']['text']=ADLAN_53;
				$var['y']['link']=e_BASE."index.php";

    //    $text = "<a href='".e_ADMIN.$adminfpage."'>".ADLAN_52."</a><br /><a href='".e_BASE."index.php'>".ADLAN_53."</a><br /><br />";
         $text ="<div style='text-align:center'>";
			$text .= show_admin_menu("",$act,$var);
//         $text .= "<input type='button' class='button' style='width:100%' onClick=\"document.location='".e_ADMIN.$adminfpage."'\" value='".ADLAN_52."' /><br />
//         <input type='button' class='button' style='width:100%' onClick=\"document.location='".e_BASE."index.php'\" value='".ADLAN_53."' /><br /><br />";


        $text .= "º <a style='cursor: pointer; cursor: hand' onclick=\"expandit(this);\">".ADLAN_93."</a>
        <div style='display: none;'>
        <br />";

        if(getperms("3")){$text .= "<a href='".e_ADMIN."administrator.php'>".ADLAN_8."</a><br />";}
        $text .= "<a href='".e_ADMIN."updateadmin.php'>".ADLAN_10."</a><br />";
        if(getperms("J")){$text .= "<a href='".e_ADMIN."article.php'>".ADLAN_14."</a><br />";}
        if(getperms("4")){$text .= "<a href='".e_ADMIN."banlist.php'>".ADLAN_34."</a><br />";}
        if(getperms("D")){$text .= "<a href='".e_ADMIN."banner.php'>".ADLAN_54."</a><br />";}
        if(getperms("0")){$text .= "<a href='".e_ADMIN."cache.php'>".ADLAN_74."</a><br />";}
        if(getperms("C")){$text .= "<a href='".e_ADMIN."chatbox.php'>".ADLAN_56."</a><br />";}
        if(getperms("L")){$text .= "<a href='".e_ADMIN."content.php'>".ADLAN_16."</a><br />";}
        if(getperms("2")){$text .= "<a href='".e_ADMIN."custommenu.php'>".ADLAN_42."</a><br />";}
        if(getperms("0")){$text .= "<a href='".e_ADMIN."db.php'>".ADLAN_44."</a><br />";}
        if(getperms("R")){$text .= "<a href='".e_ADMIN."download.php'>".ADLAN_24."</a><br />";}
        if(getperms("F")){$text .= "<a href='".e_ADMIN."emoticon.php'>".ADLAN_58."</a><br />";}
        if(getperms("6")){$text .= "<a href='".e_ADMIN."filemanager.php'>".ADLAN_30."</a><br />";}
        if(getperms("5")){$text .= "<a href='".e_ADMIN."forum.php'>".ADLAN_12."</a><br />";}
        if(getperms("G")){$text .= "<a href='".e_ADMIN."frontpage.php'>".ADLAN_60."</a><br />";}
        if(getperms("4")){$text .= "<a href='".e_ADMIN."image.php'>".ADLAN_105."</a><br />";}
        if(getperms("I")){$text .= "<a href='".e_ADMIN."links.php'>".ADLAN_20."</a><br />";}
        if(getperms("S")){$text .= "<a href='".e_ADMIN."log.php'>".ADLAN_64."</a><br />";}
        if(getperms("9")){$text .= "<a href='".e_ADMIN."ugflag.php'>".ADLAN_40."</a><br />";}
        if(getperms("2")){$text .= "<a href='".e_ADMIN."menus.php'>".ADLAN_6."</a><br />";}
        if(getperms("T")){$text .= "<a href='".e_ADMIN."meta.php'>".ADLAN_66."</a><br />";}
        if(getperms("H")){$text .= "<a href='".e_ADMIN."newspost.php'>".ADLAN_0."</a><br />";}
        if(getperms("E")){$text .= "<a href='".e_ADMIN."newsfeed.php'>".ADLAN_62."</a><br />";}
        if(getperms("0")){$text .= "<a href='".e_ADMIN."phpinfo.php'>".ADLAN_68."</a><br />";}
        if(getperms("U")){$text .= "<a href='".e_ADMIN."poll.php'>".ADLAN_70."</a><br />";}
        if(getperms("1")){$text .= "<a href='".e_ADMIN."prefs.php'>".ADLAN_4."</a><br />";}
        if(getperms("V")){$text .= "<a href='".e_ADMIN."upload.php'>".ADLAN_72."</a><br />";}
        if(getperms("K")){$text .= "<a href='".e_ADMIN."review.php'>".ADLAN_18."</a><br />";}
        if(getperms("4")){$text .= "<a href='".e_ADMIN."users.php'>".ADLAN_36."</a><br />";}
        if(getperms("4")){$text .= "<a href='".e_ADMIN."userclass2.php'>".ADLAN_38."</a><br />";}
        if(getperms("M")){$text .= "<a href='".e_ADMIN."wmessage.php'>".ADLAN_28."</a><br />";}
        $text .= "</div><br />";

			unset($var);
			$var['x']['text']=ADLAN_46;
			$var['x']['link']=e_ADMIN."admin.php?logout";
			$text .= "<br />".show_admin_menu("",$act,$var);

      $text .="</div>";

        $ns -> tablerender(LAN_head_1, $text);

 }else{

     $text = "<div style='text-align:center'>";
		unset($var);
		$var['x']['text']=ADLAN_53;
		$var['x']['link']=e_ADMIN."../index.php";
		$text .= show_admin_menu("",$act,$var);
     $text  .="</div>";
     $ns -> tablerender(LAN_head_1, $text);
        unset($text);
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
                if($file != "." && $file != ".." && $file != "CVS"){
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
        message_handler("ADMIN_MESSAGE", LAN_head_2, __LINE__, __FILE__);
}
/*
if(OPEN_BASEDIR){
        message_handler("ADMIN_MESSAGE", LAN_head_3, __LINE__, __FILE__);
}
*/

echo "</td>
<td style='width:60%; vertical-align: top;'>";

function show_admin_menu($title,$page,$vars){
	global $ns;
	$text = "<table class='fborder' style='width:100%;'>";
	foreach(array_keys($vars) as $act){
		$pre = "";
		$post = "";
		if($page == $act){
			$pre = "<b> &laquo; ";
			$post = " &raquo; </b>";
		}
		if(!$vars[$act]['perm'] || getperms($vars[$act]['perm'])){
			$text .= "<tr><td class='button' style='text-align:center;'>{$pre}<a style='text-decoration:none;' href='{$vars[$act]['link']}'>{$vars[$act]['text']}</a>{$post}</td></tr>";
		}
	}
	$text .= "</table>";
	if($title==""){
		return $text;
	}
	$ns -> tablerender($title,$text);
}


?>