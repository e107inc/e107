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

$REQUEST_TEMPLATE = "<div style='padding:40px;width:75%;margin-right:auto;margin-left:auto;text-align:center'>
<div class='forumheader3' style='padding:20px'>
<div style='padding:40px'>{LOGO}</div>

	<h2>{REQUEST_MESSAGE}</h2>
</div>
<div style='padding:40px'>
	<h2><a href='".e_BASE."download.php'>".LAN_dl_64."</a></h2>
</div>
</div>";


?>