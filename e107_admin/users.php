<?php
/*
* e107 website system
*
* Copyright (C) 2008-2017 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Administration Area - Users
*
*/

if(!empty($_POST) && !isset($_POST['e-token']))
{
	$_POST['e-token'] = ''; // make sure e-token hasn't been deliberately removed.
}

if (!defined('e107_INIT'))
{
	require_once("../class2.php");
}

if (!getperms('4|U0|U1|U2|U3'))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('user');
e107::coreLan('users', true);
e107::coreLan('date');

// TODO List of permissions to be implemented
// "options" - getperms('4|U2')
// "create" - getperms('4|U1|U0')
// "ranks" - getperms('4|U3')
// "default" - getperms('4|U1|U0')

e107::css('inline', "

 .label-status, .label-password { width:100%; display:block; padding-bottom:5px; padding-top:5px }
");

class users_admin extends e_admin_dispatcher
{
	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'users_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'users_admin_form_ui',
			'uipath' 		=> null,
			//'perm'			=> '0',
		),
		'ranks'		=> array(
			'controller' 	=> 'users_ranks_ui',
			'path' 			=> null,
			'ui' 			=> 'users_ranks_ui_form',
			'uipath' 		=> null,
			//'perm'			=> '0',
		)
	);	


	protected $adminMenu = array(
		'main/list'		=> array('caption'=> LAN_MANAGE, 'perm' => '0|4'),
		'main/add' 		=> array('caption'=> LAN_USER_QUICKADD, 'perm' => '4|U0|U1'),
		'main/prefs' 	=> array('caption'=> LAN_OPTIONS, 'perm' => '4|U2'),
		'ranks/list'	=> array('caption'=> LAN_USER_RANKS, 'perm' => '4|U3'),
		'main/maintenance'  => array('caption'=> LAN_MAINTENANCE, 'perm' => '4')
	//	'ranks/list'	=> array('caption'=> LAN_USER_RANKS, 'perm' => '4|U3')
	);
	
	/*
		FIXME - move user prune to Schedule tasks (cron)
		$var ['prune']['text'] = LAN_USER_PRUNE;
		$var ['prune']['link'] = e_ADMIN.'users.php?action=prune';// Will be moved to "Schedule tasks"
		$var ['prune']['perm'] = '4';

*/
	protected $adminMenuAliases = array(
		'main/edit'		=> 'main/list',
		'main/admin'	=> 'main/list',
		'main/userclass'=> 'main/list',					
		'main/test'		=> 'main/list',					
	);	
	
	protected $menuTitle = ADLAN_36;  // 'Users'

	protected $adminMenuIcon = 'e-users-24';


	function init()
	{

		$JS = <<<JS

			//	$('#user-action-indicator-'+user).html('<i class="fa fa-cog"></i>'); //

			$(document).on('click', ".user-action", function(e){
				// e.stopPropagation();

				var action = $(this).attr('data-action-type');
				var user = $(this).attr('data-action-user');

			//	$('#user-action-indicator-'+user).html('<i class="fa fa-spin fa-spinner"></i>'); //

				$('.user-action-hidden').val(''); // clear all, incase of back-button or auto-fill.
				$('#user-action-'+ user).val(action);
				$('#core-user-list-form').submit();


				});
JS;

		e107::js('footer-inline', $JS);
		e107::css('inline', '
			.user-action { cursor: pointer }
			.btn-user-action { margin-right:15px}

		');

	}
	
	/**
	 * Run observers/headers override
	 *
	 * @return users_admin
	 */
	public function runObservers($run_header = true)
	{
		// Catch useraction
		if (isset($_POST['useraction']))
		{
			if(is_array($_POST['useraction']))
			{
				foreach ($_POST['useraction'] as $key => $val)
				{
					if ($val)
					{
						$_POST['useraction'] = $val;
						$_POST['userip'] = $_POST['userip'][$key];
						$_POST['userid'] = (int) $key;
						break;
					}
				}
			}
			
			// FIXME IMPORTANT - permissions per action/trigger
			
			// map useraction to UI trigger
			switch ($_POST['useraction']) 
			{
				### etrigger_delete
				case 'deluser':
					if($_POST['userid'])
					{
						$id = (int) $_POST['userid'];
						$_POST['etrigger_delete'] = array($id => $id);
						$user = e107::getDb()->retrieve('user', 'user_email, user_name', 'user_id='.$id);
						$rplc_from = array('[x]', '[y]', '[z]');
						$rplc_to = array($user['user_name'], $user['user_email'], $id);
						$message = str_replace($rplc_from, $rplc_to, USRLAN_222);
						$this->getController()->deleteConfirmMessage = $message;
					}
				break;
				
				// map to List{USERACTION}Trigger()
				case 'unban':
				case 'ban':
				case 'verify':
				case 'reqverify':
				case 'resend':
				case 'loginas':
				case 'unadmin':
					$_POST['etrigger_'.$_POST['useraction']] = intval($_POST['userid']);
				break;


				case 'logoutas':
					$this->getRequest()
						->setQuery(array())
						->setMode('main')
						->setAction('logoutas');
				break;
				
				// redirect to AdminObserver/AdminPage()
				case 'admin':
				case 'adminperms':
					$this->getRequest()
						->setQuery(array())
						->setMode('main')
						->setAction('admin')
						->setId($_POST['userid']);
						
					$this->getController()->redirect();
				break;
				
				// redirect to UserclassObserver/UserclassPage()
				case 'userclass':
					$this->getRequest()
						->setQuery(array())
						->setMode('main')
						->setAction('userclass')
						->setId($_POST['userid']);
						
					$this->getController()->redirect();
				break;
				
				// redirect to TestObserver/TestPage
				case 'test':
					$this->getRequest()
						->setQuery(array())
						->setMode('main')
						->setAction('test')
						->setId($_POST['userid']);
						
					$this->getController()->redirect();
				break;
				
				// redirect to TestObserver/TestPage
				case 'usersettings':
					$this->getRequest()
						->setQuery(array())
						->setMode('main')
						->setAction('edit')
						->setId($_POST['userid']);
					$this->getController()->redirect();
					
					
					//XXX Broken to the point of being unusable. //header('location:'.e107::getUrl()->create('user/profile/edit', 'id='.(int) $_POST['userid'], 'full=1&encode=0'));
					// exit;
				break;
			}
			
		}
		
		return parent::runObservers($run_header);
	}
}


class users_admin_ui extends e_admin_ui
{
		
	protected $pluginTitle = ADLAN_36;
	protected $pluginName = 'core';
	protected $eventName = 'user';
	protected $table = "user";
		
//	protected $listQry = "SELECT SQL_CALC_FOUND_ROWS * FROM #users"; // without any Order or Limit. 
	protected $listQry = "SELECT SQL_CALC_FOUND_ROWS u.*,ue.* from #user AS u LEFT JOIN #user_extended AS ue ON u.user_id = ue.user_extended_id  "; // without any Order or Limit.
			
	protected $editQry = "SELECT u.*,ue.* FROM #user AS u left join #user_extended AS ue ON u.user_id = ue.user_extended_id  WHERE user_id = {ID}";
	
	protected $pid 			= "user_id";
	protected $perPage 		= 10;
	protected $batchDelete 	= true;
	protected $batchExport	= true; 
	protected $listOrder 	= 'user_id DESC'; 
	
	/**
	 * Show confirm screen before (batch/single) delete
	 * @var boolean
	 */
	public $deleteConfirmScreen = true;
	
	/**
	 * @var boolean
	 */
	protected $batchCopy = false;
	
	/**
	 * List (numerical array) of only disallowed for this controller actions
	 */
	protected $disallow = array('create');

	protected $tabs		= array(LAN_BASIC, LAN_EXTENDED);

	  protected $url          = array(
    	'route'=>'user/profile/view',
    	'name' => 'user_name',
    	'description' => 'user_name',
    	'vars'=> array('user_id' => true, 'user_name' => true)
	);
	
