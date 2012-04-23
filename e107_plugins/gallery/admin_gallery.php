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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/admin_download.php,v $
 * $Revision: 12639 $
 * $Date: 2012-04-20 00:28:53 -0700 (Fri, 20 Apr 2012) $
 * $Author: e107coders $
 */

$eplug_admin = true;

require_once("../../class2.php");
if (!getperms("P") || !plugInstalled('gallery'))
{
	header("location:".e_BASE."index.php");
	exit() ;
}

	$e_sub_cat = 'gallery';
	new plugin_gallery_admin();
	require_once(e_ADMIN."auth.php");
	e107::getAdminUI()->runPage(); //gallery/includes/admin.php is auto-loaded. 
	require_once(e_ADMIN."footer.php");
	exit;


?>