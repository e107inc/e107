<?php

require('..\..\class2.php');
require_once(e_HANDLER.'user_handler.php');
if (!check_class(e_UC_MAINADMIN))
{
  exit;
}

$user_info = new userHandler;

define('LAN_PCONV_01','E107 Password Conversion Utility');
define('LAN_PCONV_02','This utility converts all the passwords in your user database to current formats');
define('LAN_PCONV_03','Caution!!!! Back up your database first!!!!!');
define('LAN_PCONV_04','Proceed');
define('LAN_PCONV_05','Convert md5 passwords to salted passwords');
define('LAN_PCONV_06','Passwords for email address login');
define('LAN_PCONV_07','Create');
define('LAN_PCONV_08','Delete');
define('LAN_PCONV_09','Do nothing');
define('LAN_PCONV_10','Back up user database');
define('LAN_PCONV_11', 'Yes');
define('LAN_PCONV_12', 'Have you backed up your database?');
define('LAN_PCONV_13', 'Backing up database');
define('LAN_PCONV_14', 'Done');
define('LAN_PCONV_15', 'Creating email passwords');
define('LAN_PCONV_16', 'Deleting email passwords');
define('LAN_PCONV_17', 'Scanning database...');
define('LAN_PCONV_18', 'Cannot open user table');
define('LAN_PCONV_19', 'Creates a table called \'user_backup\' with the information about to be changed. If the table already exists, it is emptied first');
define('LAN_PCONV_20', 'Error creating backup table');
define('LAN_PCONV_21', 'Error copying to backup table');
define('LAN_PCONV_22', 'Total --TOTAL-- users checked');
define('LAN_PCONV_23', 'Total --TOTAL-- email passwords calculated');
define('LAN_PCONV_24', 'Total --TOTAL-- user passwords updated');
define('LAN_PCONV_25', 'Total --TOTAL-- users could not be updated');
define('LAN_PCONV_26', 'Create Backup');
define('LAN_PCONV_27', 'Restore backup');
define('LAN_PCONV_28', 'Restoring from backup....');
define('LAN_PCONV_29', 'Backup database table not found!');
define('LAN_PCONV_30', 'Cannot access backup table');
define('LAN_PCONV_31', '');
define('LAN_PCONV_32', '');
define('LAN_PCONV_33', '');
define('LAN_PCONV_34', '');
define('LAN_PCONV_35', '');


	function multi_radio($name, $textsVals, $currentval = '')
	{
	  $ret = '';
	  $gap = '';
	  foreach ($textsVals as $v => $t)
	  {
	    $sel = ($v == $currentval) ? " checked='checked'" : "";
		$ret .= $gap."<input type='radio' name='{$name}' value='{$v}'{$sel} /> ".$t."\n";
//		$gap = "&nbsp;&nbsp;";
		$gap = "<br />";
	  }
	  return $ret;
	}


$recordCount = 0;
$emailProcess = 0;
$saltProcess = 0;
$cantProcess = 0;
$cookieChange = '';

require(HEADERF);
$pc_db = new db;
if (isset($_POST['GetOnWithIt']))
{
  $doBackup = varset($_POST['doDBBackup'],0);
  $saltConvert = varset($_POST['convertToSalt'],0);
  $emailGen = varset($_POST['EmailPasswords'],0);
  if ($doBackup == 2)
  {
    $saltConvert = 0;		// Don't do conversions if restoring database
	$emailGen = 0;
  }

  $error = '';
  if ($emailGen == 1)
  {  // Scan DB for salted passwords
  }


//-----------------------------------------
//		Backup user DB (selected fields)
//-----------------------------------------
  if (!$error && ($doBackup == 1))
  {
    echo LAN_PCONV_13;
	if ($pc_db->db_Table_exists('user_backup'))
	{  // Completely delete table - avoids problems with incorrect structure
	  $pc_db->db_Select_gen('DROP TABLE `#user_backup` ');
	}

	$qry = "CREATE TABLE `#user_backup` (
  user_id int(10) unsigned NOT NULL,
  user_name varchar(100) NOT NULL default '',
  user_loginname varchar(100) NOT NULL default '',
  user_password varchar(50) NOT NULL default '',
  user_email varchar(100) NOT NULL default '',
  user_prefs text NOT NULL,
  PRIMARY KEY  (user_id),
  UNIQUE KEY user_name (user_name)
) ENGINE=MyISAM;";			// If not exists, of course

	if (!$pc_db->db_Select_gen($qry))
	{
	  $error = LAN_PCONV_20;
	}

	if (!$error)
	{
	  $qry = "INSERT INTO `#user_backup` SELECT user_id, user_name, user_loginname, user_password, user_email, user_prefs FROM `#user` ";
	  if ($pc_db->db_Select_gen($qry) === FALSE)
	  {
		$error = LAN_PCONV_21;
	  }
	}
    if (!$error) echo '...'.LAN_PCONV_14.'<br /><br />';
  }



