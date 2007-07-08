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
|
|   $Source: /cvs_backup/e107_0.8/e107_handlers/upload_handler.php,v $
|   $Revision: 1.5 $
|   $Date: 2007-07-08 20:58:24 $
|   $Author: e107steved $
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_upload_handler.php");
@include_once(e_LANGUAGEDIR."English/lan_upload_handler.php");
function file_upload($uploaddir, $avatar = FALSE, $fileinfo = "", $overwrite = "")
{

	global $pref, $sql, $tp;

	if (!$uploaddir) {$uploaddir = e_FILE."public/";}
	if($uploaddir == e_THEME) {$pref['upload_storagetype'] = 1;}

	$allowed_filetypes = get_allowed_filetypes();

	if ($pref['upload_storagetype'] == "2" && $avatar == FALSE)
	{
		extract($_FILES);
		for($c = 0; $c <= 1; $c++)
		{
			if ($file_userfile['tmp_name'][$c])
			{
			  if (($file_status = vet_file($file_userfile['tmp_name'][$c], $file_userfile['name'][$c], $allowed_filetypes)) !== TRUE)
			  {
				require_once(e_HANDLER."message_handler.php");
				message_handler("MESSAGE", "".LANUPLOAD_1." '".$file_userfile['type'][$c]."' ".LANUPLOAD_2." ({$file_status})");
				return FALSE;
				require_once(FOOTERF);
				exit;
			  }
				set_magic_quotes_runtime(0);
				$data = mysql_escape_string(fread(fopen($file_userfile['tmp_name'][$c], "rb"), filesize($file_userfile['tmp_name'][$c])));
				set_magic_quotes_runtime(get_magic_quotes_gpc());
				$file_name = preg_replace("/[^a-z0-9._]/", "", str_replace(" ", "_", str_replace("%20", "_", strtolower($file_userfile['name'][$c]))));
				$sql->db_Insert("rbinary", "0, '".$tp -> toDB($file_name, true)."', '".$tp -> toDB($file_userfile['type'][$c], true)."', '$data' ");
				$uploaded[$c]['name'] = "Binary ".mysql_insert_id()."/".$file_name;
				$uploaded[$c]['type'] = $file_userfile['type'][$c];
				$uploaded[$c]['size'] = $file_userfile['size'][$c];
			}
		}
		return $uploaded;
	}
	/*
	if (ini_get('open_basedir') != ''){
	require_once(e_HANDLER."message_handler.php");
	message_handler("MESSAGE", "'open_basedir' restriction is in effect, unable to move uploaded file, deleting ...", __LINE__, __FILE__);
	return FALSE;
	}
	*/

	//	echo "<pre>"; print_r($_FILES); echo "</pre>"; exit;

	$files = $_FILES['file_userfile'];
	if (!is_array($files))
	{
		return FALSE;
	}

	$c = 0;
	foreach($files['name'] as $key => $name)
	{

		if ($files['size'][$key])
		{
			$filesize[] = $files['size'][$key];
			$name = preg_replace("/[^a-z0-9._-]/", "", str_replace(" ", "_", str_replace("%20", "_", strtolower($name))));
			$raw_name = $name;			// Save 'proper' file name - useful for display
			if ($avatar == "attachment") {
				$name = time()."_".USERID."_".$fileinfo.$name;
			}

			$destination_file = getcwd()."/".$uploaddir."/".$name;
			if ($avatar == "unique" && file_exists($destination_file))
			{
				$name = time()."_".$name;
				$destination_file = getcwd()."/".$uploaddir."/".$name;
			}
			if (file_exists($destination_file) && !$overwrite)
			{
				require_once(e_HANDLER."message_handler.php");
				message_handler("MESSAGE", LANUPLOAD_10, __LINE__, __FILE__); // duplicate file
				$f_message .= LANUPLOAD_10 . __LINE__ .  __FILE__;
				$dupe_found = TRUE;
			}
			else
			{
			  $uploadfile = $files['tmp_name'][$key];
			  if (($file_status = vet_file($uploadfile, $name, $allowed_filetypes)) !== TRUE)
			  {
				require_once(e_HANDLER."message_handler.php");
				message_handler("MESSAGE", LANUPLOAD_1." ".$files['type'][$key]." ".LANUPLOAD_2.". ({$file_status})", __LINE__, __FILE__);
				$f_message .= LANUPLOAD_1." ".$files['type'][$key]." ".LANUPLOAD_2."." . __LINE__ .  __FILE__;
				return FALSE;
				require_once(FOOTERF);
				exit;
			  }

				$uploaded[$c]['name'] = $name;
				$uploaded[$c]['rawname'] = $raw_name;
				$uploaded[$c]['type'] = $files['type'][$key];
				$uploaded[$c]['size'] = 0;

				$method = (OPEN_BASEDIR == FALSE ? "copy" : "move_uploaded_file");

				if (@$method($uploadfile, $destination_file))
				{
					@chmod($destination_file, 0644);
					$_tmp = explode('.', $name);
					$fext = array_pop($_tmp);
					$fname = basename($name, '.'.$fext);
					$tmp = pathinfo($name);
					$rename = substr($fname, 0, 15).".".time().".".$fext;
					if (@rename(e_FILE."public/avatars/".$name, e_FILE."public/avatars/".$rename))
					{
						$uploaded[$c]['name'] = $rename;
					}

					if ($method == "copy")
					{
						@unlink($uploadfile);
					}

					if(!$dupe_found)
					{   // don't display 'success message' when duplicate file found.
						require_once(e_HANDLER."message_handler.php");
						message_handler("MESSAGE", "".LANUPLOAD_3." '".$files['name'][$key]."'", __LINE__, __FILE__);
						$f_message .= "".LANUPLOAD_3." '".$files['name'][$key]."'.<br />";
					}
					$uploaded[$c]['size'] = $files['size'][$key];

				}
				else
				{
					$uploaded[$c]['error'] = $files['error'][$key];
					switch ($files['error'][$key])
					{
						case 0:
						$error = LANUPLOAD_4." [".str_replace("../", "", $uploaddir)."]";
						break;
						case 1:
						$error = LANUPLOAD_5;
						break;
						case 2:
						$error = LANUPLOAD_6;
						break;
						case 3:
						$error = LANUPLOAD_7;
						break;
						case 4:
						$error = LANUPLOAD_8;
						break;
						case 5:
						$error = LANUPLOAD_9;
						break;
					}
					require_once(e_HANDLER."message_handler.php");
					message_handler("MESSAGE", LANUPLOAD_11." '".$files['name'][$key]."' <br />".LANUPLOAD_12.": ".$error, __LINE__, __FILE__);
					$f_message .= LANUPLOAD_11." '".$files['name'][$key]."' <br />".LANUPLOAD_12.": ".$error . __LINE__ . __FILE__;

				}
			}
		}
		$c++;
	}
	define("F_MESSAGE", "<br />".$f_message);

	return $uploaded;
}


