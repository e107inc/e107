<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/menus2.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("2")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
$frm = new form;

function headerjs(){
$headerjs =  "<script type=\"text/javascript\">
<!--
image1 = new Image(); image1.src = \"".e_IMAGE."generic/off.png\";
image2 = new Image(); image2.src = \"".e_IMAGE."generic/move.png\";
image3 = new Image(); image3.src = \"".e_IMAGE."generic/up.png\";
image4 = new Image(); image4.src = \"".e_IMAGE."generic/up.png\";
// -->
</script>";
return $headerjs;
}
$sql -> db_Select("core", "*", "e107_name='menu_pref' ");
$row = $sql -> db_Fetch();
$tmp = stripslashes($row['e107_value']);
$menu_pref=unserialize($tmp);

if($_POST['menuAct']){
	$menu_act = $_POST['menuAct'];
	$position = $_POST['menuPosition'];
	if(preg_match("#move\.(\d+)#",$menu_act,$matches))
	{
		$menu_act = 'move';
		$position = $matches[1];
	}
	$id = $_POST['menuId'];
	$location= $_POST['menuLocation'];
}


if($menu_act == 'config')
{
	header("location:".$_POST['ConfigPath']);
	exit;
}

if($menu_act == "adv"){
        $sql -> db_Select("menus", "*", "menu_id='$id' ");
        $row = $sql -> db_Fetch(); extract($row);
        $text = "<div style=\"text-align:center\">
<form  method=\"post\" action=\"".e_SELF."?sv.".$menu_id."\">\n
<table style=\"width:50%\">
<tr>
<td>

        <input name=\"menu_class\" type=\"radio\" value=\"0\" ";
        if(!$menu_class){ $text .= "checked='checked'"; }
        $text .= " />".MENLAN_1."<br />

        <input name=\"menu_class\" type=\"radio\" value=\"252\" ";
        if($menu_class == 252){ $text .= "checked='checked'"; }
        $text .= " />".MENLAN_21."<br />

        <input name=\"menu_class\" type=\"radio\" value=\"253\" ";
        if($menu_class == 253){ $text .= "checked='checked'"; }
        $text .= " />".MENLAN_2."<br />

        <input name=\"menu_class\" type=\"radio\" value=\"254\" ";
        if($menu_class == 254){ $text .= "checked='checked'"; }
        $text .= " />".MENLAN_3."<br />";


        $sql -> db_Select("userclass_classes");
        while($row = $sql -> db_Fetch()){
                extract($row);
                $text .= "<input name=\"menu_class\" type=\"radio\" value=\"".$userclass_id."\"";
                if($menu_class == $userclass_id){ $text .= "checked='checked'"; }
                $text .= ">".MENLAN_4." ".$userclass_name." ".MENLAN_5."<br />";
        }

        $text .= "</td>
</tr>
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



if($menu_act == "sv"){
        $sql -> db_Update("menus", "menu_class='".$_POST['menu_class']."' WHERE menu_id='$id' ");
        $message = "<br />".MENLAN_8."<br />";
}

if($menu_act == "move"){
        $menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='$position' ");
        $sql -> db_Update("menus", "menu_location='$position', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
}

if($_POST['activate'])
{
	list($act,$id,$position) = explode(".",$_POST['activate']);
	
	echo "position = $position <br />";
	echo "id = $id <br />";

	$menu_count = $sql -> db_Count("menus", "(*)", " WHERE menu_location='$position' ");
	$sql -> db_Update("menus", "menu_location='$position', menu_order='".($menu_count+1)."' WHERE menu_id='$id' ");
}



if($menu_act == "deac"){
        $sql -> db_Update("menus", "menu_location='0', menu_order='0' WHERE menu_id='$id' ");
        header("location: ".e_SELF);
        exit;
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

if($menu_act == "dec"){
        $sql -> db_Update("menus", "menu_order=menu_order-1 WHERE menu_order='".($position+1)."' AND menu_location='$location' ");
        $sql -> db_Update("menus", "menu_order=menu_order+1 WHERE menu_id='$id' AND menu_location='$location' ");
}

if($menu_act == "inc"){
        $sql -> db_Update("menus", "menu_order=menu_order+1 WHERE menu_order='".($position-1)."' AND menu_location='$location' ");
        $sql -> db_Update("menus", "menu_order=menu_order-1 WHERE menu_id='$id' AND menu_location='$location' ");
        header("location: ".e_SELF);
        exit;
}
unset($message);
$handle=opendir(e_PLUGIN);
$c=0;
while(false !== ($file = readdir($handle))){
        if($file != "." && $file != ".." && $file != "index.html" && (strstr($file, "menu") || strstr($file, "custom"))){


                if($file == "custom"){
                        $handle2=opendir(e_PLUGIN."custom/");
                        $d=0;
                        while(false !== ($file2 = readdir($handle2))){
                                if($file2 != "." && $file2 != ".." && $file2 != "/" && $file2 != "Readme.txt"){
                                        $file2 = "custom_".str_replace(".php", "", $file2);
                                        if(!$sql -> db_Select("menus", "*", "menu_name='$file2'")){
                                                $sql -> db_Insert("menus", " 0, '$file2', 0, 0, 0 ");
                                                $message .= "<b>".MENLAN_9." - ".$file2."</b><br />";
                                        }
                                        $menustr .= "&".$file2;
                                        $d++;
                                }
                        }
                        closedir($handle2);


                }else if(!$sql -> db_Select("menus", "*", "menu_name='$file'")){
                        if(file_exists(e_PLUGIN.$file."/plugin.php")){
                                @include(e_PLUGIN.$file."/plugin.php");
                                if($sql -> db_Select("plugin", "*", "plugin_name='$eplug_name' AND plugin_installflag='1' ")){
                                        $sql -> db_Insert("menus", " 0, '$file', 0, 0, 0 ");
                                        $message .= "<b>".MENLAN_10." - ".$file."</b><br />";
                                }
                        }else{
                                $sql -> db_Insert("menus", " 0, '$file', 0, 0, 0 ");
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
for($a=1; $a<=$areas; $a++){
        if($sql -> db_Select("menus", "*",  "menu_location='$a' ORDER BY menu_order ASC")){
                $c=1;
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $sql2 -> db_Update("menus", "menu_order='$c' WHERE menu_id='$menu_id' ");
                        $c++;
                }
        }
}


$sql -> db_Select("menus");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
        if(!eregi($menu_name, $menustr)){
                $sql2 -> db_Delete("menus", "menu_name='$menu_name'");
                $message .= "<b>".MENLAN_11." - ".$menu_name."</b><br />";
        }
}

$menus_used = (substr_count(($NEWSHEADER ? $NEWSHEADER : $HEADER).$FOOTER, "MENU"));
$sql -> db_Update("menus", "menu_location='0', menu_order='0' WHERE menu_location>'$menus_used' ");

if($message != ""){
        echo "<div style=\"text-align:center\"><b>".$message."</b></div>";
}

parseheader(($NEWSHEADER ? $NEWSHEADER : $HEADER), $menus_used);

echo "<div style=\"text-align:center\">
<div style=\"font-size:14px\" class=\"fborder\"><div class=\"forumheader\"><b>".MENLAN_22."</b></div></div><br />
<table style=\"width:96%\" class=\"fborder\">";

$sql -> db_Select("menus", "*", "menu_location='0' ");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
        $menu_name = eregi_replace("_menu", "", $menu_name);

        echo "<tr>
<td class=\"fcaption\" style=\"text-align:center\">
<b>".$menu_name."</b>
</td>
</tr>
<td class=\"forumheader3\" style=\"text-align:center\">";
	$text = $frm -> form_open("post",e_SELF,"menuActivation");
	$text .= $frm -> form_select_open("activate","OnChange='this.form.submit()'");
	$text .= $frm -> form_option(MENLAN_12." ...",TRUE, "");
	
        for($a=1; $a<=$menus_used; $a++){
				$text .= $frm -> form_option(MENLAN_13." ".$a,"", "activate.$menu_id.$a");
        }
			$text .= $frm -> form_select_close();
			$text .= $frm -> form_close();
			echo $text;

echo "</td></tr>
<tr><td><br /></td></tr>
        ";
}
echo "</table></div>";


parseheader($FOOTER, $menus_used);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function parseheader($LAYOUT){
        $tmp = explode("\n", $LAYOUT);
        for($c=0; $c < count($tmp); $c++){
                if(preg_match("/[\{|\}]/", $tmp[$c])){
                        $str = checklayout($tmp[$c]);
                }else{
                        echo $tmp[$c];
                }
        }
}
function checklayout($str){
        global $pref, $menus_used, $menu_pref, $areas;
        global $PLUGINS_DIRECTORY, $frm;
        
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
                unset($text);
					
                $sql9 = new db;
                $sql9 -> db_Select("menus", "*",  "menu_location='$menu' ORDER BY menu_order");
                $menu_count = $sql9 -> db_Rows();
                while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql9-> db_Fetch()){
                        $menu_name = eregi_replace("_menu", "", $menu_name);
                        $caption = "<div style=\"text-align:center\">".$menu_name."</div>";


                        $text = "<div style='text-align:center'>";
								$text .= $frm -> form_open("post",e_SELF,"frm_".$menu_name);
								$text .= $frm -> form_hidden("menuId",$menu_id);
								$text .= $frm -> form_hidden("menuLocation",$menu_location);
								$text .= $frm -> form_hidden("menuPosition",$menu_order);
                        $config_path = e_PLUGIN.$menu_name."_menu/config.php";
                        $conf = FALSE;
                        if(file_exists($config_path)){
                        	$text .= $frm -> form_hidden("ConfigPath",SITEURL.$PLUGINS_DIRECTORY.$menu_name."_menu/config.php");
                        	$conf = TRUE;
                        	
                        }
								$text .= $frm -> form_select_open("menuAct","OnChange='this.form.submit()'");
								$text .= $frm -> form_option(MENLAN_25,TRUE,"");

								$text .= $frm -> form_option(MENLAN_15,"","deac");

								if($conf)
								{
									$text .= $frm -> form_option(MENLAN_16,"","config");
								}
                        	

                        if($menu_order != 1){
									$text .= $frm -> form_option(MENLAN_17,"","inc");
									$text .= $frm -> form_option(MENLAN_24,"","top");
                        }
                        if($menu_count != $menu_order){
									$text .= $frm -> form_option(MENLAN_18,"","dec");
									$text .= $frm -> form_option(MENLAN_23,"","bot");
                        }
                        for($c=1; $c<=$areas; $c++){
                                if($menu <> $c){
													$text .= $frm -> form_option(MENLAN_19." ".$c,"","move.".$c);

                                }
                        }

								$text .= $frm -> form_option(MENLAN_20,"","adv");
								$text .= $frm -> form_select_close();
								$text .= $frm -> form_close();
                        $text .= "</div>";

                        $ns -> tablerender($caption, $text);
                        echo "<br />";
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