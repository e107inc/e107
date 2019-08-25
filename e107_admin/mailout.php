<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Mailout
 *
*/

/*
Features:
1. Additional sources of email addresses for mailouts can be provided via plugins, and can be enabled via the mailout preferences page
2. Both list of email recipients and the email are separately stored in the DB using a documented interface (allows alternative creation/mailout routines)
		- see mail_manager_class.php
3. Can specify qmail in the sendmail path


$pref['mailout_enabled'][plugin_path] - array of flags determining which mailers are active


Extra mailout address handlers - these provide email addresses
------------------------------
1. The handler is called 'e_mailout.php' in the plugin directory.
2. Mailout options includes a facility to enable the individual handlers
3. Certain variables may be defined at load time to determine whether loading is exclusive or supplementary
4. Interface is implemented as a class, which must be called 'plugin_path_mailout'
5. see mailout_class.php in the handlers directory for an example (also simpler examples in newsletter and event calendar plugins)

*/


/*
Valid actions ($_GET['mode']):
	'prefs' - Edit options

	'makemail' - Create an email for use as a template, or to send

	'saved' - email templates saved (was 'list')

	'sent' - list emails where sending process complete (was 'mailouts')
	'pending' - list emails in queue or being sent

	'maildelete' - delete email whose 'handle' is in $mailID - shows confirmation page
	'maildeleteconfirm' - does it

	'edit' - edit email whose 'handle' is in $mailID

	'detail' - show all the target recipients of a specific email

	'resend' - resend failures on a specific list

	'debug' - not currently used; may be useful to list other info
 
Valid subparameters (where required):
	$_GET['m'] - id of mail info in db
	$_GET['t'] - id of target info in db
*/
// header('Content-Encoding: none'); // turn off gzip. 
require_once('../class2.php');

if (!getperms('W'))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('users', true); 
e107::coreLan('mailout', true); 

require_once(e_HANDLER.'ren_help.php');

require_once(e_HANDLER.'userclass_class.php');
// require_once(e_HANDLER.'mailout_class.php');			// Class handler for core mailout functions
require_once(e_HANDLER.'mailout_admin_class.php');		// Admin tasks handler
require_once(e_HANDLER.'mail_manager_class.php');		// Mail DB API


/**
 * Display Progress-bar of real-time mail-out. 
 * @return 
 */
function sendProgress($id)
{
	
//	return rand(92,100);
	
	$pref = e107::getPref();
	
	ob_start();
	
	$perAjaxHit = e107::getConfig()->get('mail_pause',1);
	
	$mailManager = new e107MailManager();
	$mailManager->doEmailTask($perAjaxHit);

	$sqld = e107::getDb('progress');
	
	$sqld->select("mail_content","mail_total_count,mail_togo_count,mail_sent_count,mail_fail_count","mail_source_id= ".intval($id) );
    $row = $sqld->fetch();
  
 	$rand 	= ($row['mail_sent_count'] + $row['mail_fail_count']);
	$total 	= ($row['mail_total_count']);

	$errors = ob_get_clean();
	
	$errors .= " id=".$id;
	
	
	e107::getMessage()->addDebug($errors);

	
	
	$inc = round(($rand / $total) * 100);
	
	$errors .= " inc=".$inc;
	
	file_put_contents(e_LOG.'send-mail-progress.txt',$errors);
	
	e107::getMessage()->addDebug("Returned: ".$inc);
	
	return $inc;

	 
}

	if(!empty($_GET['iframe']))
	{
		define('e_IFRAME', true);
	}

if(e_AJAX_REQUEST)
{
	$id = intval($_GET['mode']);
	echo sendProgress($id);
	exit;

}
		
	

if(vartrue($_GET['mode']) == "progress")
{
//	session_write_close();
//	sendProgress();
//	exit;
}


$mes = e107::getMessage();
$tp = e107::getParser();
/*
if($_GET['mode']=="process")
{
	session_write_close(); // allow other scripts to run in parallel. 
	header('Content-Encoding: none');
	ignore_user_abort(true);
	set_time_limit(0);

	header("Content-Length: $size");
	header('Connection: close');
	
	$mailManager = new e107MailManager();
	$mailManager->doEmailTask(999999);	
	echo "Completed Mailout ID: ".$_GET['id'];
	exit;
}
*/






class mailout_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'mailout_main_ui',
			'path' 			=> null,
			'ui' 			=> 'mailout_admin_form_ui',
			'uipath' 		=> null
		),
		'saved'		=> array(
			'controller' 	=> 'mailout_main_ui',
			'path' 			=> null,
			'ui' 			=> 'mailout_admin_form_ui',
			'uipath' 		=> null
		),
		'pending'		=> array(
			'controller' 	=> 'mailout_main_ui',
			'path' 			=> null,
			'ui' 			=> 'mailout_admin_form_ui',
			'uipath' 		=> null
		),
		'held'		=> array(
			'controller' 	=> 'mailout_main_ui',
			'path' 			=> null,
			'ui' 			=> 'mailout_admin_form_ui',
			'uipath' 		=> null
		),
		'sent'		=> array(
			'controller' 	=> 'mailout_main_ui',
			'path' 			=> null,
			'ui' 			=> 'mailout_admin_form_ui',
			'uipath' 		=> null
		),
		'prefs'		=> array(
			'controller' 	=> 'mailout_main_ui',
			'path' 			=> null,
			'ui' 			=> 'mailout_admin_form_ui',
			'uipath' 		=> null
		),
		'maint'		=> array(
			'controller' 	=> 'mailout_main_ui',
			'path' 			=> null,
			'ui' 			=> 'mailout_admin_form_ui',
			'uipath' 		=> null
		),
		'recipients'	=> array(
			'controller'	=> 'mailout_recipients_ui',
			'path'			=> null,
			'ui'			=> 'mailout_recipients_form_ui',
			'uipath'		=> null
		)
					
	);	

	protected $adminMenu = array(
	//	'makemail/makemail'		=> array('caption'=> LAN_MAILOUT_190, 	'perm' => 'W', 'url'=>e_SELF),
		'main/list'			=> array('caption'=> LAN_MANAGE, 		'perm'=>  'W'),
		'main/create'		=> array('caption'=> LAN_CREATE, 	'perm' => 'W'),
	
		'recipients/list'	=> array('caption'=> LAN_MAILOUT_173, 		'perm' => 'W'),		
	//	'main/send'			=> array('caption'=> "Send", 			'perm' => 'W'),
		'other' 			=> array('divider'=> true),
	//	'saved/list'		=> array('caption'=> LAN_MAILOUT_191, 	'perm' => 'W'),
		'pending/list'		=> array('caption'=> LAN_MAILOUT_193, 	'perm' => 'W'),
		'held/list'			=> array('caption'=> LAN_MAILOUT_194, 	'perm' => 'W'),
		'sent/list'			=> array('caption'=> LAN_MAILOUT_192, 	'perm' => 'W'),
		'other2' 			=> array('divider'=> true),
		'prefs/prefs' 		=> array('caption'=> LAN_PREFS, 		'perm' => '0'),

		'maint/maint'		=> array('caption'=> ADLAN_40, 			'perm' => '0'),
		'main/templates'	=> array('caption'=> LAN_MAILOUT_262, 'perm' => '0'),
	);



	protected $adminMenuAliases = array(
		'main/send'	=> 'main/create',	
	);

	protected $adminMenuIcon = 'e-mail-24';
	
	protected $menuTitle = LAN_MAILOUT_15;


	function init()
	{
		$mailer = e107::getPref('bulkmailer');

		if($mailer === 'smtp' )
		{
			$this->adminMenu['other3'] =   array('divider'=> true);
			$this->adminMenu['prefs/test'] =array('caption'=> LAN_MAILOUT_270, 'perm' => '0'); //TODO LAN
		}

	}
}

class mailout_main_ui extends e_admin_ui
{
	
		//TODO Move to Class above.
		protected $pluginTitle		= LAN_MAILOUT_15;
		protected $pluginName		= LAN_MAILOUT_15;
		protected $table			= "mail_content";

	//	protected $listQry			= null;


	//	protected $editQry			= "SELECT * FROM #mail_content WHERE cust_id = {ID}";

		protected $pid 					= "mail_source_id";
		protected $perPage 				= 10;
		protected $listOrder			= "mail_source_id desc";

		protected $batchDelete 			= true;	
		protected $batchCopy 			= true;	
		
		protected $tabs					= array(LAN_BASIC,LAN_ADVANCED);

		protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null, 		'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'mail_source_id' 		=> array('title' => LAN_MAILOUT_137, 'width' =>'5%', 'thclass' => 'center', 'class'=>'center', 'forced' => TRUE),
			
			'mail_selectors'		=> array('title' => LAN_MAILOUT_03, 'type'=>'method', 'data'=>false, 'nolist' => true, 'writeParms'=>'nolabel=0'),
			'mail_title' 			=> array('title' => LAN_TITLE, 'type'=>'text', 'forced' => TRUE, 'data'=>'str', 'inline'=>true, 'writeParms'=>'size=xxlarge&required=1', 'help'=>''),
			'mail_sender_name'		=> array('title' => LAN_MAILOUT_150, 'type'=>'method', 'data'=>false),
			'mail_sender_email' 	=> array('title' => LAN_MAILOUT_149,'type'=>'method','data'=>false),
			'mail_copy_to'			=> array('title' => LAN_MAILOUT_151,'tab'=>1, 'type'=>'method','data'=>false),
			'mail_bcopy_to'			=> array('title' => LAN_MAILOUT_152,'tab'=>1, 'type'=>'method','data'=>false),	
			'mail_subject' 			=> array('title' => LAN_MAILOUT_06, 'type'=>'text', 'forced' => TRUE,'data'=>'str', 'inline'=>true, 'writeParms'=>'size=xxlarge&required=1'),
			'mail_content_status' 	=> array('title' => LAN_MAILOUT_136, 'tab'=>1, 'type'=> 'dropdown', 'data'=>'int', 'filter'=>false, 'inline'=>false, 'thclass' => 'left', 'class'=>'left'),
			'mail_total_count' 		=> array('title' => LAN_MAILOUT_263, 'noedit'=>true, 'type'=>'number'),
			'mail_sent_count' 		=> array('title' => LAN_MAILOUT_82, 'noedit'=>true, 'type'=>'number'),
			'mail_togo_count' 		=> array('title' => LAN_MAILOUT_83, 'noedit'=>true, 'type'=>'number'),
		
			'mail_fail_count' 		=> array('title' => LAN_MAILOUT_128, 'noedit'=>true, 'type'=>'number'),
			'mail_bounce_count' 	=> array('title' => LAN_MAILOUT_144, 'noedit'=>true, 'type'=>'number'),
			'mail_start_send' 		=> array('title' => LAN_MAILOUT_131,'noedit'=>true,  'type'=>'datestamp'),
			'mail_end_send' 		=> array('title' => LAN_MAILOUT_132, 'noedit'=>true,  'type'=>'datestamp'),
			'mail_create_date' 		=> array('title' => LAN_MAILOUT_130, 'type'=>null, 'noedit'=>true, 'data'=>'int'),
			'mail_creator' 			=> array('title' => LAN_MAILOUT_85, 'type'=>null, 'noedit'=>true, 'data'=>'int'),
			'mail_create_app' 		=> array('title' => LAN_SOURCE, 'type'=>null, 'noedit'=>true,'data'=>'str'),
			'mail_e107_priority' 	=> array('title' => LAN_MAILOUT_134, 'noedit'=>true),
			'mail_notify_complete' => array('title' => LAN_MAILOUT_243,  'noedit'=>true, 'nolist' => true),
			'mail_last_date' 		=> array('title' => LAN_MAILOUT_129, 'noedit'=>true, 'type'=>'int', 'proc' => 'sdatetime'),
			'mail_attach'			=> array('title' => LAN_MAILOUT_153, 'tab'=>1, 'type'=>'method','data'=>false),
			'mail_include_images' 	=> array('title' => LAN_MAILOUT_224, 'tab'=>1, 'type'=>'boolean','data'=>false, 'proc' => 'yesno'),
			'mail_send_style'		=> array('title' => LAN_MAILOUT_154,'type'=>'method','data'=>false),
			'mail_media' 			=> array('title' => LAN_MAILOUT_264, 'type' => 'images', 'data' => 'array', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => 'video=1', 'class' => 'center', 'thclass' => 'center',  ),
	 
