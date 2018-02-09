<?php

require('..\..\class2.php');
require_once(e_HANDLER.'user_handler.php');
$user_info = new userHandler;

define('LAN_PCALC_01','E107 Password Calculation Utility');
define('LAN_PCALC_02','Login Name');
define('LAN_PCALC_03','Desired password');
define('LAN_PCALC_04','Calculate');
define('LAN_PCALC_05','Invalid login name');
define('LAN_PCALC_06','Errors Found!!!');
define('LAN_PCALC_07','Calculated hash:');
define('LAN_PCALC_08','Password invalid');
define('LAN_PCALC_09','Confirm password');
define('LAN_PCALC_10','Passwords don\'t match!');
define('LAN_PCALC_11', 'Password Calculation');


$loginName = varset($_POST['calc_loginname'],'');

require(HEADERF);
$text = 
	"<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='width:95%' class='fborder'>
	<colgroup>
	<col style='width:60%' />
	<col style='width:40%' />
	</colgroup>

	<tr>
	  <td colspan='2' class='forumheader3' style='text-align:center'>".LAN_PCALC_01."
	  </td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_PCALC_02."</td>
	  <td class='forumheader3'>
	    <input class='tbox' type='text' size='60' maxlength='100' name='calc_loginname' value='{$loginName}' />
	  </td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_PCALC_03."</td>
	  <td class='forumheader3'>
	    <input class='tbox' type='password' size='60' maxlength='100' name='calc_password' value='' />
	  </td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_PCALC_09."</td>
	  <td class='forumheader3'>
	    <input class='tbox' type='password' size='60' maxlength='100' name='calc_password1' value='' />
	  </td>
	</tr>";


$errorString = '';
if (isset($_POST['show_password']))
{
  if ($_POST['calc_password'] != $_POST['calc_password1'])
  {
    $errorString = LAN_PCALC_10;
  }

  if (!$errorString)
  {
	$loginResult = $user_info->validateField('user_loginname',$loginName,FALSE);
	$passwordResult = $user_info->validateField('user_password',trim($_POST['calc_password']),FALSE);

	if ($passwordResult !== TRUE)
	{
	  $errorString = LAN_PCALC_08;
	}
	elseif ($loginResult === TRUE)
	{
	  $passwordHash = $user_info->HashPassword($_POST['calc_password'],$loginName);
	}
	else
	{
	  $errorString = LAN_PCALC_05;
	}
  }
  
  if (!$errorString)
  {
	$text .= "
	  <tr>
	    <td class='forumheader3'>".LAN_PCALC_07."</td>
	    <td class='forumheader3'>".$passwordHash."</td>
	</tr>";
  }
  

  if ($errorString)
  {
	$text .= "
	  <tr>
	    <td class='forumheader3'>".LAN_PCALC_06."</td>
	    <td class='forumheader3'>".$errorString."</td>
	</tr>";
  }
}


  $text .= "
	<tr>
	  <td class='forumheader3' colspan='3' style='text-align:center'>
		<input class='btn btn-default btn-secondary button' type='submit' name='show_password' value='".LAN_PCALC_04."' />
	  </td>
	</tr>";




$text .= "
	</table>\n
	</form>
	</div><br />";
	$ns->tablerender(LAN_PCALC_11, $text);

require(FOOTERF);


?>
