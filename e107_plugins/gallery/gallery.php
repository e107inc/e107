<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Cron Administration
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_admin/cron.php $
 * $Id: cron.php 12492 2011-12-30 16:09:10Z e107steved $
 *
 */

/**
 *
 * @package     e107
 * @subpackage    frontend
 * @version     $Id: cron.php 12492 2011-12-30 16:09:10Z e107steved $
 *    Ultra-simple Image-Gallery
 */

require_once("../../class2.php");

if(!e107::isInstalled('gallery'))
{
	e107::redirect();
	exit;
}

// [PLUGINS]/gallery/languages/[LANGUAGE]/[LANGUAGE]_front.php
e107::lan('gallery', false, true);

e107::library('load', 'jquery.prettyPhoto');
e107::css('gallery', 'css/gallery.css');
e107::js('gallery', 'js/gallery.js');

require_once(HEADERF);


class gallery
{

	private $catList = array();

	function __construct()
	{
		$this->catList = e107::getMedia()->getCategories('gallery');

		if((vartrue($_GET['cat'])) && isset($this->catList[$_GET['cat']]))
		{
			$this->showImages($_GET['cat']);
		}
		else
		{
			$this->listCategories();
		}
	}

	function listCategories()
	{
		$template = e107::getTemplate('gallery');
		$template = array_change_key_case($template);
		$sc = e107::getScBatch('gallery', true);

		$text = "";

		if(defset('BOOTSTRAP') === true || defset('BOOTSTRAP') === 2) // Convert bootstrap3 to bootstrap2 compat.
		{
			$template['cat_start'] = str_replace('row', 'row-fluid', $template['cat_start']);
		}


		$text = e107::getParser()->parseTemplate($template['cat_start'], true, $sc);

		foreach($this->catList as $val)
		{
			$sc->setVars($val);
			$text .= e107::getParser()->parseTemplate($template['cat_item'], true, $sc);
		}

		$text .= e107::getParser()->parseTemplate($template['cat_end'], true, $sc);

		e107::getRender()->tablerender(LAN_PLUGIN_GALLERY_TITLE, $text);
	}


	function showImages($cat)
	{
		$plugPrefs = e107::getPlugConfig('gallery')->getPref();
		$mes = e107::getMessage();
		$tp = e107::getParser();
		$template = e107::getTemplate('gallery');
		$template = array_change_key_case($template);
		$sc = e107::getScBatch('gallery', true);

		if(defset('BOOTSTRAP') === true || defset('BOOTSTRAP') === 2) // Convert bootsrap3 to bootstrap2 compat.
		{
			$template['list_start'] = str_replace('row', 'row-fluid', $template['list_start']);
		}

		$sc->total = e107::getMedia()->countImages($cat);
		$sc->amount = varset($plugPrefs['perpage'], 12);
		$sc->curCat = $cat;
		$sc->from = ($_GET['frm']) ? intval($_GET['frm']) : 0;
		$orderBy = varset($plugPrefs['orderby'], 'media_id DESC');

		$list = e107::getMedia()->getImages($cat, $sc->from, $sc->amount, null, $orderBy);
		$catname = $tp->toHtml($this->catList[$cat]['media_cat_title'], false, 'defs');

		$inner = "";

		foreach($list as $row)
		{
			$sc->setVars($row);
			$inner .= $tp->parseTemplate($template['list_item'], true, $sc);
		}

		$text = $tp->parseTemplate($template['list_start'], true, $sc);
		$text .= $inner;
		$text .= $tp->parseTemplate($template['list_end'], true, $sc);

		e107::getRender()->tablerender(LAN_PLUGIN_GALLERY_TITLE, $mes->render() . $text);

	}

}


new gallery;

require_once(FOOTERF);
exit;
