<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /request.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
if(!e_QUERY){
        header("location: ".e_BASE."index.php");
        exit;
}

if(!is_numeric(e_QUERY)){
        if($sql -> db_Select("download", "*", "download_url='".e_QUERY."'", TRUE)){
                $row = $sql -> db_Fetch(); extract($row);
                $type = "file";
                $id = $download_id;
    //    }else if(file_exists(e_FILE."downloads/".e_QUERY)){
    }else if(file_exists($DOWNLOADS_DIRECTORY.e_QUERY)){

             //   header("location:".e_FILE."downloads/".e_QUERY);
                send_file(e_QUERY);
                exit;
        }else if(strstr(e_QUERY, "http") || strstr(e_QUERY, "ftp")){
              //  header("location:".e_QUERY);
                 send_file(e_QUERY);
                exit;
        }
}else{

        $tmp = explode(".", e_QUERY);
        if(!$tmp[1]){
                $id = $tmp[0];
                $type = "file";
        }else{
                $table = $tmp[0];
                $id = $tmp[1];
                $type = "image";
        }
}

if($type == "file"){
        if($sql -> db_Select("download", "*", "download_id= '$id' ")){
                $row = $sql -> db_Fetch(); extract($row);
                $sql -> db_Select("download_category", "*", "download_category_id=$download_category");
                $row = $sql -> db_Fetch(); extract($row);
                if(check_class($download_category_class)){
                        $sql -> db_Update ("download", "download_requested=download_requested+1 WHERE download_id='$id' ");
                        if(preg_match("/Binary\s(.*?)\/.*/", $download_url, $result)){
                                $bid = $result[1];
                                $result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id='$bid' ");
                                $binary_data = @mysql_result($result, 0, "binary_data");
                                $binary_filetype = @mysql_result($result, 0, "binary_filetype");
                                $binary_name = @mysql_result($result, 0, "binary_name");
                                header("Content-type: ".$binary_filetype);
                                header("Content-length: ".$download_filesize);
                                header("Content-Disposition: attachment; filename=".$binary_name);
                                header("Content-Description: PHP Generated Data");
                                echo $binary_data;
                                exit;
                        }
                        if(strstr($download_url, "http") || strstr($download_url, "ftp")){
                            //    header("location:".$download_url);
                                send_file($download_url);
                                exit;
                        }else{
                          //      if(file_exists(e_FILE."downloads/".$download_url)){
                                if(file_exists($DOWNLOADS_DIRECTORY.$download_url)){
                                  //      header("location:".e_FILE."downloads/".$download_url);
                                          send_file($download_url);
                                        exit;



                                }else if(file_exists(e_FILE."public/".$download_url)){
                                        header("location:".e_FILE."public/".$download_url);
                                        exit;
                                }
                        }
                }else{
                        echo "<br /><br /><div style='text-align:center; font: 12px Verdana, Tahoma'>You do not have the correct permissions to download this file.</div>";
                        exit;
                }
        }
}

$sql -> db_Select($table, "*", $table."_id= '$id' ");
$row = $sql -> db_Fetch(); extract($row);

$image = ($table == "upload" ? $upload_ss : $download_image);

if(preg_match("/Binary\s(.*?)\/.*/", $image, $result)){
        $bid = $result[1];
        $result = @mysql_query("SELECT * FROM ".MPREFIX."rbinary WHERE binary_id='$bid' ");
        $binary_data = @mysql_result($result, 0, "binary_data");
    $binary_filetype = @mysql_result($result, 0, "binary_filetype");
        $binary_name = @mysql_result($result, 0, "binary_name");
        header("Content-type: ".$binary_filetype);
        header("Content-Disposition: inline; filename=\"$binary_name\"");
        echo $binary_data;
        exit;

}


$image = ($table == "upload" ? $upload_ss : $download_image);

if(eregi("http", $image)){
        header("location:".$image);
        exit;
}else{
        if($table == "download"){
                require_once(HEADERF);
                if(file_exists(e_FILE."download/".$image)){
                        echo "<img src='".e_FILE."download/".$image."' alt='' />";
                }else if(file_exists(e_FILE."downloadimages/".$image)){
                        echo "<img src='".e_FILE."downloadimages/".$image."' alt='' />";
                }else{
                        echo "<img src='".e_FILE."public/".$image."' alt='' />";
                }
                echo "<br /><a href='javascript:history.back(1)'>Back</a>";
                require_once(FOOTERF);
        }else{
                echo "<img src='".e_FILE."public/".$image."' alt='' />";
                exit;
        }
}



// File retrieval function. by Cam.

function send_file($file){
      global $DOWNLOADS_DIRECTORY;
      $fullpath = $DOWNLOADS_DIRECTORY . $file;
      $file = basename($file);
        if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")){
        $file = preg_replace('/\./', '%2e', $file,substr_count($file, '.') - 1);
        }

      @set_time_limit(600);
      if(file_exists($fullpath)){
       header("Cache-control: private");
       header('Pragma: no-cache');
       header("Content-Type: application/force-download");
       header("Content-Disposition:$disposition; filename=\"".trim(htmlentities($file))."\"");
       header("Content-Description: ".trim(htmlentities($file)));
       header("Content-length:".(string)(filesize($fullpath)));

       $fd=fopen($fullpath,'rb');
       while(!feof($fd)) {
         print fread($fd, 4096);
       }
       fclose($fd);
      }else{
      header("location: ".e_BASE."index.php");
      }
}
?>