<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once(__DIR__.'/../class2.php');

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
					$sub_action = varset($tmp[1]);
					$sub_action = $tp->filter($sub_action);
					$id = varset($tmp[2], 0);
					unset($tmp);
				}

				if($sql->select('user_extended_struct', '*', "user_extended_struct_id = '$sub_action'"))
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
					$selected = (varset($_POST['table_db']) == $fld || $curVals[0] == $fld) ? " selected='selected'" : "";
					$text .= "<option value=\"" . $fld . "\" $selected>" . $fld . "</option>\n";
				}
				$text .= "</select></td></tr>";

				if($_POST['table_db'] || $curVals[0])
				{
					// Field ID.
					$text .= "<tr><td>" . EXTLAN_63 . "</td><td>";
					$text .= "<select style='width:99%' class='tbox e-select' name='field_id'>";
					$text .= "<option value='' class='caption'>" . LAN_NONE . "</option>";
					$table_list = !empty($_POST['table_db']) ? $_POST['table_db'] : $curVals[0];
					if($sql->gen("DESCRIBE " . MPREFIX . $table_list))
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
					$table_list = !empty($_POST['table_db']) ? $_POST['table_db'] : $curVals[0];
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
					$table_list = !empty($_POST['table_db']) ? $_POST['table_db'] : $curVals[0];
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
/*
if (isset($_POST['cancel']))
{
	header('location:'.e_SELF);
	exit;
}

if (isset($_POST['cancel_cat']))
{
	header("location:".e_SELF."?cat");
	exit;
}*/

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
			'main/add'		=> array('caption'=>  EXTLAN_45, 'perm' => '0|4', 'icon'=>'fa-plus'),
			'main/create'		=> array('caption'=> EXTLAN_81, 'perm' => '0|4', 'icon'=>'fa-user-edit'),
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
		protected $sortField		= 'user_extended_struct_order';
		protected $orderStep		= 10;
		protected $tabs				= array(LAN_BASIC,LAN_ADVANCED); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

		protected $listQry      	= "SELECT * FROM `#user_extended_struct` WHERE user_extended_struct_type != 0 AND user_extended_struct_text != '_system_'  "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= 'user_extended_struct_order ASC';

		protected $fields 		= array (
		    'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		    'user_extended_struct_id' =>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		    'user_extended_struct_name' =>   array ( 'title' => LAN_NAME, 'type' => 'method', 'data' => 'str', 'readonly'=>true, 'width' => '350px', 'help' => '', 'readParms' => '', 'writeParms' => array('tdClassRight' => 'form-inline', 'pre' => 'user_ ', 'required'=>true), 'class' => 'left', 'thclass' => 'left',  ),
		    'user_extended_struct_text' =>   array ( 'title' => EXTLAN_79, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => 'constant=1', 'writeParms' => array('required'=>true), 'class' => 'left', 'thclass' => 'left',  ),
			'user_extended_struct_type' =>   array ( 'title' => LAN_PREVIEW, 'type' => 'method', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
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

			$row = e107::getDb()->retrieve('user_extended_struct', 'user_extended_struct_type,user_extended_struct_parms',"user_extended_struct_id = ".intval($_GET['id']));

			$parms = $row['user_extended_struct_parms'];
			$tmp = explode('^,^', $parms);

			$this->fields['field_include']['writeParms']['default']     =  $tmp[0];
			$this->fields['field_regex']['writeParms']['default']       =  $tmp[1];
			$this->fields['field_regexfail']['writeParms']['default']   =  $tmp[2];
			$this->fields['field_userhide']['writeParms']['default']    =  $tmp[3];
			$this->fields['field_placeholder']['writeParms']['default'] =  $tmp[4];
			$this->fields['field_helptip']['writeParms']['default']     =  $tmp[5];

			if((int) $row['user_extended_struct_type'] === 12) // EUF_ADDON
			{
				unset(
				$this->fields['field_include'],
				$this->fields['field_regex'],
				$this->fields['field_regexfail'],
				$this->fields['field_userhide'],
				$this->fields['field_placeholder'],
				$this->fields['field_helptip'],
				$this->fields['user_extended_struct_default']
				);
			}


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

/*
		public function afterDelete($data,$id)
		{


		}*/


		// ------- Customize Update --------

		public function beforeUpdate($new_data, $old_data, $id)
		{

			$ue = e107::getUserExt();
			$mes = e107::getMessage();

			if ($ue->user_extended_field_exist($new_data['user_extended_struct_name']))
			{
				$type = isset($new_data['user_extended_struct_type']) ? $new_data['user_extended_struct_type'] : $old_data['user_extended_struct_type'];
				$field_info = $ue->user_extended_type_text($type, $new_data['user_extended_struct_default']);

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
			if(empty($_POST['activate']))
			{
				return null;
			}

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
						if (!is_readable(e_CORE.'sql/extended_'.$f.'.php'))
						{
							$ret .= str_replace('[x]',e_CORE.'sql/extended_'.$f.'.php',EXTLAN_78);
		           //     $ret .= ($this->process_sql($f)) ? LAN_CREATED." user_extended_{$f}<br />" : LAN_CREATED_FAILED." user_extended_{$f}<br />";
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

			if(empty($_POST['deactivate']))
			{
				return null;
			}

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
			$curNames = array();
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
				if($k !== 'version') // don't know why this is appearing in the array.
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
			<td>".$ue->user_extended_edit($var,'')."</td>
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
					return e107::getUserExt()->renderElement($ext,$ext['user_extended_struct_default']);
				//	reutrn e107::getParser()>toHTML(deftrue($ext['user_extended_struct_text'], $ext['user_extended_struct_text']), FALSE, "defs")
					break;

				case 'write': // Edit Page

					if(empty($curVal))
					{
						$curVal = '1';
					}

					$types = e107::getUserExt()->getFieldTypes();

					if($curVal == EUF_ADDON)
					{
						return LAN_PLUGIN;
					}

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
		function user_extended_struct_name($curVal,$mode, $att)
		{
			switch($mode)
			{
				case 'read': // List Page

					return str_replace('plugin_', "<span class='label label-primary'>".LAN_PLUGIN."</span> ",$curVal);
					break;

				case 'write': // Edit Page
					$field = [];
					$field['type'] = 'text';
					$field['writeParms'] = $att;
					$field['pattern'] = '[0-9a-z_]*';

					return $this->renderElement('user_extended_struct_name', $curVal, $field);
				break;

				case 'filter':
				case 'batch':
					return  array();
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

			$type = (int) varset($current['user_extended_struct_type']);

			if($type === EUF_ADDON)
			{
				return '-';
			}

			$val_hide = ($type !== EUF_DB_FIELD && $type !== EUF_TEXT && $type !== EUF_COUNTRY ) ? "visible" : "none";

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

				$text .= "<div class='checkbox' style='margin-top:10px; margin-bottom:0'>".$frm->checkbox('sort_user_values',1, false, EXTLAN_87)."</div>";
			$text .= "</div>";


			if($this->getController()->getAction() === 'edit' && ($type !== EUF_DB_FIELD))
			{
				return $text;
			}

// End of Values. --------------------------------------




			$db_hide = ($current['user_extended_struct_type'] == EUF_DB_FIELD) ? "block" : "none";

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
				$selected =  (varset($_POST['table_db']) == $fld || $curVals[0] == $fld) ? " selected='selected'" : "";
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
				$table_list = !empty($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;

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
				$table_list = !empty($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;

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
				$table_list = !empty($_POST['table_db']) ? $_POST['table_db'] : $curVals[0] ;

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
/*
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
		}*/



	}









new user_extended_adminArea();
require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();
require_once(e_ADMIN."footer.php");






