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
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once('../class2.php');
if (!getperms('0')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}

require_once('auth.php');

if (isset($_POST['do_check']))
{
	if (isset($_POST['check_duplicates']))
	{
		$result = checkDuplicateField('loginname');
		$ns->tablerender(LAN_CKUSER_07,$result);
	}
	if (isset($_POST['check_mixed']))
	{
		$result = checkUserLogin();
		$ns->tablerender(LAN_CKUSER_17,$result);
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
			<input class='tbox' type='checkbox' name='check_mixed' value='1' />
		</td>
		<td class='forumheader3'>".LAN_CKUSER_16."</td>
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
					<tr><td class='fcaption'>".$hdg1."</td><td class='fcaption'>".LAN_CKUSER_10."</td>
						<td class='fcaption'>".$hdg2."</td><td class='fcaption'>".LAN_CKUSER_11."</td></tr>";
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


function checkUserLogin()
{
	global $sql;
	$result = "<table style='".ADMIN_WIDTH."' class='fborder'>";
	$query = "SELECT u.user_name AS un1, u.user_loginname AS ul1, u.user_id AS ui1, x.user_id AS ui2, x.user_name AS un2, x.user_loginname AS ul2 FROM `e107_user` as u 
			LEFT JOIN `e107_user` as x ON u.user_name=x.user_loginname WHERE x.user_id != u.user_id ";
	if ($sql->db_Select_gen($query))
	{
		$result .= "<tr><td colspan='3' class='fcaption' style='text-align:center'>".LAN_CKUSER_18."</td>
					<td colspan='3' class='fcaption' style='text-align:center'>".LAN_CKUSER_19."</td></tr>
				<tr><td class='fcaption'>".LAN_CKUSER_10."</td>
					<td class='fcaption'>".LAN_CKUSER_09."</td>
					<td class='fcaption'>".LAN_CKUSER_11."</td>
					<td class='fcaption'>".LAN_CKUSER_10."</td>
					<td class='fcaption'>".LAN_CKUSER_09."</td>
					<td class='fcaption'>".LAN_CKUSER_11."</td></tr>";
		while ($row = $sql->db_Fetch(MYSQL_ASSOC))
		{
			$result .= "<tr><td class='forumheader3'>".$row['ui1']."</td>
						<td class='forumheader3'>".$row['ul1']."</td>
						<td class='forumheader3'>".$row['un1']."</td>
						<td class='forumheader3'>".$row['ui2']."</td>
						<td class='forumheader3'>".$row['ul2']."</td>
						<td class='forumheader3'>".$row['un2']."</td></tr>";
		}
	}
	else
	{
		$result .= "<tr><td style='width:90%; text-align:center' class='forumheader3'>".LAN_CKUSER_15."</td></tr>";
	}
	$result .= '</table>';
	return $result;
}

?>
