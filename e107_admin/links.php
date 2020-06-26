<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Site navigation administration
 *
 */

require_once("../class2.php");

if (!getperms("I"))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('links', true);

e107::css('inline', " td .label-warning { margin-left:30px } ");


class links_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'links_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'links_admin_form_ui',
			'uipath' 		=> null
		)
	);

	protected $adminMenu = array(
		'main/list'		=> array('caption'=> LAN_MANAGE, 'perm' => 'I'),
		'main/create' 	=> array('caption'=> LAN_CREATE, 'perm' => 'I'),
		'main/prefs' 	=> array('caption'=> LAN_OPTIONS, 'perm' => 'I'),
		'main/tools'	=> array('caption'=> LINKLAN_4, 'perm' => 'I')
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'
	);

	protected $menuTitle = ADLAN_138;

		protected $adminMenuIcon = 'e-links-24';
}

class links_admin_ui extends e_admin_ui
{
	protected $pluginTitle 	= ADLAN_138;
	protected $pluginName 	= 'core';
	protected $table 		= "links";
	protected $listQry 		= '';
	protected $pid 			= "link_id";
	protected $perPage 		= 0;
	protected $batchDelete 	= true;
	protected $batchCopy 	= true;
	protected $listOrder = 'link_category,link_order ASC';
	protected $sortField	= 'link_order';
    
    //FIXME TOOD - Filter out 'unassigned' entries by default. 

	public $current_parent = 0;
	public $sublink_data = null;

