<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

/**
 *
 * @package     e107
 * @subpackage  faqs
 * @version     $Id$
 * @author      e107inc
 *
 *	FAQ plugin list controller
 */


class plugin_faqs_list_controller extends eControllerFront
{
	/**
	 * Plugin name - used to check if plugin is installed
	 * Set this only if plugin requires installation
	 * @var string
	 */
	protected $plugin = 'faqs';
	
	/**
	 * User input filter (_GET)
	 * Format 'action' => array(var => validationArray)
	 * @var array
	 */
	protected $filter = array(
		'all' => array(
			'category' => array('int', '0:'),
			'tag' => array('str', '2:'),
		),
	);
	
	public function init()
	{
		e107::lan('faqs', 'front');
		e107::css('faqs','faqs.css'); 
	}
	
	public function actionIndex()
	{
		$this->_forward('all');
	}
	
	public function actionAll()
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		//global $FAQ_START, $FAQ_END, $FAQ_LISTALL_START,$FAQ_LISTALL_LOOP,$FAQ_LISTALL_END;
		
		$FAQ_START = e107::getTemplate('faqs', true, 'start');
		$FAQ_END = e107::getTemplate('faqs', true, 'end');
		$FAQ_LISTALL = e107::getTemplate('faqs', true, 'all');
		$FAQ_CAPTION = e107::getTemplate('faqs', true, 'caption');

		// request parameter based on filter (int match in this case, see $this->filter[all][category]) - SAFE to be used in a query
		$category = $this->getRequest()->getRequestParam('category');
		$where = array();
		if($category)
		{
			$where[] = "f.faq_parent={$category}";
		}
		$tag = $this->getRequest()->getRequestParam('tag');
		if($tag)
		{
			$where[] = "FIND_IN_SET ('".$tp->toDB($tag)."', f.faq_tags)";
		}
		
		if($where)
		{
			$where = ' AND '.implode(' AND ' , $where);
		}
		else $where = '';

		$query = "
			SELECT f.*,cat.* FROM #faqs AS f 
			LEFT JOIN #faqs_info AS cat ON f.faq_parent = cat.faq_info_id 
			WHERE cat.faq_info_class IN (".USERCLASS_LIST."){$where} ORDER BY cat.faq_info_order,f.faq_order ";
		$sql->gen($query, false);
		
		$prevcat = "";
		$sc = e107::getScBatch('faqs', true);
		$sc->counter = 1;
		$sc->tag = htmlspecialchars($tag, ENT_QUOTES, 'utf-8');
		$sc->category = $category;

		$text = $tp->parseTemplate($FAQ_START, true, $sc);
		
		while ($rw = $sql->db_Fetch())
		{
			$sc->setVars($rw);	
			
			if($rw['faq_info_order'] != $prevcat)
			{
				if($prevcat !='')
				{
					$text .= $tp->parseTemplate($FAQ_LISTALL['end'], true, $sc);
				}
				$text .= "\n\n<!-- FAQ Start ".$rw['faq_info_order']."-->\n\n";
				$text .= $tp->parseTemplate($FAQ_LISTALL['start'], true, $sc);
				$start = TRUE;
			}

			$text .= $tp->parseTemplate($FAQ_LISTALL['item'], true, $sc);
			$prevcat = $rw['faq_info_order'];
			$sc->counter++;
			if($category) $meta = $rw;
		}
		$text .= ($start) ? $tp->parseTemplate($FAQ_LISTALL['end'], true, $sc) : "";
		$text .= $tp->parseTemplate($FAQ_END, true, $sc);
		
		// add meta data if there is parent category
		if(!empty($meta))
		{
			$response = $this->getResponse();
			if($meta['faq_info_metad'])
			{
				$response->addMetaDescription($meta['faq_info_metad']);
			}
			if($meta['faq_info_metak'])
			{
				$response->addMetaKeywords($meta['faq_info_metak']);
			}
		}
		
		$caption = ($FAQ_CAPTION) ? $FAQ_CAPTION : LAN_PLUGIN_FAQS_FRONT_NAME;
	
		$this->addTitle($caption);
		
		$this->addBody($text);
	}
}
