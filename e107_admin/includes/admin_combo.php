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
|     $Revision: 1.3 $
|     $Date: 2005-01-10 00:34:20 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$text = "<div style='text-align:center'>
<table style='".ADMIN_WIDTH."'>";

while(list($key, $funcinfo) = each($newarray)){
        $text .= render_links($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5], 'default');
}

while ($td<=5) {
	$text_cat .= "<td class='td' style='width:20%;' ></td>";
	$td++;
}
$td = 1;


if(!$tdc){ $text .= "</tr>"; }



unset($tdc);

$text .= "</tr>";


$text .= "</tr>
</table></div>";

$ns -> tablerender(ADLAN_47." ".ADMINNAME, $text);

$text3="";

if(getperms("Z")){ // Plugin Manager
        $text3 .= render_links(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "P0", E_32_PLUGMANAGER, 'classis');
}

        if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        unset($eplug_icon);
                        include(e_PLUGIN.$plugin_path."/plugin.php");
                        if($eplug_conffile){
				          $plugin_icon = $eplug_icon ? "<img src='".e_PLUGIN.$eplug_icon."' alt='".$eplug_caption."' style='border:0px; width: 32px; height: 32px' />" : E_32_CAT_PLUG;
						$text3 .= render_links(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, $plugin_icon, 'classis');
                        }
                        unset($eplug_conffile, $eplug_name, $eplug_caption, $eplug_icon);
                }
        }

$text .= "</tr></table></div>";

if($text3){  // Only render if some kind of P access exists.
 $ns -> tablerender(ADLAN_CL_7, "<table><tr>".$text3."</tr></table>");
}

admin_info();

?>
