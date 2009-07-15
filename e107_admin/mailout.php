<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/mailout.php,v $
|     $Revision: 1.15 $
|     $Date: 2009-07-15 09:37:59 $
|     $Author: e107coders $
|
| Work in progress - supplementary mailer plugin
|
+----------------------------------------------------------------------------+

Features:
1. Additional sources of email addresses for mailouts can be provided via plugins
2. Both list of email recipients and the email are separately stored in the DB using a documented interface (allows alternative creation/mailout routines)
3. Can specify qmail in the sendmail path
4. Handling of partially sent email runs

Interface to add extra mailout (source) handlers - these provide email addresses:
1. Plugin path defined in $eplug_array_pref - added to $pref['mailout_sources']
2. Mailout options has a facility to enable the individual handlers
3. The handler is called 'mailout_class.php' in the plugin directory.
4. Certain variables may be defined at load time to determine whether this is exclusive or supplementary
5. The class name must be 'mailout_plugin_path'


$pref['mailout_enabled'][plugin_path] - array of flags determining which mailers are active
*/


/*
Various information is stored in the 'generic' table, with tags:
	'sendmail' - an entry for which an email is to be sent
	'massmail' - a saved email

Each mailout task is implemented as a class, which must include a number of mandatory entry points:
	show_select($allow_edit = FALSE) - in edit mode, returns text which facilitates address selection. Otherwise shows the current selection criteria
	select_init() - initialise the selection mechanism
	select_add() - routine pulls out email addresses etc, for caller to add to the list of addressees
	select_close() - selection complete

*/

require_once("../class2.php");
$e_sub_cat = 'mail';

set_time_limit(180);
session_write_close();
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."ren_help.php");
if (!getperms("W")) 
{
	header("location:".e_BASE."index.php");
	 exit;
}
include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_users.php");
require_once(e_HANDLER."userclass_class.php");


  $images_path = e_IMAGE.'admin_images/';


$mail_plugin = FALSE;
$action = '';
$sub_par = '';
$mail_id = 0;
if (e_QUERY)
{
  $qs = explode('.',e_QUERY);
  $action = varset($qs[0],'');
  switch ($action)
  {
	case 'justone' :
	  $mail_plugin = varset($qs[1],'');
	  $mail_plugin = trim($mail_plugin);
	  break;
	case 'debug' :
	  $sub_par = varset($qs[1],'sendmail');
	  break;
	case 'savedmail' :
	case 'mailouts' :
	  $sub_par = varset($qs[1],'');
	  if ($sub_par) $mail_id = intval(varset($qs[2],0));
  }
}


// Class handler for core mailout functions
require_once(e_HANDLER."mailout_class.php");

$mail_handlers = array();
$mail_handlers[] = new core_mailout();		// Start by loading the core mailout class


$active_mailers = explode(',',varset($pref['mailout_enabled'],''));


// Load additional configured handlers
foreach (explode(',',$pref['mailout_sources']) as $mailer)
{
  if (isset($pref['plug_installed'][$mailer]) && in_array($mailer,$active_mailers))
//  if (isset($pref['plug_installed'][$mailer]))											// Check its enabled later
  {  // Could potentially use this handler - its installed and enabled
	if (($mail_plugin === FALSE) || ($mail_plugin == $mailer))
	{
	  if (!is_readable(e_PLUGIN.$mailer."/mailout_class.php"))
	  {
		echo "Invalid mailer selected: ".$mailer."<br />";
		exit;
	  }
	  require_once(e_PLUGIN.$mailer."/mailout_class.php");
	  if (varset($mailer_include_with_default,TRUE) || ($mail_plugin == $mailer))
	  {	// Definitely need this plugin
		$mail_class = 'mailout_'.$mailer;
		$temp = new $mail_class;
		if ($temp->mailer_enabled)
		{
		  $mail_handlers[] = &$temp;
		  if (($mail_plugin !== FALSE) && varset($mailer_exclude_default,FALSE))
		  {
			$mail_handlers[0]->mailer_enabled = FALSE;			// Don't need default handler
		  }
		}
		else
		{
		  unset($temp);
		}
	  }
	}
  }
}


//----------------------------------------
//		Send test email - uses standard 'single email' handler
//----------------------------------------
if (isset($_POST['testemail']) && getperms("0")) 
{
    if(trim($_POST['testaddress']) == "")
	{
	  $message = LAN_MAILOUT_19;
	}
	else
	{
		$mailheader_e107id = USERID;
		require_once(e_HANDLER."mail.php");
		$add = ($pref['mailer']) ? " (".strtoupper($pref['mailer']).")" : " (PHP)";
		$sendto = trim($_POST['testaddress']);
		if (!sendemail($sendto, LAN_MAILOUT_113." ".SITENAME.$add, LAN_MAILOUT_114,LAN_MAILOUT_125)) 
		{
			$message = ($pref['mailer'] == "smtp")  ? LAN_MAILOUT_67 : LAN_MAILOUT_106;
		} 
		else 
		{
			$message = LAN_MAILOUT_81. "(".$sendto.")";
			$admin_log->log_event('MAIL_01',$sendto,E_LOG_INFORMATIVE,'');
		}
	}
}

/*
// Delete any mailout entries that have hung around for a day or more (intentionally commented out - done manually now)
$sql->db_Delete("generic", "gen_type='sendmail' AND gen_datestamp < ".(time()-86400));
*/

//----------------------------------------
//		Saved emails
//----------------------------------------
if (isset($_POST['save_email']))
{
	$qry = "0,'massmail', '".time()."', '".USERID."', '".$tp->toDB($_POST['email_subject'])."',  '0', \"".$tp->toDB($_POST['email_body'])."\"  ";
	$message = $sql -> db_Insert("generic", $qry) ? LAN_SAVED : LAN_ERROR;
}


if (isset($_POST['update_email']))
{
	$qry = "gen_user_id = '".USERID."', gen_datestamp = '".time()."', gen_ip = '".$tp->toDB($_POST['email_subject'])."', gen_chardata= \"".$tp->toDB($_POST['email_body'])."\" WHERE gen_id = '".$_POST['update_id']."' ";
	$message = $sql -> db_Update("generic", $qry) ? LAN_UPDATED : LAN_UPDATED_FAILED;
}

/*
if (isset($_POST['delete']))
{
	$d_idt = array_keys($_POST['delete']);
	$message = ($sql -> db_Delete("generic", "gen_id='".$d_idt[0]."'")) ? LAN_DELETED : LAN_DELETED_FAILED;
	$action = 'list';
}


if (isset($_POST['edit']))
{
  $e_idt = array_keys($_POST['edit']);
  if($sql -> db_Select("generic", "*", "gen_id='".$e_idt[0]."' "))
  {
	$foo = $sql -> db_Fetch();
  }
}
*/

