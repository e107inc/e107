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
|     $Source: /cvs_backup/e107_0.7/e107_admin/adminb.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-10-10 21:13:18 $
|     $Author: loloirie $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
require_once("auth.php");

// auto db update ...
if("0" == ADMINPERMS){
        require_once(e_ADMIN."update_routines.php");
        update_check();
}
//        end auto db update

$tdc=0;

function wad($link, $title, $description, $perms, $icon = FALSE){
        global $tdc;
        $permicon = ($icon ? e_PLUGIN.$icon : e_IMAGE."generic/hme.png");
        if(getperms($perms)){
                $tmp = "<tr>\n<td class='forumheader3' style='text-align:left; vertical-align:top; width:100%'
                onmouseover='tdover(this)' onmouseout='tdnorm(this)'
                onclick=\"document.location.href='$link'\">
                <img src='$permicon' alt='$description' style='border:0; vertical-align:middle'/> <b>".$title."</b> ".($description ? "[ <span class='smalltext'>".$description."</span> ]" : "")."</td></tr>\n";
        }
        return $tmp;
}

$text = "<div style='text-align:center'>
<table style='width:95%'>";

require_once(e_ADMIN."ad_links.php");

$newarray = asortbyindex ($array_functions, 1);

$text = "<div style='text-align:center'>
<table style='width:95%'>";

while(list($key, $funcinfo) = each($newarray)){
        $text .= wad($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3]);
}

if(!$tdc){ $text .= "</tr>"; }


        $text .= "<tr>
        <td colspan='5'>
                <br />
        <div class='border'><div class='caption'>Plugins</div></div></div>
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
$ns -> tablerender("<div style='text-align:center'>".ADLAN_47." ".ADMINNAME."</div>", $text);
require_once("footer.php");

// Multi indice array sort by sweetland@whoadammit.com
function asortbyindex ($sortarray, $index) {
        $lastindex = count ($sortarray) - 1;
        for ($subindex = 0; $subindex < $lastindex; $subindex++) {
                $lastiteration = $lastindex - $subindex;
                for ($iteration = 0; $iteration < $lastiteration;    $iteration++) {
                        $nextchar = 0;
                        if (comesafter ($sortarray[$iteration][$index], $sortarray[$iteration + 1][$index])) {
                                $temp = $sortarray[$iteration];
                                $sortarray[$iteration] = $sortarray[$iteration + 1];
                                $sortarray[$iteration + 1] = $temp;
                        }
                }
        }
        return ($sortarray);
}

function comesafter ($s1, $s2) {
        $order = 1;
        if (strlen ($s1) > strlen ($s2)) {
                $temp = $s1;
                $s1 = $s2;
                $s2 = $temp;
                $order = 0;
        }
        for ($index = 0; $index < strlen ($s1); $index++) {
                if ($s1[$index] > $s2[$index]) return ($order);
                if ($s1[$index] < $s2[$index]) return (1 - $order);
        }
        return ($order);
}


function headerjs(){
$script = "<script type=\"text/javascript\">
function tdover(object) {
  if (object.className == 'forumheader3') object.className = 'forumheader4';
}

function tdnorm(object) {
  if (object.className == 'forumheader4') object.className = 'forumheader3';
}
</script>\n";

return $script;

}

?>
