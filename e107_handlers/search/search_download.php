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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/search/search_download.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-12-03 21:16:14 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
$c = 0;
if($results = $sql -> db_Select("download", "download_id, download_category, download_name, download_author, download_description, download_author_website", "(download_name REGEXP('".$query."') OR download_author REGEXP('".$query."') OR download_description  REGEXP('".$query."') OR download_author_website REGEXP('".$query."')) AND download_active='1'  ")){
        while(list($download_id, $download_category, $download_name, $download_author, $download_description, $download_author_website) = $sql -> db_Fetch()){

                $download_name = parsesearch($download_name, $query);

                $download_author = parsesearch($download_author, $query);

                $download_description = parsesearch($download_description , $query);

                $download_author_website = parsesearch($download_author_website, $query);


                                $action = "download.php?view.".$download_id."";
                                $text .= "<form method='post' action='$action' id='download_".$c."'>
                                <input type='hidden' name='highlight_search' value='1' /><input type='hidden' name='search_query' value='$query' /><img src='".THEME."images/bullet2.gif' alt='bullet' /> <b><a href='javascript:document.getElementById(\"download_".$c."\").submit()'>$download_name</a></b></form><br />$download_description<br /><br />";
                                $c ++;

        }
}else{
        $text .= LAN_198;
}
?>