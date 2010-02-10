include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_user.php");

global $tp;
if (substr($parm, -5) == '-link')
{
	$parm = substr($parm, 0, -5);
	return ($user_hideemail && !ADMIN) ? "<i>".LAN_143."</i>" : $tp->toHTML($parm,TRUE);
}
else
{
	return ($user_hideemail && !ADMIN) ? "<i>".LAN_143."</i>" : $parm;
}



