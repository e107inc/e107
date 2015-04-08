<?php

// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}


class social_adminarea extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'social_ui',
			'path' 			=> null,
			'ui' 			=> 'social_form_ui',
			'uipath' 		=> null
		),
		

	);	
	
	
	protected $adminMenu = array(

	//	'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		// 'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
			
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),	

		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'social';
}




				
class social_ui extends e_admin_ui
{
			
		protected $pluginTitle		= 'Social';
		protected $pluginName		= 'social';
	//	protected $eventName		= 'social-social'; // remove comment to enable event triggers in admin.
	//	protected $table			= 'social';
	//	protected $pid				= 'interview_id';
		protected $perPage			= 10; 
		protected $batchDelete		= true;
	//	protected $batchCopy		= true;		
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable. 
		
	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected $listOrder		= '';
	
		protected $fields           = array();
		
		protected $fieldpref        = array();
		

		protected $preftabs        = array('Twitter Menu');

		protected $prefs = array(
			'twitter_menu_height'		=> array('title'=> 'Height', 'type'=>'number', 'tab'=>0, 'data' => 'int','help'=>'Height in px'),
			'twitter_menu_limit'		=> array('title'=> 'Limit', 'type'=>'number', 'tab'=>0, 'data' => 'int','help'=>'Number of tweets to display.'),


		);

	
		public function init()
		{


			//	print_a($this->fields);
		}

		
		// ------- Customize Create --------
		
		public function beforeCreate($new_data)
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
		
			
	/*	
		// optional - a custom page.  
		public function customPage()
		{
			$ns = e107::getRender();
			$text = 'Hello World!';
			$ns->tablerender('Hello',$text);	
			
		}
	*/
			
}
				


class social_form_ui extends e_admin_form_ui
{

}		
		
		
new social_adminarea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

?>