if (($action == 'savedmail') && $sub_par && $mail_id)
{
//  echo "Dealing with saved emails {$sub_par} ID {$mail_id}<br />";
  switch($sub_par)
  {
	case 'edit' : 
	  if($sql -> db_Select("generic", "*", "gen_id='".$mail_id."' "))
	  {
		$foo = $sql -> db_Fetch();
		$action = 'makemail';
	  }
	  break;
	case 'delete' :
	  $message = ($sql -> db_Delete("generic", "gen_id='".$mail_id."'")) ? LAN_DELETED : LAN_DELETED_FAILED;
	  $action = 'list';
	  break;
	default :
	  $action = 'makemail';
  }
}





  $_POST['mail_id']  = time();		// Unique ID for email - used to select our run of emails (as opposed to any other that might be going on)

//  if (!is_object($sql2)) $sql2 = new db;				// Should be OK in 0.8



function ret_extended_field_list($list_name, $add_blank = FALSE)
{
  global $sql;
  $ret = "<select name='{$list_name}' class='tbox'>\n";
  if ($add_blank) $ret .= "<option value=''>&nbsp;</option>\n";
  
  $sql -> db_Select("user_extended_struct");
  while($row = $sql-> db_Fetch())
  {
	$ret .= "<option value='ue.user_".$row['user_extended_struct_name']."' >".ucfirst($row['user_extended_struct_name'])."</option>\n";
  }
  $ret .= "</select>\n";
  return $ret;
}

// ---------------------------------------------
//		Find a block of emails to send
// ---------------------------------------------
if (isset($_POST['submit'])) 
{
  $c = 0;					// Record count
  $dups = 0;				// Counter for duplicates


  $email_subject = $tp->toDB($_POST['email_subject']);
  $mail_id = intval(varset($_POST['mail_id'],0));
  
/* Save the actual email, so we aren't reliant on passing $_POST data for immediate use
 Format is as follows:
  gen_id int(10) unsigned NOT NULL auto_increment, 		- set to zero (auto assigned)
  gen_type varchar(80) NOT NULL default '',				- record type being added ('savemail')
  gen_datestamp int(10) unsigned NOT NULL default '0',	- Mail ID code - to match the destination address records
  gen_user_id int(10) unsigned NOT NULL default '0',	- User ID of current author
  gen_ip varchar(80) NOT NULL default '',				- Email subject
  gen_intdata int(10) unsigned NOT NULL default '0',	- Initially set to zero - set to number of emails initially added
  gen_chardata text NOT NULL,							- 'From' email address and name, Email body
*/
  $email_data = array('sender_email' => $email_address, 
						'sender_name' => $email_name, 
						'copy_to'		=> $tp->toDB($_POST['email_cc']),
						'bcopy_to'		=> $tp->toDB($_POST['email_bcc']),
						'attach'		=> $tp->toDB(trim($_POST['email_attachment'])),
						'email_subject'	=> $tp->toDB(trim($_POST['email_subject'])),
						'email_body' 	=> $tp->toDB($_POST['email_body']),
						'use_theme'		=> intval(varset($_POST['use_theme'],0))
					); 
  $qry = "0,'savemail', '".$mail_id."', '".USERID."', '".$tp->toDB($_POST['email_subject'])."',  '0', '".serialize($email_data)."'  ";

  

  $message = ($mail_text_id = $sql -> db_Insert("generic", $qry)) ? LAN_SAVED : LAN_ERROR;

  $mail_text_id = intval($mail_text_id);
  if ($mail_text_id == 0)
  {
    Echo "Email not saved.<br />";
	echo $message;
	require_once(FOOTERF);
	exit;
  }

 
  foreach ($mail_handlers as $m)
  {	// Get email addresses from each handler in turn. Do them one at a time, so that all can use the $sql data object
	if ($m->mailer_enabled)
	{
	// Initialise
      $m->select_init();
	
	// Get email addresses - add to list, strip duplicates
	  while ($row = $m->select_add()) 
	  {	// Add email addresses to the database ready for sending (the body is never saved in the DB - it gets passed as a $_POST value)

		$email_address = trim($row['user_email']);
		$email_name = $row['user_name'];
		if ($email_name == '') { $email_name = 'unknown'; }

		$email_target = serialize(array('user_email' => $email_address, 
									'user_name' => $email_name, 
									'user_signup' => varset($row['user_signup'],''))); 

/*
Table data:
  gen_id int(10) unsigned NOT NULL auto_increment, 		- set to zero (auto assigned)
  gen_type varchar(80) NOT NULL default '',				- record type being added - 'sendmail'
  gen_datestamp int(10) unsigned NOT NULL default '0',	- Mail ID code (matches the stored email)
  gen_user_id int(10) unsigned NOT NULL default '0',	- User ID - zero if not a registered user
  gen_ip varchar(80) NOT NULL default '',				- User email address (so we can search for duplicates)
  gen_intdata int(10) unsigned NOT NULL default '0',	- ID number of email text in 'generic' table (previous version used zero here)
  gen_chardata text NOT NULL,							- User email address, name, signup link ID (previous version stored subject here)
*/


		$qry = "0,'sendmail', '".$_POST['mail_id']."', '".$row['user_id']."', '".$email_address."', '".$mail_text_id."', '".$email_target."' ";

		if ($sql2->db_Select('generic', 'gen_ip', "`gen_datestamp`= '{$_POST['mail_id']}' AND `gen_ip`='{$email_address}'"))
		{
	      $dups++;		// Found second entry with same email address
		}
		else
		{
		  if($sql2 -> db_Insert("generic", $qry))
		  {
			$c++;
		  }
		  else
		  {
			echo "Error on insert: ".$qry."<br />";
		  }
		}
	  }

	  // Close
	  $m->select_close();
	}
  }
  
	$sql->db_Update('generic',"`gen_intdata`={$c} WHERE `gen_id`={$mail_text_id}");
	$admin_log->log_event('MAIL_02','ID: '.$mail_text_id.' '.$c.'[!br!]'.$_POST['email_from_name']." &lt;".$_POST['email_from_email'],E_LOG_INFORMATIVE,'');




// We've got all the email addresses here - display a confirmation form
	$debug = (e_MENU == "debug") ? "?[debug]" : "";

	$text = "<div style='text-align:center'>
		<form method='post' action='".e_HANDLER."phpmailer/mailout_process.php".$debug."' name='mailform' onsubmit=\"open('', 'popup','width=230,height=170,resizable=1,scrollbars=0');this.target = 'popup';return true;\" >
		<div>";

	$text .= "<input type='hidden' name='mail_text_id' value='".$mail_text_id."' />\n";
	$text .= "<input type='hidden' name='mail_id' value='".$mail_id."' />\n";

	$text .= "</div>";

	$text .= "<div>{$c} ".LAN_MAILOUT_24."</div>";

	$text .= "<div><br /><input class='button' type='submit' name='send_mails' value='".LAN_MAILOUT_37."' />
	<input class='button' type='submit' name='cancel_emails' value='".LAN_MAILOUT_38."' />
	</div>";
	$text .= "<br /><br />".LAN_MAILOUT_118."</form><br /><br /></div>";



//  Preview Email 
// --------------
	$text .= "
	<div>
    <table cellpadding='0' cellspacing='0' class='adminform'>
    	<colgroup span='2'>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>
		<tr>
			<td>".LAN_MAILOUT_01." / ".LAN_MAILOUT_02."</td>
			<td>".$_POST['email_from_name']." &lt;".$_POST['email_from_email']."&gt;</td>
		</tr>";


// Add in core and any plugin selectors here
	foreach ($mail_handlers as $m)
	{
	  if ($m->mailer_enabled)
	  {
		$text .= "<tr><td>".$m->mailer_name."</td><td>".$m->show_select(FALSE)."</td></tr>";
	  }
	}


// Support 'cc' and 'bcc' as standard mailout addresses
	$text .= ($_POST['email_cc']) ? "
		<tr>
			<td>".LAN_MAILOUT_04."</td>
			<td>".$_POST['email_cc']."&nbsp;</td>
		</tr>": "";

	$text .= ($_POST['email_bcc']) ? "
		<tr>
			<td>".LAN_MAILOUT_05."</td>
			<td>".$_POST['email_bcc']."&nbsp;</td>
		</tr>": "";

	$text .= "
		<tr>
			<td>".LAN_MAILOUT_51."</td>
			<td>".$_POST['email_subject']."&nbsp;</td>
		</tr>";

	// Attachment
	if ($email_data['attach'])
	{
	$text .= "
		<tr>
			<td>".LAN_MAILOUT_07."</td>
			<td>".$email_data['attach']."&nbsp;</td>
		</tr>";
	}

	// Figures - number of emails to send, number of duplicates stripped
	$text .= "
		  <tr>
			<td>".LAN_MAILOUT_71."</td>
			<td> ".$c." ".LAN_MAILOUT_69.$dups.LAN_MAILOUT_70."</td>
		  </tr>";

	// Email text
	$text .="<tr>
			<td colspan='2'>".stripslashes($tp->toHTML($_POST['email_body'],TRUE))."</td>
		</tr>

	</table>
	</div>";


 	$ns->tablerender(LAN_MAILOUT_39." ({$c}) ", $text);
	require_once(e_ADMIN."footer.php");
	exit;
}	// End of previewed email




