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
|     $Source: /cvs_backup/e107_0.7/e107_admin/menus.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-09-30 11:23:29 $
|     $Author: loloirie $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("2")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
$frm = new form;

//$sql -> db_Select("core", "*", "e107_name='menu_pref' ");
//$row = $sql -> db_Fetch();
//$tmp = stripslashes($row['e107_value']);
//$menu_pref=unserialize($tmp);

foreach($_POST['menuAct'] as $k => $v){
        if(trim($v)){
                $id = $k;
                list($menu_act,$location,$position,$newloc) = explode(".",$_POST['menuAct'][$k]);
        }
}

if($menu_act == 'config')
{
        header("location:".SITEURL.$PLUGINS_DIRECTORY.$location."_menu/config.php");
        exit;
}

if($menu_act == "adv")
{
        require_once(e_HANDLER."userclass_class.php");
        $sql -> db_Select("menus", "*", "menu_id='$id' ");
        $row = $sql -> db_Fetch(); extract($row);
        $listtype = substr($menu_pages,0,1);
        $menu_pages = substr($menu_pages,2);
        $menu_pages = preg_replace("#\|#","\n",$menu_pages);
        $text = "<div style='text-align:center'>
        <form  method='post' action='".e_SELF."'>\n
        <table style='width:100%'>
        <tr>
        <td>
        <input type='hidden' name='menuAct[$menu_id]' value='sv.$menu_id' />
        ";
        $text .= MENLAN_4." ";
        $text .= r_userclass('menu_class',$menu_class,"off","public,member,guest,admin,classes,nobody");
        $text .= "</td>
        </tr>";
        $text .= "<tr><td><br />";
        $checked = ($listtype == 1) ? " checked='checked' " : "";
        $text .= "<input type='radio' {$checked} name='listtype' value='1' /> ".MENLAN_26."<br />";
        $checked = ($listtype == 2) ? " checked='checked' " : "";
        $text .= "<input type='radio' {$checked} name='listtype' value='2' /> ".MENLAN_27."<br /><br />".MENLAN_28."<br />";
        $text .= "<textarea name='pagelist' cols='60' rows='10' class='tbox'>$menu_pages</textarea>";
        $text .= "
        <tr>
        <td style=\"text-align:center\"><br />
        <input class=\"button\" type=\"submit\" name=\"class_submit\" value=\"".MENLAN_6."\" />
        </td>
        </tr>
        </table>
        </form>
        </div>";
        $caption = MENLAN_7." ".$menu_name;
        $ns -> tablerender($caption, $text);
}

unset($message);

if($menu_act == "sv")
{
        $pagelist = explode("\r\n",$_POST['pagelist']);
        for($i = 0 ;$i < count($pagelist) ; $i++){
                $pagelist[$i]=trim($pagelist[$i]);
        }
        $plist = implode("|",$pagelist);
        $pageparms = $_POST['listtype'].'-'.$plist;
        $pageparms = preg_replace("#\|$#","",$pageparms);
        $pageparms = (trim($_POST['pagelist']) == '') ? '' : $pageparms;
        $sql -> db_Update("menus", "menu_class='".$_POST['menu_class']."', menu_pages='{$pageparms}' WHERE menu_id='$id' ");
        $message = "<br />".MENLAN_8."<br />";
}

