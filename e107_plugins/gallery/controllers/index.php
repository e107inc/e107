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
 * @subpackage	frontend
 * @version     $Id$
 *	Ultra-simple Image-Gallery
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
			'cat' => array('regex', '/[\w\pL\s\-+.,\']+/u'),
		),
		'list' => array(
			'cat' => array('regex', '/[\w\pL\s\-+.,\']+/u'),
			'frm' => array('int'),
		),
	);
	
	/**
	 * @var array
	 */
	protected $catList;
	
	public function init()
	{
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
		$template 	= e107::getTemplate('gallery');	
		$sc 		= e107::getScBatch('gallery',TRUE);
		
		$text = "";		
		foreach($this->catList as $val)
		{
			$sc->setParserVars($val);	
			$text .= e107::getParser()->parseTemplate($template['CAT_ITEM'],TRUE);
		}	
		$text = $template['CAT_START'].$text.$template['CAT_END'];
		$this->addBody($text);
	}
	
	public function actionList()
	{
		$request = $this->getRequest();
		
		// use only filtered variables
		$cid = $request->getRequestParam('cat');
		
		if($cid && !isset($this->catList[$cid]))
		{
			// get ID by SEF
			$_cid = null;
			foreach ($this->catList as $id => $row) 
			{
				if($cid === $row['media_cat_title'])
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
		
		$tp			= e107::getParser();			
		$template 	= e107::getTemplate('gallery');
		$sc 		= e107::getScBatch('gallery',TRUE);
					
		$sc->total 	= e107::getMedia()->countImages($cid);
		$sc->amount = e107::getPlugPref('gallery','perpage', 12); // TODO Add Pref. amount per page. 
		$sc->curCat = $cid;
		$sc->from 	= $request->getRequestParam('frm', 0);
		
		$list 		= e107::getMedia()->getImages($cid,$sc->from,$sc->amount);
		$catname	= $tp->toHtml($this->catList[$cid]['media_cat_title'],false,'defs');
		$cat = $this->catList[$cid];
		
		$inner = "";	
		
		foreach($list as $row)
		{
			$sc->setVars($row)
				->addVars($cat);	

			$inner .= $tp->parseTemplate($template['LIST_ITEM'],TRUE);
		}
					
		$text = $tp->parseTemplate($template['LIST_START'],TRUE);
		$text .= $inner; 	
		$text .= $tp->parseTemplate($template['LIST_END'],TRUE);
		
		$this->addBody($text);
	}
}
 