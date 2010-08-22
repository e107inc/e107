<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit(); }

include_lan(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");

$front_page['links_page'] = array('page' => $PLUGINS_DIRECTORY.'links_page/links.php', 'title' => LCLAN_PLUGIN_LAN_1);

?>