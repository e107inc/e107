<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords Page
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tagwords/tagwords.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

if(!defined('e107_INIT')) require_once('../../class2.php');

//$_GET = e107::getUrl()->parseRequest('tagwords', 'main', e_QUERY);

require_once(HEADERF);

require_once(e_PLUGIN."tagwords/tagwords_class.php");
$tag = new tagwords();
//echo e107::getUrl()->create('tagwords/search/area', 'area=news&q=something');
if(vartrue($tag->pref['tagwords_class']) && !check_class($tag->pref['tagwords_class']) )
{
	header("location:".SITEURL); exit;
}

if(vartrue($_GET['q']))
{
	$tag->TagSearchResults();
}
else
{
	$tag->TagRender();
}

require_once(FOOTERF);

?>