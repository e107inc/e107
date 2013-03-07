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
 
class plugin_faqs_list_controller extends eController
{
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

		$query = "SELECT f.*,cat.* FROM #faqs AS f LEFT JOIN #faqs_info AS cat ON f.faq_parent = cat.faq_info_id WHERE cat.faq_info_class IN (".USERCLASS_LIST.") ORDER BY cat.faq_info_order,f.faq_order ";
		$sql->gen($query);
		
		$prevcat = "";
		$sc = e107::getScBatch('faqs', true);

		$text = $tp->parseTemplate($FAQ_START, true);
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

		}
		$text .= $tp->parseTemplate($FAQ_LISTALL['end'], true);
		$text .= $tp->parseTemplate($FAQ_END, true);

		$this->addTitle(FAQLAN_FAQ);
		
		$this->addBody($text);
	}
}
