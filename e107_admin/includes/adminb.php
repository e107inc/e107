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
|     $Source: /cvs_backup/e107_0.7/e107_admin/includes/adminb.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-05 16:57:40 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

$tdc=0;
function wad($link, $title, $description, $perms, $icon = FALSE, $mode = FALSE){
	$permicon = $mode ? "<img src='".e_PLUGIN.$icon."' alt='$description' style='border:0; vertical-align:middle'/>" : $icon;
	if (getperms($perms)){
		$text = "<tr>\n<td class='forumheader3' style='text-align:left; vertical-align:top; width:100%'
                onmouseover='tdover(this)' onmouseout='tdnorm(this)'
                onclick=\"document.location.href='$link'\">".$permicon."
			<b>".$title."</b> ".($description ? "[ <span class='smalltext'>".$description."</span> ]" : "")."</td></tr>\n";
	} 
	return $text;
}

$newarray = asortbyindex ($array_functions, 1);

$text = "<div style='text-align:center'>
<table style='".ADMIN_WIDTH."'>";

while(list($key, $funcinfo) = each($newarray)){
        $text .= wad($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[5]);
}

if(!$tdc){ $text .= "</tr>"; }


        $text .= "<tr>
        <td colspan='5'>
                <br />
        <div class='border'><div class='caption'>Plugins</div></div></div>
        </td>
        <tr>";

        $text .= wad(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", e_PLUGIN.e_IMAGE."generic/plugin.png", TRUE);

        if($sql -> db_Select("plugin", "*", "plugin_installflag=1")){
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        include(e_PLUGIN.$plugin_path."/plugin.php");
                        if($eplug_conffile){
                                $text .= wad(e_PLUGIN.$plugin_path."/".$eplug_conffile, $eplug_name, $eplug_caption, "P".$plugin_id, $eplug_icon, TRUE);
                }
        }
}

$text .= "</tr>
</table></div>";

$ns -> tablerender(ADLAN_47." ".ADMINNAME, $text);

$admin_info = FALSE;

?>