			'mail_body' 			=> array('title' => LAN_MAILOUT_100, 'type'=>'bbarea', 'proc' => 'trunc200'),
			'mail_body_templated' 	=> array('title' => LAN_MAILOUT_257, 'noedit'=>true, 'proc' => 'chars'),
			'mail_other' 			=> array('title' => LAN_MAILOUT_84, 'type'=>null, 'noedit'=>true, 'data'=>'array', 'nolist'=>true),
	  	
			'options' 				=> array('title' => LAN_OPTIONS, 'type'=>'method', 'width'=>'10%', 'forced' => TRUE)
		
	);




		protected $fieldpref = array('checkboxes', 'mail_source_id', 'mail_title', 'mail_subject', 'mail_content_status', 'options');


		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array(
		//	'after_submit'	   			=> array('title'=> 'Custom After Submit Text:', 'type'=>'bbarea')
		//	'submit_question'	   		=> array('title'=> 'Allow submitting of Questions by:', 'type'=>'userclass'),
		//	'classic_look'				=> array('title'=> 'Use Classic Layout', 'type'=>'boolean')
		);
		
		
			

	public $mailAdmin = null;
	
	private	$selectFields = array('email_to', 
		'extended_1_name','extended_1_value',
		'extended_2_name', 'extended_2_value',
		'user_search_name', 'user_search_value',
		'last_visit_match', 'last_visit_date'
	);
	
	
	private $mailOtherFields = array(
		'mail_sender_email',
		'mail_sender_name',
		'mail_copy_to',
		'mail_bcopy_to',
		'mail_attach',
		'mail_send_style',			// HTML, text, template name etc
		'mail_selectors',			// Only used internally
		'mail_include_images',			// Used to determine whether to embed images, or link to them
		'mail_body_alt'	,			// If non-empty, use for alternate email text (generally the 'plain text' alternative)
		'mail_overrides'
	);


	function afterDelete($del_data,$id)
	{
		$result = e107::getDb()->delete('mail_recipients', 'mail_detail_id = '.intval($id));
	//	$this->getModel()->addMessageDebug("Deleted ".$result." recipients from the deleted email #".$id);
	//	e107::getMessage()->addDebug("Deleted ".$result." recipients from the deleted email #".$id, 'default', true);

	}


	function init()
	{
		$action = varset($_GET['mode'], 'main');

		$this->mailAdmin = new mailoutAdminClass($action);	




		if($_GET['action'] == 'preview')
		{
			$tp = e107::getParser();
			echo $this->previewPage($tp->filter($_GET['id']), $tp->filter($_GET['user']));
			exit;
		}
		
		if ($this->mailAdmin->loadMailHandlers() == 0)
		{
			e107::getMessage()->addDebug('No mail handlers loaded!!');
			
		}	
		
		/*
			define('MAIL_STATUS_SENT', 0);			// Mail sent. Email handler happy, but may have bounced (or may be yet to bounce)
			define('MAIL_STATUS_BOUNCED', 1);
			define('MAIL_STATUS_CANCELLED', 2);
			define('MAIL_STATUS_PARTIAL', 3);		// A run which was abandoned - errors, out of time etc
			define('MAIL_STATUS_FAILED', 5);		// Failure on initial send - rejected by selected email handler
													// This must be the numerically highest 'processing complete' code
			define('MAIL_STATUS_PENDING', 10);		// Mail which is in the sending list (even if outside valid sending window)
													// This must be the numerically lowest 'not sent' code
													// E107_EMAIL_MAX_TRIES values used in here for retry counting
			define('MAIL_STATUS_MAX_ACTIVE', 19);	// Highest allowable 'not sent or processed' code
			define('MAIL_STATUS_SAVED', 20);		// Identifies an email which is just saved (or in process of update)
			define('MAIL_STATUS_HELD',21);			// Held pending release
			define('MAIL_STATUS_TEMP', 22);			// Tags entries which aren't yet in any list
		*/
	
		$types = array(10=>LAN_MAILOUT_265,20=>LAN_SAVED, 21=>LAN_MAILOUT_217, 0=>LAN_MAILOUT_211, 1=>LAN_MAILOUT_213, 2=>LAN_MAILOUT_218, 3=>LAN_MAILOUT_219, 5=>LAN_MAILOUT_212,  19 => LAN_MAILOUT_266,  22=>"Temp");
		
		
		$qr = array('saved'=>20,'pending'=>10,'held'=>21,'sent'=>0);
		
		if($action !== 'main'  )
		{
			$this->listQry	= "SELECT * FROM `#mail_content` WHERE mail_content_status =  ".varset($qr[$action],20);
		}
		else
		{
			$this->listQry	= "SELECT * FROM `#mail_content` WHERE (mail_content_status =  ".MAIL_STATUS_TEMP ." OR mail_content_status = ".MAIL_STATUS_SAVED.")";
		}
		

		if($action == 'sent' || $action == 'pending' || $action == 'held')
		{
			$this->fieldpref = array('checkboxes', 'mail_source_id', 'mail_title', 'mail_subject','mail_total_count', 'mail_togo_count', 'mail_sent_count', 'mail_fail_count', 'mail_bounce_count', 'options');	
		}
				
		$this->fields['mail_content_status']['writeParms'] = $types; 
		
		$this->processSendActions();
		
		$mes = e107::getMessage();
		
		if (getperms('0'))
		{
			if (isset($_POST['testemail'])) 
			{		
				$this->sendTestEmail(); 	//	Send test email - uses standard 'single email' handler
			}
			if(!empty($_POST['send_bounce_test']))
			{
				$this->sendTestBounce();	
			}
			elseif (isset($_POST['updateprefs']))
			{
				$this->saveMailPrefs($mes); // TODO check if functional, $emessage -> $mes
			}
			elseif(!empty($_POST['DKIM_generate']))
			{
				$this->generateDKIM();
			}
		}
		
		
		
	}


	/**
	 *
	 * https://www.mail-tester.com/spf-dkim-check
	 * http://dkimvalidator.com/
	 * @return bool
	 */
	private function generateDKIM()
	{
		$privatekeyfile = e_SYSTEM.'dkim_private.key';
		$tp = e107::getParser();


		if(file_exists($privatekeyfile))
		{
			e107::getMessage()->addInfo("DKIM keys already exists (".$privatekeyfile.")");

			$text = $this->getDKIMPublicKey();
			e107::getMessage()->addInfo("Add the following to your ".e_DOMAIN." DNS Zone records:".print_a($text,true));
			e107::getMessage()->addInfo("Consider testing it using this website: http://dkimvalidator.com");

			return false;
		}

		$keyLength = 1024; // Any higher and cPanel < 11.50 will refuse it.

		$pk = openssl_pkey_new(
			array(
				"digest_alg" => "sha1",
				'private_key_bits' => $keyLength, //  (2048 bits is the recommended minimum key length - gmail won't accept less than 1024 bits)
				'private_key_type' => OPENSSL_KEYTYPE_RSA
			)
		);

		openssl_pkey_export_to_file($pk, $privatekeyfile);

	//	$contents = file_get_contents($privatekeyfile);

		$tmp = openssl_pkey_get_details($pk);

		$pubKey = $tmp['key'];

		file_put_contents( e_SYSTEM."dkim_public.key",$pubKey);

		//	e107::getMessage()->addInfo(nl2br($pubKey));

		$pubString = str_replace(array('-----BEGIN PUBLIC KEY-----','-----END PUBLIC KEY-----',"\n"),"",$pubKey);

	//	$dnsEntry = 'phpmailer._domainkey	IN	TXT	"v=DKIM1; k=rsa; g=*; s=email; h=sha1; t=s; p=[x];"';
		$dnsEntry = 'phpmailer._domainkey	IN	TXT	"v=DKIM1; k=rsa; p=[x];"';


		$text = $tp->lanVars($dnsEntry, $pubString);
		e107::getMessage()->addInfo("Add the following ".$keyLength." bit key to your ".e_DOMAIN." DNS Zone records:".print_a($text,true));
		e107::getMessage()->addInfo("Consider testing it using this website: http://dkimvalidator.com");
	}


	private function getDKIMPublicKey()
	{

		$pubKey = file_get_contents( e_SYSTEM."dkim_public.key");

		$pubString = str_replace(array('-----BEGIN PUBLIC KEY-----','-----END PUBLIC KEY-----',"\n"),"",$pubKey);

		//	$dnsEntry = 'phpmailer._domainkey	IN	TXT	"v=DKIM1; k=rsa; g=*; s=email; h=sha1; t=s; p=[x];"';
		$dnsEntry = 'phpmailer._domainkey	IN	TXT	"v=DKIM1; k=rsa; p=[x];"';

		return e107::getParser()->lanVars($dnsEntry, $pubString);
	}



	private function sendTestBounce()
	{
		$mes = e107::getMessage();
		$pref = e107::getPref();
		
		$sendto = $pref['mail_bounce_email'];
		
		$eml = array('subject'=>'Test Bounce',	'body'	=> 'Test Bounce Email address','e107_header'=>99999999, 'extra_header' => 'X-Bounce-Test: true');
		
		if(e107::getEmail()->sendEmail($sendto, 'Bounce handler', $eml))
		{
			$mes->addSuccess('Test Bounce sent to '.$sendto);
		}
		else
		{
			$mes->addError('Failed Bounce email sent to '.$sendto);	
		}	
		
		
	}


	private function sendTestEmail()
	{
		$mes = e107::getMessage();
		$pref = e107::getPref();
		
		if(trim($_POST['testaddress']) == '')
		{
			$mes->addError(LAN_MAILOUT_19);
			return null;
		}

		if(empty($pref['bulkmailer']))
		{
			$pref['bulkmailer'] = $pref['mailer'];
		}

		$add = ($pref['bulkmailer']) ? " (".strtoupper($pref['bulkmailer']).") " : ' (PHP)';

		if($pref['bulkmailer'] == 'smtp')
		{
			$add .= "Port: ".varset($pref['smtp_port'],25);
			$add .= " - ".str_replace("secure=", "", $pref['smtp_options']);
		}

		$sendto = trim($_POST['testaddress']);

		$subjectSitename = ($_POST['testtemplate'] == 'textonly') ? SITENAME : '';
			
		$eml = array(
			'e107_header'	=> USERID,
			'subject'		=> LAN_MAILOUT_113." ".$subjectSitename.$add,
			'body'			=> str_replace("[br]", "\n", LAN_MAILOUT_114),
			'template'		=> vartrue($_POST['testtemplate'],null),
			'shortcodes'	=> $this->getExampleShortcodes(),
			'media'			=>  array(
					0 => array('path' => '{e_PLUGIN}gallery/images/butterfly.jpg'),
					1 => array('path' => 'h-v880sXEOQ.youtube'),
					2 => array('path' => '{e_PLUGIN}gallery/images/horse.jpg'),
					3 => array('path' => '{e_PLUGIN}gallery/images/butterfly.jpg'),
					4 => array('path' => '{e_PLUGIN}gallery/images/horse.jpg'),
				)
			);
			
		if(E107_DEBUG_LEVEL > 0)
		{
			$eml['SMTPDebug'] = true;
		}


		$options = array('mailer'=>$pref['bulkmailer']);

			
		if (e107::getEmail($options)->sendEmail($sendto, LAN_MAILOUT_189, $eml) !== true)
		{
			$mes->addError(($pref['bulkmailer'] == 'smtp')  ? LAN_MAILOUT_67 : LAN_MAILOUT_106);
		}
		else
		{
			$mes->addSuccess(LAN_MAILOUT_81. ' ('.$sendto.')');
			e107::getAdminLog()->log_event('MAIL_01', $sendto, E_LOG_INFORMATIVE,'');
		}

		
		
	}


	public function beforeCreate($new_data, $old_data)
	{
		$ret = $this->processData($new_data);
		
		$ret['mail_create_date'] 	= time();
		$ret['mail_creator'] 		= USERID;	
		$ret['mail_create_app'] 	= 'core';
		$ret['mail_content_status'] = MAIL_STATUS_TEMP; 
		
		return $ret;	

	}
	
	
	public function beforeUpdate($new_data, $old_data, $id)
	{
		$ret = $this->processData($new_data);
		
		$ret['mail_content_status'] = MAIL_STATUS_TEMP; 

		return $ret;
	}
	
	
	function afterCopy($firstInsert, $copied)
	{
		$num = array();
		$count = 0; 
		foreach($copied as $tmp)
		{
			$num[] = ($firstInsert + $count);
			$count ++; 	
		} 
		
		if(!empty($firstInsert))
		{
			$update = array(
				'mail_content_status'	=> MAIL_STATUS_TEMP,
				'mail_total_count'		=> 0,
				'mail_togo_count'		=> 0,
				'mail_sent_count'		=> 0,
				'mail_fail_count'		=> 0,
				'mail_bounce_count'		=> 0,
				'mail_start_send'		=> '',
				'mail_end_send'			=> '',
				'mail_create_date'		=> time(),
				'WHERE'					=> "mail_source_id IN (".implode(",",$num).")" // FIXME Currently modifies the original instead of the copy. 
			);

			if(!e107::getDb()->update('mail_content',$update))
			{
				e107::getMessage()->addDebug(print_a($update,true));	
			}
			
			
		}

	}
	
	
	private function processSendActions()
	{
		
		if((vartrue($_POST['email_send']) || vartrue($_POST['email_hold']) || vartrue($_POST['email_cancel'])) && !vartrue($_POST['email_id']))
		{
			e107::getMessage()->addError("No Message ID submitted");
			return;
		}
		
		$id = intval($_POST['email_id']);
				
		if(vartrue($_POST['email_send']))
		{
			$this->emailSend($id);		
		}	
		
		if(vartrue($_POST['email_hold']))
		{
			$this->emailHold($id);		
		}	
		
		if(vartrue($_POST['email_cancel']))
		{
			$this->emailCancel($id);			
		}		
			
	}
		

	private function emailSendNow($id)
	{
	
	}
	
	
	
	private function emailSend($mailId)
	{
		$log 		= e107::getAdminLog();	
			
		$notify 	= isset($_POST['mail_notify_complete']) ? 3 : 2;
		$first 		= 0;
		$last 		= 0;		// Set defaults for earliest and latest send times.
	
		
		
		if (isset($_POST['mail_earliest_time']))
		{
			$first = e107::getDateConvert()->decodeDateTime($_POST['mail_earliest_time'], 'datetime', CORE_DATE_ORDER, FALSE);
		}
		if (isset($_POST['mail_latest_time']))
		{
			$last = e107::getDateConvert()->decodeDateTime($_POST['mail_earliest_time'], 'datetime', CORE_DATE_ORDER, TRUE);
		}

		if ($this->mailAdmin->activateEmail($mailId, FALSE, $notify, $first, $last))
		{
			e107::getMessage()->addSuccess(LAN_MAILOUT_185);
			$log->log_event('MAIL_06','ID: '.$mailId,E_LOG_INFORMATIVE,'');
		}
		else
		{
			$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_188);
		}
	}
	
	
	
	private function emailHold($mailId)
	{
		if ($this->mailAdmin->activateEmail($mailId, TRUE))
		{
			e107::getMessage()->addSuccess(str_replace('[x]', $mailId, LAN_MAILOUT_187));
		}
		else
		{
			$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_166);
		}
	}
	
	
	
	private function emailCancel($mailId)
	{
		if ($this->mailAdmin->cancelEmail($mailId))
		{
			e107::getMessage()->addSuccess(str_replace('[x]', $mailId, LAN_MAILOUT_220));
		}
		else
		{
			$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_221);
		}
	}	
	

	/**
	 * Preview the Email. 
	 */
	function previewPage($id='', $user=null)
	{
		
		if(is_numeric($id))
		{
			$mailData = e107::getDb()->retrieve('mail_content','*','mail_source_id='.intval($id)." LIMIT 1");
			
			$shortcodes = array(
				'USERNAME'          => 'John Example',
			    'DISPLAYNAME'       => 'John Example',
			    'USERID'            => '555',
				'LOGINNAME'         => 'johnE',
			    'ACTIVATION_LINK'   => SITEURL.'signup.php?testing-activation',
			    'PASSWORD'          => 'MyPass123',
			    'NEWSLETTER'        => SITEURL."newsletter/?id=example1234567",
			    'UNSUBSCRIBE'       => SITEURL."unsubscribe/?id=example1234567"
			);

			if(!empty($user))
			{
				$userData = e107::getDb()->retrieve('mail_recipients','*', 'mail_detail_id = '.intval($id).' AND mail_recipient_id = '.intval($user).' LIMIT 1');
				$shortcodes = e107::unserialize(stripslashes($userData['mail_target_info']));
			}

			if(!isset($shortcodes['MAILREF']))
			{
				$shortcodes['MAILREF'] =  intval($_GET['id']);
			}

						
			$data = $this->mailAdmin->dbToMail($mailData);
	
			$eml = array(
				'subject'		=> $data['mail_subject'],
				'body' 			=> $data['mail_body'],
				'template'		=> $data['mail_send_style'],
				'shortcodes'	=> $shortcodes,
				'media'			=> $data['mail_media'],
			);
			
		//	return print_a($data,true);
		}
		else
		{
		//	e107::coreLan('signup');
			$tp = e107::getParser();

			$eml = array(
				'subject'		=> 'Test Subject',
				'body' 			=> "This is the body text of your email. Included are example media attachments such as images and video thumbnails.<br /></br >Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed aliquam volutpat risus, a efficitur ex dignissim ac. Phasellus ornare tortor est, a elementum orci finibus non! Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce feugiat bibendum venenatis. Ut sit amet purus id magna consequat euismod vitae ac elit. Maecenas rutrum nisi metus, sed pulvinar velit fermentum eu? Aliquam erat volutpat.<br />
									Ut risus massa, consequat et gravida vitae, tincidunt in metus. Nam sodales felis non tortor faucibus lacinia! Integer neque libero, maximus eu cursus nec, fringilla varius erat. Phasellus elementum scelerisque mauris at fermentum. Aliquam erat volutpat. Aliquam sit amet placerat leo, vitae mollis purus. Nulla laoreet nulla pretium risus placerat, a luctus risus pulvinar. Duis ut dolor sed arcu aliquam dictum sed auctor magna. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Etiam eleifend in mi lobortis blandit. Aliquam vestibulum rhoncus vestibulum. Cras metus.",
				'template'		=> $id,
				'shortcodes'	=> $this->getExampleShortcodes(),
				'media'			=>  array(
						0 => array('path' => '{e_PLUGIN}gallery/images/butterfly.jpg'),
						1 => array('path' => 'h-v880sXEOQ.youtube'),
						2 => array('path' => '_j0b9syAuIk.youtube'),
						3 => array('path' => '{e_PLUGIN}gallery/images/horse.jpg'),
						4 => array('path' => '{e_PLUGIN}gallery/images/lake-and-forest.jpg'),
				)
			);	
			
		}
			
		return e107::getEmail()->preview($eml);


	}


	function getExampleShortcodes()
	{
		$tp = e107::getParser();

		return array(

			'USERNAME'			=>'test-username',
			'EMAIL'				=> 'test@email.com',
			'DISPLAYNAME'		=> 'John Example',
			'USERID'			=>'555',
			'MAILREF'			=> '123',
			'NEWSLETTER'		=> SITEURL."newsletter/?id=example1234567",
			'UNSUBSCRIBE'		=> SITEURL."unsubscribe.php?id=example1234567",
			'UNSUBSCRIBE_MESSAGE'=> "This email was sent to test@email.com. If you don't want to receive these emails in the future, please <a href='".SITEURL."unsubscribe.php?id=example1234567'>unsubscribe</a>. ",
			'ACTIVATION_LINK'	=> "<a href='http://whereever.to.activate.com/'>http://whereever.to.activate.com/</a>",
			'USERURL'			=> "www.user-website.com",
			'PASSWORD'			=> "test-password",
			'LOGINNAME'			=> "test-loginname",
			'SUBJECT'           => "Test Subject",
			'DATE_SHORT'        => $tp->toDate(time(),'short'),
			'DATE_LONG'         => $tp->toDate(time(),'long')
		);



	}

	function templatesPage()
	{
		$templates = e107::getCoreTemplate('email',null,'front');
		$tab = array();
		
		foreach($templates as $k=>$layout)
		{
			$caption = $k;
			$text = "<iframe src='".e_ADMIN."mailout.php?mode=main&action=preview&id=".$k."' width='100%' height='700'>Loading...</iframe>";	
			$tab[$k] = array('caption'=>$caption, 'text'=>$text);
					
		}	
		
		return e107::getForm()->tabs($tab); 
	}
	
	/**
	 * Process Posted Data
	 */
	private function processData($new_data)
	{
		$other = array();
		
		$ret = $new_data;
		
		foreach($new_data as $k=>$v)
		{
			if(in_array($k, $this->mailOtherFields))
			{
				$other[$k] = $v;		
				unset($ret[$k]);
			}
		}
		
		$other['mail_selectors'] = $this->mailAdmin->getAllSelectors();
		
		
		$ret['mail_attach']			= trim($new_data['mail_attach']);
		$ret['mail_send_style'] 	= varset($new_data['mail_send_style'],'textonly');
		$ret['mail_include_images'] = (isset($new_data['mail_include_images']) ? 1 : 0);
		$ret['mail_other'] 			= $other;		// type is set to 'array' in $fields. 	
		
	//	e107::getMessage()->addInfo(print_a($ret,true));
		return $ret;
	}
	
	
	/*
	function createPage()
	{
		if (!isset($mailData) || !is_array($mailData))
		{
			$mailData = array();			// Empty array just in case
		}
		
		if($id = $this->getId())
		{
			$mailData = e107::getDB()->retrieve('mail_content','*','mail_source_id='.$id);
		}
				
		return $this->mailAdmin->show_mailform($mailData);	

	}
	*/

	function sendnowPage()
	{
		$id = $this->getId();

		$this->getResponse()->setTitle(LAN_MAILOUT_15.SEP.'Process Mail Queue #'.$id);
	
		e107::getDb()->update('mail_content', 'mail_content_status='.MAIL_STATUS_PENDING.' WHERE mail_source_id = '.intval($id));
		e107::getDb()->update('mail_recipients', 'mail_status='.MAIL_STATUS_PENDING.' WHERE mail_detail_id = '.intval($id));
	
		if(E107_DEBUG_LEVEL > 0)
		{
			echo "<h4>Debug Mode : Mail is sent and data displayed below. </h4>";
			sendProgress($id);	
		}
		else 
		{
			$pause = e107::getConfig()->get('mail_pausetime',1);
			$interval = ($pause * 1000);
			
			$text = e107::getForm()->progressBar('mail-progress',0, array('btn-label'=>'Start', 'interval'=>$interval, 'url'=> e_SELF, 'mode'=>$id));
		}
	
		return $text;	
	}


	/**
	 * @TODO Do NOT translate, this is for debugging ONLY.
	 *
	*/
	function testPage()
	{

		require_once(e_HANDLER. 'phpmailer/PHPMailerAutoload.php');

		/** @var SMTP $smtp */
		$smtp = new SMTP;
		$smtp->do_debug = SMTP::DEBUG_CONNECTION;

		$mes = e107::getMessage();
		$pref = e107::getPref();

		$username = $pref['smtp_username'];
		$pwd     = $pref['smtp_password'];
		$port = ($pref['smtp_port'] != 465) ? $pref['smtp_port'] : 25;
//		$port = vartrue($pref['smtp_port'], 25);

	//	var_dump($pref['smtp_port']);

	//	return null;

	//	var_dump($pref['smtp_password']);
	//	print_a($pref['smtp_password']);

		ob_start();

		try
		{
			//Connect to an SMTP server
			if(!$smtp->connect($pref['smtp_server'], $port))
			{
				$mes->addError('Connect failed using '.$pref['smtp_server'] .' on port '.$port);
				$content = ob_get_contents();
				ob_end_clean();
				print_a($content);
				return null;
			}
			//Say hello
			if(!$smtp->hello(gethostname()))
			{
				$mes->addError('EHLO failed: ' . $smtp->getError()['error']);
			}
			//Get the list of ESMTP services the server offers
			$e = $smtp->getServerExtList();
			//If server can do TLS encryption, use it
			if(is_array($e) && array_key_exists('STARTTLS', $e))
			{
				$mes->addSuccess("TLS is supported. ");
				$tlsok = $smtp->startTLS();
				if(!$tlsok)
				{
					$mes->addError('Failed to start encryption: ' . $smtp->getError()['error']);
				}
				//Repeat EHLO after STARTTLS
				if(!$smtp->hello(gethostname()))
				{
					$mes->addError('EHLO (2) failed: ' . $smtp->getError()['error']);
				}
				//Get new capabilities list, which will usually now include AUTH if it didn't before
				$e = $smtp->getServerExtList();
			}
			else
			{
				$mes->addWarning("TLS is not supported. ");

			}
			//If server supports authentication, do it (even if no encryption)
			if(is_array($e) && array_key_exists('AUTH', $e))
			{
				if($smtp->authenticate($username, $pwd))
				{
					$mes->addSuccess("Connected ok!");
				}
				else
				{
					$msg = e107::getParser()->lanVars(LAN_MAILOUT_271,array('x'=>$username, 'y'=>$pwd), true);
					$mes->addError($msg . $smtp->getError()['error']);
				}
			}
		}
		catch(Exception $e)
		{
			$mes->addError('SMTP error: ' . $e->getMessage());
		}
		//Whatever happened, close the connection.
		$smtp->quit(true);

		$content = ob_get_contents();

		ob_end_clean();

		print_a($content);

	}





		
	function sendPage()
	{
		
		$id = $this->getId();
		
		$mailData = e107::getDb()->retrieve('mail_content','*','mail_source_id='.intval($id));
		
		if(empty($mailData))
		{
			e107::getMessage()->addError("Couldn't retrieve mail data for id: ".$id);
			return '';	
		}

		$fromHold = false;
		
		$mailData = $this->mailAdmin->dbToMail($mailData);
		
		e107::getMessage()->addDebug("Regenerating recipient list");
		
		e107::getDb()->delete('mail_recipients','mail_detail_id='.intval($id));
				
		return $this->mailAdmin->sendEmailCircular($mailData, $fromHold);
		
		
	}
	
	function maintPage()
	{
		if (!getperms('0'))
		{
			return;
		}
		
		$mes = e107::getMessage();
		$ns = e107::getRender();
		$frm = e107::getForm();
		
		$text = "
				<form action='".e_SELF."?mode=maint' id='email_maint' method='post'>
				<fieldset id='email-maint'>
				<table class='table adminlist'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				
				<tbody>";

		$text .= "<tr><td>".LAN_MAILOUT_182."</td><td>
		
		".$frm->admin_button('email_dross','no-value','delete', LAN_RUN)."
		<br /><span class='field-help'>".LAN_MAILOUT_252."</span></td></tr>";
		$text .= "</tbody></table>\n</fieldset></form>";

		return $text;
		
		// return $ns->tablerender(ADLAN_136.SEP.ADLAN_40, $text, 'maint',true);
		
	}
	
	function prefsPage()
	{
		if (!getperms('0'))
		{
			return;
		}	
		$pref = e107::getPref();
	$e107 = e107::getInstance();
	$frm = e107::getForm();
	$mes = e107::getMessage();
	$ns = e107::getRender();
	
	if($pref['mail_bounce'] == 'auto' && !empty($pref['mail_bounce_email']) && !is_executable(e_HANDLER."bounce_handler.php"))
	{
		$mes->addWarning('Your bounce_handler.php file is NOT executable');	
	}


	e107::getCache()->CachePageMD5 = '_';
	$lastload = e107::getCache()->retrieve('emailLastBounce',FALSE,TRUE,TRUE);
	$lastBounce = round((time() - $lastload) / 60);
	
	$lastBounceText = ($lastBounce > 1256474) ? "<span class='label label-important label-danger'>Never</span>" : "<span class='label label-success'>".$lastBounce . " minutes ago.</span>";

	$text = "
		<form method='post' action='".e_SELF."?".e_QUERY."' id='mailsettingsform'>
		<fieldset id='mail'>
		<legend>".LAN_MAILOUT_110."</legend>
		<table class='table adminform'>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tbody>
		<tr>
			<td>".LAN_MAILOUT_110."<br /></td>
			<td class='form-inline'><div class='input-append'>".$frm->admin_button('testemail', LAN_MAILOUT_112,'other')."&nbsp;
			<input name='testaddress' class='form-control input-xlarge' type='text' size='40' maxlength='80' value=\"".(varset($_POST['testaddress']) ? $_POST['testaddress'] : USEREMAIL)."\" />
			 <span style='padding-left:5px'>".$this->mailAdmin->sendStyleSelect(varset($_POST['testtemplate'], 'textonly'), 'testtemplate')."</span>
			</div></td>
		</tr>

		<tr>
		<td>".LAN_MAILOUT_77."</td>
		<td> ";
		
		$mail_enable = explode(',',vartrue($pref['mailout_enabled'],'core'));
		
		$coreCheck = (in_array('core',$mail_enable)) ? "checked='checked'" : "";
	//	$text .= $frm->checkbox('mail_mailer_enabled[]','core', $coreCheck, 'users');

		if(!empty($pref['e_mailout_list']))
		{
			foreach ($pref['e_mailout_list'] as $mailer => $v)	 
			 {
			    $addon = e107::getAddon($v,'e_mailout');
			    $name = $addon->mailerName;
				$check = (in_array($mailer,$mail_enable)) ? "checked='checked'" : "";
				$text .= $frm->checkbox('mail_mailer_enabled[]',$mailer,$check,$name);
			}
		}




		$text .= "</td></tr>
		
		
		<tr>
		<td style='vertical-align:top'>".LAN_MAILOUT_115."<br /></td>
		<td>";


		$text .= mailoutAdminClass::mailerPrefsTable($pref, 'bulkmailer');
		
	
	/* FIXME - posting SENDMAIL path triggers Mod-Security rules. 
	// Sendmail. -------------->
		$senddisp = ($pref['mailer'] != 'sendmail') ? "style='display:none;'" : '';
		$text .= "<div id='sendmail' {$senddisp}><table style='margin-right:0px;margin-left:auto;border:0px'>";
		$text .= "
		<tr>
		<td>".LAN_MAILOUT_20.":&nbsp;&nbsp;</td>
		<td>
		<input class='tbox' type='text' name='sendmail' size='60' value=\"".(!$pref['sendmail'] ? "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'] : $pref['sendmail'])."\" maxlength='80' />
		</td>
		</tr>
	
		</table></div>";
	*/

	$text .="</td>
	</tr>


	<tr>
		<td>".LAN_MAILOUT_222."</td>
		<td>";
	$text .= $this->mailAdmin->sendStyleSelect(varset($pref['mail_sendstyle'], 'textonly'), 'mail_sendstyle');
	$text .= 
		"<span class='field-help'>".LAN_MAILOUT_223."</span>
		</td>
	</tr>\n

	
	<tr>
		<td>".LAN_MAILOUT_25."</td>
		<td class='form-inline'> ".LAN_MAILOUT_26." ".$frm->number('mail_pause', $pref['mail_pause'])." ".LAN_MAILOUT_27." ".
		$frm->number('mail_pausetime', $pref['mail_pausetime'])." ".LAN_MAILOUT_29.".<br />
		<span class='field-help'>".LAN_MAILOUT_30."</span>
		</td>
	</tr>\n
	
	<tr>
		<td>".LAN_MAILOUT_156."</td>
		<td>".$frm->number('mail_workpertick',varset($pref['mail_workpertick'],5))."<span class='field-help'>".LAN_MAILOUT_157."</span>
		</td>
	</tr>
	
	
	";

	list($mail_log_option,$mail_log_email) = explode(',',varset($pref['mail_log_options'],'0,0'));
	
	$check = ($mail_log_email == 1) ? " checked='checked'" : "";
	
	
	$logOptions = array(LAN_MAILOUT_73,LAN_MAILOUT_74,LAN_MAILOUT_75,LAN_MAILOUT_119);
	
	$text .= "<tr>
		<td>".LAN_MAILOUT_72."</td>
		<td class='form-inline'>
		".$frm->select('mail_log_option',$logOptions,$mail_log_option);
	$text .= " ".$frm->checkbox('mail_log_email', 1, $check, 'label='.LAN_MAILOUT_76);
	$text .= "</td></tr>\n";
	/*
	$text .= "
		<select class='tbox' name='mail_log_option'>\n
		<option value='0'".(($mail_log_option==0) ? " selected='selected'" : '').">".LAN_MAILOUT_73."</option>\n
		<option value='1'".(($mail_log_option==1) ? " selected='selected'" : '').">".LAN_MAILOUT_74."</option>\n
		<option value='2'".(($mail_log_option==2) ? " selected='selected'" : '').">".LAN_MAILOUT_75."</option>\n
		<option value='3'".(($mail_log_option==3) ? " selected='selected'" : '').">".LAN_MAILOUT_119."</option>\n
		</select>\n
		<input type='checkbox' name='mail_log_email' value='1' {$check} />".LAN_MAILOUT_76.
		"</td>
	</tr>\n";
	*/



	if(function_exists('openssl_pkey_new') && deftrue('e_DEVELOPER'))
	{
		$text .= "<tr><td>DomainKeys Identified Mail (DKIM)</td><td class='form-inline'>".$frm->button('DKIM_generate',1,'primary',LAN_MAILOUT_267)."
		<span class='label label-warning'>".LAN_MAILOUT_268."</span></td></tr>";
	}





	$text .= "</table></fieldset>
	<fieldset id='core-mail-prefs-bounce'>
		<legend>".LAN_MAILOUT_31."</legend>
		<table class='table adminform'>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tbody>
	<tr>
		<td>".LAN_MAILOUT_231."</td><td>";
		
	// bounce divs = mail_bounce_none, mail_bounce_auto, mail_bounce_mail
	$autoDisp = ($pref['mail_bounce'] != 'auto') ? "style='display:none;'" : '';
	$autoMail = ($pref['mail_bounce'] != 'mail') ? "style='display:none;'" : '';
	$bounceOpts = array('none' => LAN_MAILOUT_232, 'auto' => LAN_MAILOUT_233, 'mail' => LAN_MAILOUT_234);
	$text .= "<select name='mail_bounce' class='form-control' onchange='bouncedisp(this.value)'>\n<option value=''>&nbsp;</option>\n";
	foreach ($bounceOpts as $k => $v)
	{
		$selected = ($pref['mail_bounce'] == $k) ? " selected='selected'" : '';
		$text .= "<option value='{$k}'{$selected}>{$v}</option>\n";
	}
	$text .= "</select>\n</td>
	</tr></tbody></table>


		<table class='adminform' id='mail_bounce_auto' {$autoDisp}>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tbody>
		<tr>
			<td>".LAN_EMAIL."</td>
			<td><div class='input-append'>".$frm->text('mail_bounce_email2', $pref['mail_bounce_email'], 40, 'size=xlarge');
			
			if(!empty($pref['mail_bounce_email']))
			{
				$text .= $frm->admin_button('send_bounce_test','Send Test','primary','Test');	
			}
			
			$text .= "</div></td>
		</tr>
	
	<tr>
		<td>".LAN_MAILOUT_233."</td><td><b>".(e_ROOT).e107::getFolder('handlers')."bounce_handler.php</b>";
	
	$status = '';
	
	if(!is_readable(e_HANDLER.'bounce_handler.php'))
	{
		$status = LAN_MAILOUT_161;
	}
	elseif(!is_executable(e_HANDLER.'bounce_handler.php'))		// Seems to give wrong answers on Windoze
	{
		$status = LAN_MAILOUT_162;
	}
	else 
	{
	//	$text .= " ".ADMIN_TRUE_ICON;	
	}
	
	if(!empty($status))
	{
		$text .= "&nbsp;&nbsp;<span class='label label-warning'>".$status."</span>"; 
	}
			
		
	
	$text .= "<div>".LAN_MAILOUT_235."</div>
	
	
	</td></tr>
	<tr><td>".LAN_MAILOUT_236."</td><td>".$lastBounceText."</td></tr>";





	$text .= "
	</tbody></table>";

	// Parameters for mail-account based bounce processing
	$text .= "
		<table class='table adminform' id='mail_bounce_mail' {$autoMail}>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tbody>";
	
	$bouncePrefs = array('mail_bounce_email'=>LAN_EMAIL, 'mail_bounce_pop3'=>LAN_MAILOUT_33, 'mail_bounce_user'=>LAN_MAILOUT_34, 'mail_bounce_pass'=>LAN_PASSWORD);
	
	foreach($bouncePrefs as $key =>$label)
	{
		$text .= "<tr><td>".$label."</td><td>".$frm->text($key,$pref[$key],40,'size=xlarge')."</td></tr>";
	}	
	
	/*	
	$text .= "
		<tr><td>".LAN_MAILOUT_32."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_email' value=\"".$pref['mail_bounce_email']."\" /></td></tr>
		<tr><td>".LAN_MAILOUT_33."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_pop3' value=\"".$pref['mail_bounce_pop3']."\" /></td></tr>
		<tr><td>".LAN_MAILOUT_34."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_user' value=\"".$pref['mail_bounce_user']."\" /></td></tr>
		<tr><td>".LAN_PASSWORD."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_pass' value=\"".$pref['mail_bounce_pass']."\" /></td></tr>
	";
	*/
		
	$text .= "	
		<tr><td>".LAN_MAILOUT_120."</td><td><select class='tbox' name='mail_bounce_type'>\n
			<option value=''>&nbsp;</option>\n
			<option value='pop3'".(($pref['mail_bounce_type']=='pop3') ? " selected='selected'" : "").">".LAN_MAILOUT_121."</option>\n
			<option value='pop3/notls'".(($pref['mail_bounce_type']=='pop3/notls') ? " selected='selected'" : "").">".LAN_MAILOUT_122."</option>\n
			<option value='pop3/tls'".(($pref['mail_bounce_type']=='pop3/tls') ? " selected='selected'" : "").">".LAN_MAILOUT_123."</option>\n
			<option value='imap'".(($pref['mail_bounce_type']=='imap') ? " selected='selected'" : "").">".LAN_MAILOUT_124."</option>\n
		</select></td></tr>\n
		";

	$check = ($pref['mail_bounce_delete']==1) ? " checked='checked'" : "";
	$text .= "<tr><td>".LAN_MAILOUT_36."</td><td><input type='checkbox' name='mail_bounce_delete' value='1' {$check} /></td></tr>";

	$check = ($pref['mail_bounce_auto']==1) ? " checked='checked'" : "";
	$text .= "<tr><td>".LAN_MAILOUT_245."</td><td><input type='checkbox' name='mail_bounce_auto' value='1' {$check} /><span class='field-help'>&nbsp;".LAN_MAILOUT_246."</span></td></tr>
				";




				$text .= "
	</tbody>
	</table></fieldset>

	<div class='buttons-bar center'>".$frm->admin_button('updateprefs',LAN_MAILOUT_28,'update')."</div>

	</form>";

	return $text;
//	$caption = ADLAN_136.SEP.LAN_PREFS;
//	$ns->tablerender($caption, $mes->render(). $text);
		
	}
	
	
	
	
	
	
	
	
	// Update Preferences. (security handled elsewhere)
	function saveMailPrefs(&$mes) // $emessage to $mes, working?
	{
		if(!getperms('0'))
		{
			return;
		}
				
		//$pref = e107::getPref();
		$e107 = e107::getInstance();
		$tp = e107::getParser();
		$mes = e107::getMessage();
	
		$bounceOpts = array('none' => LAN_MAILOUT_232, 'auto' => LAN_MAILOUT_233, 'mail' => LAN_MAILOUT_234);
		unset($temp);
		if (!in_array($_POST['mailer'], array('smtp', 'sendmail', 'php'))) $_POST['mailer'] = 'php';
		$temp['mailer'] = $_POST['mailer'];
		// Allow qmail as an option as well - works much as sendmail
		if ((strpos($_POST['sendmail'],'sendmail') !== FALSE) || (strpos($_POST['sendmail'],'qmail') !== FALSE))
		{
			$temp['sendmail'] = $tp->toDB($_POST['sendmail']);
		}
		else
		{
			$temp['sendmail'] = '';
		}

		$temp['bulkmailer']     = $tp->filter($_POST['bulkmailer']);
		$temp['smtp_server'] 	= $tp->toDB(trim($_POST['smtp_server']));
		$temp['smtp_username'] 	= $tp->toDB(trim($_POST['smtp_username']));
		$temp['smtp_password'] 	= $tp->toDB(trim($_POST['smtp_password']));
		$temp['smtp_port'] 	    = intval($_POST['smtp_port']);
	
		$smtp_opts = array();
		switch (trim($_POST['smtp_options']))
		{
		  case 'smtp_ssl' :
		    $smtp_opts[] = 'secure=SSL';
			break;
		  case 'smtp_tls' :
		    $smtp_opts[] = 'secure=TLS';
			break;
		  case 'smtp_pop3auth' :
		    $smtp_opts[] = 'pop3auth';
			break;
		}
		if (vartrue($_POST['smtp_keepalive'])) $smtp_opts[] = 'keepalive';
		if (vartrue($_POST['smtp_useVERP'])) $smtp_opts[] = 'useVERP';
	
		$temp['smtp_options'] = implode(',',$smtp_opts);
	
		$temp['mail_sendstyle'] = $tp->toDB($_POST['mail_sendstyle']);
		$temp['mail_pause'] 	= intval($_POST['mail_pause']);
		$temp['mail_pausetime'] = intval($_POST['mail_pausetime']);
		$temp['mail_workpertick'] = intval($_POST['mail_workpertick']);
		$temp['mail_workpertick'] = min($temp['mail_workpertick'],1000);
		$temp['mail_bounce'] = isset($bounceOpts[$_POST['mail_bounce']]) ? $_POST['mail_bounce'] : 'none';
		$temp['mail_bounce_auto'] = 0;				// Make sure this is always defined
		switch ($temp['mail_bounce'])
		{
			case 'none' :
				$temp['mail_bounce_email'] = '';
				break;
			case 'auto' :
				$temp['mail_bounce_email'] = $tp->toDB($_POST['mail_bounce_email2']);
				break;
			case 'mail' :
				$temp['mail_bounce_email'] = $tp->toDB($_POST['mail_bounce_email']);
				$temp['mail_bounce_auto'] = intval($_POST['mail_bounce_auto']);
				break;
		}
		$temp['mail_bounce_pop3'] = $tp->toDB($_POST['mail_bounce_pop3']);
		$temp['mail_bounce_user'] =	$tp->toDB($_POST['mail_bounce_user']);
		$temp['mail_bounce_pass'] = $tp->toDB($_POST['mail_bounce_pass']);
		$temp['mail_bounce_type'] = $tp->toDB($_POST['mail_bounce_type']);
		$temp['mail_bounce_delete'] = intval(varset($_POST['mail_bounce_delete'], 0));

		if(empty($_POST['mail_mailer_enabled']))
		{
			$_POST['mail_mailer_enabled'] = array('user'); // set default when empty.
		}
	
		$temp['mailout_enabled'] = implode(',', varset($_POST['mail_mailer_enabled'], ''));
		$temp['mail_log_options'] = intval($_POST['mail_log_option']).','.intval($_POST['mail_log_email']);




		if(empty($temp['mailout_enabled']))
		{
			$temp['mailout_enabled'] = 'user';
		}

	
		foreach ($temp as &$t)
		{
			if ($t === NULL) $t = '';
		}
		$pref = e107::pref('core');              		 	// Core Prefs Array.
		if (e107::getAdminLog()->logArrayDiffs($temp, $pref, 'MAIL_03'))
		{
			e107::getConfig()->updatePref($temp);
			e107::getConfig()->save(false);		// Only save if changes - generates its own message
		}
		else
		{
			$mes->addInfo(LAN_NO_CHANGE);
		}
	}
	
	
	
}

