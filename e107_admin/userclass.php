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
|     $Source: /cvs_backup/e107_0.8/e107_admin/userclass.php,v $
|     $Revision: 1.2 $
|     $Date: 2008-01-01 18:18:05 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("4")) {
	header("location:".e_BASE."index.php");
	 exit;
}

if (!e_QUERY) 
{
  header("location:".e_ADMIN."admin.php");
  exit;
} 
else 
{
  $qs = explode(".", e_QUERY);
  $id = intval($qs[0]);
}

require_once(e_HANDLER."userclass_class.php");		// Modified class handler
$e_userclass = new user_class;


if (isset($_POST['updateclass'])) 
{
  $remuser = TRUE;
  $classcount = count($_POST['userclass']);
  $spacer = '';
  foreach ($_POST['userclass'] as $a) 
  {
	$a = intval($a);
	check_allowed($a);
	$svar .= $spacer.$a;
	$spacer = ',';
  }
  $sql->db_Update("user", "user_class='{$svar}' WHERE user_id={$id} ");
  $message = UCSLAN_9;

  if ($_POST['notifyuser']) 
  {
	$sql->db_Select("user", "*", "user_id={$id} ");
	$row = $sql->db_Fetch();
	$message .= "<br />".UCSLAN_1.":</b> ".$row['user_name']."<br />";
	require_once(e_HANDLER."mail.php");
	$messaccess = '';
	foreach (explode(',',$row['user_class']) as $a)
	{
	  if (!isset($e_userclass->fixed_classes[$a]))
	  {
		$messaccess .= $e_userclass->class_tree[$a]['userclass_name']." - " . $e_userclass->class_tree[$a]['userclass_description']. "\n";
	  }
	}
	$send_to = $row['user_email'];
	$subject = UCSLAN_2;
    $message = UCSLAN_3." " . $row['user_name']. ",\n\n".UCSLAN_4." ".SITENAME."\n( ".SITEURL . " )\n\n".UCSLAN_5.": \n\n".$messaccess."\n".UCSLAN_10."\n".SITEADMIN."\n( ".SITENAME." )";
//    $admin_log->e_log_event(4,__FILE__."|".__FUNCTION__."@".__LINE__,"DBG","User class change",str_replace("\n","<br />",$message),FALSE,LOG_TO_ROLLING);
	sendemail($send_to, $subject, $message);
  }
  $admin_log->log_event('LAN_ADMIN_LOG_016',str_replace(array('--UID--','--CLASSES--'),array($id,$svar),UCSLAN_11),E_LOG_INFORMATIVE,'USET_14');


  header("location: ".$_POST['adminreturn']);
  echo "location redirect failed.";
  exit;
}


$e_sub_cat = 'userclass';
require_once("auth.php");



$sql->db_Select("user", "*", "user_id={$id} ");
$row = $sql->db_Fetch();

$caption = UCSLAN_6." <b>".$row['user_name']."</b> (".$row['user_class'].")";

$text = "	<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr><td class='forumheader3'>";

$text .= $e_userclass->vetted_tree('userclass',array($e_userclass,'checkbox_desc'), $row['user_class'], 'classes');
$text .= '</td></tr>';
 
$adminreturn = e_ADMIN."users.php?cu".($qs[2] ? ".{$qs[2]}.{$qs[3]}.{$qs[4]}" : "");

$text .= "	<tr><td class='forumheader' style='text-align:center'>
			<input type='hidden' name='adminreturn' value='{$adminreturn}' />
			<input type='checkbox' name='notifyuser' value='1' /> ".UCSLAN_8."&nbsp;&nbsp;
			<input class='button' type='submit' name='updateclass' value='".UCSLAN_7."' />
			</td>
			</tr>
			</table>
			</form>
			</div>";

$ns->tablerender($caption, $text);


require_once("footer.php");


// ----------------------------------------------------------

function check_allowed($class_id) 
{
  global $e_userclass;
  if (!isset($e_userclass->class_tree[$class_id]))
  {
	header("location:".SITEURL);
	exit;
  }
  if (!getperms("0") && !check_class($e_userclass->class_tree[$class_id]['userclass_editclass'])) 
  {
	header("location:".SITEURL);
	exit;
  }
  return TRUE;
}
?>