	protected $fields = array(
		'checkboxes' 		=> array('title'=> '',				'width' => '3%',		'forced' => true,	'thclass'=>'center first',	'class'=>'center first'),
		'link_button'		=> array('title'=> LAN_ICON, 		'type'=>'icon',			'width'=>'5%',		'thclass'=>'center',		'class'=>'center',	'readParms'=>array('legacy'=>'{e_IMAGE}icons/'),	'writeParms'=>'glyphs=1'),
		'link_id'			=> array('title'=> LAN_ID, 			'type'=>'method',		'readParms'=>'',	'noedit'=>TRUE),
		'link_name'			=> array('title'=> LAN_NAME,		'type'=>'text',			'inline'=>true,		'required'=>false,		'validate'=>false,	'width'=>'auto', 'writeParms'=>array('size'=>'xlarge')), // not required as only an icon may be used.
		'link_category'		=> array('title'=> LAN_TEMPLATE,	'type'=>'dropdown',		'inline'=>true,		'batch'=>true,			'filter'=>true,		'width'=>'auto', 'writeParms'=>array('size'=>'xlarge')),

		'link_parent'		=> array('title'=> LAN_PARENT,		'type' => 'method',		'data'=>'int',		'width'=>'auto',		'batch'=>true,		'filter'=>true,		'thclass'=>'left first', 'writeParms'=>array('size'=>'xlarge')),
		'link_url'	   		=> array('title'=> LAN_URL, 		'width'=>'auto', 'type'=>'method', 'inline'=>true, 'required'=>true,'validate' => true, 'writeParms'=>'size=xxlarge'),
		'link_sefurl' 		=> array('title'=> LAN_SEFURL, 		'type' => 'method', 'inline'=>false, 'width' => 'auto', 'help'=>LCLAN_107),
		'link_class' 		=> array('title'=> LAN_USERCLASS, 	'type' => 'userclass','inline'=>true, 'writeParms' => 'classlist=public,guest,nobody,member,classes,admin,main', 'batch'=>true, 'filter'=>true, 'width' => 'auto'),
		'link_description' 	=> array('title'=> LAN_DESCRIPTION,	'type' => 'textarea', 'width' => 'auto'), // 'method'=>'tinymce_plugins',  ?
		'link_order' 		=> array('title'=> LAN_ORDER, 		'type' => 'number', 'width' => 'auto', 'nolist'=>false, 'inline' => true),
		'link_open'			=> array('title'=> LCLAN_19, 		'type' => 'dropdown', 'inline'=>true, 'width' => 'auto', 'batch'=>true, 'filter'=>true, 'thclass' => 'left first', 'writeParms'=>array('size'=>'xlarge')),
		'link_function'		=> array('title'=> LCLAN_105, 		'type' => 'method', 'data'=>'str', 'width' => 'auto', 'thclass' => 'left first'),
		'link_owner'		=> array('title'=> LCLAN_106,		'type' => 'hidden', 'data'=>'str'),
		'options' 			=> array('title'=> LAN_OPTIONS, 	'type'	=> null, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class'=>'center','readParms'=>'sort=1') // quick workaround
	);

	protected $fieldpref =  array('checkboxes','link_button', 'link_id','link_name','link_sefurl','link_class','link_category','options');

	protected $prefs = array(
		'linkpage_screentip'	=> array('title'=>LCLAN_78,	'type'=>'boolean', 'help'=>LCLAN_79),
		'sitelinks_expandsub'	=> array('title'=>LCLAN_80,	'type'=>'boolean', 'help'=>LCLAN_81)
	);


	/**
	 * Runtime cache of all links array
	 * @var array
	 */
	protected $_link_array	= null;
	
	
	function afterCreate($newdata,$olddata, $id) //FIXME needs to work after inline editing too. 
	{
		e107::getCache()->clearAll('content');	
	}
	
	function afterUpdate($newdata,$olddata, $id) //FIXME needs to work after inline editing too. 
	{
		e107::getCache()->clearAll('content');
	}	
	
	

	function init()
	{
		$this->fields['link_category']['writeParms']['optArray'] = array(
			1	=> "1 - Main",
			2	=> "2 - Sidebar",
			3	=> "3 - Footer",
			4	=> "4 - Alt",
			5	=> "5 - Alt",
			6	=> "6 - Alt", // If more than 6 are required, then something is not right with the themeing method. 
	//		7	=> "7 - Alt",
	//		8	=> "8 - Alt",
	//		9	=> "9 - Alt",
	//		10	=> "10 - Alt"
	       255 => "(Unassigned)",
		);


		$this->fields['link_open']['writeParms']['optArray'] = array(
			0 => LCLAN_20, // 0 = same window
			1 => LCLAN_23, // new window
			4 => LCLAN_24, // 4 = miniwindow  600x400
			5 => LINKLAN_1 // 5 = miniwindow  800x600
		);





	}



	public function handleListLinkParentBatch($selected, $value)
	{
		$field = 'link_parent';
		$ui = $this->getUI();
		$found = false;
		foreach ($selected as $k => $id)
		{
			// var_dump($ui->_has_parent($value, $id, $this->getLinkArray()));
			if($ui->_has_parent($value, $id, $this->getLinkArray()))
			{
				unset($selected[$k]);
				$found = true;
			}
		}
		if($found) e107::getMessage()->addWarning(LCLAN_108);
		if(!$selected) return;
		
		if(parent::handleListBatch($selected, $field, $value))
		{
			$this->_link_array = null; // reset batch/filters
			return true;
		}
		return false;
	}

	public function ListObserver()
	{
		$searchFilter = $this->_parseFilterRequest($this->getRequest()->getQuery('filter_options', ''));

		if($searchFilter && in_array('link_parent', $searchFilter))
		{
			$this->getTreeModel()->current_id = intval($searchFilter[1]);
			$this->current_parent = intval($searchFilter[1]);
		}
		parent::ListObserver();

	}
	public function ListAjaxObserver()
	{
		$searchFilter = $this->_parseFilterRequest($this->getRequest()->getQuery('filter_options', ''));

		if($searchFilter && in_array('link_parent', $searchFilter))
		{
			$this->getTreeModel()->current_id = intval($searchFilter[1]);
			$this->current_parent = intval($searchFilter[1]);
		}
		parent::ListAjaxObserver();
	}

	/**
	 * Form submitted - 'etrigger_generate_sublinks' POST variable caught
	 *//*
	public function SublinksGenerateSublinksTrigger()
	{
		$this->generateSublinks();
	}

	public function sublinksObserver()
	{
		$this->getTreeModel()->load();
	}*/

	/**
	 * Sublinks generator
	 */
	public function toolsPage()
	{

		if(!empty($_POST['etrigger_generate_sublinks']))
		{
			$this->generateSublinks($_POST);
		}

		$sublinks = $this->sublink_data();
		$ui = $this->getUI();
		// TODO - use UI create form
		$sql = e107::getDb();
		$text = "
		<form method='post' action='".e_REQUEST_URL."'>
			<fieldset id='core-links-generator'>
				<legend class='e-hideme'>".LINKLAN_4."</legend>
				<table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td>".LINKLAN_6.":</td>
							<td>
		";

		foreach($sublinks as $key => $type)
		{
			$optarrayp[$key] = $type['title'];
			//$selected = $this->getPosted('sublink_type') == $key ? ' selected="selected"' : '';
			/*$text .= "
									<option value='{$key}'{$selected}>".$type['title']."</option>
					";*/
		}
		$text .= $ui->selectbox('sublink_type', $optarrayp, $this->getPosted('sublink_type'), '', true);

		$text .= "
							</td>
						</tr>
						<tr>
							<td>".LINKLAN_7." (".LAN_OPTIONAL.")</td>
							<td>
								";
		$text .= $ui->link_parent($this->getPosted('link_parent'), 'write');
		$text .= "

							</td>
						</tr>
					</tbody>
				</table>
				<div class='buttons-bar center'>
					".$ui->admin_button('etrigger_generate_sublinks', 'no-value', 'submit', LINKLAN_5)."
				</div>
			</fieldset>
		</form>
		";

		//$e107->ns->tablerender(LINKLAN_4, $emessage->render().$text);
	//	$this->addTitle(LINKLAN_4);

		return $text;

	}












	function sublink_data($name = "")
	{
		if(null !== $this->sublink_data) return ($name ? $this->sublink_data[$name] : $this->sublink_data);
		$sublink_type = array();
		$sublink_type['news']['title'] = LINKLAN_8; // "News Categories";
		$sublink_type['news']['table'] = "news_category";
		$sublink_type['news']['query'] = "category_id !='-2' ORDER BY category_name ASC";
		$sublink_type['news']['url'] = "news.php?list.#";
		$sublink_type['news']['fieldid'] = "category_id";
		$sublink_type['news']['fieldname'] = "category_name";
		$sublink_type['news']['fieldicon'] = "category_icon";
		$sublink_type['news']['sef'] = "news/list/category";

		$sublink_type['newsalt'] = $sublink_type['news'];
		$sublink_type['newsalt']['url'] = "news.php?cat.#";
		$sublink_type['newsalt']['title'] = LINKLAN_8." (".LAN_LIST.")"; // "News Categories";
		$sublink_type['newsalt']['sef'] = "news/list/short";


		$sublink_type['downloads']['title'] = LINKLAN_9; //"Download Categories";
		$sublink_type['downloads']['table'] = "download_category";
		$sublink_type['downloads']['query'] = "download_category_parent ='0' ORDER BY download_category_name ASC";
		$sublink_type['downloads']['url'] = "download.php?list.#";
		$sublink_type['downloads']['fieldid'] = "download_category_id";
		$sublink_type['downloads']['fieldname'] = "download_category_name";
		$sublink_type['downloads']['fieldicon'] = "download_category_icon";

		// fixed - sql query not needed
		$plugins = array_keys(e107::getConfig()->get('plug_installed'));

		foreach ($plugins as $plugin)
		{
			if(is_readable(e_PLUGIN.$plugin.'/e_linkgen.php'))
			{
				require_once (e_PLUGIN.$plugin.'/e_linkgen.php');
			}
		}
		$this->sublink_data = $sublink_type;
		if($name)
		{
			return $sublink_type[$name];
		}

		return $sublink_type;
	}

	function generateSublinks($sublink)
	{

		$mes = e107::getMessage();
		$subtype = $this->getPosted('sublink_type');//$_POST['sublink_type'];
		$pid = intval($this->getPosted('link_parent'));
		$sublink = $this->sublink_data($subtype);

		if(!$pid)
		{
		//	$mes->addWarning(LCLAN_109);
		//	return;
		}
		if(!$subtype)
		{
			$mes->addWarning(LCLAN_110);
			return;
		}
		if(!$sublink)
		{
			$mes->addError(LCLAN_111);
			return;
		}

		$sublink = $this->sublink_data($subtype);


		$sql = e107::getDb();
		$sql2 = e107::getDb('sql2');


		$sql->select("links", "*", "link_id=".$pid);
		$par = $sql->fetch();

		//extract($par);
		// Added option for passing of result array
		if(vartrue($sublink['result']))
		{
			$count = 1;
			foreach ($sublink['result'] as $row)
			{
				$subcat = $row[($sublink['fieldid'])];
				$name = $row[($sublink['fieldname'])];
				$subname = $name; // eliminate old embedded hierarchy from names. (e.g. 'submenu.TopName.name')

				if(!empty($sublink['sef']))
				{
					$suburl = e107::url($sublink['sef'], $row);
				}
				else
				{
					$suburl = str_replace("#", $subcat, $sublink['url']);
				}

				$subicon = ($sublink['fieldicon']) ? $row[($sublink['fieldicon'])] : $par['link_button'];
				$subdiz = ($sublink['fielddiz']) ? $row[($sublink['fielddiz'])] : $par['link_description'];
				$subparent = $pid;

				$insert_array = array(
						'link_name'			=> $subname,
						'link_url'			=> $suburl,
						'link_description'	=> $subdiz,
						'link_button'		=> $subicon,
						'link_category'		=> $par['link_category'],
						'link_order'		=> $count,
						'link_parent'		=> $subparent,
						'link_open'			=> $par['link_open'],
						'link_class'		=> $par['link_class'],
						'link_function'		=> ''
				);
				$count++;
			}
		}
		else
		{
			$sql->select($sublink['table'], "*", $sublink['query']);
			$count = 1;
			while($row = $sql->fetch())
			{
				$subcat = $row[($sublink['fieldid'])];
				$name = $row[($sublink['fieldname'])];
				$subname = $name; // eliminate old embedded hierarchy from names. (e.g. 'submenu.TopName.name')
				if(!empty($sublink['sef']))
				{
					$suburl = e107::url($sublink['sef'], $row);
				}
				else
				{
					$suburl = str_replace("#", $subcat, $sublink['url']);
				}
				$subicon = ($sublink['fieldicon']) ? $row[($sublink['fieldicon'])] : $par['link_button'];
				$subdiz = ($sublink['fielddiz']) ? $row[($sublink['fielddiz'])] : $par['link_description'];
				$subparent = $pid;

				$insert_array = array(
						'link_name'			=> $subname,
						'link_url'			=> $suburl,
						'link_description'	=> $subdiz,
						'link_button'		=> $subicon,
						'link_category'		=> vartrue($par['link_category'],1),
						'link_order'		=> $count,
						'link_parent'		=> $subparent,
						'link_open'			=> $par['link_open'],
						'link_class'		=> intval($par['link_class']),
						'link_function'		=> ''
				);

				e107::getMessage()->addDebug(print_a($insert_array,true));

				if($sql2->insert("links",$insert_array))
				{
					$message = LAN_CREATED." ({$name})[!br!]";
					$mes->addSuccess(LAN_CREATED." ({$name})");
				} else
				{
					$message = LAN_CREATED_FAILED." ({$name})[!br!]";
					$mes->addError(LAN_CREATED_FAILED." ({$name})");
				}
				$count++;
			}
		}

		if($message) // TODO admin log
		{
			// sitelinks_adminlog('01', $message); // 'Sublinks generated'
		}
	}

	/**
	 * Product tree model
	 * @return links_model_admin_tree
	 */
	public function _setTreeModel()
	{
		$this->_tree_model = new links_model_admin_tree();
		return $this;
	}

	/**
	 * Link ordered array
	 * @return array
	 */
	public function getLinkArray($current_id = 0)
	{
		if(null === $this->_link_array)
		{
			if($this->getAction() != 'list')
			{
				$this->getTreeModel()->setParam('order', 'ORDER BY '.$this->listOrder)->loadBatch();
			}
			/** @var e_tree_modell $tree */
			$tree = $this->getTreeModel()->getTree();
			$this->_link_array = array();
			foreach ($tree as $id => $model)
			{
				if($current_id == $id) continue;
				$this->_link_array[$model->get('link_parent')][$id] = $model->get('link_name');
			}
			asort($this->_link_array);
		}

		return $this->_link_array;
	}
}

class links_model_admin_tree extends e_admin_tree_model
{
	public $modify = false;
	public $current_id = 0;