class mailout_admin_form_ui extends e_admin_form_ui
{
	
	public function mail_selectors($curval, $mode)
	{
		$val 	= stripslashes($this->getController()->getModel()->get('mail_other'));		
		$data 	= e107::unserialize($val);
		
		switch($mode)
		{
			case 'read':
				return varset($data['mail_selectors'], '');
			break;

			case 'write':

				return $this->getController()->mailAdmin->emailSelector('all', varset($data['mail_selectors'], FALSE));	
			break;

			case 'filter':
			case 'batch':
			//	return $controller->getcustCategoryTree();
			break;	
		
		}	
		
	}
	
	
	
	
	public function mail_send_style($curVal, $mode)
	{
		$val 	= stripslashes($this->getController()->getModel()->get('mail_other'));		
		$data 	= e107::unserialize($val);
		
		switch($mode)
		{
			case 'read':
				return varset($data['mail_send_style'], '');
			break;

			case 'write':
				return $this->getController()->mailAdmin->sendStyleSelect(varset($data['mail_send_style'], ''),'mail_send_style');
			break;

			case 'filter':
			case 'batch':
			//	return $controller->getcustCategoryTree();
			break;	
		
		}	
		
		
	}
	
	
	private function mailDetails($field, $mode)
	{
		
		switch($mode)
		{
			case 'read':
				$val 	= stripslashes($this->getController()->getListModel()->get('mail_other'));		
				$data 	= e107::unserialize($val);
				return $data[$field];
			break;

			case 'write':
				$val 	= stripslashes($this->getController()->getModel()->get('mail_other'));		
				$data 	= e107::unserialize($val);
				
				if($field == 'mail_sender_name' && !vartrue($data['mail_sender_name']))
				{
					$data['mail_sender_name'] = USERNAME;	
				}
				
				if($field == 'mail_sender_email' && !vartrue($data['mail_sender_email']))
				{
					$data['mail_sender_email'] = USEREMAIL;	
				}
				
				
				return $this->text($field, $data[$field],70, 'size=xxlarge');
			break;

			case 'filter':
			case 'batch':
			//	return $controller->getcustCategoryTree();
			break;	
		
		}
	}
	
	
	public function mail_sender_name($curVal,$mode)
	{
		return $this->mailDetails('mail_sender_name', $mode);
	}
	
