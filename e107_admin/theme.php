<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * blankd under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 blank Plugin
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/blank/admin_config.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

require_once("../class2.php");

if (!getperms("1|TMP"))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('theme', true);

if(!empty($_GET['iframe']))
{
	define('e_IFRAME', true);
}


class theme_admin extends e_admin_dispatcher
{
	/**
	 * Format: 'MODE' => array('controller' =>'CONTROLLER_CLASS'[, 'index' => 'list', 'path' => 'CONTROLLER SCRIPT PATH', 'ui' => 'UI CLASS NAME child of e_admin_ui', 'uipath' => 'UI SCRIPT PATH']);
	 * Note - default mode/action is autodetected in this order:
	 * - $defaultMode/$defaultAction (owned by dispatcher - see below)
	 * - $adminMenu (first key if admin menu array is not empty)
	 * - $modes (first key == mode, corresponding 'index' key == action)
	 * @var array
	 */
	protected $modes = array(
		'main'		=> array(
						'controller' => 'theme_admin_ui',
						'path' 		=> null,
						'ui' 		=> 'theme_admin_form_ui',
						'uipath' => null
		),
		'convert'		=> array(
						'controller' => 'theme_builder',
						'path' 		=> null,
						'ui' 		=> 'theme_admin_form_ui',
						'uipath' => null
		),
	);


	protected $adminMenu = array(
		'main/main'			=> array('caption'=> TPVLAN_33, 'perm' => '0|1|TMP'),
		'main/admin' 		=> array('caption'=> TPVLAN_34, 'perm' => '0'),
		'main/choose' 		=> array('caption'=> TPVLAN_51, 'perm' => '0'),
		'main/online'		=> array('caption'=> TPVLAN_62, 'perm' => '0'),
		'main/upload'		=> array('caption'=> TPVLAN_38, 'perm' => '0'),
		'convert/main'		=> array('caption'=> ADLAN_CL_6, 'perm' => '0')
	);


	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'
	);

	protected $adminMenuIcon = 'e-themes-24';

	protected $menuTitle = TPVLAN_26;

	function init()
	{

	}



	function handleAjax()
	{
		if(empty($_GET['action']))
		{
			return null;
		}


		require_once(e_HANDLER."theme_handler.php");
		$themec = new themeHandler;

		switch ($_GET['action'])
		{
			case 'login':
				$mp = $themec->getMarketplace();
				echo $mp->renderLoginForm();
				exit;
			break;

				/*
				case 'download':
					$string =  base64_decode($_GET['src']);
					parse_str($string, $p);
					$mp = $themec->getMarketplace();
					$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
					// Server flush useless. It's ajax ready state 4, we can't flush (sadly) before that (at least not for all browsers)
					echo "<pre>Connecting...\n"; flush();
					// download and flush
					$mp->download($p['id'], $p['mode'], $p['type']);
					echo "</pre>"; flush();
					exit;
				break;
				*/

				case 'info':
					if(!empty($_GET['src']))
					{
						$string =  base64_decode($_GET['src']);
						parse_str($string,$p);
						$themeInfo = e107::getSession()->get('thememanager/online/'.intval($p['id']));
						echo $themec->renderThemeInfo($themeInfo);
					}
				break;

				case 'preview':
					// Theme Info Ajax
					$tm = (string) $_GET['id'];
					$data = $themec->getThemeInfo($tm);
					echo $themec->renderThemeInfo($data);
				//	exit;
				break;

			}
		/*
			if(vartrue($_GET['src'])) // Process Theme Download.
			{
				$string =  base64_decode($_GET['src']);
				parse_str($string,$p);

				if(vartrue($_GET['info']))
				{
					echo $themec->renderThemeInfo($p);
				//	print_a($p);
					exit;
				}

				$remotefile = $p['url'];

				e107::getFile()->download($remotefile,'theme');
				exit;

			}
		*/
			// Theme Info Ajax
			// FIXME  addd action=preview to the url, remove this block
			if(!empty($_GET['id']))
			{
				$tm = (string) $_GET['id'];
				$data = $themec->getThemeInfo($tm);
				echo $themec->renderThemeInfo($data);
			}

			require_once(e_ADMIN."footer.php");
			exit;

		}




}



class theme_admin_ui extends e_admin_ui
{
		// required
		protected $pluginTitle      = TPVLAN_26;
		protected $pluginName       = 'core';
		protected $table            = false;
		protected $listQry          = false;
		protected $pid              = "id";
		protected $perPage          = 10;
		protected $batchDelete      = false;

	//	protected \$sortField		= 'somefield_order';
	//	protected \$sortParent      = 'somefield_parent';
	//	protected \$treePrefix      = 'somefield_title';
		protected $grid             = array('price'=>'price', 'version'=>'version','title'=>'name', 'image'=>'thumbnail',  'body'=>'',  'class'=>'col-md-2 col-sm-3', 'perPage'=>12, 'carousel'=>true, 'toggleButton'=>false);


