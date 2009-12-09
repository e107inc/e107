<?php
/*
* Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php,v 1.3 2009-12-09 18:33:37 secretr Exp $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if (!defined('e107_INIT')) { exit; }

e107::includeLan(e_PLUGIN.'featurebox/languages/'.e_LANGUAGE.'_front_featurebox.php');

class featurebox_shortcodes // must match the plugin's folder name. ie. [PLUGIN_FOLDER]_shortcodes
{	
	/**
	 * Available parameters (GET string format)
	 * - cols (integer): number of items per column, default 1
	 * - no_fill_empty (boolean): don't fill last column with empty items (if required), default 0
	 * - tablestyle (string): mode to be used with <code>tablerender()</code>, default 'featurebox'
	 * - notablestyle (null): if isset - disable <code>tablerender()</code>
	 * 
	 * @param string $parm parameters
	 * @param string $mod category template
	 */
	function sc_featurebox($parm, $mod = '')
	{
		// TODO cache
		if(!e107::isInstalled('featurebox')) //just in case
		{
			return '';
		}
		
		if(!$mod)
		{
			$ctemplate = 'default';
		}
		else
		{
			$ctemplate = $mod;
		}
		
		parse_str($parm, $parm);
		
		$category = new plugin_featurebox_category();
		$category->loadByTemplate($ctemplate);
		if(!$category->hasData())
		{
			return '';
		}
		
		$tree = $category->getItemTree();
		if($tree->isEmpty())
		{
			return '';
		}
		
		$tmpl = $this->getFboxTemplate($category);
		
		$tp = e107::getParser();
		$ret = array();
		
		$cols = intval(vartrue($parm['cols'], 1));
		$counter = 1;
		$col_counter = 1;
		$total = count($tree->getTree());
		foreach ($tree->getTree() as $id => $node) 
		{
			$tmpl_item = e107::getTemplate('featurebox', 'featurebox', $node->get('fb_template'));
			if(!$tmpl_item) 
			{
				$tmpl_item = e107::getTemplate('featurebox', 'featurebox', 'default');
			}
			
			// reset column counter
			if($col_counter > $cols)
			{
				$col_counter = 1;
			}

			// add column start
			if(1 == $col_counter && vartrue($tmpl['col_start']))
			{
				$tmpl_item = $tmpl['col_start'].$tmpl_item;
			}
			
			// there is more
			if(($total - $counter) > 0)
			{
				// add column end if column end reached
				if($cols == $col_counter && vartrue($tmpl['col_end']))
				{
					$tmpl_item .= $tmpl['col_end'];
				}
				// else add item separator
				else 
				{
					$tmpl_item .= $ret['item_separator'];
				}
			}
			// no more items - clean & close
			else
			{
				$empty_cnt = $cols - $col_counter;
				if($empty_cnt > 0 && !isset($parm['no_fill_empty']))
				{
					// empty items fill
					for ($index = 1; $index <= $empty_cnt; $index++) 
					{
						$tmpl_item .= $ret['item_separator'].varset($ret['item_empty'], '<div><!-- --></div>');
					}
				}
				// add column end
				$tmpl_item .= varset($tmpl['col_end']);
			}
			
			$ret[$counter] = $node->setParam('counter', $counter)
				->setCategory($category)
				->toHTML($tmpl_item);
				
			
			$counter++;
			$col_counter++;
		}
		
		$ret = $tp->parseTemplate($tmpl['list_start'], true, $category).implode('', $ret).$tp->parseTemplate($tmpl['list_end'], true, $category);
		if(isset($parm['notablestyle']))
		{
			return $ret;
		}
		
		return e107::getRender()->tablerender(FBLAN_01, $ret, vartrue($parm['tablestyle'], 'featurebox'), true);
	}
	
	/**
	 * Render featurebox navigation
	 * Available parameters (GET string format)
	 * - cols (integer): number of items per column, default 1
	 * - no_fill_empty (boolean): don't fill last column with empty items (if required), default 0
	 * - tablestyle (string): mode to be used with <code>tablerender()</code>, default 'featurebox'
	 * - notablestyle (null): if isset - disable <code>tablerender()</code>
	 * 
	 * @param string $parm parameters
	 * @param string $mod category template
	 */
	function sc_featurebox_navigation($parm, $mod = '')
	{
		// TODO cache
		if(!e107::isInstalled('featurebox')) //just in case
		{
			return '';
		}
		
		if(!$mod)
		{
			$ctemplate = 'default';
		}
		else
		{
			$ctemplate = $mod;
		}
		
		parse_str($parm, $parm);
		
		$category = new plugin_featurebox_category();
		$category->loadByTemplate($ctemplate);
		if(!$category->hasData())
		{
			return '';
		}
		
		$tree = $category->getItemTree();
		if($tree->isEmpty())
		{
			return '';
		}
		
		$tmpl = $this->getFboxTemplate($category);
	}
	
	/**
	 * Retrieve template array by category
	 * 
	 * @param plugin_featurebox_category $category
	 * @return array
	 */
	public function getFboxTemplate($category)
	{
		$tmpl = e107::getTemplate('featurebox', 'featurebox_category', $category->get('fb_category_template'), 'front');  
		if(!$tmpl && e107::getTemplate('featurebox', 'featurebox_category', $category->get('fb_category_template'), false))
		{
			$tmpl = e107::getTemplate('featurebox', 'featurebox_category', $category->get('fb_category_template'), false); // plugin template
		}
		elseif(!$tmpl && e107::getTemplate('featurebox', 'featurebox_category', 'default'))
		{
			$tmpl = e107::getTemplate('featurebox', 'featurebox_category', 'default'); // theme/plugin default template
		}
		elseif(!$tmpl)
		{
			$tmpl = e107::getTemplate('featurebox', 'featurebox_category', 'default', false); //plugin default
		}
		return $tmpl;
	}
}