	//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
	protected $fields = array(
		'checkboxes'		=> array('title'=> '',				'type' => null, 'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
	
		'user_id' 			=> array('title' => LAN_ID,			'tab'=>0, 'type' =>'text',	'data'=>'int',	'width' => '5%','forced' => true, 'readParms'=>'link=sef&target=blank'),
//		'user_status' 		=> array('title' => LAN_STATUS,		'type' => 'method',	'alias'=>'user_status', 'width' => 'auto','forced' => true, 'nosort'=>TRUE),
		'user_ban' 			=> array('title' => LAN_STATUS,		'tab'=>0, 'type' => 'method', 'width' => 'auto', 'filter'=>true, 'batch'=>true,'thclass'=>'center', 'class'=>'center'),
	
		'user_name' 		=> array('title' => LAN_USER_01,	'tab'=>0, 'type' => 'text',	'inline'=>true, 'data'=>'safestr', 'width' => 'auto','thclass' => 'left first'), // Display name
 		'user_loginname' 	=> array('title' => LAN_USER_02,	'tab'=>0, 'type' => 'text',	'data'=>'safestr', 'width' => 'auto'), // User name
 		'user_login' 		=> array('title' => LAN_USER_03,	'tab'=>0, 'type' => 'text',	'inline'=>true, 'data'=>'safestr', 'width' => 'auto'), // Real name (no real vetting)
 		'user_customtitle' 	=> array('title' => LAN_USER_04,	'tab'=>0, 'type' => 'text',	'inline'=>true, 'data'=>'safestr', 'width' => 'auto'), // No real vetting
 		'user_password' 	=> array('title' => LAN_PASSWORD,	'tab'=>0, 'type' => 'method',	'data'=>'safestr', 'width' => 'auto'), //TODO add md5 option to form handler?
		'user_sess' 		=> array('title' => LAN_SESSION,	'tab'=>0, 'noedit'=>true, 'type' => 'text',	'width' => 'auto'), // Photo
 		'user_image' 		=> array('title' => LAN_USER_07,	'tab'=>0, 'type' => 'dropdown',	'data'=>'str', 'width' => 'auto'), // Avatar
 		'user_email' 		=> array('title' => LAN_EMAIL,		'tab'=>0, 'type' => 'text', 'inline'=>true, 'data'=>'safestr',	'width' => 'auto', 'writeParms'=>array('size'=>'xxlarge')),
		'user_hideemail' 	=> array('title' => LAN_USER_10,	'tab'=>0, 'type' => 'boolean', 'data'=>'int',	'width' => 'auto', 'thclass'=>'center', 'class'=>'center', 'filter'=>true, 'batch'=>true, 'readParms'=>'trueonly=1'),
		'user_xup' 			=> array('title' => 'Xup',			'tab'=>0, 'noedit'=>true, 'type' => 'text', 'data'=>'str',	'width' => 'auto'),
		'user_class' 		=> array('title' => LAN_USER_12,	'tab'=>0, 'type' => 'userclasses' , 'data'=>'safestr', 'inline'=>true, 'writeParms' => 'classlist=classes,new', 'readParms'=>'classlist=classes,new&defaultLabel=--', 'filter'=>true, 'batch'=>true),
		'user_join' 		=> array('title' => LAN_USER_14,	'tab'=>0, 'noedit'=>true, 'type' => 'datestamp', 	'width' => 'auto', 'writeParms'=>'readonly=1'),
		'user_lastvisit' 	=> array('title' => LAN_USER_15,	'tab'=>0, 'noedit'=>true, 'type' => 'datestamp', 	'width' => 'auto'),
		'user_currentvisit' => array('title' => LAN_USER_16,	'tab'=>0, 'noedit'=>true, 'type' => 'datestamp', 	'width' => 'auto'),
		'user_comments' 	=> array('title' => LAN_COMMENTS,	'tab'=>0, 'noedit'=>true, 'type' => 'int', 	'width' => 'auto','thclass'=>'right','class'=>'right'),
		'user_lastpost' 	=> array('title' => USRLAN_195,	'tab'=>0, 'noedit'=>true, 'type' => 'datestamp', 	'width' => 'auto'),
		'user_ip' 			=> array('title' => LAN_USER_18,	'tab'=>0, 'noedit'=>true, 'type' => 'ip',	'data'=>'str',	'width' => 'auto'),
		//	'user_prefs' 		=> array('title' => LAN_USER_20,	'type' => 'text', 	'width' => 'auto'),
		'user_visits' 		=> array('title' => LAN_USER_21,	'tab'=>0, 'noedit'=>true, 'type' => 'int', 'width' => 'auto','thclass'=>'right','class'=>'right'),
		'user_admin' 		=> array('title' => LAN_USER_22,	'tab'=>0, 'type' => 'method', 'width' => 'auto', 'thclass'=>'center', 'class'=>'center', 'filter'=>true, 'batch'=>true, 'readParms'=>'trueonly=1'),
		'user_perms' 		=> array('title' => LAN_USER_23,	'tab'=>0, 'type' => 'method', 'data'=>'str',	'width' => 'auto'),
		'user_pwchange'		=> array('title' => LAN_USER_24,	'tab'=>0, 'noedit'=>true, 'type'=>'datestamp' , 'width' => 'auto'),
					
	);
	
	protected $fieldpref = array('user_ban','user_name','user_loginname','user_login','user_email','user_class','user_admin');
			
	protected $prefs = array(
	//	'anon_post'				=> array('title'=>PRFLAN_32, 	'type'=>'boolean'),
		'avatar_upload'				=> array('title' => USRLAN_44,  'type' => 'boolean', 'writeParms' => 'label=yesno', 'data' => 'int',),
		'photo_upload'				=> array('title' => USRLAN_53,  'type' => 'boolean', 'writeParms' => 'label=yesno', 'data' => 'int',),
		'im_width'					=> array('title' => USRLAN_47,  'type' => 'number',  'writeParms' => array('maxlength' => 4), 'help' => USRLAN_48, 'data' => 'int', ),
		'im_height'					=> array('title' => USRLAN_49,  'type' => 'number',  'writeParms' => array('maxlength' => 4), 'help' => USRLAN_50, 'data' => 'int', ),
		'profile_rate'				=> array('title' => USRLAN_126, 'type' => 'boolean', 'writeParms' => 'label=yesno', 'data' => 'int',),
		'profile_comments'			=> array('title' => USRLAN_127, 'type' => 'boolean', 'writeParms' => 'label=yesno', 'data' => 'int',),
		'force_userupdate'			=> array('title' => USRLAN_133, 'type' => 'boolean', 'writeParms' => 'label=yesno', 'help' => USRLAN_134, 'data' => 'int',),
		'del_unv'					=> array('title' => USRLAN_93,  'type' => 'number',  'writeParms' => array('maxlength' => 5, 'post' => USRLAN_95), 'help' => USRLAN_94, 'data' => 'int',),
		'del_accu'					=> array('title' => USRLAN_257, 'type' => 'boolean', 'writeParms' => 'label=yesno', 'data' => 'int',),
		'track_online'				=> array('title' => USRLAN_130, 'type' => 'boolean', 'writeParms' => 'label=yesno', 'help' => USRLAN_131, 'data' => 'int',),
		'memberlist_access'			=> array('title' => USRLAN_146, 'type' => 'userclass', 'writeParms' => 'classlist=public,member,guest,admin,main,classes,nobody', 'data' => 'int',),
		'signature_access'			=> array('title' => USRLAN_194, 'type' => 'userclass', 'writeParms' => 'classlist=member,admin,main,classes,nobody', 'data' => 'int',),
		'user_new_period'			=> array('title' => USRLAN_190,  'type' => 'number',  'writeParms' => array('maxlength' => 3, 'post' => LANDT_04s), 'help' => USRLAN_191, 'data' => 'int',),
	);
	
	public $extended = array();
	public $extendedData = array();



	function getExtended()
	{
		return $this->extendedData;
	}

	function init()
	{
	
		$sql = e107::getDb();
		$tp = e107::getParser();


		if(!empty($_POST['resendToAll']))
		{
			$resetPasswords = !empty($_POST['resetPasswords']);
			$age = vartrue($_POST['resendAge'], 24);
			$class = vartrue($_POST['resendClass'], false);
			$this->resend_to_all($resetPasswords, $age, $class);
		}

		
		if($this->getAction() == 'edit')
		{
			$this->fields['user_class']['noedit'] = true;
		}






		
		// Extended fields - FIXME - better field types
		
		if($rows = $sql->retrieve('user_extended_struct', '*', "user_extended_struct_type > 0 AND user_extended_struct_text != '_system_' ORDER BY user_extended_struct_parent ASC",true))
		{
			// TODO FIXME use the handler to build fields and field attributes
			// FIXME a way to load 3rd party language files for extended user fields
			e107::coreLan('user_extended');

			$dataMode = ($this->getAction() === 'list') ? 'str' : false;  // allow for search of extended fields.

			foreach ($rows as $row)
			{
				$field = "user_".$row['user_extended_struct_name'];
				// $title = ucfirst(str_replace("user_","",$field));
				$label = $tp->toHTML($row['user_extended_struct_text'],false,'defs');
				$this->fields[$field] = array('__tableField'=>'ue.'.$field, 'title' => $label,'width' => 'auto', 'type'=>'method', 'readParms'=>array('ueType'=>$row['user_extended_struct_type']), 'method'=>'user_extended', 'data'=>$dataMode, 'tab'=>1, 'noedit'=>false);
			
				$this->extended[] = $field;
				$this->extendedData[$field] = $row;
			}
		}


		if(empty($this->extended))
		{
			$this->tabs = false;
		}



		$this->fields['user_signature']['writeParms']['data'] = e107::getUserClass()->uc_required_class_list("classes");
		
		$this->fields['user_signature'] = array('title' => LAN_USER_09,	'type' => 'textarea', 'data'=>'str',	'width' => 'auto', 'writeParms'=>array('size'=>'xxlarge'));
		$this->fields['options'] = array('title'=> LAN_OPTIONS."&nbsp;",	'type' => 'method',	'forced'=>TRUE, 'width' => '10%', 'thclass' => 'right last', 'class' => 'left');

				
		if(!getperms('4|U0')) // Quick Add User Access Only. 
		{
			unset($this->fields['checkboxes']);
			unset($this->fields['options']);			
		}	
				
		$this->fields['user_image']['writeParms'] = $this->getAvatarList();

		if(!empty($_GET['readonly']))
		{
			foreach($this->fields as $key=>$v)
			{
				if($key == 'options' || $key == 'checkboxes')
				{
					continue;
				}

				$this->fields[$key]['readonly'] = 2;

			}
		}
	//	print_a($this->fields);
		if(!empty($_GET['iframe']))
		{
			define('e_IFRAME', true);
		}



	}



	protected function getAvatarList()
	{
		$avs = array(''=>LAN_NONE);
		$upload = array();
		$sys = array();
		$uploaded = e107::getFile()->get_files(e_AVATAR_UPLOAD);
		foreach($uploaded as $f)
		{
			$id = '-upload-'.$f['fname'];
			$upload[$id] = $f['fname'];	
		}
		$system = e107::getFile()->get_files(e_AVATAR_DEFAULT);
		foreach($system as $f)
		{
			$id = $f['fname'];
			$sys[$id] = $f['fname'];	
		}
		
		$avs['uploaded'] = $upload;
		$avs['system'] = $sys;	
		
		return $avs;
	}


	public function afterDelete($deletedData, $id=null, $deleted_check)
	{
		if(!empty($id))
		{
			$sql = e107::getDb();
			$sql->delete('user_extended',"user_extended_id = ".$id);

			e107::getCache()->clear('online_menu_member_newest');
			e107::getCache()->clear('online_menu_member_total');

			// Trigger admin_user_delete
			e107::getEvent()->trigger('admin_user_delete', $deletedData);
		}

	}




	public function beforeUpdate($new_data, $old_data, $id)
	{
		$tp = e107::getParser();

		$pwdField = 'user_password_'.$id;

		if(!empty($new_data[$pwdField]))
		{
			$new_data['user_password'] = $new_data[$pwdField];
			unset($new_data[$pwdField]);
		}

	//	e107::getMessage()->addInfo(print_a($new_data,true));

		if(empty($new_data['user_password']))
		{
			$new_data['user_password'] = $old_data['user_password'];
		}
		else 
		{

			// issues #3126, #3143: Login not working after admin set a new password using the backend
			// Backend used user_login instead of user_loginname (used in usersettings) and did't escape the password.
			$savePassword = $new_data['user_password'];
			$loginname = $new_data['user_loginname'] ? $new_data['user_loginname'] : $old_data['user_loginname'];
			$email = (isset($new_data['user_email']) && $new_data['user_email']) ? $new_data['user_email'] : $old_data['user_email'];
			$new_data['user_password'] = e107::getDb()->escape(e107::getUserSession()->HashPassword($savePassword, $loginname), false);

			e107::getMessage()->addDebug("Password Hash: ".$new_data['user_password']);
		}
		
		if(!empty($new_data['perms']))
		{
			$new_data['user_perms']	= implode(".",$new_data['perms']);
		}
		
		// Handle the Extended Fields. 
		$this->saveExtended($new_data);

		
	
		
		return $new_data;
	}
	

	function saveExtended($new_data)
	{
		$update = array();
		$fieldtype = array();
		foreach($this->extended as $key) // Grab Extended field data.
		{
			$update['data'][$key] = vartrue($new_data['ue'][$key],'_NULL_');
		}

		e107::getMessage()->addDebug(print_a($update,true));

		if(!empty($update))
		{
			e107::getUserExt()->addFieldTypes($update);

			if(!e107::getDb()->count('user_extended', '(user_extended_id)', "user_extended_id=".intval($new_data['submit_value'])))
			{
				$update['data']['user_extended_id'] = intval($new_data['submit_value']);
				if(e107::getDb()->insert('user_extended', $update))
				{
					// e107::getMessage()->addSuccess(LAN_UPDATED.': '.ADLAN_78); // not needed see pull/1816
					e107::getMessage()->addDebug(LAN_UPDATED.': '.ADLAN_78); // let's put it in debug instead
				}
				else
				{
					e107::getMessage()->addError(LAN_UPDATED_FAILED.': '.ADLAN_78);
					$error = e107::getDb()->getLastErrorText();
					e107::getMessage()->addDebug($error);
					e107::getMessage()->addDebug(print_a($update,true));


					e107::getDb()->getLastErrorText();
				}
			}
			else
			{
				$update['WHERE'] = 'user_extended_id='. intval($new_data['submit_value']);

				if(e107::getDb()->update('user_extended',$update)===false)
				{
					e107::getMessage()->addError(LAN_UPDATED_FAILED.': '.ADLAN_78);
					$error = e107::getDb()->getLastErrorText();
					e107::getMessage()->addDebug($error);
					e107::getMessage()->addDebug(print_a($update,true));

				}
				else
				{
					 e107::getMessage()->reset(E_MESSAGE_SUCCESS)->addSuccess(LAN_UPDATED); 
					e107::getMessage()->addDebug(LAN_UPDATED.': '.ADLAN_78); // let's put it in debug instead
				}
			}
		}


	}


	/**
	 * Unban user trigger
	 * @param int $userid
	 * @return void
	 */
	public function ListUnbanTrigger($userid)
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		$sysuser = e107::getSystemUser($userid, false);
		
		if(!$sysuser->getId())
		{
			e107::getMessage()->addError(USRLAN_223);
			return;
		}

		$row = e107::user($userid);
		
		$sql->update("user", "user_ban='0' WHERE user_id='".$userid."' ");
		$sql->delete("banlist"," banlist_ip='{$row['user_ip']}' ");

		$vars = array('x'=>$sysuser->getId(), 'y'=> $sysuser->getName(), 'z'=> $sysuser->getValue('email'));

		e107::getAdminLog()->log_event('USET_06', $tp->lanVars( USRLAN_162, $vars), E_LOG_INFORMATIVE);
		e107::getMessage()->addSuccess("(".$sysuser->getId().".".$sysuser->getName()." - ".$sysuser->getValue('email').") ".USRLAN_9);
		
		// List data reload
		$this->getTreeModel()->loadBatch(true);
	}
	
	/**
	 * Ban user trigger
	 * @param int $userid
	 * @return void
	 */
	public function ListBanTrigger($userid)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();
		$admin_log = e107::getAdminLog();
		$iph = e107::getIPHandler();
		$tp = e107::getParser();
		
		$sysuser = e107::getSystemUser($userid, false);
		if(!$sysuser->getId())
		{
			$mes->addError(USRLAN_223);
			return;
		}
		$row = $sysuser->getData();
		
		if (($row['user_perms'] == "0") || ($row['user_perms'] == "0."))
		{
			$mes->addWarning(USRLAN_7);
		}
		else
		{
			if ($sql->update("user","user_ban='1' WHERE user_id='".$userid."' "))
			{
				$vars = array('x'=>$row['user_id'], 'y'=> $row['user_name']);
				e107::getLog()->add('USET_05',$tp->lanVars(USRLAN_161, $vars), E_LOG_INFORMATIVE);
				$mes->addSuccess("(".$userid.".".$row['user_name']." - {$row['user_email']}) ".USRLAN_8);
			}
			if (trim($row['user_ip']) == "")
			{
				$mes->addInfo(USRLAN_135);
			}
			else
			{
				if($sql->count('user', '(*)', "user_ip = '{$row['user_ip']}' AND user_ban=0 AND user_id <> {$userid}") > 0)
				{
					// Other unbanned users have same IP address
					$mes->addWarning(str_replace("{IP}", $iph->ipDecode($row['user_ip']), USRLAN_136));
				}
				else
				{
					if ($iph->add_ban(6, USRLAN_149.$row['user_name'].'/'.$row['user_loginname'], $row['user_ip'], USERID))
					{
						// Successful IP ban
						$mes->addSuccess(str_replace("{IP}", $iph->ipDecode($row['user_ip']), USRLAN_137));
					}
					else
					{
						// IP address on whitelist
						$mes->addWarning(str_replace("{IP}", $iph->ipDecode($row['user_ip']), USRLAN_150));
					}
				}
			}
		}
		
