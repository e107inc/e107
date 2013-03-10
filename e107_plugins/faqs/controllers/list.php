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
		),
	);
	
	public function init()
	{
		e107::lan('faqs', 'front');
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

		// request parameter based on filter (int match in this case, see $this->filter[all][category]) - SAFE to be used in a query
		$category = $this->getRequest()->getRequestParam('category');
		$where = '';
		if($category)
		{
			$where = " AND f.faq_parent={$category}";
		}
		$query = "
			SELECT f.*,cat.* FROM #faqs AS f 
			LEFT JOIN #faqs_info AS cat ON f.faq_parent = cat.faq_info_id 
			WHERE cat.faq_info_class IN (".USERCLASS_LIST."){$where} ORDER BY cat.faq_info_order,f.faq_order ";
		$sql->gen($query);
		
		$prevcat = "";
		$sc = e107::getScBatch('faqs', true);

		$text = $tp->parseTemplate($FAQ_START, true);
		
		$sc->counter = 1;
		while ($rw = $sql->db_Fetch())
		{
			$sc->setVars($rw);	
			
			if($rw['faq_info_order'] != $prevcat)
			{
				if($prevcat !='')
				{
					$text .= $tp->parseTemplate($FAQ_LISTALL['end'], true);
				}
				$text .= "\n\n<!-- FAQ Start ".$rw['faq_info_order']."-->\n\n";
				$text .= $tp->parseTemplate($FAQ_LISTALL['start'], true);
				$start = TRUE;
			}

			$text .= $tp->parseTemplate($FAQ_LISTALL['item'], true);
			$prevcat = $rw['faq_info_order'];
			$sc->counter++;
		}
		$text .= $tp->parseTemplate($FAQ_LISTALL['end'], true);
		$text .= $tp->parseTemplate($FAQ_END, true);

		$this->addTitle(LAN_PLUGIN_FAQS_FRONT_NAME);
		
		$this->addBody($text);
	}
}