//. Update Preferences.

if (isset($_POST['updateprefs']) && getperms("0")) 
{
	unset($temp);
	$temp['mailer'] = $_POST['mailer'];
	// Allow qmail as an option as well - works much as sendmail
	if ((strpos($_POST['sendmail'],'sendmail') !== FALSE) || (strpos($_POST['sendmail'],'qmail') !== FALSE)) $temp['sendmail'] = $_POST['sendmail'];
	$temp['smtp_server'] 	= $tp->toDB($_POST['smtp_server']);
	$temp['smtp_username'] 	= $tp->toDB($_POST['smtp_username']);
	$temp['smtp_password'] 	= $tp->toDB($_POST['smtp_password']);

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
	if (varsettrue($_POST['smtp_keepalive'])) $smtp_opts[] = 'keepalive';
	if (varsettrue($_POST['smtp_useVERP'])) $smtp_opts[] = 'useVERP';

	$temp['smtp_pop3auth'] 	= in_array('pop3auth',$smpt_opts);					// This will go!
	$temp['smtp_keepalive'] = $_POST['smtp_keepalive'];					// This will go!

	$temp['smtp_options'] = implode(',',$smtp_opts);

	$temp['mail_pause'] 	= intval($_POST['mail_pause']);
	$temp['mail_pausetime'] = intval($_POST['mail_pausetime']);
	$temp['mail_bounce_email'] = $tp->toDB($_POST['mail_bounce_email']);
	$temp['mail_bounce_pop3'] = $tp->toDB($_POST['mail_bounce_pop3']);
	$temp['mail_bounce_user'] =	$tp->toDB($_POST['mail_bounce_user']);
	$temp['mail_bounce_pass'] = $tp->toDB($_POST['mail_bounce_pass']);
	$temp['mail_bounce_type'] = $tp->toDB($_POST['mail_bounce_type']);
	$temp['mail_bounce_delete'] = intval($_POST['mail_bounce_delete']);

	$temp['mailout_enabled'] = implode(',',$_POST['mail_mailer_enabled']);
	$temp['mail_log_options'] = intval($_POST['mail_log_option']).','.intval($_POST['mail_log_email']);

	if ($admin_log->logArrayDiffs($temp, $pref, 'MAIL_03'))
	{
		save_prefs();		// Only save if changes
		$message = LAN_SETSAVED;
	}
	else
	{
		$message = IMALAN_20;
	}
}


if (isset($message)) 
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}



// ----------------- Actions ----------------------------------------------->

//if((!e_QUERY && !$_POST['delete']) || $_POST['edit']) $action = 'makemail';

if (!varsettrue($action)) $action = 'makemail';
switch ($action)
{
  case "prefs" :
	if (getperms("0"))
	{
	  show_prefs();
	}
	break;

  case 'makemail' :
	show_mailform($foo);
	break;

  case "list" :
   showList();
   break;

  case 'debug' :
	showList($sub_par);
	break;

  case 'mailouts' :
    showMailouts($sub_par,$mail_id);
}

require_once(e_ADMIN."footer.php");