	public function mail_sender_email($curVal,$mode)
	{
		return $this->mailDetails('mail_sender_email', $mode);
	}

	public function mail_copy_to($curVal,$mode)
	{
		return $this->mailDetails('mail_copy_to', $mode);
	}

	public function mail_bcopy_to($curVal,$mode)
	{
		return $this->mailDetails('mail_bcopy_to', $mode);
	}
	
	public function mail_attach($curVal,$mode)
	{
		if($mode == 'read')
		{
			$val 	= stripslashes($this->getController()->getListModel()->get('mail_other'));		
			$data 	= e107::unserialize($val);
			return basename($data['mail_attach']);	
		}
		
		if($mode == 'write')
		{
			$val 	= stripslashes($this->getController()->getModel()->get('mail_other'));		
			$data 	= e107::unserialize($val);
			return $this->filepicker('mail_attach',$data['mail_attach'], $mode);	
		}
		
	}
	
	
	function options($parms, $value, $id, $attributes)
	{
		$controller = $this->getController();
		
		
		
		$mode = $this->getController()->getMode();

		if($mode == 'main')
		{
			$text = "";
			
			$link = e_SELF."?mode=main&action=send&id=".$id;	
			$preview = e_SELF."?mode=main&action=preview&id=".$id;
			$text .= "<span class='btn-group'>";
			$text .= "<a href='".$link."' class='btn btn-default' title='".LAN_MAILOUT_08."'>".E_32_MAIL."</a>";
			$text .= "<a rel='external' class='btn btn-default btn-secondary e-modal' data-modal-caption='".LAN_PREVIEW."' href='".$preview."'  title='".LAN_PREVIEW."'>".E_32_SEARCH."</a>";

			$text .= $this->renderValue('options',$value,$attributes,$id);

			return $text;
		}
		
		if($mode == 'sent' || $mode == 'pending' || $mode == 'held')
		{
			$user = $this->getController()->getModel()->get('mail_recipient_id');
			$link = e_SELF."?searchquery=&filter_options=mail_detail_id__".$id."&mode=recipients&action=list&iframe=1";
			$preview = e_SELF."?mode=main&action=preview&id=".$id.'&user='.$user;

			$text = "<span class='btn-group'>";
			$text .= "<a href='".$link."' class='btn btn-default btn-secondary e-modal' data-modal-caption='Recipients for Mail #".$id."' title='".LAN_MAILOUT_173."'>".E_32_USER."</a>";
			$text .= "<a rel='external' class='btn btn-default btn-secondary e-modal' data-modal-caption='".LAN_PREVIEW."' href='".$preview."'  title='".LAN_PREVIEW."'>".E_32_SEARCH."</a>";

			$attributes['readParms']['editClass'] = e_UC_NOBODY;
			$text .= $this->renderValue('options',$value,$attributes,$id);
			$text .= "</span>";
			return $text;
		}

		$mode 		= $controller->getMode();
		$mailData 	= $controller->getListModel()->getData();
			
		return 		$controller->mailAdmin->makeMailOptions($mode,$mailData);	
		
	}
	
} 	 	 





