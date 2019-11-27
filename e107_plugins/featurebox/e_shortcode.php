<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if (!defined('e107_INIT')) { exit; }

e107::includeLan(e_PLUGIN.'featurebox/languages/'.e_LANGUAGE.'_front_featurebox.php');


class featurebox_shortcodes// must match the plugin's folder name. ie. [PLUGIN_FOLDER]_shortcodes
{	
	protected $_categories = array();
	
	/**
	 * Available parameters (GET string format)
	 * - cols (integer): number of items per column, default 1
	 * - no_fill_empty (boolean): don't fill last column with empty items (if required), default 0
	 * - tablestyle (string): mode to be used with <code>tablerender()</code>, default 'featurebox'
	 * - notablestyle (null): if isset - disable <code>tablerender()</code>
	 * - force (boolean): force category model load , default false
	 * - ids (string): comma separated id list - load specific featurebox items, default empty 
	 * 
	 * @param string $parm parameters
	 * @param string $mod category template
	 * @example {FEATUREBOX=cols=2|tabs}
	 */
	function sc_featurebox($parm=null, $mod = '')
	{

		if($parm == null && $mod == '') // ie {FEATUREBOX}
		{
			$type 	= vartrue(e107::getPlugPref('featurebox','menu_category'),'bootstrap_carousel');
			$text = e107::getParser()->parseTemplate("{FEATUREBOX|".$type."}");
			
			return $text;
		}
		
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

		if(is_string($parm))
		{
			parse_str($parm, $parm);
		}
		
		$category = $this->getCategoryModel($ctemplate, (vartrue($parm['force']) ? true : false));
		$defopt = array(
			'force' => 0,
			'no_fill_empty' => 0,
			'tablestyle' => 'featurebox',
			'cols' => 1,	
			'ids' => '',
			'notablestyle' => null
		);
		// reset to default, update current
		$category->setParams($defopt)
			->updateParams($parm);

		if(!$category->hasData())
		{
			
			return '';
		}
		
		$tmpl = $this->getFboxTemplate($ctemplate);
		
		
		$type = vartrue($tmpl['js_type'],''); // Legacy support (prototype.js)
		
		if(vartrue($tmpl['js']))
		{		
			$tmp = explode(',', $tmpl['js']);
			
			foreach($tmp as $file)
			{
				e107::js('footer',$file,$type);	
			}
		}
		
		$tp = e107::getParser();
				
		if(vartrue($tmpl['js_inline']))
		{
			$data = $tp->toText($category->getData('fb_category_parms'));
			$jsInline = str_replace("{FEATUREBOX_PARMS}","{".trim($data)."}",$tmpl['js_inline']);
			e107::js('footer-inline', $jsInline, $type, 3);
		}
				
	
		
		// Fix - don't use tablerender if no result (category could contain hidden items)
		$ret = $this->render($category, $ctemplate, $parm);
		if(empty($ret))
		{
			e107::getMessage()->addDebug('Featurebox returned nothing.')->addDebug('Category: '.print_a($category,true))->addDebug('Template: '.$ctemplate)->addDebug('Param: '.print_a($parm,true));
			return '';
		}
		
		$ret = $tp->parseTemplate($tmpl['list_start'], true, $category).$ret.$tp->parseTemplate($tmpl['list_end'], true, $category);
		if(isset($parm['notablestyle']))
		{
			return $ret;
		}
		
		return e107::getRender()->tablerender(LAN_PLUGIN_FEATUREBOX_NAME, $ret, vartrue($parm['tablestyle'], 'featurebox'), true);
	}
	