//---------------------------------------------
//		List of incomplete mailouts
//---------------------------------------------
function showMailouts($sub_par,$mail_id)
{

  global $sql,$ns,$tp, $images_path;
//  gen_datestamp int(10) unsigned NOT NULL default '0',	- Mail ID code - to match the destination address records
//  gen_user_id int(10) unsigned NOT NULL default '0',	- User ID of current author
//  gen_ip varchar(80) NOT NULL default '',				- Email subject
//  gen_intdata int(10) unsigned NOT NULL default '0',	- Initially set to zero - set to number of emails initially added

  $message = '';
  if ($sub_par && $mail_id)
  {
    switch ($sub_par)
	{
	  case 'delete' :
		if ($sql->db_Select('generic','gen_datestamp',"`gen_datestamp`={$mail_id} AND `gen_type`='savemail'"))
		{
			$message = $sql->db_Delete('generic',"`gen_datestamp`={$mail_id} AND (`gen_type`='sendmail' OR `gen_type`='savemail')") ? LAN_DELETED : LAN_DELETED_FAILED;
			$admin_log->log_event('MAIL_04',$mail_id,E_LOG_INFORMATIVE,'');
		}
		else
		{	// Should only happen if people fiddle!
		  $message = "Error - database record not found";
		  echo "DB error<br />";
		}
		break;

	  case 'detail' :		// Show the detail of an email run above the main list
		if ($sql->db_Select('generic','gen_id,gen_datestamp,gen_chardata',"`gen_datestamp`={$mail_id} AND `gen_type`='savemail'"))
		{	
		  $row = $sql->db_Fetch();
		  // Display a little bit of the email
		  $mail = unserialize($row['gen_chardata']);
		  $text = "
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<colgroup>
			<col style='width:25%; text-align: center;' />
			<col style='width:75%' />
			</colgroup>\n
			<tr>
			<td>".LAN_MAILOUT_51."</td>
			<td>".$mail['email_subject']."</td>
			</tr>\n
			<tr>
			<td>".LAN_MAILOUT_103."</td>
			<td>".(isset($mail['send_results']) ? implode('<br />',$mail['send_results']) : LAN_MAILOUT_104)."</td>
			</tr>\n";
		  if ($sql->db_Select('generic','gen_id,gen_datestamp,gen_chardata',"`gen_datestamp`={$mail_id} AND `gen_type`='sendmail'"))
		  {
		    $text .= "<tr><td>".LAN_MAILOUT_105."</td><td>";
			$spacer = '';
			$i = 0;
			while (($row = $sql->db_Fetch()) && ($i < 10))
			{
			  $this_mail = unserialize($row['gen_chardata']);
			  if (isset($this_mail['send_result'])) 
			  {
			    $text .= $spacer.LAN_MAILOUT_03.' '.$this_mail['user_name'].' '.LAN_MAILOUT_107.' '.$this_mail['user_email'].' '.LAN_MAILOUT_108.' '.$this_mail['send_result'];
				$spacer = '<br />';
				$i++;
			  }
			}
			$text .= "</td></tr>\n";
		  }
		  $text .= "
			</table>
		  ";
		  $ns->tablerender(LAN_MAILOUT_102,$text);
		}
		else
		{	// Should only happen if people fiddle!
		  $message = "Error - database record not found";
		  echo "DB error<br />";
		}
	    break;

	  case 'resend' :
//	    Echo "resend: {$mail_id}<br />";
		if ($sql->db_Select('generic','gen_id,gen_datestamp,gen_chardata',"`gen_datestamp`={$mail_id} AND `gen_type`='savemail'"))
		{	// Put up confirmation
		  $row = $sql->db_Fetch();

		  $debug = (e_MENU == "debug") ? "[debug]" : "";
		  $mailer_url = e_HANDLER."phpmailer/mailout_process.php?".$debug."{$row['gen_datestamp']}.{$row['gen_id']}";

		  $c = $sql->db_Count('generic','(*)',"WHERE `gen_datestamp`={$mail_id} AND `gen_type`='sendmail'");	// Count of mails to go

		  $text = "<div style='text-align:center'>
			<form method='post' action='{$mailer_url}' name='mailform' onsubmit=\"open('', 'popup','width=230,height=170,resizable=1,scrollbars=0');this.target = 'popup';return true;\" >
			";
		  $text .= "<div>{$c} ".LAN_MAILOUT_24."</div>";

		  $text .= "<div><br /><input class='button' type='submit' name='send_mails' value='".LAN_MAILOUT_37."' />
			<input class='button' type='submit' name='cancel_emails' value='".LAN_MAILOUT_38."' />
			</div>";
		  $text .= "</form><br /><br /></div>";
		  $ns->tablerender(LAN_MAILOUT_99,$text);

		  // Display a little bit of the email
		  $mail = unserialize($row['gen_chardata']);
		  $text = "
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<colgroup>
			<col style='width:25%; text-align: center;' />
			<col style='width:75%' />
			</colgroup>\n
			<tr>
			<td>".LAN_MAILOUT_51."</td>
			<td>".$mail['email_subject']."</td>
			</tr>\n
			<tr>
			<td>".LAN_MAILOUT_100."</td>
			<td>".$mail['email_body']."</td>
			</tr>\n
			</table>
		  ";
		  $ns->tablerender(LAN_MAILOUT_101,$text);

		  return;
		}
		else
		{	// Should only happen if people fiddle!
		  $message = "Error - database record not found";
		  echo "DB error<br />";
		}
		break;
	  case 'orphans' :				// Delete any orphaned emails
		if ($sql->db_Select('generic','gen_datestamp',"`gen_datestamp`={$mail_id} AND `gen_type`='sendmail'"))
		{
			$message = $sql->db_Delete('generic',"`gen_datestamp`={$mail_id} AND `gen_type`='sendmail'") ? LAN_DELETED : LAN_DELETED_FAILED;
			$admin_log->log_event('MAIL_04',$mail_i5,E_LOG_INFORMATIVE,'');
		}
		else
		{	// Should only happen if people fiddle!
		  $message = "Error - database record not found";
		  echo "DB error<br />";
		}
		break;
	  default :
	    echo "Invalid parameter: {$sub_par}<br />";
	}
  }


  if ($message) $ns -> tablerender("<div style='text-align:center'>".LAN_MAILOUT_78."</div>", $message);

// Need to select main email entries; count number of addresses attached to each
  $gen = new convert;
  $qry = "SELECT 
		u.user_name, g.*, 
		COUNT(m.gen_datestamp) AS pending
		FROM `#generic` AS g 
		LEFT JOIN `#user` as u ON g.gen_user_id=u.user_id
		LEFT JOIN `#generic` AS m ON m.gen_datestamp = g.gen_datestamp AND m.gen_type='sendmail'
		WHERE g.gen_type='savemail'
		GROUP BY g.gen_datestamp
		ORDER BY g.gen_id ASC";
  $count = $sql -> db_Select_gen($qry);
  
  $emails_found = array();			// Log ID and count for later

  $text = "<div style='text-align:center'>";

  if (!$count)
  {
	$text = "<div class='forumheader2' style='text-align:center'>".LAN_MAILOUT_79."</div>";
	$ns -> tablerender("<div style='text-align:center'>".LAN_MAILOUT_78."</div>", $text);
	require_once(e_ADMIN."footer.php");
	exit;
  }

  $text .= "
		<form action='".e_SELF.'?'.e_QUERY."' id='email_list' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:5%; text-align: center;' />
		<col style='width:12%' />
		<col style='width:10%' />
		<col style='width:30%' />
		<col style='width:7%; text-align: center;' />
		<col style='width:7%; text-align: center;' />
		<col style='width:9%; text-align: center;' />
		</colgroup>
		<tr>
		<td class='fcaption'>".LAN_MAILOUT_84."</td>
		<td class='fcaption'>".LAN_MAILOUT_80."</td>
		<td class='fcaption'>".LAN_MAILOUT_85."</td>
		<td class='fcaption'>".LAN_MAILOUT_06."</td>
		<td class='fcaption'>".LAN_MAILOUT_82."</td>
		<td class='fcaption'>".LAN_MAILOUT_83."</td>
		<td class='fcaption'>".LAN_OPTIONS."</td>
		</tr>
	";

  while ($row = $sql->db_Fetch())
  {
	$datestamp = $gen->convert_date($row['gen_datestamp'], "short");

	if ($row['pending']) $emails_found[$row['gen_datestamp']] = $row['pending'];				// Log the mailshot in a list if any emails to go
	$text .= "<tr>
		<td >".$row['gen_datestamp'] ."</td>
		<td>".$datestamp."</td>
		<td>".$row['user_name']."</td>
		<td>".$row['gen_ip']."</td>
		<td>".$row['gen_intdata']."</td>
		<td>".$row['pending']."</td>
		<td style='width:50px;white-space:nowrap'>
		<div>";
	$text .= "<a href='".e_SELF."?mailouts.detail.{$row['gen_datestamp']}'><img src='".$images_path."search_16.png' alt='".LAN_MAILOUT_109."' title='".LAN_MAILOUT_109."' style='border:0px' /></a>";
	if ($row['pending'])
	{
	  $text .= "<a href='".e_SELF."?mailouts.resend.{$row['gen_datestamp']}'><img src='".$images_path."mail_16.png' alt='".LAN_MAILOUT_86."' title='".LAN_MAILOUT_86."' style='border:0px' /></a>";
	}
	$text .= "
		<a href='".e_SELF."?mailouts.delete.{$row['gen_datestamp']}' onclick='return jsconfirm(\"".$tp->toJS(LAN_CONFIRMDEL." [".$row2['gen_ip']."]")."\")'><img src='".$images_path."delete_16.png' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' /></a>
		</div>
		</td>
		</tr>
	";
  }

  $text .= "</table>\n</form><br /><br /><br /></div>";
  $ns -> tablerender("<div style='text-align:center'>".LAN_MAILOUT_78."</div>", $text);
  
  // Now see if we can find any 'orphaned' mailout entries
  $qry = "SELECT 
		g.gen_datestamp, 
		COUNT(g.gen_datestamp) AS pending
		FROM `#generic` AS g 
		WHERE g.gen_type='sendmail'
		GROUP BY g.gen_datestamp
		ORDER BY g.gen_id ASC";
  $count = $sql -> db_Select_gen($qry);
//  Echo "There are {$count} groups of unsent emails: ".count($emails_found)." in previous table<br />";
  if ($count > count($emails_found))
  {
	$text = "
		<form action='".e_SELF.'?'.e_QUERY."' id='email_orphans' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:25%; text-align: center;' />
		<col style='width:60%' />
		<col style='width:15%; text-align: center;' />
		</colgroup>
		<tr>
		<td class='fcaption'>".LAN_MAILOUT_84."</td>
		<td class='fcaption'>".LAN_MAILOUT_83."</td>
		<td class='fcaption'>".LAN_OPTIONS."</td>
		</tr>\n
	";
	while ($row = $sql->db_Fetch())
	{
	  if (!isset($emails_found[$row['gen_datestamp']]))
	  {
		$text .= "<tr>
			<td >".$row['gen_datestamp'] ."</td>
			<td>".$row['pending']."</td>
			<td style='white-space:nowrap'>
			<div>
			<a href='".e_SELF."?mailouts.orphans.{$row['gen_datestamp']}' onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [".$row['gen_datestamp']."]")."') \"><img src='".$images_path."delete_16.png' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' /></a>
			</div>
			</td>
			</tr>\n
		";
	  }
//    echo "ID: {$row['gen_datestamp']}  Unsent: {$row['pending']}";
	}
	$text .= "</table>\n</form><br /><br /><br /></div>";
	$ns -> tablerender("<div style='text-align:center'>".LAN_MAILOUT_98."</div>", $text);
	}
}