if($menu_act == "move")
{
        $menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='$position' ");
        $sql -> db_Update("menus", "menu_location='$newloc', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
}

if($menu_act== 'activate')
{
        $menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='$position' ");
        $sql -> db_Update("menus", "menu_location='$location', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
}

if($menu_act == "deac")
{
        $sql -> db_Update("menus", "menu_location='0', menu_order='0' WHERE menu_id='$id' ");
}

if($menu_act == "bot")
{
   $menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='$location' ");
        $sql -> db_Update("menus", "menu_order=".($menu_count+1)." WHERE menu_order='$position' AND menu_location='$location' ");
        $sql -> db_Update("menus", "menu_order=menu_order-1 WHERE menu_location='$location' AND menu_order > $position");
}

if($menu_act == "top")
{
        $sql -> db_Update("menus", "menu_order=menu_order+1 WHERE menu_location='$location' AND menu_order < $position");
        $sql -> db_Update("menus", "menu_order=0 WHERE menu_id='$id' ");
}

if($menu_act == "dec")
{
        $sql -> db_Update("menus", "menu_order=menu_order-1 WHERE menu_order='".($position+1)."' AND menu_location='$location' ");
        $sql -> db_Update("menus", "menu_order=menu_order+1 WHERE menu_id='$id' AND menu_location='$location' ");
}

if($menu_act == "inc")
{
        $sql -> db_Update("menus", "menu_order=menu_order+1 WHERE menu_order='".($position-1)."' AND menu_location='$location' ");
        $sql -> db_Update("menus", "menu_order=menu_order-1 WHERE menu_id='$id' AND menu_location='$location' ");
}

$handle=opendir(e_PLUGIN);
$c=0;
while(false !== ($file = readdir($handle)))
{
        if($file != "." && $file != ".." && $file != "index.html" && (strstr($file, "menu") || (strstr($file, "custom") && !strstr($file, "custompage"))))
        {
                if($file == "custom")
                {
                        $handle2=opendir(e_PLUGIN."custom/");
                        $d=0;
                        while(false !== ($file2 = readdir($handle2)))
                        {
                                if($file2 != "." && $file2 != ".." && $file2 != "/" && $file2 != "Readme.txt")
                                {
                                        $file2 = "custom_".str_replace(".php", "", $file2);
                                        if(!$sql -> db_Select("menus", "*", "menu_name='$file2'"))
                                        {
                                                $sql -> db_Insert("menus", " 0, '$file2', 0, 0, 0, '' ");
                                                $message .= "<b>".MENLAN_9." - ".$file2."</b><br />";
                                        }
                                        $menustr .= "&".$file2;
                                        $d++;
                                }
                        }
                        closedir($handle2);
                }
                else if(!$sql -> db_Select("menus", "*", "menu_name='$file'"))
                {
                        if(file_exists(e_PLUGIN.$file."/plugin.php")){
                                @include(e_PLUGIN.$file."/plugin.php");
                                if($sql -> db_Select("plugin", "*", "plugin_name='$eplug_name' AND plugin_installflag='1' "))
                                {
                                        $sql -> db_Insert("menus", " 0, '$file', 0, 0, 0, '' ");
                                        $message .= "<b>".MENLAN_10." - ".$file."</b><br />";
                                }
                        }
                        else
                        {
                        $sql -> db_Insert("menus", " 0, '$file', 0, 0, 0, '' ");
                        $message .= "<b>".MENLAN_10." - ".$file."</b><br />";
                }
        }
        $menustr .= "&".str_replace(".php", "", $file);
        $c++;
}
}
closedir($handle);

$areas = substr_count(($NEWSHEADER ? $NEWSHEADER : $HEADER).$FOOTER, "MENU");

$sql2 = new db;
for($a=1; $a<=$areas; $a++)
{
        if($sql -> db_Select("menus", "*",  "menu_location='$a' ORDER BY menu_order ASC"))
        {
                $c=1;
                while($row = $sql -> db_Fetch())
                {
                        extract($row);
                        $sql2 -> db_Update("menus", "menu_order='$c' WHERE menu_id='$menu_id' ");
                        $c++;
                }
        }
}

$sql -> db_Select("menus");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch())
{
        if(!eregi($menu_name, $menustr))
        {
                $sql2 -> db_Delete("menus", "menu_name='$menu_name'");
                $message .= "<b>".MENLAN_11." - ".$menu_name."</b><br />";
        }
}

$menus_used = (substr_count(($NEWSHEADER ? $NEWSHEADER : $HEADER).$FOOTER, "MENU"));
$sql -> db_Update("menus", "menu_location='0', menu_order='0' WHERE menu_location>'$menus_used' ");

if($message != ""){
        echo "<div style='text-align:center'><b>".$message."</b></div>";
}

parseheader(($NEWSHEADER ? $NEWSHEADER : $HEADER), $menus_used);

echo "<div style='text-align:center'>";
echo "<div style='font-size:14px' class='fborder'><div class='forumheader'><b>".MENLAN_22."</b></div></div><br />";
echo $frm -> form_open("post",e_SELF,"menuActivation");
echo "<table style='width:96%' class='fborder'>";

$sql -> db_Select("menus", "*", "menu_location='0' ORDER BY menu_name ");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch())
{
        $text="";
        $menu_name = eregi_replace("_menu", "", $menu_name);

        echo "<tr>
        <td class=\"fcaption\" style=\"text-align:center\">
        <b>".$menu_name."</b>
        </td>
        </tr>
        <tr>
        <td class=\"forumheader3\" style=\"text-align:center\">";

        $text .= "<div>
        <select id='menuAct_$menu_id' name='menuAct[$menu_id]' class='tbox' onchange='this.form.submit()' >";
        $text .= $frm -> form_option(MENLAN_12." ...",TRUE, " ");

        for($a=1; $a<=$menus_used; $a++)
        {
                $text .= $frm -> form_option(MENLAN_13." ".$a,"", "activate.$a");
        }
        $text .= $frm -> form_select_close()."</div>";
        echo $text;
        echo "</td></tr>
        <tr><td><br /></td></tr>
        ";
}
echo "</table>";
echo $frm -> form_close();
echo "</div>";