	protected $_db_table = 'links';
	protected $_link_array	= null;
	protected $_link_array_modified	= null;

	protected $_field_id = 'link_id';


	/**
	 * Get array of models
	 * Custom tree order
	 * @return array
	 */
	function getTree($force = false)
	{
		return $this->getOrderedTree($this->modify);
	}

	/**
	 * Get ordered by their parents models
	 * @return array
	 */
	function getOrderedTree($modified = false)
	{
		$var = !$modified ? '_link_array' : '_link_array_modified';
		if(null === $this->$var)
		{
			$tree = $this->get('__tree', array());

			$this->$var = array();
			$search = array();
			foreach ($tree as $id => $model)
			{
				$search[$model->get('link_parent')][$id] = $model;
			}
			asort($search);
			$this->_tree_order($this->current_id, $search, $this->$var, 0, $modified);
		}
		//$this->buildTreeIndex();
		return $this->$var;
	}

	/**
	 * Reorder current tree
	 * @param $parent_id
	 * @param $search
	 * @param $src
	 * @param $level
	 * @return void
	 */
	function _tree_order($parent_id, $search, &$src, $level = 0, $modified = false)
	{
		if(!isset($search[$parent_id]))
		{
			return;
		}

		$level_image = $level ? '<img src="'.e_IMAGE_ABS.'generic/branchbottom.gif" class="icon" alt="" style="margin-left: '.($level * 20).'px" />&nbsp;' : '';
		foreach ($search[$parent_id] as $model)
		{
			$id = $model->get('link_id');
			$src[$id] = $model;
			if($modified)
			{
				$model->set('link_name', $this->bcClean($model->get('link_name')))
					->set('link_indent', $level_image);
			}
			$this->_tree_order($id, $search, $src, $level + 1, $modified);
		}
	}
	
	
	function bcClean($link_name)
	{
		if(substr($link_name, 0,8) == 'submenu.') // BC Fix. 
		{
			list($tmp,$tmp2,$link) = explode('.', $link_name, 3);	
		}
		else
		{
			$link = $link_name;	
		}
		
		return $link;		
	}
	
}


class links_admin_form_ui extends e_admin_form_ui
{
	protected $current_parent = null;
	
