<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * File Upload Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/upload_handler.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */


/**
 *	File upload handler
 *
 *	@package    e107
 *	@subpackage	e107_handlers
 *	@version 	$Id$;
 *
 *	@todo - option to restrict by total size irrespective of number of uploads
 */

if (!defined('e107_INIT'))
{
	exit;
}

e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_upload_handler.php');

//define("UH_DEBUG",TRUE);


//FIXME need another name
// define('e_UPLOAD_TEMP_DIR', e_MEDIA.'temp/');
define('e_UPLOAD_TEMP_DIR', e_TEMP);	// Temporary directory - used if PHP's OPEN_BASEDIR active
define('e_READ_FILETYPES', 'filetypes.xml'); 	// Upload permissions
define('e_SAVE_FILETYPES', 'filetypes_.xml');

/**
 * File upload handler - this is the preferred interface for new code
 *
 *	Routine processes the array of uploaded files according to both specific options set by the caller, and
 *		system options configured by the main admin.
 *
 *	function process_uploaded_files($uploaddir, $fileinfo = FALSE, $options = array())
 *	Parameters:
 *		@param string $uploaddir - target directory (checked that it exists, but path not otherwise changed)
 *
 *		@param string $fileinfo - determines any special handling of file name (combines previous $fileinfo and $avatar parameters):
 *			FALSE - default option; no processing
 *			"attachment+extra_text" - indicates an attachment (related to forum post or PM), and specifies some optional text which is
 *				incorporated into the final file name (the original $fileinfo parameter).
 *			"prefix+extra_text" - indicates an attachment or file, and specifies some optional text which is prefixed to the file name
 *			"unique"
 *				- if the proposed destination file doesn't exist, saved under given name
 *				- if the proposed destination file does exist, prepends time() to the file name to make it unique
 *			'avatar'
 *				- indicates an avatar is being uploaded (not used - options must be set elsewhere)
 *
 *		@param array $options - an array of supplementary options, all of which will be given appropriate defaults if not defined:
 *			'filetypes' - name of file containing list of valid file types
 *				- Always looks in the admin directory
 *				- defaults to e_ADMIN.filetypes.xml, else e_ADMIN.admin_filetypes.php for admins (if file exists), otherwise e_ADMIN.filetypes.php for users.
 *				- FALSE disables this option (which implies that 'extra_file_types' is used)
 *			'file_mask' - comma-separated list of file types which if defined limits the allowed file types to those which are in both this list and the
 *				file specified by the 'filetypes' option. Enables restriction to, for example, image files.
 *			'extra_file_types' - if is FALSE or undefined, rejects totally unknown file extensions (even if in $options['filetypes'] file).
 *				if TRUE, accepts totally unknown file extensions which are in $options['filetypes'] file.
 *				otherwise specifies a comma-separated list of additional permitted file extensions
 *			'final_chmod' - chmod() to be applied to uploaded files (0644 default)  (This routine expects an integer value, so watch formatting/decoding - its normally
 *				specified in octal. Typically use intval($permissions,8) to convert)
 *			'max_upload_size' - maximum size of uploaded files in bytes, or as a string with a 'multiplier' letter (e.g. 16M) at the end.
 *				- otherwise uses $pref['upload_maxfilesize'] if set
 *				- overriding limit of the smaller of 'post_max_size' and 'upload_max_size' if set in php.ini
 *				(Note: other parts of E107 don't understand strings with a multiplier letter yet)
 *			'file_array_name' - the name of the 'input' array - defaults to file_userfile[] - otherwise as set.
 *			'max_file_count' - maximum number of files which can be uploaded - default is 'unlimited' if this is zero or not set.
 *			'overwrite' - if TRUE, existing file of the same name is overwritten; otherwise returns 'duplicate file' error (default FALSE)
 *			'save_to_db' - [obsolete] storage type - if set and TRUE, uploaded files were saved in the database (rather than as flat files)
 *
 *	@return boolean|array
 *		Returns FALSE if the upload directory doesn't exist, or various other errors occurred which restrict the amount of meaningful information.
 *		Returns an array, with one set of entries per uploaded file, regardless of whether saved or
 *		discarded (not all fields always present) - $c is array index:
 *		 	$uploaded[$c]['name'] - file name - as saved to disc
 *			$uploaded[$c]['rawname'] - original file name, prior to any addition of identifiers etc (useful for display purposes)
 *			$uploaded[$c]['type'] - mime type (if set - as sent by browser)
 *			$uploaded[$c]['size'] - size in bytes (should be zero if error)
 *			$uploaded[$c]['error'] - numeric error code (zero = OK)
 *			$uploaded[$c]['index'] - if upload successful, the index position from the file_userfile[] array - usually numeric, but may be alphanumeric if coded
 *			$uploaded[$c]['message'] - text of displayed message relating to file
 *			$uploaded[$c]['line'] - only if an error occurred, has line number (from __LINE__)
 *			$uploaded[$c]['file'] - only if an error occurred, has file name (from __FILE__)
 *
 *	On exit, uploaded files should all have been removed from the temporary directory.
 *	No messages displayed - its caller's responsibility to handle errors and display info to
 *	user (or can use handle_upload_messages() from this module)
 *
 *	Details of uploaded files are in $_FILES['file_userfile'] (or other array name as set) on entry.
 *	Elements passed (from PHP) relating to each file:
 *		['name']	- the original name
 *		['type']	- mime type (if provided - not checked by PHP)
 *		['size']	- file size in bytes
 *		['tmp_name'] - temporary file name on server
 *		['error']	- error code. 0 = 'good'. 1..4 main others, although up to 8 defined for later PHP versions
 *	Files stored in server's temporary directory, unless another set
 */
