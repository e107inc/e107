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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/userlanguage_menu/userlanguage_menu.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:40 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if(USER == TRUE){


$handle=opendir(e_LANGUAGEDIR);
while ($file = readdir($handle)){
        if($file != "." && $file != ".." && $file != "/"){
                $lanlist[] = $file;
        }
}
closedir($handle);

$defaultlan = $pref['sitelanguage'];
$count = 0;

$totalct = $sql -> db_Select("user", "user_prefs", "user_prefs REGEXP('sitelanguage') ");

while ($row = $sql -> db_Fetch()){
        $up = unserialize($row['user_prefs']);
        $lancount[$up['sitelanguage']]++;
}

$defaultusers = $sql -> db_Count("user") - $totalct;
$lancount[$defaultlan] += $defaultusers;

$text = "
<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<select name='sitelanguage' class='tbox'>";
$counter = 0;

while($lanlist[$counter]){
        $text .= "<option value='".$lanlist[$counter]."' ";
        if(($lanlist[$counter] == USERLAN) || (USERLAN == FALSE && $lanlist[$counter] == $defaultlan)){
                $text .= "selected";
        }
        $text .= ">".($lanlist[$counter] == $defaultlan ? "[ ".$lanlist[$counter]." ]" : $lanlist[$counter])." (users: ".($lancount[$lanlist[$counter]] ? $lancount[$lanlist[$counter]] : "0").")</option>\n";
        $counter++;
}
$text .= "</select>
<br /><br />
<input class='button' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."' />
</form>
</div>";

$ns -> tablerender(UTHEME_MENU_L2, $text);
}
?>