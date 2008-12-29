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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/tagwords/tagwords.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-12-29 20:51:07 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

require_once('../../class2.php');
if (!defined('e107_INIT')) { exit; }

require_once(HEADERF);

require_once(e_PLUGIN."tagwords/tagwords_class.php");
$tag = new tagwords();

if(varsettrue($tag->pref['tagwords_class']) && !check_class($tag->pref['tagwords_class']) )
{
	header("location:".e_BASE); exit;
}

if(varsettrue($_GET['q']))
{
	$tag->TagSearchResults();
}
else
{
	$tag->TagRender();
}

require_once(FOOTERF);

?>