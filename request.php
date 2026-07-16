<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2013 e107 Inc 
|     http://e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|		
|	  Generic File Request Script. 
|
+----------------------------------------------------------------------------+
*/



require_once("class2.php");

if (!e_QUERY || isset($_POST['userlogin'])) 
{
	e107::redirect();
	exit();
}


// Media-Manager direct file download. 

if(!empty($_GET['file'])) // eg. request.php?file=1
{
	$sql = e107::getDb();
	$qb = $sql->createQueryBuilder();
	$qb->select('media_url')->from('core_media');

	if(is_numeric($_GET['file']))
	{
		$qb->where('media_id', (int) $_GET['file']);
	}
	else // @see $tp->toFile()
	{
		$srch = array(
			'{e_MEDIA_FILE}' => 'e_MEDIA_FILE/',
			'{e_PLUGIN}' => 'e_PLUGIN/'
		);

		$fileName = str_replace($srch,array_keys($srch),$_GET['file']);

		$qb->where('media_url', e107::getParser()->filter($fileName));
	}

	$qb->whereIn('media_userclass', array_map('intval', explode(',', USERCLASS_LIST)))->setMaxResults(1);

	$row = $qb->fetchRow();
	if (!empty($row))
	{
		// $file = $tp->replaceConstants($row['media_url'],'rel');
		e107::getFile()->send($row['media_url']);
	}
	else
	{
		require_once(HEADERF);
		echo e107::getMessage()->addError(LAN_DOWNLOAD_NO_PERMISSION)->render();
		require_once(FOOTERF);
	}

}
elseif(e107::isInstalled('download')) //BC Legacy Support. (Downloads Plugin)
{
	e107::getRedirect()->redirect(e_PLUGIN."download/request.php?".e_QUERY);
}

exit(); 



?>