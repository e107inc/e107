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
<title><?php echo SITENAME." : ".LAN_head_4; ?></title>
<link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
<?php if(file_exists(e_FILE."e107.css")){ echo "\n<link rel='stylesheet' href='".e_FILE."e107.css' />\n"; } ?>
<?php if(file_exists(e_FILE."style.css")){ echo "\n<link rel='stylesheet' href='".e_FILE."style.css' />\n"; } ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<meta http-equiv="content-style-type" content="text/css" />
<?php
echo "<script type='text/javascript' src='".e_FILE."e107.js'></script>";
if(file_exists(THEME."theme.js")){echo "<script type='text/javascript' src='".THEME."theme.js'></script>";}
if(file_exists(e_FILE."user.js")){echo "<script type='text/javascript' src='".e_FILE."user.js'></script>\n";}
if(function_exists("headerjs")){     echo headerjs(); }
if($eplug_js){ echo "<script type='text/javascript' src='{$eplug_js}'></script>\n"; }
?>
</head>
<body>
<?php

$ns = new e107table;
$e107_var = array();

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
                                $e107_var['x']['text']=ADLAN_52;
                                $e107_var['x']['link']=e_ADMIN.$adminfpage;

                                $e107_var['y']['text']=ADLAN_53;
                                $e107_var['y']['link']=e_BASE."index.php";

    //    $text = "<a href='".e_ADMIN.$adminfpage."'>".ADLAN_52."</a><br /><a href='".e_BASE."index.php'>".ADLAN_53."</a><br /><br />";
    //     $text ="<div style='text-align:center'>";
                        $text .= show_admin_menu("",$act,$e107_var)."<br />";
//         $text .= "<input type='button' class='button' style='width:100%' onClick=\"document.location='".e_ADMIN.$adminfpage."'\" value='".ADLAN_52."' /><br />
//         <input type='button' class='button' style='width:100%' onClick=\"document.location='".e_BASE."index.php'\" value='".ADLAN_53."' /><br /><br />";


                unset($e107_var);

                // Admin links menu

                $e107_var['a']['text']=ADLAN_8;
                $e107_var['a']['link']=e_ADMIN."administrator.php";
                $e107_var['a']['perm']="3";

                $e107_var['b']['text']=ADLAN_10;
                $e107_var['b']['link']=e_ADMIN."updateadmin.php";

                $e107_var['c']['text']=ADLAN_14;
                $e107_var['c']['link']=e_ADMIN."article.php";
                $e107_var['c']['perm']="J";

                $e107_var['e']['text']=ADLAN_34;
                $e107_var['e']['link']=e_ADMIN."banlist.php";
                $e107_var['e']['perm']="4";

                $e107_var['f']['text']=ADLAN_54;
                $e107_var['f']['link']=e_ADMIN."banner.php";
                $e107_var['f']['perm']="D";

                $e107_var['g']['text']=ADLAN_74;
                $e107_var['g']['link']=e_ADMIN."cache.php";
                $e107_var['g']['perm']="0";

                $e107_var['h']['text']=ADLAN_56;
                $e107_var['h']['link']=e_ADMIN."chatbox.php";
                $e107_var['h']['perm']="C";

                $e107_var['i']['text']=ADLAN_16;
                $e107_var['i']['link']=e_ADMIN."content.php";
                $e107_var['i']['perm']="L";

                $e107_var['j']['text']=ADLAN_42;
                $e107_var['j']['link']=e_ADMIN."custommenu.php";
                $e107_var['j']['perm']="2";

                $e107_var['k']['text']=ADLAN_44;
                $e107_var['k']['link']=e_ADMIN."db.php";
                $e107_var['k']['perm']="0";

                $e107_var['l']['text']=ADLAN_24;
                $e107_var['l']['link']=e_ADMIN."download.php";
                $e107_var['l']['perm']="R";

                $e107_var['m']['text']=ADLAN_58;
                $e107_var['m']['link']=e_ADMIN."emoticon.php";
                $e107_var['m']['perm']="F";

                $e107_var['n']['text']=ADLAN_30;
                $e107_var['n']['link']=e_ADMIN."filemanager.php";
                $e107_var['n']['perm']="6";

                $e107_var['o']['text']=ADLAN_12;
                $e107_var['o']['link']=e_ADMIN."forum.php";
                $e107_var['o']['perm']="5";

                $e107_var['p']['text']=ADLAN_60;
                $e107_var['p']['link']=e_ADMIN."frontpage.php";
                $e107_var['p']['perm']="G";

                $e107_var['q']['text']=ADLAN_105;
                $e107_var['q']['link']=e_ADMIN."image.php";
                $e107_var['q']['perm']="4";

                $e107_var['r']['text']=ADLAN_20;
                $e107_var['r']['link']=e_ADMIN."links.php";
                $e107_var['r']['perm']="I";

                $e107_var['s']['text']=ADLAN_64;
                $e107_var['s']['link']=e_ADMIN."log.php";
                $e107_var['s']['perm']="S";

                $e107_var['t']['text']=ADLAN_40;
                $e107_var['t']['link']=e_ADMIN."ugflag.php";
                $e107_var['t']['perm']="9";

                $e107_var['u']['text']=ADLAN_6;
                $e107_var['u']['link']=e_ADMIN."menus.php";
                $e107_var['u']['perm']="2";

                $e107_var['v']['text']=ADLAN_66;
                $e107_var['v']['link']=e_ADMIN."meta.php";
                $e107_var['v']['perm']="T";

                $e107_var['w']['text']=ADLAN_0;
                $e107_var['w']['link']=e_ADMIN."newspost.php";
                $e107_var['w']['perm']="H";

                $e107_var['x']['text']=ADLAN_62;
                $e107_var['x']['link']=e_ADMIN."newsfeed.php";
                $e107_var['x']['perm']="E";

                $e107_var['y']['text']=ADLAN_68;
                $e107_var['y']['link']=e_ADMIN."phpinfo.php";
                $e107_var['y']['perm']="0";

                $e107_var['z']['text']=ADLAN_70;
                $e107_var['z']['link']=e_ADMIN."poll.php";
                $e107_var['z']['perm']="U";

                $e107_var['aa']['text']=ADLAN_4;
                $e107_var['aa']['link']=e_ADMIN."prefs.php";
                $e107_var['aa']['perm']="1";

                $e107_var['bb']['text']=ADLAN_72;
                $e107_var['bb']['link']=e_ADMIN."upload.php";
                $e107_var['bb']['perm']="V";

                $e107_var['cc']['text']=ADLAN_18;
                $e107_var['cc']['link']=e_ADMIN."review.php";
                $e107_var['cc']['perm']="K";

                $e107_var['dd']['text']=ADLAN_36;
                $e107_var['dd']['link']=e_ADMIN."users.php";
                $e107_var['dd']['perm']="4";

                $e107_var['ee']['text']=ADLAN_38;
                $e107_var['ee']['link']=e_ADMIN."userclass2.php";
                $e107_var['ee']['perm']="4";

                $e107_var['ff']['text']=ADLAN_28;
                $e107_var['ff']['link']=e_ADMIN."wmessage.php";
                $e107_var['ff']['perm']="M";

                $text .= get_admin_treemenu(ADLAN_93,$act,$e107_var);



                unset($e107_var);

                // Plugin links menu

                $sql2 = new db;
                if($sql2 -> db_Select("plugin", "*", "plugin_installflag=1")){
                        while($row = $sql2 -> db_Fetch()){
                                extract($row);
                                include(e_PLUGIN.$plugin_path."/plugin.php");

                                //Link Plugin Manager
                                $e107_var['x']['text'] = "<b>".ADLAN_98."</b>";
                                $e107_var['x']['link'] = e_ADMIN."plugin.php";
                                $e107_var['x']['perm'] = "P";

                                // Links Plugins
                                if($eplug_conffile){
                        $e107_var['x'.$plugin_id]['text'] = $eplug_caption;
                                        $e107_var['x'.$plugin_id]['link'] = e_PLUGIN.$plugin_path."/".$eplug_conffile;
                                        $e107_var['x'.$plugin_id]['perm'] = "P".$plugin_id;
                                }
                                unset($eplug_conffile, $eplug_name, $eplug_caption);
                        }
                        $text .= get_admin_treemenu(ADLAN_95,$act,$e107_var);
                        unset($e107_var);
                }