function process_uploaded_files($uploaddir, $fileinfo = FALSE, $options = NULL)
{
	$admin_log = e107::getAdminLog();

	$ul_temp_dir = '';
	if (ini_get('open_basedir') != '') // Need to move file to intermediate directory before we can read its contents to check it.
	{ 
		$ul_temp_dir = e_UPLOAD_TEMP_DIR;
	}
	
	if(E107_DEBUG_LEVEL > 0)
	{
		define("UH_DEBUG", true);	
	}
	else
	{
		define("UH_DEBUG", false);		
	}
	
	if (UH_DEBUG)
	{
		e107::getLog()->e_log_event(10, debug_backtrace(), "DEBUG", "Upload Handler test", "Process uploads to {$uploaddir}, fileinfo  ".$fileinfo, FALSE, LOG_TO_ROLLING);
	}
	
	//	$admin_log->e_log_event(10,__FILE__."|".__FUNCTION__."@".__LINE__,"DEBUG","Upload Handler test","Intermediate directory: {$ul_temp_dir} ",FALSE,LOG_TO_ROLLING);

	$overwrite = varset($options['overwrite'], FALSE);

	$uploaddir = realpath($uploaddir); // Mostly to get rid of the grot that might be passed in from legacy code. Also strips any trailing '/'
	
	if (!is_dir($uploaddir))
	{
		if (UH_DEBUG)
		{
			e107::getLog()->e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Invalid directory: ".$uploaddir, FALSE, FALSE);
		}
		
		return FALSE; // Need a valid directory
	}
	if (UH_DEBUG)
	{
		e107::getLog()->e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Destination directory: ".$uploaddir, FALSE, FALSE);
	}
	
	$final_chmod = varset($options['final_chmod'], 0644);

	if (isset($options['file_array_name']))
	{
		$files = $_FILES[$options['file_array_name']];
	}
	else
	{
		$files = $_FILES['file_userfile'];
	}

	$max_file_count = varset($options['max_file_count'], 0);

	if (!is_array($files))
	{
		if (UH_DEBUG)
		{
			e107::getLog()->e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "No files uploaded", FALSE, FALSE);
		}
		return FALSE;
	}

	$uploaded = array();

	$max_upload_size 	= calc_max_upload_size(varset($options['max_upload_size'], -1)); // Find overriding maximum upload size
	$allowed_filetypes 	= get_filetypes(varset($options['file_mask'], ''), varset($options['filetypes'], ''));
	$max_upload_size 	= set_max_size($allowed_filetypes, $max_upload_size);

	// That's the basics set up - we can start processing files now

	if (UH_DEBUG)
	{
		e107::getLog()->e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Start individual files: ".count($files['name'])." Max upload: ".$max_upload_size, FALSE, FALSE);
	}
	
	$c = 0;
	$tp = e107::getParser();
	$uploadfile = null;
	
	foreach ($files['name'] as $key=>$name)
	{
		$name = filter_var($name, FILTER_SANITIZE_STRING);

		$first_error = FALSE; // Clear error flag
		if (($name != '') || $files['size'][$key]) // Need this check for things like file manager which allow multiple possible uploads
		{
			$origname = $name; 
			//$name = preg_replace("/[^a-z0-9._-]/", '', str_replace(' ', '_', str_replace('%20', '_', strtolower($name))));
			// FIX handle non-latin file names
			$name = preg_replace("/[^\w\pL.-]/u", '', str_replace(' ', '_', str_replace('%20', '_', $tp->ustrtolower($name))));
			$raw_name = $name; // Save 'proper' file name - useful for display
			$file_ext = trim(strtolower(substr(strrchr($name, "."), 1))); 	// File extension - forced to lower case internally

			if (!trim($files['type'][$key]))
				$files['type'][$key] = 'Unknowm mime-type';

			if (UH_DEBUG)
			{
				e107::getLog()->e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Process file {$name}, size ".$files['size'][$key], FALSE, FALSE);
			}
			
			if ($max_file_count && ($c >= $max_file_count))
			{
				$first_error = 249; // 'Too many files uploaded' error
			}
			else
			{
				$first_error = $files['error'][$key]; // Start with whatever error PHP gives us for the file
			}

			if (!$first_error)
			{ // Check file size early on
				if ($files['size'][$key] == 0)
				{
					$first_error = 4; // Standard error code for zero size file
				}
				elseif ($files['size'][$key] > $max_upload_size)
				{
					$first_error = 254;
				}
				elseif (isset($allowed_filetypes[$file_ext]) && ($allowed_filetypes[$file_ext] > 0) && ($files['size'][$key] > $allowed_filetypes[$file_ext]))
				{ // XML file set limits per extension
					$first_error = 254;
				}
			}

			if (!$first_error)
			{
				$uploadfile = $files['tmp_name'][$key]; // Name in temporary directory
				if (!$uploadfile)
					$first_error = 253;
			}

			if (!$first_error)
			{
				// Need to support multiple files with the same 'real' name in some cases
				if (strpos($fileinfo, "attachment") === 0)
				{ // For attachments, add in a prefix plus time and date to give a unique file name
					$addbit = explode('+', $fileinfo, 2);
					$name = time()."_".USERID."_".trim($addbit[1]).$name;
				}
				elseif (strpos($fileinfo, "prefix") === 0)
				{ // For attachments, avatars, photos etc alternatively just add a prefix we've been passed
					$addbit = explode('+', $fileinfo, 2);
					$name = trim($addbit[1]).$name;
				}

				$destination_file = $uploaddir."/".$name;

				if ($fileinfo == "unique" && file_exists($destination_file))
				{ // Modify destination name to make it unique - but only if target file name exists
					$name = time()."_".$name;
					$destination_file = $uploaddir."/".$name;
				}

				if (file_exists($destination_file) && !$overwrite)
					$first_error = 250; // Invent our own error number - duplicate file
			}

			if (!$first_error)
			{
				$tpos = FALSE;
				if ($file_ext != '') // Require any uploaded file to have an extension
				{
					if ($ul_temp_dir)
					{ // Need to move file to our own temporary directory
						$tempfilename = $uploadfile;
						$uploadfile = $ul_temp_dir.basename($uploadfile);
						
						if (UH_DEBUG)
						{
							e107::getLog()->e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Move {$tempfilename} to {$uploadfile} ", FALSE, LOG_TO_ROLLING);
						}
						
						@move_uploaded_file($tempfilename, $uploadfile); // This should work on all hosts
					}
					
					$tpos = (($file_status = vet_file($uploadfile, $name, $allowed_filetypes, varset($options['extra_file_types'], FALSE))) === TRUE);
				}
				if ($tpos === FALSE)
				{
					// File type upload not permitted - error message and abort
					$first_error = 251; // Invent our own error number - file type not permitted
				}
			}

			if (!$first_error)  // All tests passed - can store it somewhere
			{
				// File upload broken - temp file renamed.
				// FIXME - method starting with 'get' shouldn't do system file changes.
				$uploaded[$c] = e107::getFile()->get_file_info($uploadfile, true, false);

				$uploaded[$c]['name'] = $name;
				$uploaded[$c]['rawname'] = $raw_name;
				$uploaded[$c]['origname'] = $origname;
				$uploaded[$c]['type'] = $files['type'][$key];
				$uploaded[$c]['size'] = 0;
				$uploaded[$c]['index'] = $key; // Store the actual index from the file_userfile array

			//	e107::getMessage()->addDebug(print_a($uploaded[$c],true));

				// Store as flat file
				if ((!$ul_temp_dir && @move_uploaded_file($uploadfile, $destination_file)) || ($ul_temp_dir && @rename($uploadfile, $destination_file))) // This should work on all hosts
				{
					@chmod($destination_file, $final_chmod);
					
					if (UH_DEBUG)
					{
						e107::getLog()->e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Final chmod() file {$destination_file} to {$final_chmod} ", FALSE, FALSE);
					}
									
					$uploaded[$c]['size'] = $files['size'][$key];
					$uploaded[$c]['fullpath'] = $uploaddir.DIRECTORY_SEPARATOR.$name;
					
					if (UH_DEBUG)
					{
						e107::getLog()->e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Saved file {$c} OK: ".$uploaded[$c]['name'], FALSE, FALSE);
					}
								
				}
				else
				{
					$first_error = 252; // Error - "couldn't save destination"
				}
			}

			if (!$first_error) // This file succeeded
			{ 
				$uploaded[$c]['message'] = LANUPLOAD_3." '".$raw_name."'";
				$uploaded[$c]['error'] = 0;
			}
			else
			{
				$uploaded[$c]['error'] = $first_error;
				$uploaded[$c]['size'] = 0;
				switch ($first_error)
				{
					case 1: // Exceeds upload_max_filesize in php.ini
						$error = LANUPLOAD_5;
					break;
					case 2: // Exceeds MAX_FILE_SIZE in form
						$error = LANUPLOAD_6;
					break;
					case 3: // Partial upload
						$error = LANUPLOAD_7;
					break;
					case 4: // No file uploaded
						$error = LANUPLOAD_8;
					break;
					case 5: // Undocumented code (zero file size)
						$error = LANUPLOAD_9;
					break;
					case 6: // Missing temporary folder
						$error = LANUPLOAD_13;
					break;
					case 7: // File write failed
						$error = LANUPLOAD_14;
					break;
					case 8: // Upload stopped by extension
						$error = LANUPLOAD_15;
					break;
					case 249: // Too many files  (our error code)
						$error = LANUPLOAD_19;
					break;
					case 250: // Duplicate File  (our error code)
						$error = LANUPLOAD_10;
					break;
					case 251: // File type not allowed (our error code)
						$error = LANUPLOAD_1." ".$files['type'][$key]." ".LANUPLOAD_2." ({$file_status})";
					break;
					case 252: // File uploaded OK, but couldn't save it
						$error = LANUPLOAD_4." [".str_replace("../", "", $uploaddir)."]";
					break;
					case 253: // Bad name for uploaded file (our error code)
						$error = LANUPLOAD_17;
					break;
					case 254: // file size exceeds allowable limits (our error code)
						$error = LANUPLOAD_18;
					break;
					default: // Shouldn't happen - but at least try and make it obvious if it does!
						$error = LANUPLOAD_16;
				}

				$uploaded[$c]['message'] = LANUPLOAD_11." '".$name."' <br />".LAN_ERROR.": ".$error;
				$uploaded[$c]['line'] = __LINE__;
				$uploaded[$c]['file'] = __FILE__;
				
				if (UH_DEBUG) // If we need to abort on first error, do so here - could check for specific error codes
				{
					e107::getLog()->e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Main routine error {$first_error} file {$c}: ".$uploaded[$c]['message'], FALSE, FALSE);
				}
			}
			
			if (is_file($uploadfile))
			{
				@unlink($uploadfile); // Don't leave the file on the server if error (although should be auto-deleted)
			}
			$c++;
		}
	}

	return $uploaded;
}