class mailout_recipients_ui extends e_admin_ui
{
		
		protected $pluginTitle		= LAN_MAILOUT_15;
		protected $pluginName		= LAN_MAILOUT_15;
		protected $table			= "mail_recipients";

	//	protected $listQry			= "SELECT * FROM #mail_content WHERE mail_content_status = 20 ";


	//	protected $editQry			= "SELECT * FROM #mail_content WHERE cust_id = {ID}";

		protected $pid 					= "mail_target_id";
		protected $perPage 				= 10;
		protected $listOrder			= "mail_target_id desc";

		protected $batchDelete 			= true;	
		
	//	protected $tabs					= array('General','Details', 'Questionnaire', 'Correspondence');

	protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null, 		'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),	
			'mail_target_id'  		=> array('title' => LAN_MAILOUT_143, 'thclass' => 'left', 'forced' => TRUE),
			'mail_recipient_id' 	=> array('title' => LAN_MAILOUT_142, 'type'=>'number', 'data'=>'int', 'thclass' => 'left', 'readonly'=>2),
			'mail_recipient_name' 	=> array('title' => LAN_MAILOUT_141, 'type'=>'text', 'data'=>'str', 'readonly'=>2, 'forced' => TRUE),
			'mail_recipient_email' 	=> array('title' => LAN_MAILOUT_140, 'type'=>'email', 'data'=>'str', 'thclass' => 'left', 'forced' => TRUE),
			'mail_status' 			=> array('title' => LAN_MAILOUT_138, 'type'=>'method', 'filter'=>true, 'data'=>'int', 'thclass' => 'left', 'class'=>'left', 'writeParms'=>''),
			'mail_detail_id' 		=> array('title' => LAN_MAILOUT_137, 'type'=>'dropdown', 'filter'=>true),
			'mail_send_date' 		=> array('title' => LAN_MAILOUT_139,  'type'=>'datestamp', 'proc' => 'sdatetime'),
			'mail_target_info'		=> array('title' => LAN_MAILOUT_148, 'proc' => 'array'),
			'options' 				=> array('title' => LAN_OPTIONS, 'type'=>'method',  'width'=>'10%', 'forced' => TRUE)
		);
	
	
	protected $fieldpref = array('checkboxes', 'mail_target_id', 'mail_recipient_name', 'mail_recipient_email', 'mail_detail_id', 'mail_status', 'options');
	
	public $mailManager = null;
	public $mailStatus = array();
	
	function init()
	{
		
		$this->mailManager = new e107MailManager;

		$sql = e107::getDb();
		$sql->gen("SELECT r.mail_detail_id,c.mail_title FROM `#mail_recipients` AS r LEFT JOIN `#mail_content` as c ON r.mail_detail_id = c.mail_source_id GROUP BY r.mail_detail_id");
				
		while($row = $sql->fetch())
		{
			$id = $row['mail_detail_id'];
			$array[$id] = $id." : ".vartrue($row['mail_title'], "(No Name)");	
		}
		$this->fields['mail_detail_id']['writeParms'] = varset($array, array());
		
		
		$this->mailStatus = array(
			 MAIL_STATUS_SENT 		=> LAN_MAILOUT_211,
			 MAIL_STATUS_BOUNCED 	=> LAN_MAILOUT_213,
			 MAIL_STATUS_CANCELLED	=> LAN_MAILOUT_218,
			 MAIL_STATUS_PARTIAL	=> LAN_MAILOUT_219,
			 MAIL_STATUS_FAILED		=> LAN_MAILOUT_212,
			 MAIL_STATUS_PENDING 	=> LAN_MAILOUT_214,
			 MAIL_STATUS_SAVED 		=> LAN_MAILOUT_215,
			 MAIL_STATUS_HELD		=> LAN_MAILOUT_217
		);
				
	}
	
	/**
	 * Fix Total counts after recipient deletion. 
	 */
	public function afterDelete($data, $id, $deleted_check)
	{
		
		if($data['mail_status'] < MAIL_STATUS_PENDING)
		{
			return;	
		}
						
		$query = "mail_total_count = mail_total_count - 1, mail_togo_count = mail_togo_count - 1 WHERE mail_source_id = ".intval($data['mail_detail_id'])." LIMIT 1";

		if(!e107::getDb()->update('mail_content',$query))
		{
			e107::getMessage()->addDebug(print_a($update,true));	
		}
		
		
	}
	

}



