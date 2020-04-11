<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

$frm = e107::getForm();

class e_menuManager {

        var $menu_areas = array();
        var $curLayout;
        var $menuId;
		var $menuNewLoc;
		var $dragDrop;
		var $menuActivateLoc;
        var $menuActivateIds;
		var $debug;
		var $menuMessage;
		var $style = 'default';
		private $menuData = array();

		function __construct($dragdrop=FALSE)
		{
        		global $HEADER,$FOOTER, $NEWSHEADER;
        		$pref = e107::getPref();
			$tp = e107::getParser();

                $this->debug = FALSE;


                $this->dragDrop = $dragdrop;



				if($this->dragDrop)
				{
                	$this->debug = TRUE;
				}

                if ($NEWSHEADER)
				{
					$HEADER .= $NEWSHEADER;
				}


                if(isset($_POST['custom_select']))
				{
					$this->curLayout =  $_POST['custom_select'];
				}
				elseif(isset($_GET['lay']))
				{
                	$this->curLayout =  $_GET['lay'];
				}
				else
				{
					$this->curLayout = vartrue($_GET['configure'], $pref['sitetheme_deflayout']);
				}

				$this->curLayout =  $tp->filter($this->curLayout);

				$this->dbLayout = ($this->curLayout != $pref['sitetheme_deflayout']) ? $this->curLayout : "";  //menu_layout is left blank when it's default.

				if(isset($_POST['menu_id']) || vartrue($_GET['id']))
				{
                	$this->menuId = (isset($_POST['menu_id'])) ? intval($_POST['menu_id']) : intval($_GET['id']);
				}




				if (/*$menu_act == "sv" || */isset($_POST['class_submit']))
				{
					$this->menuSaveVisibility();
				}
				elseif(isset($_POST['parms_submit']))
				{
					$this->menuSaveParameters();
				}

                if (vartrue($_GET['mode']) == "deac")
				{
				 	$this->menuDeactivate();
				}

				if ($_GET['mode'] == "conf")
				{
				 	$this->menuGoConfig();
				}



				$this->menuGrabLayout();

	        	$menu_array = $this->parseheader($HEADER.$FOOTER, 'check');



				if($menu_array)
				{
					sort($menu_array, SORT_NUMERIC);
					$menu_check = 'set';
					foreach ($menu_array as $menu_value)
					{
						if ($menu_value != $menu_check)
						{
					   		$this->menu_areas[] = $menu_value;
						}
						$menu_check = $menu_value;
					}
                }

				$this->menuModify();

            	if(!empty($_POST['menuActivate']))
				{
					$menuActivate = $tp->filter($_POST['menuActivate']);
                    $this->menuActivateLoc = key($menuActivate);
					$this->menuActivateIds = $tp->filter($_POST['menuselect']);
					$this->menuActivate();

				}

				$this->loadMenuData();

				if(vartrue($_POST['menuSetCustomPages']))
				{
					$custompages = $tp->filter($_POST['custompages']);
					$this->menuSetCustomPages($custompages);
				}

				if(isset($_POST['menuUsePreset']) && $_POST['curLayout'])
				{
					$this->menuSetPreset();
				}

				$this->menuSetConfigList(); // Update Active MenuConfig List.

		}


	/**
	 * Load the Menu Table data for the current layout.
	 */
	private function loadMenuData()
	{
		$menu_qry = 'SELECT * FROM #menus WHERE menu_location > 0 AND  menu_layout = "'.$this->dbLayout.'" ORDER BY menu_location,menu_order';

		$sql = e107::getDb();

		$eMenuArea = array();

		if($rows = $sql->retrieve($menu_qry, true))
		{

			$lastLoc = -1;
			$c = 0;
			foreach($rows as $row)
			{
				$loc = intval($row['menu_location']);

				if($lastLoc != $loc)
				{
					$c = 1;
				}

				if($c !== intval($row['menu_order'])) // fix the order if it is off..
				{
					if($sql->update('menus', "menu_order= ".$c." WHERE menu_id = ".$row['menu_id']." LIMIT 1"))
					{
						$row['menu_order'] = $c;
					}

				}

				$eMenuArea[$loc][] = $row;

				$lastLoc = $loc;
				$c++;
			}
		}

		$this->menuData = $eMenuArea;

	}


// -------------------------------------------------------------------------

	function menuRenderIframe($url='')
	{ 
		$ns = e107::getRender();
		$sql = e107::getDb();

        if(!$url)
		{
        	$url = e_SELF."?configure=".$this->curLayout;
		}

	//	$cnt = $sql->select("menus", "*", "menu_location > 0 AND menu_layout = '$curLayout' ORDER BY menu_name "); // calculate height to remove vertical scroll-bar.

	//	$text = "<object class='well' type='text/html' id='menu_iframe' data='".$url."' width='100%' style='overflow:auto;width: 100%; height: ".(($cnt*90)+600)."px; border: 0px' ></object>";
		$text = "<iframe class='well' id='menu_iframe' name='e-mm-iframe' src='".$url."' width='100'   ></iframe>";
	
		return $text;
	}


	function menuRenderMessage()
	{
	  //	return $this->menuMessage;
		$text = e107::getMessage()->render('menuUi');
	  //	$text .= "ID = ".$this->menuId;
		return $text;
		
	}


	function menuAddMessage($message, $type = E_MESSAGE_INFO, $session = false)
	{
 		e107::getMessage()->add(array($message, 'menuUi'), $type, $session);
	}

    // -------------------------------------------------------------------------

    function menuGrabLayout()
	{
		global $HEADER,$FOOTER,$CUSTOMHEADER,$CUSTOMFOOTER,$LAYOUT;

		// new v2.3
		if($tmp = e_theme::loadLayout($this->curLayout))
		{
			$LAYOUT = $tmp;
		}

		if(isset($LAYOUT) && is_array($LAYOUT)) // $LAYOUT is a combined $HEADER,$FOOTER. 
		{
			$HEADER = array();
			$FOOTER = array();
			foreach($LAYOUT as $key=>$template)
			{
				list($hd,$ft) = explode("{---}",$template);
				$HEADER[$key] = isset($LAYOUT['_header_']) ? $LAYOUT['_header_'] . $hd : $hd;
				$FOOTER[$key] = isset($LAYOUT['_footer_']) ? $ft . $LAYOUT['_footer_'] : $ft ;		
			}	
			unset($hd,$ft);
		}
			
      	if(($this->curLayout == 'legacyCustom' || $this->curLayout=='legacyDefault') && (isset($CUSTOMHEADER) || isset($CUSTOMFOOTER)) )  // 0.6 themes.
		{
		 	if($this->curLayout == 'legacyCustom')
			{
				$HEADER = ($CUSTOMHEADER) ? $CUSTOMHEADER : $HEADER;
				$FOOTER = ($CUSTOMFOOTER) ? $CUSTOMFOOTER : $FOOTER;
			}
		}
		elseif($this->curLayout && $this->curLayout != "legacyCustom" && (isset($CUSTOMHEADER[$this->curLayout]) || isset($CUSTOMFOOTER[$this->curLayout]))) // 0.7 themes
		{
		 // 	echo " MODE 0.7 ".$this->curLayout;
			$HEADER = isset($CUSTOMHEADER[$this->curLayout]) ? $CUSTOMHEADER[$this->curLayout] : $HEADER;
			$FOOTER = is_array($CUSTOMFOOTER) && isset($CUSTOMFOOTER[$this->curLayout]) ? $CUSTOMFOOTER[$this->curLayout] : $FOOTER;
		}
	    elseif($this->curLayout && is_array($HEADER) && isset($HEADER[$this->curLayout]) && isset($FOOTER[$this->curLayout])) // 0.8 themes - we use only $HEADER and $FOOTER arrays.
		{
		//  echo " MODE 0.8 ".$this->curLayout;
			$HEADER = $HEADER[$this->curLayout];
			$FOOTER = $FOOTER[$this->curLayout];
		}

       // Almost the same code as found in templates/header_default.php  ---------

	}

    function menuGoConfig()
	{
		if(!$_GET['path'] || ($_GET['mode'] != "conf"))
		{
			return;
		}

		$file = urldecode($_GET['path']).".php";
		$file = e107::getParser()->filter($file);
		$newurl = e_PLUGIN_ABS.$file."?id=".intval($_GET['id']).'&iframe=1';

     /*



	  return "<object type='text/html' id='menu_iframe' data='".$newurl."' width='100%' style='overflow:auto;width: 100%; border: 0px' ></object>";

*/
		header("Location: ".$newurl);
		exit;
	 //	echo "URL = ".$newurl;
	  //	$newurl = $PLUGINS_DIRECTORY.$location."/{$position}{$this->menuNewLoc}.php";
	  //	$newurl = SITEURL.str_replace("//", "/", $newurl);
	  //	echo "<script type='text/javascript'>alert($newurl);	top.location.href = '{$newurl}'; </script> ";
	//	exit;


	}

	// -----------------------------------------------------------------------------