//---------------------------------------------
// 			Display Mailout Form
//---------------------------------------------

function show_mailform($foo="")
{
	global $ns,$sql,$tp,$pref,$HANDLERS_DIRECTORY;
	global $mail_handlers;

	
	$email_subject = $foo['gen_ip'];
	$email_body = $tp->toForm($foo['gen_chardata']);
	$email_id = $foo['gen_id'];
	$text = "";

	if(strpos($_SERVER['SERVER_SOFTWARE'],"mod_gzip") && !is_readable(e_HANDLER."phpmailer/.htaccess"))
	{
		$warning = LAN_MAILOUT_40." ".$HANDLERS_DIRECTORY."phpmailer/ ".LAN_MAILOUT_41;
		$ns -> tablerender(LAN_MAILOUT_42, $warning);
	}

	$debug = (e_MENU == "debug") ? "?[debug]" : "";
	$text .= "<div>
	<form method='post' action='".e_SELF.$debug."' id='mailout_form'>
	<table cellpadding='0' cellspacing='0' class='adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
	<tr>
	<td>".LAN_MAILOUT_01.": </td>
	<td>
	<input type='text' name='email_from_name' class='tbox' style='width:80%' size='10' value=\"".varset($_POST['email_from_name'],USERNAME)."\" />
	</td>
	</tr>";


	$text .="
	<tr>
	<td>".LAN_MAILOUT_02.": </td>
	<td >
	<input type='text' name='email_from_email' class='tbox' style='width:80%' value=\"".varset($_POST['email_from_email'],USEREMAIL)."\" />
	</td>
	</tr>";


// Add in the core and any plugin selectors here
	foreach ($mail_handlers as $m)
	{
	  if ($m->mailer_enabled)
	  {
		$text .= "<tr><td>".$m->mailer_name."</td><td>".$m->show_select(TRUE)."</td></tr>";
	  }
	}



// CC, BCC
	$text .= "
	<tr>
	<td>".LAN_MAILOUT_04.": </td>
	<td >
	<input type='text' name='email_cc' class='tbox' style='width:80%' value=\"".$_POST['email_cc']."\" />
	</td>
	</tr>

	<tr>
	<td>".LAN_MAILOUT_05.": </td>
	<td >
	<input type='text' name='email_bcc' class='tbox' style='width:80%' value='{$email_bcc}' />
	</td>
	</tr>";



// Close one table, open another - to give a boundary between addressees and content
	$text .= "</table>
<table cellpadding='0' cellspacing='0' class='adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>";

// Subject
	$text .= "
	<tr>
	<td>".LAN_MAILOUT_51.": </td>
	<td>
	<input type='text' name='email_subject' class='tbox' style='width:80%' value='{$email_subject}' />
	</td>
	</tr>";


// Attachment.
	$text .= "<tr>
	<td>".LAN_MAILOUT_07.": </td>
	<td >";
	$text .= "<select class='tbox' name='email_attachment' >
	<option value=''>&nbsp;</option>\n";
	$sql->db_Select("download", "download_url,download_name", "download_id !='' ORDER BY download_name");
	while ($row = $sql->db_Fetch()) 
	{
		extract($row);
		$selected = ($_POST['email_attachment'] == $download_url) ? "selected='selected'" :
		 "";
		$text .= "<option value=\"{$download_url} \" {$selected}>".htmlspecialchars($download_name)."</option>\n";
	}
	$text .= " </select>";

	$text .= "</td>
	</tr>";


	$text .= "
	<tr>
	<td>".LAN_MAILOUT_09.": </td>
	<td >
	<input type='checkbox' name='use_theme' value='1' />
	</td>
	</tr>

	<tr>
	<td colspan='2' >
	<textarea rows='10' cols='20' id='email_body' name='email_body'  class='e-wysiwyg tbox' style='width:80%;height:200px' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".$email_body."</textarea>
	</td>
	</tr>";

	$text .="
	<tr>
	<td colspan='2'>
	<div>";

    global $eplug_bb;

    $eplug_bb[] = array(
			"name"		=> 'shortcode',
			"onclick"	=> 'expandit',
			"onclick_var" => "sc_selector",
			"icon"		=> e_IMAGE."generic/bbcode/shortcode.png",
			"helptext"	=> LAN_MAILOUT_11,
			"function"	=> "sc_Select",
			"function_var"	=> "sc_selector"
	);

	$text .= display_help("helpb",'mailout');

	if(e_WYSIWYG) 
	{
		$text .="<span style='vertical-align: super;margin-left:5%;margin-bottom:auto;margin-top:auto'><input type='button' class='button' name='usrname' value=\"".LAN_MAILOUT_16."\" onclick=\"tinyMCE.selectedInstance.execCommand('mceInsertContent',0,'|USERNAME|')\" />
		<input type='button' class='button' name='usrlink' value=\"".LAN_MAILOUT_17."\" onclick=\"tinyMCE.selectedInstance.execCommand('mceInsertContent',0,'|SIGNUP_LINK|')\" />
		<input type='button' class='button' name='usrid' value=\"".LAN_MAILOUT_18."\" onclick=\"tinyMCE.selectedInstance.execCommand('mceInsertContent',0,'|USERID|')\" /></span>";
	}

 	$text .="
	</div></td>
	</tr>
		</table> ";


	$text .= "<div class='buttons-bar center'>";
	if(isset($_POST['edit'])){
		$text .= "<input type='hidden' name='update_id' value='".$email_id."' />";
		$text .= "<input class='button' type='submit' name='update_email' value=\"".LAN_UPDATE."\" />";
	}else{
		$text .= "<input class='button' type='submit' name='save_email' value=\"".LAN_SAVE."\" />";
	}

	$text .="&nbsp;<input class='button' type='submit' name='submit' value=\"".LAN_MAILOUT_08."\" />

	</div>

	</form>
	</div>";

	$ns->tablerender(LAN_MAILOUT_15, $text);

}

