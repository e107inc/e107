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
|     $Source: /cvs_backup/e107_0.7/e107_admin/includes/admin_combo.php,v $
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

function wad2($link, $title, $description, $perms, $icon = FALSE){
        global $tdt;
        $permicon = ($icon ? e_PLUGIN.$icon : e_IMAGE."generic/e107.gif");
        if(getperms($perms)){
             //  if($tdt == 0){$tmp1 = "<tr>";}
                if($tdt == 4){$tmp2 = "</tr><tr>";$tdt=-1;}
                $tdt++;
                $tmp = $tmp1."<td style='text-align:center; vertical-align:top; width:20%'><a href='".$link."'><img src='$permicon' alt='$description' style='border:0'/></a><br /><a href='".$link."'><b>".$title."</b></a><br />".$description."<br /><br /></td>\n\n".$tmp2;
        }
        return $tmp;
}

$newarray = asortbyindex($array_functions, 1);

$text = "<div style='text-align:center'>
<table style='".ADMIN_WIDTH."'>";

while(list($key, $funcinfo) = each($newarray)){
        $text .= wad($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5]);
}

if(!$tdc){ $text .= "</tr>"; }



        unset($tdc);

        $text .= "</tr>";


$text .= "</tr>
</table></div>";

$ns -> tablerender(ADLAN_47." ".ADMINNAME, $text);

$text3="";


if(getperms("Z")){ // Plugin Manager
        $text3 .= wad2(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "P0", e_PLUGIN.e_IMAGE."generic/plugin.png");
}

        if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        include(e_PLUGIN.$plugin_path."/plugin.php");
                        if($eplug_conffile){
                                $text3 .= wad2(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, $eplug_icon);
                        }
                }
        }

$text .= "</tr></table></div>";

if($text3){  // Only render if some kind of P access exists.
 $ns -> tablerender(ADLAN_CL_7, "<table><tr>".$text3."</tr></table>");
}

$admin_info = TRUE;

?>
