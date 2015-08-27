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
	
		'inbox'	=> array(
			'controller' 	=> 'private_msg_ui',
			'path' 			=> null,
			'ui' 			=> 'private_msg_form_ui',
			'uipath' 		=> null
		),
		'outbox'	=> array(
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

		'inbox/list'			=> array('caption'=> "Inbox", 'perm' => 'P'),
		'outbox/list'			=> array('caption'=> "Outbox", 'perm' => 'P'),
		'outbox/create'		=> array('caption'=> "Compose", 'perm' => 'P'),

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
		protected $perPage 			= 7;
        protected $listQry          = '';
        protected $listOrder        = "p.pm_id DESC";
			
		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'pm_id'             => array ( 'title' => LAN_ID,       'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_from'           => array ( 'title' => 'From',       'type' => 'method', 'noedit'=>true, 'data' => 'int', 'filter'=>true, 'width' => '5%%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_to'             => array ( 'title' => 'To',         'type' => 'user', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_sent'           => array ( 'title' => LAN_DATE,     'type' => 'datestamp', 'data' => 'int', 'width' => '15%', 'help' => '', 'readParms' => '', 'writeParms' => 'auto=1&readonly=1', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_subject'        => array ( 'title' => "Subject",    'type' => 'text', 'data' => 'str', 'width' => '15%', 'help' => '', 'readParms' => '', 'writeParms' => array('size'=>'xlarge'), 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_text'           => array ( 'title' => 'Message',    'type' => 'bbarea', 'data' => 'str', 'width' => '40%', 'help' => '', 'readParms' => 'expand=1&truncate=50', 'writeParms' => 'rows=5&size=block&cols=80', 'class' => 'left', 'thclass' => 'left',  ),
		  'pm_read'           => array ( 'title' => 'Read',       'type' => 'boolean', 'noedit'=>1, 'data' => 'int', 'batch'=>true, 'filter'=>true, 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
        
          'pm_sent_del'       => array ( 'title' => 'Del',        'type' => 'boolean', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_read_del'       => array ( 'title' => 'Del',        'type' => 'boolean', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_attachments'    => array ( 'title' => 'Attachments', 'type' => 'text', 'noedit'=>true, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_option'         => array ( 'title' => 'Option',     'type' => 'text', 'noedit'=>true, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'pm_size'           => array ( 'title' => 'Size',       'type' => 'boolean', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'options'           => array ( 'title' => LAN_OPTIONS,    'type' => 'method', 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		
		
		protected $fieldpref = array('pm_id', 'pm_from', 'pm_to', 'pm_sent', 'pm_read', 'pm_subject', 'pm_text');
		

        public function init()
        {
          //  $this->listQry = "SELECT p.*,u.user_name FROM #private_msg AS p LEFT JOIN #user AS u ON p.pm_from = u.user_id  ";

			if($this->getMode() == 'inbox')
			{
                 $this->listQry = 'SELECT  p.*, u.user_name, f.user_name AS fromuser FROM #private_msg AS p LEFT JOIN  #user AS u ON u.user_id = p.pm_to
					LEFT JOIN #user as f on f.user_id = p.pm_from WHERE p.pm_to = '.USERID;
				$this->fields['pm_to']['nolist'] = true;
				$this->fields['options']['readParms'] = 'editClass='.e_UC_NOBODY;
			}

			if($this->getMode() == 'outbox')
			{
				$this->listQry = 'SELECT  p.*, u.user_name, f.user_name AS fromuser FROM #private_msg AS p LEFT JOIN  #user AS u ON u.user_id = p.pm_to
					LEFT JOIN #user as f on f.user_id = p.pm_from WHERE p.pm_from = '.USERID;
				$this->fields['pm_from']['nolist'] = true;
				$this->fields['options']['readParms'] = 'editClass='.e_UC_NOBODY;
			}

			if($this->getAction() == 'create')
			{
				$this->fields['pm_to']['writeParms']['default'] = 99999999;
				$this->fields['pm_to']['writeParms']['required'] = 1;
				$this->fields['pm_subject']['writeParms']['required'] = 1;

	            if(!empty($_GET['to']))
	            {
	                $this->fields['pm_to']['writeParms']['default'] = intval($_GET['to']);
	                $this->addTitle('Reply');
	            }

				if(!empty($_GET['subject']))
				{
					$this->fields['pm_subject']['writeParms']['default'] = "Re: ". base64_decode($_GET['subject']);
				}


			}


            if(vartrue($_GET['iframe']))
            {
                define('e_IFRAME', true);   
            }
     
 		


        
        }

		public function beforeCreate($new_data)
		{

			if(empty($new_data['pm_to']))
			{
				e107::getMessage()->addError('Please enter a recipient in the "To" field.');
				return false;
			}

			$new_data['pm_size'] = strlen($new_data['pm_text']);
			$new_data['pm_from'] = USERID;
			return $new_data;
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

	function options($parms, $value, $id, $attributes)
	{

	//	return $this->renderValue('options',$value,$att,$id);;
		$tp = e107::getParser();
		$mode = $this->getController()->getMode();

		if($mode == 'inbox')
		{
			$text = "";
			$pmData = $this->getController()->getListModel()->getData();

			if($pmData['pm_from'] != USERID)
			{
				$link = e_SELF."?";
				$link .= (!empty($_GET['iframe'])) ? 'mode=inbox&iframe=1' : 'mode=outbox';


				$link .= "&action=create&to=".intval($pmData['pm_from'])."&subject=".base64_encode($pmData['pm_subject']);



				$text .= "<a href='".$link."' class='btn' title='Reply'>".$tp->toGlyph('fa-reply', array('size'=>'1x'))."</a>";
			}

		//	$text .= $this->renderValue('options',$value,$attr,$id);

			return $text;
		}
	}

	function pm_from($curVal, $mode)
	{

		if($mode == 'read')
		{
			$pmData = $this->getController()->getListModel()->getData();
		}

		return $pmData['fromuser'];
	}
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