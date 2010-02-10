<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tinymce/e_meta.php,v $
 * $Revision$
 * $Date$
 * $Author$
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