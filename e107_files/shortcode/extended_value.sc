//USAGE:  {EXTENDED_VALUE=<field_name>.<user_id>}
//EXAMPLE: {EXTENDED_VALUE=user_gender.5}  will show the value of the extended field user_gender for user #5
$parms = explode(".", $parm);
global $currentUser, $sql, $tp;
require_once(e_HANDLER."user_extended_class.php");
$ueStruct = e107_user_extended::user_extended_getStruct();
if($parms[1] == USERID)
{
	$udata = $currentUser;
}
else
{
	$udata = getcachedvars('userinfo_'.$parms[1]);
	if(!$udata)
	{
		$qry = "
		SELECT u.*, ue.* FROM #user AS u
		LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id
		WHERE u.user_id='{$parms[1]}'
		";
		if($sql->db_Select_gen($qry))
		{
			$udata = $sql->db_Fetch();
			cachevars('userinfo_'.$parms[1], $udata);
		}
	}
}
if(!check_class($ueStruct[$parms[0]]['user_extended_struct_applicable'], $udata['user_class']) || !check_class($ueStruct[$parms[0]]['user_extended_struct_read']))
{
	return FALSE;
}

if($ueStruct[$parms[0]]['user_extended_struct_type'] == '4')
{
	$tmp = explode(",",$ueStruct[$parms[0]]['user_extended_struct_values']);
	if($sql->db_Select($tmp[0],"{$tmp[1]}, {$tmp[2]}","{$tmp[1]} = {$udata[$parms[0]]}"))
	{
		$row = $sql->db_Fetch();
		$ret_data = $row[$tmp[2]];
	}
	else
	{
		$ret_data = FALSE;
	}
}
else
{
	$ret_data = $udata[$parms[0]];
}
if($ret_data)
{
	return $tp->toHTML($ret_data, TRUE, "", "class:{$udata['user_class']}");
}
return FALSE;