class mailout_recipients_form_ui extends e_admin_form_ui
{
	private $mailStatus = array();
	
	function init()
	{
		$this->mailStatus = array(
			 MAIL_STATUS_SENT 		=> LAN_MAILOUT_211,
			 MAIL_STATUS_BOUNCED 	=> LAN_MAILOUT_213,
			 MAIL_STATUS_CANCELLED	=> LAN_MAILOUT_218,
			 MAIL_STATUS_PARTIAL	=> LAN_MAILOUT_219,
			 MAIL_STATUS_FAILED		=> LAN_MAILOUT_212,
			 MAIL_STATUS_PENDING 	=> LAN_MAILOUT_214,
		//	 MAIL_STATUS_SAVED 		=> LAN_MAILOUT_215,
		//	 MAIL_STATUS_HELD		=> LAN_MAILOUT_217
		//	MAIL_STATUS_TEMP        => ",
		);	
		
		
	}
	
	
	public function mail_status($curVal,$mode)
	{
		if($mode == 'read')
		{
			$stat = array();
			$stat[0] = 'label-success';
			$stat[10] = 'label-warning';
			$stat[13] = 'label-warning';
			$stat[5] = 'label-error'; // MAIL_STATUS_FAILED
			
			return "<span class='label ".varset($stat[$curVal])."'>#".$curVal." ".$this->getController()->mailManager->statusToText($curVal)."</span>";
		}
		
		if($mode == 'write')
		{
			return $this->select('mail_status', $this->mailStatus, $curVal);
		}
		
		if($mode == 'filter')
		{
			return $this->mailStatus;
		}
		
	}	
	
	function options($parms, $value, $id, $attributes)
	{
		
		$user = $this->getController()->getListModel()->get('mail_recipient_id');
		$eid = $this->getController()->getListModel()->get('mail_detail_id');
		
		$preview = e_SELF."?mode=main&action=preview&id=".$eid.'&user='.$user;

		$text = "<span class='btn-group'>";
		$text .= "<a rel='external' class='btn btn-default btn-secondary e-modal' data-modal-caption='".LAN_PREVIEW."' href='".$preview."' class='btn' title='".LAN_PREVIEW."'>".E_32_SEARCH."</a>";
		


		if(E107_DEBUG_LEVEL > 0)
		{
			$att['readParms']['editClass'] = e_UC_MAINADMIN;
		}
		else
		{
			$att['readParms']['editClass'] = e_UC_NOBODY;
		}

		$text .= $this->renderValue('options',$value,$att,$id);
		$text .= "</span>";
		return $text;
	
		
		
	}
	
}


new mailout_admin();















$e_sub_cat = 'mail';






$action = $tp->toDB(varset($_GET['mode'],'makemail'));
$pageMode = varset($_GET['savepage'], $action);			// Sometimes we need to know what brought us here - $action gets changed
$mailId = intval(varset($_GET['m'],0));
$targetId = intval(varset($_GET['t'],0));

// Create mail admin object, load all mail handlers
$mailAdmin = new mailoutAdminClass($action);			// This decodes parts of the query using $_GET syntax
e107::setRegistry('_mailout_admin', $mailAdmin);
if ($mailAdmin->loadMailHandlers() == 0)
{	// No mail handlers loaded
//	echo 'No mail handlers loaded!!';
	//exit;
}

require_once(e_ADMIN.'auth.php');

e107::getAdminUI()->runPage();

require_once(e_ADMIN.'footer.php');



$errors = array();

$subAction = '';
$midAction = '';
$fromHold = FALSE;


if (isset($_POST['mailaction']))
{
	if (is_array($_POST['mailaction']))
	{
		foreach ($_POST['mailaction'] as $k => $v)
		{
			if ($v)		// Look for non-empty action
			{
				$mailId = $k;
				$action = $v;
				break;
			}
		}
	}
}


if (isset($_POST['targetaction']))
{
	if (is_array($_POST['targetaction']))
	{
		foreach ($_POST['targetaction'] as $k => $v)
		{
			if ($v)		// Look for non-empty action
			{
				$targetId = $k;
				$action = $v;
				break;
			}
		}
	}
}


// e107::getMessage()->addDebug("Action=".$action);
//echo "Action: {$action}  MailId: {$mailId}  Target: {$targetId}<br />";
// ----------------- Actions ------------------->

//TODO - replace code sections with class/functions. 