	private $linkFunctions;

	function init()
	{
		
		$tp = e107::getParser();
		$tmp = e107::getAddonConfig('e_sitelink','sitelink');
					
		foreach($tmp as $cat=> $array)
		{
			$func = array();
			foreach($array as $val)
			{
				$newkey = $cat.'::'.$val['function'];
				if(vartrue($val['parm']))
				{
					$newkey .= "(".$val['parm'].")";	
				}
				$func[$newkey] = $tp->toHTML($val['name'],'','TITLE');
			}
			$this->linkFunctions[$cat] = $func;
		}

		$sitetheme = e107::getPref('sitetheme');

		if(!file_exists(e_THEME.$sitetheme.'/theme_shortcodes.php'))
		{
			return null;
		}

		require_once(e_THEME.$sitetheme.'/theme_shortcodes.php');
		$methods = get_class_methods('theme_shortcodes');

		asort($methods);

		$cat = defset('LINKLAN_10',"Theme Shortcodes");

		foreach($methods as $func)
		{
			if(strpos($func,'sc_') !== 0)
			{
				continue;
			}

			$newkey = 'theme::'.$func;

			$this->linkFunctions[$cat][$newkey] = str_replace('sc_','',$func);
		}


	//	var_dump($methods );


	}
	
	function link_parent($value, $mode)
	{
		switch($mode)
		{
			case 'read':
				$current = $this->getController()->current_parent;
				if($current) // show only one parent
				{
					if(null === $this->current_parent)
					{
						if(e107::getDb()->db_Select('links', 'link_name', 'link_id='.$current))
						{
							$tmp = e107::getDb()->db_Fetch();
							$this->current_parent = $tmp['link_name'];
						}
					}
				}
				$cats	= $this->getController()->getLinkArray();
				$ret	= array();
				$this->_parents($value, $cats, $ret);
				if($this->current_parent) array_unshift($ret, $this->current_parent);
				return ($ret ? implode('&nbsp;&raquo;&nbsp;', $ret) : '-');
			break;

			case 'write':
				$catid	= $this->getController()->getId();
				$cats	= $this->getController()->getLinkArray($catid);
				$ret	= array();
				$this->_parent_select_array(0, $cats, $ret);
				return $this->selectbox('link_parent', $ret, $value, array('size'=>'xlarge','default' => LAN_SELECT."..."));
			break;

			case 'batch':
			case 'filter':
				$cats	= $this->getController()->getLinkArray();

				$ret[0]	= $mode == 'batch' ? 'REMOVE PARENT' : 'Main Only';
				$this->_parent_select_array(0, $cats, $ret);
				return $ret;
			break;
		}
	}

