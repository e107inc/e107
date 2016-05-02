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

if(vartrue($_GET['file']) && is_numeric($_GET['file'])) // eg. request.php?file=1
{
	$sql = e107::getDb();
	if ($sql->select('core_media', 'media_url', "media_id= ".intval($_GET['file'])." AND media_userclass IN (".USERCLASS_LIST.") LIMIT 1 ")) 
	{
		$row = $sql->fetch();
		// $file = $tp->replaceConstants($row['media_url'],'rel');
		e107::getFile()->send($row['media_url']);
	} 	
}
else //BC Legacy Support. (Downloads Plugin)
{
	e107::getRedirect()->redirect(e_PLUGIN."download/request.php?".e_QUERY);
}

exit(); 



?>