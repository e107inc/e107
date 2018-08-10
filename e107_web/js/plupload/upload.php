<?php
	/**
	 * upload.php
	 *
	 * Copyright 2009, Moxiecode Systems AB
	 * Released under GPL License.
	 *
	 * License: http://www.plupload.com/license
	 * Contributing: http://www.plupload.com/contributing
	 */

// HTTP headers for no cache etc

	$_E107['no_online'] = true;
	define('e_MINIMAL', true);
	define('FLOODPROTECT', false);
	require_once("../../../class2.php");

	if(!ADMIN)
	{
		exit;
	}

	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

// Settings
// $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
	$targetDir = e_IMPORT;
//$targetDir = 'uploads';

	$cleanupTargetDir = true; // Remove old files
	$maxFileAge = 5 * 3600; // Temp file age in seconds

// 5 minutes execution time
	@set_time_limit(5 * 60);

// Uncomment this one to fake upload time
// usleep(5000);

// Get parameters
	$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
	$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
	$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

// Clean the fileName for security reasons
	$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

	if(!empty($_FILES['file']['name'])) // dropzone support v2.1.9
	{
		$fileName = $_FILES['file']['name'];
	}

// Make sure the fileName is unique but only if chunking is disabled
	if($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName))
	{
		$ext = strrpos($fileName, '.');
		$fileName_a = substr($fileName, 0, $ext);
		$fileName_b = substr($fileName, $ext);

		$count = 1;
		while(file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
		{
			$count++;
		}

		$fileName = $fileName_a . '_' . $count . $fileName_b;
	}

	$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

// Create target dir
	if(!file_exists($targetDir))
	{
		@mkdir($targetDir);
	}

// Remove old temp files	
	if($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir)))
	{
		while(($file = readdir($dir)) !== false)
		{
			$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

			// Remove temp file if it is older than the max age and is not the current file
			if(preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part"))
			{
				@unlink($tmpfilePath);
			}
		}

		closedir($dir);
	}
	else
	{
		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
	}


// Look for the content type header
	if(isset($_SERVER["HTTP_CONTENT_TYPE"]))
	{
		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
	}

	if(isset($_SERVER["CONTENT_TYPE"]))
	{
		$contentType = $_SERVER["CONTENT_TYPE"];
	}

// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
	if(strpos($contentType, "multipart") !== false)
	{
		if(isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name']))
		{
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");

			if($out)
			{
				// Read binary input stream and append it to temp file
				$tmpName = e107::getParser()->filter($_FILES['file']['tmp_name'],'str');
				$in = fopen($tmpName, "rb");

				if($in)
				{
					while($buff = fread($in, 4096))
					{
						fwrite($out, $buff);
					}
				}
				else
				{
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
				}
				fclose($in);
				fclose($out);
				@unlink($tmpName);
			}
			else
			{
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}
		}
		else
		{
			die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file. '.ini_get('upload_max_filesize').'"}, "id" : "id"}');
		}


	}
	else
	{
		// Open temp file
		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
		if($out)
		{
			// Read binary input stream and append it to temp file
			$in = fopen("php://input", "rb");

			if($in)
			{
				while($buff = fread($in, 4096))
				{
					fwrite($out, $buff);
				}
			}
			else
			{
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}

			fclose($in);
			fclose($out);
		}
		else
		{
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
	}

// Check if file has been uploaded
	if(!$chunks || $chunk == $chunks - 1)
	{
		// Strip the temp .part suffix off
		rename("{$filePath}.part", $filePath);
	}

	$filePath = str_replace('//','/',$filePath); // cleanup .


	if(e107::getFile()->isClean($filePath) !== true)
	{
		@unlink($filePath);
		die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Bad File Detected."}, "id" : "id"}');
	}


	$convertToJpeg = e107::getPref('convert_to_jpeg', 0);
	$fileSize = filesize($filePath);

	if(varset($_GET['for']) !== '_icon' && !empty($convertToJpeg))
	{
		if($jpegFile = e107::getMedia()->convertImageToJpeg($filePath, true))
		{
			$filePath = $jpegFile;
			$fileName = basename($filePath);
			$fileSize = filesize($jpegFile);
		}

	}




	if($_GET['for'] != '') // leave in upload directory if no category given.
	{
		$uploadPath = varset($_GET['path'],null);
		$result = e107::getMedia()->importFile($fileName, $_GET['for'], array('path'=>$uploadPath));
	}


	$log = e107::getParser()->filter($_GET,'str');
	$log['filepath'] = str_replace('../','',$filePath);
	$log['filename'] = $fileName;
	$log['filesize'] = $fileSize;
	$log['status'] = ($result) ? 'ok' : 'failed';
	$log['_files'] = $_FILES;
//	$log['_get'] = $_GET;
//	$log['_post'] = $_POST;



	

	$type = ($result) ? E_LOG_INFORMATIVE : E_LOG_WARNING;

	e107::getLog()->add('LAN_AL_MEDIA_01', print_r($log, true), $type, 'MEDIA_01');


	$preview = e107::getMedia()->previewTag($result);
	$array = array("jsonrpc" => "2.0", "result" => $result, "id" => "id", 'preview' => $preview, 'data'=>$_FILES );

	echo json_encode($array);
// Return JSON-RPC response
// die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');


?>