/**
 *	Utility routine to handle the messages returned by process_uploaded_files().
 *	@param array $upload_array is the list of uploaded files (as returned by process_uploaded_files())
 *	@param boolean $errors_only - if TRUE, no message is shown for a successful upload.
 *	@param boolean $use_handler - if TRUE, message_handler is used to display the message.
 *	@return string - a list of all accumulated messages. (Non-destructive call, so can be called several times with different options).
 */

function handle_upload_messages(&$upload_array, $errors_only = TRUE, $use_handler = FALSE)
{
	// Display error messages, accumulate FMESSAGE
	// Write as a separate routine - returns all messages displayed. Option to only display failures.
	$f_message = '';
	foreach ($upload_array as $k=>$r)
	{
		if (!$errors_only || $r['error'])
		{
			if ($use_handler)
			{
				require_once (e_HANDLER."message_handler.php");
				message_handler("MESSAGE", $r['message'], $r['line'], $r['file']);
			}
			$f_message[] = $r['message'];
		}
	}
	return implode("<br />", $f_message);
}

/*
//====================================================================
//					LEGACY FILE UPLOAD HANDLER
//====================================================================

/**
 *	This is the 'legacy' interface, which handles various special cases etc.
 *	It was the only option in E107 0.7.8 and earlier, and is still used in some places in core.
 *	It also attempts to return in the same way as the original, especially when any errors occur
 *  @deprecated
 *	@param string $uploaddir - target directory for file. Defaults to e_FILE/public
 *	@param boolean|string $avatar - sets the 'type' or destination of the file:
 * 				FALSE 			- its a 'general' file
 *				'attachment'	- indicates an attachment (related to forum post or PM)
 *				'unique' 		- indicates that file name must be unique - new name given (prefixed with time()_ )
 *				'avatar'		- indicates an avatar is being uploaded
 *	@param string $fileinfo		- included within the name of the saved file with attachments - can be an identifier of some sort
 *									 (Forum adds 'FT{$tid}_' - where $tid is the thread ID.
 *	@param boolean $overwrite	- if true, an uploaded file can overwrite an existing file of the same name (not used in 0.7 core)
 *
 *	Preference used:
 *		$pref['upload_storagetype'] - now ignored - used to be 1 for files, 2 for database storage
 *
 *	@return boolean|array		- For backward compatibility, returns FALSE if only one file uploaded and an error;
 *  								otherwise returns an array with per-file error codes as appropriate.
 *	 On exit, F_MESSAGE is defined with the success/failure message(s) that have been displayed - one file per line
 */
