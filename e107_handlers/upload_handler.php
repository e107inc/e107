<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /classes/upload_class.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

@include(e_LANGUAGEDIR.e_LANGUAGE."/lan_upload_handler.php");
@include(e_LANGUAGEDIR."English/lan_upload_handler.php");
function file_upload($uploaddir, $avatar = FALSE){

        if(!$uploaddir) $uploaddir=e_FILE."public/";
        global $pref, $sql;

        $allowed_filetypes = ($pref['upload_allowedfiletype'] ? explode("\n", $pref['upload_allowedfiletype']) : array(".zip", ".gz", ".jpg", ".png", ".gif", ".txt"));

        $a=0;
        foreach($allowed_filetypes as $v) {
                $allowed_filetypes[$a] = trim(chop($v));
                $a++;
        }

        if($pref['upload_storagetype'] == "2" && $avatar == FALSE){
                extract($_FILES);
                for($c=0; $c<=1; $c++){
                        if($file_userfile['tmp_name'][$c]){
                                $fileext1 = substr(strrchr($file_userfile['name'][$c], "."), 1);
                                $fileext2 = substr(strrchr($file_userfile['name'][$c], "."), 0); // in case user has left off the . in allowed_filetypes
                                if(!in_array($fileext1, $allowed_filetypes) && !in_array(strtolower($fileext1), $allowed_filetypes) && !in_array(strtolower($file_userfile['type'][$c]), $allowed_filetypes)){
                                        if(!in_array($fileext2, $allowed_filetypes) && !in_array(strtolower($fileext2), $allowed_filetypes) && !in_array(strtolower($file_userfile['type'][$c]), $allowed_filetypes)){
                                                require_once(e_HANDLER."message_handler.php");
                                                message_handler("MESSAGE", "".LANUPLOAD_1." '".$file_userfile['type'][$c]."' ".LANUPLOAD_2."");
                                                return FALSE;
                                                require_once(FOOTERF);
                                                exit;
                                        }
                                }
                                set_magic_quotes_runtime(0);
                                $data = mysql_escape_string(fread(fopen($file_userfile['tmp_name'][$c], "rb"), filesize($file_userfile['tmp_name'][$c])));
                                set_magic_quotes_runtime(get_magic_quotes_gpc());
                                $file_name = ereg_replace("[^a-z0-9._]", "", str_replace(" ", "_", str_replace("%20", "_", strtolower($file_userfile['name'][$c]))));
                                $sql -> db_Insert("rbinary", "0, '$file_name', '".$file_userfile['type'][$c]."', '$data' ");
                                $uploaded[$c]['name'] = "Binary ".mysql_insert_id()."/".$file_name;
                                $uploaded[$c]['type'] = $file_userfile['type'][$c];
                                $uploaded[$c]['size'] = $file_userfile['size'][$c];
                        }
                }
                return $uploaded;
        }
        /*
        if(ini_get('open_basedir') != ''){
                require_once(e_HANDLER."message_handler.php");
                message_handler("MESSAGE", "'open_basedir' restriction is in effect, unable to move uploaded file, deleting ...", __LINE__, __FILE__);
                return FALSE;
        }
        */

        $files = $_FILES['file_userfile'];
        if(!is_array($files)){ return FALSE; }
        $c=0;
        foreach($files['name'] as $key => $name){

                if($files['size'][$key]){
                        $filesize[] = $files['size'][$key];
                        $name = ereg_replace("[^a-z0-9._]", "", str_replace(" ", "_", str_replace("%20", "_", strtolower($name))));
                        if($avatar == "attachment"){$name = USERID."_".$name;}
                        $destination_file  = getcwd()."/".$uploaddir."/".$name;
                        $uploadfile = $files['tmp_name'][$key];
                        $fileext1 = substr(strrchr($files['name'][$key], "."), 1);
                        $fileext2 = substr(strrchr($files['name'][$key], "."), 0);
                        if(!in_array($fileext1, $allowed_filetypes) && !in_array(strtolower($fileext1), $allowed_filetypes) && !in_array(strtolower($files['type'][$c]), $allowed_filetypes)){
                                if(!in_array($fileext2, $allowed_filetypes) && !in_array(strtolower($fileext2), $allowed_filetypes) && !in_array(strtolower($files['type'][$c]), $allowed_filetypes)){
                                        require_once(e_HANDLER."message_handler.php");
                                        message_handler("MESSAGE", "".LANUPLOAD_1." ".$files['type'][$key]." ".LANUPLOAD_2.".", __LINE__, __FILE__);
                                        return FALSE;
                                        require_once(FOOTERF);
                                        exit;
                                }
                        }

                        $uploaded[$c]['name'] = $name;
                        $uploaded[$c]['type'] = $files['type'][$key];
                        $uploaded[$c]['size'] = $files['size'][$key];

                        $method = (OPEN_BASEDIR == FALSE ? "copy" : "move_uploaded_file");

                        if(@$method($uploadfile, $destination_file)){
                                @chmod($destination_file, 0644);
										$fext  = array_pop(explode('.', $name));
										$fname = basename($name, '.'.$fext);
                                $tmp = pathinfo($name);
                                $rename = substr($fname, 0, 15).".".time().".".$fext;
                                if(@rename(e_FILE."public/avatars/".$name, e_FILE."public/avatars/".$rename)){
                                        $uploaded[$c]['name'] = $rename;
                                }

                                if($method == "copy"){
                                        @unlink($uploadfile);
                                }

                                require_once(e_HANDLER."message_handler.php");
                                message_handler("MESSAGE", "".LANUPLOAD_3." '".$files['name'][$key]."'", __LINE__, __FILE__);
                                $message .= "".LANUPLOAD_3." '".$files['name'][$key]."'.<br />";
                                $uploaded[$c]['size'] = $files['size'][$key];
                        }else{
                                switch ($files['error'][$key]){
                                        case 0: $error = LANUPLOAD_4; break;
                                        case 1: $error = LANUPLOAD_5; break;
                                        case 2: $error = LANUPLOAD_6; break;
                                        case 3: $error = LANUPLOAD_7; break;
                                        case 4: $error = LANUPLOAD_8; break;
                                        case 5: $error = LANUPLOAD_9; break;
                                }
                                require_once(e_HANDLER."message_handler.php");
                                message_handler("MESSAGE", "The file did not upload. Filename: '".$files['name'][$key]."' - Error: ".$error, __LINE__, __FILE__);
                                return FALSE;
                        }
                }
                $c++;
        }
        return $uploaded;
}
?>