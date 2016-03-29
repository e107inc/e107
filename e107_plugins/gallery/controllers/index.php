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
 * @version     $Id$
 *    Ultra-simple Image-Gallery
 */
/*
 * THIS SCRIPT IS HIGHLY EXPERIMENTAL. USE AT OWN RISK.
 *
 */


class plugin_gallery_index_controller extends eControllerFront
{

	/**
	 * Plugin name - used to check if plugin is installed
	 * Set this only if plugin requires installation
	 * @var string
	 */
	protected $plugin = 'gallery';

	/**
	 * Default controller access
	 * @var integer
	 */
	protected $userclass = e_UC_PUBLIC;

	/**
	 * User input filter
	 * Format 'action' => array(var => validationArray)
	 * @var array
	 */
	protected $filter = array(
		'category' => array(
			'cat' => array('regex', '/[\w\pL\s\-+.,]+/u'),
		),
		'list'     => array(
			'cat' => array('regex', '/[\w\pL\s\-+.,]+/u'),
			'frm' => array('int'),
		),
	);

	/**
	 * @var array
	 */
	protected $catList;

	public function init()
	{
		e107::plugLan('gallery', 'front');

		e107::library('load', 'jquery.prettyPhoto');
		e107::css('gallery', 'css/gallery.css');
		e107::js('gallery', 'js/gallery.js');

		$this->catList = e107::getMedia()->getCategories('gallery');
	}

	public function actionIndex()
	{
		if(isset($_GET['cat']) && !empty($_GET['cat']))
		{
			$this->_forward('list');
		}
		else
		{
			$this->_forward('category');
		}
	}

	public function actionCategory()
	{
		$template = e107::getTemplate('gallery');
		$template = array_change_key_case($template);
		$sc = e107::getScBatch('gallery', true);

		$text = "";

		if(defset('BOOTSTRAP') === true || defset('BOOTSTRAP') === 2) // Convert bootsrap3 to bootstrap2 compat.
		{
			$template['cat_start'] = str_replace('row', 'row-fluid', $template['cat_start']);
		}

		$text = e107::getParser()->parseTemplate($template['cat_start'], true, $sc);

		foreach($this->catList as $val)
		{
			$sc->setVars($val);
			$text .= e107::getParser()->parseTemplate($template['cat_item'], true);
		}

		$text .= e107::getParser()->parseTemplate($template['cat_end'], true, $sc);

		if(isset($template['cat_caption']))
		{
			$title = e107::getParser()->parseTemplate($template['cat_caption'], true, $sc);

			$this->addTitle($title)->addBody($text);
		}
		else
		{
			$this->addTitle(LAN_PLUGIN_GALLERY_TITLE)->addBody($text);
		}


	}

	public function actionList()
	{
		$plugPrefs = e107::getPlugConfig('gallery')->getPref();

		$request = $this->getRequest();

		// use only filtered variables
		$cid = $request->getRequestParam('cat');

		if($cid && !isset($this->catList[$cid]))
		{
			// get ID by SEF
			$_cid = null;
			foreach($this->catList as $id => $row)
			{
				if($cid === $row['media_cat_sef'])
				{
					$_cid = $id;
					break;
				}
			}
			$cid = $_cid;
		}

		if(empty($cid) || !isset($this->catList[$cid]))
		{
			$this->_forward('category');
			return;
		}

		$tp = e107::getParser();
		$template = e107::getTemplate('gallery');
		$template = array_change_key_case($template);
		$sc = e107::getScBatch('gallery', true);

		if(defset('BOOTSTRAP') === true || defset('BOOTSTRAP') === 2) // Convert bootsrap3 to bootstrap2 compat.
		{
			$template['list_start'] = str_replace('row', 'row-fluid', $template['list_start']);
		}

		$sc->total = e107::getMedia()->countImages($cid);
		$sc->amount = varset($plugPrefs['perpage'], 12);
		$sc->curCat = $cid;
		$sc->from = $request->getRequestParam('frm', 0);

		$orderBy = varset($plugPrefs['orderby'], 'media_id DESC');

		$list = e107::getMedia()->getImages($cid, $sc->from, $sc->amount, null, $orderBy);
		$catname = $tp->toHtml($this->catList[$cid]['media_cat_title'], false, 'defs');
		$cat = $this->catList[$cid];

		$inner = "";

		foreach($list as $row)
		{
			$sc->setVars($row)
				->addVars($cat);

			$inner .= $tp->parseTemplate($template['list_item'], true, $sc);
		}

		$text = $tp->parseTemplate($template['list_start'], true, $sc);
		$text .= $inner;
		$text .= $tp->parseTemplate($template['list_end'], true, $sc);

		if(isset($template['list_caption']))
		{
			$title = $tp->parseTemplate($template['list_caption'], true, $sc);
			$this->addTitle($title)->addBody($text);
		}
		else
		{
			$this->addTitle($catname)
				->addTitle(LAN_PLUGIN_GALLERY_TITLE)
				->addBody($text);
		}

	}
}
 