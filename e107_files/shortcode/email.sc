@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_user.php");
@include_once(e_LANGUAGEDIR."English/lan_user.php");
if (substr($parm, -5) == '-link')
{
	$parm = substr($parm, 0, -5);
	return ($user_hideemail && !ADMIN) ? "<i>".LAN_143."</i>" : "<a href='mailto:".$parm."'>".$parm."</a>";
}
else
{
	return ($user_hideemail && !ADMIN) ? "<i>".LAN_143."</i>" : $parm;
}



