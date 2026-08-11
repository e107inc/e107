<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * On-the-fly thumbnail generator
 *
 * $URL$
 * $Id$
 */

 /**
 * @package e107
 * @subpackage core
 * @author secretr
 *
 *
 * On-the-fly thumbnail generator
 */

const e107_INIT = true;


/**
 * Answer the caller with a status and nothing else. The caller is
 * unauthenticated, so everything worth knowing goes to the error log.
 *
 * @param int    $status
 * @param string $message
 * @return void
 */
function thumbFail($status, $message)
{
	if(!headers_sent())
	{
		http_response_code($status);
		header('Content-Type: text/plain; charset=utf-8');
		header('X-Content-Type-Options: nosniff');
	}

	echo $message;
}

/**
 * @param \Throwable $e
 */
function thumbExceptionHandler($e)
{
	error_log(sprintf(
		'thumb.php: %s in %s on line %d%s%s',
		$e->getMessage(),
		$e->getFile(),
		$e->getLine(),
		PHP_EOL,
		$e->getTraceAsString()
	));

	thumbFail(500, 'Thumbnail error');
}

function thumbErrorHandler($errno, $errstr, $errfile, $errline)
{
	// A handler is called for a diagnostic the caller suppressed with @ as
	// well, so the mask has to be read rather than assumed.
	if(!(error_reporting() & $errno))
	{
		return true;
	}

	error_log(sprintf('thumb.php: [%d] %s in %s on line %d', $errno, $errstr, $errfile, $errline));

	if($errno === E_USER_ERROR)
	{
		thumbFail(500, 'Thumbnail error');
		exit(1);
	}

	return true;
}

@ini_set('display_errors', '0'); // this endpoint answers in bytes, not in prose.

set_exception_handler('thumbExceptionHandler'); // disable to troubleshoot.
set_error_handler("thumbErrorHandler"); // disable to troubleshoot.

// error_reporting(0); // suppress all errors or image will be corrupted.



ini_set('gd.jpeg_ignore_warning', 1);
//require_once './e107_handlers/benchmark.php';
//$bench = new e_benchmark();
//$bench->start();

/**
 * Class e_thumbpage
 * @todo Simplify all this, e.g. e107::getInstance()->initMinimal($path_to_e107_config);
 */
class e_thumbpage
{

	function __construct()
	{

		$self = realpath(__DIR__);

		$e_ROOT = $self."/";

		if ((substr($e_ROOT,-1) !== '/') && (substr($e_ROOT,-1) !== '\\') )
		{
			$e_ROOT .= DIRECTORY_SEPARATOR;  // Should function correctly on both windows and Linux now.
		}

		define('e_ROOT', $e_ROOT);

		$mySQLdefaultdb = '';
		$HANDLERS_DIRECTORY = '';
		$mySQLprefix = '';

		// Config

		$config = include($self.DIRECTORY_SEPARATOR.'e107_config.php');

		// support early include feature
		if(!empty($CLASS2_INCLUDE))
		{
			 require_once(realpath(__DIR__ .'/'.$CLASS2_INCLUDE));
		}


		ob_end_clean(); // Precaution - clearout utf-8 BOM or any other garbage in e107_config.php

		if(empty($HANDLERS_DIRECTORY))
		{
			$HANDLERS_DIRECTORY = 'e107_handlers/'; // quick fix for CLI Unit test.
		}

		$tmp = $self.DIRECTORY_SEPARATOR.$HANDLERS_DIRECTORY;

		//Core functions - now API independent
		require($tmp.DIRECTORY_SEPARATOR.'core_functions.php');
		//e107 class
		require($tmp.DIRECTORY_SEPARATOR.'e107_class.php');

		if(empty($config['paths'])) // old e107_config.php format.
		{
			$e107_paths = [];
			foreach (array_keys(e107::getInstance()->overridableDirs()) as $name)
			{
			    if (isset($$name))
			    {
			        $e107_paths[$name] = $$name;
			    }
			}

			$legacy_sql_info = compact('mySQLserver', 'mySQLuser', 'mySQLpassword', 'mySQLdefaultdb', 'mySQLprefix');
			$sql_info = array_combine(array_map(function($k) {
				return str_replace('mySQL', '', $k);
				}, array_keys($legacy_sql_info)),
		        $legacy_sql_info
			);
		}
		else // New e107_config.php format. v2.4+
		{
			$e107_paths = $config['paths'];
			$sql_info = $config['database'];
			$E107_CONFIG = isset($config['other']) ? $config['other'] : [];
		}

		$e107 = e107::getInstance()->initCore($e107_paths, e_ROOT, $sql_info, varset($E107_CONFIG, array()));
		// basic Admin area detection - required for proper path parsing
		define('ADMIN', strpos(e_SELF, (e107::getFolder('admin')) != false || strpos(e_PAGE, 'admin') !== false));

		// Next function call maintains behavior identical to before; might not be needed
		//  See https://github.com/e107inc/e107/issues/3033
		$e107->set_urls_deferred();

		$pref = e107::getPref();


		require_once(e_HANDLER."e_thumbnail_class.php");

		$thm = new e_thumbnail;
		$thm->init($pref);

		if(!$thm->checkSrc())
		{
			thumbFail(403, 'Bad URL');
			exit;
		}

		$thm->sendImage();
	}
}

new e_thumbpage;
// Check your e_LOG folder
//$bench->end()->logResult('thumb.php', $_GET['src'].' - no cache');
exit;


