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
require_once("../../class2.php");

if (!isset($pref['plug_installed']['trackback']))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

header('Content-Type: text/xml');
include(e_PLUGIN."trackback/trackbackClass.php");
$trackback = trackbackClass :: respondTrackback();

?>