	/**
	 * Render featurebox navigation
	 * Available parameters (GET string format)
	 * - loop (boolean): loop using 'nav_loop' template, default 0
	 * - base (string): template key prefix, default is 'nav'. Example: 'mynav' base key will search templates 'mynav_start', 'mynav_loop', 'mynav_end'.
	 * - nolimit (boolean): ignore 'limit' field , use 'total' items number for navigation looping
	 * - uselimit (boolean): ignore 'limit' field , use 'total' items number for navigation looping
	 * 
	 * @param string $parm parameters
	 * @param string $mod category template
	 */
	function sc_featurebox_navigation($parm=null, $mod = '')
	{
		// TODO cache	
		//TODO default $parm values. eg. assume 'tabs' when included in the 'tabs' template. 	
		
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
		
		$category = $this->getCategoryModel($ctemplate); 
		
		if(!$category->hasData())
		{
			return '';
		}
		$tree = $category->getItemTree();
		if($tree->isEmpty())
		{
			return '';
		}
		$tmpl = $this->getFboxTemplate($ctemplate);
		
		if($category->get('fb_category_random'))
		{
			unset($parm['loop']);
		}
		
		$base = vartrue($parm['base'], 'nav').'_';
		$tree_ids = array_keys($tree->getTree()); //all available item ids
		
		
		
		$ret = $category->toHTML(varset($tmpl[$base.'start']), true); 
		$cols = $category->getParam('cols', 1);
		
		if(isset($parm['loop']) && $tree->getTotal() > 0 && vartrue($tmpl[$base.'item']))
		{
			// loop for all
			if(isset($parm['nolimit'])) 
			{
				$total = $tree->getTotal();
			}
		
			// loop for limit number
			elseif(isset($parm['uselimit'])) 
			{
				$total = $category->sc_featurebox_category_limit() ? intval($category->sc_featurebox_category_limit()) : $tree->getTotal();
				if($total > $tree->getTotal())
				{
					$total = $tree->getTotal();
				}
			}
			// default - number based on all / limit (usefull for ajax navigation)
			else 
			{ 
				$total = ceil($tree->getTotal() / ($category->sc_featurebox_category_limit() ? intval($category->sc_featurebox_category_limit()) : $tree->getTotal()) );
			}
			if($cols > 1)
			{
				$total = ceil($total / $cols);
			}
				

			$model = clone $category;
			$item = new plugin_featurebox_item();
			$tmp = array();
			
			for ($index = 1; $index <= $total; $index++)
			{
				
				$nodeid = varset($tree_ids[($index - 1) * $cols], 0); 
				if($nodeid && $tree->hasNode($nodeid))
				{
					
					$model->setParam('counter', $index)
						->setParam('total', $total)
						->setParam('active', $index == varset($parm['from'], 1));
						
					$node = $tree->getNode($nodeid);
					
					e107::getScParser()->resetScClass('plugin_featurebox_category', $model);
					$node->setCategory($model)
						->setParam('counter', $index)
						->setParam('total', $total)
						->setParam('limit', $category->get('fb_category_limit'))
						;
	
					$tmp[] = $node->toHTML($tmpl[$base.'item'], true); 
					continue;
				}
				
				e107::getScParser()->resetScClass('plugin_featurebox_item', $item);
				$tmp[] = $model->setParam('counter', $index)
					->setParam('active', $index == varset($parm['from'], 1))
					->toHTML($tmpl[$base.'item'], true);
			} 
			$ret .= implode(varset($tmpl[$base.'separator']), $tmp);
			unset($model, $tmp);
		}
		$ret .= $category->toHTML(varset($tmpl[$base.'end']), true);
		
		// Moved to 'sc_featurebox' - as it wouldn't load js if navigation was not used. 
		/*
		$type = vartrue($tmpl['js_type'],''); // Legacy support (prototype.js)
		
		if(vartrue($tmpl['js']))
		{		
			$tmp = explode(',', $tmpl['js']);
			
			foreach($tmp as $file)
			{
				e107::js('footer',$file,$type);	
			}
		}
				
		if(vartrue($tmpl['js_inline']))
		{
			e107::js('inline',$tmpl['js_inline'],$type,3);
			// e107::getJs()->footerInline($tmpl['js_inline'], 3);
		}
		
		if(vartrue($tmpl['css']))
		{		
			$tmp = explode(',', $tmpl['css']);
			foreach($tmp as $file)
			{
				e107::css('url',$file,$type);	
			}
		}
		*/
		return $ret;
	}
	
	/**
	 * Get & Render featurebox items (custom)
	 * Available parameters (GET string format)
	 * - cols (integer): number of items per column, default 1
	 * - no_fill_empty (boolean): don't fill last column with empty items (if required), default 0
	 * - from (integer): start load at
	 * - limit (integer): load to
	 * 
	 * @param string $parm parameters
	 * @param string $mod category template
	 */
	function sc_featurebox_items($parm, $mod = '')
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
		