/**
 * @Deprecated use e107::getFile()->getUploaded();
 * @param $uploaddir
 * @param bool|false $avatar
 * @param string $fileinfo
 * @param string $overwrite
 * @return array|bool
 */
function file_upload($uploaddir, $avatar = FALSE, $fileinfo = "", $overwrite = "")
{
	$admin_log = e107::getAdminLog();
	$options = array(
		'extra_file_types'=>TRUE
	); // As default, allow any filetype enabled in filetypes.php

	if (!$uploaddir)
	{
		$uploaddir = e_UPLOAD;
	}

	if (strpos($avatar, '=') !== FALSE)
	{
		list($avatar, $param) = explode('=', $avatar, 2);
	}
	else
	{
		$param = USERID;
	}
	switch ($avatar)
	{
		case 'attachment':
			$avatar = "attachment+".$fileinfo;
		break;
		case 'avatar':
			$avatar = 'prefix+ap_'.$param.'_'; // Prefix unique to user
			$options['overwrite'] = TRUE; // Allow update of avatar with same file name
		break;
	}

	if (UH_DEBUG)
		$admin_log->
			e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Legacy call, directory ".$uploaddir, FALSE, FALSE);

	$ret = process_uploaded_files(getcwd()."/".$uploaddir, $avatar, $options); // Well, that's the way it was done before

	if ($ret === FALSE)
	{
		if (UH_DEBUG)
			$admin_log->
				e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Legacy return FALSE", FALSE, FALSE);
		return FALSE;
	}

	if (UH_DEBUG)
		$admin_log->
			e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Legacy return with ".count($ret)." files", FALSE, FALSE);
	$messages = handle_upload_messages($ret, FALSE, TRUE); // Show all the error and acknowledgment messages
	define(F_MESSAGE, $messages);

	if (count($ret) == 1)
	{
		if ($ret[0]['error'] != 0)
			return FALSE; // Special case if errors
	}
	return $ret;
}




