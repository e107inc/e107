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
	@set_time_limit(5 * 60); // 5 minutes execution time

	echo e107::getMedia()->processAjaxUpload();

exit; 


?>