//                unset($e107_var);
//                $e107_var['x']['text']=ADLAN_46;
//                $e107_var['x']['link']=e_ADMIN."admin.php?logout";
//                $text .= "<br />".show_admin_menu("",$act,$e107_var);
      $ns -> tablerender(LAN_head_1, $text);

 }else{

     $text = "<div style='text-align:center'>";
                unset($e107_var);
                $e107_var['x']['text']=ADLAN_53;
                $e107_var['x']['link']=e_ADMIN."../index.php";
                $text .= show_admin_menu("",$act,$e107_var);
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
                                @require_once("help/".$file);
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
        foreach(array_keys($e107_vars) as $act){
                $pre = "";
                $post = "";
                if($page == $act){
                        $pre = "<b>&laquo;&nbsp;";
                        $post = "&nbsp;&raquo;</b>";
                }
                $t=str_replace(" ","&nbsp;",$e107_vars[$act]['text']);
                if(!$e107_vars[$act]['perm'] || getperms($e107_vars[$act]['perm'])){
                        $text .= "<tr><td class='button'><div style='width:100%; text-align:center'><a style='cursor:hand; cursor:pointer; text-decoration:none;' href='{$e107_vars[$act]['link']}'>{$pre}{$t}{$post}</a></div></td></tr>";
                }
        }
        $text .= "</table></div>";
        if($title==""){
                return $text;
        }
        $ns -> tablerender($title,$text);
}

function get_admin_treemenu($title,$page,$e107_vars){
        global $ns;
        $idtitle="yop_".str_replace(" ","",$title);
        $text = "<div style='text-align:center; width:100%'><table class='fborder' style='width:100%;'>";
        $text .= "<tr><td class='button'><a style='text-align:center; cursor:hand; cursor:pointer; text-decoration:none;' onclick=\"expandit('{$idtitle}');\" >{$title}</a></td></tr>";
        $text .= "<tr id=\"{$idtitle}\" style=\"display: none;\" ><td class='forumheader3' style='text-align:left;'>";
        foreach(array_keys($e107_vars) as $act){
                $pre = "";
                $post = "";
                if($page == $act){
                        $pre = "<b> &laquo; ";
                        $post = " &raquo; </b>";
                }
                if(!$e107_vars[$act]['perm'] || getperms($e107_vars[$act]['perm'])){
                        $text .= "{$pre}<a style='text-decoration:none;' href='{$e107_vars[$act]['link']}'>{$e107_vars[$act]['text']}</a>{$post}<br />";
                }
        }

        $text .= "</td></tr>";
        $text .= "</table></div>";
        return $text;
//        $ns -> tablerender($title,$text);
}
?>