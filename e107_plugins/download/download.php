<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT'))
{
	require_once("../../class2.php");
}

if (!e107::isInstalled('download'))
{
	e107::redirect();
}

	e107::lan('download',false, true); // Loads e_PLUGIN.'download/languages/'.e_LANGUAGE.'/English_front.php'
	
	require_once(e_PLUGIN.'download/handlers/download_class.php');
	require_once(e_PLUGIN.'download/handlers/category_class.php');


	$dl = new download();


	if(!defined("USER_WIDTH") && !deftrue('BOOTSTRAP')) { define("USER_WIDTH","width:100%"); }

	/* define images */

	if(deftrue('BOOTSTRAP'))
	{
		define("IMAGE_DOWNLOAD", (file_exists(THEME."images/download.png") ? THEME."images/download.png" : e_IMAGE."generic/download.png"));
		define("IMAGE_NEW", (file_exists(THEME."images/new.png") ? THEME."images/new.png" : 'icon-star.glyph'));	
	}
	else 
	{
		define("IMAGE_DOWNLOAD", (file_exists(THEME."images/download.png") ? THEME."images/download.png" : e_IMAGE."generic/download.png"));
		define("IMAGE_NEW", (file_exists(THEME."images/new.png") ? THEME."images/new.png" : e_IMAGE."generic/new.png"));
	}
	


	$dl->init();

	// Legacy Comment Save. 
	if (isset($_POST['commentsubmit']))
	{
		if (!$sql->select("download", "download_comment", "download_id = '{$id}' "))
		{
			e107::redirect();
			exit;
		}
		else
		{
			$dlrow = $sql->fetch();
			if ($dlrow['download_comment'] && (ANON === TRUE || USER === TRUE))
			{
				$clean_authorname = $_POST['author_name'];
				$clean_comment = $_POST['comment'];
				$clean_subject = $_POST['subject'];
	
				e107::getComment()->enter_comment($clean_authorname, $clean_comment, "download", $id, $pid, $clean_subject);
	//			$e107cache->clear("comment.download.{$sub_action}");	$sub_action not used here
				e107::getCache()->clear("comment.download");
			}
		}
	}


	$texts = $dl->render(); // Load before header. 

	require_once (HEADERF);
	
	
	
	echo $texts;
	
	require_once (FOOTERF);
	
	
exit ;
?>