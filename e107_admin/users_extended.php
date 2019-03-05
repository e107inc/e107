<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once('../class2.php');

if (!getperms('4'))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('users_extended', true);

if(varset($_GET['mode']) == "ajax")
{
	// Construct action string.
	$action = varset($_GET['mode']) . '/' . varset($_GET['action']);

	switch($action)
	{
		case 'ajax/changeTable':
			$tableName = varset($_POST['table_db'], null);

			if($tableName)
			{
				$sql = e107::getDb();
				$tp = e107::getParser();

				$sub_action = '';

				if(e_QUERY)
				{
					$tmp = explode(".", e_QUERY);
					$action = $tp->filter($tmp[0]);
					$sub_action = varset($tmp[1], '');
					$sub_action = $tp->filter($sub_action);
					$id = varset($tmp[2], 0);
					unset($tmp);
				}

				if($sql->select('user_extended_struct', '*', "user_extended_struct_id = '{$sub_action}'"))
				{
					$current = $sql->fetch();
				}
				else
				{
					$current = 'new';
				}

				$currVal = $current['user_extended_struct_values'];
				$curVals = explode(",", varset($currVal));

				// Ajax URL for "Table" dropdown.
				$ajaxGetTableSrc = e_SELF . '?mode=ajax&action=changeTable';

				$text = "<table class='table table-striped table-bordered' style='width:70%;margin-left:0;'><tr><td>";
				$text .= EXTLAN_62 . "</td><td style='70%'>\n";
				$text .= "<select name='table_db' style='width:99%' class='tbox e-ajax' data-src='{$ajaxGetTableSrc}'>";
				$text .= "<option value='' class='caption'>" . LAN_NONE . "</option>";
				$result = e107::getDb()->tables();
				foreach($result as $row2)
				{
					$fld = $row2;
					$selected = (varset($_POST['table_db'], '') == $fld || $curVals[0] == $fld) ? " selected='selected'" : "";
					$text .= "<option value=\"" . $fld . "\" $selected>" . $fld . "</option>\n";
				}
				$text .= "</select></td></tr>";

				if($_POST['table_db'] || $curVals[0])
				{
					// Field ID.
					$text .= "<tr><td>" . EXTLAN_63 . "</td><td>";
					$text .= "<select style='width:99%' class='tbox e-select' name='field_id'>";
					$text .= "<option value='' class='caption'>" . LAN_NONE . "</option>";
					$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0];
					if($sql->gen("DESCRIBE " . MPREFIX . "{$table_list}"))
					{
						while($row3 = $sql->fetch())
						{
							$field_name = $row3['Field'];
							$selected = ($curVals[1] == $field_name) ? " selected='selected' " : "";
							$text .= "<option value=\"$field_name\" $selected>" . $field_name . "</option>";
						}
					}
					$text .= "</select></td></tr><tr><td>";

					// Display Value.
					$text .= EXTLAN_64 . "</td><td>";
					$text .= "<select style='width:99%' class='tbox e-select' name='field_value'>";
					$text .= "<option value='' class='caption'>" . LAN_NONE . "</option>";
					$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0];
					if($sql->gen("DESCRIBE " . MPREFIX . "{$table_list}"))
					{
						while($row3 = $sql->fetch())
						{
							$field_name = $row3['Field'];
							$selected = ($curVals[2] == $field_name) ? " selected='selected' " : "";
							$text .= "<option value=\"$field_name\" $selected>" . $field_name . "</option>";
						}
					}
					$text .= "</select></td></tr><tr><td>";

					// Order.
					$text .= LAN_ORDER . "</td><td>";
					$text .= "<select style='width:99%' class='tbox e-select' name='field_order'>";
					$text .= "<option value='' class='caption'>" . LAN_NONE . "</option>";
					$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0];
					if($sql->gen("DESCRIBE " . MPREFIX . "{$table_list}"))
					{
						while($row3 = $sql->fetch())
						{
							$field_name = $row3['Field'];
							$selected = ($curVals[3] == $field_name) ? " selected='selected' " : "";
							$text .= "<option value=\"$field_name\" $selected>" . $field_name . "</option>";
						}
					}
					$text .= "</select></td></tr>";
				}

				$text .= "</table>";


				$ajax = e107::getAjax();
				$commands = array();
				$commands[] = $ajax->commandInsert('#db_mode', 'html', $text);
				$ajax->response($commands);
				exit;
			}

			break;
	}
}

if (isset($_POST['cancel']))
{
	header('location:'.e_SELF);
	exit;
}

if (isset($_POST['cancel_cat']))
{
	header("location:".e_SELF."?cat");
	exit;
}

