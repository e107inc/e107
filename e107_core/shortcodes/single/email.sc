e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/lan_user.php");

if(!is_string($parm))
{
    return null;
}

if (substr($parm, -5) === '-link')
{
	$parm = substr($parm, 0, -5);
	return ($user_hideemail && !ADMIN) ? "<i>".LAN_143."</i>" : e107::getParser()->toHTML($parm,TRUE);
}
else
{
	return ($user_hideemail && !ADMIN) ? "<i>".LAN_143."</i>" : ($parm);
}



