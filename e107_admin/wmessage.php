<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("../class2.php");
if (!getperms("M")) 
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('wmessage', true);


class wmessage_admin extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'generic_ui',
			'path' 			=> null,
			'ui' 			=> 'generic_form_ui',
			'uipath' 		=> null
		),
		

	);	
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),	

		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = WMLAN_00;

	protected $adminMenuIcon = 'e-welcome-24';
}




				
class generic_ui extends e_admin_ui
{
			
		protected $pluginTitle		= WMLAN_00;
		protected $pluginName		= 'core';
		protected $eventName		= 'wmessage';
		protected $table			= 'generic';
		protected $pid				= 'gen_id';
		protected $perPage			= 10; 
		protected $batchDelete		= true;
		protected $batchCopy		= true;		
		
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs			= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable. 
		
		protected $listQry      	= "SELECT * FROM `#generic` WHERE gen_type='wmessage'  "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected $listOrder		= 'gen_id DESC';
	
		protected $fields		= array (  
		  'checkboxes'		=>   array ( 'title' => '', 			'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'gen_id'			=>   array ( 'title' => LAN_ID, 		'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_type'		=>   array ( 'title' => LAN_TYPE, 		'type' => 'hidden', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => 'default=wmessage', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_datestamp' 	=>   array ( 'title' => LAN_DATESTAMP, 	'type' => 'hidden', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_user_id'		=>   array ( 'title' => LAN_AUTHOR,		'type' => 'hidden', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_ip'			=>   array ( 'title' => LAN_TITLE,		'type' => 'text', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => 'size=xxlarge', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_intdata'		=>   array ( 'title' => LAN_VISIBILITY,	'type' => 'userclass', 'data' => 'int', 'inline'=>true, 'batch'=>true, 'filter'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_chardata'	=>   array ( 'title' => LAN_MESSAGE, 	'type' => 'bbarea', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'options' 		=>   array ( 'title' => LAN_OPTIONS, 	'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('gen_ip', 'gen_intdata');
		
		
		protected $prefs = array(
			'wm_enclose'		=> array('title'=> WMLAN_05, 'type'=>'radio', 'data' => 'int','help'=> WMLAN_06, 'writeParms'=>array('optArray'=>array(0=> LAN_DISABLED, 1=> LAN_ENABLED, 2=> WMLAN_11))),
		);

	
		public function init()
		{

	
		}
	
		public function beforeCreate($new_data)
		{
			return $new_data;
		}
	
		public function afterCreate($new_data, $old_data, $id)
		{
			// do something
		}

		public function beforeUpdate($new_data, $old_data, $id)
		{
			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			e107::getCache()->clear("wmessage");
		}
		
		public function onCreateError($new_data, $old_data)
		{
			// do something		
		}

		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something		
		}
		
		
	/*	
		// optional - override edit page. 
		public function customPage()
		{
			$ns = e107::getRender();
			$text = 'Hello World!';
			$ns->tablerender('Hello',$text);	
			
		}
	*/
			
}
				


class generic_form_ui extends e_admin_form_ui
{

}		
		
		
new wmessage_admin();

require_once(e_ADMIN."auth.php");

e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;












/*










require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER."ren_help.php");

$frm = e107::getForm();
$mes = e107::getMessage();

vartrue($action) == '';
if (e_QUERY) 
{
	$tmp = explode('.', e_QUERY);
	$action = $tmp[0];
	$sub_action = varset($tmp[1], '');
	$id = varset($tmp[2], 0);
	unset($tmp);
}

if($_POST)
{
	$e107cache->clear("wmessage");
}

if (isset($_POST['wm_update'])) 
{
	$data 		= $_POST['data']; // $tp->toDB($_POST['data']) causes issues with ':'
	$wm_title 	= $tp->toDB($_POST['wm_caption']);
	$wmId 		= intval($_POST['wm_id']);
	
	$updateArray = array(
		'gen_chardata'	=> $data,
		'gen_ip'		=> $wm_title,
		'gen_intdata'	=>  $_POST['wm_active'],
		'WHERE'			=> "gen_id=".$wmId
	); 
	
	//$message = ($sql->db_Update("generic", "gen_chardata ='{$data}',gen_ip ='{$wm_title}', gen_intdata='".$_POST['wm_active']."' WHERE gen_id=".$wmId." ")) ? LAN_UPDATED : LAN_UPDATED_FAILED;
	// if ($sql->update("generic", "gen_chardata ='{$data}',gen_ip ='{$wm_title}', gen_intdata='".$_POST['wm_active']."' WHERE gen_id=".$wmId." "))
	
	if ($sql->update("generic", $updateArray))
	{
		$mes->addSuccess(LAN_UPDATED);
		welcome_adminlog('02', $wmId, $wm_title);
	}
	else 
	{
		$mes->addError(LAN_UPDATED_FAILED); 
	}
}

if (isset($_POST['wm_insert'])) 
{
	$wmtext 	= $tp->toDB($_POST['data']);
	$wmtitle 	= $tp->toDB($_POST['wm_caption']);
	welcome_adminlog('01', 0, $wmtitle);
		
	//$message = ($sql->db_Insert("generic", "0, 'wmessage', '".time()."', ".USERID.", '{$wmtitle}', '{$_POST['wm_active']}', '{$wmtext}' ")) ? LAN_CREATED :  LAN_CREATED_FAILED ;
	if ($sql->db_Insert("generic", "0, 'wmessage', '".time()."', ".USERID.", '{$wmtitle}', '{$_POST['wm_active']}', '{$wmtext}' "))
	{
		$mes->addSuccess(LAN_CREATED);
	}
	else
	{
		$mes->addError(LAN_CREATED_FAILED); 
	}
}

if (isset($_POST['updateoptions'])) 
{
	$changed = FALSE;
	foreach (array('wm_enclose','wmessage_sc') as $opt)
	{
		$temp = intval($_POST[$opt]);
		if ($temp != $pref[$opt])
		{
			$pref[$opt] = $temp;
			$changed = TRUE;
		}
	}
	if ($changed)
	{
		save_prefs();
		welcome_adminlog('04', 0, $pref['wm_enclose'].', '.$pref['wmessage_sc']);
	}
	else 
	{
		$mes->addInfo(LAN_NOCHANGE_NOTSAVED);
	}
}

if (isset($_POST['main_delete'])) 
{
	$del_id = array_keys($_POST['main_delete']);
	welcome_adminlog('03', $wmId, '');
	if ($sql->delete("generic", "gen_id='".$del_id[0]."' "))
	{
		$mes->addSuccess(LAN_DELETED);
	}
	else 
	{
		$mes->addError(LAN_DELETED_FAILED); 
	}
}

echo $mes->render();


// Show Existing -------
if ($action == "main" || $action == "") 
{
	if ($wm_total = $sql->select("generic", "*", "gen_type='wmessage' ORDER BY gen_id ASC")) 
	{
		$wmList = $sql->db_getList();
		$text = $frm->open('myform_wmessage','post',e_SELF);
		$text .= "
            <table class='table adminlist'>
			<colgroup>
				<col style='width:5%' />
				<col style='width:70%' />
				<col style='width:10%' />
				<col style='width:10%' />
   			</colgroup>
			<thead>
			<tr>
				<th>".LAN_ID."</th>
				<th>".LAN_MESSAGE."</th>
				<th class='center'>".LAN_VISIBILITY."</th>
				<th class='center'>".LAN_OPTIONS."</th>
			</tr>
			</thead>
			<tbody>";

		foreach($wmList as $row) 
		{
			$text .= "
			<tr>
				<td class='center' style='text-align: center; vertical-align: middle'>".$row['gen_id']."</td>
				<td>".strip_tags($tp->toHTML($row['gen_ip']))."</td>
				<td>".r_userclass_name($row['gen_intdata'])."</td>
            	<td class='center nowrap'>
            		<a class='btn btn-large' href='".e_SELF."?create.edit.{$row['gen_id']}'>".ADMIN_EDIT_ICON."</a>
            		<button class='btn btn-large action delete' type='submit' title='".LAN_DELETE."' name='main_delete[".$row['gen_id']."]' data-confirm=\"".LAN_CONFIRMDEL." [ID: {$row['gen_id']} ]\" >".ADMIN_DELETE_ICON."</button>
				</td>
			</tr>";
		}

		$text .= "</tbody></table>";
		$text .= $frm->close();
	
	} else {
		$mes->addInfo(WMLAN_09);
	}
	
	$ns->tablerender(WMLAN_00.SEP.LAN_MANAGE, $mes->render() . $text);
}

// Create and Edit
if ($action == "create" || $action == "edit")
{

	if ($sub_action == "edit")
	{
		$sql->select("generic", "gen_intdata, gen_ip, gen_chardata", "gen_id = $id");
		$row = $sql->fetch();
	}

	$text = "
		<form method='post' action='".e_SELF."'  id='wmform'>
		<fieldset id='code-wmessage-create'>
        <table class='table adminform'>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tr>
			<td>".WMLAN_10."</td>
			<td>".$frm->text('wm_caption', $tp->toForm(vartrue($row['gen_ip'])), 80)."</td>
		</tr>
		<tr>
			<td>".LAN_MESSAGE."</td>
			<td>";
			
		$text .= $frm->bbarea('data',$row['gen_chardata']);
		
	//	$text .= "<textarea class='e-wysiwyg tbox' id='data' name='data' cols='70' rows='15' style='width:95%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this)'>".$tp->toForm(vartrue($row['gen_chardata']))."</textarea>";
		
		$text .= "</td>
		</tr>";

	//	$text .= display_help("helpb", "admin"); //XXX Serves as BC Check 

	$text .= "
		<tr>
			<td>".LAN_VISIBILITY."</td>
			<td>".r_userclass("wm_active", vartrue($row['gen_intdata']), "off", "public,guest,nobody,member,admin,classes")."</td>
		</tr>
		</table>

		<div class='buttons-bar center'>";

			if($sub_action == "edit")
			{
		    	$text .= $frm->admin_button('wm_update', LAN_UPDATE, 'update');
			}
			else
			{
		    	$text .= $frm->admin_button('wm_insert', LAN_CREATE, 'create');
			}

	$text .= "<input type='hidden' name='wm_id' value='".$id."' />";
	$text .= "</div>
		</fieldset>
		</form>";
	
	$ns->tablerender(WMLAN_00.SEP.LAN_CREATE, $mes->render() . $text);
}


if ($action == "opt") {
	$pref = e107::getPref();
	$ns = e107::getRender();
	
	$text = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<fieldset id='code-wmessage-options'>
        <table class='table adminform'>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tr>
			<td>".WMLAN_05."</td>
			<td>".$frm->radio_switch('wm_enclose', varset($pref['wm_enclose']))."<span class='field-help'>".WMLAN_06."</span></td>
		</tr>";
	
	//	DEPRECATED - see header_default.php {WMESSAGE}
	// $text .= "
	// 	<tr>
	// 		<td>".WMLAN_07."</td>
	// 		<td>".$frm->checkbox('wmessage_sc', 1, varset($pref['wmessage_sc'],0))."</td>
	// 	</tr>";
	
	
	$text .= "
		</table>

		<div class='buttons-bar center'>
			". $frm->admin_button('updateoptions', LAN_SAVE)."
		</div>
		</fieldset>
		</form>
		";

	$ns->tablerender(WMLAN_00.SEP.LAN_PREFS, $mes->render() . $text);
}

function wmessage_adminmenu() 
{

	$act = e_QUERY;
	$action = vartrue($act,'main');
	
	$var['main']['text'] = LAN_MANAGE;
	$var['main']['link'] = e_SELF;
	$var['create']['text'] = LAN_CREATE;
	$var['create']['link'] = e_SELF."?create";
	$var['opt']['text'] = LAN_PREFS;
	$var['opt']['link'] = e_SELF."?opt";

	show_admin_menu(WMLAN_00, $action, $var);
}

require_once("footer.php");



// Log event to admin log
function welcome_adminlog($msg_num='00', $id=0, $woffle='')
{
  global $pref, $admin_log;
//  if (!varset($pref['admin_log_log']['admin_welcome'],0)) return;
	$msg = '';
	if ($id) $msg = 'ID: '.$id;
	if ($woffle)
	{
		if ($msg) $msg .= '[!br!]';
		$msg .= $woffle;
	}
	e107::getLog()->add('WELCOME_'.$msg_num,$msg,E_LOG_INFORMATIVE,'');
}
 */

?>
