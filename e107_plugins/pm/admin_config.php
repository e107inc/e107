<?php

// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}



class pm_admin extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'private_msg_ui',
			'path' 			=> null,
			'ui' 			=> 'private_msg_form_ui',
			'uipath' 		=> null
		),
    /*
		'block'	=> array(
			'controller' 	=> 'private_msg_block_ui',
			'path' 			=> null,
			'ui' 			=> 'private_msg_block_form_ui',
			'uipath' 		=> null
		),
    */
	);	
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> "Inbox", 'perm' => 'P'),
		'main/create'		=> array('caption'=> "Compose", 'perm' => 'P'),

	//	'block/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
	//	'block/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
			
	/*
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),
		'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	*/	

	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'pm';
}




				
class private_msg_ui extends e_admin_ui
{
			
		protected $pluginTitle		= 'Private Messaging';
		protected $pluginName		= 'pm';
		protected $table			= 'private_msg';
		protected $pid				= 'pm_id';
		protected $perPage 			= 5; 
        protected $listQry          = 'SELECT  p.*, u.user_name FROM #private_msg AS p LEFT JOIN  #user AS u ON u.user_id = p.pm_from ';
        protected $listOrder        = "pm_sent DESC";
			
		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'pm_id'             => array ( 'title' => LAN_ID,       'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_from'           => array ( 'title' => 'From',       'type' => 'user', 'noedit'=>true, 'data' => 'int', 'filter'=>true, 'width' => '5%%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_to'             => array ( 'title' => 'To',         'type' => 'user', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_sent'           => array ( 'title' => LAN_DATE,     'type' => 'datestamp', 'data' => 'int', 'width' => '15%', 'help' => '', 'readParms' => '', 'writeParms' => 'auto=1&readonly=1', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_subject'        => array ( 'title' => "Subject",    'type' => 'text', 'data' => 'str', 'width' => '15%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_text'           => array ( 'title' => 'Message',    'type' => 'bbarea', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => 'expand=1&truncate=50', 'writeParms' => 'size=medium', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_read'           => array ( 'title' => 'Read',       'type' => 'boolean', 'noedit'=>1, 'data' => 'int', 'batch'=>true, 'filter'=>true, 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
        
          'pm_sent_del'       => array ( 'title' => 'Del',        'type' => 'boolean', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_read_del'       => array ( 'title' => 'Del',        'type' => 'boolean', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_attachments'    => array ( 'title' => 'Attachments', 'type' => 'text', 'noedit'=>true, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_option'         => array ( 'title' => 'Option',     'type' => 'text', 'noedit'=>true, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_size'           => array ( 'title' => 'Size',       'type' => 'boolean', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'options'           => array ( 'title' => 'Options',    'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('pm_id', 'pm_from', 'pm_to', 'pm_sent', 'pm_read', 'pm_subject', 'pm_text');
		
        // optional
        public function init()
        {
          //  $this->listQry = "SELECT p.*,u.user_name FROM #private_msg AS p LEFT JOIN #user AS u ON p.pm_from = u.user_id  ";  
            
            if(vartrue($_GET['iframe']))
            {
                define('e_IFRAME', true);   
            }
     
 		
            // Prevent snooping of other people's messages. ;-) //XXX Not working Yet. FIXME!
            if(varset($_GET['filter_options'])) 
            {
                list($tmp,$field,$id) = explode("__",$_GET['filter_options']);
			
				if($field == 'pm_to') // Inbox 
				{
				
					$this->listQry		= "SELECT  p.*, u.user_name FROM #private_msg AS p LEFT JOIN  #user AS u ON u.user_id = p.pm_from "; 

					$this->fields['pm_to']['nolist'] = true; 
					$this->fields['options']['readParms'] = 'editClass='.e_UC_NOBODY;
						
				}
				
				if($field == 'pm_from') // Outbox 
				{
					$this->listQry		= "SELECT  p.*, u.user_name FROM #private_msg AS p LEFT JOIN  #user AS u ON u.user_id = p.pm_to "; 
					$this->fields['pm_from']['nolist'] = true; 
					$this->fields['options']['readParms'] = 'editClass='.e_UC_NOBODY;
				}
					
             //   echo "FIELD = ".$field;
              //  $this->getDispatcher()->setRequest('filter_options')
                
                if($field == 'pm_to' && $id != USERID)
                {
                    $_GET['filter_options'] = 'batch__pm_to__'.USERID;    
                }
           
                if($field == 'pm_from' && $id != USERID)
                {
                		echo "<h3>HI THERE</h3>";
                	
                    $_GET['filter_options'] = 'batch__pm_from__'.USERID;    
              
                }     
    
            }

        
        }		
		
		/*
		protected  = array(
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text', 'data' => 'string', 'validate' => true),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean', 'data' => 'integer'),
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text', 'data' => 'string', 'validate' => 'regex', 'rule' => '#^[\w]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')
		);

		

	
		
		public function customPage()
		{
			$ns = e107::getRender();
			$text = 'Hello World!';
			$ns->tablerender('Hello',$text);	
			
		}
		*/
			
}
				


class private_msg_form_ui extends e_admin_form_ui
{

}		
		
/*

				
class private_msg_block_ui extends e_admin_ui
{
			
		protected $pluginTitle		= 'Private Messaging';
		protected $pluginName		= 'pm';
		protected $table			= 'private_msg_block';
		protected $pid				= 'pm_block_id';
		protected $perPage 			= 10; 
			
		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'pm_block_id' =>   array ( 'title' => 'LAN_ID', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_block_from' =>   array ( 'title' => 'From', 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_block_to' =>   array ( 'title' => 'To', 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_block_datestamp' =>   array ( 'title' => 'LAN_DATESTAMP', 'type' => 'datestamp', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_block_count' =>   array ( 'title' => 'Count', 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'options' =>   array ( 'title' => 'Options', 'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('pm_block_datestamp');
		
		
		

	//	protected  = array(
	//		'pref_type'	   				=> array('title'=> 'type', 'type'=>'text', 'data' => 'string', 'validate' => true),
	//		'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean', 'data' => 'integer'),
	//		'pref_name' 				=> array('title'=> 'name', 'type' => 'text', 'data' => 'string', 'validate' => 'regex', 'rule' => '#^[\w]+$#i', 'help' => 'allowed characters are a-zA-Z and underscore')
	//	);

		
		// optional
		public function init()
		{
			
		}
	
		
		public function customPage()
		{
			$ns = e107::getRender();
			$text = 'Hello World!';
			$ns->tablerender('Hello',$text);	
			
		}
		
			
}
				


class private_msg_block_form_ui extends e_admin_form_ui
{

}		
	*/
	
		
new pm_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

?>