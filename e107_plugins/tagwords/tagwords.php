<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords Page
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tagwords/tagwords.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-09-25 20:13:12 $
 * $Author: secretr $
 *
*/

require_once('../../class2.php');
if (!defined('e107_INIT')) { exit; }

$_GET = e107::getUrl()->parseRequest('tagwords', 'main', e_QUERY);

require_once(HEADERF);

require_once(e_PLUGIN."tagwords/tagwords_class.php");
$tag = new tagwords();

if(varsettrue($tag->pref['tagwords_class']) && !check_class($tag->pref['tagwords_class']) )
{
	header("location:".SITEURL); exit;
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