function js()
{
	include_once(e_LANGUAGEDIR . e_LANGUAGE . "/lan_user_extended.php");

	$text =  "

	$('.e-select').change(function(e){
			var type = $(this).val();

			if(type == 4)
			{
				$('#db_mode').show();
				$('#values').hide();
			}
			else if(type == 2 || type == 3 || type == 9 || type == 10)
			{
				$('#db_mode').hide();
	            $('#values').show();
			}
			else
			{

	            $('#db_mode').hide();
	            $('#values').hide();

			}

		 	";

	for($i = 1; $i <= 9; $i++)
	{
		$type_const = "UE_LAN_{$i}";
		$help_const = "\"" . str_replace("/", "\/", "EXTLAN_HELP_{$i}") . "\"";
		$text .= "
				if(type == \"{$i}\")
				{
					xtype=\"" . defset($type_const) . "\";
					what=\"" . defset($help_const) . "\";
				}";
	}


	$text .= "
	//	$('#ue_type').innerHTML=''+xtype+'';
	//	$('#ue_help').innerHTML=''+what+''

		 	console.log(type);
		 	return false;
		});
	";

	return $text;
	//FIXME
/*
	$text .= "


		function changeHelp(type) {

			return;
	 //<![CDATA[
		var ftype;
		var helptext;


		";
	for($i = 1; $i <= 9; $i++)
	{
		$type_const = "UE_LAN_{$i}";
		$help_const = "\"" . str_replace("/", "\/", "EXTLAN_HELP_{$i}") . "\"";
		$text .= "
				if(type == \"{$i}\")
				{
					xtype=\"" . defset($type_const) . "\";
					what=\"" . defset($help_const) . "\";
				}";
	}

	$text .= "
		//	document.getElementById('ue_type').innerHTML=''+xtype+'';
		//	document.getElementById('ue_help').innerHTML=''+what+'';

			if(type == 4){
				document.getElementById('db_mode').style.display = '';
				document.getElementById('values').style.display = 'none';
			}else{
	            document.getElementById('values').style.display = '';
				document.getElementById('db_mode').style.display = 'none';
			}
			   // ]]>
		}
	";

	return $text;*/
}

e107::js('footer-inline', js());

















	class user_extended_adminArea extends e_admin_dispatcher
	{

		protected $modes = array(

			'main'	=> array(
				'controller' 	=> 'user_extended_struct_ui',
				'path' 			=> null,
				'ui' 			=> 'user_extended_struct_form_ui',
				'uipath' 		=> null
			),

			'cat'	=> array(
				'controller' 	=> 'user_extended_category_struct_ui',
				'path' 			=> null,
				'ui' 			=> 'user_extended_struct_form_ui',
				'uipath' 		=> null
			),


		);


		protected $adminMenu = array(

			'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => '0|4'),
			'main/add'		=> array('caption'=>  EXTLAN_45, 'perm' => '0|4'),
			'main/create'		=> array('caption'=> EXTLAN_81, 'perm' => '0|4'),
			'cat/list'		=> array('caption'=> LAN_CATEGORIES, 'perm' => '0|4'),
			'cat/create'		=> array('caption'=> LAN_CREATE_CATEGORY, 'perm' => '0|4'),


			// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
		);

			protected $adminMenuIcon = 'e-extended-24';

		/*
		 * 	}
		$var['main']['text'] = EXTLAN_34;
		$var['main']['link'] = e_SELF;

		$var['pre']['text'] = EXTLAN_45;
		$var['pre']['link'] = e_SELF."?pre";

		$var['editext']['text'] = "Add Custom Field";
		$var['editext']['link'] = e_SELF."?editext";

		$var['cat']['text'] = EXTLAN_35;
		$var['cat']['link'] = e_SELF."?cat";



		show_admin_menu(EXTLAN_9, $action, $var);
	}
		 */

		protected $adminMenuAliases = array(
			'main/edit'	=> 'main/list'
		);

		protected $menuTitle = EXTLAN_9;
	}





	class user_extended_struct_ui extends e_admin_ui
	{

		protected $pluginTitle		= EXTLAN_9;
		protected $pluginName		= 'user_extended';
		//	protected $eventName		= 'user_extended-user_extended_struct'; // remove comment to enable event triggers in admin.
		protected $table			= 'user_extended_struct';
		protected $pid				= 'user_extended_struct_id';
		protected $perPage			= 10;
		protected $batchDelete		= true;
		//	protected $batchCopy		= true;
		//	protected $sortField		= 'somefield_order';
		//	protected $orderStep		= 10;
		protected $tabs				= array(LAN_BASIC,LAN_ADVANCED); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

		protected $listQry      	= "SELECT * FROM `#user_extended_struct` WHERE user_extended_struct_type != 0 AND user_extended_struct_text != '_system_'  "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= 'user_extended_struct_order ASC';

		protected $fields 		= array (
		    'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		    'user_extended_struct_id' =>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		    'user_extended_struct_name' =>   array ( 'title' => LAN_NAME, 'type' => 'text', 'data' => 'str', 'readonly'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => 'tdClassRight=form-inline&pre=user_ ', 'class' => 'left', 'thclass' => 'left',  ),
		    'user_extended_struct_text' =>   array ( 'title' => EXTLAN_79, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => 'constant=1', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'user_extended_struct_type' =>   array ( 'title' => EXTLAN_2, 'type' => 'method', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'user_extended_struct_values' =>   array ( 'title' => EXTLAN_82, 'type' => 'method', 'nolist'=>true, 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
            'user_extended_struct_default' =>   array ( 'title' => EXTLAN_16, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
             'user_extended_struct_parent' =>   array ( 'title' => LAN_CATEGORY, 'type' => 'dropdown', 'tab'=>1, 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => array('size'=>'xxlarge'), 'class' => 'left', 'thclass' => 'left',  ),

			// These are combined into user_extended_struct_parms on submit.
             'field_placeholder' => array('title'=>EXTLAN_83, 'tab'=>1, 'type'=>'text', 'data'=>false, 'writeParms'=>array('size'=>'xxlarge')),
            'field_helptip'     => array('title'=>EXTLAN_84, 'tab'=>1, 'type'=>'text', 'data'=>false, 'writeParms'=>array('size'=>'xxlarge')),

            'field_include'     => array('title'=> EXTLAN_15, 'tab'=>1, 'type'=>'textarea', 'data'=>false, 'help'=>EXTLAN_51, 'writeParms'=>array('size'=>'xxlarge')),
			'field_regex'       => array('title'=> EXTLAN_52, 'tab'=>1, 'type'=>'text', 'data'=>false, 'help'=> EXTLAN_53, 'writeParms'=>array('size'=>'xxlarge')),
			'field_regexfail'   => array('title'=> EXTLAN_54, 'tab'=>1,  'type'=>'text', 'data'=>false, 'help'=>EXTLAN_55, 'writeParms'=>array('size'=>'xxlarge')),
			'field_userhide'    => array('title'=> EXTLAN_49, 'tab'=>1, 'type'=>'boolean', 'data'=>false, 'help'=>EXTLAN_50, 'writeParms'=>array('size'=>'xxlarge')),


		      'user_extended_struct_required' =>   array ( 'title' => EXTLAN_18, 'type' => 'method', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		     'user_extended_struct_applicable' =>   array ( 'title' => EXTLAN_5, 'type' => 'userclass', 'data' => 'int', 'filter'=>true, 'batch'=>true, 'width' => '10%', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
             'user_extended_struct_parms' =>   array ( 'title' => "Params", 'type' => 'hidden', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
             'user_extended_struct_read' =>   array ( 'title' =>EXTLAN_6, 'type' => 'userclass', 'data' => 'int',  'filter'=>true, 'batch'=>true,'width' => '10%', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
             'user_extended_struct_write' =>   array ( 'title' => EXTLAN_7, 'type' => 'userclass', 'data' => 'int', 'filter'=>true, 'batch'=>true, 'width' => '10%', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
             'user_extended_struct_signup' =>   array ( 'title' => 'Signup', 'type' => 'hidden', 'nolist'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
             'user_extended_struct_order' =>   array ( 'title' => LAN_ORDER, 'type' => 'hidden', 'nolist'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
             'options' =>   array ( 'title' => LAN_OPTIONS, 'type' => 'method', 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1', 'readParms'=>'sort=1' ),
		);

		protected $fieldpref = array('user_extended_struct_name', 'user_extended_struct_text', 'user_extended_struct_type', 'user_extended_struct_read', 'user_extended_struct_write', 'user_extended_struct_required', 'user_extended_struct_applicable');


		protected $prefs = array(
		);


		/**
		 *  Automatically exectured when edit mode is active.
		 */
		public function EditObserver()
		{
			parent::EditObserver();

			$parms = e107::getDb()->retrieve('user_extended_struct', 'user_extended_struct_parms',"user_extended_struct_id = ".intval($_GET['id']));
			$tmp = explode('^,^', $parms);

			$this->fields['field_include']['writeParms']['default']     =  $tmp[0];
			$this->fields['field_regex']['writeParms']['default']       =  $tmp[1];
			$this->fields['field_regexfail']['writeParms']['default']   =  $tmp[2];
			$this->fields['field_userhide']['writeParms']['default']    =  $tmp[3];
			$this->fields['field_placeholder']['writeParms']['default'] =  $tmp[4];
			$this->fields['field_helptip']['writeParms']['default']     =  $tmp[5];

		}




		public function init()
		{

			if($this->getAction() == 'edit' || $this->getAction() == 'create')
			{
				$this->fields['user_extended_struct_type']['title'] = LAN_TYPE;
			}

			$data = e107::getDb()->retrieve("user_extended_struct", "*", "user_extended_struct_type = 0 ORDER BY user_extended_struct_order ASC", true);

			$opts = array();
			$opts[0] = EXTLAN_36;

			foreach($data as $row)
			{
				$id = $row['user_extended_struct_id'];
			    $opts[$id] = $row['user_extended_struct_name'];
			}


			$this->fields['user_extended_struct_parent']['writeParms']['optArray'] = $opts;


		}


		private function compileData($new_data)
		{
			$parms = array(
				$new_data['field_include'],
				$new_data['field_regex'],
				$new_data['field_regexfail'],
				$new_data['field_userhide'],
				$new_data['field_placeholder'],
				$new_data['field_helptip'],
			);

			if(isset($new_data['field_include']))
			{
				$new_data['user_extended_struct_parms'] = implode('^,^', $parms);
			}

			if($new_data['user_extended_struct_type'] == EUF_DB_FIELD)
			{
		        $new_data['user_extended_struct_values'] = array($new_data['table_db'],$new_data['field_id'],$new_data['field_value'],$new_data['field_order']);
			}

			if(isset($new_data['user_extended_struct_values']))
			{
				$new_data['user_extended_struct_values'] = array_filter($new_data['user_extended_struct_values']);
				$new_data['user_extended_struct_values'] = implode(',',$new_data['user_extended_struct_values']);
			}

		//	e107::getMessage()->addInfo(print_a($new_data,true),'default', true);

			return $new_data;
		}


		// ------- Customize Create --------

		public function beforeCreate($new_data, $old_data)
		{

			$ue = e107::getUserExt();
			$mes = e107::getMessage();


			if($ue->user_extended_field_exist($new_data['user_extended_struct_name']))
			{
				$mes->addError("Field name already exists");
				return false;
			}

			if($ue->user_extended_reserved($new_data['user_extended_struct_name']))
			{
				$mes->addError("Field name is reserved. Please try a different name.");
				return false;
			}

			$new_data = $this->compileData($new_data);

			$field_info = $ue->user_extended_type_text($new_data['user_extended_struct_type'], $new_data['user_extended_struct_default']);

			// wrong type
			if(false === $field_info)
			{
				e107::getMessage()->addDebug("\$field_info is false ".__METHOD__." ".__LINE__);
				return false;
			}
			else
			{
				if(e107::getDb()->gen('ALTER TABLE #user_extended ADD user_'.e107::getParser()->toDB($new_data['user_extended_struct_name'], true).' '.$field_info)===false)
				{
					$mes->addError("Unable to alter table user_extended.");
				}
			}

			if(empty($new_data['user_extended_struct_order']))
			{
			    if($max = e107::getDb()->retrieve('user_extended_struct','MAX(user_extended_struct_order) as maxorder','1'))
			    {
					if(is_numeric($max))
					{
				        $new_data['user_extended_struct_order'] = ($max + 1);
					}
			    }
			}

			return $new_data;

		}



		public function afterCreate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onCreateError($new_data, $old_data)
		{
			// do something
		}

		public function beforeDelete($data,$id)
		{
			$mes = e107::getMessage();

			if(!e107::getUserExt()->user_extended_remove($id, $data['user_extended_struct_name']))
			{
				$mes->addError("Unable to delete column from user_extended table.");
				return false;
			}
			else
			{
				$mes->addSuccess(EXTLAN_86); 
			}

		}


		public function afterDelete($data,$id)
		{


		}


		// ------- Customize Update --------

		public function beforeUpdate($new_data, $old_data, $id)
		{

			$ue = e107::getUserExt();
			$mes = e107::getMessage();

			if ($ue->user_extended_field_exist($new_data['user_extended_struct_name']))
			{
				$field_info = $ue->user_extended_type_text($new_data['user_extended_struct_type'], $new_data['user_extended_struct_default']);

				if(false === $field_info) 	// wrong type
				{
					 return false;
				}
				else
				{
					if(e107::getDb()->gen("ALTER TABLE #user_extended MODIFY user_".e107::getParser()->toDB($new_data['user_extended_struct_name'], true)." ".$field_info)===false)
					{
						$mes->addError("Unable to alter table user_extended.");
					}
				}


			}

			$new_data = $this->compileData($new_data);


			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something
		}

		private function addPageActivate()
		{
			$ue = e107::getUserExt();
			$tp = e107::getParser();
			$ret = "";
			$preList = $ue->parse_extended_xml('getfile');
			$tmp = $preList;

			foreach(array_keys($_POST['activate']) as $f)
			{

				$tmp[$f]['parms'] = $tp->toDB($tmp[$f]['parms']);
				if($ue->user_extended_add($tmp[$f]))
				{
					$ret .= EXTLAN_68." $f ".EXTLAN_69."<br />";

					if ($tmp[$f]['type']=="db field")
					{
						if (is_readable(e_CORE.'sql/extended_'.$f.'.php'))
						{
		           //     $ret .= ($this->process_sql($f)) ? LAN_CREATED." user_extended_{$f}<br />" : LAN_CREATED_FAILED." user_extended_{$f}<br />";
						}
						else
						{
							$ret .= str_replace('[x]',e_CORE.'sql/extended_'.$f.'.php',EXTLAN_78);
						}
					}
				}
				else
				{
					$ret .= EXTLAN_70." $f ".EXTLAN_71."<br />";
				}
			}

			e107::getLog()->add('EUF_11',implode(', ',$_POST['activate']),E_LOG_INFORMATIVE,'');
			e107::getMessage()->addSuccess($ret);
			return $ret;
		}


		private function addPageDeactivate()
		{

			$tp = e107::getParser();
			$sql = e107::getDb();
			$ue = e107::getUserExt();

			$ret = "";
			foreach(array_keys($_POST['deactivate']) as $f)
			{
				$f = $tp->filter($f);

				if($ue->user_extended_remove($f, $f))
				{
					$ret .= EXTLAN_68." $f ".EXTLAN_72."<br />";
					if(is_readable(e_CORE."sql/extended_".$f.".php"))
					{
		                $ret .= ($sql->gen("DROP TABLE ".MPREFIX."user_extended_".$f)) ? LAN_DELETED." user_extended_".$f."<br />" : LAN_DELETED_FAILED." user_extended_".$f."<br />";
					}
				}
				else
				{
					$ret .= EXTLAN_70." $f ".EXTLAN_73."<br />";
				}
			}
			e107::getLog()->add('EUF_12',implode(', ',$_POST['deactivate']),E_LOG_INFORMATIVE,'');
			e107::getMessage()->addSuccess($ret);
			return $ret;
		}



		function addPage()
		{

			$ns = e107::getRender();
			$ue = e107::getUserExt();

			$this->addPageActivate();
			$this->addPageDeactivate();


			// Get list of current extended fields
			$curList = $ue->user_extended_get_fieldlist();
			foreach($curList as $c)
			{
				$curNames[] = $c['user_extended_struct_name'];
			}

			//Get list of predefined fields.
			$preList = $ue->parse_extended_xml('getfile');
			ksort($preList);

			$txt = "
			<form method='post' action='".e_REQUEST_URI."'>
		    <table class='table adminlist'>
			<colgroup>
				<col  />
				<col  />
				<col  />
		        <col  />
				<col  />
				<col  />
			</colgroup>
		    <thead>
				<tr>
				<th>".UE_LAN_21."</th>
				<th>".EXTLAN_79."</th>
				<th>".EXTLAN_2."</th>
				<th>".UE_LAN_22."</th>
				<th class='center' >".EXTLAN_57."</th>
				<th class='center last' >".LAN_OPTIONS."</th>
				</tr>
				</thead>
				<tbody>";

		    foreach($preList as $k=>$a)
			{
				if($k !='version') // don't know why this is appearing in the array.
				{
			        $active = (in_array($a['name'], $curNames)) ? TRUE : FALSE;
					$txt .= $this->show_predefined_field($a,$active);
				}
			}

			$txt .= "</tbody></table></form>";

			$emessage = e107::getMessage();


			return $txt;
		//	$ns->tablerender(EXTLAN_9.SEP.EXTLAN_56,$emessage->render(). $txt);

		}




		function show_predefined_field($var, $active)
		{

			static $head_shown;
			$txt = "";
			$tp = e107::getParser();
			$ue = e107::getUserExt();
			$frm = e107::getForm();

			foreach($var as $key=>$val) // convert predefined xml to default array format
			{
		        $var['user_extended_struct_'.$key] = $val;
			}

			$var['user_extended_struct_type'] = $ue->typeArray[$var['user_extended_struct_type']];
			$var['user_extended_struct_parms'] = $var['include_text'];

			$txt .= "
			<tr>
			<td>{$var['user_extended_struct_name']}</td>
			<td>".constant(strtoupper($var['user_extended_struct_text'])."_DESC")."</td>
			<td>".$ue->user_extended_edit($var,$uVal)."</td>
	        <td>".$tp->toHTML($var['type'], false, 'defs')."</td>
			<td class='center'>".($active ? ADMIN_TRUE_ICON : "&nbsp;")."</td>
			";
		//	$txt .= constant("UE_LAN_".strtoupper($var['text'])."DESC")."<br />";
		//	foreach($showlist as $f)
		//	{
		//		if($var[$f] != "" && $f != 'type' && $f !='text')
		//		{
		//			$txt .= "<strong>{$f}: </strong>".$tp->toHTML($var[$f], false, 'defs')."<br />";
		//		}
		//	}
			$val = (!$active) ? EXTLAN_59 : EXTLAN_60;
			$type = (!$active) ? 'activate' : 'deactivate';
			$style = (!$active) ? 'other' : 'delete';

			$txt .= "
			<td class='center last'>";
	        $txt .= $frm->admin_button($type."[".$var['user_extended_struct_name']."]", $val, $style );
			$txt .= "</td>
			</tr>";
			return $txt;
		}



		/*
			// optional - a custom page.
			public function customPage()
			{
				$ns = e107::getRender();
				$text = 'Hello World!';
				return $text;

			}
		*/

	}



	class user_extended_struct_form_ui extends e_admin_form_ui
	{


		function options($parms, $value, $id, $attributes)
		{

			if($attributes['mode'] == 'read')
			{

				$name = $this->getController()->getListModel()->get('user_extended_struct_name');

				if(strpos($name, 'plugin_') === 0)
				{
					$attributes['readParms']['deleteClass'] = e_UC_NOBODY;
				}

				$text = "<div class='btn-group'>";
				$text .= $this->renderValue('options',$value,$attributes, $id);
				$text .= "</div>";

				return $text;
			}
		}



		// Custom Method/Function
		function user_extended_struct_type($curVal,$mode)
		{

			switch($mode)
			{
				case 'read': // List Page
					$ext = $this->getController()->getListModel()->getData();


				//	return print_a($ext,true);
					$ext['user_extended_struct_required'] = 0; // so the form can be posted.
					return e107::getUserExt()->user_extended_edit($ext,$ext['user_extended_struct_default']);
				//	reutrn e107::getParser()>toHTML(deftrue($ext['user_extended_struct_text'], $ext['user_extended_struct_text']), FALSE, "defs")
					break;

				case 'write': // Edit Page

					if(empty($curVal))
					{
						$curVal = '1';
					}

					$types = e107::getUserExt()->getFieldTypes();

					return $this->select('user_extended_struct_type', $types, $curVal, array('class'=>'tbox e-select'));

			}
		}


		function user_extended_struct_required($curVal, $mode)
		{
			$opts = array('0' => EXTLAN_65, '1' => EXTLAN_66, '2' => EXTLAN_67);

			switch($mode)
			{
				case 'read': // List Page
					return $opts[$curVal];
					break;

				case 'write': // Edit Page


					return $this->select('user_extended_struct_required',$opts, varset($curVal,1),'size=xxlarge');
					break;

				case 'filter':
				case 'batch':
					return  $opts;
					break;
			}



		}




		// Custom Method/Function
		function user_extended_struct_values($curVal,$mode)
		{
			switch($mode)
			{
				case 'read': // List Page
					return $curVal;
					break;

				case 'write': // Edit Page
					return $this->renderStructValues($curVal);
					break;

				case 'filter':
				case 'batch':
					return  array();
					break;
			}
		}



		function renderStructValues($curVal)
		{
			$sql = e107::getDb();
			$frm = e107::getForm();

			$current = $this->getController()->getModel()->getData();

			$type = intval($current['user_extended_struct_type']);

			$val_hide = ($type != 4 && $type !=1 ) ? "visible" : "none";

			if($type == 0)
			{
				$val_hide = 'none';
			}
// return print_a($type,true);		//	return print_a($current,true);

			$text = "<div id='values' style='display:$val_hide'>\n";
			$text .= "<div id='value_container' >\n";
			$curVals = explode(",",varset($current['user_extended_struct_values']));
			if(count($curVals) == 0)
			{
				$curVals[]='';
			}
			$i=0;
			foreach($curVals as $v)
			{
				$id = $i ? "" : " id='value_line'";
				$i++;
				$text .= "
				<span {$id}>
				<input class='tbox' type='text' name='user_extended_struct_values[]' size='40' value='{$v}' /></span><br />";
			}
			$text .= "
			</div>
			<input type='button' class='btn btn-primary' value='".EXTLAN_48."' onclick=\"duplicateHTML('value_line','value_container');\"  />
			<br /><span class='field-help'>".EXTLAN_17."</span>";

				$text .= "<div style='margin-top:10px'>".$frm->checkbox('sort_user_values',1, false, EXTLAN_87)."</div>";
			$text .= "</div>";




// End of Values. --------------------------------------




			$db_hide = ($current['user_extended_struct_type'] == 4) ? "block" : "none";

			// Ajax URL for "Table" dropdown.
			$ajaxGetTableSrc = e_SELF . '?mode=ajax&action=changeTable';

			$text .= "<div id='db_mode' style='display:{$db_hide}'>";
			$text .= "<table class='table table-striped table-bordered' style='width:70%;margin-left:0;'><tr><td>";
			$text .= EXTLAN_62 . "</td><td style='70%'>";
			$text .= "<select name='table_db' style='width:99%' class='tbox e-ajax' data-src='{$ajaxGetTableSrc}'>";
			$text .= "<option value='' class='caption'>" . LAN_NONE . "</option>";

			$result = e107::getDb()->tables();

			foreach($result as $row2)
			{
				$fld = $row2;
				$selected =  (varset($_POST['table_db'],'') == $fld || $curVals[0] == $fld) ? " selected='selected'" : "";
			//	if (MPREFIX!='' && strpos($row2[0], MPREFIX)!==FALSE)
				{
					$text .= "<option value=\"".$fld."\" $selected>".$fld."</option>\n";
				}
			}
			$text .= " </select></td></tr>";

			if($_POST['table_db'] || $curVals[0])
			{
				// Field ID
				$text .= "<tr><td>".EXTLAN_63."</td><td><select style='width:99%' class='tbox e-select' name='field_id' >\n
			<option value='' class='caption'>".LAN_NONE."</option>\n";
				$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;

				if($sql->gen("DESCRIBE ".MPREFIX."{$table_list}"))
				{
					while($row3 = $sql->fetch())
					{
						$field_name=$row3['Field'];
						$selected =  ($curVals[1] == $field_name) ? " selected='selected' " : "";
						$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
					}
				}
				$text .= " </select></td></tr><tr><td>";
				// Field Value
				$text .= EXTLAN_64."</td><td><select style='width:99%' class='tbox e-select' name='field_value' >
			<option value='' class='caption'>".LAN_NONE."</option>\n";
				$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;

				if($sql->gen("DESCRIBE ".MPREFIX."{$table_list}"))
				{
					while($row3 = $sql->fetch())
					{
						$field_name=$row3['Field'];
						$selected =  ($curVals[2] == $field_name) ? " selected='selected' " : "";
						$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
					}
				}
				$text .= " </select></td></tr><tr><td>";

				$text .= LAN_ORDER."</td><td><select style='width:99%' class='tbox e-select' name='field_order' >
			<option value='' class='caption'>".LAN_NONE."</option>\n";
				$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;

				if($sql ->gen("DESCRIBE ".MPREFIX."{$table_list}"))
				{
					while($row3 = $sql->fetch())
					{
						$field_name=$row3['Field'];
						$selected =  ($curVals[3] == $field_name) ? " selected='selected' " : "";
						$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
					}
				}
				$text .= " </select></td></tr>";

			}
			$text .= "</table></div>";
// ---------------------------------------------------------



			return $text;


		}

	} // end class.




	class user_extended_category_struct_ui extends e_admin_ui
	{

		protected $pluginTitle		= EXTLAN_9;
		protected $pluginName		= 'user_extended';
		//	protected $eventName		= 'user_extended-user_extended_struct'; // remove comment to enable event triggers in admin.
		protected $table			= 'user_extended_struct';
		protected $pid				= 'user_extended_struct_id';
		protected $perPage			= 10;
		protected $batchDelete		= true;
		//	protected $batchCopy		= true;
		//	protected $sortField		= 'somefield_order';
		//	protected $orderStep		= 10;
		//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

		protected $listQry      	= "SELECT * FROM `#user_extended_struct` WHERE user_extended_struct_type = 0 AND user_extended_struct_text != '_system_'  "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= 'user_extended_struct_order ASC';

		protected $fields 		= array (
		 'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		             'user_extended_struct_id' =>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		             'user_extended_struct_name' =>   array ( 'title' => LAN_NAME, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		             'user_extended_struct_text' =>   array ( 'title' => EXTLAN_79, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => 'constant=1', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		             'user_extended_struct_type' =>   array ( 'title' => EXTLAN_2, 'type' => 'hidden', 'nolist'=>true, 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => array('default'=>0), 'class' => 'left', 'thclass' => 'left',  ),
		      //       'user_extended_struct_values' =>   array ( 'title' => "Values", 'type' => 'method', 'nolist'=>true, 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
              //       'user_extended_struct_default' =>   array ( 'title' => LAN_DEFAULT, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),

		      //       'user_extended_struct_parent' =>   array ( 'title' => LAN_CATEGORY, 'type' => 'dropdown', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => array('size'=>'xxlarge'), 'class' => 'left', 'thclass' => 'left',  ),
		     //        'user_extended_struct_required' =>   array ( 'title' => EXTLAN_4, 'type' => 'method', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		          'user_extended_struct_applicable' =>   array ( 'title' => EXTLAN_5, 'type' => 'userclass', 'data' => 'int', 'width' => '15%', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),


		       //      'user_extended_struct_parms' =>   array ( 'title' => "Params", 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		              'user_extended_struct_read' =>   array ( 'title' =>EXTLAN_6, 'type' => 'userclass', 'data' => 'int', 'width' => '15%', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		              'user_extended_struct_write' =>   array ( 'title' => EXTLAN_7, 'type' => 'userclass', 'data' => 'int', 'width' => '15%', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		       //       'user_extended_struct_signup' =>   array ( 'title' => 'Signup', 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		               'user_extended_struct_order' =>   array ( 'title' => LAN_ORDER, 'type' => 'number', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'right', 'thclass' => 'right',  ),
		               'options' =>   array ( 'title' => LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1', 'readParms'=>'sort=0' ),
		);

		protected $fieldpref = array('user_extended_struct_name', 'user_extended_struct_text',  'user_extended_struct_read', 'user_extended_struct_write', 'user_extended_struct_applicable');


		protected $prefs = array(
		);


		public function init()
		{
			// Set drop-down values (if any).



		}


		// ------- Customize Create --------

		public function beforeCreate($new_data, $old_data)
		{
			return $new_data;
		}

		public function afterCreate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onCreateError($new_data, $old_data)
		{
			// do something
		}


		// ------- Customize Update --------

		public function beforeUpdate($new_data, $old_data, $id)
		{
			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something
		}



	}









	new user_extended_adminArea();

	require_once(e_ADMIN."auth.php");
	e107::getAdminUI()->runPage();

	require_once(e_ADMIN."footer.php");






	exit;




// -------------------------------------- Old Code --------------------------------------





$e_sub_cat = 'user_extended';

$curtype = '1';
require_once("auth.php");
$ue = new e107_user_extended;
$user = new users_ext;

$frm = e107::getForm();
$mes = e107::getMessage();
$tp = e107::getParser();

require_once(e_HANDLER.'user_extended_class.php');
require_once(e_HANDLER.'userclass_class.php');



$message = '';
$message_type = E_MESSAGE_SUCCESS;

if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tp->filter($tmp[0]);
	$sub_action = varset($tmp[1],'');
	$sub_action = $tp->filter($sub_action);
	$id = varset($tmp[2],0);
	unset($tmp);
}

// TODO $_POST['up_x'] check for the evil IE
$tmp = isset($_POST['up']) ? $tp->filter($_POST['up']) : false;

if (is_array($tmp))
{
	$tmp = array_values($tmp);
	$qs = explode(".", $tmp[0]);
	$_id = intval($qs[0]);
	$_order = intval($qs[1]);
	$_parent = intval($qs[2]);
	if (($_id > 0) && ($_order > 0) /*&& ($_parent > 0)*/)
	{
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_order ='".($_order-1)."'");
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_id='".$_id."'");
		e107::getLog()->add('EUF_01',$_id.', '.$_order.', '.$_parent,E_LOG_INFORMATIVE,'');
		e107::getCache()->clear_sys('user_extended_struct', true);
	}
}

// TODO $_POST['down_x'] check for the evil IE
$tmp = isset($_POST['down']) ? $tp->filter($_POST['down']) : false;

if (is_array($tmp))
{
	$tmp = array_values($tmp);
	$qs = explode(".", $tmp[0]);
	$_id = intval($qs[0]);
	$_order = intval($qs[1]);
	$_parent = intval($qs[2]);
	if (($_id > 0) && ($_order > 0)/* && ($_parent > 0)*/)
	{
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_order='".($_order+1)."'");
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type > 0 AND user_extended_struct_parent = {$_parent} AND user_extended_struct_id='".$_id."'");
		e107::getLog()->add('EUF_02',$_id.', '.$_order.', '.$_parent,E_LOG_INFORMATIVE,'');
		e107::getCache()->clear_sys('user_extended_struct', true);
	}
}


if (isset($_POST['catup_x']) || isset($_POST['catup']))
{
	$qs = explode(".", $_POST['id']);
	$_id = intval($qs[0]);
	$_order = intval($qs[1]);
	if (($_id > 0) && ($_order > 0))
	{
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type = 0 AND user_extended_struct_order='".($_order-1)."'");
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type = 0 AND user_extended_struct_id='".$_id."'");
		e107::getLog()->add('EUF_03',$_id.', '.$_order,E_LOG_INFORMATIVE,'');
		e107::getCache()->clear_sys('user_extended_struct', true);
	}
}


if (isset($_POST['catdown_x']) || isset($_POST['catdown']))
{
	$qs = explode(".", $_POST['id']);
	$_id = intval($qs[0]);
	$_order = intval($qs[1]);
	if (($_id > 0) && ($_order > 0))
	{
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order-1 WHERE user_extended_struct_type = 0 AND user_extended_struct_order='".($_order+1)."'");
		$sql->db_Update("user_extended_struct", "user_extended_struct_order=user_extended_struct_order+1 WHERE user_extended_struct_type = 0 AND user_extended_struct_id='".$_id."'");
		e107::getLog()->add('EUF_04',$_id.', '.$_order,E_LOG_INFORMATIVE,'');
		e107::getCache()->clear_sys('user_extended_struct', true);
	}
}



if (isset($_POST['add_field']))
{
	$ue_field_name = str_replace(' ','_',trim($_POST['user_field']));		// Replace space with underscore - better security
	if (preg_match('#^\w+$#',$ue_field_name) === 1)						// Check for allowed characters, finite field length
	{
		if($_POST['user_type']==EUF_DB_FIELD)
		{
			$_POST['user_values'] = array(
				$tp->filter($_POST['table_db']),
				$tp->filter($_POST['field_id']),
				$tp->filter($_POST['field_value']),
				$tp->filter($_POST['field_order']),
			);
		}

		if(!empty($_POST['sort_user_values']))
		{
			sort($_POST['user_values']);
		}

		$new_values = $user->make_delimited($_POST['user_values']);
		$new_parms = $tp->toDB($_POST['user_include']."^,^".$_POST['user_regex']."^,^".$_POST['user_regexfail']."^,^".$_POST['user_hide']);

// Check to see if its a reserved field name before adding to database
		if ($ue->user_extended_reserved($ue_field_name))
		{  // Reserved field name
			$message = "[user_".$tp->toHTML($ue_field_name)."] ".EXTLAN_74;
			$message_type = E_MESSAGE_ERROR;
		}
		else
		{

				$result = $mes->addAuto($ue->user_extended_add($ue_field_name, $tp->toDB($_POST['user_text']), intval($_POST['user_type']), $new_parms, $new_values, $tp->toDB($_POST['user_default']), intval($_POST['user_required']), intval($_POST['user_read']), intval($_POST['user_write']), intval($_POST['user_applicable']), 0, intval($_POST['user_parent'])), 'insert', EXTLAN_29, false, false);

		//	$result = $mes->addAuto($ue->user_extended_add($ue_field_name, $tp->toDB($_POST['user_text']), intval($_POST['user_type']), $new_parms, $new_values, $tp->toDB($_POST['user_default']), intval($_POST['user_required']), intval($_POST['user_read']), intval($_POST['user_write']), intval($_POST['user_applicable']), 0, intval($_POST['user_parent'])), 'insert', EXTLAN_29, false, false);
			if(!$result)
			{
				$message = EXTLAN_75;
				$message_type = E_MESSAGE_INFO;
			}
			else
			{
				e107::getLog()->add('EUF_05',$ue_field_name.'[!br!]'.$tp->toDB($_POST['user_text']).'[!br!]'.intval($_POST['user_type']),E_LOG_INFORMATIVE,'');
				e107::getCache()->clear_sys('user_extended_struct', true);
			}
		}
	}
	else
	{
		$message = EXTLAN_76." : ".$tp->toHTML($ue_field_name);
		$message_type = E_MESSAGE_ERROR;
	}
}


if (isset($_POST['update_field']))
{
	if($_POST['user_type']==EUF_DB_FIELD)
	{
    	$_POST['user_values'] = array(
		    $tp->filter($_POST['table_db']),
		    $tp->filter($_POST['field_id']),
			$tp->filter($_POST['field_value']),
			$tp->filter($_POST['field_order']),
	    );
	}

	if(!empty($_POST['sort_user_values']))
	{
		sort($_POST['user_values']);
	}

	$upd_values = $user->make_delimited($_POST['user_values']);
	$upd_parms = $tp->toDB($_POST['user_include']."^,^".$_POST['user_regex']."^,^".$_POST['user_regexfail']."^,^".$_POST['user_hide']);
	$result = $mes->addAuto($ue->user_extended_modify($sub_action, $tp->toDB($_POST['user_field']), $tp->toDB($_POST['user_text']), intval($_POST['user_type']), $upd_parms, $upd_values, $tp->toDB($_POST['user_default']), intval($_POST['user_required']), intval($_POST['user_read']), intval($_POST['user_write']), intval($_POST['user_applicable']), intval($_POST['user_parent'])), 'update', EXTLAN_29, false, false);
	if($result)
	{
		e107::getLog()->add('EUF_06',$tp->toDB($_POST['user_field']).'[!br!]'.$tp->toDB($_POST['user_text']).'[!br!]'.intval($_POST['user_type']),E_LOG_INFORMATIVE,'');
		e107::getCache()->clear_sys('user_extended_struct', true);
	}
}


if (isset($_POST['update_category']))
{
	if (preg_match('#^[\w\s]+$#', $_POST['user_field']) === 1) // Check for allowed characters
  	{
		$name = trim($tp->toDB($_POST['user_field']));
		$result = $mes->addAuto(
			$sql->db_Update(
				"user_extended_struct",
				"user_extended_struct_name = '{$name}', user_extended_struct_text='".$tp->toDB($_POST['user_text'])."', user_extended_struct_read = '".intval($_POST['user_read'])."', user_extended_struct_write = '".intval($_POST['user_write'])."', user_extended_struct_applicable = '".intval($_POST['user_applicable'])."' WHERE user_extended_struct_id = '{$sub_action}'"),
				'update',
				EXTLAN_43,
				false,
				false
		);
		if($result)
		{
			e107::getLog()->add('EUF_09',$name,E_LOG_INFORMATIVE,'');
			e107::getCache()->clear_sys('user_extended_struct', true);
		}
	}
	else
	{
		$message = EXTLAN_80;
		$message_type = E_MESSAGE_ERROR;
	}
}


if (isset($_POST['add_category']))
{
	if (preg_match('#^[\w\s]+$#', $_POST['user_field']) === 1) // Check for allowed characters
  	{
		$name = $tp->toDB($_POST['user_field']);
		$result = $mes->addAuto($sql->db_Insert("user_extended_struct","'0', '{$name}', '".$tp->toDB($_POST['user_text'])."', 0, '', '', '', '".intval($_POST['user_read'])."', '".intval($_POST['user_write'])."', '0', '0', '".intval($_POST['user_applicable'])."', '0', '0'"), 'insert', EXTLAN_40, false, false);
		if($result)
		{
			e107::getLog()->add('EUF_08',$name,E_LOG_INFORMATIVE,'');
			e107::getCache()->clear_sys('user_extended_struct', true);
		}
	}
	else
	{
		$message = EXTLAN_80;
		$message_type = E_MESSAGE_ERROR;
	}
}


// Delete category
if (varset($_POST['eu_action'],'') == "delcat")
{
	list($_id, $_name) = explode(",",$_POST['key']);
	if (count($ue->user_extended_get_fields($_id)) > 0)
	{
	  $message = EXTLAN_77;
	  $message_type = E_MESSAGE_INFO;
	}
	elseif($ue->user_extended_remove($_id, $_name))
	{
		e107::getLog()->add('EUF_10',$_id.', '.$_name,E_LOG_INFORMATIVE,'');
		$message = EXTLAN_41;
		e107::getCache()->clear_sys('user_extended_struct', true);
	}
}

if(isset($_POST['activate']))
{
	$message .= $user->field_activate();
}

if(isset($_POST['deactivate']))
{
	$message .= $user->field_deactivate();
}



/*if($sql->select("user_extended_struct","DISTINCT(user_extended_struct_parent)"))
{
	$plist = $sql->db_getList();
	foreach($plist as $_p)
	{
		$o = 0;
		if($sql->select("user_extended_struct", "user_extended_struct_id", "user_extended_struct_parent = {$_p['user_extended_struct_parent']} && user_extended_struct_type != 0 ORDER BY user_extended_struct_order ASC"))
		{
			$_list = $sql->db_getList();
			foreach($_list as $r)
			{
				$sql->db_Update("user_extended_struct", "user_extended_struct_order = '{$o}' WHERE user_extended_struct_id = {$r['user_extended_struct_id']}");
				$o++;
			}
		}
	}
}*/


if($message)
{
    $emessage = eMessage::getInstance();
	$emessage->add($message, $message_type);
}


if(isset($_POST['table_db']) && !$_POST['add_field'] && !$_POST['update_field'])
{
	$action = "continue";
	$current['user_extended_struct_name'] = $tp->filter($_POST['user_field']);
    $current['user_extended_struct_parms'] = $tp->filter($_POST['user_include']."^,^".$_POST['user_regex']."^,^".$_POST['user_regexfail']."^,^".$_POST['user_hide']);
    $current['user_extended_struct_text'] = $tp->filter($_POST['user_text']);
	$current['user_extended_struct_type'] = $tp->filter($_POST['user_type']);
	$user->show_extended($current);
}





if ($action == "editext")
{
	if($sql->select('user_extended_struct','*',"user_extended_struct_id = '{$sub_action}'"))
	{
		$tmp = $sql->fetch();
		$user->show_extended($tmp);
	}
	else
	{
		$user->show_extended('new');
	}
}

if($action == 'pre')
{
	$user->show_predefined();
}

if($action == 'cat')
{
	if(is_numeric($sub_action))
	{
		if($sql->select('user_extended_struct','*',"user_extended_struct_id = '{$sub_action}'"))
		{
			$tmp = $sql->fetch();
		}
	}
	$user->show_categories($tmp);
}

require_once("footer.php");

class users_ext
{
    protected $catList;
	protected $catNums;

	function __construct()
	{
        global $action,$ue;

        if (varset($_POST['eudel'],''))
		{
			foreach(array_keys($_POST['eudel']) as $name)
			{
            	$this->delete_extended($name);
			}
		}

        $this->catList = $ue->user_extended_get_categories();
		$this->catList[0][0] = array('user_extended_struct_name' => EXTLAN_36);
		$this->catNums = array_keys($this->catList);

		if($action == 'cat' && !empty($_POST))
		{
			$this->reorderItems();
		}

        if (!e_QUERY || $action == 'main')
		{
  			// moved here for better performance
			if(!empty($_POST))
			{
				$this->reorderItems();
			}
			$this->showExtendedList();
		}

	}

	function reorderItems()
	{
		$sql = e107::getDb();
		if($sql->select("user_extended_struct","DISTINCT(user_extended_struct_parent)"))
		{
			$plist = $sql->db_getList();
			foreach($plist as $_p)
			{
				$o = 0;
				if($sql->select("user_extended_struct", "user_extended_struct_id", "user_extended_struct_parent = {$_p['user_extended_struct_parent']} && user_extended_struct_type != 0 ORDER BY user_extended_struct_order ASC"))
				{
					$_list = $sql->db_getList();
					foreach($_list as $r)
					{
						$sql->db_Update("user_extended_struct", "user_extended_struct_order = '{$o}' WHERE user_extended_struct_id = {$r['user_extended_struct_id']}");
						$o++;
					}
				}
			}
			e107::getCache()->clear_sys('user_extended_struct', true);
		}
	}



	function delete_extended($_name)
	{
		$ue 	= e107::getUserExt();
		$log 	= e107::getAdminLog();
		$mes 	= e107::getMessage();

		if ($ue->user_extended_remove($_name, $_name))
		{
			$log->add('EUF_07',$_name, E_LOG_INFORMATIVE);
			$mes->addSuccess(LAN_DELETED." [".$_name."]");
			e107::getCache()->clear_sys('user_extended_struct', true);
		}
		else
		{
        	$mes->addError(LAN_ERROR." [".$_name."]");
		}
	}

	function showExtendedList()
	{
        global  $curtype, $mySQLdefaultdb, $action, $sub_action;

		$ue = e107::getUserExt();
        $frm = e107::getForm();
        $ns = e107::getRender();
		$sql = e107::getDb();
		$tp = e107::getParser();

		$extendedList = $ue->user_extended_get_fields();

        $emessage = e107::getMessage();
	  	$text = $emessage->render();

		$mode = 'show';
			$text .= "

			   <form method='post' action='".e_SELF."' >
			   <table class='table adminlist'>
			<thead>
				<tr>
				<th>".EXTLAN_1."</th>
                <th>".EXTLAN_79."</th>

				<th>".EXTLAN_2."</th>
				<th>".EXTLAN_44."</th>
				<th>".EXTLAN_4."</th>

				<th>".EXTLAN_5."</th>
				<th>".EXTLAN_6."</th>
				<th>".EXTLAN_7."</th>
				<th class='center last' colspan='2'>".EXTLAN_8."</th>
				</tr>
			</thead>
			<tbody>
			";

			foreach($this->catNums as $cn)
			{

				$i=0;
				$category_name = $this->catList[$cn][0]['user_extended_struct_name'];

				if(vartrue($extendedList[$cn]))  //	Show current extended fields
				{
					foreach($extendedList[$cn] as $ext)
					{

						$name = $ext['user_extended_struct_name'];
						$fname = "user_".$name;

						$id = $ext['user_extended_struct_id'];

						$uVal = str_replace(chr(1), "", $ext['user_extended_struct_default']);		// Is this right?
							$text .= "
							<tr>
							<td>{$ext['user_extended_struct_name']}</td>
							<td>".$tp->toHTML(deftrue($ext['user_extended_struct_text'], $ext['user_extended_struct_text']), FALSE, "defs")."</td>
							<td class='left'>".$ue->user_extended_edit($ext,$uVal)."</td>
							<td class='left'>".$category_name."</td>
							<td>".($ext['user_extended_struct_required'] == 1 ? LAN_YES : LAN_NO)."</td>
							<td>".r_userclass_name($ext['user_extended_struct_applicable'])."</td>
							<td>".r_userclass_name($ext['user_extended_struct_read'])."</td>
							<td>".r_userclass_name($ext['user_extended_struct_write'])."</td>
							<td>";

						  	if($i > 0)
						  	{
						 		$text .= "<input type='image' alt='' title='".EXTLAN_26."' src='".ADMIN_UP_ICON_PATH."' name='up[$id]' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}.{$ext['user_extended_struct_parent']}' />";
							}
							if($i <= count($extendedList[$cn])-2)
							{
								$text .= "<input type='image' alt='' title='".EXTLAN_25."' src='".ADMIN_DOWN_ICON_PATH."' name='down[$id]' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}.{$ext['user_extended_struct_parent']}' />";
							}
							$text .= "
						  	</td>
							<td class='center' style='width:10%;white-space:nowrap'>

							<a class='btn btn-default' style='text-decoration:none' href='".e_SELF."?editext.".$id."'>".ADMIN_EDIT_ICON."</a>
							".$frm->submit_image('eudel['.$name.']',$id, 'delete',  LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'action delete btn btn-default'.$delcls));

		 					// ."<input class='btn btn-large' type='image' title='".LAN_DELETE."' name='eudel[".$name."]' src='".ADMIN_DELETE_ICON_PATH."' value='".$id."' onclick='return confirm(\"".EXTLAN_27."\")' />
							$text .= "</td>
								</tr>
							";
							$i++;
					  }
				}
				elseif($cn == 0)
				{
						$text .= "
						<tr>
						<td colspan='10' class='center'>".EXTLAN_28."</td>
						</tr>
						";
				}

			}

			//Show add/edit form
			$text .= "</tbody>
			</table></form>";

	  		$ns->tablerender(EXTLAN_9, $text);


	}

	function show_extended($current = '')  // Show Add fields List.
	{
        global $ue, $curtype,$mySQLdefaultdb, $action, $sub_action;

		$sql = e107::getDb();
		$frm = e107::getForm();
		$ns = e107::getRender();
		$tp = e107::getParser();


 			if($current == 'new')
			{
					$mode = 'new';
				  $current = array();
				  $current_include = '';
				  $current_regex = '';
				  $current_regexfail = '';
				  $current_hide = '';
			}
			else
			{	// Editing existing definition
				$mode = 'edit';
				list($current_include, $current_regex, $current_regexfail, $current_hide) = explode("^,^",$current['user_extended_struct_parms']);
			}

			$text = "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
			<fieldset id='core-user-extended-create'>";

			$text .= "
            <table class='table adminform'>
            	<colgroup>
            		<col class='col-label' />
            		<col class='col-control' />
            	</colgroup>
			<tr>
			<td>".EXTLAN_10.":</td>
			<td>user_";
			if(is_array($current) && varset($current['user_extended_struct_name']))
			{
				$text .= $current['user_extended_struct_name']."
				<input type='hidden' name='user_field' value='".vartrue($current['user_extended_struct_name'])."' />
				";
			}
			else
			{
				$text .= "
				<input class='tbox' type='text' name='user_field' size='40' value='".vartrue($current['user_extended_struct_name'])."' maxlength='50' required pattern='[a-z0-9_]*' />
				";
			}
			$text .= "
			<br /><span class='field-help'>".EXTLAN_11."</span>
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_12.":</td>
			<td colspan='3'>
			<input class='tbox' type='text' name='user_text' size='40' value='".vartrue($current['user_extended_struct_text'])."' maxlength='50' required /><br />
			<span class='field-help'>".EXTLAN_13."</span>
			</td>
			</tr>
			";

			$text .= "<tr>
			<td >".EXTLAN_14."</td>
			<td colspan='3'>
			<select onchange='changeHelp(this.value)' class='tbox e-select' name='user_type' id='user_type'>";
			foreach($ue->user_extended_types as $key => $val)
			{
				$selected = (vartrue($current['user_extended_struct_type']) == $key) ? " selected='selected'": "";
				$text .= "<option value='".$key."' $selected>".$val."</option>";
			}
			$curtype = $current['user_extended_struct_type'];
			if(!$curtype)
			{
				$curtype = '1';
			}
			$text .= "
			</select>
			</td></tr>";



			$text .= "
			<tr>
			<td>".EXTLAN_3."</td>
			<td colspan='3'>";
  // Start of Values ---------------------------------

      		$val_hide = ($current['user_extended_struct_type'] != 4) ? "visible" : "none";

			$text .= "<div id='values' style='display:$val_hide'>\n";
			$text .= "<div id='value_container' >\n";
			$curVals = explode(",",varset($current['user_extended_struct_values']));
			if(count($curVals) == 0)
			{
				$curVals[]='';
			}
			$i=0;
			foreach($curVals as $v){
				$id = $i ? "" : " id='value_line'";
				$i++;
				$text .= "
				<span {$id}>
				<input class='tbox' type='text' name='user_values[]' size='40' value='{$v}' /></span><br />";
			}
			$text .= "
			</div>
			<input type='button' class='btn btn-primary' value='".EXTLAN_48."' onclick=\"duplicateHTML('value_line','value_container');\"  />
			<br /><span class='field-help'>".EXTLAN_17."</span>


			<div style='margin-top:10px'>".$frm->checkbox('sort_user_values',1, false, "Sort values")."</div>

			</div>";
// End of Values. --------------------------------------




		$db_hide = ($current['user_extended_struct_type'] == 4) ? "block" : "none";

		// Ajax URL for "Table" dropdown.
		$ajaxGetTableSrc = e_SELF . '?mode=ajax&action=changeTable';

		$text .= "<div id='db_mode' style='display:{$db_hide}'>";
		$text .= "<table style='width:70%;margin-left:0;'><tr><td>";
		$text .= EXTLAN_62 . "</td><td style='70%'>";
		$text .= "<select name='table_db' style='width:99%' class='tbox e-ajax' data-src='{$ajaxGetTableSrc}'>";
		$text .= "<option value='' class='caption'>" . LAN_NONE . "</option>";


			$result = e107::getDb()->tables();
			foreach ($result as $row2)
			{
		//	  $fld = str_replace(MPREFIX,"",$row2[0]);
			  $fld = $row2;
			  $selected =  (varset($_POST['table_db'],'') == $fld || $curVals[0] == $fld) ? " selected='selected'" : "";
			//  if (MPREFIX!='' && strpos($row2[0], MPREFIX)!==FALSE)
			  {
				$text .= "<option value=\"".$fld."\" $selected>".$fld."</option>\n";
			  }
			}
			$text .= " </select></td></tr>";
     	if($_POST['table_db'] || $curVals[0])
		{
			// Field ID
			$text .= "<tr><td>".EXTLAN_63."</td><td><select style='width:99%' class='tbox e-select' name='field_id' >\n
			<option value='' class='caption'>".LAN_NONE."</option>\n";
			$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;

			if($sql->gen("DESCRIBE ".MPREFIX."{$table_list}"))
			{
		   		while($row3 = $sql->fetch())
		   		{
    				$field_name=$row3['Field'];
					$selected =  ($curVals[1] == $field_name) ? " selected='selected' " : "";
					$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
				}
			}
    		$text .= " </select></td></tr><tr><td>";
             // Field Value
			$text .= EXTLAN_64."</td><td><select style='width:99%' class='tbox e-select' name='field_value' >
			<option value='' class='caption'>".LAN_NONE."</option>\n";
			$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;

			if($sql->gen("DESCRIBE ".MPREFIX."{$table_list}"))
			{
		   		while($row3 = $sql->fetch())
		   		{
    				$field_name=$row3['Field'];
					$selected =  ($curVals[2] == $field_name) ? " selected='selected' " : "";
					$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
				}
			}
    		$text .= " </select></td></tr><tr><td>";

			$text .= LAN_ORDER."</td><td><select style='width:99%' class='tbox e-select' name='field_order' >
			<option value='' class='caption'>".LAN_NONE."</option>\n";
			$table_list = ($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;

			if($sql ->gen("DESCRIBE ".MPREFIX."{$table_list}"))
			{
		   		while($row3 = $sql->fetch())
		   		{
    				$field_name=$row3['Field'];
					$selected =  ($curVals[3] == $field_name) ? " selected='selected' " : "";
					$text .="<option value=\"$field_name\" $selected>".$field_name."</option>\n";
				}
			}
    		$text .= " </select></td></tr>";

     	}
        $text .= "</table></div>";
// ---------------------------------------------------------






			$text .= "
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_16."</td>
			<td colspan='3'>
			<input class='tbox' type='text' name='user_default' size='40' value='".vartrue($current['user_extended_struct_default'])."' />
			</td>
			</tr>


			<tr>
			<td>".EXTLAN_15."</td>
			<td colspan='3'>
			<textarea class='tbox' name='user_include' cols='60' rows='2'>{$current_include}</textarea><br />
			<span class='field-help'>".EXTLAN_51."</span><br />
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_52."</td>
			<td colspan='3'>
			<input class='tbox' type='text' name='user_regex' size='30' value='{$current_regex}' /><br />
			<span class='field-help'>".EXTLAN_53."</span><br />
			</td>
			</tr>

			<tr>
			<td >".EXTLAN_54."</td>
			<td colspan='3'>
			<input class='tbox' type='text' name='user_regexfail' size='40' value='{$current_regexfail}' /><br />
			<span class='field-help'>".EXTLAN_55."</span><br />
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_44."</td>
			<td colspan='3'>
			<select class='tbox e-select' name='user_parent'>";
			foreach($this->catNums as $k)
			{
				$sel = ($k == varset($current['user_extended_struct_parent'])) ? " selected='selected' " : "";
				$text .= "<option value='{$k}' {$sel}>".$this->catList[$k][0]['user_extended_struct_name']."</option>\n";
			}
			$text .= "</select>

			</td>
			</tr>

			<tr>
			<td>".EXTLAN_18."</td>
			<td colspan='3'>";
			/*
			$text .= "
			<select class='tbox e-select' name='user_required'>
			";

			foreach($_r as $k => $v)
			{
				$sel = (varset($current['user_extended_struct_required'],1) == $k ? " selected='selected' " : "");
				$text .= "<option value='{$k}' {$sel}>{$v}</option>\n";
			}

			$text .= "</select>";
			*/

			$_r = array('0' => EXTLAN_65, '1' => EXTLAN_66, '2' => EXTLAN_67);

			$text .= $frm->select('user_required',$_r, varset($current['user_extended_struct_required'],1),'size=xxlarge');

			$text .= "

			<br />
			<span class='field-help'>".EXTLAN_19."</span>
			</td>
			</tr>

			<tr>
			<td >".EXTLAN_5."</td>
			<td colspan='3'>
			".r_userclass("user_applicable", varset($current['user_extended_struct_applicable'],253), 'off', 'member, admin, main, classes, nobody')."<br /><span class='field-help'>".EXTLAN_20."</span>
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_6."</td>
			<td colspan='3'>
			".r_userclass("user_read", varset($current['user_extended_struct_read']), 'off', 'public, member, admin, main, readonly, classes')."<br /><span class='field-help'>".EXTLAN_22."</span>
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_7."</td>
			<td colspan='3'>
			".r_userclass("user_write", varset($current['user_extended_struct_write']), 'off', 'member, admin, main, classes')."<br /><span class='field-help'>".EXTLAN_21."</span>
			</td>
			</tr>

			<tr>
			<td>".EXTLAN_49."
			</td>
			<td colspan='3'>
			<select class='tbox e-select' name='user_hide'>
			";
			if($current_hide)
			{
				$text .= "
				<option value='1' selected='selected'>".LAN_YES."</option>
				<option value='0'>".LAN_NO."</option>";
			}
			else
			{
				$text .= "
				<option value='1'>".LAN_YES."</option>
				<option value='0' selected='selected'>".LAN_NO."</option>";
			}
			$text .= "
			</select>
			<br /><span class='field-help'>".EXTLAN_50."</span>
			</td>
			</tr>
			";

			$text .= "
			</table>
			<div class='buttons-bar center'>
			";

//			if ((!is_array($current) || $action == "continue") && $sub_action == "")
			if ((($mode == 'new') || $action == "continue") && $sub_action == "")
			{
				$text .= $frm->admin_button('add_field', EXTLAN_23);
			}
			else
			{
				$text .= $frm->admin_button('update_field', EXTLAN_24,'update').
				$frm->admin_button('cancel', EXTLAN_33,'cancel');
			}


			$text .= "</div>
			</fieldset></form>
			";

		//		$text .= "</div>";
		$emessage = e107::getMessage();
		$ns->tablerender(EXTLAN_9.SEP.LAN_ADD,$emessage->render().$text);
	}


	function show_categories($current)
	{
		global $sql, $ns, $ue, $frm;

		$text = "<div style='text-align:center'>";
		$text .= "
        <table class='table adminlist'>

		<thead>
		<tr>
		<th>".EXTLAN_1."</th>
		<th>".EXTLAN_79."</th>
		<th>".EXTLAN_5."</th>
		<th>".EXTLAN_6."</th>
		<th>".EXTLAN_7."</th>
		<th>&nbsp;</th>
		<th>".EXTLAN_8."</th>
		</tr>
		</thead>
		<tbody>
		";
		$catList = $ue->user_extended_get_categories(FALSE);
		if(count($catList))
		{
			//			Show current categories
			$i=0;
			foreach($catList as $ext)
			{
				if ($ext['user_extended_struct_order'] != $i)
				{
					$ext['user_extended_struct_order'] = $i;
					$xID=$ext['user_extended_struct_id'];
					$sql->db_Update("user_extended_struct", "user_extended_struct_order=$i WHERE user_extended_struct_type = 0 AND user_extended_struct_id=$xID");
				}

				$text .= "
				<tr>
				<td>{$ext['user_extended_struct_name']}</td>
				<td>".deftrue($ext['user_extended_struct_text'], $ext['user_extended_struct_text'])."</td>
				<td>".r_userclass_name($ext['user_extended_struct_applicable'])."</td>
				<td>".r_userclass_name($ext['user_extended_struct_read'])."</td>
				<td>".r_userclass_name($ext['user_extended_struct_write'])."</td>
				<td>
				<form method='post' action='".e_SELF."?cat'>
				<div>
				<input type='hidden' name='id' value='{$ext['user_extended_struct_id']}.{$ext['user_extended_struct_order']}' />
				";
				if($i > 0)
				{
					$text .= "
					<input type='image' alt='' title='".EXTLAN_26."' src='".ADMIN_UP_ICON_PATH."' name='catup' value='{$ext['user_extended_struct_id']}.{$i}' />
					";
				}
				if($i <= count($catList)-2)
				{
					$text .= "<input type='image' alt='' title='".EXTLAN_25."' src='".ADMIN_DOWN_ICON_PATH."' name='catdown' value='{$ext['user_extended_struct_id']}.{$i}' />";
				}
				$text .= "
				</div>
				</form>
				</td>
				<td class='center' style='white-space: nowrap'>
				<form method='post' action='".e_SELF."?cat' onsubmit='return confirm(\"".EXTLAN_27."\")'>
				<div>
				<input type='hidden' name='eu_action' value='delcat' />
				<input type='hidden' name='key' value='{$ext['user_extended_struct_id']},{$ext['user_extended_struct_name']}' />
				<a class='btn btn-default'  href='".e_SELF."?cat.{$ext['user_extended_struct_id']}'>".ADMIN_EDIT_ICON."</a>
				<button class='btn btn-default btn-secondary action delete' type='submit' title='".LAN_DELETE."' name='eudel' data-confirm='".LAN_JSCONFIRM."' >".ADMIN_DELETE_ICON."</button>
				</div>
				</form>
				</td>
				</tr>
				";
				$i++;
			}
		}
		else
		{
			$text .= "
			<tr>
			<td colspan='8' class='center'>".EXTLAN_37."</td>
			</tr>
			";
		}

		//Show add/edit form
		$text .= "</tbody>
		</table>
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		";
		$text .= "<div><br /></div>
		<table class='table adminform'>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>";

		$text .= "

		<tr>
		<td>".EXTLAN_38.":</td>
		<td colspan='3'>
		<input class='tbox' type='text' name='user_field' size='40' value='".$current['user_extended_struct_name']."' maxlength='50' />
		<br /><span class='field-help'>".EXTLAN_11."</span>
		</td>
		</tr>

		<tr>
		<td>".EXTLAN_31.":</td>
		<td colspan='3'>
		<input class='tbox' type='text' name='user_text' size='40' value='".$current['user_extended_struct_text']."' maxlength='255' />
		<br /><span class='field-help'>".EXTLAN_32."</span>
		</td>
		</tr>

		<tr>
		<td>".EXTLAN_5."</td>
		<td colspan='3'>
		".r_userclass("user_applicable", $current['user_extended_struct_applicable'], 'off', 'member, admin, classes')."<br /><span class='field-help'>".EXTLAN_20."</span>
		</td>
		</tr>

		<tr>
		<td>".EXTLAN_6."</td>
		<td colspan='3'>
		".r_userclass("user_read", $current['user_extended_struct_read'], 'off', 'public, member, admin, classes, readonly')."<br /><span class='field-help'>".EXTLAN_22."</span>
		</td>
		</tr>

		<tr>
		<td >".EXTLAN_7."</td>
		<td colspan='3'>
		".r_userclass("user_write", $current['user_extended_struct_write'], 'off', 'member, admin, classes')."<br /><span class='field-help'>".EXTLAN_21."</span>
		</td>
		</tr>
		</table>";


		$text .= "<div class='buttons-bar center'>";

		if (!is_array($current))
		{
			$text .= $frm->admin_button('add_category', EXTLAN_39);
		}
		else
		{
        	$text .= $frm->admin_button('update_category', EXTLAN_42,'update').
				$frm->admin_button('cancel', EXTLAN_33);
		}
		// ======= end added by Cam.
		$text .= "</div></form></div>";
		$emessage = e107::getMessage();
		$ns->tablerender(EXTLAN_9.SEP.LAN_CATEGORIES, $emessage->render().$text);
	}


	function show_options($action)
	{
		if ($action == "")
		{
			$action = "main";
		}
		$var['main']['text'] = EXTLAN_34;
		$var['main']['link'] = e_SELF;

		$var['pre']['text'] = EXTLAN_45;
		$var['pre']['link'] = e_SELF."?pre";

		$var['editext']['text'] = EXTLAN_81;
		$var['editext']['link'] = e_SELF."?editext";

		$var['cat']['text'] = EXTLAN_35;
		$var['cat']['link'] = e_SELF."?cat";



		show_admin_menu(EXTLAN_9, $action, $var);
	}






	function make_delimited($var)
	{
		global $tp;
		foreach($var as $k => $v)
		{
			$var[$k] = $tp->toDB(trim($v));
			$var[$k] = str_replace(",", "[E_COMMA]", $var[$k]);
			if($var[$k] == "")
			{
				unset($var[$k]);
			}
		}
		$ret = implode(",", $var);
		return $ret;
	}


	function show_predefined()
	{
		global $ue;
		$frm = e107::getForm();
		$ns = e107::getRender();
		$tp = e107::getParser();
		$sql = e107::getDb();


		// Get list of current extended fields
		$curList = $ue->user_extended_get_fieldlist();
		foreach($curList as $c)
		{
			$curNames[] = $c['user_extended_struct_name'];
		}

		//Get list of predefined fields.
		$preList = $ue->parse_extended_xml('getfile');
		ksort($preList);

		$txt = "
		<form method='post' action='".e_SELF."?pre'>
	    <table class='table adminlist'>
		<colgroup>
			<col  />
			<col  />
			<col  />
	        <col  />
			<col  />
			<col  />
		</colgroup>
	    <thead>
			<tr>
			<th>".UE_LAN_21."</th>
			<th>".EXTLAN_79."</th>
			<th>".EXTLAN_2."</th>
			<th>".UE_LAN_22."</th>
			<th class='center' >".EXTLAN_57."</th>
			<th class='center last' >".LAN_OPTIONS."</th>
			</tr>
			</thead>
			<tbody>";

	   	foreach($preList as $k=>$a)
		{
			if($k !='version') // don't know why this is appearing in the array.
			{
		   		$active = (in_array($a['name'], $curNames)) ? TRUE : FALSE;
				$txt .= $this->show_predefined_field($a,$active);
			}
		}

		$txt .= "</tbody></table></form>";

		$emessage = e107::getMessage();

		$ns->tablerender(EXTLAN_9.SEP.EXTLAN_56,$emessage->render(). $txt);

	}


	function show_predefined_field($var, $active)
	{
		global $tp,$ue, $frm;
		static $head_shown;
		$txt = "";


		foreach($var as $key=>$val) // convert predefined xml to default array format
		{
	    	$var['user_extended_struct_'.$key] = $val;
		}

		$var['user_extended_struct_type'] = $ue->typeArray[$var['user_extended_struct_type']];
		$var['user_extended_struct_parms'] = $var['include_text'];

		$txt .= "
		<tr>
		<td>{$var['user_extended_struct_name']}</td>
		<td>".constant(strtoupper($var['user_extended_struct_text'])."_DESC")."</td>
		<td>".$ue->user_extended_edit($var,$uVal)."</td>
       	<td>".$tp->toHTML($var['type'], false, 'defs')."</td>
		<td class='center'>".($active ? ADMIN_TRUE_ICON : "&nbsp;")."</td>
		";
	//	$txt .= constant("UE_LAN_".strtoupper($var['text'])."DESC")."<br />";
	//	foreach($showlist as $f)
	//	{
	//		if($var[$f] != "" && $f != 'type' && $f !='text')
	//		{
	//			$txt .= "<strong>{$f}: </strong>".$tp->toHTML($var[$f], false, 'defs')."<br />";
	//		}
	//	}
		$val = (!$active) ? EXTLAN_59 : EXTLAN_60;
		$type = (!$active) ? 'activate' : 'deactivate';
		$style = (!$active) ? 'other' : 'delete';

		$txt .= "
		<td class='center last'>";
        $txt .= $frm->admin_button($type."[".$var['user_extended_struct_name']."]", $val, $style );
		$txt .= "</td>
		</tr>";
		return $txt;
	}


	function field_activate()
	{
		global $ue, $ns, $tp, $admin_log;
		$ret = "";
		$preList = $ue->parse_extended_xml('getfile');
		$tmp = $preList;

		foreach(array_keys($_POST['activate']) as $f)
		{

			$tmp[$f]['parms'] = $tp->toDB($tmp[$f]['parms']);
			if($ue->user_extended_add($tmp[$f]))
			{
				$ret .= EXTLAN_68." $f ".EXTLAN_69."<br />";

				if ($tmp[$f]['type']=="db field")
				{
					if (is_readable(e_CORE.'sql/extended_'.$f.'.php'))
					{
	             	$ret .= ($this->process_sql($f)) ? LAN_CREATED." user_extended_{$f}<br />" : LAN_CREATED_FAILED." user_extended_{$f}<br />";
				}
					else
					{
						$ret .= str_replace('[x]',e_CORE.'sql/extended_'.$f.'.php',EXTLAN_78);
					}
				}
			}
			else
			{
				$ret .= EXTLAN_70." $f ".EXTLAN_71."<br />";
			}
		}
		e107::getLog()->add('EUF_11',implode(', ',$_POST['activate']),E_LOG_INFORMATIVE,'');
		return $ret;
	}


	function field_deactivate()
	{

		$tp = e107::getParser();
		$sql = e107::getDb();
		$ue = e107::getUserExt();

		$ret = "";
		foreach(array_keys($_POST['deactivate']) as $f)
		{
			$f = $tp->filter($f);

			if($ue->user_extended_remove($f, $f))
			{
				$ret .= EXTLAN_68." $f ".EXTLAN_72."<br />";
				if(is_readable(e_CORE."sql/extended_".$f.".php"))
				{
	             	$ret .= ($sql->gen("DROP TABLE ".MPREFIX."user_extended_".$f)) ? LAN_DELETED." user_extended_".$f."<br />" : LAN_DELETED_FAILED." user_extended_".$f."<br />";
				}
			}
			else
			{
				$ret .= EXTLAN_70." $f ".EXTLAN_73."<br />";
			}
		}
		e107::getLog()->add('EUF_12',implode(', ',$_POST['deactivate']),E_LOG_INFORMATIVE,'');

		return $ret;
	}


	function process_sql($f)
	{
	    global $sql;
		$filename = e_CORE."sql/extended_".$f.".php";
		$fd = fopen ($filename, "r");
		$sql_data = fread($fd, filesize($filename));
		fclose ($fd);

		$search[0] = "CREATE TABLE ";	$replace[0] = "CREATE TABLE ".MPREFIX;
		$search[1] = "INSERT INTO ";	$replace[1] = "INSERT INTO ".MPREFIX;

	    preg_match_all("/create(.*?)myisam;/si", $sql_data, $creation);
	    foreach($creation[0] as $tab){
			$query = str_replace($search,$replace,$tab);
	      	if(!$sql->gen($query)){
	        	$error = TRUE;
			}
		}

	    preg_match_all("/insert(.*?);/si", $sql_data, $inserts);
		foreach($inserts[0] as $ins){
			$qry = str_replace($search,$replace,$ins);
			if(!$sql->gen($qry)){
			  	$error = TRUE;
			}
	    }

		return ($error) ? FALSE : TRUE;

	}
}// end class



	function users_extended_adminmenu() {
		global $user, $action, $ns, $curtype, $action;
		// $user->show_options($action);
		$ac = e_QUERY;
		$action = vartrue($ac,'main');

		users_ext::show_options($action);
		if($action == 'editext' || $action == 'continue')
		{
			$ns->tablerender(EXTLAN_46." - <span id='ue_type'>&nbsp;</span>", "<div id='ue_help'>&nbsp;</div>");
			echo "<script type='text/javascript'>changeHelp('{$curtype}');</script>";
		}
	}


?>
