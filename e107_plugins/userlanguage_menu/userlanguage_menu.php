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
|     $Revision: 1.4 $
|     $Date: 2004-12-13 13:20:46 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

    $handle=opendir(e_LANGUAGEDIR);
    while ($file = readdir($handle)){
        if($file != "." && $file != ".." && $file != "/" && $file != "CVS" ){
            $lanlist[] = $file;
        }
    }

    closedir($handle);

    $text = "
    <div style='text-align:center'>
    <form method='post' action='".e_SELF."'>
    <select name='sitelanguage' class='tbox'>";
    sort($lanlist);

    foreach($lanlist as $langval){
        $langname = $langval;
        $langval = ($langval == $pref['sitelanguage']) ? "" : $langval;
        $selected = ($langval == USERLAN) ? "selected='selected'" : "";
        $text .= "<option value='".$langval."' $selected>$langname</option>\n ";
    }

    $text .= "</select>
    <br /><br />
    <input class='button' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."' />
    </form>
    </div>";

$ns -> tablerender(UTHEME_MENU_L2, $text, 'user_lan');

?>