	function link_function($curVal,$mode)
	{
		if($mode == 'read')
		{
			return $curVal; //  $this->linkFunctions[$curVal];
		}

		if($mode == 'write')
		{			
			return $this->selectbox('link_function',$this->linkFunctions,$curVal,array('size'=>'xlarge','default'=> "(".LAN_OPTIONAL.")"));
		}

		else
		{
			return $this->linkFunctions;
		}
	}

	function link_id($curVal,$mode)
	{
		if($mode == 'read')
		{
			$linkUrl = $this->getController()->getListModel()->get('link_url');



			$url = $this->link_url($linkUrl,'link_id');



			return "<a href='".$url."' rel='external'>".$curVal."</a>"; //  $this->linkFunctions[$curVal];
		}
	}


	function link_sefurl($curVal,$mode)
	{
		if($mode == 'read')
		{
			$plugin = $this->getController()->getModel()->get('link_owner');
			return $curVal; //  $this->linkFunctions[$curVal];
		}

		if($mode == 'write')
		{
			$plugin = $this->getController()->getModel()->get('link_owner');
			$obj    = e107::getAddon($plugin,'e_url');
			$config = e107::callMethod($obj,'config');
			$opts   = array();

			if(empty($config))
			{
				return $this->hidden('link_sefurl','')."<span class='label label-warning'>".LAN_NOT_AVAILABLE."</span>";
			}

			foreach($config as $k=>$v)
			{
				if($k == 'index' || (strpos($v['regex'],'(') === false)) // only provide urls without dynamic elements.
				{
					$opts[] = $k;
				}
			}

			sort($opts);

			return $this->select('link_sefurl', $opts, $curVal, array('useValues'=>true,'defaultValue'=>'','default'=>'('.LAN_DISABLED.')'));
		}

	}

