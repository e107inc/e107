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
|     $Source: /cvs_backup/e107_0.7/e107_admin/check_user.php,v $
|     $Revision: 1.2 $
|     $Date: 2009-01-04 09:45:05 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
if (!getperms('0')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}

define('LAN_CKUSER_01','Check user database');
define('LAN_CKUSER_02','This will check for various potential problems with your user database');
define('LAN_CKUSER_03','If you have a lot of users, it may take some time, or even time out');
define('LAN_CKUSER_04','Proceed');
define('LAN_CKUSER_05','Check for duplicate login names');
define('LAN_CKUSER_06','Select functions to perform');
define('LAN_CKUSER_07','Duplicate User Names found');
define('LAN_CKUSER_08','No duplicates found');
define('LAN_CKUSER_09','User Name');
define('LAN_CKUSER_10','User ID');
define('LAN_CKUSER_11','Display Name');
define('LAN_CKUSER_12','Check for duplicate email addresses');
define('LAN_CKUSER_13','Duplicate email addresses found');
define('LAN_CKUSER_14','Email address');
define('LAN_CKUSER_15','');

require_once("auth.php");

if (isset($_POST['do_check']))
{
	if (isset($_POST['check_duplicates']))
	{
		$result = checkDuplicateField('loginname');
		$ns->tablerender(LAN_CKUSER_07,$result);
	}
	if (isset($_POST['check_dupdisplay']))
	{
		$result = checkDuplicateField('email');
		$ns->tablerender(LAN_CKUSER_13,$result);
	}
}


$text = "
	<form method='post' action='".e_SELF."'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<colgroup span='2'>
		<col style='width: 10%'></col>
		<col style='width: 90%'></col>
	</colgroup>

	<tr>
		<td colspan='2' style='text-align:center' class='forumheader2'>".LAN_CKUSER_06."</td>
	</tr>

	<tr>
		<td class='forumheader3'>
			<input class='tbox' type='checkbox' name='check_duplicates' value='1' />
		</td>
		<td class='forumheader3'>".LAN_CKUSER_05."</td>
	</tr>

	<tr>
		<td class='forumheader3'>
			<input class='tbox' type='checkbox' name='check_dupdisplay' value='1' />
		</td>
		<td class='forumheader3'>".LAN_CKUSER_12."</td>
	</tr>

	<tr>
		<td colspan='2' style='text-align:center' class='forumheader3'><input  class='button' type='submit' name='do_check' value='".LAN_CKUSER_04."'></td>
	</tr>
	</table>
	</form>
	";

$ns->tablerender(LAN_CKUSER_01, $text);


require_once("footer.php");


function checkDuplicateField($checkField)
{
	global $sql;
	switch ($checkField)
	{
		case 'loginname' :
			$dupField = 'user_loginname';
			$otherField = 'user_email';
			$hdg1 = LAN_CKUSER_09;
			$hdg2 = LAN_CKUSER_14;
			break;
		case 'email' :
			$dupField = 'user_email';
			$otherField = 'user_name';
			$hdg2 = LAN_CKUSER_09;
			$hdg1 = LAN_CKUSER_14;
			break;
		default :
			return "Error";
	}
		$result = '';
		$qry = "SELECT count({$dupField}) AS u_count, {$dupField} FROM #user GROUP BY {$dupField} HAVING u_count > 1 ";
		if ($sql->db_Select_gen($qry))
		{
			$duplicates = array();
			while ($row = $sql->db_Fetch(MYSQL_ASSOC))
			{
				$duplicates[] = $row[$dupField];
			}
			$result .= "<table style='".ADMIN_WIDTH."' class='fborder'>
					<colgroup>
					<col style='width:30%' />
					<col style='width:10%' />
					<col style='width:30%' />
					<col style='width:30%' />
					</colgroup>
					<tr><td class='forumheader2'>".$hdg1."</td><td class='forumheader2'>".LAN_CKUSER_10."</td>
						<td class='forumheader2'>".$hdg2."</td><td class='forumheader2'>".LAN_CKUSER_11."</td></tr>";
			foreach ($duplicates as $ul)
			{
				$doneName = FALSE;
				if ($ucount = $sql->db_Select_gen("SELECT user_id, user_name, user_loginname, user_email FROM `#user` WHERE {$dupField}='".$ul."'"))
				{
					while ($row = $sql->db_Fetch(MYSQL_ASSOC))
					{
						$result .= '<tr>';
						if (!$doneName)
						{
							$result .= "<td class='forumheader3' rowspan='".$ucount."'>".$row[$dupField]."</td>";
							$doneName = TRUE;
						}
						$result .= "<td class='forumheader3'>".$row['user_id']."</td>
								<td class='forumheader3'>".$row[$otherField]."</td><td class='forumheader3'>".$row['user_name']."</td></tr>";
					}
				}
			}
			$result .= '</table>';
		}
		else
		{
			$result = LAN_CKUSER_08;
		}
	return $result;
}

?>