//----------------------------------------------------
//		MAILER OPTIONS
//----------------------------------------------------

function show_prefs()
{
	global $pref,$ns;
$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."' id='mailsettingsform'>
	<div id='mail'>
	<table cellpadding='0' cellspacing='0' class='adminform'>
	<colgroup span='2'>
		<col class='col-label' />
		<col class='col-control' />
	</colgroup>
	<tr>
	<td>".LAN_MAILOUT_110."<br /></td>
	<td style='text-align:right'><input class='button' type='submit' name='testemail' value=\"".LAN_MAILOUT_112."\" />&nbsp;
	<input name='testaddress' class='tbox' type='text' size='40' maxlength='80' value=\"".SITEADMINEMAIL."\" />
	</td>
	</tr>

	<tr>
	<td style='vertical-align:top'>".LAN_MAILOUT_115."<br /><span class='smalltext'>".LAN_MAILOUT_116."</span></td>
	<td style='text-align:right'>
	<select class='tbox' name='mailer' onchange='disp(this.value)'>\n";
	$mailers = array("php","smtp","sendmail");
	foreach($mailers as $opt)
	{
	  $sel = ($pref['mailer'] == $opt) ? "selected='selected'" : "";
	  $text .= "<option value='{$opt}' {$sel}>{$opt}</option>\n";
	}
	$text .="</select><br />";



// SMTP. -------------->
	$smtp_opts = explode(',',varset($pref['smtp_options'],''));
	$smtpdisp = ($pref['mailer'] != "smtp") ? "display:none;" : "";
	$text .= "<div id='smtp' style='{$smtpdisp} text-align:right'>
		<table style='margin-right:0px;margin-left:auto;border:0px'>";
	$text .= "	<tr>
	<td style='text-align:right' >".LAN_MAILOUT_87.":&nbsp;&nbsp;</td>
	<td style='width:50%; text-align:right' >
	<input class='tbox' type='text' name='smtp_server' size='40' value='".$pref['smtp_server']."' maxlength='50' />
	</td>
	</tr>

	<tr>
	<td style='text-align:right' >".LAN_MAILOUT_88.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
	<td style='width:50%; text-align:right' >
	<input class='tbox' type='text' name='smtp_username' size='40' value=\"".$pref['smtp_username']."\" maxlength='50' />
	</td>
	</tr>

	<tr>
	<td style='text-align:right' >".LAN_MAILOUT_89.":&nbsp;(".LAN_OPTIONAL.")&nbsp;&nbsp;</td>
	<td style='width:50%; text-align:right' >
	<input class='tbox' type='password' name='smtp_password' size='40' value='".$pref['smtp_password']."' maxlength='50' />
	</td>
	</tr>

	<tr>
	<td colspan='2' style='text-align:right' >".LAN_MAILOUT_90.":&nbsp;
	<select class='tbox' name='smtp_options'>\n
	<option value=''>".LAN_MAILOUT_96."</option>\n";
	$selected = (in_array('secure=SSL',$smtp_opts) ? " selected='selected'" : '');
	$text .= "<option value='smtp_ssl'{$selected}>".LAN_MAILOUT_92."</option>\n";
	$selected = (in_array('secure=TLS',$smtp_opts) ? " selected='selected'" : '');
	$text .= "<option value='smtp_tls'{$selected}>".LAN_MAILOUT_93."</option>\n";
	$selected = (in_array('pop3auth',$smtp_opts) ? " selected='selected'" : '');
	$text .= "<option value='smtp_pop3auth'{$selected}>".LAN_MAILOUT_91."</option>\n";
	$text .= "</select>\n<br />".LAN_MAILOUT_94."</td></tr>";

	$text .= "<tr>
	<td colspan='2' style='text-align:right' >".LAN_MAILOUT_57.":&nbsp;
	";
	$checked = (varsettrue($pref['smtp_keepalive']) ) ? "checked='checked'" : "";
	$text .= "<input type='checkbox' name='smtp_keepalive' value='1' {$checked} />
	</td>
	</tr>";

	$checked = (in_array('useVERP',$smtp_opts) ? "checked='checked'" : "");
	$text .= "<tr>
		<td colspan='2' style='text-align:right' >".LAN_MAILOUT_95.":&nbsp;
		<input type='checkbox' name='smtp_useVERP' value='1' {$checked} />
	</td>
	</tr>

	</table></div>";


// Sendmail. -------------->
	$senddisp = ($pref['mailer'] != "sendmail") ? "display:none;" : "";
	$text .= "<div id='sendmail' style='{$senddisp} text-align:right'><table style='margin-right:0px;margin-left:auto;border:0px'>";
	$text .= "

	<tr>
	<td >".LAN_MAILOUT_20.":&nbsp;&nbsp;</td>
	<td style='text-align:right' >
	<input class='tbox' type='text' name='sendmail' size='60' value=\"".(!$pref['sendmail'] ? "/usr/sbin/sendmail -t -i -r ".$pref['siteadminemail'] : $pref['sendmail'])."\" maxlength='80' />
	</td>
	</tr>

	</table></div>";


	$text .="</td>
	</tr>

	<tr>
		<td>".LAN_MAILOUT_25."</td>
		<td style='text-align: right;'> ".LAN_MAILOUT_26."
		<input class='tbox' size='3' type='text' name='mail_pause' value='".$pref['mail_pause']."' /> ".LAN_MAILOUT_27.
		"<input class='tbox' size='3' type='text' name='mail_pausetime' value='".$pref['mail_pausetime']."' /> ".LAN_MAILOUT_29.".<br />
		<span class='smalltext'>".LAN_MAILOUT_30."</span>
		</td>
	</tr>\n

	<tr>
	<td style='vertical-align:top'>".LAN_MAILOUT_31."</td>
	<td style=' text-align:right'>
		".LAN_MAILOUT_32.": <input class='tbox' size='40' type='text' name='mail_bounce_email' value=\"".$pref['mail_bounce_email']."\" /><br />
		".LAN_MAILOUT_33.":  <input class='tbox' size='40' type='text' name='mail_bounce_pop3' value=\"".$pref['mail_bounce_pop3']."\" /><br />
		".LAN_MAILOUT_34.":  <input class='tbox' size='40' type='text' name='mail_bounce_user' value=\"".$pref['mail_bounce_user']."\" /><br />
		".LAN_MAILOUT_35.":  <input class='tbox' size='40' type='text' name='mail_bounce_pass' value=\"".$pref['mail_bounce_pass']."\" /><br />
		".LAN_MAILOUT_120.": <select class='tbox' name='mail_bounce_type'>\n
		<option value=''>&nbsp;</option>\n
		<option value='pop3'".(($pref['mail_bounce_type']=='pop3') ? " selected='selected'" : "").">".LAN_MAILOUT_121."</option>\n
		<option value='pop3/notls'".(($pref['mail_bounce_type']=='pop3/notls') ? " selected='selected'" : "").">".LAN_MAILOUT_122."</option>\n
		<option value='pop3/tls'".(($pref['mail_bounce_type']=='pop3/tls') ? " selected='selected'" : "").">".LAN_MAILOUT_123."</option>\n
		<option value='imap'".(($pref['mail_bounce_type']=='imap') ? " selected='selected'" : "").">".LAN_MAILOUT_124."</option>\n
		</select><br />\n
		";

	$check = ($pref['mail_bounce_delete']==1) ? " checked='checked'" : "";
	$text .= LAN_MAILOUT_36.":  <input type='checkbox' name='mail_bounce_delete' value='1' {$check} />

	</td>
	</tr>\n";

	if (isset($pref['mailout_sources']))
	{  // Allow selection of email address sources
	  $text .= "<tr>
		<td>".LAN_MAILOUT_77."</td>
		<td style='text-align:right'> 
	  ";
	  $mail_enable = explode(',',$pref['mailout_enabled']);
	  foreach (explode(',',$pref['mailout_sources']) as $mailer)
	  {
		$check = (in_array($mailer,$mail_enable)) ? "checked='checked'" : "";
		$text .= $mailer."&nbsp;<input type='checkbox' name='mail_mailer_enabled[]' value='{$mailer}' {$check} /><br />";
	  }
	  $text .= "</td></tr>\n";
	}

	list($mail_log_option,$mail_log_email) = explode(',',varset($pref['mail_log_options'],'0,0'));
	$check = ($mail_log_email == 1) ? " checked='checked'" : "";
	$text .= "<tr>
		<td>".LAN_MAILOUT_72."</td>
		<td style='text-align:right'> 
		<select class='tbox' name='mail_log_option'>\n
		<option value='0'".(($mail_log_option==0) ? " selected='selected'" : "").">".LAN_MAILOUT_73."</option>\n
		<option value='1'".(($mail_log_option==1) ? " selected='selected'" : "").">".LAN_MAILOUT_74."</option>\n
		<option value='2'".(($mail_log_option==2) ? " selected='selected'" : "").">".LAN_MAILOUT_75."</option>\n
		<option value='2'".(($mail_log_option==3) ? " selected='selected'" : "").">".LAN_MAILOUT_119."</option>\n
		</select><br />\n
		<input type='checkbox' name='mail_log_email' value='1' {$check} />".LAN_MAILOUT_76.
		"</td>
	</tr>\n";

	$text .= "</table>
	<div class='buttons-bar center'>
	<input class='button' type='submit' name='updateprefs' value=\"".LAN_MAILOUT_28."\" />
	</div>

	</div></form>";

	$caption = LAN_PREFS;
	$ns->tablerender($caption, $text);
}