parseheader($FOOTER, $menus_used);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function parseheader($LAYOUT)
{
        $tmp = explode("\n", $LAYOUT);
        for($c=0; $c < count($tmp); $c++)
        {
                if(preg_match("/[\{|\}]/", $tmp[$c]))
                {
                        $str = checklayout($tmp[$c]);
                }
                else
                {
                        echo $tmp[$c];
                }
        }
}
function checklayout($str){
        global $pref, $menus_used, $areas;
        global $frm;

        if(strstr($str, "LOGO")){
                echo "[Logo]";
        }else if(strstr($str, "SITENAME")){
                echo "[SiteName]";
        }else if(strstr($str, "SITETAG")){
                echo "[SiteTag]";
        }else if(strstr($str, "SITELINKS")){
                echo "[SiteLinks]";
        }else if(strstr($str, "MENU")){
                $ns = new e107table;
                $menu = preg_replace("/\{MENU=(.*?)\}/si", "\\1", $str);
                echo "<div style=\"text-align:center; font-size:14px\" class=\"fborder\"><div class=\"forumheader\"><b>".MENLAN_14."  ".$menu."</b></div></div><br />";
                $text = "&nbsp;";
                $sql9 = new db;
                if($sql9 -> db_Count("menus", "(*)", " WHERE menu_location='$menu' "))
                {


                unset($text);
                                        echo $frm -> form_open("post",e_SELF,"frm_menu_".intval($menu));

                $sql9 -> db_Select("menus", "*",  "menu_location='$menu' ORDER BY menu_order");
                $menu_count = $sql9 -> db_Rows();
                while(list($menu_id, $menu_name, $menu_location, $menu_order, $menu_class, $menu_pages) = $sql9-> db_Fetch()){
                        $menu_name = eregi_replace("_menu", "", $menu_name);
                                                                $vis = ($menu_class || strlen($menu_pages) > 1) ? " <span style='color:red'>*</span> " : "";
                        $caption = "<div style=\"text-align:center\">{$menu_name}{$vis}</div>";
                        $menu_info = "{$menu_location}.{$menu_order}";

                                                                $text = "";
                        $config_path = e_PLUGIN.$menu_name."_menu/config.php";
                        $conf = FALSE;
                        if(file_exists($config_path)){
                                $conf = TRUE;
                        }
                                                                $text .="<select id='menuAct_$menu_id' name='menuAct[$menu_id]' class='tbox' onchange='this.form.submit()' >";
                                                                $text .= $frm -> form_option(MENLAN_25,TRUE," ");
                                                                $text .= $frm -> form_option(MENLAN_15,"","deac.{$menu_info}");

                                                                if($conf)
                                                                {
                                                                        $text .= $frm -> form_option(MENLAN_16,"","config.{$menu_name}");
                                                                }

                        if($menu_order != 1){
                                                                        $text .= $frm -> form_option(MENLAN_17,"","inc.{$menu_info}");
                                                                        $text .= $frm -> form_option(MENLAN_24,"","top.{$menu_info}");
                        }
                        if($menu_count != $menu_order){
                                                                        $text .= $frm -> form_option(MENLAN_18,"","dec.{$menu_info}");
                                                                        $text .= $frm -> form_option(MENLAN_23,"","bot.{$menu_info}");
                        }
                        for($c=1; $c<=$areas; $c++){
                                if($menu <> $c){
                                                                                                        $text .= $frm -> form_option(MENLAN_19." ".$c,"","move.{$menu_info}.".$c);

                                }
                        }

                                                                $text .= $frm -> form_option(MENLAN_20,"","adv.{$menu_info}");
                                                                $text .= $frm -> form_select_close();
                        $ns -> tablerender($caption, $text);
                        echo "<div><br /></div>";
                }
                                        echo $frm -> form_close();
                                }
        }else if(strstr($str, "SETSTYLE")){
                $tmp = explode("=", $str);
                $style = preg_replace("/\{SETSTYLE=(.*?)\}/si", "\\1", $str);
        }else if(strstr($str, "SITEDISCLAIMER")){
                echo "[Sitedisclaimer]";
        }
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
require_once("footer.php");
?>