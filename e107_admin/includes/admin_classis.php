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
|     $Source: /cvs_backup/e107_0.7/e107_admin/includes/admin_classis.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-05 16:57:39 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$tdc=0;

function wad($link, $title, $description, $perms, $icon = FALSE){
        global $tdc;
        $permicon = ($icon ? e_PLUGIN.$icon : e_IMAGE."generic/e107.gif");
        if(getperms($perms)){
                if(!$tdc){$tmp1 = "<tr>";}
                if($tdc == 4){$tmp2 = "</tr>";$tdc=-1;}
                $tdc++;
                $tmp = $tmp1."<td style='text-align:center; vertical-align:top; width:20%'><a href='".$link."'><img src='$permicon' alt='$description' style='border:0'/></a><br /><a href='".$link."'><b>".$title."</b></a><br />".$description."<br /><br /></td>\n\n".$tmp2;
        }
        return $tmp;
}

$newarray = asortbyindex ($array_functions, 1);

$text = "<div style='text-align:center'>
<table style='".ADMIN_WIDTH."'>";

while(list($key, $funcinfo) = each($newarray)){
        $text .= wad($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3]);
}

if(!$tdc){ $text .= "</tr>"; }


        $text .= "<tr>
        <td colspan='5'>
        <div class='border'><div class='caption'>Plugins</div></div></div>
        <br />
        </td>
        <tr>";

        $text .= wad(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", e_PLUGIN.e_IMAGE."generic/plugin.png");

        if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        include(e_PLUGIN.$plugin_path."/plugin.php");
                        if($eplug_conffile){
                                $text .= wad(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, $eplug_icon);
                }
        }
}
$text .= "</tr>
</table></div>";

$ns -> tablerender(ADLAN_47." ".ADMINNAME, $text);

$admin_info = FALSE;

?>