switch ($action)
{
	/*
	case 'prefs' :
		if (getperms('0'))
		{
			if (isset($_POST['testemail'])) 
			{		//		Send test email - uses standard 'single email' handler
				if(trim($_POST['testaddress']) == '')
				{
					$mes->addError(LAN_MAILOUT_19);
					$subAction = 'error';
				}
				else
				{
					$mailheader_e107id = USERID;
					require_once(e_HANDLER.'mail.php');
					$add = ($pref['mailer']) ? " (".strtoupper($pref['mailer']).")" : ' (PHP)';
					$sendto = trim($_POST['testaddress']);
					if (!sendemail($sendto, LAN_MAILOUT_113." ".SITENAME.$add, str_replace("[br]", "\n", LAN_MAILOUT_114),LAN_MAILOUT_189)) 
					{
						$mes->addError(($pref['mailer'] == 'smtp')  ? LAN_MAILOUT_67 : LAN_MAILOUT_106);
					} 
					else 
					{
						$mes->addSuccess(LAN_MAILOUT_81. ' ('.$sendto.')');
						e107::getLog()->add('MAIL_01',$sendto,E_LOG_INFORMATIVE,'');
					}
				}
			}
			elseif (isset($_POST['updateprefs']))
			{
				saveMailPrefs($mes); // TODO check if functional, $emessage -> $mes
			}
		}
		break;
	*/
	/*
	case 'mailcopy' :		// Copy existing email and go to edit screen
		if (isset($_POST['mailaction']))
		{
			$action = 'makemail';
			$mailData = $mailAdmin->retrieveEmail($mailId);
			if ($mailData === FALSE)
			{
				$mes->addError(LAN_MAILOUT_164.':'.$mailId);
				break;
			}
			unset($mailData['mail_source_id']);
		}
		break;
	*/
	/*
	case 'mailedit' :		// Edit existing mail
		if (isset($_POST['mailaction']))
		{
			$action = 'makemail';
			$mailData = $mailAdmin->retrieveEmail($mailId);
			if ($mailData === FALSE)
			{
				$mes->addError(LAN_MAILOUT_164.':'.$mailId);
				break;
			}
		}
		break;
	*/
	
	case 'makemail' :
		$newMail = TRUE;
		
		if (isset($_POST['save_email']))
		{
			$subAction = 'new';
		}
		elseif (isset($_POST['update_email']))
		{
			$subAction = 'update';
			$newMail = FALSE;
		}
		elseif (isset($_POST['send_email'])) 
		{	// Send bulk email
			$subAction = 'send';
		}
		if ($subAction != '')
		{
			$mailData = $mailAdmin->parseEmailPost($newMail);
			$errors = $mailAdmin->checkEmailPost($mailData, $subAction == 'send');		// Full check if sending email
			if ($errors !== TRUE)
			{
				$subAction = 'error';
				break;
			}
			$mailData['mail_selectors'] = $mailAdmin->getAllSelectors();	// Add in the selection criteria
		}

		// That's the checking over - now do something useful!
		switch ($subAction)
		{
			case 'send' :					// This actually creates the list of recipients in the display routine
				$action = 'marksend';
				break;
			case 'new' :
				// TODO: Check all fields created - maybe 
				$mailData['mail_content_status'] = MAIL_STATUS_SAVED;
				$mailData['mail_create_app'] = 'core';
				$result = $mailAdmin->saveEmail($mailData, TRUE);
				if (is_numeric($result))
				{
					$mailData['mail_source_id'] = $result;
					$mes->addSuccess(LAN_MAILOUT_145);
				}
				else
				{
					$mes->addError(LAN_MAILOUT_146);
				}
				break;
			case 'update' :
				$mailData['mail_content_status'] = MAIL_STATUS_SAVED;
				$result = $mailAdmin->saveEmail($mailData, FALSE);
				if (is_numeric($result))
				{
					$mailData['mail_source_id'] = $result;
					$mes->addSuccess(LAN_MAILOUT_147);
				}
				else
				{
					$mes->addError(LAN_MAILOUT_146);
				}
				break;
		}
		break;

	case 'mailhold' :
		$action = 'held';
		if ($mailAdmin->holdEmail($mailId))
		{
			$mes->addSuccess(str_replace('[x]', $mailId, LAN_MAILOUT_229));
		}
		else
		{
			$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_230);
		}
		break;

	case 'mailcancel' :
		$action = $pageMode;		// Want to return to some other page
		if ($mailAdmin->cancelEmail($mailId))
		{
			$mes->addSuccess(str_replace('[x]', $mailId, LAN_MAILOUT_220));
		}
		else
		{
			$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_221);
		}
		break;

	case 'maildelete' :
		break;


	case 'marksend' :			// Actually do something with an email and list of recipients - entry from email confirm page
		$action = 'saved';
		if (isset($_POST['email_cancel']))		// 'Cancel' in this context means 'delete' - don't want it any more
		{
			$midAction = 'midDeleteEmail';
		}
		elseif (isset($_POST['email_hold']))
		{
			if ($mailAdmin->activateEmail($mailId, TRUE))
			{
				$mes->addSuccess(str_replace('[x]', $mailId, LAN_MAILOUT_187));
			}
			else
			{
				$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_166);
			}
			$action = 'held';
		}
		elseif (isset($_POST['email_send']))
		{
			$midAction = 'midMoveToSend';
			$action = 'pending';
		}
	
		if(isset($_POST['email_sendnow']))
		{
			$midAction = 'midMoveToSend';
			//$action = 'pending';
		}
		break;

	case 'mailsendnow' :			// Send mail previously on 'held' list. Need to give opportunity to change time/date etc
		$action = 'marksend';			// This shows the email details for confirmation
		$fromHold = TRUE;
		$mailData['mail_source_id'] = $mailId;
		break;

	case 'maildeleteconfirm' :
		$action = $pageMode;		// Want to return to some other page
		$midAction = 'midDeleteEmail';
		if (!isset($_POST['mailIDConf']) || (intval($_POST['mailIDConf']) != $mailId))
		{
			$errors[] = str_replace(array('[x]', '[z]'), array($mailId, intval($_POST['mailIDConf'])), LAN_MAILOUT_174);
			break;
		}
		break;

	case 'mailonedelete' :
	case 'debug' :
		$mes->addError('Not implemented yet');
		break;

	case 'mailtargets' :
		$action = 'recipients';
		// Intentional fall-through
	case 'recipients' :
	case 'saved' :		// Show template emails - probably no actions
	case 'sent' :
	case 'pending' :
	case 'held' :
	case 'mailshowtemplate' :
		if (isset($_POST['etrigger_ecolumns']))
		{
	//		$mailAdmin->mailbodySaveColumnPref($action);
		}
		break;

	case 'maint' :		// Perform any maintenance actions required
		if (isset($_POST['email_dross']))
		if ($mailAdmin->dbTidy())			// Admin logging done in this routine
		{
			$mes->addSuccess(LAN_MAILOUT_184);
		}
		else
		{
			$errors[] = LAN_MAILOUT_183;
		}
		break;

	// Send Emails Immediately using Ajax
	case 'mailsendimmediately' : 
	
		$id = array_keys($_POST['mailaction']);
		sendImmediately($id[0]);
							
	break;


	default :
	//	$mes->addError('Code malfunction 23! ('.$action.')');
	//	$ns->tablerender(LAN_MAILOUT_97, $mes->render());
		exit;			// Could be a hack attempt
}	// switch($action) - end of 'executive' tasks



// ------------------------ Intermediate actions ---------------------------
// (These have more than one route to trigger them)
switch ($midAction)
{
	case 'midDeleteEmail' :
//		$emessage->add($pageMode.': Would delete here: '.$mailId, E_MESSAGE_SUCCESS);
//		break;														// Delete this
		$result = $mailAdmin->deleteEmail($mailId, 'all');
		e107::getLog()->add('MAIL_04','ID: '.$mailId,E_LOG_INFORMATIVE,'');
		if (($result === FALSE) || !is_array($result))
		{
			$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_166);
		}
		else
		{
			if (isset($result['content']))
			{
				if ($result['content'] === FALSE)
				{
					$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_167);
				}
				else
				{
					$mes->addSuccess(str_replace('[x]', $mailId, LAN_MAILOUT_167));
				}
			}
			if (isset($result['recipients']))
			{
				if ($result['recipients'] === FALSE)
				{
					$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_169);
				}
				else
				{
					$mes->addSuccess(str_replace(array('[x]', '[y]'), array($mailId, $result['recipients']), LAN_MAILOUT_170));
				}
			}
		}
		break;
	case 'midMoveToSend' :
		$notify = isset($_POST['mail_notify_complete']) ? 3 : 2;
		$first = 0;
		$last = 0;		// Set defaults for earliest and latest send times.
		// TODO: Save these fields
		if (isset($_POST['mail_earliest_time']))
		{
			$first = e107::getDateConvert()->decodeDateTime($_POST['mail_earliest_time'], 'datetime', CORE_DATE_ORDER, FALSE);
		}
		if (isset($_POST['mail_latest_time']))
		{
			$last = e107::getDateConvert()->decodeDateTime($_POST['mail_earliest_time'], 'datetime', CORE_DATE_ORDER, TRUE);
		}
		if ($mailAdmin->activateEmail($mailId, FALSE, $notify, $first, $last))
		{
			$mes->addSuccess(LAN_MAILOUT_185);
			e107::getLog()->add('MAIL_06','ID: '.$mailId,E_LOG_INFORMATIVE,'');
		}
		else
		{
			$errors[] = str_replace('[x]', $mailId, LAN_MAILOUT_188);
		}
		break;
}

if(isset($_POST['email_sendnow']))
{
//	sendImmediately($mailId);
}

// --------------------- Display errors and results ------------------------
if (is_array($errors) && (count($errors) > 0))
{
	foreach ($errors as $e)
	{
		$mes->addError($e);
	}
	unset($errors);
}
if ($mes->hasMessage())
{
	 $ns->tablerender(LAN_MAILOUT_97, $mes->render());
}


// ---------------------- Display required page ----------------------------
// At this point $action determines which page display is required - one of a 
// fairly limited number of choices
$mailAdmin->newMode($action);
//echo "Action: {$action}  MailId: {$mailId}  Target: {$targetId}<br />";

switch ($action)
{
	case 'prefs' :
		if (getperms('0'))
		{
			show_prefs($mailAdmin);
		}
		break;

	case 'maint' :
		if (getperms('0'))
		{
			show_maint(FALSE);
		}
		break;
	
	case 'debug' :
		if (getperms('0'))
		{
			show_maint(TRUE);
		}
		break;

	case 'saved' :				// Show template emails
	case 'sent' :
	case 'pending' :
	case 'held' :
	//	$mailAdmin->showEmailList($action, -1, -1);
		break;

	case 'mailshowtemplate' :	// Show the templated email
		$mailAdmin->showEmailTemplate($mailId);
		break;

	case 'maildelete' :			// NOTE:: need to set previous page in form
		$mailAdmin->showDeleteConfirm($mailId, $pageMode);
		break;

	case 'marksend' :			// Show the send confirmation page
		$mailAdmin->sendEmailCircular($mailData, $fromHold);
		break;

	case 'recipients' :
	//	$mailAdmin->showmailRecipients($mailId, $action);
		break;

	case 'makemail' :
	default :
		if (!isset($mailData) || !is_array($mailData))
		{
			$mailData = array();			// Empty array just in case
		}
	//	$mailAdmin->show_mailform($mailData);
		break;
}



require_once(e_ADMIN.'footer.php');

/**
 * Real-time Immediate Mail-out. Browser may be closed and will continue. 
 * @param integer $id (mailing id)
 * @return 
 */
 /*
function sendImmediately($id)
{
	$mes = e107::getMessage();
	
	$text = "<div id='mstatus'>Processing Mailout ID: ".$id."</div>";
	$text .= "<div id='progress' style='margin-bottom:30px'>&nbsp;</div>";

	//Initiate the Function in the Background. 

	$text .= "
	<script type='text/javascript'>
	
	//<![CDATA[
		new Ajax.Updater('mstatus', '".e_SELF."?mode=process&id=".intval($id)."', {
			method: 'get',
			evalScripts: true
		});
	// ]]>
	</script>";
	
	// Update the Progress in real-time. 
	$text .= "
	<script type='text/javascript'>
	//<![CDATA[

		x = new Ajax.PeriodicalUpdater('progress', '".e_SELF."?mode=progress&id=".intval($id)."',
		{
			method: 'post',
			frequency: 3,
			decay: 1,
			evalScripts: true		
			
		});

	// ]]>
	</script>";
	
	
	$mes->addSuccess($text);
	
	e107::getRender()->tablerender("Sending...", $mes->render());

}
*/