	    function menuModify()
		{
			$sql = e107::getDb();
			$tp = e107::getParser();

			$menu_act = "";

			if(isset($_POST['menuAct']))
			{
				foreach($_POST['menuAct'] as $k => $v)
				{
					if(trim($v))
					{
						$value = $tp->filter($_POST['menuAct'][$k]);
						$this->menuId = intval($k);
						list($menu_act, $location, $position, $this->menuNewLoc) = explode(".", $value);
					}
				}
			}

			if ($menu_act == "move")
			{
			 	$this->menuMove();
			}

			if (isset($location) && isset($position) && $menu_act == "bot")
			{
				$menu_count = $sql->count("menus", "(*)", " WHERE menu_location='{$location}' AND menu_layout = '".$this->dbLayout."'  ");
				$sql->db_Update("menus", "menu_order=".($menu_count+1)." WHERE menu_order='{$position}' AND menu_location='{$location}' AND menu_layout = '$this->dbLayout'  ");
				$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_location='{$location}' AND menu_order > {$position} AND menu_layout = '".$this->dbLayout."' ");
				e107::getLog()->add('MENU_06',$location.'[!br!]'.$position.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}

			if (isset($location) && isset($position) && $menu_act == "top")
			{
				$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_location='{$location}' AND menu_order < {$position} AND menu_layout = '".$this->dbLayout."' ",$this->debug);
				$sql->db_Update("menus", "menu_order=1 WHERE menu_id='{$this->menuId}' ");
				e107::getLog()->add('MENU_05',$location.'[!br!]'.$position.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}

			if (isset($location) && isset($position) && $menu_act == "dec")
			{
				$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_order='".($position+1)."' AND menu_location='{$location}' AND menu_layout = '".$this->dbLayout."' ",$this->debug);
				$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_id='{$this->menuId}' AND menu_location='{$location}' AND menu_layout = '".$this->dbLayout."' ");
				e107::getLog()->add('MENU_08',$location.'[!br!]'.$position.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}

			if (isset($location) && isset($position) && $menu_act == "inc")
			{
				$sql->db_Update("menus", "menu_order=menu_order+1 WHERE menu_order='".($position-1)."' AND menu_location='{$location}' AND menu_layout = '".$this->dbLayout."' ",$this->debug);
				$sql->db_Update("menus", "menu_order=menu_order-1 WHERE menu_id='{$this->menuId}' AND menu_location='{$location}' AND menu_layout = '".$this->dbLayout."' ");
				e107::getLog()->add('MENU_07',$location.'[!br!]'.$position.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}

			if (!isset($_GET['configure']))
			{  // Scan plugin directories to see if menus to add
			    $this->menuScanMenus();
			}

		}






	// ----------------------------------------------------------------------------

	function menuSetPreset()
	{
		global $location;

		$sql = e107::getDb();
		$tp = e107::getParser();

		if(!$menuAreas = $this->getMenuPreset())
		{
			e107::getMessage()->addDebug("No Menu Preset Found");
			return false;
		}

		$sql->db_Update("menus", "menu_location='0' WHERE menu_layout = '" . $this->dbLayout . "' "); // Clear All existing.

		foreach($menuAreas as $val)
		{
			if($sql->select("menus", 'menu_name, menu_path', "menu_name = '" . $tp->filter($val['menu_name']) . "' LIMIT 1"))
			{
				$row = $sql->fetch();

				if(!$sql->db_Update('menus', "menu_order='" . (int) $val['menu_order'] . "', menu_location = " . (int) $val['menu_location'] . ", menu_class= " . $val['menu_class'] . " WHERE menu_name='" . $tp->filter($val['menu_name']) . "' AND menu_layout = '" . $this->dbLayout . "' LIMIT 1 "))
				{
					$insert = array(
						'menu_id'       => 0,
						'menu_name'     => $tp->filter($val['menu_name']),
						'menu_location' => (int) $val['menu_location'],
						'menu_order'    => (int) $val['menu_order'],
						'menu_class'    => $tp->filter($val['menu_class']),
						'menu_pages'    => '',
						'menu_path'     => $tp->filter($row['menu_path']),
						'menu_layout'   => $this->dbLayout,
						'menu_parms'    => '',
					);

					$sql->insert("menus", $insert);
					e107::getLog()->add('MENU_01', $tp->filter($row['menu_name']) . '[!br!]' . $location . '[!br!]' . varset($menu_count, 0) . '[!br!]' . $tp->filter($row['menu_path']), E_LOG_INFORMATIVE, '');
				}
			}
		}

		return $menuAreas;
	}


	// ----------------------------------------------------------------------------

	function menuScanMenus()
	{
		global $sql2;
		$sql = e107::getDb();

		$efile = new e_file;
		$efile->dirFilter = array('/', 'CVS', '.svn', 'languages');
		$efile->fileFilter[] = '^e_menu\.php$';

		$fileList = $efile->get_files(e_PLUGIN, "_menu\.php$", 'standard', 2);

		//	$this->menuAddMessage('Scanning for new menus', E_MESSAGE_DEBUG);

		e107::getDebug()->log("Scanning for new menus", E107_DBG_BASIC);

		$menuList = array(); // existing menus in table.
		if($result = $sql->retrieve('menus', 'menu_name', null, true))
		{
			foreach($result as $mn)
			{
				if($mn['menu_name'])
				{
					$menuList[] = $mn['menu_name'];
				}
			}
		}


		//v2.x Scan Custom Page Menus.

		$pageMenus = $sql->retrieve('page', 'page_id, menu_name, menu_title', "menu_name !='' ", true);
		foreach($pageMenus as $row)
		{
			if(!in_array($row['menu_name'], $menuList))
			{
				$insert = array(
					'menu_id'       => 0,
					'menu_name'     => $row['menu_name'],
					'menu_location' => 0,
					'menu_order'    => 0,
					'menu_class'    => 0,
					'menu_pages'    => '',
					'menu_path'     => $row['page_id'],
					'menu_layout'   => '',
					'menu_parms'    => ''
				);

				if($sql->insert("menus", $insert))
				{
					$this->menuAddMessage(MENLAN_10 . " - " . $row['menu_name'], E_MESSAGE_DEBUG);
				}
			}

		}


		$menustr = varset($menustr);
		$message = varset($message);


		foreach($fileList as $file)
		{

			list($parent_dir) = explode('/', str_replace(e_PLUGIN, "", $file['path']));
			$file['path'] = str_replace(e_PLUGIN, "", $file['path']);
			$file['fname'] = str_replace(".php", "", $file['fname']);
			$valid_menu = false;

			$existing_menu = in_array($file['fname'], $menuList); // $sql->count("menus", "(*)", "WHERE menu_name='{$file['fname']}'");
			if(file_exists(e_PLUGIN . $parent_dir . '/plugin.xml') || file_exists(e_PLUGIN . $parent_dir . '/plugin.php'))
			{
				if(e107::isInstalled($parent_dir))
				{  // Its a 'new style' plugin with a plugin.php file, or an even newer one with plugin.xml file - only include if plugin installed
					$valid_menu = true;        // Whether new or existing, include in list
//						echo "Include {$parent_dir}:{$file['fname']}<br />";
				}
			}
			else  // Just add the menu anyway
			{
				$valid_menu = true;
//					echo "Default Include {$parent_dir}:{$file['fname']}<br />";
			}
			if($valid_menu)
			{
				$menustr .= "&" . str_replace(".php", "", $file['fname']);

				if(!$existing_menu)  // New menu to add to list
				{
					$insert = array(
						'menu_id'       => 0,
						'menu_name'     => $file['fname'],
						'menu_location' => 0,
						'menu_order'    => 0,
						'menu_class'    => 0,
						'menu_pages'    => '',
						'menu_path'     => $file['path'],
						'menu_layout'   => '',
						'menu_parms'    => ''
					);

					if($sql->insert("menus", $insert))
					{
						// Could do admin logging here - but probably not needed
						$message .= MENLAN_10 . " - " . $file['fname'] . "<br />"; //FIXME
					}
					else
					{
						$this->menuAddMessage("Couldn't add menu: " . $file['fname'] . " to table ", E_MESSAGE_DEBUG);
					}
				}
			}
		}

		//Reorder all menus into 1...x order
		if(!is_object($sql2))
		{
			$sql2 = new db;
		}        // Shouldn't be needed
		if(!isset($sql3) || !is_object($sql3))
		{
			$sql3 = new db;
		}

		$location_count = $sql3->select("menus", "menu_location", "menu_location>0 GROUP BY menu_location");
		while($location_count)
		{
			if($sql->select("menus", "menu_id", "menu_location={$location_count} ORDER BY menu_order ASC"))
			{
				$c = 1;
				while($row = $sql->fetch())
				{
					$sql2->db_Update("menus", "menu_order={$c} WHERE menu_id=" . $row['menu_id']);
					$c++;
				}
			}
			$location_count--;
		}
		$sql->select("menus", "*", "menu_path NOT REGEXP('[0-9]+') ");
		while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql->fetch('num'))
		{
			if(stristr($menustr, $menu_name) === false)
			{
				$sql2->db_Delete("menus", "menu_name='$menu_name'");
				$message .= MENLAN_11 . " - " . $menu_name . "<br />";
			}
		}

		$this->menuAddMessage(vartrue($message), E_MESSAGE_DEBUG);
	}

	// ---------------------------------------------------------------------------


    function menuPresetPerms($val)
	{
		$link_class = strtolower(trim($val));
   		$menu_perm['everyone'] = e_UC_PUBLIC;
		$menu_perm['guest'] = e_UC_GUEST;
	  	$menu_perm['member'] = e_UC_MEMBER;
		$menu_perm['mainadmin'] = e_UC_MAINADMIN;
		$menu_perm['admin'] = e_UC_ADMIN;
		$menu_perm['nobody'] = e_UC_NOBODY;
		$link_class = isset($menu_perm[$link_class]) ? $menu_perm[$link_class] : e_UC_PUBLIC;

		return $link_class;
	}

	private function menuParamForm($id, $fields,$tabs, e_form $ui, $values=array())
	{
		$fields['menu_id'] = array('type'=>'hidden', 'writeParms'=>array('value'=>$id));
		$fields['mode']  = array('type'=>'hidden', 'writeParms'=>array('value'=>'parms'));

		$forms = $models = array();
		$forms[] = array(
				'id'  => 'e-save',
				'header' => '',
				'footer' => '',
				'url' => e_SELF,
				'query' => "lay=".$this->curLayout,
				'fieldsets' => array(
					'create' => array(
						'tabs'	=>  $tabs, //used within a single form.
						'legend' => '',
						'fields' => $fields, //see e_admin_ui::$fields
						'header' => '', //XXX Unused?
						'footer' => '',
						'after_submit_options' => '', // or true for default redirect options
						'after_submit_default' => '', // or true for default redirect options
						'triggers' => false, // standard create/update-cancel triggers
					)
				)
		);
	//	$models[] = $controller->getModel();
		$models[] = e107::getModel()->setData($values);

		return $ui->renderCreateForm($forms, $models, e_AJAX_REQUEST);




	}



	/**
	 * This one will be greatly extended, allowing menus to offer UI and us 
	 * settings per instance later ($parm variable available for menus - same as shortcode's $parm)
	 */
	function menuInstanceParameters()
	{
		if(!vartrue($_GET['parmsId'])) return;
		$id = intval($_GET['parmsId']);
		$frm = e107::getForm();
		$sql = e107::getDb();
		
		if(!$sql->select("menus", "*", "menu_id=".$id))
		{
        	$this->menuAddMessage("Couldn't Load Menu",E_MESSAGE_ERROR);
            return null;
		};
		$row = $sql->fetch();



		$text = "<div style='text-align:center;'>
		<form  id='e-save-form' method='post' action='".e_SELF."?lay=".$this->curLayout."'>
        <fieldset id='core-menus-parametersform'>
		<legend>".MENLAN_44." ".$row['menu_name']."</legend>
        <table class='table '>
        <colgroup>
            <col class='col-label' />
            <col class='col-control' />
        </colgroup>

		";

		if(file_exists(e_PLUGIN.$row['menu_path']."e_menu.php")) // v2.x new e_menu.php
		{
			$plug = rtrim($row['menu_path'],'/');
			$obj = e107::getAddon($plug,'e_menu');




			if(!is_object($obj))
			{
				$text .= "<tr><td colspan='2' class='alert alert-danger'>".e107::getParser()->lanVars(MENLAN_46, $plug)."</td></tr>";
			}
			else
			{
				$menuName = substr($row['menu_name'],0,-5);
			}

			$menuName = varset($menuName);
			$fields = e107::callMethod($obj,'config',$menuName);
			$tabs = isset($obj->tabs) ? $obj->tabs : array(LAN_CONFIGURE);


			if(!$form = e107::getAddon($plug,'e_menu',$plug."_menu_form"))
			{
				$form = $frm;
			}



			$value = e107::unserialize($row['menu_parms']);




			if(!empty($fields))
			{

				return $this->menuParamForm($id, $fields,$tabs,$form,$value);
				/*

				foreach($fields as $k=>$v)
				{
					$text .= "<tr><td class='text-left'>".$v['title']."</td>";
				//	$v['writeParms']['class'] = 'e-save';
					$i = $k;
					if(!empty($v['multilan']))
					{
						$i = $k.'['.e_LANGUAGE.']';

						if(isset($value[$k][e_LANGUAGE]))
						{
							$value[$k] = varset($value[$k][e_LANGUAGE],'');
						}

					}


					$text .= "<td class='text-left'>".$form->renderElement($i, $value[$k], $v);



					if(!empty($v['help']))
					{
						//$v['writeParms']['title'] = e107::getParser()->toAttribute($v['help']);
						$text .= "<div class='field-help'>".$v['help']."</div>";
					}

					$text .= "</td></tr>";
				}*/

			}
			else
			{
				$text .= "<tr><td colspan='2' class='alert alert-danger'>".MENLAN_47.": ".$row['menu_path']."e_menu.php</td></tr>";
			}

		}
		else
		{
			$text .= "<tr>
			<td>
			".MENLAN_45."</td>
			<td>
			".$frm->text('menu_parms', $row['menu_parms'], 900, 'class=e-save&size=xxlarge')."
			</td>
			</tr>";
		}

		$text .= "</table>";

	/*
		
			$text .= "
			<div class='buttons-bar center'>";
			$text .= $frm->admin_button('parms_submit', LAN_SAVE, 'update');
			$text .= "<input type='hidden' name='menu_id' value='".$id."' />
			</div>";
			
		*/
		$text .= $frm->hidden('mode','parms');
		$text .= $frm->hidden('menu_id',$id);
		$text .= "
		</fieldset>
		</form>
		</div>";
		
		return $text;
	
	}


	function menuVisibilityOptions()
	{
		if(!vartrue($_GET['vis'])) return;

		$sql = e107::getDb();
		$frm = e107::getForm();
		$tp = e107::getParser();

		
		require_once(e_HANDLER."userclass_class.php");
		
		if(!$sql->select("menus", "*", "menu_id=".intval($_GET['vis'])))
		{
        	$this->menuAddMessage(MENLAN_48,E_MESSAGE_ERROR);
            return;
		}
		
		$row = $sql->fetch();
		
		$listtype 	= substr($row['menu_pages'], 0, 1);
		$menu_pages = substr($row['menu_pages'], 2);
		$menu_pages = str_replace("|", "\n", $menu_pages);

		$text = "<div>
			<form class='form-horizontal' id='e-save-form' method='post' action='".e_SELF."?lay=".$this->curLayout."&amp;iframe=1'>
	        <fieldset>
			<legend>". MENLAN_7." ".$row['menu_name']."</legend>
	        <table class='table adminform'>
			<tr>
			<td>
			<input type='hidden' name='menuAct[{$row['menu_id']}]' value='sv.{$row['menu_id']}' />
			".LAN_VISIBLE_TO." ".
			$frm->userclass('menu_class', $row['menu_class'], 'dropdown', array('options'=>"public,member,guest,admin,main,classes,nobody", 'class'=>'e-save'))."
			</td>
			</tr>
			<tr><td><div class='radio'>
		";
		$checked = ($listtype == 1) ? " checked='checked' " : "";
		
		$text .= $frm->radio('listtype', 1, $checked, array('label'=>$tp->toHTML(MENLAN_26,true), 'class'=> 'e-save'));
		$text .= "<br />";
	//	$text .= "<input type='radio' class='e-save' {$checked} name='listtype' value='1' /> ".MENLAN_26."<br />";
		$checked = ($listtype == 2) ? " checked='checked' " : "";
		
		$text .= $frm->radio('listtype', 2, $checked, array('label'=> $tp->toHTML(MENLAN_27,true), 'class'=> 'e-save'));
		
		
		// $text .= "<input type='radio' class='e-save' {$checked} name='listtype' value='2' /> ".MENLAN_27."<br />";
		
		$text .= "</div>
		<div class='row' style='padding:10px'>
			
			<div class='pull-left span3' >
		
				<textarea name='pagelist' class='e-save span3 tbox' cols='60' rows='8'>" . $menu_pages . "</textarea>
			</div>
			<div class='  span4 col-md-4'><small>".MENLAN_28."</small></div>
		</div></td></tr>
		</table>";
		
		$text .= $frm->hidden('mode','visibility'); 
		$text .= $frm->hidden('menu_id',intval($_GET['vis'])); // "<input type='hidden' name='menu_id' value='".intval($_GET['vis'])."' />";
		
		/*
		$text .= "
		<div class='buttons-bar center'>";
        $text .= $frm->admin_button('class_submit', MENLAN_6, 'update');

		
		</div>";
		 */ 
		$text .= "
		</fieldset>
		</form>
		</div>";
	
		
		return $text;
		//$caption = MENLAN_7." ".$row['menu_name'];
		//$ns->tablerender($caption, $text);
		//echo $text;
	}



	// -----------------------------------------------------------------------------


	function menuActivate()    // Activate Multiple Menus.
	{
		$sql = e107::getDb();

		$location = $this->menuActivateLoc;

		$menu_count = $sql->count("menus", "(*)", " WHERE menu_location=".$location." AND menu_layout = '".$this->dbLayout."' ");
		$menu_count++; // Need to add 1 to create NEW order number.
		
		foreach($this->menuActivateIds as $sel_mens)
		{
			//Get info from menu being activated
			if($sql->select("menus", 'menu_name, menu_path' , "menu_id = ".intval($sel_mens)." "))
			{
				$row=$sql->fetch();
				//If menu is not already activated in that area, add the record.
				//$query = "SELECT menu_name,menu_path FROM #menus WHERE menu_name='".$row['menu_name']."' AND menu_layout = '".$this->dbLayout."' AND menu_location = ".$location." LIMIT 1 ";
				//if(!$sql->gen($query, $this->debug))
				{

                   $insert = array(
                        	'menu_id'	=> 0,
							'menu_name' 	=> $row['menu_name'],
							'menu_location'	=> $location,
							'menu_order'	=> $menu_count,
							'menu_class'	=> intval($row['menu_class']),
							'menu_pages'	=> '',
                            'menu_path'		=> $row['menu_path'],
							'menu_layout'  	=> $this->dbLayout,
							'menu_parms'	=> ''
				   );

					$sql->insert("menus",$insert, $this->debug);

					e107::getLog()->add('MENU_01',$row['menu_name'].'[!br!]'.$location.'[!br!]'.$menu_count.'[!br!]'.$row['menu_path'],E_LOG_INFORMATIVE,'');
					$menu_count++;
				}
			}
		}
	}



	// -----------------------------------------------------------------------------


	function menuSetCustomPages($array)
	{
		$pref = e107::getPref();
		$key = key($array);
		$pref['sitetheme_custompages'][$key] = array_filter(explode(" ",$array[$key]));
		save_prefs();
	}


	// ------------------------------------------------------------------------------

	function getMenuPreset()
	{
		$pref = e107::getPref();

		$layout = $this->curLayout;

	    if(!isset($pref['sitetheme_layouts'][$layout]['menuPresets']))
		{
			e107::getMessage()->addDebug(print_a($pref['sitetheme_layouts'],true));
	    	return FALSE;
		}

		$areas = $pref['sitetheme_layouts'][$layout]['menuPresets']['area'];

		foreach($areas as $area => $menus)
		{
			$areaID = $menus['@attributes']['id'];	
			foreach($menus['menu'] as $k=>$v)
			{
				$perm = isset($v['@attributes']['perm']) ? $v['@attributes']['perm'] : null;

				$menuArea[] = array(
					'menu_location' => $areaID,
					'menu_order'	=> $k,
					'menu_name'		=> $v['@attributes']['name']."_menu",
					'menu_class'	=> $this->menuPresetPerms($perm)
				);	
			}
		}
						
		if(E107_DEBUG_LEVEL > 0)
		{
	//		e107::getMessage()->addDebug(print_a($menuArea,true)); 	
		}
		

	   return varset($menuArea, array());

	}


	// ------------------------------------------------------------------------------

	function checkMenuPreset($array,$name)
	{
		if(!is_array($array))
		{
	    	return;
		}
		foreach($array as $key=>$val)
		{
	        if($val['menu_name']==$name)
			{
				return $val['menu_location'];
			}
		}

	    return FALSE;
	}
	
	// --------------------------------------------------------------------------
	
	function menuSaveParameters()
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		$id = intval($_POST['menu_id']);

		if(isset($_POST['menu_parms'])) // generic params
		{
			$parms = $tp->filter($_POST['menu_parms']);
			$parms = $sql->escape(strip_tags($parms));
			$check = $sql->update("menus", "menu_parms=\"".$parms."\" WHERE menu_id=".$id."");
		}
		else // e_menu.php
		{
			unset($_POST['menu_id'], $_POST['mode'], $_POST['menuActivate'], $_POST['menuSetCustomPages'], $_POST['e-token']);

		/*	$tmp = $sql->retrieve("menus", "menu_parms", " menu_id=".$id);
			$parms = !empty($tmp) ? e107::unserialize($tmp) : array();

			foreach($_POST as $k=>$v)
			{
				$parms[$k] = $tp->filter($v);
			}

			$parms = e107::serialize($parms, 'json');*/

		//	if(e_DEBUG == true)
			{
			//	return array('msg'=>print_r($parms,true),'error'=>true);
			}

			$parms = $_POST;

			$check = e107::getMenu()->updateParms($id,$parms); //
		}



		if($check)
		{
			return array('msg'=>'All Okay','error'=>false);
			// FIXME - menu log
			//e107::getLog()->add('MENU_02',$_POST['menu_parms'].'[!br!]'.$parms.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
		//	$this->menuAddMessage(LAN_SAVED,E_MESSAGE_SUCCESS);
		}
		elseif(false === $check)
		{

			return array('msg'=>LAN_UPDATED_FAILED,'error'=>true);
            
		}
		else
		{
			return array('msg'=>'No Changes Made','error'=>false); // $this->menuAddMessage(LAN_NOCHANGE_NOTSAVED,E_MESSAGE_INFO);
		}
	}


	// --------------------------------------------------------------------------

	function menuSaveVisibility() // Used by Ajax
	{
		$tp = e107::getParser();
		$sql = e107::getDb();

		$pageList = $tp->filter($_POST['pagelist']);
		$listType = $tp->filter($_POST['listtype']);

		$pagelist = explode("\r\n", $pageList);

		for ($i = 0 ; $i < count($pagelist) ; $i++)
		{
			$pagelist[$i] = trim($pagelist[$i]);
		}
		$plist = implode("|", $pagelist);
		$pageparms = $listType.'-'.$plist;
		$pageparms = preg_replace("#\|$#", "", $pageparms);
		$pageparms = (trim($pageList) == '') ? '' : $pageparms;

		if($sql->update("menus", "menu_class='".intval($_POST['menu_class'])."', menu_pages='{$pageparms}' WHERE menu_id=".intval($_POST['menu_id'])))
		{
			e107::getLog()->add('MENU_02',$_POST['menu_class'].'[!br!]'.$pageparms.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
						
			return array('msg'=>LAN_UPDATED, 'error'=> false);
			//$this->menuAddMessage($message,E_MESSAGE_SUCCESS);
		}
		else
		{
	     	return array('msg'=>LAN_UPDATED_FAILED, 'error'=> true, 'posted'=>$_POST);
          //  $this->menuAddMessage($message,E_MESSAGE_ERROR);
		}

	}

	function setMenuId($id)
	{
		$this->menuId = intval($id);	
	}

	// -----------------------------------------------------------------------

	function menuDeactivate()
	{

		$sql = e107::getDb();
		$sql2 = e107::getDb();
		
		//echo "FOUND= ".$this->menuId;
		$error = false;
		$message = '';

		if($sql->gen('SELECT menu_name, menu_location, menu_order FROM #menus WHERE menu_id = '.$this->menuId.' LIMIT 1'))
		{
			$row = $sql->fetch();

			//Check to see if there is already a menu with location = 0 (to maintain BC)
			if($sql2->select('menus', 'menu_id', "menu_name='{$row['menu_name']}' AND menu_location = 0 AND menu_layout ='".$this->dbLayout."' LIMIT 1"))
			{
				//menu_location=0 already exists, we can just delete this record
				if(!$sql2->db_Delete('menus', 'menu_id='.$this->menuId))
				{
					$message = "Deletion Failed";
					$error = true;
				}
			}
			else
			{
				//menu_location=0 does NOT exist, let's just convert this to it
				if(!$sql2->update("menus", "menu_location=0, menu_order=0, menu_class=0, menu_pages='' WHERE menu_id=".$this->menuId))
				{
	            	$message = "FAILED";
					$error = true;
				}
			}
			//Move all menus up (reduces order number) that have a higher menu order number than one deactivated, in the selected location. 
			$sql->update("menus", "menu_order=menu_order-1 WHERE menu_location={$row['menu_location']} AND menu_order > {$row['menu_order']} AND menu_layout = '".$this->dbLayout."' ");
			e107::getLog()->add('MENU_04',$row['menu_name'].'[!br!]'.$row['menu_location'].'[!br!]'.$row['menu_order'].'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
		}
		else
		{
			$message = "NO CHANGES MADE : ".$this->menuId;	
			$error = true;
			
		}

		return array('msg'=>$message,'error'=>$error);
	}


	// ----------------------------------------------------------------------

	/**
	 * Move a Menu
	 */
	function menuMove()
	{// Get current menu name

			$sql = e107::getDb();

			if($sql->select('menus', 'menu_name', 'menu_id='.$this->menuId, 'default'))
			{
				$row = $sql->fetch();
				//Check to see if menu is already active in the new area, if not then move it
				if(!$sql->select('menus', 'menu_id', "menu_name='{$row['menu_name']}' AND menu_location = ".$this->menuNewLoc." AND menu_layout='".$this->dbLayout ."' LIMIT 1"))
				{
					$menu_count = $sql->count("menus", "(*)", " WHERE menu_location=".$this->menuNewLoc);
					$sql->update("menus", "menu_location='{$this->menuNewLoc}', menu_order=".($menu_count+1)." WHERE menu_id=".$this->menuId);

					if(isset($location) && isset($position))
					{
						$sql->update("menus", "menu_order=menu_order-1 WHERE menu_location='{$location}' AND menu_order > {$position} AND menu_layout='".$this->dbLayout ."' ");
					}
				}
				e107::getLog()->add('MENU_03',$row['menu_name'].'[!br!]'.$this->menuNewLoc.'[!br!]'.$this->menuId,E_LOG_INFORMATIVE,'');
			}
	}


	// =-----------------------------------------------------------------------------

	
	function renderOptionRow($row)
	{
		$frm 	= e107::getForm();
		
		
		$text = "";
		
		$pdeta = "";
	        $color = (varset($color) == "white") ? "#DDDDDD" : "white";
			if($row['menu_pages'] == "dbcustom")
			{
				$pdeta = LAN_CUSTOM;
			}
			else
			{
				$menuPreset = varset($menuPreset);
				$row['menu_name'] = preg_replace("#_menu$#i", "", $row['menu_name']);
	            if($pnum = $this->checkMenuPreset($menuPreset,$row['menu_name'].'_menu'))
				{
		        	$pdeta = MENLAN_39."  {$pnum}";
				}
			}

	        if(!$this->dragDrop)
			{
				$menuInf = (!is_numeric($row['menu_path'])) ? ' ('.substr($row['menu_path'],0,-1).')' : " ( #".$row['menu_path']." )";
	    	//	$menuInf = $row['menu_path'];
	    		
	    		$text .= "<tr style='background-color:$color;color:black'>
				<td style='text-align:left; color:black;' >";

				$text .= $frm->checkbox('menuselect[]',$row['menu_id'],'',array('label'=>$row['menu_name'].$menuInf));
		
				$text .= "
				</td>
				<td style='color:black'>&nbsp; ".$pdeta."&nbsp;</td>
				</tr>\n";
			}
			else
			{
				$menu_count = varset($menu_count);
				// Menu Choices box. 
	            $text .= "<div class='portlet block block-archive' id='block-".$row['menu_id']."' style='border:1px outset black;text-align:left;color:black'>";
			 	$text .= $this->menuRenderMenu($row, $menu_count,true);
	  			$text .= "</div>\n";
			}	
		
		return $text;
	}
	
	
	
	
	

	function menuRenderPage()
	{
		global $HEADER, $FOOTER, $rs;
		$pref   = e107::getPref();  
		$sql    = e107::getDb();     
		$tp     = e107::getParser();


	//	echo "<div id='portal'>";
		$this->parseheader($HEADER);  // $layouts_str;
		
		$layout = ($this->curLayout);
		$menuPreset = $this->getMenuPreset();


		echo "<div style='text-align:center'>";
		echo $rs->form_open("post", e_SELF."?configure=".$this->curLayout, "menuActivation");
		$text = "<table  style='width:100%;margin-left:auto;margin-right:auto'>";


		$text .= "<tr><td style='color:#2F2F2F;width:65%;text-align:center;padding-bottom:8px'>".MENLAN_36."...</td>
		<td style='color:#2F2F2F;width:50%;padding-bottom:8px;text-align:center'>...".MENLAN_37."</td></tr>";
		$text .= "<tr><td  style='width:35%;vertical-align:top;text-align:center'>";

	 
		



		if(!$this->dragDrop)
		{
			$text .= "<div class='column' id='portal-column-block-list' style='border:1px inset black;height:250px;display:block;overflow:auto;margin-bottom:20px'>";
			$text .= "<table class='table table-striped adminlist core-menumanager-main' id='core-menumanager-main'  >
			<tbody>\n";

		}
		else
		{
       // 	$text .= "<div class='column' id='remove' style='border:1px solid silver'>\n";
		}


		$pageMenu = array();
		$pluginMenu = array();

		$done = array();
		
		$sql->select("menus", "menu_name, menu_id, menu_pages, menu_path", "1 ORDER BY menu_name ASC");
		while ($row = $sql->fetch())
		{

			if(in_array($row['menu_name'],$done))
			{
				continue;
			}

			$done[] = $row['menu_name'];

			if(is_numeric($row['menu_path']))
			{
				$pageMenu[] = $row;	
			}
			else 
			{
				$pluginMenu[] = $row;	
			}
						
		}

		$text .= "<tr><th colspan='2'>".MENLAN_49."</th></tr>";

		foreach($pageMenu as $row)
		{	
			$text .= $this->renderOptionRow($row);	
		}
		
		$text .= "<tr><th colspan='2' >".MENLAN_50."</th></tr>";
		foreach($pluginMenu as $row)
		{	
			$text .= $this->renderOptionRow($row);	
		}

		$text .= (!$this->dragDrop) ? "</tbody></table>" : "";
		$text .= "</div>";

		$text .= "</td><td id='menu-manage-actions' ><br />";
		foreach ($this->menu_areas as $menu_act)
		{
			$text .= "<input type='submit' class='menu-btn' id='menuActivate_".trim($menu_act)."' name='menuActivate[".trim($menu_act)."]' value='".MENLAN_13." ".trim($menu_act)."' /><br /><br />\n";
		}


	    if($layout)
		{
			if(isset($pref['sitetheme_layouts'][$layout]['menuPresets']))
			{
		    	$text .= "<input type='submit' class='menu-btn' name='menuUsePreset' value=\"".MENLAN_40."\" onclick=\"return jsconfirm('".$tp->toJS(MENLAN_41)."')\" /><br /><br />\n";  // Use Menu Presets
				$text .= "<input type='hidden' name='menuPreset' value='".$layout."' />";
			}
			$text .= "<input type='hidden'  name='curLayout' value='".$layout."' />";
	    }
		$text .= "<input type='hidden'  id='dbLayout' value='".$this->dbLayout."' />";
		$text .= "</td>";

		$text .= "</tr></table>";

		if(!count($this->menu_areas))
		{
			$text = "<div class='alert alert-block alert-warning text-left'>";
			$text .= MENLAN_51."<br />";
			
			if(isset($this->customMenu) && count($this->customMenu))
			{
				$text .= "<p>".MENLAN_52."<ul ><li>".implode("</li><li>",$this->customMenu)."</li></ul></p>";	
				$text .= "<p><a href='".e_ADMIN."cpage.php?mode=menu&action=list&tab=2' class='button btn btn-primary'>".MENLAN_53."</a></p>";
			}
			
			$text .= "</div>";
		}
	//	$ns -> tablerender(MENLAN_22.'blabla', $text);
		if(!deftrue("e_DEBUG_MENUMANAGER"))
		{
			echo "<div class='menu-panel' style='padding:50px'>Main Content Area</div>";
		}
		else
		{
			echo $this->renderPanel(MENLAN_22, $text);
		}

	//
        echo $rs->form_close();
		echo "</div>";

		$FOOTER = str_replace('</body>','', $FOOTER);

		$this->parseheader($FOOTER);
		if($this->debug)
		{
	    	echo "<div id='debug' style='margin-left:0px;border:1px solid silver; overflow:scroll;height:250px'> &nbsp;</div>";
        }
	//	echo "</div>";
	}





	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function menuSelectLayout()
	{
		$pref = e107::getPref();
		
// onchange=\"urljump(this.options[selectedIndex].value);\"

		$text = "<form class='form-inline' method='post' action='".e_SELF."?configure=".$this->curLayout."'>";
		$text .= "<div class='buttons-bar'>".MENLAN_54.": ";
        $text .= "<select name='custom_select' style='width:auto' id='menuManagerSelect'  >\n"; //tbox class will break links.  // window.frames['menu_iframe'].location=this.options[selectedIndex].value ???


	    $search = array("_","legacyDefault","legacyCustom");
		$replace = array(" ",MENLAN_31,MENLAN_33);


	    foreach($pref['sitetheme_layouts'] as $key=>$val)
		{
			$layoutName = str_replace($search,$replace,$key);
			$layoutName .=($key==$pref['sitetheme_deflayout']) ? " (".MENLAN_31.")" : "";
			$selected = ($this->curLayout == $key || ($key==$pref['sitetheme_deflayout'] && $this->curLayout=='')) ? "selected='selected'" : FALSE;
		
           // $url = e_SELF."?lay=".$key;

			$url = e_SELF."?configure=".$key;
			
			$text .= "<option value='".$url."' {$selected}>".$layoutName."</option>";
		}

	    $text .= "</select>
	    <div class='field-help'>".MENLAN_30."</div>
		</div></form>";
		
		// $text .= "<div id='visibility'>Something here</div>";
		
		  return $text;
	}

		//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function parseheader($LAYOUT, $check = FALSE)
	{

		//  $tmp = explode("\n", $LAYOUT);

		if(strpos($LAYOUT,'<body ') !== false) // FIXME Find a way to remove the <body> tag from the admin header when menu-manager is active.
		{
		//	$LAYOUT = preg_replace('/<body[^>]*>/','', $LAYOUT);
		}

		// Split up using the same function as the shortcode handler
		$tmp = preg_split('#(\{\S[^\x02]*?\S\})#', $LAYOUT, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$str = array();
		for($c = 0; $c < count($tmp); $c++)
		{



			if(preg_match("/[\{|\}]/", $tmp[$c]))
			{
				if($check)
				{
					if(strpos($tmp[$c], "{MENU=")!==false || strpos($tmp[$c], "{MENUAREA=")!==false)
					{
						$matches = array();
						// Match all menu areas, menu number is limited to tinyint(3)
						preg_match_all("/\{(?:MENU|MENUAREA)=([\d]{1,3})(:[\w\d]*)?\}/", $tmp[$c], $matches);
						$this->menuSetCode($matches, $str);
					}
				}
				else
				{
					$this->checklayout($tmp[$c]);
				}
			}
			else
			{
				if(!$check)
				{
					echo $tmp[$c];
				}
			}
		}
		if($check)
		{
			return $str;
		}
	}
	
	function menuSetCode($matches, &$ret)
	{
		if(!$matches || !vartrue($matches[1]))
		{
			return;
		}
	
		foreach ($matches[1] as $match) 
		{
			$ret[] = $match;
		}
	}
	
	function renderPanel($caption,$text)
	{
		$plugtext = "<div class='menu-panel'>";
		$plugtext .= "<div class='menu-panel-header' title=\"".MENLAN_34."\">".$caption."</div>";
		$plugtext .= $text;
		$plugtext .= "</div>";	
		return $plugtext;
	}

	function checklayout($str)
	{ // Displays a basic representation of the theme
		global $PLUGINS_DIRECTORY, $rs, $sc_style, $menu_order, $style; // global $style required. 
		$PLUGINS_DIRECTORY = e107::getFolder('PLUGINS');
		$pref   = e107::getPref();  
		$tp     = e107::getParser(); 
		$ns     = e107::getRender();  


		$menuLayout = ($this->curLayout != $pref['sitetheme_deflayout']) ? $this->curLayout : "";
		
	//	if(strstr($str, "LOGO"))
	//	{
	//		echo $tp->parseTemplate("{LOGO}");
	//	}
		if(strstr($str, "SETSTYLE"))
		{
			$style = preg_replace("/\{SETSTYLE=(.*?)\}/si", "\\1", $str);

			$this->style = $style;
			$ns->setStyle($style);

		}
		/*elseif(strstr($str, "SITENAME"))
		{
			echo "[SiteName]";
		}*/
		/*elseif(strstr($str, "SITETAG"))
		{
			echo "<div style='padding: 2px'>[SiteTag]</div>";
		}*/
	//	elseif(strstr($str, "SITELINKS"))
	//	{
	//		echo "[SiteLinks]";
	//	}
	//	elseif(strstr($str, "NAVIGATION"))
	//	{
	//		$cust = preg_replace("/\W*\{NAVIGATION(.*?)(\+.*)?\}\W*/si", "\\1", $str);
	//		$tp->parseTemplate("{NAVIGATION".$cust."}",true);
		//	echo "<span class='label label-info'>Navigation Area</span>";
	//	}
		elseif(strstr($str, '{---MODAL---}'))
		{
			//echo "\n<!-- Modal would appear here --> \n";
			echo '<div id="uiAlert" class="notifications center"><!-- empty --></div>';

			//TODO Store in a central area - currently used in header.php, header_default.php and here.
			echo '
       
	         <div id="uiModal" class="modal  fade" tabindex="-1" role="dialog"  aria-hidden="true">
	            <div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
	                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	                        <h4 class="modal-caption">&nbsp;</h4>
	                     </div>
	
	                    <div class="modal-body">
	                        <p>Loading…</p>
	                    </div>
	
	                    <div class="modal-footer">
	                        <a href="#" data-dismiss="modal" class="btn btn-primary">Close</a>
	                    </div>
	               </div>
			    </div>
	        </div>';

			//echo getModal();
		}
		elseif(strstr($str, '{---CAPTION---}'))
		{
			echo LAN_CAPTION;
		}
		elseif(strstr($str, '{LAYOUT_ID}'))
		{
			echo 'layout-'.e107::getForm()->name2id($this->curLayout);
		}
		elseif(strstr($str, "ALERT"))
		{
			//echo "[Navigation Area]";
		}
		elseif(strstr($str, "LANGUAGELINKS"))
		{
			echo "<div class=text style='padding: 2px; text-align: center'>[".LAN_LANGUAGE."]</div>";
		}
		elseif(strstr($str, "CUSTOM"))
		{
			$cust = preg_replace("/\W*\{CUSTOM=(.*?)(\+.*)?\}\W*/si", "\\1", $str);
			echo "<div style='padding: 2px'>[" . $cust . "]</div>";
		}
		elseif(strstr($str, "CMENU"))
		{
			$cust = preg_replace("/\W*\{CMENU=(.*?)(\+.*)?\}\W*/si", "\\1", $str);
			if(isset($this->customMenu))
			{
				$this->customMenu[] = $cust;
			}
			echo $tp->parseTemplate("{CMENU=".$cust."}",true);
		//	echo $this->renderPanel('Embedded Custom Menu',$cust);
		}
		elseif(strstr($str, "SETIMAGE"))
		{
			$cust = preg_replace("/\W*\{SETIMAGE(.*?)(\+.*)?\}\W*/si", "\\1", $str);
			echo $tp->parseTemplate("{SETIMAGE".$cust."}",true);
		//	echo $this->renderPanel('Embedded Custom Menu',$cust);
		}
		/*elseif(strstr($str, "{WMESSAGE"))
		{
			echo "<div class=text style='padding: 30px; text-align: center'>[Welcome Message Area]</div>";
		//	echo $this->renderPanel('Embedded Custom Menu',$cust);
		}*/
		elseif(strstr($str, "{FEATUREBOX"))
		{
			echo "<div class=text style='padding: 80px; text-align: center'>[".LAN_PLUGIN_FEATUREBOX_NAME."]</div>";
		//	echo $this->renderPanel('Embedded Custom Menu',$cust);
		}
		// Display embedded Plugin information.
		else if(strstr($str, "PLUGIN"))
		{
			$plug = preg_replace("/\{PLUGIN=(.*?)\}/si", "\\1", $str);
			$plug = trim($plug);
			if(file_exists((e_PLUGIN . "{$plug}/{$plug}_config.php")))
			{
				$link = e_PLUGIN . "{$plug}/{$plug}_config.php";
			}
			
			if(file_exists((e_PLUGIN . $plug . "/config.php")))
			{
				$link = e_PLUGIN . $plug . "/config.php";
			}
			
		//	$plugtext = "<div class='menu-panel'>";
		//	$plugtext .= "<div class='menu-panel-header' title=\"".MENLAN_34."\">".$plug."</div>";
			$plugtext = (varset($link)) ? "(" . MENLAN_34 . ":<a href='$link btn-menu' title='" . LAN_CONFIGURE . "'>" . LAN_CONFIGURE . "</a>)" : "";
		//	$plugtext .= "</div>";
			echo "<br />";
			echo $this->renderPanel($plug, $plugtext);
			// $ns->tablerender($plug, $plugtext);
		}
		else if(strstr($str, "MENU"))
		{

			$matches = array();
			if(preg_match_all("/\{(?:MENU|MENUAREA)=([\d]{1,3})(:[\w\d]*)?\}/", $str, $matches)) //
			{

				$menuText = "";
				foreach($matches[1] as $menu)
				{
					$menu = preg_replace("/\{(?:MENU|MENUAREA)=(.*?)(:.*?)?\}/si", "\\1", $str);
					if(isset($sc_style['MENU']['pre']) && strpos($str, 'ret') !== false)
					{
						$menuText .= $sc_style['MENU']['pre'];
					}


					// ---------------
					$menuText .= "\n\n<!-- START AREA ".$menu." -->";
					$menuText .= "<div id='start-area-".$menu."' class='menu-panel'>";

					$menuText .= "<div class='menu-panel-header' >" . MENLAN_14 . "  " . $menu . "</div>\n\n";

				//	$sql9 = new db();
				//	$sql9 = e107::getDb('sql9');
				//	if($sql9->count("menus", "(*)", " WHERE menu_location='$menu' AND menu_layout = '" . $this->dbLayout . "' "))
					if(!empty($this->menuData[$menu]))
					{
						unset($text);
						$menuText .= $rs->form_open("post", e_SELF . "?configure=" . $this->curLayout, "frm_menu_" . intval($menu));
						
					//	$rows = $sql9->retrieve("menus", "*", "menu_location='$menu' AND menu_layout='" . $this->dbLayout . "' ORDER BY menu_order",true);
						$rows = $this->menuData[$menu];
					//	$menu_count = $sql9->db_Rows();
						$menu_count = count($rows);

						if(!empty($_GET['debug']))
						{
							print_a($rows);
					//		print_a($this->menuData[$menu]);
						}

						$cl = ($this->dragDrop) ? "'portlet" : "regularMenu";
						
						$menuText .= "\n<div class='column' id='area-".$menu."'>\n\n";
					//	while($row = $sql9->fetch())
						foreach($rows as $row)
						{
							$menuText .= "\n\n\n <!-- Menu Start ".$row['menu_name']. "-->\n";
							$menuText .= "<div class='{$cl}' id='block-".$row['menu_id']."-".$menu."'>\n";
		
						//	echo "<div class='ggportal'>";
							
						//	$menuText .= "hi there";
							$menuText .= $this->menuRenderMenu($row, $menu_count);
							
						//	echo "\n</div>";
							$menuText .= "\n</div>\n";
							$menuText .= "<!-- Menu end -->\n\n\n";
							// echo "<div><br /></div>";
						
						}
						$menuText .= "\n\n</div>\n\n"; // End Column 
						$menuText .= $rs->form_close();
					}
					else
					{	// placeholder
						$menuText .=  "<div class='column' id='area-" . $menu . "'><!-- --></div>";
					}
					
					$menuText .= "</div><!-- END OF AREA -->\n\n";
					
					// ---------------
					
					
					if(isset($sc_style['MENU']['post']) && strpos($str, 'ret') !== false)
					{
						$menuText .= $sc_style['MENU']['post'];
					}
					
					
				}

				echo $menuText;

			//	$ns->tablerender('', varset($menuText)); // Could fail with a badly built theme.
			}
			else
			{

				echo $tp->parseTemplate($str,true);
			}


		}
		else
		{
			echo $tp->parseTemplate($str,true);
		}
	}
	//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

	function menuRenderMenu($row,$menu_count,$rep = FALSE)
	{
	
		global $rs,$menu,$menu_info,$menu_act, $style;

		$style = $this->style;
		//      $menu_count is empty in here
		//FIXME extract
		$menu_location = '';
		$menu_order = '';

		extract($row);
		if(empty($menu_id)){ return; }

		$menu_name = varset($menu_name);
		$menu_name = preg_replace("#_menu#i", "", $menu_name);
		//TODO we need a CSS class for this
		$vis = (varset($menu_class) || strlen(varset($menu_pages)) > 1) ? " <span class='required'><i class='e-mm-icon-search'></i></span> " : "";
		//DEBUG div not allowed in final tags 	$caption = "<div style='text-align:center'>{$menu_name}{$vis}</div>";
		// use theme render style instead
		
		// Undocumented special parameter 'admin_title'
		$menuParms = array();
		if(!empty($row['menu_parms'])) parse_str($row['menu_parms'], $menuParms);
		if(isset($menuParms['admin_title']) && $menuParms['admin_title'])
		{
			$caption = deftrue($menuParms['admin_title'], $menuParms['admin_title']).$vis;
		}
		elseif(isset($menuParms['title']) && $menuParms['title'])
		{
			$caption = deftrue($menuParms['title'], $menuParms['title']).$vis;
		}
		else $caption = $menu_name.$vis;
		
		$menu_info = "{$menu_location}.{$menu_order}";

		$text = "";
		$conf = '';
		if (file_exists(e_PLUGIN.varset($menu_path).$menu_name.'_menu_config.php'))
		{
			$conf = $menu_path.$menu_name.'_menu_config';
		}

		if($conf == '' && file_exists(e_PLUGIN."{$menu_path}config.php"))
		{
		  $conf = "{$menu_path}config";
		}
//
	//	$text = "<div style='white-space:nowrap'>";
		$text .= '<div class="menuOptions">';
		if(!$this->dragDrop)
		{
			$text .= "<select id='menuAct_".$menu_id."' name='menuAct[$menu_id]' class='menu-btn' onchange='this.form.submit()' >";
			$text .= $rs->form_option(MENLAN_25, TRUE, " ");
		//	$text .= $rs->form_option(MENLAN_15, "", "deac.{$menu_info}");
	
			if ($conf) 
			{
			//	$text .= $rs->form_option("Configure", "", $conf); // TODO Check LAN availability
			}
	
			if ($menu_order != 1) 
			{
				$text .= $rs->form_option(MENLAN_17, "", "inc.{$menu_info}");
				$text .= $rs->form_option(MENLAN_24, "", "top.{$menu_info}");
			}
			if ($menu_count != $menu_order) 
			{
				$text .= $rs->form_option(MENLAN_18, "", "dec.{$menu_info}");
				$text .= $rs->form_option(MENLAN_23, "", "bot.{$menu_info}");
			}
			foreach ($this->menu_areas as $menu_act) 
			{
				if ($menu != $menu_act) 
				{
					$text .= $rs->form_option(MENLAN_19." ".$menu_act, "", "move.{$menu_info}.".$menu_act);
				}
			}
			
			// Visibility is an action icon now
			//$text .= $rs->form_option(MENLAN_20, "", "adv.{$menu_info}");
			$text .= $rs->form_select_close();
		}

		if($rep == true)
		{	
			$text .= "<div id='check-".$menu_id."'><input type='checkbox' name='menuselect[]' value='{$menu_id}' />".$menu_id."  " . varset($pdeta) . "</div>
	            <div id='option-".$menu_id."' style='display:none'>";
		}
				
		//DEBUG remove inline style, switch to simple quoted string for title text value
		//TODO hardcoded text
		
	//	$visibilityLink = e_SELF.'?'.urlencode('lay='.$this->curLayout.'&amp;vis='.$menu_id.'&amp;iframe=1');
		
		$visibilityLink = e_SELF."?enc=".base64_encode('lay='.$this->curLayout.'&vis='.$menu_id.'&iframe=1');
		
		$text .= '<span class="menu-options-buttons">
		<a class="e-menumanager-option menu-btn" data-modal-caption="'.LAN_VISIBILITY.'" href="'.$visibilityLink.'" title="'.LAN_VISIBILITY.'"><i class="S16 e-search-16"></i></a>';

		if($conf)
		{
			$text .= '<a data-modal-caption="'.LAN_OPTIONS.'" class="e-modal-menumanager menu-btn" target="_top" href="'.e_SELF.'?lay='.$this->curLayout.'&amp;mode=conf&amp;path='.urlencode($conf).'&amp;id='.$menu_id.'&iframe=1"
			title="'.LAN_OPTIONS.'"><i class="S16 e-configure-16"></i></a>';
		}
		
		$editLink = e_SELF."?enc=".base64_encode('lay='.$this->curLayout.'&parmsId='.$menu_id.'&iframe=1');
		$text .= '<a data-modal-caption="'.LAN_CONFIGURE.'" class="e-menumanager-option menu-btn" target="_top" href="'.$editLink.'" title="'.LAN_CONFIGURE.'"><i class="S16 e-edit-16" ></i></a>';

		$text .= '<a title="'.LAN_DELETE.'" id="remove-'.$menu_id.'-'.$menu_location.'" class="delete e-menumanager-delete menu-btn" href="'.e_SELF.'?configure='.$this->curLayout.'&amp;mode=deac&amp;id='.$menu_id.'"><i class="S16 e-delete-16"></i></a>
		
		<span id="status-'.$menu_id.'" style="display:none">'.($rep == true ? "" : "insert").'</span>
		</span></div>';

		$text .= ($rep == true) ? "</div>" : "";

	//	$text .= "</div>";
		
		if(!$this->dragDrop)
		{
				
			return "<span class='muted'>".$caption."</span><br />". $text;
		//	return;
	

		//	return $ns->tablerender($caption, $text,'', true); Theme style too unpredictable. 
			
			
		}
		else
		{
			
			return "
			<div class='portlet-header'>".$caption."</div>
			<div class='portlet-content' >".$text."</div>";		
		}
		
		

	}

	function menuSaveAjax($mode = null)
	{
		
		
		if($mode == 'visibility')
		{
		
			$ret = $this->menuSaveVisibility();	
		//	echo json_encode($ret);
			return null;
		}		
		
		if($mode == 'delete')
		{
			list($tmp,$area) = explode("-",$_POST['area']);
		
			if($_POST['area'] == 'remove')
			{
				list($tmp,$deleteID) = explode("-",$_POST['removeid']);	
				$this->menuId = $deleteID;

				$ret = $this->menuDeactivate();	
			//	echo json_encode($ret);
				
				return null;
			}	
			
		}
		
		
		if($mode == 'parms') 
		{
			$ret = $this->menuSaveParameters();
			if(!empty($ret['error']))
			{
				return json_encode($ret);
			}
			return null;
		}
		
		
		
    // 	print_r($_POST);
		return;
	 
	 
		$this->debug = TRUE;
		
		$sql = e107::getDb();


		

		// Allow deletion by ajax, but not the rest when drag/drop disabled.  

		if(!$this->dragDrop){ return; }

		$this -> dbLayout = $_POST['layout'];
		list($tmp,$insertID) = explode("-",$_POST['insert']);	
		$insert[] = $insertID;

		

		if($_POST['mode'] == 'insert'  && count($insert) && $area) // clear out everything before rewriting everything to db. 
		{
		 	$this->menuActivateLoc = $area;  // location
			$this->menuActivateIds = $insert;  // array of ids, in order.
			$this->menuActivate(); 
			
		}
		elseif($_POST['mode'] == 'update')
		{
			$sql->update("menus","menu_location = ".intval($area)." WHERE menu_id = ".intval($insertID)."",$this->debug);
		}
		
		$c = 0;
		
		if(count($_POST['list'])<2)
		{
			return;
		}
		
		// resort the menus in this 'Area"
		foreach($_POST['list'] as $val)
		{
			list($b,$id) = explode("-",$val);
			$order[] = $id;
			$sql->update("menus","menu_order = ".$c." WHERE menu_id = ".intval($id)."",$this->debug);
       		$c++;
		}

		// same for delete etc.

	//	echo "<hr />";


	}

    function menuSetConfigList()
	{
		e107::getDebug()->log("Scanning for Menu config files");

        	$sql = e107::getDb();
        	$pref = e107::getPref();
			$prev_name = '';
			$search = array('_menu','_');

			$sql -> select("menus", "*", "menu_location != 0 ORDER BY menu_path,menu_name");
			while($row = $sql-> fetch())
			{
				$link = "";

				$id = substr($row['menu_path'],0,-1);

				if (file_exists(e_PLUGIN."{$row['menu_path']}{$row['menu_name']}_menu_config.php"))
				{
				    $link = $row['menu_path'].$row['menu_name']."_menu_config.php";
				}

				if($row['menu_path'] == 'news/')
				{
					$row['menu_path'] = "blogcalendar_menu/";
				}

				if(file_exists(e_PLUGIN.$row['menu_path']."config.php"))
				{
					 $link = $row['menu_path']."config.php";
				}



				if($link)
				{


         			$tmp[$id]['name'] = ucwords(str_replace($search,"",$row['menu_name'])); // remove _

					if(vartrue($prev) == $id && ($tmp[$id]['name'] != $prev_name))
					{
	                	$tmp[$id]['name'] .= ":".$prev_name;
					}

					$tmp[$id]['link'] = $link;
					$prev = $id;

					$prev_name = $tmp[$id]['name'];
				}
			}

           $pref['menuconfig_list'] = vartrue($tmp);
		   
		   e107::getConfig()->setPref($pref)->save(false,true,false);

	}
}  // end of Class.







// new v2.1.4
class e_menu_layout
{
	function __construct()
	{

	}

	static function getLayouts($theme=null)
	{
		if(empty($theme))
		{
			$theme = e107::pref('core','sitetheme');
		}

		$sql = e107::getDb(); // required
		$tp = e107::getParser();

		$HEADER         = null;
		$FOOTER         = null;
		$LAYOUT         = null;
		$CUSTOMHEADER   = null;
		$CUSTOMFOOTER   = null;

		$path = e_THEME.$theme.'/';
		$file = $path."theme.php";

		if(!is_readable($file))
		{
			return false;
		}

		e107::set('css_enabled',false);
		e107::set('js_enabled',false);

		// new v2.2.2 HTML layout support.
		if(is_dir($path."layouts") && is_readable($path."theme.html"))
		{
			$lyt = scandir($path."layouts");
			$LAYOUT = array();

			foreach($lyt as $lays)
			{
				if($lays === '.' || $lays === '..')
				{
					continue;
				}

				$key = str_replace("_layout.html", '', $lays);

				if($lm = e_theme::loadLayout($key, $theme))
				{
					$LAYOUT  = $LAYOUT + $lm;
				}

			}

		}
		else // prior to v2.2.2
		{

			$themeFileContent = file_get_contents($file);

			$srch = array('<?php','?>');

			$themeFileContent = preg_replace('/\(\s?THEME\s?\./', '( e_THEME. "'.$theme.'/" .', str_replace($srch, '', $themeFileContent));

			$themeFileContent = str_replace('tablestyle', $tp->filter($theme, 'wd')."_tablestyle",$themeFileContent); // rename function to avoid conflicts while parsing.

			$themeFileContent = str_replace("class ".$theme."_theme", "class ".$theme."__theme", $themeFileContent); // rename class to avoid conflicts while parsing.

			try
			{
			   @eval($themeFileContent);
			}
			catch (ParseError $e)
			{
				echo "<div class='alert alert-danger'>Couldn't parse theme.php: ". $e->getMessage()." </div>";
			}
		}


		e107::set('css_enabled',true);
		e107::set('js_enabled',true);

		$head = array();
		$foot = array();




		if(isset($LAYOUT) && (isset($HEADER) || isset($FOOTER)))
		{
			$fallbackLan = "This theme is using deprecated elements. All [x]HEADER and [x]FOOTER variables should be removed from theme.php."; // DO NOT TRANSLATE!
			$warningLan = $tp->lanVars(deftrue('MENLAN_60',$fallbackLan),'$');
			echo "<div class='alert alert-danger'>".$warningLan."</div>";

		}



		if(isset($LAYOUT) && is_array($LAYOUT)) // $LAYOUT is a combined $HEADER,$FOOTER.
		{
			foreach($LAYOUT as $key=>$template)
			{
				if($key == '_header_' || $key == '_footer_' || $key == '_modal_')
				{
					continue;
				}

				if(strpos($template,'{---}') !==false)
				{
					list($hd,$ft) = explode("{---}",$template);
					$head[$key] = isset($LAYOUT['_header_']) ? $LAYOUT['_header_'] . $hd : $hd;
					$foot[$key] = isset($LAYOUT['_footer_']) ? $ft . $LAYOUT['_footer_'] : $ft ;
				}
				else
				{
					e107::getMessage()->addDebug('Missing "{---}" in $LAYOUT["'.$key.'"] ');
				}
			}
			unset($hd,$ft);
		}


        if(is_string($CUSTOMHEADER))
        {
			$head['legacyCustom'] = $CUSTOMHEADER;
        }
        elseif(is_array($CUSTOMHEADER))
        {
            foreach($CUSTOMHEADER as $k=>$v)
            {
                $head[$k] = $v;
            }
        }

        if(is_string($HEADER))
        {
			$head['legacyDefault'] = $HEADER;
        }
        elseif(is_array($HEADER))
        {
			 foreach($HEADER as $k=>$v)
            {
                $head[$k] = $v;
            }

        }

		if(is_string($CUSTOMFOOTER))
        {
			$foot['legacyCustom'] = $CUSTOMFOOTER;
        }
        elseif(is_array($CUSTOMFOOTER))
        {
	        foreach($CUSTOMFOOTER as $k=>$v)
            {
                $foot[$k] = $v;
            }
        }


        if(is_string($FOOTER))
        {
			$foot['legacyDefault'] = $FOOTER;
        }
        elseif(is_array($FOOTER))
        {
	        foreach($FOOTER as $k=>$v)
            {
                $foot[$k] = $v;
            }
        }

		$layout = array();



		foreach($head as $k=>$v)
		{
			$template = $head[$k]."\n{---}".$foot[$k];
			$layout['templates'][$k] = $template;
			$layout['menus'][$k] = self::countMenus($template, $k);
		}


		return $layout;


	}


	private static function countMenus($template, $name)
	{
		if(preg_match_all("/\{(?:MENU|MENUAREA)=([\d]{1,3})(:[\w\d]*)?\}/", $template, $matches))
		{
			sort($matches[1]);
			return $matches[1];
		}

		e107::getDebug()->log("No Menus Found in Template:".$name." with strlen: ".strlen($template));

		return array();
	}



	static function menuSelector()
	{

	//	$p = e107::getPref('e_menu_list');	// new storage for xxxxx_menu.php list.
		$sql = e107::getDb();
		$frm = e107::getForm();

		$done = array();

		$pageMenu = array();
		$pluginMenu = array();

		$sql->select("menus", "menu_name, menu_id, menu_pages, menu_path", "1 ORDER BY menu_name ASC");
		while ($row = $sql->fetch())
		{

			if(in_array($row['menu_name'],$done))
			{
				continue;
			}

			$done[] = $row['menu_name'];

			if(is_numeric($row['menu_path']))
			{
				$pageMenu[] = $row;
			}
			else
			{
				$pluginMenu[] = $row;
			}

		}

		$tab1 = '<div class="menu-selector"><ul class="list-unstyled">';

		foreach($pageMenu as $row)
		{
			$menuInf = (!is_numeric($row['menu_path'])) ? ' ('.substr($row['menu_path'],0,-1).')' : " (#".$row['menu_path'].")";
			$tab1 .= "<li>".$frm->checkbox('menuselect[]',$row['menu_id'],'',array('label'=>"<span>".$row['menu_name']."<small>".$menuInf."</small></span>"))."</li>";
		}

		$tab1 .= '</ul></div>';

		$tab2 = '<div class="menu-selector"><ul class=" list-unstyled">';
		foreach($pluginMenu as $row)
		{
			$menuInf = (!is_numeric($row['menu_path'])) ? ' ('.substr($row['menu_path'],0,-1).')' : " (#".$row['menu_path'].")";
			$tab2 .= "<li>".$frm->checkbox('menuselect[]',$row['menu_id'],'',array('label'=>"<span>".$row['menu_name']."<small>".$menuInf."</small></span>"))."</li>";
		}

		$tab2 .= '</ul></div>';

		$tabs = array(
			'custom' => array('caption'=>'<i title="'.MENLAN_49.'" class="S16 e-custom-16"></i>', 'text'=>$tab1),
			'plugin' => array('caption'=>'<i title="'.ADLAN_CL_7.'" class="S16 e-plugins-16"></i>', 'text'=>$tab2)

		);


		$defLayout =e107::getRegistry('core/e107/menu-manager/curLayout');;

		$text = '<form id="e-mm-selector" action="'.e_ADMIN_ABS.'menus.php?configure='.$defLayout.'" method="post" target="e-mm-iframe">';

		$text .= "<input type='hidden' id='curLayout' value='".$defLayout."' />";


		$layouts = self::getLayouts();
		$tp = e107::getParser();

	//	 var_dump($layouts['menus']);


		$text .= '

		    <div class="dropdown pull-right e-mm-selector-container">

		        <a class="btn btn-default btn-secondary btn-sm e-mm-selector " title="'.LAN_ACTIVATE.'">'.LAN_GO." ".e107::getParser()->toGlyph('fa-chevron-right').'</a>';

				$menuButtonLabel = defset("MENLAN_59", "Area [x]");

		        foreach($layouts['menus'] as $name=>$areas)
		        {
					$text .= '<ul class="dropdown-menu e-mm-selector '.$name.'" >
					<li><div>';

					foreach ($areas as $menu_act)
					{
						$text .= "<input type='submit' class='btn btn-sm btn-primary col-xs-6'  name='menuActivate[".trim($menu_act)."]' value=\"".$tp->lanVars($menuButtonLabel,trim($menu_act))."\" />\n";
					}

					$text .= '</div></li></ul>';

		        }

		        $text .= '

		    </div>';


		$text .= $frm->tabs($tabs);





		$text .= '</form>';

		$tp = e107::getParser();

		$caption = MENLAN_22;

		;




		return array('caption'=>$caption,'text'=>$text);






	}



}