	function link_url($curVal,$mode)
	{
		if($mode == 'read' || $mode == 'link_id') // read = display mode, link_id = actual absolute URL
		{
			$owner = $this->getController()->getListModel()->get('link_owner');
			$sef =  $this->getController()->getListModel()->get('link_sefurl');

			if($curVal[0] !== '{' && substr($curVal,0,4) != 'http' && $mode == 'link_id')
			{
				$curVal = '{e_BASE}'.$curVal;
			}

			if(!empty($owner) && !empty($sef))
			{
				$opt = ($mode == 'read') ? array('mode'=>'raw') : array();
				$curVal = e107::url($owner,$sef, null, $opt);
			}
			else
			{
				$opt = ($mode == 'read') ? 'rel' : 'abs';
				$curVal = e107::getParser()->replaceConstants($curVal,$opt);
			}

			e107::getDebug()->log($curVal);

			return $curVal; //  $this->linkFunctions[$curVal];
		}

		if($mode == 'write')
		{
			$owner = $this->getController()->getModel()->get('link_owner');
			$sef =  $this->getController()->getModel()->get('link_sefurl');

			if(!empty($owner) && !empty($sef))
			{

				$text = str_replace(e_HTTP,'',e107::url($owner,$sef)); // dynamically created.
				$text .= $this->hidden('link_url',$curVal);
				$text .= " <span class='label label-warning'>".LAN_AUTO_GENERATED."</span>";

				return $text;

			}

			return $this->text('link_url', $curVal, 255,  array('size'=>'xxlarge'));
		}

		if($mode == 'inline')
		{
			$sef =  $this->getController()->getListModel()->get('link_sefurl');

			if(empty($sef))
			{
				return array('inlineType'=>'text');
			}

			return false;
		}

	}

	/**
	 *
	 * @param integer $category_id
	 * @param array $search
	 * @param array $src
	 * @param boolean $titles
	 * @return array
	 */
	function _parents($link_id, $search, &$src, $titles = true)
	{
		foreach ($search as $parent => $cat)
		{
			if($cat && array_key_exists($link_id, $cat))
			{
				array_unshift($src, ($titles ? $cat[$link_id] : $link_id));
				if($parent > 0)
				{
					$this->_parents($parent, $search, $src, $titles);
				}
			}
		}
	}

