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
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/upload.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';

switch ($action)
{
  case 'filetypes' :
    $text = "The file types and maximum file size which can be uploaded are set by user class. This option generates a file called ".e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES.",
		which must be copied or moved to directory ".e_ADMIN_ABS." and renamed to ".e_READ_FILETYPES." before it takes effect.<br />
		One definition per class is permitted.<br />
		Note that these definitions apply site-wide, even if the public uploads feature is disabled.";
    break;
  case 'options' :
    $text = "The complete public uploads system may be disabled here.<br />
		Flatfile storage is generally appropriate (otherwise the maximum file size is hard-limited to 500kb).<br />
		The maximum filesize overrides any definitions set in filetypes.xml.<br />
		Public uploads may be restricted to a particular user class, but these settings also apply to other areas of the site where uploads may be permitted, such as
		news and forms.";
	break;
  case 'view' :
  default :
	$text = "This page lists any submitted uploads. You may choose to delete them, transfer them to downloads, or post them to news";
}
$ns -> tablerender("Public Uploads Help", $text);
