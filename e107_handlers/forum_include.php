<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/forum_include.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-27 19:52:27 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
	
require_once(e_HANDLER.'multilang/pictures.php');
	
/**
* @return string path to and filename of forum icon image
*
* @param string $filename  filename of forum image
* @param string $eMLANG_folder if specified, indicates its a multilanguage image being processed and
*       gives the subfolder of the image path to the eMLANG_path() function,
*       default = FALSE
* @param string $eMLANG_pref  if specified, indicates that $filename may be overridden by the
*       $pref with $eMLANG_pref as its key if that pref is TRUE, default = FALSE
*
* @desc checks for the existence of a forum icon image in the themes forum folder and if it is found
*  returns the path and filename of that file, otherwise it returns the path and filename of the
*  default forum icon image in e_IMAGES. The additional $eMLANG args if specfied switch the process
*  to the sister multi-language function eMLANG_path().
*
* @access public
*/
function img_path($filename, $eMLANG_folder = FALSE, $eMLANG_pref = FALSE) {
	global $pref;
	if ($eMLANG_folder) {
		if ($eMLANG_pref) {
			$filename = $pref[$eMLANG_pref] ? $pref[$eMLANG_pref] :
			 $filename;
		}
		$image = eMLANG_path($filename, $eMLANG_folder);
	} else {
		$image = file_exists(THEME.'forum/'.$filename) ? THEME.'forum/'.$filename :
		 e_IMAGE.'forum/'.$filename;
	}
	return $image;
}
	
if (file_exists(THEME.'forum/forum_icons_template.php')) {
	require_once(THEME.'forum/forum_icons_template.php');
} else {
	require_once(e_BASE.$THEMES_DIRECTORY.'templates/forum_icons_template.php');
}
	
?>