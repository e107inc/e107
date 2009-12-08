<?php
/*
* Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php,v 1.2 2009-12-08 17:21:30 secretr Exp $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

class featurebox_shortcodes // must match the plugin's folder name. ie. [PLUGIN_FOLDER]_shortcodes
{	
	function sc_featurebox($parm, $mod = '')
	{
		// TODO cache
		if(!$mod)
		{
			$clayout = 'default';
		}
		else
		{
			$clayout = $mod;
		}
		
		$category = new plugin_featurebox_category();
		$category->loadByLayout($clayout);
		if(!$category->hasData())
		{
			return '';
		}
		
		$tree = $category->getItemTree();
		if($tree->isEmpty())
		{
			return '';
		}
		
		$tmpl = e107::getTemplate('featurebox', 'layout/'.$category->get('fb_category_layout'));
		if(!$tmpl)
		{
			$tmpl = e107::getTemplate('featurebox', 'layout/default');
		}
		
		$tp = e107::getParser();
		$ret = array();
		
		$counter = 1;
		foreach ($tree->getTree() as $id => $node) 
		{
			$tmpl_item = e107::getTemplate('featurebox', 'featurebox', $node->get('fb_template'));
			if(!$tmpl_item) 
			{
				$tmpl_item = e107::getTemplate('featurebox', 'featurebox', 'default');
			}
			
			$ret[] = $node->setParam('counter', $counter)
				->setCategory($category)
				->toHTML($tmpl_item);
				
			//$ret[] = $node->toHTML($tmpl_item);
			$counter++;
		}
		
		return $tp->parseTemplate($tmpl['list_start'], true, $category).implode($ret['item_separator'], $ret).$tp->parseTemplate($tmpl['list_end'], true, $category);
	}
}