//====================================================================
//				 VETTING AND UTILITY ROUTINES
//====================================================================

/**
 * @deprecated Get image (string) mime type
 * @see e_file::getImageMime();
 * or when extended - array [(string) mime-type, (array) associated extensions)].
 * A much faster way to retrieve mimes than getimagesize()
 *
 * @param $filename
 * @param bool|false $extended
 * @return array|string|false
 */
function get_image_mime($filename, $extended = false)
{
	// mime types as returned from image_type_to_mime_type()
	// and associated file extensions
	$imageExtensions = array(
		'image/gif' 					=> array('gif'),
		'image/jpeg' 					=> array('jpg'),
		'image/png' 					=> array('png'),
		'application/x-shockwave-flash' => array('swf', 'swc'),
		'image/psd' 					=> array('psd'),
		'image/bmp' 					=> array('bmp'),
		'image/tiff' 					=> array('tiff'),
		'application/octet-stream' 		=> array('jpc', 'jpx', 'jb2'),
		'image/jp2' 					=> array('jp2'),
		'image/iff' 					=> array('iff'),
		'image/vnd.wap.wbmp' 			=> array('wbmp'),
		'image/xbm' 					=> array('xbm'),
		'image/vnd.microsoft.icon' 		=> array('ico')
	);

	$ret = image_type_to_mime_type(exif_imagetype($filename));

	if($extended)
	{
		return array(
			$ret,
			$ret && isset($imageExtensions[$ret]) ? $imageExtensions[$ret]: array()
		);
	}

	return $ret;

}

