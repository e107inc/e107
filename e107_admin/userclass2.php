<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - User classes
 *
*/

require_once('../class2.php');

if (!getperms('4'))
{
  e107::redirect('admin');
  exit;
}

e107::coreLan('userclass2', true);


	class uclass_admin extends e_admin_dispatcher
	{

		protected $modes = array(

			'main'	=> array(
				'controller' 	=> 'uclass_ui',
				'path' 			=> null,
				'ui' 			=> 'uclass_ui_form',
				'uipath' 		=> null
			),


		);


		protected $adminMenu = array(

			'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => '4'),
			'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => '4'),
			'main/initial' 		=> array('caption'=> UCSLAN_38, 'perm' => '4'),
			'main/options' 		=> array('caption'=> LAN_OPTIONS, 'perm' => '4'),

			// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
		);

		protected $adminMenuAliases = array(
			'main/edit'	=> 'main/list'
		);

		protected $menuTitle = ADLAN_38;

		protected $adminMenuIcon = 'e-userclass-24';
	}




	class uclass_ui extends e_admin_ui
	{

		protected $pluginTitle		= ADLAN_38;
		protected $pluginName		= 'core';
//		protected $eventName		= 'userclass';
		protected $table			= 'userclass_classes';
		protected $pid				= 'userclass_id';
		protected $perPage			= 10;
		protected $batchDelete		= true;
		protected $batchCopy		= false; // no way to generate the non-incrementing primary key at the moment
	//	protected $listOrder		= 'userclass_id DESC'; //XXX Make more intuitive.
		protected $listOrder        = "CASE WHEN userclass_id = 250 THEN 1 WHEN userclass_id =254 THEN 2 WHEN userclass_id = 253 THEN 3  WHEN userclass_id < 250 THEN 4 END, userclass_id DESC ";
		//	protected $sortField		= 'somefield_order';
		//	protected $orderStep		= 10;
		protected $tabs			= null;// Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

	//	protected $listQry      	= "SELECT * FROM `#generic` WHERE gen_type='wmessage'  "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

	//	protected $listOrder		= 'gen_id DESC';



		protected $fields = array(
			'checkboxes' 		        =>  array ( 'title' => '', 		'type' => null,         'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
			'userclass_id'				=> array('title'=> LAN_ID,		'type' =>'hidden',  	 'data'=>'int', 'width' => '5%',	'thclass' => 'left'),
			'userclass_icon' 			=> array('title'=> LAN_ICON,	'type' => 'icon', 		'tab'=>0, 'data'=>'str', 'width' => '5%',	'thclass' => 'left', 'class' => 'center'),
			'userclass_name'	   		=> array('title'=> LAN_NAME,	'type' => 'text', 		'tab'=>0,'data'=>'str', 'inline'=>true, 'width' => 'auto',	'thclass' => 'left'),
			'userclass_description'   	=> array('title'=> LAN_DESCRIPTION,	'type' => 'text', 		'tab'=>0,'data'=>'str', 'inline'=>true,'width' => 'auto',	'thclass' => 'left', 'writeParms'=>array('size'=>'xxlarge')),
			'userclass_type' 			=> array('title'=> LAN_TYPE,	'type' => 'dropdown',	'tab'=>0,'data'=>'int', 'width' => '10%',	'thclass' => 'left',	'class'=>'left' ),
			'userclass_editclass' 		=> array('title'=> LAN_MANAGER,	'type' => 'userclass',	'tab'=>0,'data'=>'int', 'width' => 'auto',	'thclass' => 'left', 'writeParms'=>array('classlist'=>'nobody,public,main,admin,classes,matchclass,member, no-excludes')),
			'userclass_visibility' 		=> array('title'=> LAN_VISIBILITY,	'type' => 'userclass',	'tab'=>0,'data'=>'int', 'width' => 'auto',	'thclass' => 'left', 'writeParms'=>array()),
			'userclass_parent' 			=> array('title'=> LAN_PARENT,	'type' => 'userclass',	'tab'=>0,'data'=>'int', 'width' => 'auto',	'thclass' => 'left', 'writeParms'=>array('classlist'=>'main,admin,nobody,public,classes,matchclass,member, no-excludes')),
			'userclass_perms' 			=> array('title'=> "Perms",	'type' => 'hidden',	'tab'=>0,'data'=>'str', 'width' => 'auto',	'thclass' => 'left', 'writeParms'=>array()),

			'options' 					=> array('title'=> LAN_OPTIONS, 'type' => 'method',		'width' => '10%',	'thclass' => 'center last', 'forced'=>TRUE,  'class'=>'right', 'readParms' => array('deleteClass' => e_UC_NOBODY))
		);

		protected $fieldpref = array('userclass_icon', 'userclass_name', 'userclass_description');

	/*
		protected $prefs = array(
			'wm_enclose'		=> array('title'=> WMLAN_05, 'type'=>'boolean', 'data' => 'int','help'=> WMLAN_06),		);*/

		public function init()
		{

			if(E107_DBG_BASIC && intval($_GET['id']) === 254) // Experimental
			{
				e107::getMessage()->addDebug("Experimental Feature active");
				$this->tabs = array(LAN_GENERAL,"Administrator Permissions");
				$this->fields['userclass_perms']['type'] = 'method';
				$this->fields['userclass_perms']['tab'] = 1;
			}


			// Listen for submitted data.
			$this->initialPageSubmit();
			$this->optionsPageSubmit();

			if($this->getAction() == 'list')
			{
				$this->fields['userclass_id']['type'] = 'number';
			}

			// Set Defaults for when creating new records.
			$this->fields['userclass_type']['writeParms']                   = array(UC_TYPE_STD => UCSLAN_80,	UC_TYPE_GROUP => UCSLAN_81);
			$this->fields['userclass_editclass']['writeParms']['default']   = e_UC_ADMIN;
			$this->fields['userclass_parent']['writeParms']['default']      = e_UC_NOBODY;
			$this->fields['userclass_visibility']['writeParms']['default']  = e_UC_ADMIN;
			$this->fields['userclass_id']['writeParms']['default']          =$this->getUserClassAdmin()->findNewClassID();

		}

		/**
		 * @return Object
		 */
		public function getUserClassAdmin()
		{
			return e107::getSingleton('user_class_admin');
		}

		public function beforeCreate($new_data, $old_data)
		{
			return $new_data;
		}

		public function afterCreate($new_data, $old_data, $id)
		{
			e107::getUserClass()->clearCache();
			$e_userclass    = $this->getUserClassAdmin();
			$e_userclass->calc_tree();
			$e_userclass->save_tree();
		}

		public function beforeUpdate($new_data, $old_data, $id)
		{

			if(!empty($new_data['perms']))
			{
				$new_data['userclass_perms'] = implode(".",$new_data['perms']);
			}

			e107::getMessage()->addDebug(print_a($new_data,true));

			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			e107::getUserClass()->clearCache();
		}

		public function afterDelete($data,$id, $check = false)
		{
			e107::getUserClass()->clearCache();
		}


		public function onCreateError($new_data, $old_data)
		{
			// do something
		}

		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something
		}


		function renderHelp()
		{
			$e_userclass = e107::getSingleton('user_class_admin'); 			// Admin functions - should just obliterate any previous object created in class2.php
			$e_userclass->calc_tree();
			$text = "<div id='userclass-tree-structure'>".$e_userclass->show_graphical_tree()."</div>";

			return array('caption'=>UCSLAN_91, 'text' => $text); 

			// $text .= $e_userclass->show_graphical_tree();
		}

		public function optionsPage()
		{
			$mes = e107::getMessage();
			$frm = e107::getForm();


			$mes->addWarning(LAN_OPTIONS."<br /><br />".UCSLAN_53);

			$text = "<h4>".LAN_PREFS."</h4>
			<form method='post' action='".e_SELF."?mode=main&action=options' id='treesetForm'>
			<table class='table adminform'>
			<colgroup>
			<col class='col-label' />
			<col class='col-content' />
			</colgroup>
			<tr><td >".UCSLAN_54."<br /><span class='smalltext'>".UCSLAN_57."</span><br />
			</td><td>
			".$frm->admin_button('add_class_tree','no-value','delete', UCSLAN_58)."
			</td>
			</tr>
			<tr>
			<td>".UCSLAN_55."<br /><span class='smalltext'>".UCSLAN_56."</span><br />
			</td><td>
			".$frm->admin_button('flatten_class_tree','no-value','delete', UCSLAN_58)."
			</td>
			</tr>";

		/*
			if ($params == '.xml')
			{
				$text .= "<tr>
			<td>".'Create XML file of DB'."<br /><span class='smalltext'>".'Dev aid to set initial values'."</span><br />
			</td><td>
			".$frm->admin_button('create_xml_db','no-value','create', 'Create')."
			</td>
		</tr>";

			}
		*/

			$text .= "</table></form>";



		//	$ns->tablerender(ADLAN_38.SEP.LAN_PREFS, $mes->render().$text);


			$text .= "
			<h4>".UCSLAN_71."</h4><form method='post' action='".e_SELF."?options' id='maintainForm'>
			<table class='table adminform'>
			<colgroup>
				<col class='col-label' />
				<col class='col-content' />
			</colgroup>
			<tr>
				<td>".UCSLAN_72."<br />
					<span class='smalltext'>".UCSLAN_73."</span>
				</td>
				<td>
				".$frm->admin_button('rebuild_tree','no-value','delete', UCSLAN_58)."
				</td>
			</tr>
			</table>
			</form>";

		//	$ns->tablerender(UCSLAN_71, $text);
			return $text;

		}


		public function optionsPageSubmit()
		{

			if (!check_class(e_UC_MAINADMIN))
			{
				return false;
			}

			$message        = '';
			$sql            = e107::getDb();
			$mes            = e107::getMessage();
			$e_userclass    = $this->getUserClassAdmin();

			if (isset($_POST['add_class_tree'])) 	// Create a default tree
			{
				$message = UCSLAN_62;
				$e_userclass->set_default_structure();
				$e_userclass->calc_tree();
				$e_userclass->save_tree();
				$e_userclass->readTree(TRUE);		// Need to re-read the tree to show correct info
				$message .= UCSLAN_64;
			}

			if (isset($_POST['flatten_class_tree'])) // Remove the default tree
			{
				$message = UCSLAN_65;
				$sql->update('userclass_classes', "userclass_parent='0'");
				$e_userclass->calc_tree();
				$e_userclass->save_tree();
				$e_userclass->readTree(TRUE);		// Need to re-read the tree to show correct info
				$message .= UCSLAN_64;
			}

			if (isset($_POST['rebuild_tree']))
			{
				$message = UCSLAN_70;
				$e_userclass->calc_tree();
				$e_userclass->save_tree();
				$message .= UCSLAN_64;
			}

			/*
			if ($params == 'xml') $params = '.xml'; else $params = '';

			if (isset($_POST['create_xml_db']) && ($params == '.xml'))
			{
				$message = $e_userclass->makeXMLFile() ? 'XML file created' : 'Error creating XML file';
			}
			*/

			if ($message)
			{
				$mes->addSuccess($message);
			//	$ns->tablerender('', "<div style='text-align:center'><b>".$message."</b></div>");
			}

			return null;
		}



		public function initialPage()
		{

			$pref           = e107::pref('core');
			$mes            = e107::getMessage();

			$frm            = e107::getForm();
	//		$e_userclass    = $this->getUserClassAdmin();

		//	$text           = "";

			$initial_classes = varset($pref['initial_user_classes'],'');

			$irc = explode(',',$initial_classes);
			$icn = array();

			foreach ($irc as $i)
			{
				if (trim($i)) $icn[] = e107::getUserClass()->getName($i);
			}

			$class_text = $frm->userclass('init_classes',$initial_classes, 'checkbox', array('options'=>'classes,force'));

		//	$class_text = e107::getUserClass()->uc_checkboxes('init_classes', $initial_classes, 'classes, force', TRUE);
		//	$class_text = e107::getUserClass()->vetted_tree('init_classes',array($e_userclass,'checkbox_desc'), $initial_classes, 'classes, force, no-excludes');

			$mes->addInfo(UCSLAN_49);

			$text = "<div>
			<form method='post' action='".e_SELF."?mode=main&action=initial' id='initialForm'>
			<table class='table table-bordered adminform'>
			<tr><td>".UCSLAN_43."</td><td>";

				if (count($icn) > 0)
				{
					//  $text .= implode(', ',$icn);
				}
				else
				{
					$text .= LAN_NONE;
				}


				if ($class_text)
				{
					$text .= $class_text."</td></tr><tr><td>";
					$sel_stage = varset($pref['init_class_stage'],2);

					$initClassStages = array(1 =>UCSLAN_47, 2=>UCSLAN_48);

					$text .= UCSLAN_45."<br />	</td>
				    <td>".$frm->select('init_class_stage', $initClassStages, $sel_stage, 'size=xlarge')."<span class='field-help'>".UCSLAN_46."</span>

				    </td></tr></table>
				    <div class='buttons-bar center'>".	$frm->admin_button('set_initial_classes','no-value','create',LAN_UPDATE)."</div>";
				}
				else
				{
					$text .= UCSLAN_39;
				}

			$text .= "</td></tr></table></form></div>";
			return $mes->render() . $text;
		//	$ns->tablerender(ADLAN_38.SEP.UCSLAN_40, $mes->render() . $text);

		}


		/**
		 * @return bool
		 */
		public function initialPageSubmit()
		{
			if(empty($_POST['set_initial_classes']))
			{
				return false;
			}

			$pref['init_class_stage'] = intval($_POST['init_class_stage']);

			$temp = array();

			foreach ($_POST['init_classes'] as $ic)
			{
				$temp[] = intval($ic);
			}

			$newval = implode(',', $temp);

			$pref['initial_user_classes'] = $newval;

			e107::getConfig()->setPref($pref)->save(true,true,true);

			return true;
		}




	}



	class uclass_ui_form extends e_admin_form_ui
	{
		function userclass_type($curVal,$mode)
		{
			$types = array(
				UC_TYPE_STD 	=> UCSLAN_80,
				UC_TYPE_GROUP	=> UCSLAN_81
			);

			return varset($types[$curVal]);
		}

		function options($parms, $value, $id, $attributes)
		{


			$text = "";
			$options = array();

			if($attributes['mode'] == 'read')
			{

				$classID = $this->getController()->getListModel()->get('userclass_id');


				if(!$this->getController()->getUserClassAdmin()->queryCanDeleteClass($classID))
				{
					$options['readParms']['deleteClass'] = e_UC_NOBODY;
				}

				if($classID == 0)
				{
					$options['readParms']['deleteClass'] = e_UC_NOBODY;
					$options['readParms']['editClass'] = e_UC_NOBODY;
				}

				$text .= $this->renderValue('options',$value, $options,$id);

			//	if($parent != 0)
				{
		//			$link = e_SELF."?searchquery=&filter_options=page_chapter__".$id."&mode=page&action=list";
		//			$text .= "<a href='".$link."' class='btn' title='View Pages in this chapter'>".E_32_CUST."</a>";
				}

				return $text;
			}

			return '';
		}


		function userclass_perms($curVal,$mode)
		{
			if($mode == 'read')
			{
				//	$uid = $this->getController()->getModel()->get('user_id');
				//	return e107::getUserPerms()->renderPerms($curVal,$uid);
			}
			if($mode == 'write')
			{
				$prm = e107::getUserPerms();
				return $prm->renderPermTable('tabs',$curVal);

			}

			return '';
		}
	}






	new uclass_admin();

	require_once(e_ADMIN."auth.php");

	e107::getAdminUI()->runPage();

	require_once(e_ADMIN."footer.php");
	exit;



?>