// Check uploaded file to try and identify dodgy content.
// Return TRUE if appears OK.
// Return a numeric reason code 1..9 if unacceptable

// $filename is the full path+name to the uploaded file on the server
// $target_name is the intended name of the file once transferred
// $allowed_filetypes is an array of permitted file extensions, in lower case, no leading '.'
//		(usually generated from filetypes.php)
// if $unknown is FALSE, rejects totally unknown file extensions (even if in $allowed_filetypes).
// if $unknown is TRUE, accepts totally unknown file extensions.
// otherwise $unknown is a comma-separated list of additional permitted file extensions
function vet_file($filename, $target_name, $allowed_filetypes = '', $unknown = FALSE)
{
// 1. Start by checking against filetypes - that's the easy one!
  $file_ext = strtolower(substr(strrchr($target_name, "."), 1));
  if (!in_array($file_ext, $allowed_filetypes)) return 1;


// 2. For all files, read the first little bit to check for any flags etc
  $res = fopen($filename, 'rb');
  $tstr = fread($res,100);
  fclose($res);
  if ($tstr === FALSE) return 2;			// If can't read file, not much use carrying on!
  if (stristr($tstr,'<?php') !== FALSE) return 3;		// Pretty certain exploit
  if (stristr($tstr,'<?') !== FALSE) return 7;			// Possible exploit - maybe allowable?
  
  
// 3. Now do what we can based on file extension
  switch ($file_ext)
  {
    case 'jpg' :
	case 'gif' :
	case 'png' :
	case 'jpeg' :
	case 'pjpeg' :
	case 'bmp' :
	  $ret = getimagesize($filename);
	  if (!is_array($ret)) return 4;		// getimagesize didn't like something
	  if (($ret[0] == 0) || ($ret[1] == 0)) return 5;		// Zero size picture or bad file format
	  break;
	  
	case 'zip' :
	case 'gzip' :
	case 'gz' :
	case 'tar' :
	case 'bzip' :
	case 'pdf' :
	case 'rar' :
	case '7z' :
	  break;			// Just accept these

	case 'php' :
	case 'htm' :
	case 'html' :
	  return 9;			// Never accept these! Whatever the user thinks!
	  
	default :
	  if (is_bool($unknown)) return ($unknown ? TRUE : 8);
	  $tmp = explode(',', $unknown);
	  for ($i = 0; $i < count($tmp); $i++) { $tmp[$i] = strtolower(trim(str_replace('.', '', $tmp[$i])));  }
	  if (!in_array($file_ext, $tmp)) return 6;
  }
  return TRUE;			// Accepted here
}



function get_allowed_filetypes($def_file = 'filetypes.php')
{
  $ret = array();
  
  if (is_readable(e_ADMIN.$def_file)) 
  {
	$a_filetypes = trim(file_get_contents(e_ADMIN.$def_file));
	$a_filetypes = explode(',', $a_filetypes);
	foreach ($a_filetypes as $ftype) 
	{
	  $ret[] = strtolower(trim(str_replace('.', '', $ftype)));
	}
  }
  return $ret;
}

?>