/**
 *	Check uploaded file to try and identify dodgy content.
 *	@param string $filename is the full path+name to the uploaded file on the server
 *	@param string $target_name is the intended name of the file once transferred
 *	@param array $allowed_filetypes is an array of permitted file extensions, in lower case, no leading '.'
 *			(usually generated from filetypes.xml/filetypes.php)
 *	@param boolean|string $unknown - handling of file types unknown to us/define additional types
 *			if FALSE, rejects totally unknown file extensions (even if in $allowed_filetypes).
 *			if $unknown is TRUE, accepts totally unknown file extensions.
 *			otherwise $unknown is a comma-separated list of additional permitted file extensions
 *	@return boolean|int - TRUE if file acceptable, a numeric 'fail' code if unacceptable:
 *		1 - file type not allowed
 *		2 - can't read file contents
 *		3 - illegal file contents (usually '<?php')
 *		4 - not an image file
 *		5 - bad image parameters - REMOVED
 *		6 - not in supplementary list
 *		7 - suspicious file contents
 *		8 - unknown file type
 *		9 - unacceptable file type (prone to exploits)
 */

 //TODO - Move this function to file_class.php
	function vet_file($filename, $target_name, $allowed_filetypes = '', $unknown = false)
	{

		// 1. Start by checking against filetypes - that's the easy one!
		$file_ext = strtolower(substr(strrchr($target_name, '.'), 1));

		if(!isset($allowed_filetypes[$file_ext]))
		{
			if(is_bool($unknown))
			{
				return 1;
			} // Reject out of hand if no possible alternative extensions
			// Otherwise, it could be in the supplementary list

			$tmp = explode(',', $unknown);
			for($i = 0; $i < count($tmp); $i++)
			{
				$tmp[$i] = strtolower(trim(str_replace('.', '', $tmp[$i])));
			}

			if(!in_array($file_ext, $tmp))
			{
				return 6;
			}
		}

		// 2. For all files, read the first little bit to check for any flags etc
		$res = fopen($filename, 'rb');
		$tstr = fread($res, 2048);
		fclose($res);

		if($tstr === false)
		{
			return 2; // If can't read file, not much use carrying on!
		}

		if(stripos($tstr, '<?php') !== false)
		{
			return 3; // Pretty certain exploit
		}

		if(strpos($tstr, '<?') !== false)                // Bit more tricky - can sometimes be OK
		{
			if(stripos($tstr, '<?xpacket') === false)    // Allow the XMP header produced by CS4
			{
				return 7;
			}
		}

		// 3. Now do what we can based on file extension
		switch($file_ext)
		{

			case 'jpg':
			case 'gif':
			case 'png':
			case 'jpeg':
			case 'pjpeg':
			case 'bmp':
			case 'swf':
			case 'fla':
			case 'flv':
			case 'swc':
			case 'psd':
			case 'ai':
			case 'eps':
			case 'svg':
			case 'tiff':
			case 'jpc': // http://fileinfo.com/extension/jpc
			case 'jpx': // http://fileinfo.com/extension/jpx
			case 'jb2': // http://fileinfo.com/extension/jb2
			case 'jp2': // http://fileinfo.com/extension/jp2
			case 'iff':
			case 'wbmp':
			case 'xbm':
			case 'ico':
				$ret = get_image_mime($filename);
				if($ret === false)
				{
					return 4; // exif_imagetype didn't recognize the image mime
				}
				// getimagesize() is extremely slow + it can't handle all required media!!! Abandon this check!
				//	return 5; // Zero size picture or bad file format
				break;

			case 'zip':
			case 'gzip':
			case 'gz':
			case 'tar':
			case 'bzip':
			case 'pdf':
			case 'doc':
			case 'docx':
			case 'xls':
			case 'xlsx':
			case 'rar':
			case '7z':
			case 'csv':
			case 'mp3':
			case 'wav':
			case 'mp4':
			case 'mpg':
			case 'mpa':
			case 'wma':
			case 'wmv':
			case 'flv': //Flash stream
			case 'f4v': //Flash stream
			case 'mov': //media
			case 'avi': //media
				break; // Just accept these

			case 'php':
			case 'htm':
			case 'html':
			case 'cgi':
			case 'pl':
				return 9; // Never accept these! Whatever the user thinks!

			default:
				if(is_bool($unknown))
				{
					return ($unknown ? true : 8);
				}
		}

		return true; // Accepted here
	}



	/**
	 *	Get array of file types (file extensions) which are permitted - reads a definition file.
	 *	(Similar to @See{get_XML_filetypes()}, but expects an XML file)
	 *
	 *	@param string $def_file - name of a file containing a comma-separated list of file types, which is sought in the E_ADMIN directory
	 *	@param string $file_mask - comma-separated list of allowed file types - only those specified in both $file_mask and $def_file are returned
	 *
	 *	@return array - where key is the file type (extension); value is max upload size
	 */
	function get_allowed_filetypes($def_file = FALSE, $file_mask = '')
	{
		$ret = array(
		);
		if ($def_file === FALSE)
			return $ret;

		if ($file_mask)
		{
			$file_array = explode(',', $file_mask);
			foreach ($file_array as $k=>$f)
			{
				$file_array[$k] = trim($f);
			}
		}

		if ($def_file && is_readable(e_ADMIN.$def_file))
		{
			$a_filetypes = trim(file_get_contents(e_ADMIN.$def_file));
			$a_filetypes = explode(',', $a_filetypes);
		}

		foreach ($a_filetypes as $ftype)
		{
			$ftype = strtolower(trim(str_replace('.', '', $ftype)));
			if ($ftype && (!$file_mask || in_array($ftype, $file_array)))
			{
				$ret[$ftype] = -1;
			}
		}
		return $ret;
	}




	/**
	 *	Parse a file size string (e.g. 16M) and compute the simple numeric value.
	 *	Proxy to e_file::file_size_decode().
	 *
	 *	@see e_file::file_size_decode()
	 *	@param string $source - input string which may include 'multiplier' characters such as 'M' or 'G'. Converted to 'decoded value'
	 *	@param int $compare - a 'compare' value
	 *	@param string $action - values (gt|lt)
	 *
	 *	@return int file size value.
	 *		If the decoded value evaluates to zero, returns the value of $compare
	 *		If $action == 'gt', return the larger of the decoded value and $compare
	 *		If $action == 'lt', return the smaller of the decoded value and $compare
	 */
	function file_size_decode($source, $compare = 0, $action = '')
	{
		return e107::getFile(true)->file_size_decode($source, $compare, $action);
	}



	/**
	 * @deprecated @see e_file::getAllowedFileTypes();
	 *	Get array of file types (file extensions) which are permitted - reads an XML-formatted definition file.
	 *	(Similar to @See{get_allowed_filetypes()}, but expects an XML file)
	 *
	 *	@param string $def_file - name of an XML-formatted file, which is sought in the E_ADMIN directory
	 *	@param string $file_mask - comma-separated list of allowed file types - only those specified in both $file_mask and $def_file are returned
	 *
	 *	@return array - where key is the file type (extension); value is max upload size
	 */
	function get_XML_filetypes($def_file = FALSE, $file_mask = '')
	{
		$ret = array(
		);
		if ($def_file === FALSE)
			return $ret;

		if ($file_mask)
		{
			$file_array = explode(',', $file_mask);
			foreach ($file_array as $k=>$f)
			{
				$file_array[$k] = trim($f);
			}
		}

		if ($def_file && is_readable(e_SYSTEM.$def_file))
		{
			$xml = e107::getXml();
			// class tag should be always array
			$xml->setOptArrayTags('class');
			$temp_vars = $xml->loadXMLfile(e_SYSTEM.$def_file, 'filetypes', false);
			if ($temp_vars === FALSE)
			{
				echo "Error reading XML file: {$def_file}<br />";
				return $ret;
			}
			foreach ($temp_vars['class'] as $v1)
			{
				$v = $v1['@attributes'];
				if (check_class($v['name']))
				{
					$current_perms[$v['name']] = array(
						'type'=>$v['type'], 'maxupload'=>$v['maxupload']
					);
					$a_filetypes = explode(',', $v['type']);
					foreach ($a_filetypes as $ftype)
					{
						$ftype = strtolower(trim(str_replace('.', '', $ftype))); // File extension

						if (!$file_mask || in_array($ftype, $file_array))
						{ // We can load this extension
							if (isset($ret[$ftype]))
							{
								$ret[$ftype] = file_size_decode($v['maxupload'], $ret[$ftype], 'gt'); // Use largest value
							}
							else
							{
								$ret[$ftype] = file_size_decode($v['maxupload']);
							}
						}
					}
				}
			}
		}

		return $ret;
	}



	/**
	 *	Calculate 'global' maximum upload size - the maximum before extension-specific restrictions taken into account
	 *
	 *	@param int $max_up - if > 0, its a global maximum permitted. If < 0, $pref['upload_maxfilesize'] is used (if set)
	 *
	 *	@return int maximum allowed upload size for file
	 */
	function calc_max_upload_size($max_up = -1)
	{
		global $pref;
		$admin_log = e107::getAdminLog();
		// Work out maximum allowable file size
		if (deftrue('UH_DEBUG'))
		{
			$admin_log->
				e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "File size limits - user set: ".$pref['upload_maxfilesize']." Post_max_size: ".ini_get('post_max_size')." upload_max_size: ".ini_get('upload_max_size'), FALSE, FALSE);
		}
		$max_upload_size = file_size_decode(ini_get('post_max_size'));
		$max_upload_size = file_size_decode(ini_get('upload_max_filesize'), $max_upload_size, 'lt');
		if ($max_up > 0)
		{
			$max_upload_size = file_size_decode($max_up, $max_upload_size, 'lt');
		}
		else
		{
			if (varset($pref['upload_maxfilesize'], 0) > 0)
				$max_upload_size = file_size_decode($pref['upload_maxfilesize'], $max_upload_size, 'lt');
		}
		if (deftrue('UH_DEBUG'))
			$admin_log->
				e_log_event(10, __FILE__."|".__FUNCTION__."@".__LINE__, "DEBUG", "Upload Handler test", "Final max upload size: {$max_upload_size}", FALSE, FALSE);
		return $max_upload_size;
	}



	/**
	 *	Get an array of permitted filetypes according to a set hierarchy.
	 *	If a specific file name given, that's used. Otherwise the default hierarchy is used
	 *
	 *	@param string $file_mask - comma-separated list of allowed file types
	 *	@param string $filename - optional override file name - defaults ignored
	 *
	 *	@return array of filetypes
	 */
	function get_filetypes($file_mask = FALSE, $filename = '')
	{
		if ($filename != '')
		{
			
			if (strtolower(substr($filename, -4) == '.xml'))
			{
				return get_XML_filetypes($filename, $file_mask);
			}
			return get_allowed_filetypes($filename, $file_mask);
		}

		if (is_readable(e_SYSTEM.e_READ_FILETYPES))
		{
			return get_XML_filetypes(e_READ_FILETYPES, $file_mask);
		}

		if (ADMIN && is_readable(e_ADMIN.'admin_filetypes.php'))
		{
			return get_allowed_filetypes('admin_filetypes.php', $file_mask);
		}

		if (is_readable(e_ADMIN.'filetypes.php'))
		{
			return get_allowed_filetypes('filetypes.php', $file_mask);
		}
		return array(); // Just an empty array
	}



	/**
	 * 	Scans the array of allowed file types, updates allowed max size as appropriate.
	 *	If the value is larger than the site-wide maximum, reduces it.
	 *
	 *	@param array $allowed_filetypes - key is file type (extension), value is maximum size allowed
	 *	@param int $max_upload_size - site-wide maximum file upload size
	 *
	 *	@return int largest allowed file size across all file types
	 */
	function set_max_size(&$allowed_filetypes, $max_upload_size)
	{
		$new_max = 0;
		foreach ($allowed_filetypes as $t=>$s)
		{
			if ($s < 0)
			{ // Unspecified max - use the global value
				$allowed_filetypes[$t] = $max_upload_size;
			}
			elseif ($allowed_filetypes[$t] > $max_upload_size)
				$allowed_filetypes[$t] = $max_upload_size;
			if ($allowed_filetypes[$t] > $new_max)
				$new_max = $allowed_filetypes[$t];
		}
		return $new_max;
	}



	/*
	 *	Quick routine if all we want is the size of the largest file the current user can upload
	 *
	 *	@return int largest allowed file size across all file types
	 */
	function get_user_max_upload()
	{
		$a_filetypes = get_filetypes();
		if (count($a_filetypes) == 0)
			return 0; // Return if no upload allowed
		$max_upload_size = calc_max_upload_size(-1); // Find overriding maximum upload size
		$max_upload_size = set_max_size($a_filetypes, $max_upload_size);
		return $max_upload_size;
	}

?>
