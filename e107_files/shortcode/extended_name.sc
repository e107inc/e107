//USAGE:  {EXTENDED_NAME=<field_name>.<user_id>}
//EXAMPLE: {EXTENDED=user_gender.5}  will show the name of the extended field user_gender for user #5
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

return $ueStruct[$parms[0]]['user_extended_struct_name'];
