<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/tinymce/e_meta.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-10-12 06:38:01 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

if(e_WYSIWYG || strpos(e_SELF,"tinymce/admin_config.php"))
{
  	require_once(e_PLUGIN."tinymce/wysiwyg.php");
	if(deftrue('TINYMCE_CONFIG'))
	{
		$wy = new wysiwyg(TINYMCE_CONFIG);	
	}
	else
	{
		$wy = new wysiwyg();	
	}
  	
	$wy -> render();
}
  

?>