//--------------------------------------------------------
//		Show list of saved emails
//--------------------------------------------------------
// Default is what the user wants - saved emails
// Debug modes list any type of data in the generic table - don't believe the column headings!
function showList($type='massmail')
{
  global $sql,$ns,$tp, $images_path;
  $gen = new convert;
  if (!(trim($type))) $type = 'massmail';
  $qry ="SELECT g.*,u.* FROM #generic AS g LEFT JOIN #user AS u ON g.gen_user_id = u.user_id WHERE g.gen_type = '{$type}' ORDER BY g.gen_datestamp DESC";
  $count = $sql -> db_Select_gen($qry);

  $text = "<div style='text-align:center'>";

  if (!$count)
  {
	$text = "<div class='forumheader2' style='text-align:center'>".LAN_MAILOUT_22."</div>";
	$ns -> tablerender("<div style='text-align:center'>".LAN_MAILOUT_21."</div>", $text);
	require_once(e_ADMIN."footer.php");
	exit;
  }

  $text .= "
		<form action='".e_SELF."' id='display' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<colgroup>
		<col style='width:5%; text-align: center;' />
		<col style='width:10%' />
		<col style='width:40%' />
		<col style='width:20%; text-align: center;' />
		<col style='width:5%; text-align: center;' />
		</colgroup>
		<tr>
		<td class='fcaption'>".LAN_MAILOUT_49."</td>
		<td class='fcaption'>".LAN_MAILOUT_50."</td>
		<td class='fcaption'>".LAN_MAILOUT_51."</td>
		<td class='fcaption'>".LAN_MAILOUT_52."</td>
		<td class='fcaption'>".LAN_OPTIONS."</td>
		</tr>
	";

  $glArray = $sql -> db_getList();
  foreach($glArray as $row2)
  {
	$datestamp = $gen->convert_date($row2['gen_datestamp'], "short");

	$text .= "<tr>
		<td >".$row2['gen_id'] ."</td>
		<td>".$row2['user_name']."</td>
		<td>".$row2['gen_ip']."</td>
		<td>".$datestamp."</td>
		<td style='width:50px;white-space:nowrap'>
		<div>";
	$text .= "<a href='".e_SELF."?savedmail.edit.{$row2['gen_id']}'><img src='".$images_path."edit_16.png' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:0px' /></a>";
//		<input type='image' name='edit[{$row2['gen_id']}]' value='edit' src='".$images_path."edit_16.png' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:0px' />
//		<input type='image' name='delete[{$row2['gen_id']}]' value='del' onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [".$row2['gen_ip']."]")."') \" src='".$images_path."delete_16.png' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' />
	$text .= "
		<a href='".e_SELF."?savedmail.delete.{$row2['gen_id']}' onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [".$row2['gen_ip']."]")."') \"><img src='".$images_path."delete_16.png' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' /></a>
		</div>
		</td>
		</tr>
	";
  }

  $text .= "</table>\n</form><br /><br /><br /></div>";
  $ns -> tablerender("<div style='text-align:center'>".LAN_MAILOUT_21."</div>", $text);
}



