<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("4")) 
{
	header("location:".e_BASE."index.php");
	exit();
}

$id = 0;
if (e_QUERY) 
{
    $qs = explode(".", e_QUERY);
	$id = intval($qs[0]);
}
if ($id == 0) 
{
  	header("location:".e_ADMIN."admin.php");
	exit();
}

$e_sub_cat = 'userclass';

require_once('auth.php');

// Get class membership for user now - we may need to udpate it
$sql->db_Select('user', '*', 'user_id='.$id);
$row = $sql->db_Fetch();
$currentClasses = array_flip(explode(',', $row['user_class']));			// Current class membership for user (class ID as key)


$sql->db_Select('userclass_classes');
$c = 0;
$class = array();
while ($crow = $sql->db_Fetch()) 
{
	if (getperms("0") || check_class($crow['userclass_editclass'])) 
	{
		$class[$c][0] = $crow['userclass_id'];
		$class[$c][1] = $crow['userclass_name'];
		$class[$c][2] = $crow['userclass_description'];
		$c++;
		if (isset($currentClasses[$crow['userclass_id']])) unset($currentClasses[$crow['userclass_id']]);
	}
}


if (isset($_POST['updateclass'])) 
{
	$remuser = TRUE;
	$classcount = count($_POST['userclass'])-1;
	$currentClasses = array_flip($currentClasses);		// Back to having class number in data value
	for($a = 0; $a <= $classcount; $a++) 
	{
		$tmp = intval($_POST['userclass'][$a]);
		check_allowed($tmp);
		$currentClasses[] = $tmp;
	}
	$svar = implode(',', $currentClasses);
	$row['user_class'] = $svar;					// Get updated list back into user data
	$sql->db_Update("user", "user_class='$svar' WHERE user_id='$id' ");
	$message = UCSLAN_9;

	if ($_POST['notifyuser']) 
	{
		$message .= "<br />".UCSLAN_1.":</b> ".$row['user_name']."<br />";
		require_once(e_HANDLER."mail.php");
		$messaccess = '';
		for($a = 0; $a <= (count($class)-1); $a++) 
		{
		  if (check_class($class[$a][0], $row['user_class'])) 
		  {
			$messaccess .= $class[$a][1]." - " . $class[$a][2]. "\n";
		  }
		}
		if ($messaccess == '') $messaccess = UCSLAN_12."\n";
		$send_to = $row['user_email'];
		$subject = UCSLAN_2;
        $message = UCSLAN_3." " . $row['user_name']. ",\n\n".UCSLAN_4." ".SITENAME."\n( ".SITEURL . " )\n\n".UCSLAN_5.": \n\n".$messaccess."\n".UCSLAN_10."\n".SITEADMIN."\n( ".SITENAME." )";
		sendemail($send_to, $subject, $message);
	}


	header("location: ".$_POST['adminreturn']);
	echo "location redirect failed.";
	exit();
}




$caption = UCSLAN_6." <b>".$row['user_name']."</b> (".$row['user_class'].")";

$text = "	<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>
			<table style='".ADMIN_WIDTH."' class='fborder'>";

for($a = 0; $a <= (count($class)-1); $a++) {
	$text .= "<tr><td style='width:30%' class='forumheader3'>";
	if (check_class($class[$a][0], $row['user_class'])) {
		$text .= "<input type='checkbox' name='userclass[]' value='".$class[$a][0]."' checked='checked' />".$class[$a][1]." ";
	} else {
		$text .= "<input type='checkbox' name='userclass[]' value='".$class[$a][0]."' />".$class[$a][1]." ";
	}
	$text .= "</td><td style='width:70%' class='forumheader3'> ".$class[$a][2]."</td></tr>";
}

$adminreturn = e_ADMIN.'users.php?cu';
if (isset($qs[1]))
{
	$adminreturn .= '.'.$qs[1].(isset($qs[2]) ? ".{$qs[2]}.{$qs[3]}.{$qs[4]}" : "");
}

$text .= "	<tr><td class='forumheader' colspan='2' style='text-align:center'>
			<input type='hidden' name='adminreturn' value='$adminreturn' />
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
	global $sql;
	if (!$sql->db_Select("userclass_classes", "*", "userclass_id = {$class_id}")) 
	{
		header("location:".SITEURL);
		exit;
	}
	$row = $sql->db_Fetch();
	extract($row);
	if (!getperms("0") && !check_class($userclass_editclass)) 
	{
		header("location:".SITEURL);
		exit;
	}
}
?>