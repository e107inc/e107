<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Menu Class
 *
*/

if(!defined('e107_INIT'))
{
	exit();
}

/**
 * Retrieve and render site menus
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author Cameron
 * @copyright Copyright (c) 2009, e107 Inc.
 *
 */
class e_menu
{
	/**
	 * Runtime cached menu data
	 *
	 * @var array
	 */
	public $eMenuActive = array();

	/**
	 * Visibility check cache
	 *
	 * @var array
	 */
	protected $_visibility_cache = array();

	/**
	 * @var null
	 */
	protected $_current_menu = null;

	/**
	 * @var array
	 */
	protected $_current_parms = array();

	/**
	 * Params of all active menus.
	 * @var array
	 */
	protected $_menu_parms = array();

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
	}

	/**
	 * Retrieve menus, check visibility against
	 * current user classes and current page url
	 *
	 */
	public function init()
	{
		global $_E107;

		if(vartrue($_E107['cli']))
		{
			return;
		}
		
		//	print_a($eMenuArea);
		if(varset($_SERVER['E_DEV_MENU']) == 'true') // New in v2.x Experimental
		{
			$layouts = e107::getPref('menu_layouts');
			if(!is_array($layouts))
			{
				$converted = $this->convertMenuTable();
				e107::getConfig('core')->set('menu_layouts', $converted)->save();
			}
			
			$eMenuArea = $this->getData(THEME_LAYOUT);
			//print_a($eMenuArea);
		}
		else // standard DB 'table' method.
		{
			$eMenuArea = $this->getDataLegacy();
		}

		
		
		$total = array();
		foreach($eMenuArea as $area => $val)
		{
			foreach($val as $row)
			{
				if($this->isVisible($row))
				{
					$path = str_replace("/", "", $row['menu_path']);
					if(!isset($total[$area]))
					{
						$total[$area] = 0;
					}
					$this->eMenuActive[$area][] = $row;

					if(!empty($row['menu_parms']))
					{
						$key = $row['menu_name'];
						$this->_menu_parms[$area][$key][] = $row['menu_parms'];
					}

					$total[$area]++;
				}
			}
		}

	
		


		e107::getRender()->eMenuTotal = $total;
	}

	/** 
	 * Convert from v1.x e107_menu table to v2.x $pref format. 
	 */
	function convertMenuTable()
	{
		$sql = e107::getDb();
		
		$sql->select('menus','*','menu_location !=0 ORDER BY menu_location,menu_order');
		$data = array();

		while($row = $sql->fetch())
		{
			$layout 	= vartrue($row['menu_layout'],'default');	
			$location 	= $row['menu_location'];
			$data[$layout][$location][] = array('name'=>$row['menu_name'],'class'=> intval($row['menu_class']),'path'=>$row['menu_path'],'pages'=>$row['menu_pages'],'parms'=>$row['menu_parms']);	
		}
		
		return $data;		
	}


	/**
	 * Return the preferences/parms for the current menu.
	 * @return array
	 */
	public function pref()
	{
		return (empty($this->_current_parms)) ?  array() : $this->_current_parms;
	}


	/**
	 * Return the parameters of an active Menu.
	 * @param string $menuName
	 * @param int $area
	 * @example $parms = $tmp->getParams('news_months_menu',1);
	 * @return array|bool
	 */
	public function getParams($menuName, $area)
	{

		if(empty($area) || empty($menuName))
		{
			return false;
		}

		if(!empty($this->_menu_parms[$area][$menuName]))
		{
			$arr = array();
			foreach($this->_menu_parms[$area][$menuName] as $val)
			{
				$arr[] = e107::unserialize($val);
			}

			return $arr;
		}

		return false;
	}


	/**
	 * Experimental V2 Menu Re-Write - retrieve Menu data from $pref['menu_layouts']
	 */
	protected function getData($layout)
	{
		$mpref = e107::getPref('menu_layouts');
		
		if(!varset($mpref[$layout]))
		{
			return array();	
		}
		
		foreach($mpref[$layout] as $area=>$v)
		{
			$c = 1;
					
			foreach($v as $val)
			{
				$class = intval($val['class']);
				
				if(!check_class($class))
				{
					continue;	
				}
				
				$ret[$area][] = array(
					'menu_id'		=> $c,
					'menu_name'		=> $val['name'],
					'menu_location'	=> $area,
					'menu_class'	=> $class,
					'menu_order'	=> $c,
					'menu_pages'	=> $val['pages'],
					'menu_path'		=> $val['path'],
					'menu_layout'	=>  '',
					'menu_parms'	=> $val['parms']

				);

				$c++;
			}
				
			
		}
		
		
			// print_a($ret);
				
		return $ret;	
		
	}


	/**
	 * Set Parms for a specific menu.
	 * @param string $plugin ie. plugin folder name.
	 * @param string $menu menu name. including the _menu but not the .php
	 * @param array $parms
	 * @param string|int $location default 'all' or  a menu area number..
	 * @return int|boolean number of records updated or false.
	 */
	public function setParms($plugin, $menu, $parms=array(), $location = 'all')
	{
		$qry = 'menu_parms="'.e107::serialize($parms).'" WHERE menu_parms="" AND menu_path="'.$plugin.'/" AND menu_name="'.$menu.'" ';
		$qry .= ($location != 'all') ? 'menu_location='.intval($location) : '';

		return  e107::getDb()->update('menus', $qry);
	}


	/**
	 * @param int $id menu_id
	 * @param array $parms
	 * @return mixed
	 */
	public function updateParms($id, $parms)
	{
		$model = e107::getModel();
		$model->setModelTable("menus");
		$model->setFieldIdName("menu_id");
		$model->setDataFields(array('menu_parms'=>'json'));

		$model->load($id, true);

		$d = $model->get('menu_parms');

		$model->setPostedData('menu_parms', e107::unserialize($d));

		foreach($parms as $key=>$value)
		{
			if(!is_array($value))
			{
				$model->setPostedData('menu_parms/'.$key, $value);
			}
			else
			{
				$lang = key($value);
				$val = $value[$lang];
				$model->setPostedData('menu_parms/'.$key.'/'.$lang, $val);
			}
		}


		return $model->save();

		// return $model;

	}




	/**
	 * Add a Menu to the Menu Table.
	 * @param string $plugin folder name
	 * @param string $menufile name without the .php
	 * @return bool|int
	 */
	public function add($plugin, $menufile)
	{
		$sql = e107::getDb();

		if(empty($plugin) || empty($menufile))
		{
			return false;
		}

		if($sql->select('menus', 'menu_id' , 'menu_path="'.$plugin.'/" AND menu_name="'.$menufile.'" LIMIT 1'))
		{
			return false;
		}

		$insert = array(
			'menu_id'       => 0,
			'menu_name'     => $menufile,
			'menu_location' => 0,
			'menu_order'    => 0,
			'menu_class'    => 0,
			'menu_pages'    => 0,
			'menu_path'     => $plugin."/",
			'menu_layout'   => '',
			'menu_parms'    => ''
		);

		return  $sql->insert('menus', $insert);


	}


	/**
	 * Remove a menu from the Menu table.
	 * @param string $plugin folder name
	 * @param string $menufile
	 * @return int
	 */
	public function remove($plugin, $menufile=null)
	{
		$qry = 'menu_path="'.$plugin.'/" ';

		if(!empty($menufile))
		{
			$qry .= ' AND menu_name="'.$menufile.'" ';
		}

		return e107::getDb()->delete('menus', $qry);
	}


	/** 
	 * Function to retrieve Menu data from tables.
	 */
	private function getDataLegacy()
	{
		$sql = e107::getDb();
		$menu_layout_field = THEME_LAYOUT!=e107::getPref('sitetheme_deflayout') ? THEME_LAYOUT : "";
		
	//	e107::getCache()->CachePageMD5 = md5(e_LANGUAGE.$menu_layout_field); // Disabled by line 93 of Cache class. 
		//FIXME add a function to the cache class for this.

		$cacheData = e107::getCache()->retrieve_sys("menus_".USERCLASS_LIST."_".md5(e_LANGUAGE.$menu_layout_field));

	//	$menu_data = json_decode($cacheData,true);
		$menu_data = e107::unserialize($cacheData);

		$eMenuArea = array();
		// $eMenuList = array();
		//	$eMenuActive	= array();  // DEPRECATED
		
		
		if(empty($menu_data) || !is_array($menu_data))
		{
			$menu_qry = 'SELECT * FROM #menus WHERE menu_location > 0 AND menu_class IN ('.USERCLASS_LIST.') AND menu_layout = "'.$menu_layout_field.'" ORDER BY menu_location,menu_order';
			
			if($sql->gen($menu_qry))
			{
				while($row = $sql->fetch())
				{
					$eMenuArea[$row['menu_location']][] = $row;
				}
			}
			
			$menu_data['menu_area'] = $eMenuArea;

			$menuData = e107::serialize($menu_data,'json');

			e107::getCache()->set_sys('menus_'.USERCLASS_LIST.'_'.md5(e_LANGUAGE.$menu_layout_field), $menuData);
			
		}
		else
		{
			$eMenuArea = $menu_data['menu_area'];
		}	
		
		
		
		return $eMenuArea;
	}

	/**
	 * Returns true if a menu is currently active. 
	 * @param string $menuname (without the '_menu.php' )
	 */
	function isLoaded($menuname)
	{
		if(empty($menuname))
		{
			return false;	
		}
		
		foreach($this->eMenuActive as $area)
		{
			foreach($area as $menu)
			{
				if($menu['menu_name'] == $menuname."_menu")
				{
					return true;	
				}
				
			}
		
		}	
		
		return false;
	}


	protected function isFrontPage()
	{
		if(e_REQUEST_SELF == SITEURL)
		{
			return true;
		}

		return false;
	}



	/**
	 * Check visibility of a menu against URL
	 *
	 * @param array $row menu data
	 * @return boolean
	 */
	protected function isVisible($row, $url = '')
	{
		$iD = varset($row['menu_id']);

		if(isset($this->_visibility_cache[$iD]))
		{
			return $this->_visibility_cache[$iD];
		}

		$show_menu = TRUE;
		$tp = e107::getParser();
		if($row['menu_pages'])
		{
			list ($listtype, $listpages) = explode("-", $row['menu_pages'], 2);
			$pagelist = explode("|", $listpages);
			// TODO - check against REQUEST_URI, see what would get broken
			$check_url = $url ? $url : ($_SERVER['REQUEST_URI'] ? SITEURLBASE.$_SERVER['REQUEST_URI'] : e_SELF.(e_QUERY ? "?".e_QUERY : ''));

			switch($listtype)
			{
				case '1': //show menu
					$show_menu = false;

					foreach($pagelist as $p)
					{
						if($p == 'FRONTPAGE' && $this->isFrontPage())
						{
							$show_menu = true;
							break;
						}

						$p = $tp->replaceConstants($p, 'full');
						if(substr($p, -1)==='!')
						{
							$p = substr($p, 0, -1);
							if(substr($check_url, strlen($p)*-1) == $p)
							{
								$show_menu = true;
								break 2;
							}
						}
						elseif(strpos($check_url, $p) !== false)
						{
							$show_menu = true;
							break 2;
						}
					}
					break;
				case '2': //hide menu
					$show_menu = true;
					foreach($pagelist as $p)
					{
						if($p == 'FRONTPAGE' && $this->isFrontPage())
						{
							$show_menu = false;
							break;
						}


						$p = $tp->replaceConstants($p, 'full');
						if(substr($p, -1)=='!')
						{
							$p = substr($p, 0, -1);
							if(substr($check_url, strlen($p)*-1) == $p)
							{
								$show_menu = false;
								break 2;
							}
						}
						elseif(strpos($check_url, $p) !== false)
						{
							$show_menu = false;
							break 2;
						}
					}
					break;
			} //end switch
		} //endif menu_pages

		$this->_visibility_cache[$iD] = $show_menu;
		return $show_menu;
	}

	/**
	 * Render menu area
	 *
	 * @param string $parm
	 * @return string
	 */
	public function renderArea($parm = '')
	{
		global $sql, $ns, $tp, $sc_style;
		global $error_handler;
				
		$e107 = e107::getInstance();

		$tmp = explode(':', $parm);
		
		
		$buffer_output = (E107_DBG_INCLUDES) ? false : true; // Turn off when trouble-shooting includes. Default - return all output.
		

		if(isset($tmp[1])&&$tmp[1]=='echo')
		{
			$buffer_output = false;
		}
		if(!array_key_exists($tmp[0], $this->eMenuActive))
		{
			return;
		}
		if($buffer_output)
		{
			ob_start();
		}
		
		e107::getRender()->eMenuArea = $tmp[0];
		foreach($this->eMenuActive[$tmp[0]] as $row)
		{
			$this->renderMenu($row['menu_path'], $row['menu_name'], $row['menu_parms']);
		}
		e107::getRender()->eMenuCount = 0;
		e107::getRender()->eMenuArea = null;
		if($buffer_output)
		{
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
	}

	/**
	 * Render menu
	 *
	 * @param string $mpath menu path
	 * @param string $mname menu name
	 * @param string $parm menu parameters
	 * @param boolean $return
	 * return string if required
	 */
	public function renderMenu($mpath, $mname='', $parm = '', $return = false)
	{
	//	global $sql; // required at the moment.


		global $sc_style, $e107_debug;
				

		$sql        = e107::getDb();
		$ns         = e107::getRender();
		$tp         = e107::getParser();
		$e107cache  = e107::getCache(); // Often used by legacy menus.

		if($tmp = e107::unserialize($parm)) // support e_menu.php e107 serialized parm.
		{
			$parm = $tmp;
			unset($tmp);
		}

		$this->_current_parms = $parm;
		$this->_current_menu = $mname;


		if($return)
		{
			ob_start();
		}

		if(e_DEBUG === true)
		{
			echo "\n<!-- Menu Start: ".$mname." -->\n";
		}
		e107::getDB()->db_Mark_Time($mname);
		
		if(is_numeric($mpath) || ($mname === false)) // Custom Page/Menu 
		{
			$query = ($mname === false) ? "menu_name = '".$mpath."' " :  "page_id=".intval($mpath)." "; // load by ID or load by menu-name (menu_name)
			
			$sql->select("page", "*", $query);
			$page = $sql->fetch();
			
			if(!empty($page['menu_class']) && !check_class($page['menu_class']))
			{
				echo "\n<!-- Menu not rendered due to userclass settings -->\n";
				return;	
			}
			
			$caption = (vartrue($page['menu_icon'])) ? $tp->toIcon($page['menu_icon']) : '';
			$caption .= $tp->toHTML($page['menu_title'], true, 'parse_sc, constants');
			
			if(vartrue($page['menu_template'])) // New v2.x templates. see core/menu_template.php 
			{
				$template = e107::getCoreTemplate('menu',$page['menu_template'],true,true);	// override and merge required. ie. when menu template is not in the theme, but only in the core. 
				$page_shortcodes = e107::getScBatch('page',null,'cpage');  
				$page_shortcodes->setVars($page);
				  
				$head = $tp->parseTemplate($template['start'], true, $page_shortcodes);
				$foot = $tp->parseTemplate($template['end'], true, $page_shortcodes);
				  
			// 	print_a($template['body']);           
				$text = $head.$tp->parseTemplate($template['body'], true, $page_shortcodes).$foot;
			// 	echo "TEMPLATE= ($mpath)".$page['menu_template'];

				$ns->setUniqueId('cmenu-'.$page['menu_name']);
				$caption .= e107::getForm()->instantEditButton(e_ADMIN_ABS."cpage.php?mode=menu&action=edit&tab=2&id=".intval($page['page_id']),'J');

				$ns->tablerender($caption, $text, 'cmenu-'.$page['menu_template']);
			}
			else 
			{				
				$text = $tp->toHTML($page['menu_text'], true, 'parse_sc, constants');
				$ns->setUniqueId('cmenu-'.$page['menu_name']);

				$ns->tablerender($caption, $text, 'cmenu');
			}
			
		}
		else
		{
			// not sure what would break this, but it's good idea to go away
			e107::loadLanFiles($mpath);
			
			//include once is not an option anymore
			//e107_include will break many old menus (evil globals), so we'll wait for a while...
			//e107_include(e_PLUGIN.$mpath."/".$mname.".php");
			//if(substr($mpath,-1)!='/')
			//{
			//	$mpath .= '/';
			//}

			$mpath = trim($mpath, '/').'/'; // faster...

			$id = e107::getForm()->name2id($mpath . $mname);
			$ns->setUniqueId($id);


			$pref = e107::getPref(); // possibly used by plugin menu.


			$e107_debug ? include(e_PLUGIN.$mpath.$mname.'.php') : @include(e_PLUGIN.$mpath.$mname.'.php');
		}
		e107::getDB()->db_Mark_Time("(After ".$mname.")");

		if(e_DEBUG === true)
		{
			echo "\n<!-- Menu End: ".$mname." -->\n";
		}

		if($return)
		{
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}
	}
}