// Generate list of userclasses, including the number of members in each class.
function userclasses($name) 
{
	global $sql;
	$text .= "<select style='width:80%' class='tbox' name='{$name}' >
		<option value='all'>".LAN_MAILOUT_12."</option>
		<option value='unverified'>".LAN_MAILOUT_13."</option>
		<option value='admin'>".LAN_MAILOUT_53."</option>
		<option value='self'>".LAN_MAILOUT_54."</option>";
	$query = "SELECT uc.*, count(u.user_id) AS members
			FROM #userclass_classes AS uc
			LEFT JOIN #user AS u ON u.user_class REGEXP concat('(^|,)',uc.userclass_id,'(,|$)')
			GROUP BY uc.userclass_id
					";

	$sql->db_Select_gen($query);
	while ($row = $sql->db_Fetch()) 
	{
		$public = ($row['userclass_editclass'] == 0)? "(".LAN_MAILOUT_10.")" : "";
		$text .= "<option value='{$row['userclass_id']}' >".LAN_MAILOUT_55." - {$row['userclass_name']}  {$public} [{$row['members']}]</option>";
	}
	$text .= " </select>";

	return $text;
}


function mailout_adminmenu() 
{
	$action = (e_QUERY) ? e_QUERY : "post";
	if($action == "edit")
	{
    	$action = "post";
	}
    $var['post']['text'] = LAN_MAILOUT_56;
	$var['post']['link'] = e_SELF;
	$var['post']['perm'] = "W";

    $var['list']['text'] = LAN_MAILOUT_97;			// Saved emails
	$var['list']['link'] = e_SELF."?list";
	$var['list']['perm'] = "W";

    $var['mailouts']['text'] = LAN_MAILOUT_78;		// Email runs
	$var['mailouts']['link'] = e_SELF."?mailouts";
	$var['mailouts']['perm'] = "W";

	if(getperms("0")){
		$var['prefs']['text'] = LAN_OPTIONS;
		$var['prefs']['link'] = e_SELF."?prefs";
   		$var['prefs']['perm'] = "0";
    }
	show_admin_menu(LAN_MAILOUT_15, $action, $var);
}


function sc_Select($container='sc_selector') 
{
	$text ="
<!-- Start of Shortcode selector -->
	<div style='margin-left:0px;margin-right:0px; position:relative;z-index:1000;float:right;display:none' id='{$container}'>
	<div style='position:absolute; bottom:30px; right:125px'>
	<table class='fborder' style='background-color: #fff'>
	<tr><td>
	<select class='tbox' name='sc_sel' onchange=\"addtext(this.value); this.selectedIndex= 0; expandit('{$container}')\">
	<option value=''> -- </option>\n";

	$sc = array(
		"|USERNAME|" => LAN_MAILOUT_16,
        "|SIGNUP_LINK|" => LAN_MAILOUT_17,
        "|USERID|" => LAN_MAILOUT_18
	);

	foreach($sc as $key=>$val){
		$text .= "<option value='".$key."'>".$val."</option>\n";
	}
	$text .="
	</select></td></tr>	\n </table></div>
	</div>
\n<!-- End of SC selector -->

";

	return $text;
}















function headerjs()
{

	$text = "
	<script type='text/javascript'>
	function disp(type) {


		if(type == 'smtp'){
			document.getElementById('smtp').style.display = '';
			document.getElementById('sendmail').style.display = 'none';
			return;
		}

		if(type =='sendmail'){
            document.getElementById('smtp').style.display = 'none';
			document.getElementById('sendmail').style.display = '';
			return;
		}

		document.getElementById('smtp').style.display = 'none';
		document.getElementById('sendmail').style.display = 'none';

	}
	</script>";

	return $text;
}



?>
