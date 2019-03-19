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
		  'gen_type'		=>   array ( 'title' => LAN_TYPE, 		'type' => 'hidden', 'data' => 'safestr', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => 'default=wmessage', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_datestamp' 	=>   array ( 'title' => LAN_DATESTAMP, 	'type' => 'hidden', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_user_id'		=>   array ( 'title' => LAN_AUTHOR,		'type' => 'hidden', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'gen_ip'			=>   array ( 'title' => LAN_TITLE,		'type' => 'text', 'data' => 'safestr', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => 'size=xxlarge', 'class' => 'left', 'thclass' => 'left',  ),
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
	
		public function beforeCreate($new_data, $old_data)
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






