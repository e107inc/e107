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
|     $Source: /cvs_backup/e107_0.7/e107_admin/includes/admin_default.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-05 16:57:39 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$td = 1;
function wad($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE){
	global $td;
	$permicon = $mode ? SML_IMG_PLUGIN : $icon;
	if (getperms($perms)){
		if ($td==1) { $text .= "<tr>"; }
		$text = "<td class='td' style='text-align:left; vertical-align:top; width:20%; white-space:nowrap' 
		onmouseover='tdover(this)' onmouseout='tdnorm(this)' onclick=\"document.location.href='$link'\">$permicon $title</td>";
		if ($td==5) { $text .= '</tr>'; }
		if ($td<5) { $td++; } else { $td = 1; }
	} 
	return $text;
}

$newarray = asortbyindex($array_functions, 1);

$text = "<div style='text-align:center'>
<table style='".ADMIN_WIDTH."'>";

while(list($key, $funcinfo) = each($newarray)){
        $text .= wad($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5]);
}

if(!$tdc){ $text .= "</tr>"; }



        unset($tdc);

        $text .= "</tr><tr>
        <td colspan='5'><br />
        </td>
        </tr>";

        $text .= wad(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", "", TRUE);

        if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        include(e_PLUGIN.$plugin_path."/plugin.php");
                        if($eplug_conffile){
                                $text .= wad(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, "", TRUE);
                        }
                        unset($eplug_conffile, $eplug_name, $eplug_caption);
                }
        }

$text .= "</tr>
</table></div>";

$ns -> tablerender(ADLAN_47." ".ADMINNAME, $text);

$admin_info = TRUE;

?>
