<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/lancheck.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-09 18:12:38 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("0")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");


function show_comparison($language,$filename){
        global $LANGUAGES_DIRECTORY;
        $English = get_lan_phrases("English");
        $check = get_lan_phrases($language);

        $keys = array_keys($English[$filename]);
        natsort($keys);
        $ret .= "<table class='fborder' style='".ADMIN_WIDTH."'>
        <tr>
        <td class='fcaption' style='text-align:center;'>".LAN_CHECK_7."</td>
        <td class='fcaption' style='text-align:center;'>{$LANGUAGES_DIRECTORY}<br />English/{$filename}</td>
        <td class='fcaption' style='text-align:center;'>{$LANGUAGES_DIRECTORY}<br />{$language}/{$filename}</td>
        </tr>";
        foreach($keys as $k){
                $ret .= "<tr>
                <td class='forumheader3'>{$k}</td>
                <td class='forumheader3'>\"{$English[$filename][$k]}\"</td>
                ";
                if(isset($check[$filename][$k])){
                        $ret .= "<td class='forumheader3'>\"{$check[$filename][$k]}\"</td>";
                } else {
                        $ret .= "<td class='forumheader'>".LAN_CHECK_5."</td>";
                }
                $ret .= "</tr>";
        }
        $ret .= "</table>";
        return $ret;
}


function get_lan_phrases($lang){
        $ret = array();
// Read English lan_ files
        $base_dir = e_LANGUAGEDIR.$lang;
        if($r = opendir($base_dir)){
                while($file = readdir($r)){
                        $fname = $base_dir."/".$file;
                        if(preg_match("#^lan_#",$file) && is_file($fname)){
                                $data = file($fname);
                                foreach($data as $line){
                                        if(preg_match("#\"(.*?)\".*?\"(.*)\"#",$line,$matches)){
                                                $ret[$file][$matches[1]]=htmlentities($matches[2]);
                                        }
                                }
                        }
                }
                closedir($r);
        }
// Read $lang/admin lan_ files
        $base_dir = e_LANGUAGEDIR.$lang."/admin";
        if($r = opendir($base_dir)){
                while($file = readdir($r)){
                        $fname = $base_dir."/".$file;
                        if(preg_match("#^lan_#",$file) && is_file($fname)){
                                $data = file($fname);
                                foreach($data as $line){
                                        if(preg_match("#\"(.*?)\".*?\"(.*)\"#",$line,$matches)){
                                                $ret["admin/".$file][$matches[1]]=$matches[2];
                                        }
                                }
                        }
                }
                closedir($r);
        }
        return $ret;
}



function check_core_lanfiles($checklan){

        $English = get_lan_phrases("English");
        $check = get_lan_phrases($checklan);

        $text .= "<table class='fborder' style='".ADMIN_WIDTH."'>";
        $keys = array_keys($English);
        sort($keys);
        foreach($keys as $k){
                $lnk = "<a href='?{$checklan}.{$k}'>{$k}</a>";
                if(array_key_exists($k,$check)){
                        $text .= "<tr><td class='forumheader3'>{$lnk}</td>";
                        $subkeys = array_keys($English[$k]);
                        sort($subkeys);
                        $er="";
                        foreach($subkeys as $sk){
                                if(!array_key_exists($sk,$check[$k])){
                                        $er .= ($er) ? "<br />" : "";
                                        $er .= $sk." ".LAN_CHECK_5;
                                }
                        }
                        if($er){
                                $text .= "<td class='forumheader3'><div class='smalltext'>{$er}</div></td></tr>";
                        } else {
                                $text .= "<td class='forumheader3'><div class='smalltext'>".LAN_CHECK_6."</div></td></tr>";
                        }
                } else {
                        $text .= "<tr><td class='forumheader3'>{$lnk}</td><td class='forumheader'>".LAN_CHECK_4."</td></tr>";
                }
//                $text .= "$k<br />";
        }
        $text .= "</table>";

        return $text;
}

function show_languages(){
        if($r = opendir(e_LANGUAGEDIR)){
                while($file = readdir($r)){
                        $fname = e_LANGUAGEDIR.$file;
                        if(is_dir($fname) && $file != "English" && $file != "CVS" && $file != "." && $file != ".."){
                                $languages[]=$file;
                        }
                }
                $text .= "
                <form name='lancheck' method='POST'>
                <table class='fborder' style='".ADMIN_WIDTH."'>
                <tr>
                <td class='fcaption'>".LAN_CHECK_1."</td></tr>
                <tr><td class='forumheader3'>";
                foreach($languages as $lang){
                        $text .= "<input type='radio' name='language' value='{$lang}' /> {$lang}<br />";
                }
                $text .= "</td></tr>
                <tr><td class='forumheader' style='text-align:center;'><input type='submit' name='check_lang' value='".LAN_CHECK_2."' class='button' />
                </td></tr>
                </table></form>";
                return $text;
        }
}

if(e_QUERY){
        $qs = explode(".",rawurldecode(e_QUERY),2);
        $text = show_comparison($qs[0],$qs[1]);
        $ns -> tablerender(LAN_CHECK_3.": {$qs[0]}",$text);
        require_once("footer.php");
        exit;
}

if($_POST['check_lang']){
        $text = check_core_lanfiles($_POST['language']);
        $ns -> tablerender(LAN_CHECK_3.": ".$_POST['language'],$text);
        require_once("footer.php");
        exit;
}

$ns -> tablerender("",show_languages());
//$text = check_core_lanfiles();
//$ns -> tablerender(LOGLAN_13, $text);
require_once("footer.php");

?>