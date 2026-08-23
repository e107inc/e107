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

	if(defined('e_TOKEN') && empty($_POST['e-token']) && empty($_GET['e-token']))
	{
		e107::coreLan('upload_handler');

		echo json_encode(array(
			'jsonrpc' => '2.0',
			'error'   => array('code' => 103, 'message' => defset('LANUPLOAD_REFUSED_TOKEN_MISSING', 'Upload refused.')),
			'id'      => 'id',
		));
		exit;
	}

	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	@set_time_limit(5 * 60); // 5 minutes execution time

	echo e107::getMedia()->processAjaxUpload();

exit; 