//----------------------------------------------------
//		MAILER OPTIONS
//----------------------------------------------------
/*


/* FIXME - posting SENDMAIL path triggers Mod-Security rules. 
// Sendmail. -------------->
	$senddisp = ($pref['mailer'] != 'sendmail') ? "style='display:none;'" : '';
	$text .= "<div id='sendmail' {$senddisp}><table style='margin-right:0px;margin-left:auto;border:0px'>";
	$text .= "
	<tr>
	<td>".LAN_MAILOUT_20.":&nbsp;&nbsp;</td>
	<td>
	<input class='tbox' type='text' name='sendmail' size='60' value=\"".(!$pref['sendmail'] ? "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'] : $pref['sendmail'])."\" maxlength='80' />
	</td>
	</tr>

	</table></div>";
*/
/*
	$text .="</td>
	</tr>


	<tr>
		<td>".LAN_MAILOUT_222."</td>
		<td>";
	$text .= $mailAdmin->sendStyleSelect(varset($pref['mail_sendstyle'], 'textonly'), 'mail_sendstyle');
	$text .= 
		"<span class='field-help'>".LAN_MAILOUT_223."</span>
		</td>
	</tr>\n

	
	<tr>
		<td>".LAN_MAILOUT_25."</td>
		<td> ".LAN_MAILOUT_26."
		<input class='tbox e-spinner' size='3' type='text' name='mail_pause' value='".$pref['mail_pause']."' /> ".LAN_MAILOUT_27.
		"<input class='tbox e-spinner' size='3' type='text' name='mail_pausetime' value='".$pref['mail_pausetime']."' /> ".LAN_MAILOUT_29.".<br />
		<span class='field-help'>".LAN_MAILOUT_30."</span>
		</td>
	</tr>\n
	
	<tr>
		<td>".LAN_MAILOUT_156."</td>
		<td><input class='tbox e-spinner' size='3' type='text' name='mail_workpertick' value='".varset($pref['mail_workpertick'],5)."' />
		<span class='field-help'>".LAN_MAILOUT_157."</span>
		</td>
	</tr>\n";
	
	if (isset($pref['e_mailout_list']))  // Allow selection of email address sources
	{ 
		$text .= "
		<tr>
			<td>".LAN_MAILOUT_77."</td>
			<td> ";
			
	  		$mail_enable = explode(',',$pref['mailout_enabled']);
	  
			foreach ($pref['e_mailout_list'] as $mailer => $v)	 
			 {
				$check = (in_array($mailer,$mail_enable)) ? "checked='checked'" : "";
				$text .= "&nbsp;<input type='checkbox' name='mail_mailer_enabled[]' value='{$mailer}' {$check} /> {$mailer}<br />";
			}
			  
	 	 $text .= "</td></tr>\n";
	}

	list($mail_log_option,$mail_log_email) = explode(',',varset($pref['mail_log_options'],'0,0'));
	
	$check = ($mail_log_email == 1) ? " checked='checked'" : "";
	
	$text .= "<tr>
		<td>".LAN_MAILOUT_72."</td>
		<td> 
		<select class='tbox' name='mail_log_option'>\n
		<option value='0'".(($mail_log_option==0) ? " selected='selected'" : '').">".LAN_MAILOUT_73."</option>\n
		<option value='1'".(($mail_log_option==1) ? " selected='selected'" : '').">".LAN_MAILOUT_74."</option>\n
		<option value='2'".(($mail_log_option==2) ? " selected='selected'" : '').">".LAN_MAILOUT_75."</option>\n
		<option value='3'".(($mail_log_option==3) ? " selected='selected'" : '').">".LAN_MAILOUT_119."</option>\n
		</select>\n
		<input type='checkbox' name='mail_log_email' value='1' {$check} />".LAN_MAILOUT_76.
		"</td>
	</tr>\n";

	$text .= "</table></fieldset>
	<fieldset id='core-mail-prefs-bounce'>
		<legend>".LAN_MAILOUT_31."</legend>
		<table class='table adminform'>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tbody>
	<tr>
		<td>".LAN_MAILOUT_231."</td><td>";
		
	// bounce divs = mail_bounce_none, mail_bounce_auto, mail_bounce_mail
	$autoDisp = ($pref['mail_bounce'] != 'auto') ? "style='display:none;'" : '';
	$autoMail = ($pref['mail_bounce'] != 'mail') ? "style='display:none;'" : '';
	$bounceOpts = array('none' => LAN_MAILOUT_232, 'auto' => LAN_MAILOUT_233, 'mail' => LAN_MAILOUT_234);
	$text .= "<select name='mail_bounce' class='tbox' onchange='bouncedisp(this.value)'>\n<option value=''>&nbsp;</option>\n";
	foreach ($bounceOpts as $k => $v)
	{
		$selected = ($pref['mail_bounce'] == $k) ? " selected='selected'" : '';
		$text .= "<option value='{$k}'{$selected}>{$v}</option>\n";
	}
	$text .= "</select>\n</td>
	</tr></tbody></table>


		<table class='adminform' id='mail_bounce_auto' {$autoDisp}>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tbody>
		<tr><td>".LAN_MAILOUT_32."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_email2' value=\"".$pref['mail_bounce_email']."\" /></td></tr>
	
	<tr>
		<td>".LAN_MAILOUT_233."</td><td><b>".(e_DOCROOT).e107::getFolder('handlers')."bounce_handler.php</b>";
	

	if(!is_readable(e_HANDLER.'bounce_handler.php'))
	{
		$text .= "<br /><span class='required'>".LAN_MAILOUT_161.'</span>';
	}
	elseif(!is_executable(e_HANDLER.'bounce_handler.php'))		// Seems to give wrong answers on Windoze
	{
		$text .= "<br /><span class='required'>".LAN_MAILOUT_162.'</span>';
	}
	$text .= "<br /><span class='field-help'>".LAN_MAILOUT_235."</span></td></tr>
	<tr><td>".LAN_MAILOUT_236."</td><td>".$lastBounceText."</td></tr>
	</tbody></table>";

	// Parameters for mail-account based bounce processing
	$text .= "
		<table class='table adminform' id='mail_bounce_mail' {$autoMail}>
		<colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tbody>
		<tr><td>".LAN_MAILOUT_32."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_email' value=\"".$pref['mail_bounce_email']."\" /></td></tr>
		<tr><td>".LAN_MAILOUT_33."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_pop3' value=\"".$pref['mail_bounce_pop3']."\" /></td></tr>
		<tr><td>".LAN_MAILOUT_34."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_user' value=\"".$pref['mail_bounce_user']."\" /></td></tr>
		<tr><td>".LAN_PASSWORD."</td><td><input class='tbox' size='40' type='text' name='mail_bounce_pass' value=\"".$pref['mail_bounce_pass']."\" /></td></tr>
		<tr><td>".LAN_MAILOUT_120."</td><td><select class='tbox' name='mail_bounce_type'>\n
			<option value=''>&nbsp;</option>\n
			<option value='pop3'".(($pref['mail_bounce_type']=='pop3') ? " selected='selected'" : "").">".LAN_MAILOUT_121."</option>\n
			<option value='pop3/notls'".(($pref['mail_bounce_type']=='pop3/notls') ? " selected='selected'" : "").">".LAN_MAILOUT_122."</option>\n
			<option value='pop3/tls'".(($pref['mail_bounce_type']=='pop3/tls') ? " selected='selected'" : "").">".LAN_MAILOUT_123."</option>\n
			<option value='imap'".(($pref['mail_bounce_type']=='imap') ? " selected='selected'" : "").">".LAN_MAILOUT_124."</option>\n
		</select></td></tr>\n
		";

	$check = ($pref['mail_bounce_delete']==1) ? " checked='checked'" : "";
	$text .= "<tr><td>".LAN_MAILOUT_36."</td><td><input type='checkbox' name='mail_bounce_delete' value='1' {$check} /></td></tr>";

	$check = ($pref['mail_bounce_auto']==1) ? " checked='checked'" : "";
	$text .= "<tr><td>".LAN_MAILOUT_245."</td><td><input type='checkbox' name='mail_bounce_auto' value='1' {$check} /><span class='field-help'>&nbsp;".LAN_MAILOUT_246."</span></td></tr>

	</tbody>
	</table></fieldset>

	<div class='buttons-bar center'>".$frm->admin_button('updateprefs',LAN_MAILOUT_28,'update')."</div>

	</form>";

	$caption = ADLAN_136.SEP.LAN_PREFS;
	$ns->tablerender($caption, $mes->render(). $text);
}
*/


//-----------------------------------------------------------
//			MAINTENANCE OPTIONS
//-----------------------------------------------------------
function show_maint($debug = FALSE)
{
	return;
	
	$mes = e107::getMessage();
	$ns = e107::getRender();
	$frm = e107::getForm();
	
	$text = "
			<form action='".e_SELF."?mode=maint' id='email_maint' method='post'>
			<fieldset id='email-maint'>
			<table class='table adminlist'>
			<colgroup>
				<col class='col-label' />
				<col class='col-control' />
			</colgroup>
			
			<tbody>";

		$text .= "<tr><td>".LAN_MAILOUT_182."</td><td>
		
		".$frm->admin_button('email_dross','no-value','delete', LAN_RUN)."
		<br /><span class='field-help'>".LAN_MAILOUT_252."</span></td></tr>";
		$text .= "</tbody></table>\n</fieldset></form>";

		$ns->tablerender(ADLAN_136.SEP.ADLAN_40, $mes->render().$text);
}


/*
function mailout_adminmenu() 
{
	$tp = e107::getParser();

	$action = $tp->toDB(varset($_GET['mode'],'makemail'));
	if($action == 'mailedit')
	{
    	$action = 'makemail';
	}
    $var['post']['text'] = LAN_MAILOUT_190;
	$var['post']['link'] = e_SELF;
	$var['post']['perm'] = 'W';

    $var['saved']['text'] = LAN_MAILOUT_191;		// Saved emails
	$var['saved']['link'] = e_SELF.'?mode=saved';
	$var['saved']['perm'] = 'W';

    $var['pending']['text'] = LAN_MAILOUT_193;		// Pending email runs
	$var['pending']['link'] = e_SELF.'?mode=pending';
	$var['pending']['perm'] = 'W';

    $var['held']['text'] = LAN_MAILOUT_194;			// Held email runs
	$var['held']['link'] = e_SELF.'?mode=held';
	$var['held']['perm'] = 'W';

    $var['sent']['text'] = LAN_MAILOUT_192;			// Completed email runs
	$var['sent']['link'] = e_SELF.'?mode=sent';
	$var['sent']['perm'] = 'W';

	if(getperms("0"))
	{
		$var['prefs']['text'] = LAN_PREFS;
		$var['prefs']['link'] = e_SELF.'?mode=prefs';
   		$var['prefs']['perm'] = '0';

		$var['maint']['text'] = ADLAN_40;
		$var['maint']['link'] = e_SELF.'?mode=maint';
   		$var['maint']['perm'] = '0';
    }
	show_admin_menu(LAN_MAILOUT_15, $action, $var);
}
*/



function headerjs()
{

	$text = "
	<script type='text/javascript'>
		

	function bouncedisp(type)
	{
		if(type == 'auto')
		{
			document.getElementById('mail_bounce_auto').style.display = '';
			document.getElementById('mail_bounce_mail').style.display = 'none';
			return;
		}

		if(type =='mail')
		{
            document.getElementById('mail_bounce_auto').style.display = 'none';
			document.getElementById('mail_bounce_mail').style.display = '';
			return;
		}

		document.getElementById('mail_bounce_auto').style.display = 'none';
		document.getElementById('mail_bounce_mail').style.display = 'none';
	}
	</script>";

	$mailAdmin = e107::getRegistry('_mailout_admin');
// 	$text .= $mailAdmin->_cal->load_files();

	return $text;
}
?>
