<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/classes/upload_class.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

function file_upload($uploaddir="files/public/", $avatar = FALSE){
	global $pref, $sql;

	$allowed_filetypes = ($pref['upload_allowedfiletype'][1] ? explode("\n", $pref['upload_allowedfiletype'][1]) : array(".zip", ".gz", ".jpg", ".png", ".gif", ".txt"));

	for($a=0; $a<=5; $a++){
		$allowed_filetypes[$a] = trim(chop($allowed_filetypes[$a]));
	}

	if($pref['upload_storagetype'][1] == "2" && $avatar == FALSE){
		extract($_FILES);
		for($c=0; $c<=1; $c++){
			if($file_userfile['tmp_name'][$c]){

				$fileext1 = substr(strrchr($file_userfile['name'][$c], "."), 1);
				$fileext2 = substr(strrchr($file_userfile['name'][$c], "."), 0); // in case user has left off the . in allowed_filetypes

				if(!in_array($fileext1, $allowed_filetypes)){
					if(!in_array($fileext2, $allowed_filetypes)){
						require_once(e_BASE."classes/message_handler.php");
						message_handler("MESSAGE", "The filetype '".$file_userfile['type'][$c]."' is not allowed and has been deleted.");
						return FALSE;
						require_once(FOOTERF);
						exit;
					}
				}
				set_magic_quotes_runtime(0);
				$data = mysql_escape_string(fread(fopen($file_userfile['tmp_name'][$c], "rb"), filesize($file_userfile['tmp_name'][$c])));
				set_magic_quotes_runtime(get_magic_quotes_gpc());
				$file_name = ereg_replace("[^a-z0-9._]", "", str_replace(" ", "_", str_replace("%20", "_", strtolower($file_userfile['name'][$c]))));
				$sql -> db_Insert("binary", "0, '$file_name', '".$file_userfile['type'][$c]."', '$data' ");
				$uploaded[$c]['name'] = "Binary ".mysql_insert_id()."/".$file_name;
				$uploaded[$c]['type'] = $file_userfile['type'][$c];
				$uploaded[$c]['size'] = $file_userfile['size'][$c];
			}
		}
		return $uploaded;
	}

	if(ini_get('open_basedir') != ''){
		require_once(e_BASE."classes/message_handler.php");
		message_handler("MESSAGE", "'open_basedir' restriction is in effect, unable to move uploaded file, deleting ...", __LINE__, __FILE__);
		return FALSE;
	}

	$files = $_FILES['file_userfile'];
	if(!is_array($files)){ return FALSE; }
	$c=0;
	foreach($files['name'] as $key => $name){

		if($files['size'][$key]){
			$filesize[] = $files['size'][$key];
			$name = ereg_replace("[^a-z0-9._]", "", str_replace(" ", "_", str_replace("%20", "_", strtolower($name))));
			$destination_file = $_SERVER['DOCUMENT_ROOT'].e_HTTP.$uploaddir.$name;

			$uploadfile = $files['tmp_name'][$key];

			$fileext1 = substr(strrchr($files['name'][$key], "."), 1);
			$fileext2 = substr(strrchr($files['name'][$key], "."), 0);
			
			if(!in_array($fileext1, $allowed_filetypes)){
				if(!in_array($fileext2, $allowed_filetypes)){
					require_once(e_BASE."classes/message_handler.php");
					message_handler("MESSAGE", "The filetype ".$files['type'][$key]." is not allowed and has been deleted.", __LINE__, __FILE__);
					return FALSE;
					require_once(FOOTERF);
					exit;
				}
			}

			$uploaded[$c]['name'] = $files['name'][$key];
			$uploaded[$c]['type'] = $files['type'][$key];
			$uploaded[$c]['size'] = $files['size'][$key];

			if(@move_uploaded_file($uploadfile, $destination_file)){
				@chmod($destination_file, 0644);
				require_once(e_BASE."classes/message_handler.php");
				message_handler("MESSAGE", "Successfully uploaded '".$files['name'][$key]."'", __LINE__, __FILE__);
				$message .= "Successfully uploaded '".$files['name'][$key]."'.<br />";
				$uploaded[$c]['size'] = $files['size'][$key];
			}else{
				switch ($files['error'][$key]){
					case 0: $error = "None specified ..."; break;
					case 1: $error = "The uploaded file exceeds the upload_max_filesize directive in php.ini."; break;
					case 2: $error = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form."; break;
					case 3: $error = "The uploaded file was only partially uploaded."; break;
					case 4: $error = "No file was uploaded."; break;
					case 5: $error = "Uploaded file size 0 bytes"; break;	
				}
				require_once(e_BASE."classes/message_handler.php");
				message_handler("MESSAGE", "The file did not upload. Filename: '".$files['name'][$key]."' - Error: ".$error, __LINE__, __FILE__);
				return FALSE;
			}	
		}
		$c++;
	}
	return $uploaded;
}
?>