//--------------------------------------
//		Restore from backup
//--------------------------------------
  if (!$error && ($doBackup == 2))
  {
	echo LAN_PCONV_28;
	if (!$pc_db->db_Table_exists('user_backup'))
	{  
	  $error = LAN_PCONV_28;
	}
	if (!$error && $pc_db->db_Select('user_backup','*'))
	{
	  while ($row = $pc_db->db_Fetch())
	  {
		$uid = $row['user_id'];
		unset($row['user_id']);
		$sql->db_UpdateArray('user',$row," WHERE `user_id`={$uid}");	// Intentionally use $sql here
		if (USERID == $uid)
		{
		  $cookieChange = $row['user_password'];
		}
	  }
	}
	else
	{
	  $error = LAN_PCONV_30;
	}

    if (!$error) echo '...'.LAN_PCONV_14.'<br /><br />';
  }




//--------------------------------------
//		Change passwords
//--------------------------------------
  if (!$error && $emailGen || $saltConvert)
  {  // Run through the DB doing conversions.
	echo LAN_PCONV_17;
    if ($pc_db->db_Select('user', 'user_id, user_name, user_loginname, user_password, user_email, user_prefs', '') === FALSE)
	{
	  $error = LAN_PCONV_18;
	}
	if (!$error)
	{
	  while ($row = $pc_db->db_Fetch())
	  {  // Do conversions
	    $recordCount++;
	    $newData = array();
		$newPrefs = '';
		$user_prefs = e107::getArrayStorage()->unserialize($row['user_prefs']);
          if(!$user_prefs && $row['user_prefs']) $user_prefs = unserialize($row['user_prefs']);
          if ($saltConvert)
		{
		  if ($user_info->canConvert($row['user_password']))
		  {
			$newData['user_password'] = $user_info->ConvertPassword($row['user_password'], $row['user_loginname']);
			$saltProcess++;
			if (USERID == $row['user_id'])
			{
			  $cookieChange = $newData['user_password'];
			}
		  }
		  else
		  {
			$cantProcess++;
		  }
		}
		if (($emailGen == 1) && $user_info->canConvert($row['user_password']))
		{
		  $user_prefs['email_password'] = $user_info->ConvertPassword($row['user_password'], $row['user_email']);
		  $emailProcess++;
		}
		elseif ($emailGen == 2)
		{
		  unset($user_prefs['email_password']);
		  $emailProcess++;
		}
		if (count($user_prefs)) $newPrefs = e107::getArrayStorage()->serialize($user_prefs); else $newPrefs = '';
		if($newPrefs != $user_prefs)
		{
		  $newData['user_prefs'] = $newPrefs;
		}
		
		if (count($newData)) $sql->db_UpdateArray('user',$newData, " WHERE `user_id`={$row['user_id']}");
	  }
	}
	echo str_replace('--TOTAL--',$recordCount, LAN_PCONV_22).'<br />';
	echo str_replace('--TOTAL--',$saltProcess, LAN_PCONV_24).'<br />';
	echo str_replace('--TOTAL--',$emailProcess, LAN_PCONV_23).'<br />';
	echo str_replace('--TOTAL--',$cantProcess, LAN_PCONV_25).'<br />';
	echo '<br />';
  }

  if ($error)
  {
    echo '<br />'.$error.'<br /><br />';
	require_once(FOOTERF);
	exit;
  }
  
 
  if ($cookieChange)
  { 
//    echo "Cookie Updated.<br /><br />";
	$cookieval = USERID.".".md5($cookieChange);		// Just changed admin password, and hence cookie
	cookie($pref['cookie_name'], $cookieval);
  }
}


$text = 
	"<div style='text-align:center'>
	<form method='post' action='".e_SELF."' onsubmit=\"return jsconfirm('".LAN_PCONV_12."')\">
	<table style='width:95%' class='fborder'>
	<colgroup>
	<col style='width:60%' />
	<col style='width:40%' />
	</colgroup>

	<tr>
	  <td colspan='2' class='forumheader3' style='text-align:center'>".LAN_PCONV_01."
	  </td>
	</tr>
	<tr>
	  <td colspan='2' class='forumheader3' style='text-align:center'>".LAN_PCONV_02."<br />".LAN_PCONV_03."
	  </td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_PCONV_10."<br /><span class='smalltext'>".LAN_PCONV_19."</span></td>
	  <td class='forumheader3'>".multi_radio('doDBBackup',array('0' => LAN_PCONV_09, '1' => LAN_PCONV_26, '2' => LAN_PCONV_27),'')."
	  </td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_PCONV_05."</td>
	  <td class='forumheader3'>".multi_radio('convertToSalt',array('0' => LAN_PCONV_09, '1' => LAN_PCONV_11),'')."
	  </td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_PCONV_06."</td>
	  <td class='forumheader3'>".multi_radio('EmailPasswords',array('0' => LAN_PCONV_09, '1' => LAN_PCONV_07, '2' => LAN_PCONV_08),'')."
	  </td>
	</tr>";

  $text .= "
	<tr>
	  <td class='forumheader3' colspan='3' style='text-align:center'>
		<input class='btn btn-default btn-secondary button' type='submit' name='GetOnWithIt' value='".LAN_PCONV_04."' />
	  </td>
	</tr>";




$text .= "
	</table>\n
	</form>
	</div><br />";
	$ns->tablerender(LAN_PCONV_01, $text);

require_once(FOOTERF);



?>
