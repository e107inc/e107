<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.7/e107_themes/templates/membersonly_template.php $
|     $Revision: 11678 $
|     $Id: membersonly_template.php 11678 2010-08-22 00:43:45Z e107coders $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$REQUEST_TEMPLATE = "<div class='request-splash'>

	<h2 class='request-splash-message'>{REQUEST_MESSAGE}</h2>
	<div class='request-splash-clicklink'>{REQUEST_CLICKLINK}</div>
		
<div class='request-splash-footer'>
	<h2 class='request-splash-back'><a class='request-splash-back' href='".e_BASE."download.php'>".LAN_dl_64."</a></h2>
</div>
</div>";


?>