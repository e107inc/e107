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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/emote.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:26 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
function r_emote(){
        $sql = new db;
        $sql -> db_Select("core", "*", "e107_name='emote'");
        $row = $sql -> db_Fetch(); extract($row);
        $emote = unserialize($e107_value);

        $str = "<div class='spacer'>";

        $c=0;
        while(list($code, $name) = @each($emote[$c])){
                if(!$orig[$name]){
                        $str .= "<a href=\"javascript:addtext(' $code')\"><img src=\"".e_IMAGE."emoticons/$name\" style=\"border:0\" alt=\"\" /></a> \n";
                        $orig[$name] = TRUE;
                }
                $c++;
        }

        $str .= "</div>";
        return $str;
}
?>