	function _parent_select_array($parent_id, $search, &$src, $strpad = '&nbsp;&nbsp;&nbsp;', $level = 0)
	{
		if(!isset($search[$parent_id]))
		{
			return;
		}

		foreach ($search[$parent_id] as $id => $title)
		{
			$src[$id] = str_repeat($strpad, $level).($level != 0 ? '-&nbsp;' : '').$title;
			$this->_parent_select_array($id, $search, $src, $strpad, $level + 1);
		}
	}

	function _has_parent($link_id, $parent_id, $cats)
	{
		$path = array();
		$this->_parents($link_id, $cats, $path, false);
		return in_array($parent_id, $path);
	}
	
	/**
	 * New core feature - triggered before values are rendered
	 */
	function renderValueTrigger(&$field, &$value, &$params, $id)
	{
		if($field !== 'link_name') return;
		$tree = $this->getController()->getTreeModel();
		// notify we need modified tree
		$tree->modify = true;
		
		//retrieve array of data models
		$data = $tree->getTree();
		// retrieve the propper model by id
		$model = varset($data[$id]);
		
		if(!$model) return;
		
		// Add indent as 'pre' parameter
		$params['pre'] = $model->get('link_indent');
	}

	/**
	 * Override Create list view
	 *
	 * @return string
	 */
	public function getList($ajax = false, $view='default')
	{
		$tp = e107::getParser();
		$controller = $this->getController();

		$request = $controller->getRequest();
		$id = $this->getElementId();
		$tree = $options = array();
		$tree[$id] = clone $controller->getTreeModel();
		$tree[$id]->modify = true;
		
		// if going through confirm screen - no JS confirm
		$controller->setFieldAttr('options', 'noConfirm', $controller->deleteConfirmScreen);

		$options[$id] = array(
			'id' => $this->getElementId(), // unique string used for building element ids, REQUIRED
			'pid' => $controller->getPrimaryName(), // primary field name, REQUIRED
			//'url' => e_SELF, default
			//'query' => $request->buildQueryString(array(), true, 'ajax_used'), - ajax_used is now removed from QUERY_STRING - class2
			'head_query' => $request->buildQueryString('field=[FIELD]&asc=[ASC]&from=[FROM]', false), // without field, asc and from vars, REQUIRED
			'np_query' => $request->buildQueryString(array(), false, 'from'), // without from var, REQUIRED for next/prev functionality
			'legend' => $controller->getPluginTitle(), // hidden by default
			'form_pre' => !$ajax ? $this->renderFilter($tp->post_toForm(array($controller->getQuery('searchquery'), $controller->getQuery('filter_options'))), $controller->getMode().'/'.$controller->getAction()) : '', // needs to be visible when a search returns nothing
			'form_post' => '', // markup to be added after closing form element
			'fields' => $controller->getFields(), // see e_admin_ui::$fields
			'fieldpref' => $controller->getFieldPref(), // see e_admin_ui::$fieldpref
			'table_pre' => '', // markup to be added before opening table element
			'table_post' => !$tree[$id]->isEmpty() ? $this->renderBatch(array('delete'=>$controller->getBatchDelete(),'copy'=>$controller->getBatchCopy())) : '',
			'fieldset_pre' => '', // markup to be added before opening fieldset element
			'fieldset_post' => '', // markup to be added after closing fieldset element
			'perPage' => $controller->getPerPage(), // if 0 - no next/prev navigation
			'from' => $controller->getQuery('from', 0), // current page, default 0
			'field' => $controller->getQuery('field'), //current order field name, default - primary field
			'asc' => $controller->getQuery('asc', 'desc'), //current 'order by' rule, default 'asc'
		);
		//$tree[$id]->modify = false;
		return $this->renderListForm($options, $tree, $ajax);
	}
}

new links_admin();


require_once(e_ADMIN."auth.php");

e107::getAdminUI()->runPage();
// TODO Link Preview. (similar to userclass preview)
/*
echo "<h2>Preview (To-Do)</h2>";
echo $tp->parseTemplate("{SITELINKS_ALT}");
*/
require_once(e_ADMIN."footer.php");
exit;







?>
