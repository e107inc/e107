<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News frontend
 *
 * $URL$
 * $Id$
 */

 // BOOTSTRAP START


	define('e_SINGLE_ENTRY', TRUE);
	
	$_E107['single_entry'] = true; // TODO - notify class2.php
	
	define('ROOT', dirname(__FILE__));
	set_include_path(ROOT.PATH_SEPARATOR.get_include_path());
	
	require_once("class2.php");
	
	$front = eFront::instance();
	$front->init()
		->run();
	
	$inc = $front->isLegacy(); 
	if($inc)
	{
		// last chance to set legacy env
		$request = $front->getRequest();
		$request->setLegacyQstring();
		$request->setLegacyPage();
		if(!is_file($inc) || !is_readable($inc))
		{
			echo 'Bad request - destination unreachable - '.$inc;
		}
		include($inc);
		exit;
	}
	
	$response = $front->getResponse();
	if(e_AJAX_REQUEST)
	{
		$response->setParam('meta', false)
			->setParam('render', false)
			->send('default', false, true);
		exit;
	}
	$response->sendMeta();
	
	include_once(HEADERF);
		eFront::instance()->getResponse()->send('default', false, true);
	include_once(FOOTERF);
	exit;

 // BOOTSTRAP END

