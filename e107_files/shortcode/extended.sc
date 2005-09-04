//USAGE:  {EXTENDED=<field_name>.[text|value|icon].<user_id>}
//EXAMPLE: {EXTENDED=user_gender.value.5}  will show the value of the extended field user_gender for user #5
$parms = explode(".", $parm);
global $currentUser, $sql, $tp;
$ueStruct = getcachedvars("user_extended_struct");
if(!$ueStruct)
{
	require_once(e_HANDLER."user_extended_class.php");
	$ueStruct = e107_user_extended::user_extended_getStruct();
	cachevars("user_extended_struct", $ueStruct);
}

if($parms[2] == USERID)
{
	$udata = $currentUser;
}
else
{
	$udata = getcachedvars('userinfo_'.$parms[2]);
	if(!$udata)
	{
		$qry = "
		SELECT u.*, ue.* FROM #user AS u
		LEFT JOIN #user_extended AS ue ON ue.user_extended_id = u.user_id
		WHERE u.user_id='{$parms[2]}'
		";
		if($sql->db_Select_gen($qry))
		{
			$udata = $sql->db_Fetch();
			cachevars('userinfo_'.$parms[2], $udata);
		}
	}
}

$udata['user_class'] .= ($udata['user_class'] == "" ? "" : ",");
$udata['user_class'] .= e_UC_PUBLIC.",".e_UC_MEMBER;
if($udata['user_admin'] == 1)
{
	$udata['user_class'].= ",".e_UC_ADMIN;
}

//echo "{$parms[0]} read class = {$ueStruct["user_".$parms[0]]['user_extended_struct_read']} <br />";
//echo ($ueStruct["user_".$parms[0]]['user_extended_struct_read'] == e_UC_READONLY && (ADMIN || $udata['user_id'] == USERID));
if (
!check_class($ueStruct["user_".$parms[0]]['user_extended_struct_applicable'], $udata['user_class'])
|| !check_class($ueStruct["user_".$parms[0]]['user_extended_struct_read'])
|| ($ueStruct["user_".$parms[0]]['user_extended_struct_read'] == e_UC_READONLY && (!ADMIN && $udata['user_id'] != USERID))
|| (!ADMIN && substr($ueStruct["user_".$parms[0]]['user_extended_struct_parms'], -1) == 1 
&& strpos($udata['user_hidden_fields'], "^user_".$parms[0]."^") !== FALSE && $parms[2] != USERID)
)
{
	return FALSE;
}

if ($parms[1] == 'text')
{
	return ($ueStruct["user_".$parms[0]]['user_extended_struct_text']) ? $ueStruct["user_".$parms[0]]['user_extended_struct_text'] : TRUE;
}

if ($parms[1] == 'icon')
{
	if(defined(strtoupper($parms[0])."_ICON"))
	{
		return constant(strtoupper($parms[0])."_ICON");
	}
	elseif(file_exists(e_IMAGE."user_icons/{$parms[0]}.png"))
	{
		return "<img src='".e_IMAGE."user_icons/{$parms[0]}.png' style='width:16px; height:16px' alt='' />";
	}
	return "";
}

if ($parms[1] == 'value')
{
	$uVal = str_replace(chr(1), "", $udata['user_'.$parms[0]]);
	if($ueStruct["user_".$parms[0]]['user_extended_struct_type'] == '4')
	{
		$tmp = explode(",",$ueStruct["user_".$parms[0]]['user_extended_struct_values']);
		if($sql->db_Select($tmp[0],"{$tmp[1]}, {$tmp[2]}","{$tmp[1]} = {$uVal}"))
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
		$ret_data = $uVal;
	}
	if($ret_data)
	{
		return $tp->toHTML($ret_data, TRUE, "no_make_clickable", "class:{$udata['user_class']}");
	}
	return FALSE;
}
return TRUE;
