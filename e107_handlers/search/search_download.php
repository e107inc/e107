<?php
// search module for User.

if($results = $sql -> db_Select("download", "download_id, download_category, download_name, download_author, download_description, download_author_website", "download_name REGEXP('".$query."') OR download_author REGEXP('".$query."') OR download_description  REGEXP('".$query."') OR download_author_website REGEXP('".$query."') ")){
        while(list($download_id, $download_category, $download_name, $download_author, $download_description, $download_author_website) = $sql -> db_Fetch()){

                $download_name = parsesearch($download_name, $query);

                $download_author = parsesearch($download_author, $query);

                $download_description = parsesearch($download_description , $query);

                $download_author_website = parsesearch($download_author_website, $query);


                $text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"download.php?view.".$download_id."\">".$download_name."</a><br />$download_description<br /><br />";

        }
}else{
        $text .= LAN_198;
}
?>