		$category = clone $this->getCategoryModel($ctemplate);
		if(!$category->hasData())
		{
			return '';
		}
		return $this->render($category, $ctemplate, $parm);
	}
	
	/**
	 * Render featurebox list
	 * @param plugin_featurebox_category $category
	 * @param string $ctemplate category template
	 * @param array $parm
	 */
	public function render($category, $ctemplate, $parm)
	{
		$tmpl = $this->getFboxTemplate($ctemplate);
		$cols = intval(vartrue($parm['cols'], 1));
		$limit = intval(varset($parm['limit'], $category->sc_featurebox_category_limit()));
		$from = (intval(vartrue($parm['from'], 1)) - 1) * $limit;
		$category->setParam('cols', $cols)
			->setParam('no_fill_empty', isset($parm['no_fill_empty']) ? 1 : 0)
			->setParam('limit', $limit)
			->setParam('from', $from);
			
		$tree = $category->getItemTree(true); 
		if($tree->isEmpty())
		{
			return '';
		}
		
		$total = $tree->getTotal();
		$category->setParam('total', $total);
		$counter = 1;
		$col_counter = 1;
		$cols_counter = 1; // column counter
		$ret = '';
		
		foreach ($tree->getTree() as $id => $node) 
		{
			$tmpl_item = e107::getTemplate('featurebox', 'featurebox', $node->get('fb_template'));
			if(!$tmpl_item) 
			{
				$tmpl_item = e107::getTemplate('featurebox', 'featurebox', 'default', true, true);
			}
			
			// reset column counter
			if($col_counter > $cols)
			{
				$col_counter = 1;
			}
			
			// item container (category template)
			$tmpl_item = $tmpl['item_start'].$tmpl_item.$tmpl['item_end'];

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
				elseif($cols != $col_counter && 1 != $col_counter)
				{
					$tmpl_item .= $tmpl['item_separator'];
				}
			}
			// no more items - clean & close
			else
			{
				$empty_cnt = $cols - $col_counter;
				if($empty_cnt > 0 && !$category->getParam('no_fill_empty'))
				{
					// empty items fill
					$tmp = new plugin_featurebox_item();
					$tmp->setCategory($category);
					$tmp_tmpl_item = $tmpl['item_separator'].$tmpl['item_start'].varset($tmpl['item_empty'], '<div><!-- --></div>').$tmpl['item_end'];
					for ($index = 1; $index <= $empty_cnt; $index++) 
					{
						$tmpl_item .= $tmp->setParam('counter', $counter + $index)
							->setParam('cols', $cols)
							->setParam('col_counter', $col_counter + $index)
							->setParam('cols_counter', $cols_counter)
							->setParam('limit', $category->get('fb_category_limit'))
							->setParam('total', $total)
							->toHTML($tmp_tmpl_item);
					}
					unset($tmp, $tmp_tmpl_item);
				}
				// add column end
				$tmpl_item .= varset($tmpl['col_end']);
			}
			
			$ret .= $node->setParam('counter', $counter)
				->setParam('cols', $cols)
				->setParam('col_counter', $col_counter)
				->setParam('cols_counter', $cols_counter)
				->setParam('limit', $category->get('fb_category_limit'))
				->setParam('total', $total)
				->setCategory($category)
				->toHTML($tmpl_item);
			
			if($cols == $col_counter)
			{
				$cols_counter++;
			}
			
			$counter++;
			$col_counter++;
		}
		return $ret;
	}
	
	/**
	 * Retrieve template array by category
	 * 
	 * @param string $ctemplate
	 * @return array
	 */
	public function getFboxTemplate($ctemplate)
	{
		$tmpl = e107::getTemplate('featurebox', 'featurebox_category', $ctemplate, 'front');  
		if(!$tmpl && e107::getTemplate('featurebox', 'featurebox_category', $ctemplate, false))
		{
			$tmpl = e107::getTemplate('featurebox', 'featurebox_category', $ctemplate, false); // plugin template
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
	
	/**
	 * Get category model by template
	 * @param string $template
	 * @return plugin_featurebox_category
	 */
	public function getCategoryModel($template, $force = false)
	{
		
		if(!isset($this->_categories[$template]))
		{		
			$this->_categories[$template] = new plugin_featurebox_category();
			$this->_categories[$template]->loadByTemplate($template, $force);
		}
		return $this->_categories[$template];
	}
}