		// List data reload
		$this->getTreeModel()->loadBatch(true);
	}

	/**
	 * Activate user trigger
	 */
	public function ListVerifyTrigger($userid)
	{
		$e_event = e107::getEvent();
		$admin_log = e107::getAdminLog();
		$sysuser = e107::getSystemUser($userid, false);
		$userMethods = e107::getUserSession();
		$mes = e107::getMessage();
		$tp = e107::getParser();
		
		$uid = intval($userid);
		if ($sysuser->getId())
		{
			$sysuser->set('user_ban', '0')
				->set('user_sess', '');
				
			$row = $sysuser->getData();
			
			if ($initUserclasses = $userMethods->userClassUpdate($row, 'userall'))
			{
				$row['user_class'] = $initUserclasses;
			}

			$userMethods->addNonDefaulted($row);
			$sysuser->setData($row);

		//	$res = $sysuser->getData();
		//	e107::getDebug()->log($res);

			$sysuser->save();

			$vars = array(
				'x' => $sysuser->getId(),
				'y' => $sysuser->getName(),
				'z' => $sysuser->getValue('email')
			);

			e107::getLog()->add('USET_10', $tp->lanVars( USRLAN_166, $vars), E_LOG_INFORMATIVE);
			e107::getEvent()->trigger('userfull', $row); //BC
			e107::getEvent()->trigger('admin_user_activated', $row);
			
			$mes->addSuccess(USRLAN_86." (#".$sysuser->getId()." : ".$sysuser->getName().' - '.$sysuser->getValue('email').")");
			
			$this->getTreeModel()->loadBatch(true);

			if ((int) e107::pref('core', 'user_reg_veri') == 2)
			{
				$message = USRLAN_114." ".$row['user_name'].",\n\n".USRLAN_122." ".SITENAME.".\n\n".USRLAN_123."\n\n";
				$message .= str_replace("{SITEURL}", SITEURL, USRLAN_139);
				
				$options = array(
					'mail_subject' => USRLAN_113.' '.SITENAME,
					'mail_body' => nl2br($message),
				);

			//	$options['debug'] = 1;

				if($ret =$sysuser->email('email', $options))
				{
					$mes->addSuccess(USRLAN_224." ".$sysuser->getName().' ('.$sysuser->getValue('email').')');
				}
				else 
				{
					$mes->addError(USRLAN_225." ".$sysuser->getName().' ('.$sysuser->getValue('email').')');
				}
			}
		}
		else
		{
			$mes->addError(USRLAN_223);
			return;
		}
	}

	/**
	 * Main admin login as system user trigger
	 */
	public function ListLoginasTrigger($userid)
	{
		$mes = e107::getMessage();

		if(e107::getUser()->getSessionDataAs())
		{
			$mes->addWarning(USRLAN_AS_3);
		}
	  	elseif(e107::getUser()->loginAs($userid))
	  	{ 
	  		$sysuser = e107::getSystemUser($userid);
			$user = e107::getUser();
			
			// TODO - lan
			$mes->addSuccess('Successfully logged in as '.$sysuser->getName().' <a href="'.e_ADMIN_ABS.'users.php?mode=main&amp;action=logoutas">[logout]</a>')
				->addSuccess('Please, <a href="'.SITEURL.'" rel="external">Leave Admin</a> to browse the system as this user. Use &quot;Logout&quot; option in Administration to end front-end session');
			
			$search = array('--UID--', '--NAME--', '--EMAIL--', '--ADMIN_UID--', '--ADMIN_NAME--', '--ADMIN_EMAIL--');
			$replace = array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email'), $user->getId(), $user->getName(), $user->getValue('email'));
			
			 // TODO - lan
			$lan = 'Administrator --ADMIN_EMAIL-- (#--ADMIN_UID--, --ADMIN_NAME--) has logged in as the user --EMAIL-- (#--UID--, --NAME--)';
			
			e107::getLog()->log_event('USET_100', str_replace($search, $replace, $lan), E_LOG_INFORMATIVE);
			
			$eventData = array('user_id' => $sysuser->getId(), 'admin_id' => $user->getId());
			e107::getEvent()->trigger('loginas', $eventData); // BC
			e107::getEvent()->trigger('admin_user_loginas', $eventData); 
			
	  	}
	}

	/**
	 * Main admin logout as a system user trigger
	 */
	public function LogoutasObserver()
	{
		$user = e107::getUser();
		$sysuser = e107::getSystemUser($user->getSessionDataAs(), false);

	  	if(e107::getUser()->logoutAs() && $sysuser && $sysuser->getId())
	  	{
	  		 // TODO - lan
			e107::getMessage()->addSuccess('Successfully logged out from '.$sysuser->getName().' ('.$sysuser->getValue('email').') account', 'default', true);
			
			$search = array('--UID--', '--NAME--', '--EMAIL--', '--ADMIN_UID--', '--ADMIN_NAME--', '--ADMIN_EMAIL--');
			$replace = array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email'), $user->getId(), $user->getName(), $user->getValue('email'));
			
			 // TODO - lan
			$lan = 'Administrator --ADMIN_EMAIL-- (#--ADMIN_UID--, --ADMIN_NAME--) has logged out as the user --EMAIL-- (#--UID--, --NAME--)';
			
			e107::getAdminLog()->log_event('USET_101', str_replace($search, $replace, $lan), E_LOG_INFORMATIVE);
			
			$eventData = array('user_id' => $sysuser->getId(), 'admin_id' => $user->getId());
			e107::getEvent()->trigger('logoutas', $eventData); //BC 
			e107::getEvent()->trigger('admin_user_logoutas', $eventData); 
			$this->redirect('list', 'main', true);
	  	}
		

  		 if(!$sysuser->getId()) e107::getMessage()->addError(LAN_USER_NOT_FOUND);
	}

	public function LogoutasPage()
	{
		// System Message only on non-successful logout as another user 
	}
	
	/**
	 * Remove admin status trigger
	 */
	public function ListUnadminTrigger($userid)
	{
		$user = e107::getUser();
		$sysuser = e107::getSystemUser($userid, false);
		$mes = e107::getMessage();
		$tp = e107::getParser();
		
		if(!$user->checkAdminPerms('3'))
		{
			$mes->addError(USRLAN_226, 'default', true);
			
			//$search = array('--UID--', '--NAME--', '--EMAIL--', '--ADMIN_UID--', '--ADMIN_NAME--', '--ADMIN_EMAIL--');
			$vars = array(
				'u' => $sysuser->getId(),
				'v' => $sysuser->getName(),
				'w' => $sysuser->getValue('email'),
				'x' => $user->getId(),
				'y' => $user->getName(),
				'z' => $user->getValue('email')
			);
			
			e107::getAdminLog()->log_event('USET_08', $tp->lanVars(USRLAN_244,$vars), E_LOG_INFORMATIVE);
			$this->redirect('list', 'main', true);
		}

		if ($sysuser->isMainAdmin())
		{
			$mes->addError(USRLAN_5);
		}
		else
		{
			if($sysuser->set('user_admin', '0')->set('user_perms', '')->save())
			{
				$vars = array('x'=>$sysuser->getId(), 'y'=>$sysuser->getName(), 'z'=>$sysuser->getValue('email'));

				e107::getAdminLog()->log_event('USET_09',$tp->lanVars(USRLAN_165, $vars), E_LOG_INFORMATIVE);
				$mes->addSuccess($sysuser->getName()." (".$sysuser->getValue('email').") ".USRLAN_6);
				$this->getTreeModel()->loadBatch(true);
			}
			else
			{
				$mes->addError(USRLAN_227);
			}
		}
	}

	/**
	 * Admin manage observer
	 * @return void
	 */
	public function AdminObserver()
	{
		if($this->getPosted('go_back'))
		{
			$this->redirect('list', 'main', true);
		}
		
		$userid = $this->getId();
		$sql = e107::getDb();
		$user = e107::getUser();
		$sysuser = e107::getSystemUser($userid, false);
		$admin_log = e107::getAdminLog();
		$mes = e107::getMessage();
		$tp = e107::getParser();
		
		if(!$user->checkAdminPerms('3'))
		{
			$mes->addError(USRLAN_226, 'default', true);
		//	$search = array('--UID--', '--NAME--', '--EMAIL--', '--ADMIN_UID--', '--ADMIN_NAME--', '--ADMIN_EMAIL--');

			$vars = array(
				'u' => $sysuser->getId(),
				'v' => $sysuser->getName(),
				'w' => $sysuser->getValue('email'),
				'x' => $user->getId(),
				'y' => $user->getName(),
				'z' => $user->getValue('email')
			);

		//	$replace = array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email'), $user->getId(), $user->getName(), $user->getValue('email'));
			
			e107::getLog()->add('USET_08', $tp->lanVars( USRLAN_245,$vars), E_LOG_INFORMATIVE);
			
			$this->redirect('list', 'main', true);
		}
		
		if(!$sysuser->getId())
		{
			$mes->addError(USRLAN_223, 'default', true);
			$this->redirect('list', 'main', true);
		}
		
	
		if($this->getPosted('update_admin'))
		{
			 e107::getUserPerms()->updatePerms($userid, $_POST['perms']);
			 $this->redirect('list', 'main', true);
		}
		
		if(!$sysuser->isAdmin()) // Security Check Only. Admin status check is added during 'updatePerms'. 
		{
		//	$sysuser->set('user_admin', 1)->save(); //"user","user_admin='1' WHERE user_id={$userid}"
		//	$lan = str_replace(array('--UID--', '--NAME--', '--EMAIL--'), array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email')), USRLAN_164);
		//	e107::getLog()->add('USET_08', $lan, E_LOG_INFORMATIVE);
		//	$mes->addSuccess($lan);
			$rplc_from = array('[x]', '[y]', '[z]');
			$rplc_to = array($sysuser->getId(), $sysuser->getName(), $sysuser->getValue('email'));
			$message = str_replace($rplc_from, $rplc_to, USRLAN_228);
			$message = e107::getParser()->toHTML($message,true);
			$mes->addWarning($message);
			$mes->addWarning(e107::getParser()->toHTML(USRLAN_229,true));
		}
		
	}
	
	/**
	 * Admin manage page
	 */
	public function AdminPage()
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		$sysuser = e107::getSystemUser($request->getId(), false);
		//$sysuser->load($request->getId(), true);
		$prm = e107::getUserPerms();
		$frm = e107::getForm();
		
		$response->appendBody($frm->open('adminperms'))
			->appendBody($prm->renderPermTable('grouped', $sysuser->getValue('perms')))
			->appendBody($prm->renderCheckAllButtons())
			->appendBody($prm->renderSubmitButtons().$frm->token())
			->appendBody($frm->close());
		
		$this->addTitle(str_replace(array('[x]', '[y]'), array($sysuser->getName(), $sysuser->getValue('email')), USRLAN_230));
	}
	
	protected function checkAllowed($class_id) // check userclass change is permitted.
	{
		$e_userclass = e107::getUserClass();
		if (!isset ($e_userclass->class_tree[$class_id]))
		{
			return false;
		}
		if (!getperms("0") && !check_class($e_userclass->class_tree[$class_id]['userclass_editclass']))
		{
			return false;
		}
		return true;
	}
	
	protected function manageUserclass($userid, $uclass, $mode = false)
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		$sysuser = e107::getSystemUser($userid, false);
		
		$admin_log = e107::getAdminLog();
		$e_userclass = e107::getUserClass();
		$sql = e107::getDb();

		$remuser = true;
        $mes = e107::getMessage();
		
		if(!$sysuser->getId())
		{
			$mes->addError(USRLAN_223);
			return false;
		}

		$curClass = array();
		if($mode !== 'update')
		{
			$curClass = $sysuser->getValue('class') ? explode(',', $sysuser->getValue('class')) : array();
        }

    	foreach ($uclass as $a)
		{
			$a = intval($a);
			if(!$this->checkAllowed($a)) 
			{
				$mes->addError(USRLAN_231);
				return false;
			}
			
			if($a != 0) // if 0 - then do not add.
			{
				$curClass[] = $a;
			}
		}

		if($mode == "remove") // remove selected classes
		{
			$curClass = array_diff($curClass, $uclass);
		}
		elseif($mode == "clear") // clear all classes
		{
			$curClass = array();
		}

        $curClass = array_unique($curClass);

        $svar = is_array($curClass) ? implode(",", $curClass) : "";
		$check = $sysuser->set('user_class', $svar)->save();
		
		if($check)
		{
			$message = UCSLAN_9;
			if ($this->getPosted('notifyuser'))
			{
				$options = array();
				$message .= "<br />".UCSLAN_1.":</b> ".$sysuser->getName()."<br />";

				$messaccess = '';
				foreach ($curClass as $a)
				{
					if (!isset ($e_userclass->fixed_classes[$a]))
					{
						$messaccess .= $e_userclass->class_tree[$a]['userclass_name']." - ".$e_userclass->class_tree[$a]['userclass_description']."\n";
					}
				}
				if ($messaccess == '') $messaccess = UCSLAN_12."\n";
				
				$message = USRLAN_256." ".$sysuser->getName().",\n\n".UCSLAN_4." ".SITENAME."\n( ".SITEURL." )\n\n".UCSLAN_5.": \n\n".$messaccess."\n".UCSLAN_10."\n".SITEADMIN;
				//    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User class change",str_replace("\n","<br />",$message),FALSE,LOG_TO_ROLLING);
				
				$options['mail_subject'] = UCSLAN_2;
				$options['mail_body'] = nl2br($message);
				
				$sysuser->email('email', $options);
				//sendemail($send_to,$subject,$message);
			}
			e107::getLog()->add('USET_14', str_replace(array('[x]','[y]'), array($userid, $svar), UCSLAN_11), E_LOG_INFORMATIVE);

            $mes->addSuccess(nl2br($message));
		}
		else
		{
           	//	$mes->add("Update Failed", E_MESSAGE_ERROR);
        	if($check === false)
			{
				$sysuser->setMessages(); // move messages to the default stack
			}
			else
			{
				$mes->addInfo(LAN_NO_CHANGE);
			}
		}
	}

	/**
	 * Update user class trigger
	 */
	public function UserclassUpdateclassTrigger()
	{
		$this->manageUserclass($this->getId(), $this->getPosted('userclass'), 'update');
	}
	
	/**
	 * Back to user list trigger (userclass page)
	 */
	public function UserclassBackTrigger()
	{
		$this->redirect('list', 'main', true);
	}
	
	/**
	 * Manage userclasses page
	 */
	public function UserclassPage()
	{
		$request = $this->getRequest();
		$response = $this->getResponse();
		$sysuser = e107::getSystemUser($request->getId(), false);
		$e_userclass = e107::getUserClass();
		$userid = $this->getId();
		$frm = e107::getForm();
		
		$caption = UCSLAN_6." <b>".$sysuser->getName().' - '.$sysuser->getValue('email')."</b> (".$sysuser->getClassList(true).")";
		$this->addTitle($caption);
		
		$text = "	<div>
					<form method='post' action='".e_REQUEST_URI."'>
					<fieldset id='core-user-userclass'>
					
                    <table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
					<tr>
						<td>";
		$text .= $e_userclass->vetted_tree('userclass', array($e_userclass,'checkbox_desc'), $sysuser->getValue('class'), 'classes, no-excludes');
		$text .= '
						</td>
					</tr>
					</tbody>
					</table>
		';

		$text .= "	<div class='buttons-bar center'>
	 					".$frm->hidden('userid', $userid)."
						".$frm->checkbox_label(USRLAN_255.'&nbsp;&nbsp;', 'notifyuser', 1)."
						".$frm->admin_button('etrigger_updateclass', LAN_NO_RECORDS_FOUND, 'update')."
						".$frm->admin_button('etrigger_back', LAN_BACK, 'cancel')."
					</div>
					</fieldset>
					</form>
					</div>";

		$response->appendBody($text);
	}
	
	/**
	 * Resend user activation email trigger 
	 */
	public function ListResendTrigger($userid)
	{
		$this->resendActivation($userid);
	}
	
	/**
	 * Resend user activation email helper
	 * FIXME - better Subject/Content for the activation email when user is bounced/deactivated.
	 */
	protected function resendActivation($id, $lfile = '')
	{

		$sysuser = e107::getSystemUser($id, false);
		$key = $sysuser->getValue('sess');
		$mes = e107::getMessage();
		
		if(!$sysuser->getId())
		{
			$mes->addError(USRLAN_223);
			return false;
		}

		if(!$key || !$sysuser->getValue('ban'))
		{
			$mes->addError(USRLAN_232);
			$mes->addDebug("key: ".$key." ban: ".$sysuser->getValue('ban'));
			return false;
		}
		
		// Check for a Language field, and if present, send the email in the user's language.
		// FIXME - make all system emails to be created from HTML email templates, this should fix the multi-lingual issue when sending multiple emails
		if ($lfile == "")
		{
			$lan = $sysuser->getValue('language');
			if ($lan)
			{
				$lfile = e_LANGUAGEDIR.$lan.'/lan_signup.php';
			}
		}
		if ($lfile && is_readable($lfile))
		{
			require_once($lfile);
		}
		else
		{
			//@FIXME use email templates by Language
			require_once (e_LANGUAGEDIR.e_LANGUAGE."/lan_signup.php");
		}
		if(!$lan) $lan = e_LANGUAGE;
		
		// FIXME switch to e107::getUrl()->create(), use email content templates
		//$return_address = (substr(SITEURL,- 1) == "/") ? SITEURL."signup.php?activate.".$sysuser->getId().".".$key : SITEURL."/signup.php?activate.".$sysuser->getId().".".$key;
		$return_address = SITEURL."signup.php?activate.".$sysuser->getId().".".$key;
	//	$message = LAN_EMAIL_01." ".$sysuser->getName()."\n\n".LAN_SIGNUP_24." ".SITENAME.".\n".LAN_SIGNUP_21."\n\n";
	//	$message .= "<a href='".$return_address."'>".$return_address."</a>";
		

		$userInfo = array(
			'user_id'       =>  $sysuser->getId(),
			'user_name'     => $sysuser->getName(),
			'user_email'    =>  $sysuser->getValue('email'),
			'user_sess'     =>  $key,
			'user_loginname' =>  $sysuser->getValue('loginname'),
			);


		$passwordInput = e107::getPref('signup_option_password', 2);

		if(empty($passwordInput)) // auto-generated password at signup.
		{
			$newPwd = e107::getUserSession()->resetPassword($userInfo['user_id']);
		}
		else
		{
			$newPwd = '**********';
		}

		$message = 'null';
		
		$check = $sysuser->email('signup', array(
			'mail_subject' => LAN_SIGNUP_98,
			'mail_body' => nl2br($message),
			'user_password' => $newPwd
		), $userInfo);
		
		if ($check)
		{
			$vars = array('x'=> $sysuser->getId(), 'y'=>$sysuser->getName(), 'z'=> $sysuser->getValue('email'));
			$message = e107::getParser()->lanVars(USRLAN_167,$vars);
			e107::getLog()->add('USET_11', $message, E_LOG_INFORMATIVE);
			$mes->addSuccess(USRLAN_140.": <a href='mailto:".$sysuser->getValue('email')."?body=".$return_address."' title=\"".LAN_USER_08."\" >".$sysuser->getName()." (".$sysuser->getValue('email').")</a> ({$lan}) ");
		}
		else
		{
			$mes->addError(USRLAN_141.": ".$sysuser->getName().' ('.$sysuser->getValue('email').')');
		}
		return $check;
	}

	/**
	 * Test user email observer
	 */
	public function TestObserver()
	{
		$sysuser = e107::getSystemUser($this->getId(), false);
		$mes = e107::getMessage();
		$email = $sysuser->getValue('email');
		
		if(!$sysuser->getId())
		{
			$mes->addError(USRLAN_223, 'default', true);
			$this->redirect('list', 'main', true);
		}
		
		$result = $this->testEmail($email);
		if($result)
		{
			$this->setParam('testSucces', $result);
			$mes->addSuccess($email.' - '.USRLAN_233);
		}
		else
		{
			$mes->addError($email.' - '.USRLAN_234, 'default', true); // Invalid.
			$this->redirect('list', 'main', true);
		}

	}
	
	public function TestCancelTrigger()
	{
		$this->redirect('list', 'main', true);
	}
	
	/**
	 * Resend activation email page - only if tested email is valid
	 */
	public function TestPage()
	{
		$response = $this->getResponse();
		$sysuser = e107::getSystemUser($this->getId(), false);
		$userid = $this->getId();
		$email = $sysuser->getValue('email');
		$frm = e107::getForm();
		
		$caption = str_replace('[x]', $email, USRLAN_119);
		$this->addTitle($caption);
		
		$text = "<a href='".e_REQUEST_HTTP."?mode=main&amp;action=list'>".LAN_BACK."</a>";
		$text .= '<pre>'.htmlspecialchars($this->getParam('testSucces')).'</pre>';
		$text .= "	<div>
					<form method='post' action='".e_REQUEST_HTTP."?mode=main&amp;action=list'>
					<fieldset id='core-user-testemail'>
						<div class='buttons-bar center'>
		 					".$frm->hidden('useraction', 'resend')."
							".$frm->hidden('userid', $userid)."
							".$frm->hidden('userip', $sysuser->getValue('ip'))."
							".$frm->admin_button('resend', USRLAN_112, 'update')."
						</div>
					</fieldset>
					</form>
					</div>";

		$response->appendBody($text);
	}

	/**
	 * Test user email helper
	 */
	protected function testEmail($email)
	{
		list($adminuser,$adminhost) = explode('@',SITEADMINEMAIL, 2);
		
		$validator = new email_validation_class;
		$validator->localuser = $adminuser;
		$validator->localhost = $adminhost;
		$validator->timeout = 5;
		$validator->debug = 1;
		$validator->html_debug = 0;
		
		ob_start();
		$email_status = $validator->ValidateEmailBox($email);
		$text = ob_get_contents();
		ob_end_clean();
		
		if ($email_status == 1)
		{
			return $text;
		}
		
		return false;
	}
	
	/**
	 * Set user status to require verification - available for bounced users
	 */
	public function ListReqverifyTrigger($userid)
	{
		$sysuser = e107::getSystemUser($userid, false);
		
		if(!$sysuser->getId())
		{
			e107::getMessage()->addError(USRLAN_223, 'default', true);
			return;
		}
		
		$sysuser->set('user_ban', 2)
			->set('user_sess', e_user_model::randomKey());
		
		if($sysuser->save())
		{
			e107::getMessage()->addSuccess(USRLAN_235);
			
			// TODO - auto-send email or not - discuss
			$this->resendActivation($userid);
			
			//FIXME admin log
			
			// Reload tree
			$this->getTreeModel()->loadBatch(true);
			return;
		}
		
		e107::getMessage()->addError(USRLAN_236);
	}

	public function EditSubmitTrigger()
	{
		$this->_manageSubmit('beforeUpdate', 'afterUpdate', 'onUpdateError', 'edit', true); // force update.
	}



	/**
	 * Quick Add user submit trigger
	 */
	public function AddSubmitTrigger()
	{
		$e107cache 		= e107::getCache();
		$userMethods 	= e107::getUserSession();
		$mes 			= e107::getMessage();
		$sql 			= e107::getDb();
		$e_event 		= e107::getEvent();
		$admin_log		= e107::getAdminLog();
		$pref = e107::getPref();
		
		if (!$_POST['ac'] == md5(ADMINPWCHANGE))
		{
			exit;
		}
		
		$e107cache->clear('online_menu_member_total');
		$e107cache->clear('online_menu_member_newest');
		$error = false;
		
		if (isset ($_POST['generateloginname']))
		{
			$_POST['loginname'] = $userMethods->generateUserLogin($pref['predefinedLoginName']);
		}
		
		$_POST['password2'] = $_POST['password1'] = $_POST['password'];

		// #1728 - Default value, because user will always be part of 'Members'
		$_POST['class'][] =  e_UC_MEMBER;

		// Now validate everything
		$allData = validatorClass::validateFields($_POST, $userMethods->userVettingInfo, true);

		// Fix Display and user name
		if (!check_class($pref['displayname_class'], $allData['data']['user_class']))
		{
			if ($allData['data']['user_name'] != $allData['data']['user_loginname'])
			{
				$allData['data']['user_name'] = $allData['data']['user_loginname'];
				$message = str_replace('[x]', $allData['data']['user_loginname'], USRLAN_237);
				$message = e107::getParser()->toHTML($message,true);
				$mes->addWarning($message);
				//$allData['errors']['user_name'] = ERR_FIELDS_DIFFERENT;
			}
		}
		
		// Do basic validation
		validatorClass::checkMandatory('user_name, user_loginname', $allData);
		
		// Check for missing fields (email done in userValidation() )
		validatorClass::dbValidateArray($allData, $userMethods->userVettingInfo, 'user', 0);
		
		// Do basic DB-related checks
		$userMethods->userValidation($allData);
		
		// Do user-specific DB checks
		if (!isset($allData['errors']['user_password']))
		{
			// No errors in password - keep it outside the main data array
			$savePassword = $allData['data']['user_password'];
			// Delete the password value in the output array
			unset ($allData['data']['user_password']);
		}

		// Restrict the scope of this
		unset($_POST['password2'], $_POST['password1']);
		
		if (count($allData['errors']))
		{
			$temp = validatorClass::makeErrorList($allData, 'USER_ERR_','%n - %x - %t: %v', '<br />', $userMethods->userVettingInfo);
			$mes->addError($temp);
			$error = true;
		}
		
		// Always save some of the entered data - then we can redisplay on error
		$user_data = & $allData['data'];
		
		if($error)
		{
			$this->setParam('user_data', $user_data);
			return;
		}
		
		if(varset($_POST['perms']))
		{
			$allData['data']['user_admin'] = 1;
			$allData['data']['user_perms'] = implode('.',$_POST['perms']);
		}



		$user_data['user_password'] = $userMethods->HashPassword($savePassword, $user_data['user_loginname']);
		$user_data['user_join'] = time();

		e107::getMessage()->addDebug("Password Hash: ".$user_data['user_password']);
		
		if ($userMethods->needEmailPassword())
		{
			// Save separate password encryption for use with email address
            $user_prefs = e107::getArrayStorage()->unserialize($user_data['user_prefs']);
            $user_prefs['email_password'] = $userMethods->HashPassword($savePassword, $user_data['user_email']);
			$user_data['user_prefs'] = e107::getArrayStorage()->serialize($user_prefs);
            unset($user_prefs);
		}
		
		$userMethods->userClassUpdate($allData['data'], 'userall');
		
		//FIXME - (SecretR) there is a better way to fix this (missing default value, sql error in strict mode - user_realm is to be deleted from DB later)
		$allData['data']['user_realm'] = '';
		
		// Set any initial classes
		$userMethods->addNonDefaulted($user_data);
		validatorClass::addFieldTypes($userMethods->userVettingInfo, $allData);
		
		$userid = $sql->insert('user', $allData);
		if ($userid)
		{
			$this->saveExtended(array('submit_value'=>$userid));

			$sysuser = e107::getSystemUser(false, false);
			$sysuser->setData($allData['data']);
			$sysuser->setId($userid);
			$user_data['user_id'] = $userid;
			
			// Add to admin log
			e107::getLog()->add('USET_02',"UName: {$user_data['user_name']}; Email: {$user_data['user_email']}", E_LOG_INFORMATIVE);
			
			// Add to user audit trail
			e107::getLog()->user_audit(USER_AUDIT_ADD_ADMIN, $user_data, 0, $user_data['user_loginname']);
			e107::getEvent()->trigger('userfull', $user_data);
			e107::getEvent()->trigger('admin_user_created', $user_data); 
			
			// send everything available for user data - bit sparse compared with user-generated signup
			if(isset($_POST['sendconfemail']))
			{
				$check = false;
				
				// Send confirmation email to user
				switch ((int) $_POST['sendconfemail']) 
				{
					case 0:
						// activate, don't notify
						$check = -1;
					break;
					
					case 1:
						// activate and send password
						$check = $sysuser->email('quickadd', array(
							'user_password' => $savePassword, 
							'mail_subject' => USRLAN_187,
							'activation_url' => USRLAN_246,
						));
					break;
					
					case 2:
						// require activation and send password and activation link
						$sysuser->set('user_ban', 2)
							->set('user_sess', e_user_model::randomKey())
							->save();
							
						$check = $sysuser->email('quickadd', array(
							'user_password' => $savePassword, 
							'mail_subject' => USRLAN_187,
							'activation_url' => SITEURL."signup.php?activate.".$sysuser->getId().".".$sysuser->getValue('sess'),
						));
					break;
				}

				if($check && $check !== -1)
				{
					$mes->addSuccess(USRLAN_188);
				}
				elseif(!$check)
				{
					$mes->addError(USRLAN_189);
				}
			}
			
		//	$message = str_replace('--NAME--', htmlspecialchars($user_data['user_name'], ENT_QUOTES, CHARSET), USRLAN_174);
			$message = USRLAN_172; // "User account has been created with the following:" ie. keep it simple so it can easily be copied and pasted. 
			
			// Always show Login name and password
			//if (isset($_POST['generateloginname']))
			{
				$mes->addSuccess($message)
					->addSuccess(USRLAN_128.': <strong>'.htmlspecialchars($user_data['user_loginname'], ENT_QUOTES, CHARSET).'</strong>');	
			}
				
			//if (isset($_POST['generatepassword']))
			{
				$mes->addSuccess(LAN_PASSWORD.': <strong>'.htmlspecialchars($savePassword, ENT_QUOTES, CHARSET).'</strong>');	
			}
			return;
		}
		else
		{
			$mes->addError(LAN_CREATED_FAILED);
			$mes->addError($sql->getLastErrorText());
		}
	}
	
	/**
	 * Quick add user page
	 */
	function AddPage()
	{
		$prm = e107::getUserPerms();
		//$list = $prm->getPermList();
		$frm = e107::getForm();
		$e_userclass = e107::getUserClass();
		$pref = e107::getPref();
		$user_data = $this->getParam('user_data');
		
	// 	$this->addTitle(LAN_USER_QUICKADD);
		
		$text = "<div>".$frm->open("core-user-adduser-form",null,null,'autocomplete=0')."
		<div style='display:none'><input type='password' id='_no_autocomplete_' /></div>
		<fieldset id='core-user-adduser'>
        <table class='table adminform'>
		<colgroup>
		<col class='col-label' />
		<col class='col-control' />
		</colgroup>
		<tr>
			<td>".USRLAN_61."</td>
			<td>
			".$frm->text('username', varset($user_data['user_name']), varset($pref['displayname_maxlength'], 15), array('size'=>'xlarge'))."
			</td>
		</tr>

		<tr>
			<td>".USRLAN_128."</td>
			<td ><div class='form-inline'>
			".$frm->text('loginname', varset($user_data['user_loginname']), varset($pref['loginname_maxlength'], 30), array('size'=>'xlarge'))."&nbsp;&nbsp;
			".$frm->checkbox_label(USRLAN_170, 'generateloginname', 1, varset($pref['predefinedLoginName'], false))."
			</div></td>
		</tr>

		<tr>
			<td>".USRLAN_129."</td>
			<td>
			".$frm->text('realname', varset($user_data['user_login']), 30, array('size'=>'xlarge'))."
			</td>
		</tr>

		<tr>
			<td>".LAN_PASSWORD."</td>
			<td>".$frm->password('password', '', 128, array('size' => 'xlarge', 'class' => 'tbox e-password', 'generate' => 1, 'strength' => 1, 'autocomplete' => 'new-password'))."
 			</td>
		</tr>";
		


		$text .= "
			<tr>
				<td>".USRLAN_64."</td>
				<td>
				".$frm->text('email', varset($user_data['user_email']), 100, array('size'=>'xlarge'))."
				</td>
			</tr>
	
			<tr>
				<td>".USRLAN_239."</td>
				<td>
					".$frm->select('sendconfemail', array('0' => USRLAN_240, '1' => USRLAN_241, '2' => USRLAN_242), (int) varset($_POST['sendconfemail'], 0), array('size'=>'xlarge'))."
					<div class='field-help'>".USRLAN_181."</div>
				</td>
			</tr>";

		if (!isset ($user_data['user_class'])) $user_data['user_class'] = varset($pref['initial_user_classes']);
		$temp = $e_userclass->vetted_tree('class', array($e_userclass, 'checkbox_desc'), $user_data['user_class'], 'classes, no-excludes');

		if ($temp)
		{
			$text .= "<tr style='vertical-align:top'>
			<td>
				".USRLAN_120."
			</td>
			<td>
				<a href='#set_class' class='btn btn-default btn-secondary e-expandit'>".USRLAN_120."</a>
				<div class='e-hideme' id='set_class'>
				{$temp}
				</div>
			</td>
			</tr>\n";
		}

		// Make Admin.
		$text .= "
		<tr>
			<td>".USRLAN_35."</td>
			<td>
				<a href='#set_perms' class='btn btn-default btn-secondary e-expandit'>".USRLAN_243."</a>
				<div class='e-hideme' id='set_perms'>
		";
			
		$text .= $prm->renderPermTable('grouped');

		$text .= "
				</div>
			</td>
		</tr>
		";


		$text .= "

		</table>
		<div class='buttons-bar center'>
			".$frm->admin_trigger('submit', USRLAN_60, 'create')."
			".$frm->token()."
			<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
		</div>
		</fieldset>
		</form>
		</div>
		";
		
		
		return $text;
		//$ns->tablerender(USRLAN_59,$mes->render().$text);
	}	

	public function RanksUpdateTrigger()
	{
		$fg = array();
		$ranks_calc = '';
		$ranks_flist = '';
		$config = e107::getConfig();
		foreach ($_POST['op'] as $f => $o)
		{
			$cfg[$f]['op'] = $o;
			$cfg[$f]['val'] = varset($_POST['val'][$f],'');
			if ($_POST['val'][$f])
			{
				$ranks_calc .= ($ranks_calc ? ' + ' : '').'({'.$f.'} '." $o {$_POST['val'][$f]}".' )';
				$ranks_flist .= ($ranks_flist ? ',' : '').$f;
			}
		}

		//Delete existing rank config
		e107::getDb()->delete('generic', "gen_type = 'user_rank_config'");
		
		$tmp = array();
		$tmp['data']['gen_type'] = 'user_rank_config';
		$tmp['data']['gen_chardata'] = serialize($cfg);
		$tmp['_FIELD_TYPES']['gen_type'] = 'string';
		$tmp['_FIELD_TYPES']['gen_chardata'] = 'escape';
		
		//Add the new rank config
		e107::getDb()->insert('generic', $tmp);
		
		// save prefs
		$config->set('ranks_calc', $ranks_calc);
		$config->set('ranks_flist', $ranks_flist);
		$config->save();
		$config->resetMessages();

		//Delete existing rank data
		e107::getDb()->delete('generic',"gen_type = 'user_rank_data'");
		
		//Add main site admin info
		$tmp = array();
		$tmp['_FIELD_TYPES']['gen_datestamp'] = 'int';
		$tmp['_FIELD_TYPES']['gen_ip'] = 'todb';
		$tmp['_FIELD_TYPES']['gen_user_id'] = 'int';
		$tmp['_FIELD_TYPES']['gen_chardata'] = 'todb';
		$tmp['_FIELD_TYPES']['gen_intdata'] = 'int';
		$tmp['data']['gen_datestamp'] = 1;
		$tmp['data']['gen_type'] = 'user_rank_data';
		$tmp['data']['gen_ip'] = $_POST['calc_name']['main_admin'];
		$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx']['main_admin'],0);
		$tmp['data']['gen_chardata'] = $_POST['calc_img']['main_admin'];
		e107::getDb()->insert('generic',$tmp);
		
		//Add site admin info
		unset ($tmp['data']);
		$tmp['data']['gen_type'] = 'user_rank_data';
		$tmp['data']['gen_datestamp'] = 2;
		$tmp['data']['gen_ip'] = $_POST['calc_name']['admin'];
		$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx']['admin'],0);
		$tmp['data']['gen_chardata'] = $_POST['calc_img']['admin'];
		e107::getDb()->insert('generic', $tmp);
		
		//Add all current site defined ranks
		if (isset ($_POST['field_id']))
		{
			foreach ($_POST['field_id'] as $fid => $x)
			{
				unset ($tmp['data']);
				$tmp['data']['gen_type'] = 'user_rank_data';
				$tmp['data']['gen_ip'] = varset($_POST['calc_name'][$fid],'');
				$tmp['data']['gen_user_id'] = varset($_POST['calc_pfx'][$fid],0);
				$tmp['data']['gen_chardata'] = varset($_POST['calc_img'][$fid],'');
				$tmp['data']['gen_intdata'] = varset($_POST['calc_lower'][$fid],'_NULL_');
				e107::getDb()->insert('generic', $tmp);
			}
		}
		
		//Add new rank, if posted
		if (varset($_POST['new_calc_lower']))
		{
			unset ($tmp['data']);
			$tmp['data']['gen_type'] = 'user_rank_data';
			$tmp['data']['gen_datestamp'] = 0;
			$tmp['data']['gen_ip'] = varset($_POST['new_calc_name']);
			$tmp['data']['gen_user_id'] = varset($_POST['new_calc_pfx'],0);
			$tmp['data']['gen_chardata'] = varset($_POST['new_calc_img']);
			$tmp['data']['gen_intdata'] = varset($_POST['new_calc_lower']);
			e107::getDb()->insert('generic', $tmp);
		}
		
		e107::getMessage()->addSuccess(LAN_UPDATED); //XXX maybe not needed see pull/1816
		e107::getCache()->clear_sys('nomd5_user_ranks');
	}

	function RanksDeleteTrigger($posted)
	{
		$rankId = (int) key($posted);
		
		e107::getCache()->clear_sys('nomd5_user_ranks');
		if (e107::getDb()->delete('generic',"gen_id='{$rankId}'"))
		{
			e107::getMessage()->addSuccess(LAN_DELETED);
		}
		else
		{
			e107::getMessage()->addError(LAN_DELETED_FAILED);
		}
	}

	function RanksPage()
	{
		$frm = e107::getForm();
		$e107 = e107::getInstance();
		$pref = e107::getPref();
		$mes = e107::getMessage();
		/** @var users_admin_form_ui $ui */
		$ui = $this->getUI();
		$tp = e107::getParser();

		$ranks = e107::getRank()->getRankData();
		$tmp = e107::getFile()->get_files(e_IMAGE.'ranks', '.*?\.(png|gif|jpg)');
		
	//	$this->addTitle(LAN_USER_RANKS);
		
		foreach($tmp as $k => $v)
		{
			$imageList[] = $v['fname'];
		}
		unset($tmp);
		natsort($imageList);
	
		$text = $frm->open('core-user-ranks-form');

		
		$fields = array(
			'type' => array('title' => USRLAN_207, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
			'rankName' => array('title' => USRLAN_208, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
			'lowThresh' => array('title' => USRLAN_209, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
			'langPrefix' => array('title' => USRLAN_210, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
			'rankImage' => array('title' => USRLAN_211, 'type' => 'text', 'width' => 'auto', 'thclass' => 'left', 'class' => 'left'),
		);
	
	
		$text .= "
		<table class='table adminlist'>".
		$frm->colGroup($fields, array_keys($fields)).
		$frm->thead($fields, array_keys($fields));
	
		$info = $ranks['special'][1];
		$val = $tp->toForm($info['name']);
		$text .= "
		<tr>
			<td>".LAN_MAINADMIN."</td>
			<td>
				".$frm->text('calc_name[main_admin]', $val)."
			</td>
			<td>N/A</td>
			<td>".$frm->checkbox('calc_pfx[main_admin]', 1, $info['lan_pfx'] ? true : false)."</td>
			<td>".$ui->RankImageDropdown($imageList,'calc_img[main_admin]',$info['image'])."</td>
		</tr>
		";
		$info = $ranks['special'][2];
		$val = $tp->toForm($info['name']);
		$text .= "
		<tr>
			<td>".LAN_ADMIN."</td>
			<td>
				".$frm->text('calc_name[admin]', $val)."
			</td>
			<td>N/A</td>
			<td>".$frm->checkbox('calc_pfx[admin]', 1, $info['lan_pfx'] ? true : false)."</td>
			<td>".$ui->RankImageDropdown($imageList, 'calc_img[admin]', $info['image'])."</td>
		</tr>
		<tr>
			<td colspan='5'>&nbsp;</td>
		</tr>
		";
		
		foreach ($ranks['data'] as $k => $r)
		{
			$pfx_checked = ($r['lan_pfx'] ? "checked='checked'" : '');
			$text .= "
			<tr>
				<td>".USRLAN_212."</td>
				<td>
					<input type='hidden' name='field_id[{$k}]' value='1' />
					<input class='tbox' type='text' name='calc_name[$k]' value='{$r['name']}' />
				</td>
				<td>".$frm->number("calc_lower[{$k}]", $r['thresh'])."</td>
				<td>".$frm->checkbox("calc_pfx[{$k}]", 1, $r['lan_pfx'] ? true : false)."</td>
				<td>".$ui->RankImageDropdown($imageList, "calc_img[$k]", $r['image'])."&nbsp;".
				$frm->submit_image("etrigger_delete[{$r['id']}]", LAN_DELETE, 'delete', LAN_CONFIRMDEL.": [{$r['name']}]?")."
				</td>
			</tr>
			";
		}
		
		$text .= "
	
		<tr>
			<td colspan='5'>&nbsp;</td>
		</tr>
		<tr>
			<td>".USRLAN_214."</td>
			<td>".$frm->text('new_calc_name', '')."</td>
			<td>".$frm->number('new_calc_lower', '')."</td>
			<td>".$frm->checkbox('new_calc_pfx', 1, false)."</td>
			<td>".$ui->RankImageDropdown($imageList, 'new_calc_img')."</td>
		</tr>";
		
		$text .= '</table>
		<div class="buttons-bar center">
		'.$frm->admin_trigger('update', 'no-value', 'update', LAN_UPDATE).'
		</div>
		</form>';
		
		return $text;		
	}
	
	// ================== OLD CODE backup =============>
	
	// It might be rewritten with user info option (ui trigger)
	// Old trigger code
	// if ((isset ($_POST['useraction']) && $_POST['useraction'] == "userinfo") || $_GET['userinfo'])
	// {
		// $ip = ($_POST['userip']) ? $_POST['userip'] : $_GET['userinfo'];
		// $user->user_info($ip);
	// }
	function user_info($ipd)
	{
		global $ns, $sql, $e107;

		if (isset($ipd))
		{
			$bullet = '';
			if(defined('BULLET'))
			{
				$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
			}
			elseif(file_exists(THEME.'images/bullet2.gif'))
			{
				$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
			}
            // TODO - move to e_userinfo.php
			$obj = new convert;
			$sql->db_Select("chatbox", "*", "cb_ip='$ipd' LIMIT 0,20");
			$host = $e107->get_host_name($ipd);
			$text = USFLAN_3." <b>".$ipd."</b> [ ".USFLAN_4.": $host ]<br />
				<i><a href=\"banlist.php?".$ipd."\">".USFLAN_5."</a></i>

				<br /><br />";
			while (list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip ) = $sql->fetch())
			{
				$datestamp = $obj->convert_date($cb_datestamp, "short");
				$post_author_id = substr($cb_nick, 0, strpos($cb_nick, "."));
				$post_author_name = substr($cb_nick, (strpos($cb_nick, ".")+1));
				$text .= $bullet."
					<span class=\"defaulttext\"><i>".$post_author_name." (".USFLAN_6.": ".$post_author_id.")</i></span>
					<div class=\"mediumtext\">
					".$datestamp."
					<br />
					". $cb_message."
					</div>
					<br />";
			}

			$text .= "<hr />";

			$sql->db_Select("comments", "*", "comment_ip='$ipd' LIMIT 0,20");
			while (list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, $comment_ip) = $sql->fetch())
			{
				$datestamp = $obj->convert_date($comment_datestamp, "short");
				$post_author_id = substr($comment_author, 0, strpos($comment_author, "."));
				$post_author_name = substr($comment_author, (strpos($comment_author, ".")+1));
				$text .= $bullet."
					<span class=\"defaulttext\"><i>".$post_author_name." (".USFLAN_6.": ".$post_author_id.")</i></span>
					<div class=\"mediumtext\">
					".$datestamp."
					<br />". $comment_comment."
					</div>
					<br />";
			}

		}

		$ns->tablerender(USFLAN_7, $text);
	}




	function maintenancePage()
	{
		$frm = e107::getForm();
		$ns = e107::getRender();
		$sql = e107::getDb();
		$tp = e107::getParser();

		$age = array(
			1=> LAN_UI_1_HOUR, 3=> LAN_UI_3_HOURS, 6=> LAN_UI_6_HOURS, 12=> LAN_UI_12_HOURS, 24 => LAN_UI_24_HOURS, 48 => LAN_UI_48_HOURS, 72 => LAN_UI_3_DAYS);

		$count = $sql->count('user','(*)',"user_ban = 2 ");
		$caption = $tp->lanVars(USRLAN_252,$count);

		$text = $frm->open('userMaintenance','post');

		$text .= "
        <table class='table adminform'>
		<colgroup>
		<col class='col-label' />
		<col class='col-control' />
		</colgroup>
		<tr><td>".$caption."<td>
		<td>
		<div class='form-inline'>".USRLAN_253." ".$frm->select('resendAge', $age, 24).$frm->checkbox('resetPasswords',1,false,USRLAN_254).
		" <div class='input-group'>".$frm->userclass('resendClass',false, null )."<span class='input-group-btn'>".
		$frm->button('resendToAll', 1, 'warning', LAN_GO)."


		</span></div></div></td></tr>
		</table>";

		$text .= $frm->close();

		return $text;




	}


	/**
	 * Send an activation email to all unactivated users older than so many hours.
	 * @param bool $resetPasswords
	 * @param int $age in hours. ie. older than 24 hours will be sent an email.
	 */
	function resend_to_all($resetPasswords=false, $age=24, $class='')
	{
		global $sql,$pref;
		$tp = e107::getParser();
		$sql = e107::getDb();
		$sql2 = e107::getDb('toall');

		$emailLogin = e107::getPref('allowEmailLogin');

		e107::lan('core','signup');

		$ageOpt = intval($age)." hours ago";
		$age = strtotime($ageOpt);

	//	$query = "SELECT u.*, ue.* FROM `#user` AS u LEFT JOIN `#user_extended` AS ue ON ue.user_extended_id = u.user_id WHERE u.user_ban = 2 AND u.user_email != '' AND u.user_join < ".$age." ORDER BY u.user_id DESC";


		$query = "SELECT u.* FROM `#user` AS u WHERE u.user_ban = 2 AND u.user_email != '' AND u.user_join < ".$age." ";

		if(!empty($class))
		{
			$query .= " AND FIND_IN_SET( ".intval($class).", u.user_class) ";
		}

		$query .= " ORDER BY u.user_id DESC";

		$sql->gen($query);

		$recipients = array();

		$usr = e107::getUserSession();

		while ($row = $sql->fetch())
		{

			if($resetPasswords === true)
			{
				$rawPassword    = $usr->generateRandomString('********');
				$sessKey        = e_user_model::randomKey();

				$updateQry = array(
					'user_sess'     => $sessKey,
					'user_password' => $usr->HashPassword($rawPassword, $row['user_loginname']),
					'WHERE'         => 'user_id = '.$row['user_id']." LIMIT 1"
				);

				if(!$sql2->update('user',$updateQry))
				{

					e107::getMessage()->addError("Error updating user's password. #".$row['user_id']." : ".$row['user_email']);
					e107::getMessage()->addDebug(print_a($updateQry,true));

				//	break;
				}
				else
				{
					e107::getMessage()->addInfo("Updated ".$row['user_id']." : ".$row['user_email']);
				}


				$row['user_sess'] = $sessKey;

			}
			else
			{
				$rawPassword = '(*** hidden ***)';
			}

			$activationUrl = SITEURL."signup.php?activate.".$row['user_id'].".".$row['user_sess'];



			$recipients[] = array(
				'mail_recipient_id'     => $row['user_id'],
				'mail_recipient_name'   => $row['user_name'],		// Should this use realname?
				'mail_recipient_email'  => $row['user_email'],
				'mail_target_info'		=> array(
					'USERID'		        => $row['user_id'],
					'LOGINNAME'             => (intval($emailLogin) === 1) ? $row['user_email'] : $row['user_loginname'],
					'PASSWORD'              => $rawPassword,
					'DISPLAYNAME' 	        => $tp->toDB($row['user_name']),
					'SUBJECT'               => LAN_SIGNUP_98,
					'USERNAME' 		        => $row['user_name'],
					'USERLASTVISIT'         => $row['user_lastvisit'],
					'ACTIVATION_LINK'       => '<a href="'.$activationUrl.'">'.$activationUrl.'</a>', // Warning: switching the quotes on this will break the template.
					'ACTIVATION_URL'        => $activationUrl,
					'DATE_SHORT'            => $tp->toDate(time(),'short'),
					'DATE_LONG'             => $tp->toDate(time(),'long'),
					'SITEURL'               => SITEURL
				)
			);

		//	echo $row['user_id']." ".$row['user_sess']." ".$row['user_name']." ".$row['user_email']."<br />";

		}

		$siteadminemail = e107::getPref('siteadminemail');
		$siteadmin = e107::getPref('siteadmin');

		$mailer = e107::getBulkEmail();

		// Create the mail body
		$mailData = array(
			'mail_total_count'      => count($recipients),
			'mail_content_status' 	=> MAIL_STATUS_TEMP,
			'mail_create_app' 		=> 'core',
			'mail_title' 			=> 'RESEND ACTIVATION',
			'mail_subject' 			=> LAN_SIGNUP_98,
			'mail_sender_email' 	=> e107::getPref('replyto_email',$siteadminemail),
			'mail_sender_name'		=> e107::getPref('replyto_name',$siteadmin),
			'mail_notify_complete' 	=> 0,			// NEVER notify when this email sent!!!!!
			'mail_body' 			=> 'null',
			'template'				=> 'signup',
			'mail_send_style'       => 'signup'
		);


		$mailer->sendEmails('signup', $mailData, $recipients, array('mail_force_queue'=>1));
		$totalMails = count($recipients);

		$url = e_ADMIN."mailout.php?mode=pending&action=list";

		e107::getMessage()->addSuccess("Total emails added to <a href='".$url."'>mail queue</a>: ".$totalMails);

	}

	// ---------------------------------------------------------------------
	//		Bounce handling - FIXME convert to cron job
	// ---------------------------------------------------------------------
	// $bounce_act has the task to perform:
	//	'first_check' - initial read of list of bounces
	//	'delnonbounce' - delete any emails that aren't bounces
	//  'clearemailbounce' - delete email address for any user whose emails bounced
	//	'delchecked' - delete the emails whose comma-separated IDs are in $bounce_arr
	//	'delall' - delete all bounced emails

	/*
	function check_bounces($bounce_act = 'first_check',$bounce_arr = '')
	{
		### old Trigger code for bounce check
		// $bounce_act = '';
		// if (isset ($_POST['check_bounces']))
			// $bounce_act = 'first_check';
		// if (isset ($_POST['delnonbouncesubmit']))
			// $bounce_act = 'delnonbounce';
		// if (isset ($_POST['clearemailbouncesubmit']))
			// $bounce_act = 'clearemailbounce';
		// if (isset ($_POST['delcheckedsubmit']))
			// $bounce_act = 'delchecked';
		// if (isset ($_POST['delallsubmit']))
			// $bounce_act = 'delall';
		// if ($bounce_act)
		// {
			// $user->check_bounces($bounce_act,implode(',',$_POST['delete_email']));
			// require_once ("footer.php");
			// exit;
		// }
		
		global $sql,$pref;
		include (e_HANDLER.'pop3_class.php');
		if (!trim($bounce_act))
		{
			$bounce_act = 'first_check';
		}
		//	  echo "Check bounces. Action: {$bounce_act}; Entries: {$bounce_arr}<br />";
		$obj = new receiveMail($pref['mail_bounce_user'],$pref['mail_bounce_pass'],$pref['mail_bounce_email'],$pref['mail_bounce_pop3'],varset($pref['mail_bounce_type'],'pop3'));
		$del_count = 0;
		if ($bounce_act != 'first_check')
		{
		// Must do some deleting
			$obj->connect();
			$tot = $obj->getTotalMails();
			$del_array = explode(',',$bounce_arr);
			for ($i = 1; $i <= $tot; $i++)
			{
			// Scan all emails; delete current one if meets the criteria
				$dodel = false;
				switch ($bounce_act)
				{
					case 'delnonbounce' :
						$head = $obj->getHeaders($i);
						$dodel = (!$head['bounce']);
						break;
					case 'clearemailbounce' :
						if (!in_array($i,$del_array))
							break;
						$head = $obj->getHeaders($i);
						if ($head['bounce'])
						{
							if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
							{
								$usr_email = trim($result[0]);
							}
							if ($sql->db_Select('user','user_id, user_name, user_email',"user_email='".$usr_email."' "))
							{
								$row = $sql->fetch();
								if ($sql->db_Update('user',"`user_email`='' WHERE `user_id` = '".$row['user_id']."' ") !== false)
								{
								// echo "Deleting user email {$row['user_email']} for user {$row['user_name']}, id={$row['user_id']}<br />";
									$dodel = true;
								}
							}
						}
						break;
					case 'delall' :
						$dodel = true;
						break;
					case 'delchecked' :
						$dodel = in_array($i,$del_array);
						break;
				}
				if ($dodel)
				{
				//			  echo "Delete email ID {$i}<br />";
					$obj->deleteMails($i);
					$del_count++;
					// Keep track of number of emails deleted
				}
			}
			// End - Delete one email
			$obj->close_mailbox();
			// This actually deletes the emails
		}
		// End of email deletion
		// Now list the emails that are left
		$obj->connect();
		$tot = $obj->getTotalMails();
		$found = false;
		$DEL = ($pref['mail_bounce_delete']) ? true : false;
		$text = "<br /><div><form  method='post' action='".e_REQUEST_URI."'><table>
		<tr><td style='width:5%'>#</td><td>e107-id</td><td>email</td><td>Subject</td><td>Bounce</td></tr>\n";
		
		$identifier = deftrue('MAIL_IDENTIFIER', 'X-e107-id');
		
		for ($i = 1; $i <= $tot; $i++)
		{
			$head = $obj->getHeaders($i);
			if ($head['bounce'])
			{
			// Its a 'bounce' email
				if (preg_match('/.*'.$identifier.':(.*)MIME/',$obj->getBody($i),$result))
				{
					if ($result[1])
					{
						$id[$i] = intval($result[1]);
						// This should be a user ID - but not on special mailers!
						//	Try and pull out an email address from body - should be the one that failed
						if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
						{
							$emails[$i] = "'".$result[0]."'";
						}
						$found = true;
					}
				}
				elseif (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
				{
					if ($result[0] && $result[0] != $pref['mail_bounce_email'])
					{
						$emails[$i] = "'".$result[0]."'";
						$found = true;
					}
					elseif ($result[1] && $result[1] != $pref['mail_bounce_email'])
					{
						$emails[$i] = "'".$result[1]."'";
						$found = true;
					}
				}
				if ($DEL && $found)
				{
				// Auto-delete bounced emails once noticed (if option set)
					$obj->deleteMails($i);
					$del_count++;
				}
			}
			else
			{
			// Its a warning message or similar
			//			  $id[$i] = '';			// Don't worry about an ID for now
			//				Try and pull out an email address from body - should be the one that failed
				if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$obj->getBody($i),$result))
				{
					$wmails[$i] = "'".$result[0]."'";
				}
			}
			$text .= "<tr><td>".$i."</td><td>".$id[$i]."</td><td>".(isset ($emails[$i]) ? $emails[$i] : $wmails[$i])."</td><td>".$head['subject']."</td><td>".($head['bounce'] ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON);
			$text .= "<input type='checkbox' name='delete_email[]' value='{$i}' /></td></tr>\n";
		}
		if ($del_count)
		{
			e107::getLog()->add('USET_13', e107::getParser()->lanVars(USRLAN_169, $del_count),E_LOG_INFORMATIVE);
		}
		if ($tot)
		{
		// Option to delete emails - only if there are some in the list
			$text .= "</table><table style='".ADMIN_WIDTH."'><tr>
			<td style='text-align: center;'><input class='btn btn-default btn-secondary button' type='submit' name='delnonbouncesubmit' value='".USRLAN_183."' /></td>\n
			<td style='text-align: center;'><input class='btn btn-default btn-secondary button' type='submit' name='clearemailbouncesubmit' value='".USRLAN_184."' /></td>\n
			<td style='text-align: center;'><input class='btn btn-default btn-secondary button' type='submit' name='delcheckedsubmit' value='".USRLAN_179."' /></td>\n
			<td style='text-align: center;'><input class='btn btn-default btn-secondary button' type='submit' name='delallsubmit' value='".USRLAN_180."' /></td>\n
			</td></tr>";
		}
		$text .= "</table></form></div>";
		array_unique($id);
		array_unique($emails);
		$all_ids = implode(',',$id);
		$all_emails = implode(',',$emails);
		$obj->close_mailbox();
		// This will actually delete emails
		// $tot has total number of emails in the mailbox
		$found = count($emails);
		// $found - Number of bounce emails found
		// $del_count has number of emails deleted
		// Update bounce status for users
		$ed = $sql->db_Update('user',"user_ban=3 WHERE (`user_id` IN (".$all_ids.") OR `user_email` IN (".$all_emails.")) AND user_sess !='' ");
		if (!$ed)
			$ed = '0';
		$this->show_message(str_replace(array('[w]','[x]','[y]','[z]'),array($tot,$del_count,$ed,$found),USRLAN_155).$text);
	}
	*/
// ------- FIXME  Prune Users move to cron --------------
// if (isset ($_POST['prune']))
// {
	// $e107cache->clear('online_menu_member_total');
	// $e107cache->clear('online_menu_member_newest');
	// $text = USRLAN_56.' ';
	// $bantype = $_POST['prune_type'];
	// if ($bantype == 30)
		// // older than 30 days.
	// {
		// $bantype = 2;
		// $ins = " AND user_join < ".strtotime("-30 days");
	// }
	// if ($sql->db_Select("user","user_id, user_name","user_ban= {$bantype}".$ins))
	// {
		// $uList = $sql->db_getList();
		// foreach ($uList as $u)
		// {
			// $text .= $u['user_name']." ";
			// $sql->db_Delete("user","user_id='{$u['user_id']}' ");
			// $sql->db_Delete("user_extended","user_extended_id='{$u['user_id']}' ");
		// }
		// e107::getLog()->add('USET_04',str_replace(array('[x]','--TYPE--'),array(count($uList),$bantype),USRLAN_160),E_LOG_INFORMATIVE);
	// }
	// $ns->tablerender(USRLAN_57,"<div style='text-align:center'><b>".$text."</b></div>");
	// unset ($text);
// }
}


class users_admin_form_ui extends e_admin_form_ui
{


	function user_admin($curval,$mode, $att)
	{
		$att['type'] = 'boolean';

//		$uid = $this->getController()->getModel()->get('user_id');
		$perms = $this->getController()->getModel()->get('user_perms');

		if($mode == 'filter' && getperms('3'))
		{
			return array(0=>LAN_NO, '1'=>LAN_YES);
		}

		if($mode == 'read'  || (str_replace(".","",$perms) == '0') || !getperms('3'))
		{
			return $this->renderValue('user_admin',$curval,$att);
		}

		if($mode == 'write')
		{
			return $this->renderElement('user_admin',$curval,$att);
		}



	}


	function user_extended($curval,$mode, $att)
	{
		if($mode == 'read')
		{
			$field = $att['field'];

			if($this->getController()->getAction() == 'list')
			{
				$data =  $this->getController()->getListModel()->get($field); // ($att['field']);
			}
			else
			{
				$data =  $this->getController()->getModel()->get($field); // ($att['field']);
			}



			return e107::getUserExt()->renderValue($data, $att['ueType']);


		}
		if($mode == 'write')
		{
			// e107::getUserExt()->user_extended_edit
		//	return 'hello';
			$field = $att['field'];
			/** @var users_admin_ui $controller */
			$controller = $this->getController();
			$extData = $controller->getExtended();
			$extData[$field]['user_extended_struct_required'] = 0;

			return e107::getUserExt()->user_extended_edit($extData[$field],$curval);

		//	return print_a($att,true);
		}


	}





	function user_perms($curval,$mode)
	{
		$perms = $this->getController()->getModel()->get('user_perms');
		$uid = $this->getController()->getModel()->get('user_id');

		if($mode == 'read' || (str_replace(".","",$perms) == '0' && $uid == USERID) || !getperms('3'))
		{
			return e107::getUserPerms()->renderPerms($curval,$uid);
		}
		if($mode == 'write')
		{
			$prm = e107::getUserPerms();
			$text = "<a class='e-expandit' href='#perms'>".USRLAN_221."</a>";
			$text .= "<div id='perms' style='display:none'>". $prm->renderPermTable('grouped',$curval).'</div>';				
			return $text;
		}
			
		
	}
	
	function user_password($curval,$mode)
	{
		if($mode == 'read')
		{
			if(empty($curval))
			{
				return "No password!";	
			}

			// if(getperms('0'))
			{

				$type = e107::getUserSession()->getHashType($curval, 'array');
				$num = $type[0];

				$styles= array(0=>'label-danger',1=>'label-warning', 3=>'label-success');

				return "<span class='label label-password ".$styles[$num]."'>".$type[1]."</span>";
			}
		}
		if($mode == 'write')
		{
			$fieldName = 'user_password_'. $this->getController()->getId();

			return $this->password($fieldName, '', 128, array('size' => 50, 'class' => 'tbox e-password', 'placeholder' => USRLAN_251, 'generate' => 1, 'strength' => 1, 'required'=>0, 'autocomplete'=>'new-password'));
		}
			
		
	}
	
	
	
	
	
	
	function user_ban($curval,$mode)
	{
		$bo = array(
			'<span class="label label-success label-status">'.LAN_ACTIVE.'</span>',
			"<span class='label label-important label-danger label-status'>".LAN_BANNED."</span>",
			"<span class='label label-default label-status'>".LAN_NOTVERIFIED."</span>",
			"<span class='label label-info label-status'>".LAN_BOUNCED."</span>",
			"<span class='label label-important label-danger label-status'>".USRLAN_56."</span>", // Deleted
		);
		
		if($mode == 'filter' || $mode == 'batch')
		{
			return 	$bo;
		}

		$perms = $this->getController()->getModel()->get('user_perms');

		if($mode == 'write')
		{

			if(str_replace(".","",$perms) == '0')
			{
				return "<div style='width:120px'>".vartrue($bo[$curval],' ')."</div>";
			}

			return $this->select('user_ban',$bo,$curval);
		}	
			
		return vartrue($bo[$curval],' '); // ($curval == 1) ? ADMIN_TRUE_ICON : '';	
	}	
	
	
	/*
	function user_class($curval,$mode)
		{
					
			$e_userclass 	= new user_class;
			$frm 			= e107::getForm();
			$list 			= $e_userclass->uc_required_class_list("classes");
							 if($mode == 'filter')
			{
				return $list;	
			}
			
			if($mode == 'write') //FIXME userclasses are NOT be saved since they are an array. 
			{		
				return $frm->select('user_class', $list, $curval, 'description=1&multiple=1');
				// return $frm->uc_select('user_class[]', $curval, 'admin,classes', 'description=1&multiple=1');// doesn't work correctly. 	
			}
			
			
			//FIXME TODO - option to append userclass to existing value. 
			if($mode == 'batch')
			{
				//$list['#delete'] = "(clear userclass)"; // special 
				return $list;	
			}
						  $tmp = explode(",",$curval);
			$text = array();
			foreach($tmp as $v)
			{
				$text[] = $list[$v];	
			}
			return implode("<br />",$text); // $list[$curval];
					
		}*/
		
	
	/*
	function user_status($curval,$mode)
	{
	
		$row = $this->getController()->getListModel()->getData();
	
		$text = "";
			if ($row['user_perms'] == "0")
			{
				$text .= "<div style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap'>".LAN_MAINADMIN."</div>";
			}
			else
				if ($row['user_admin'])
				{
					$text .= "<div style='padding-left:3px;padding-right:3px;;text-align:center'><a href='".e_SELF."?main.user_admin.".($id == "desc" ? "asc" : "desc")."'>".LAN_ADMIN."</a></div>";
				}
				else
					if ($row['user_ban'] == 1)
					{
						$text .= "<div style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap'><a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc")."'>".LAN_BANNED."</a></div>";
					}
					else
						if ($row['user_ban'] == 2)
						{
							$text .= "<div class='label' style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap' >".LAN_NOTVERIFIED."</div>";
						}
						else
							if ($row['user_ban'] == 3)
							{
								$text .= "<div style='padding-left:3px;padding-right:3px;text-align:center;white-space:nowrap' >".LAN_BOUNCED."</div>";
							}
							else
							{
								$text .= "&nbsp;";
							}
		
		return $text;
	
		
	}
	*/

	//TODO Reduce to simple edit/delete buttons only Other options included on edit page or available via inline or batch editing. 
	function options($val, $mode) // old drop-down options. 
	{
		$controller = $this->getController();
		
		if($controller->getMode() != 'main' || $controller->getAction() != 'list') return;
		$row = $controller->getListModel()->getData();
		
		if(!getperms('4'))
		{
		//	return; 
		}
	
		
	//	extract($row);

		$user_id = intval($row['user_id']);
		$user_ip = $row['user_ip'];
		$user_admin = $row['user_admin'];

		$head = "<div>

				<input type='hidden' name='userid[{$user_id}]' value='{$user_id}' />
				<input type='hidden' name='userip[{$user_id}]' value='{$user_ip}' />
				<input type='hidden'  class='user-action-hidden' id='user-action-".$user_id."' name='useraction[{$user_id}]' value='' />
				";

		//		<select name='useraction[{$user_id}]' onchange='this.form.submit()' class='e-select tbox' data-placement='left' title='Modify' style='text-align:left;width:75%'>
		//		<option selected='selected' value=''>&nbsp;</option>";


		$opts = array();



		if ($row['user_perms'] != "0")
		{
			// disabled user info <option value='userinfo'>".USRLAN_80."</option>
		//	$text .= "<option value='usersettings'>".LAN_EDIT."</option>";
			$opts['usersettings'] = LAN_EDIT;



			// login/logout As
			if(getperms('0') && !($row['user_admin'] && getperms('0', $row['user_perms'])))
			{
				if(e107::getUser()->getSessionDataAs() == $row['user_id'])
				{
		//		    $text .= "<option value='logoutas'>".sprintf(USRLAN_AS_2, $row['user_name'])."</option>";
				    $opts['logoutas'] = e107::getParser()->lanVars(USRLAN_AS_2, $row['user_name']);
				}
				else
				{
		//		    $text .= "<option value='loginas'>".sprintf(USRLAN_AS_1, $row['user_name'])."</option>";
				    $opts['loginas'] = e107::getParser()->lanVars(USRLAN_AS_1, $row['user_name']);
				}
			}
			switch ($row['user_ban'])
			{
				case 0 :
		//			$text .= "<option value='ban'>".USRLAN_30."</option>\n";
					$opts['ban'] = USRLAN_30;
					break;
				case 1 :
					// Banned user
			//		$text .= "<option value='unban'>".USRLAN_33."</option>\n";
					$opts['unban'] = USRLAN_33;
					break;
				case 2 :
					// Unverified
			/*		$text .= "<option value='ban'>".USRLAN_30."</option>
						<option value='verify'>".USRLAN_32."</option>
						<option value='resend'>".USRLAN_112."</option>
						<option value='test'>".USRLAN_118."</option>";*/

						$opts['ban'] = USRLAN_30;
						$opts['verify'] = USRLAN_32;
						$opts['resend'] = USRLAN_112;
						$opts['test'] = USRLAN_118;
					break;
				case 3 :
					// Bounced
					// FIXME wrong lan for 'reqverify' - USRLAN_181, wrong lan for 'verify' (USRLAN_182), changed to USRLAN_32
				/*	$text .= "<option value='ban'>".USRLAN_30."</option>
						<option value='reqverify'>Make not verified</option>
						<option value='verify'>".USRLAN_32."</option>
						<option value='test'>".USRLAN_118."</option>";
						*/

						$opts['ban']        = USRLAN_30;
						$opts['reqverify'] = "Make not verified";
						$opts['verify']     = USRLAN_32;
						$opts['test']       = USRLAN_118;


					break;
				default :
			}
			if (!$user_admin && !$row['user_ban'] && $row['user_ban'] != 2 && getperms('3'))
			{
		//		$text .= "<option value='admin'>".USRLAN_35."</option>\n";
				$opts['admin'] = USRLAN_35;
			}
			else
				if ($user_admin && $row['user_perms'] != "0" && getperms('3'))
				{
			//		$text .= "<option value='adminperms'>".USRLAN_221."</option>\n";
			//		$text .= "<option value='unadmin'>".USRLAN_34."</option>\n";

					$opts['adminperms'] = USRLAN_221;
					$opts['unadmin']     = USRLAN_34;
				}
		}
		elseif(USERID ===  $user_id ||  $user_id > USERID)
		{
			$opts['usersettings'] = LAN_EDIT;
		}

		if ($row['user_perms'] == "0" && !getperms("0"))
		{
		//	$text .= "";
		}
		elseif ($user_id != USERID || getperms("0"))
		{
		//	$text .= "<option value='userclass'>".USRLAN_36."</option>\n"; // DEPRECATED. inline & batch should be enough. 
		}
		if ($row['user_perms'] != "0")
		{
		//	$text .= "<option value='deluser'>".LAN_DELETE."</option>\n";
			$opts['deldiv'] = 'divider';
			$opts['deluser'] = LAN_DELETE;
		}
		
	//	$foot = "</select>";
	//	$foot = "</div>";

		$btn =  '<div class="btn-group pull-right">

		<button aria-expanded="false" class="btn btn-default btn-secondary btn-user-action dropdown-toggle" data-toggle="dropdown">
		<span class="user-action-indicators" id="user-action-indicator-'.$user_id.'">'.e107::getParser()->toGlyph('fa-cog').'</span>
		<span class="caret"></span>
		</button>
		<ul class="dropdown-menu">

		<!-- dropdown menu links -->
		';
		//<li class="dropdown-header text-right"><strong>'.$row['user_name'].'</strong></li>
		foreach($opts as $k=>$v)
		{
			if($v == 'divider')
			{
				$btn .= '<li class="divider" ></li>';
			}
			else
			{
				$btn .= '<li class="user-action-'.$k.'"><a class="user-action text-right"  data-action-user="'.$user_id.'" data-action-type="'.$k.'" >'.$v.'</a></li>';
			}
		}



		$btn .= '
		</ul></div>';

		if(!empty($opts))
		{
			return $head.$btn;
		}
		else
		{
			return '';
		}
		
		// return ($text) ? $head.$text.$foot . $btn : "";
	}

	
	function RankImageDropdown(&$imgList, $field, $curVal = '')
	{
		$ret = "
		<select class='tbox' name='{$field}'>
		<option value=''>".USRLAN_216."</option>
		";
		foreach ($imgList as $img)
		{
			$sel = ($img == $curVal ? "selected='selected'" : '');
			$ret .= "\n<option {$sel}>{$img}</option>";
		}
		$ret .= '</select>';
		return $ret;
	}
	
	
}


	class users_ranks_ui extends e_admin_ui
	{
		protected $pluginTitle		= ADLAN_36;
		protected $pluginName		= 'user_ranks';
		protected $table			= 'generic';
		protected $pid				= 'gen_id';
		protected $perPage 			= 15;
		protected $listQry			= "SELECT * FROM `#generic` WHERE gen_type='user_rank_data' ";
		protected $listOrder     = " CASE gen_datestamp WHEN 1 THEN 1 WHEN 2 THEN 2 WHEN 3 THEN 3  WHEN 0 THEN 4 END, gen_intdata ";

		protected $fields 		= array (
		    'checkboxes'        =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		    'gen_id' 			=> array ( 'title' => LAN_ID,	 'nolist'=>true,	'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		    'gen_type' 			=> array ( 'title' => LAN_BAN, 	'type' => 'hidden', 'data' => 'str', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => 'value=user_rank_data', 'class' => 'left', 'thclass' => 'left',  ),
		    'gen_ip' 			=> array ( 'title' => USRLAN_208, 'type' => 'text', 'data' => 'str', 'inline'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		    'gen_intdata' 		=> array ( 'title' => USRLAN_209, 'type' => 'text', 'batch'=>false, 'data' => 'int', 'inline'=>true, 'width' => 'auto', 'help' => '', 'readParms' => 'default=-', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),

		    'gen_datestamp' 	=> array ( 'title' => 'Special', 'type' => 'hidden', 'nolist'=>true, 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		    'gen_user_id' 		=> array ( 'title' => USRLAN_210, 'type' => 'boolean', 'batch'=>true, 'data' => 'int', 'inline'=>true, 'width' => '15%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		    //'gen_chardata' 		=> array ( 'title' => LAN_ICON, 'type' => 'dropdown', 'data' => 'str', 'inline'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => array(), 'class' => 'left', 'thclass' => 'left',  ),
		    'gen_chardata' 		=> array ( 'title' => LAN_ICON, 'type' => 'method', 'data' => 'str', 'inline'=>true, 'width' => 'auto', 'help' => '', 'readParms' => array(), 'writeParms' => array(), 'class' => 'left', 'thclass' => 'left',  ),


		    'options'			=> array ( 'title' => LAN_OPTIONS, 'type' =>'method', 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'right last', 'forced' => '1', 'readParms'=>'edit=0'  ),
		);

		protected $fieldpref = array('gen_datestamp', 'gen_type', 'gen_ip', 'gen_intdata', 'gen_user_id', 'gen_chardata');


		// optional
		public function init()
		{
			$tmp = e107::getFile()->get_files(e_IMAGE.'ranks', '.*?\.(png|gif|jpg)');

			$mode = $this->getMode();
			$action = $this->getAction();

			$existing = e107::getDb()->gen("SELECT gen_id FROM #generic WHERE gen_type='user_rank_data' LIMIT 1 ");

			if($mode == 'ranks' && ($action == 'list') && !$existing)
			{
				$this->createDefaultRecords();
			}

			//	$this->addTitle(LAN_USER_RANKS);

			foreach($tmp as $k => $v)
			{
				$id = $v['fname'];
				$this->fields['gen_chardata']['writeParms']['optArray'][$id] = $v['fname'];
			}

			unset($tmp);
		//	natsort($imageList);
		}

		public function afterDelete($data, $id, $deleted_check)
		{
			e107::getCache()->clear_sys('nomd5_user_ranks');
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			e107::getCache()->clear_sys('nomd5_user_ranks');
		}

		private function createDefaultRecords()
		{

			$tmp = array();
			$tmp['_FIELD_TYPES']['gen_datestamp'] = 'int';
			$tmp['_FIELD_TYPES']['gen_ip'] = 'todb';
			$tmp['_FIELD_TYPES']['gen_user_id'] = 'int';
			$tmp['_FIELD_TYPES']['gen_chardata'] = 'todb';
			$tmp['_FIELD_TYPES']['gen_intdata'] = 'int';


			//Add main site admin info
			$tmp['data']['gen_datestamp']   = 1;
			$tmp['data']['gen_type']        = 'user_rank_data';
			$tmp['data']['gen_ip']          = LAN_MAINADMIN;
			$tmp['data']['gen_user_id']     = 1;
			$tmp['data']['gen_chardata']    = 'English_main_admin.png';
			$tmp['data']['gen_intdata']     = 0;
			e107::getDb()->insert('generic',$tmp);
			unset ($tmp['data']);


			//Add site admin info
			$tmp['data']['gen_type']        = 'user_rank_data';
			$tmp['data']['gen_datestamp']   = 2;
			$tmp['data']['gen_ip']          = LAN_ADMIN;
			$tmp['data']['gen_user_id']     = 1;
			$tmp['data']['gen_chardata']    = 'English_admin.png';
			$tmp['data']['gen_intdata']     = 0;


			e107::getDb()->insert('generic', $tmp);

			for($i=1; $i < 11; $i++)
			{
				unset ($tmp['data']);
				$tmp['data']['gen_type']        = 'user_rank_data';
				$tmp['data']['gen_datestamp']   = 0;
				$tmp['data']['gen_ip']          = "Level ".$i;
				$tmp['data']['gen_user_id']     = 0;
				$tmp['data']['gen_chardata']    = "lev".$i.".png";
				$tmp['data']['gen_intdata']     = ($i * 150);

				e107::getDb()->insert('generic', $tmp);
			}



		}

	}



	class users_ranks_ui_form extends e_admin_form_ui
	{
		// Override the default Options field.
		function options($parms, $value, $id, $attributes)
		{

			if($attributes['mode'] == 'read')
			{
				parse_str(str_replace('&amp;', '&', e_QUERY), $query);
				$query['action'] = 'edit';
				$query['id'] = $id;
				$query = http_build_query($query, null, '&amp;');

				$text = "<a href='".e_SELF."?{$query}' class='btn btn-default' title='".LAN_EDIT."' data-toggle='tooltip' data-placement='left'>
						".ADMIN_EDIT_ICON."</a>";

				$special = $this->getController()->getListModel()->get('gen_datestamp');

				if($special == 0)
				{
					$text .= $this->submit_image('menu_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'action delete btn btn-default'));
				}

				return $text;
			}
		}

		function gen_chardata($curVal, $mode, $parms=null)
		{
			switch($mode)
			{
				case 'read':
					return '<img src="'.e_IMAGE.'ranks/'.$curVal.'"/><br/>'.$curVal;
					break;

				case 'write':
					$opts = $this->getController()->getFields()['gen_chardata']['writeParms']['optArray'];
					return e107::getForm()->select('gen_chardata', $opts, $curVal);

				case 'filter':
				case 'batch':
					return null;
					break;

			}
		}


	}



new users_admin();
require_once ('auth.php');

e107::getAdminUI()->runPage();
 
require_once ("footer.php");
