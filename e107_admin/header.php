<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /e107_admin/header.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|   $Source: /cvs_backup/e107/e107_admin/header.php,v $
|   $Revision: 1.27 $
|   $Date: 2005-01-04 18:12:00 $
|   $Author: e107coders $
+---------------------------------------------------------------+
*/
echo (defined("STANDARDS_MODE") ? "" : "<?xml version='1.0' encoding='".CHARSET."' ?>");
if(file_exists(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php")){@include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php");}else{@include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_header.php");}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo SITENAME." : ".LAN_head_4; ?></title>
<link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
<?php if(file_exists(e_FILE."e107.css")){ echo "\n<link rel='stylesheet' href='".e_FILE."e107.css' />\n"; } ?>
<?php if(file_exists(e_FILE."style.css")){ echo "\n<link rel='stylesheet' href='".e_FILE."style.css' />\n"; } ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<meta http-equiv="content-style-type" content="text/css" />
<?php
 echo "<script type='text/javascript' src='".e_FILE."e107.js'></script>\n";
if(file_exists(THEME."theme.js")){echo "<script type='text/javascript' src='".THEME."theme.js'></script>\n";}
 if(file_exists(e_FILE."user.js")){echo "<script type='text/javascript' src='".e_FILE."user.js'></script>\n";}
if(function_exists("headerjs")){     echo headerjs(); }
if($htmlarea_js){ echo $htmlarea_js; }
if($eplug_js){ echo "<script type='text/javascript' src='{$eplug_js}'></script>\n"; }
if($eplug_css){ echo "\n<link rel='stylesheet' href='{$eplug_css}' type='text/css' />\n"; }
?>
</head>
<body>
<?php

$ns = new e107table;
$e107_var = array();

echo "<div style='text-align:center'>
<img src='".e_IMAGE."adminlogo.png' alt='Logo' />
<br />";

if(ADMIN == TRUE)
{
        $str = str_replace(".", "", ADMINPERMS);
        if(ADMINPERMS == "0"){
                echo ADLAN_48.": ".ADMINNAME." (".ADLAN_49.")";
        }
        else
        {
                echo ADLAN_48.": ".ADMINNAME." (".ADLAN_50.":  ".$str.")";
        }
}
else
{
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
                $e107_var['x']['text']=ADLAN_52;
                $e107_var['x']['link']=e_ADMIN.$adminfpage;

                $e107_var['y']['text']=ADLAN_53;
                $e107_var['y']['link']=e_BASE."index.php";

                $text .= show_admin_menu("",time(),$e107_var)."<br />";

                require_once(e_ADMIN."header_links.php");
                $text .= get_admin_treemenu(ADLAN_93,time(),$e107_var,TRUE);
                unset($e107_var);

                // Plugin links menu

                $sql2 = new db;
                if($sql2 -> db_Select("plugin", "*", "plugin_installflag=1"))
                {
                        while($row = $sql2 -> db_Fetch())
                        {
                                extract($row);
                                include(e_PLUGIN.$plugin_path."/plugin.php");

                                //Link Plugin Manager
                                $e107_var['x']['text'] = "<b>".ADLAN_98."</b>";
                                $e107_var['x']['link'] = e_ADMIN."plugin.php";
                                $e107_var['x']['perm'] = "P";

                                // Links Plugins
                                if($eplug_conffile)
                                {
                                        $e107_var['x'.$plugin_id]['text'] = $eplug_caption;
                                        $e107_var['x'.$plugin_id]['link'] = e_PLUGIN.$plugin_path."/".$eplug_conffile;
                                        $e107_var['x'.$plugin_id]['perm'] = "P".$plugin_id;
                                }
                                unset($eplug_conffile, $eplug_name, $eplug_caption);
                        }
                        $text .= get_admin_treemenu(ADLAN_95,time(),$e107_var,TRUE);
                        unset($e107_var);
                }
                unset($e107_var);
                $e107_var['x']['text']=ADLAN_46;
                $e107_var['x']['link']=e_ADMIN."admin.php?logout";
                $text .= "<br />".show_admin_menu("",$act,$e107_var);
                $ns -> tablerender(LAN_head_1, $text);

        }
        else
        {
                $text = "<div style='text-align:center'>";
                unset($e107_var);
                $e107_var['x']['text']=ADLAN_53;
                $e107_var['x']['link']=e_ADMIN."../index.php";
                $text .= show_admin_menu("",$act,$e107_var);
                $text  .="</div>";
                $ns -> tablerender(LAN_head_1, $text);
                unset($text);
        }

if(ADMINPERMS == "0")
{
        if((ADMINPWCHANGE+2592000) < time())
        {
                $text = "<div style='mediumtext; text-align:center'>".ADLAN_102." <a href='".e_ADMIN."updateadmin.php'>".ADLAN_103."</a></div>";
                $ns -> tablerender(ADLAN_104, $text);
        }
}

if(!($handle=opendir(e_LANGUAGEDIR.e_LANGUAGE."/admin/help/"))){
  $handle=opendir(e_LANGUAGEDIR."English/admin/help/");
}

$text = "";
while(false !== ($file = readdir($handle)))
{
        if($file != "." && $file != ".." && $file != "CVS")
        {
                if(eregi($file, e_SELF))
                {
                        if(file_exists(e_LANGUAGEDIR.e_LANGUAGE."/admin/help/".$file)){@require_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/help/".$file);}
                        else{@require_once(e_LANGUAGEDIR."English/admin/help/".$file);}
                }
        }
}
closedir($handle);
}

$plugpath = e_PLUGIN.substr(strrchr(substr(e_SELF, 0, strrpos(e_SELF, "/")), "/"), 1)."/help.php";
if(file_exists($plugpath)){
        @require_once($plugpath);
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

function show_admin_menu($title,$page,$e107_vars){
        global $ns;
        $text = "<div style='text-align:center; width:100%'><table class='fborder' style='width:98%;'>";
        foreach(array_keys($e107_vars) as $act)
        {
                $pre = "";
                $post = "";
                if($page == $act){
                        $pre = "<b>&laquo;&nbsp;";
                        $post = "&nbsp;&raquo;</b>";
                }
                $t=str_replace(" ","&nbsp;",$e107_vars[$act]['text']);
                if(!$e107_vars[$act]['perm'] || getperms($e107_vars[$act]['perm']))
                {
                        $text .= "<tr><td class='button'><div style='width:100%; text-align:center'><a style='cursor:hand; cursor:pointer; text-decoration:none;' href='{$e107_vars[$act]['link']}'>{$pre}{$t}{$post}</a></div></td></tr>";
                }
        }
        $text .= "</table></div>";
        if($title=="")
        {
                return $text;
        }
        $ns -> tablerender($title,$text);
}

function get_admin_treemenu($title,$page,$e107_vars,$sortlist=FALSE)
{
        global $ns;

        if($sortlist == TRUE)
        {
                $temp = $e107_vars;
                unset($e107_vars);
                foreach(array_keys($temp) as $key)
                {
                        $func_list[]=$temp[$key]['text'];
                }

    usort($func_list, 'strcoll');

                foreach($func_list as $func_text)
                {
                        foreach(array_keys($temp) as $key)
                        {
                                if($temp[$key]['text'] == $func_text)
                                {
                                        $e107_vars[] = $temp[$key];
                                }
                        }
                }
        }

        $idtitle="yop_".str_replace(" ","",$title);
        $text = "<div style='text-align:center; width:100%'><table class='fborder' style='width:100%;'>";
        $text .= "<tr><td class='button'><a style='text-align:center; cursor:hand; cursor:pointer; text-decoration:none;' onclick=\"expandit('{$idtitle}');\" >{$title}</a></td></tr>";
        $text .= "<tr id=\"{$idtitle}\" style=\"display: none;\" ><td class='forumheader3' style='text-align:left;'>";
        foreach(array_keys($e107_vars) as $act)
        {
                $pre = "";
                $post = "";
                if($page == $act)
                {
                        $pre = "<b> &laquo; ";
                        $post = " &raquo; </b>";
                }
                if(!$e107_vars[$act]['perm'] || getperms($e107_vars[$act]['perm']))
                {
                        $text .= "{$pre}<a style='text-decoration:none;' href='{$e107_vars[$act]['link']}'>{$e107_vars[$act]['text']}</a>{$post}<br />";
                }
        }

        $text .= "</td></tr>";
        $text .= "</table></div>";
        return $text;
}
?>