    	protected  $fields = array(
			'checkboxes'				=> array('title'=> '', 					'type' => null,			'data' => null,			'width'=>'5%', 		'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center', 'toggle' => 'e-multiselect'),
			'id'					    => array('title'=> LAN_ID, 				'type' => 'number',		'data' => 'int',		'width'=>'5%',		'thclass' => '',  'class'=>'center',	'forced'=> TRUE, 'primary'=>TRUE/*, 'noedit'=>TRUE*/), //Primary ID is not editable
           	'name'				        => array('title'=> LAN_TITLE, 			'type' => 'text',		'data' => 'str',		'width'=>'5%',		'thclass' => '',	'forced'=> TRUE, 'primary'=>TRUE/*, 'noedit'=>TRUE*/), //Primary ID is not editable
            'thumbnail'	   			    => array('title'=> LAN_IMAGE, 			'type' => 'image',      'readParms'=>array('thumb'=>1,'w'=>300,'h'=>169,'crop'=>1, 'link'=>false, 'fallback'=>'{e_IMAGE}admin_images/nopreview.png'),	'data' => 'str',		'width'=>'auto',	'thclass' => '', 'batch' => TRUE, 'filter'=>TRUE),
			'folder' 				    => array('title'=> 'Folder', 			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => ''),
			'category' 				    => array('title'=> LAN_CATEGORY, 				'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => ''),
			'version' 			        => array('title'=> 'Version',			'type' => 'text', 		'data' => 'str',		'width' => 'auto',	'thclass' => ''),
			'price' 				    => array('title'=> LAN_AUTHOR,			'type' => 'method', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'),
     //    	'blank_authorURL' 			=> array('title'=> "Url", 				'type' => 'url', 		'data' => 'str',		'width' => 'auto',	'thclass' => 'left'),
     //       'blank_date' 				=> array('title'=> LAN_DATE, 			'type' => 'datestamp', 	'data' => 'int',		'width' => 'auto',	'thclass' => '', 'readParms' => 'long', 'writeParms' => 'type=datetime'),
	//		'blank_compatibility' 		=> array('title'=> 'Compatible',			'type' => 'text', 		'data' => 'str',		'width' => '10%',	'thclass' => 'center' ),
		//	'blank_url' 				=> array('title'=> LAN_URL,		'type' => 'file', 		'data' => 'str',		'width' => '20%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'parms' => 'truncate=30', 'validate' => false, 'help' => 'Enter blank URL here', 'error' => 'please, ener valid URL'),
	//		'test_list_1'				=> array('title'=> 'test 1',			'type' => 'boolean', 		'data' => 'int',		'width' => '5%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'noedit' => true),
			'options' 					=> array('title'=> LAN_OPTIONS, 		'type' => 'method', 		'data' => null,			'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE)
		);

		//required - default column user prefs
	//	protected $fieldpref = array('checkboxes', 'blank_id', 'blank_type', 'blank_url', 'blank_compatibility', 'options');

		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array();

		protected $themeObj;

		public function __construct($request,$response,$params=array())
		{
			require_once(e_HANDLER."theme_handler.php");
			$this->themeObj = new themeHandler; // handles POSTed data.
			parent::__construct($request,$response,$params);
		}

		// optional
		public function init()
		{

			e107::css('inline', '


				.admin-ui-grid .price {
				position: absolute;
			/*	bottom: 68px;*/
				top:0;
				right: 18px;
				}

				.overlay-title { padding-bottom:7px }

			');




			$this->themeObj ->postObserver();

				$this->grid['template'] = '

				 <div class="panel panel-primary">
					<div class="e-overlay" >{IMAGE}
						<div class="e-overlay-content">
						<div class="overlay-title">{TITLE} v{VERSION}</div>
						{OPTIONS}
						</div>
					</div>
					<div class="panel-footer"><small>{TITLE}</small>{PRICE}</div>
				</div>


				';
		}

		public function _setTreeModel()
		{
			if($this->getAction() === 'online')
			{
				$this->_tree_model = new theme_admin_online_tree_model;
			}
			else
			{
				$this->_tree_model = new theme_admin_tree_model;// new theme_model_admin_tree();
			}

			return $this;
		}

		public function ChooseObserver() // action = choose
		{
			$mes = e107::getMessage();
			$tp = e107::getParser();

			if(!empty($_POST['selectmain']))
			{
				$id = key($_POST['selectmain']);
				$message = $tp->lanVars(TPVLAN_94,$id);

				if($this->themeObj->setTheme($id))
				{
					$mes->addSuccess($message);

					// clear infopanel in admin dashboard.
					e107::getCache()->clear('Infopanel_theme', true);
					e107::getSession()->clear('addons-update-status');
					e107::getSession()->set('addons-update-checked',false); // set to recheck it.
				}
				else
				{
					$mes->addError($message);
				}
			}




			$param = array();
			$this->perPage = 0;
			$param['limitFrom'] = (int) $this->getQuery('from', 0);
			$param['limitTo']   = 0 ; // (int) $this->getPerPage();
			$param['searchqry'] = $this->getQuery('searchquery', '');

			$this->getTreeModel()->setParams($param)->loadBatch(); // load the tree model above from the class below.
		}

		public function OnlineObserver()
		{
			$this->ChooseObserver();
		}
		
		public function ChooseAjaxObserver()
		{
			$this->ChooseObserver();
		}

		public function MainPage()
		{
			if(empty($_POST) && deftrue('e_DEVELOPER') || deftrue('e_DEBUG')) // check for new theme media and import.
			{
				$name = e107::getPref('sitetheme');
				e107::getMedia()->import('_common_image', e_THEME.$name, '', 'min-size=10000');
				e107::getMessage()->addInfo('Developer/Debug Mode: Scanning theme images folder for new media to import.');
			}

			$message = e107::getMessage()->render();
			return $message.$this->renderThemeConfig('front');
		}

		public function AdminPage()
		{
			return $this->renderThemeConfig('admin');
		}

		private function search($name, $searchVal, $submitName, $filterName='', $filterArray=false, $filterVal=false)
		{
			$frm = e107::getForm();

			return $frm->search($name, $searchVal, $submitName, $filterName, $filterArray, $filterVal);

		}

		public function OnlinePageOld()
		{
			global $e107SiteUsername, $e107SiteUserpass;
			$xml 	= e107::getXml();
			$mes 	= e107::getMessage();
			$frm 	= e107::getForm();

			require_once(e_HANDLER.'e_marketplace.php');

			$mp 	= new e_marketplace(); // autodetect the best method
			$from 	= intval(varset($_GET['frm']));
			$limit 	= 96; // FIXME - ajax pages load
			$srch 	= preg_replace('/[^\w]/','', vartrue($_GET['srch']));

			// check for cURL
			if(!function_exists('curl_init'))
			{
				$mes->addWarning(TPVLAN_79);
			}

			// auth
			$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);

			// do the request, retrieve and parse data
			$xdata = $mp->call('getList', array(
				'type' => 'theme',
				'params' => array('limit' => $limit, 'search' => $srch, 'from' => $from)
			));
			$total = $xdata['params']['count'];



			$amount =$limit;


			$c = 1;

		/*	$text = "<form class='form-search' action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='get'>";
			$text .= '<div id="myCarousel"  class="carousel slide" data-interval="false">';
			$text .= "<div class='form-inline clearfix row-fluid'>";
			$text .= $this->search('srch', $srch, 'go', $filterName, $filterArray, $filterVal).$frm->hidden('mode','online');
			$text .= '<div class="btn-group" style="margin-left:10px"><a class="btn btn-primary" href="#myCarousel" data-slide="prev">&lsaquo;</a><a class="btn btn-primary" href="#myCarousel" data-slide="next">&rsaquo;</a></div>';
			$text .= "{CAROUSEL_INDICATORS}";
			$text .= "</div>";
			$text .= '<div id="shop" style="margin-top:10px;min-height:585px" class=" carousel-inner">';*/

			if(is_array($xdata['data'] ))
			{

				$text = '<div  class="active item">';

				$slides = array();

				foreach($xdata['data'] as $r)
				{
					if(E107_DBG_PATH)
					{
						$mes->addDebug(print_a($r,true));
					}

					$theme = array(
						'id'			=> $r['params']['id'],
						'type'			=> 'theme',
						'mode'			=> $r['params']['mode'],
						'name'			=> stripslashes($r['name']),
						'category'		=> $r['category'],
						'preview' 		=> varset($r['screenshots']['image']),
						'date'			=> $r['date'],
						'version'		=> $r['version'],
						'thumbnail'		=> $r['thumbnail'],
						'url'			=> $r['urlView'],
						'author'		=> $r['author'],
						'website'		=> $r['authorUrl'],
						'compatibility'	=> $r['compatibility'],
						'description'	=> $r['description'],
						'price'			=> $r['price'],
						'livedemo'		=> $r['livedemo'],
					);


					$text .= $this->themeObj->renderTheme(FALSE, $theme);

					$c++;

					if($c == 19)
					{
						$text .= '</div><div class="item">';
						$slides[] = 1;
						$c = 1;
					}

				}


				$text .= "<div class='clear'>&nbsp;</div>";
				$text .= "</div>";
				$text .= "</div>";
			}
			else
			{
				$mes->addInfo(TPVLAN_80);
			}

			 $indicators = '<ol class="carousel-indicators col-md-6 span6">
				<li data-target="#myCarousel" data-slide-to="0" class="active"></li>';

			foreach($slides as $key=>$v)
			{
				$id = $key + 1;
				$indicators .= '<li data-target="#myCarousel" data-slide-to="'.$id.'"></li>';
			}

			$indicators .=	'</ol>';

			$text = str_replace("{CAROUSEL_INDICATORS}",$indicators,$text);

			$text .= "</form>";

			return $text;
		}


		public function InfoPage()
		{
			if(!empty($_GET['src'])) // online mode.
			{
				$string =  base64_decode($_GET['src']);
				parse_str($string,$p);
				$themeInfo = e107::getSession()->get('thememanager/online/'.intval($p['id']));
				return $this->themeObj->renderThemeInfo($themeInfo);
			}


			if(empty($_GET['id']))
			{
				echo "invalid URL";
				return null;
			}

			$tm = (string) $this->getId();
			$themeMeta  = e107::getTheme($tm)->get();
			echo $this->themeObj->renderThemeInfo($themeMeta);

		}

		public function DownloadPage()
		{
			$frm = e107::getForm();
			$mes = e107::getMessage();
			$string =  base64_decode($_GET['src']);
			parse_str($string, $data);

			if(!empty($data['price']))
			{
				e107::getRedirect()->go($data['url']);
				return true;
			}

			if(deftrue('e_DEBUG_MARKETPLACE'))
			{
				echo "<b>DEBUG MODE ACTIVE (no downloading)</b><br />";
				echo '$_GET: ';
				print_a($_GET);

				echo 'base64 decoded and parsed as $data:';
				print_a($data);
				return false;
			}

			require_once(e_HANDLER.'e_marketplace.php');

			$mp 	= new e_marketplace(); // autodetect the best method

		    $mes->addSuccess(TPVLAN_85);

			if($mp->download($data['id'], $data['mode'], 'theme')) // download and unzip theme.
			{
				// Auto install?
			//	$text = e107::getPlugin()->install($data['plugin_folder']);
			//	$mes->addInfo($text);

				e107::getTheme()->clearCache();
				return $mes->render('default', 'success');
			}
			else
			{
				return $mes->addError('Unable to continue')->render('default', 'error');
			}

			/*echo $mes->render('default', 'debug');
				echo "download page";*/


		}

		public function UploadPage()
		{

			$frm = e107::getForm();

			if(!is_writable(e_THEME))
			{
				return e107::getMessage()->addWarning(TPVLAN_15)->render();
			}
			else
			{
				require_once(e_HANDLER.'upload_handler.php');
				$max_file_size = get_user_max_upload();

				$text = "
				<form enctype='multipart/form-data' action='".e_SELF."' method='post'>
					<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
					<tr>
						<td>".TPVLAN_13."</td>
						<td>
							<input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}' />
							<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
							<input class='tbox' type='file' name='file_userfile[]' size='50' />
						</td>
					</tr>
	                <tr>
						<td>".TPVLAN_10."</td>
						<td><input type='checkbox' name='setUploadTheme' value='1' /></td>
					</tr>
					</table>

				<div class='buttons-bar center'>".$frm->admin_button('upload', 1, 'submit', LAN_UPLOAD)."</div>
				</form>
				";
			}

			return $text;

		}


		/**
		 * Check theme.php code for methods incompatible with PHP7.
		 * @param $code
		 * @return bool
		 */
		private function containsErrors($code)
		{
			if(PHP_MAJOR_VERSION < 6)
			{
				return false;
			}

			$dep = array('call_user_method(', 'call_user_method_array(', 'define_syslog_variables', 'ereg(','ereg_replace(',
			'eregi(', 'eregi_replace(', 'set_magic_quotes_runtime(', 'magic_quotes_runtime(', 'session_register(', 'session_unregister(', 'session_is_registered(',
			'set_socket_blocking(', 'split(', 'spliti(', 'sql_regcase(', 'mysql_db_query(', 'mysql_escape_string(');

			foreach($dep as $test)
			{
				if(strpos($code, $test) !== false)
				{
					e107::getMessage()->addDebug("Incompatible function <b>".rtrim($test,"(")."</b> found in theme.php");
					return true;
				}

			}

			return false;

		}




		private function renderThemeConfig($type = 'front')
		{
			$frm = e107::getForm();
			$themeMeta  = e107::getTheme($type)->get();

			$themeFileContent = file_get_contents(e_THEME.$themeMeta['path']."/theme.php");


			if($this->containsErrors($themeFileContent))
			{
				e107::getMessage()->setTitle("Incompatibility Detected", E_MESSAGE_ERROR)->addError("This theme is not compatible with your version of PHP.");
			}

			$this->addTitle("<span class='text-warning'>".$themeMeta['name']."</span>");

			$mode = ($type == 'front') ? 1 : 2;

			$text = $frm->open($type.'-form', 'post');
			$text .= $this->themeObj->renderTheme($mode, $themeMeta);
			$text .= $frm->close();

			return $text;
		}


		public function ChoosePage()
		{
			return $this->GridPage();
		}

		public function ChooseAjaxPage()
		{
			return $this->GridAjaxPage();
		}

		public function OnlinePage()
		{

			if(!function_exists('curl_init'))
			{
				e107::getMessage()->addWarning(TPVLAN_79);
			}

			return $this->GridPage();
		}

		public function OnlineAjaxPage()
		{
			return $this->GridAjaxPage();
		}


		public function renderHelp()
		{

			$tp = e107::getParser();

			$type= $this->getMode()."/".$this->getAction();

			switch($type)
			{
				case "main/main":
					$text = "<b>".TPVLAN_56."</b><br />"; // Visbility Filter
					$text .= "<br />".$tp->toHTML(TPVLANHELP_03,true);
					$text .= "<ul style='padding-left:10px;margin-top:10px'><li>".$tp->toHTML(TPVLANHELP_04,true)."</li>";
					$text .= "<li>".$tp->toHTML(TPVLANHELP_05,true)."</li></ul>";
					break;

				case "label2":
					//  code
					break;

				default:
					$text = TPVLANHELP_01."<br /><br />".TPVLANHELP_02;
			}




			return array('caption'=>LAN_HELP, 'text'=>$text);




		}


}


class theme_admin_tree_model extends e_tree_model
{

	/**
	 * Load data from theme meta file.
	 * @param bool $force
	 */
	function loadBatch($force=false)
	{
		$themeList  = e107::getTheme()->getList();
		$newArray   = array();
		$parms      = $this->getParams();
		$siteTheme  = e107::getPref('sitetheme');

		if($parms['limitFrom'] == 0 && empty($parms['searchqry'])) // place the sitetheme first.
		{

			$newArray[] = $themeList[$siteTheme];
		}

		foreach($themeList as $k=>$v)
		{

			if(!empty($parms['searchqry']) && stripos($v['description'],$parms['searchqry']) === false && stripos($v['folder'],$parms['searchqry']) === false && stripos($v['name'],$parms['searchqry']) === false)
			{
				continue;
			}

			if($v['path'] == $siteTheme)
			{
				continue;
			}

			$newArray[] = $v;
		}

		if(!empty($parms['limitTo']) && empty($parms['searchqry']))
		{
			$arr = array_slice($newArray, $parms['limitFrom'], $parms['limitTo']);
		}
		else
		{
			$arr = $newArray;
		}


		foreach($arr as $k=>$v)
		{

			$v['id'] = $k;

			$v['thumbnail'] = !empty($v['thumbnail']) ? '{e_THEME}'.$v['path'].'/'.$v['thumbnail'] : null;
			$tmp = new e_model($v);
			$this->setNode($k,$tmp);

		}

		$this->setTotal(count($newArray));
	}


}



class theme_admin_online_tree_model extends e_tree_model
{

	/**
	 * Load data from theme meta file.
	 * @param bool $force
	 */
	function loadBatch($force=false)
	{
		$themeList  = e107::getTheme()->getList();

		$parms      = $this->getParams();

		$mes 	= e107::getMessage();
		require_once(e_HANDLER.'e_marketplace.php');

		$mp 	= new e_marketplace(); // autodetect the best method
		$from 	= intval(varset($_GET['frm']));
		$limit 	= 96; // FIXME - ajax pages load
		$srch 	= preg_replace('/[^\w]/','', vartrue($_GET['srch']));

		// do the request, retrieve and parse data
		$xdata = $mp->call('getList', array(
				'type' => 'theme',
				'params' => array('limit' => $limit, 'search' => $srch, 'from' => $from)
			));

		$total = $xdata['params']['count'];

		$c = 0;
		if(is_array($xdata['data'] ))
		{

			foreach($xdata['data'] as $r)
			{
				if(E107_DBG_PATH)
				{
					$mes->addDebug(print_a($r,true));
				}

				$v = array(
						'id'			=> $r['params']['id'],
						'type'			=> 'theme',
						'mode'			=> $r['params']['mode'],
						'name'			=> stripslashes($r['name']),
						'category'		=> $r['category'],
						'preview' 		=> varset($r['screenshots']['image']),
						'date'			=> $r['date'],
						'version'		=> $r['version'],
						'thumbnail'		=> $r['thumbnail'],
						'url'			=> $r['urlView'],
						'author'		=> $r['author'],
						'website'		=> $r['authorUrl'],
						'compatibility'	=> $r['compatibility'],
						'description'	=> $r['description'],
						'price'			=> $r['price'],
						'livedemo'		=> $r['livedemo'],
					);


				$c++;
				$tmp = new e_model($v);
				$this->setNode($r['params']['id'],$tmp);

			}
		}

/*
		foreach($themeList as $k=>$v)
		{

			$v['id'] = $k;

			$v['thumbnail'] = !empty($v['thumbnail']) ? '{e_THEME}'.$v['path'].'/'.$v['thumbnail'] : null;
			$tmp = new e_model($v);
			$this->setNode($k,$tmp);

		}*/

		$this->setTotal($c);
	}


}






class theme_admin_form_ui extends e_admin_form_ui
{



	function price($curVal)
	{
		if($this->getController()->getAction() == 'choose')
		{
			$sitetheme = e107::getPref('sitetheme');
			$path = $this->getController()->getListModel()->get('path');

			if($sitetheme == $path)
			{
				return "<span class='pull-right text-warning'><i class='fa fa-home'></i></span>";
			}

			return '';
		}

		$text =(!empty($curVal)) ? "<span class='label label-primary'><i class='icon-shopping-cart icon-white'></i> ".$curVal."</span>" : "<span class='label label-success'>".TPVLAN_76."</span>";

		return '<span class="price pull-right">'.$text.'</span>';
	}



	function renderFilter($current_query = array(), $location = '', $input_options = array())
	{
		if($this->getController()->getAction() == 'choose')
		{
			return parent::renderFilter($current_query,$location,$input_options);
		}
		//	print_a($text);

	//	return $text;
			$text = "<form class='form-search' action='".e_SELF."' id='core-plugin-list-form' method='get'>
			<fieldset id='admin-ui-list-filter' class='e-filter'>
			<div class='col-md-12'>";
		//	$text .= '<div id="myCarousel"  class="carousel slide" data-interval="false">';
			$text .= "<div class='form-inline clearfix row-fluid'>";
			$text .= $this->search('srch', $_GET['srch'], 'go'/*, $filterName, $filterArray, $filterVal*/);

			$gets = $this->getController()->getQuery();

			foreach($gets as $k=>$v)
			{
				if($k == 'srch' || $k == 'go')
				{
					continue;
				}
				$text .= $this->hidden($k,$v);
			}

			$text .= $this->renderPagination();
			$text .= "</div>
					</div></fieldset></form>";

		return $text;
	}

	function options()
	{

		$theme = $this->getController()->getListModel()->getData();

		if($this->getController()->getAction() === 'online')
		{
			return $this->onlineOptions($theme);
		}
		else
		{
			return $this->chooseOptions($theme);

		}

	}

	private function chooseOptions($theme)
	{
		$pref = e107::getPref();
		$tp = e107::getParser();

		$infoPath       = e_SELF."?mode=".$_GET['mode']."&id=".$theme['path']."&action=info&iframe=1";
		$previewPath    = $tp->replaceConstants($theme['thumbnail'],'abs');

		$version = $tp->filter(e_VERSION,'version');
		$compat = $tp->filter($theme['compatibility'], 'version');

		$disabled = '';
		$mainTitle = TPVLAN_10;

		if(version_compare($compat,$version, '<=') === false)
		{
			$disabled = 'disabled';
			$mainTitle = defset('TPVLAN_97', "This theme requires a newer version of e107.");
		}

	//	e107::getDebug()->log($theme['path']." - ".$disabled. "  (".$compat.")");

		$main_icon 		= ($pref['sitetheme'] != $theme['path']) ? "<button class='btn btn-default btn-secondary btn-small btn-sm btn-inverse' type='submit' ".$disabled."   name='selectmain[".$theme['path']."]' alt=\"".$mainTitle."\" title=\"".$mainTitle."\" >".$tp->toGlyph('fa-home',array('size'=>'2x'))."</button>" : "<button class='btn btn-small btn-default btn-secondary btn-sm btn-inverse' type='button'>".$tp->toGlyph('fa-check',array('size'=>'2x'))."</button>";
		$info_icon 		= "<a class='btn btn-default btn-secondary btn-small btn-sm btn-inverse e-modal'  data-modal-caption=\"".$theme['name']." ".$theme['version']."\" href='".$infoPath."'  title='".TPVLAN_7."'>".$tp->toGlyph('fa-info-circle',array('size'=>'2x'))."</a>";
	//	$admin_icon 	= ($pref['admintheme'] != $theme['path'] ) ? "<button class='btn btn-default btn-small btn-sm btn-inverse' type='submit'   name='selectadmin[".$theme['id']."]' alt=\"".TPVLAN_32."\" title=\"".TPVLAN_32."\" >".$tp->toGlyph('fa-gears',array('size'=>'2x'))."</button>" : "<button class='btn btn-small btn-default btn-sm btn-inverse' type='button'>".$tp->toGlyph('fa-check',array('size'=>'2x'))."</button>";

		$preview_icon 	= "<a class='e-modal btn btn-default btn-secondary btn-sm btn-small btn-inverse' title=' ".TPVLAN_70." ".$theme['name']."' data-modal-caption=\"".$theme['name']." ".$theme['version']."\" rel='external'  href='".$previewPath."'>".$tp->toGlyph('fa-search',array('size'=>'2x'))."</a>";

		return $main_icon.$info_icon.$preview_icon;
	}


	private function onlineOptions($theme)
	{
		$tp = e107::getParser();
		$preview_icon = '';

		$srcData = array(
				'id'    => $theme['id'],
				'url'   => $theme['url'],
				'mode'  => $theme['mode'],
				'price' => $theme['price']
		);

		e107::getSession()->set('thememanager/online/'.$theme['id'], $theme);

		$d = http_build_query($srcData,false,'&');
		$base64 = base64_encode($d);

		$id = $this->name2id($theme['name']);

		if(!empty($theme['price'])) // Premium Theme
		{
			$LAN_DOWNLOAD = LAN_PURCHASE."/".LAN_DOWNLOAD;
			$downloadUrl = e_SELF.'?mode=main&action=download&src='.base64_encode($d); // no iframe.
			$mainTarget = '_blank';
			$mainClass = '';
			$modalCaption = ' '.LAN_PURCHASE.' '.$theme['name']." ".$theme['version'];
		}
		else // Free Theme
		{
			$LAN_DOWNLOAD = LAN_DOWNLOAD;
			$downloadUrl = e_SELF.'?mode=main&iframe=1&action=download&src='.base64_encode($d);//$url.'&amp;action=download';
			$mainTarget = '_self';
			$mainClass = 'e-modal';
			$modalCaption =  ' '.LAN_DOWNLOADING.' '.$theme['name']." ".$theme['version'];
		}

	//	$url = e_SELF."?src=".$base64;
		$infoUrl = e_SELF.'?mode=main&iframe=1&action=info&src='.$base64;
	//	$viewUrl = $theme['url'];
		$main_icon = "<a class='".$mainClass." btn-default btn-secondary btn btn-sm btn-small btn-inverse' target='".$mainTarget."' data-modal-caption=\"".$modalCaption."\"  href='{$downloadUrl}' data-cache='false' title='".$LAN_DOWNLOAD."' >".$tp->toGlyph('fa-download',array('size'=>'2x'))."</a>";
		$info_icon 	= "<a class='btn btn-default btn-secondary btn-sm btn-small btn-inverse e-modal' data-toggle='modal' data-modal-caption=\"".$theme['name']." ".$theme['version']."\" href='".$infoUrl."' data-cache='false'  title='".TPVLAN_7."'>".$tp->toGlyph('fa-info-circle',array('size'=>'2x'))."</a>";

		if(!empty($theme['preview'][0]))
		{
			$previewPath = $theme['preview'][0];

			if(!empty($theme['livedemo']))
			{
				$previewPath = $theme['livedemo'];
			}

			$preview_icon 	= "<a class='e-modal btn btn-default btn-secondary btn-sm btn-small btn-inverse' title=' ".TPVLAN_70." ".$theme['name']."' data-modal-caption=\"".$theme['name']." ".$theme['version']."\" rel='external'  href='".$previewPath."'>".$tp->toGlyph('fa-search',array('size'=>'2x'))."</a>";
		}

		return $main_icon.$info_icon.$preview_icon;

	}
	
}






class theme_builder extends e_admin_ui
{
	var $themeName = "";
	var $remove = array();

		function init()
		{






		}

		function MainPage()
		{
				$ns = e107::getRender();
			$tp = e107::getParser();

			e107::getMessage()->addDebug("Disable debug to save generated files. ");


			$this->themeName = $tp->filter($_GET['newtheme'],'w');

			if(!empty($_GET['src']))
			{
				$this->themeSrc = $tp->filter($_GET['src'],'w');
				$this->copyTheme();
			/*	$src = $tp->filter($_GET['src'],'w');
				$name = $tp->filter($_GET['f']);
				$title = $tp->filter($_GET['t']);

				$this->copyTheme($src,$name,$title);*/

			}



			if(vartrue($_GET['step']) == 3)
			{
				return $this->step3();

			}

			if(vartrue($_GET['step']) == 2)
			{
				return $this->step2();
			}
			else
			{
				$ret = $this->step1();
				$ret2 = $this->copyThemeForm();

				$tabs = array(
					0 => array('caption'=>$ret['caption'], 'text'=>$ret['text']),
					1 => array('caption'=>$ret2['caption'], 'text'=>$ret2['text']),

				);

				return e107::getForm()->tabs($tabs);

				// $text = $ns->tablerender(ADLAN_140.SEP.ADLAN_CL_6,e107::getForm()->tabs($tabs));
			}


		}

		function step1()
		{

			$fl = e107::getFile();
			$frm = e107::getForm();
			$ns = e107::getRender();
			$mes = e107::getMessage();

			$plugFolders = $fl->get_dirs(e_THEME);
			foreach($plugFolders as $dir)
			{
				if(file_exists(e_THEME.$dir."/theme.xml") || $dir == 'templates')
				{
					continue;
				}
				$newDir[$dir] = $dir;
			}

			$mes->addInfo(' '.TPVLAN_64.' <br />
       '.TPVLAN_65.'
            <ul>
						<li> '.TPVLAN_66.'</li>
						<li> '.TPVLAN_67.'</li>
				</ul>
	');

			$text = $frm->open('createPlugin','get',e_SELF."?mode=convert");
			$text .= "<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
				<tr>
					<td> ".TPVLAN_68."</td>
					<td>".$frm->select("newtheme",$newDir)."</td>
				</tr>";

				/*
				$text .= "
				<tr>
					<td>Create Files</td>
					<td>".$frm->checkbox('createFiles',1,1)."</td>
				</tr>";
				*/

			$text .= "</tbody>
				</table>
				<div class='buttons-bar center'>
				".$frm->admin_button('step', 2,'other',LAN_GO)."
				</div>";

			$text .= $frm->close();

			return array('caption'=>TPVLAN_88, 'text'=>$mes->render() . $text);

		//	$ns->tablerender(TPVLAN_26.SEP.TPVLAN_88.SEP. TPVLAN_CONV_1, $mes->render() . $text);

		}

		function step2()
		{
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$frm = e107::getForm();




			$data = array(
				'main' 			=> array('name','lang','version','date', 'compatibility'),
			    'author' 		=> array('name','url'),
				'summary'		=> array('summary'),
				'description' 	=> array('description'),

				'keywords' 		=> array('one','two'),
				'category'		=> array('category'),
				'livedemo'      => array('livedemo'),
				'copyright' 	=> array('copyright'),
				'stylesheets' 	=> array('stylesheets')
		//		'adminLinks'	=> array('url','description','icon','iconSmall','primary'),
		//		'sitelinks'		=> array('url','description','icon','iconSmall')
			);

			$legacyFile = e_THEME.$this->themeName."/theme.php";



			$newThemeXML = e_THEME.$this->themeName."/theme.xml";
			if(file_exists($newThemeXML))
			{
				$info = e107::getTheme()->getThemeInfo($this->themeName);

				e107::getDebug()->log($info);

				if($this->themeSrc) // New theme copied from another
				{
					$defaults = array(
						"main-name"				=> ucfirst($this->themeName),
						'category-category'     => vartrue($info['category']),
					);
				}
				else
				{
					$defaults = array(
						"main-name"					=> vartrue($info['name']),
						"main-date"                 => vartrue($info['date']),
						"main-version"				=> vartrue($info['version']),
						"author-name"				=> vartrue($info['author']),
						"author-url"				=> vartrue($info['website']),
						"description-description"	=> vartrue($info['description']),
						"summary-summary"			=> vartrue($info['summary']),
						'category-category'         => vartrue($info['category']),
				//		"custompages"				=> vartrue($leg['CUSTOMPAGES']),
					);
				}

				if(!empty($info['keywords']['word']))
				{
					$defaults['keywords-one'] = $info['keywords']['word'][0];
					$defaults['keywords-two'] = $info['keywords']['word'][1];
				}

			}
			elseif(file_exists($legacyFile))
			{
				$legacyData = file_get_contents($legacyFile);

				$regex = '/\$([\w]*)\s*=\s*("|\')([\w @.\/:<\>,\'\[\] !()]*)("|\');/im';
				preg_match_all($regex, $legacyData, $matches);

				$leg = array();

				foreach($matches[1] as $i => $m)
				{
					$leg[$m] = strip_tags($matches[3][$i]);
					if(substr($m,0,5) == 'theme' || $m == "CUSTOMPAGES")
					{
						$search[] = $matches[0][$i];
					}
				}

				$defaults = array(
					"main-name"					=> vartrue($leg['themename']),
					"author-name"				=> vartrue($leg['themeauthor']),
					"author-url"				=> vartrue($leg['themewebsite']),
					"description-description"	=> '',
					"summary-summary"			=> vartrue($leg['themeinfo']),
					"custompages"				=> vartrue($leg['CUSTOMPAGES']),
				);

				$search[] = "Steve Dunstan";
				$search[] = "jalist@e107.org";

				$_SESSION['themebulder-remove'] = $search;

				$mes->addInfo("Loading theme.php file");
			}

			$text = $frm->open('newtheme-step3','post', e_SELF.'?mode=convert&src='.$this->themeSrc.'&newtheme='.$this->themeName.'&step=3');
			$text .= "<table class='table adminlist'>";
			foreach($data as $key=>$val)
			{
				$text.= "<tr><td>$key</td><td>
				<div class='controls'>";
				foreach($val as $type)
				{
					$nm = $key.'-'.$type;
					$name = "xml[$nm]";
					$size = (count($val)==1) ? 'col-md-7' : 'col-md-2';
					$text .= "<div class='{$size}'>".$this->xmlInput($name, $key."-". $type, vartrue($defaults[$nm]))."</div>";
				}

				$text .= "</div></td></tr>";


			}


			$text .= "</table>";
			$text .= "
			<div class='buttons-bar center'>"
			.$frm->hidden('newtheme', $this->themeName);
			$text .= $frm->hidden('xml[custompages]', trim(vartrue($leg['CUSTOMPAGES'])))
			.$frm->admin_button('step', 3,'other',LAN_GENERATE)."
			</div>";

			$text .= $frm->close();

		//	return array('caption'=>TPVLAN_88.SEP. TPVLAN_CONV_2, 'text'=>$mes->render() . $text);
			$this->addTitle(TPVLAN_CONV_2);

			return $text;

			//$ns->tablerender(TPVLAN_26.SEP.ADLAN_CL_6.SEP. TPVLAN_CONV_2, $mes->render() . $text);
		}


		function step3()
		{
			$ns = e107::getRender();
			$mes = e107::getMessage();

		//	print_a($_POST);
			$text = '';

			if(!empty($_POST['xml']))
			{
				$xmlText =	$this->createXml($_POST['xml']);
				$text = $mes->render(). "<pre>".$xmlText."</pre>";
			//	$ns->tablerender("theme.xml", $mes->render(). "<pre>".$xmlText."</pre>");
			}


			$legacyFile = e_THEME.$this->themeName."/theme.php";
			if(file_exists($legacyFile) && empty($this->themeSrc))
			{
				$legacyData = file_get_contents($legacyFile);
				$legacyData = e107::getTheme()->upgradeThemeCode($legacyData);

				$output = nl2br(htmlentities($legacyData));

				$text .= $output;
			//	$ns->tablerender("theme.php (updated)",  $output);
			}

			return $text;
		}




		function createXml($data)
		{
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$tp = e107::getParser();

			foreach($data as $key=>$val)
			{
				$key = strtoupper(str_replace("-","_",$key));
				$newArray[$key] = $val;
			}

			if(!empty($newArray['CUSTOMPAGES']))
			{
				$newArray['CUSTOMPAGES'] = trim($newArray['CUSTOMPAGES']);
				$LAYOUTS = "\n<layout name='custom' title='Custom'>\n";
				$LAYOUTS .= "			<custompages>{CUSTOMPAGES}</custompages>\n";
				$LAYOUTS .= "		</layout>";
			}
			else
			{
				$LAYOUTS = "";
			}

			if(!empty($newArray['STYLESHEETS_STYLESHEETS']))
			{
				$STYLESHEETS = '';
				foreach($newArray['STYLESHEETS_STYLESHEETS'] as $val)
				{
					if(empty($val['file']))
					{
						continue;
					}

					$STYLESHEETS .= "\t\t<css file=\"".$val['file']."\" name=\"".$val['name']."\" />\n";
				}

				if(!empty($STYLESHEETS))
				{
					$STYLESHEETS = "\n\t<stylesheets>\n".$STYLESHEETS."\t</stylesheets>";
				}

				unset($newArray['STYLESHEETS_STYLESHEETS']);
			}
			else
			{
				$STYLESHEETS = "";
			}

			$newArray['STYLESHEETS'] = $STYLESHEETS;

		//	print_a($newArray);


$template = <<<TEMPLATE
<?xml version="1.0" encoding="utf-8"?>
<e107Theme name="{MAIN_NAME}" lan="{MAIN_LANG}" version="{MAIN_VERSION}" date="{MAIN_DATE}" compatibility="{MAIN_COMPATIBILITY}" livedemo="{LIVEDEMO_LIVEDEMO}">
	<author name="{AUTHOR_NAME}" url="{AUTHOR_URL}" />
	<summary lan="">{SUMMARY_SUMMARY}</summary>
	<description lan="">{DESCRIPTION_DESCRIPTION}</description>
	<category>{CATEGORY_CATEGORY}</category>
	<keywords>
		<word>{KEYWORDS_ONE}</word>
		<word>{KEYWORDS_TWO}</word>
	</keywords>
	<copyright>{COPYRIGHT_COPYRIGHT}</copyright>
	<screenshots>
		<image>preview.jpg</image>
		<image>fullpreview.jpg</image>
	</screenshots>{STYLESHEETS}
	<layouts>
		<layout name='default' title='Default' default='true' />{LAYOUTS}
	</layouts>
</e107Theme>
TEMPLATE;


			$template = str_replace("{LAYOUTS}",$LAYOUTS, $template);

			$result = e107::getParser()->simpleParse($template, $newArray);
			$path = e_THEME.$this->themeName."/theme.xml";


			if(E107_DEBUG_LEVEL > 0)
			{
				$mes->addDebug("Debug Mode active - no file saved. ");
				return  htmlentities($result);
			}



			if(file_put_contents($path,$result))
			{
				$mes->addSuccess("Saved: ".$path);
			}
			else
			{
				$mes->addError("Couldn't Save: ".$path);
			}



			return  htmlentities($result);


		}



		function xmlInput($name, $info, $default='')
		{
			$frm = e107::getForm();
			list($cat,$type) = explode("-",$info);

			$size 		= 30;
			$help		= '';
			$sizex      = '';

			switch ($info)
			{

				case 'main-name':
					$help 		= TPVLAN_CONV_3;
					$required 	= true;
					$pattern 	= "[A-Za-z 0-9]*";
				break;

				case 'main-lang':
					$help 		= TPVLAN_CONV_4;
					$required 	= false;
					$placeholder= "LAN equivalent";
					$pattern 	= "[A-Z0-9_]*";
				break;

				case 'main-date':
					$help 		= TPVLAN_CONV_6;
					$required 	= true;
					$default = (empty($default)) ? time() : strtotime($default);
				break;

				case 'main-version':
					$default 	= '1.0';
					$required 	= true;
					$help 		= TPVLAN_CONV_5;
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
				break;

				case 'main-compatibility':
					$default 	= '2.0';
					$required 	= true;
					$help 		= TPVLAN_CONV_7;
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
				break;

				case 'author-name':
					$default 	= (vartrue($default)) ? $default : USERNAME;
					$required 	= true;
					$help 		= TPVLAN_CONV_8;
					$pattern	= "[A-Za-z \.0-9]*";
				break;

				case 'author-url':
					$required 	= true;
					$help 		= TPVLAN_CONV_9;
				//	$pattern	= "https?://.+";
				break;

				case 'livedemo-livedemo':
					$required 	= false;
					$help 		= TPVLAN_CONV_16;
					$placeholder= "http://demo-of-my-theme.com";
				//	$pattern	= "https?://.+";
				break;

				//case 'main-installRequired':
				//	return "Installation required: ".$frm->radio_switch($name,'',LAN_YES, LAN_NO);
				//break;

				case 'summary-summary':
					$help 		= TPVLAN_CONV_10;
					$required 	= true;
					$size 		= 200;
					$placeholder= " ";
					$pattern	= "[A-Za-z,() \.0-9]*";
				break;

				case 'keywords-one':
				case 'keywords-two':
					$help 		= TPVLAN_CONV_11;
					$required 	= true;
					$size 		= 20;
					$placeholder= " ";
					$pattern 	= '^[a-z]*$';
				break;

				case 'description-description':
					$help 		= TPVLAN_CONV_12;
					$required 	= true;
					$size 		= 100;
					$placeholder = " ";
					$pattern	= "[A-Za-z \.0-9]*";
				break;


				case 'category-category':
					$help 		= TPVLAN_CONV_13;
					$required 	= true;
					$size 		= 20;
				break;

				default:

				break;
			}

			$req = ($required == true) ? "&required=1" : "";
			$placeholder = (varset($placeholder)) ? $placeholder : $type;
			$pat = ($pattern) ? "&pattern=".$pattern : "";
			$text = '';

			switch ($type)
			{

				case 'stylesheets':
					$fl = e107::getFile();

					$fl->setMode('full');
					$stylesheets = $fl->get_files(e_THEME.$this->themeName."/", "\.css", null, 1);
					foreach($stylesheets as $key=>$path)
					{
						$file = str_replace(e_THEME.$this->themeName."/",'',$path);
						$text .= "<div class='row-fluid'>";
						$text .= "<div class='controls'>";
						$text .= "<div class='col-md-3'>".$frm->checkbox($name.'['.$key.'][file]',$file, false, array('label'=>$file))."
						<div class='field-help'>".TPVLAN_CONV_14."</div></div>";
						$text .= "<div class='col-md-3'>".$frm->text($name.'['.$key.'][name]', $default, $size, 'placeholder='.$file . $req. $pat)."
						<div class='field-help'>".TPVLAN_CONV_15."</div></div>";
					//	$text .= "<div class='span2'>".$frm->checkbox('css['.$key.'][file]',$file, false, array('label'=>$file))."</div>";
					//	$text .= "<div class='span2'>".$frm->text('css['.$key.'][name]', $default, $size, 'placeholder='.$placeholder . $req. $pat)."</div>";
						$text .= "</div>";
						$text .= "</div>";
					}


					return $text;
				break;


				case 'date':
					$text = $frm->datepicker($name, $default, 'format=yyyy-mm-dd'.$req.'&size=block-level');
				break;

				case 'description':
					$text = $frm->textarea($name,$default, 3, 100, $req.'&size=block-level');	// pattern not supported.
				break;


				case 'category':

				$allowedCategories = array(
					'generic', 'adult', 'blog', 'clan', 'children',
					'corporate', 'forum', 'gaming', 'gallery', 'news',
		 			'social', 'video', 'multimedia');

				sort($allowedCategories);

					$text = $frm->select($name, $allowedCategories,$default,'useValues=1&required=1', true);
				break;


				default:
					$text = $frm->text($name, $default, $size, 'placeholder='.$placeholder . $req. $pat.'&size=block-level');
				break;
			}


			$text .= ($help) ? "<span class='field-help'>".$help."</span>" : "";
			return $text;

		}





		function copyThemeForm()
		{

			$frm = e107::getForm();

			$list = e107::getTheme()->clearCache()->getThemeList(); // (e_THEME);

			$folders = array_keys($list);

			$text = $frm->open('copytheme','get','theme.php?mode=convert');
			$text .= "<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
				<tr>
					<td>".TPVLAN_91."</td>
					<td>".$frm->select("src",$folders,'',array('useValues'=>1))."</td>
				</tr>

				<tr>
					<td>".TPVLAN_92."</td>
					<td>".$frm->text("newtheme",'',25, array('pattern'=>'[a-z_0-9]*', 'required'=>1))."</td>
				</tr>

				";

				/*
				$text .= "
				<tr>
					<td>Create Files</td>
					<td>".$frm->checkbox('createFiles',1,1)."</td>
				</tr>";
				*/

			$text .= "
				</table>
					<div class='buttons-bar center'>
				".$frm->admin_button('step', 2,'success', LAN_CREATE)."
				</div>";




				$text .= $frm->close();


		//	$text = "Create a new theme based on ".e->select('copytheme',$list);


			return array('caption'=>LAN_CREATE, 'text'=>$text);

		}

		private function copyTheme()
		{
			if(empty($this->themeSrc) || empty($this->themeName) || is_dir(e_THEME.$this->themeName))
			{
				return false;
			}

			if(e107::getFile()->copy(e_THEME.$this->themeSrc, e_THEME.$this->themeName))
			{
				$newfiles = scandir(e_THEME.$this->themeName);

				foreach($newfiles as $file)
				{
					if(is_dir(e_THEME.$this->themeName.'/'.$file) || $file === '.' || $file === '..')
					{
						continue;
					}

					if(strpos($file,"admin_") === 0)
					{
						unlink(e_THEME.$this->themeName.'/'.$file);
					}



				}

			}

			return null;
		}




}

/*
 * After initialization we'll be able to call dispatcher via e107::getAdminUI()
 * so this is the first we should do on admin page.
 * Global instance variable is not needed.
 * NOTE: class is auto-loaded - see class2.php __autoload()
 */
/* $dispatcher = */

new theme_admin();

/*
 * Uncomment the below only if you disable the auto observing above
 * Example: $dispatcher = new theme_admin(null, null, false);
 */
//$dispatcher->runObservers(true);

require_once(e_ADMIN."auth.php");

/*
 * Send page content
 */
e107::getAdminUI()->runPage();




require_once(e_ADMIN."footer.php");

/* OBSOLETE - see admin_shortcodes::sc_admin_menu()
function admin_config_adminmenu() 
{
	//global $rp;
	//$rp->show_options();
	e107::getRegistry('admin/blank_dispatcher')->renderMenu();
}
*/

/* OBSOLETE - done within header.php
function headerjs() // needed for the checkboxes - how can we remove the need to duplicate this code?
{
	return e107::getAdminUI()->getHeader();
}
*/

