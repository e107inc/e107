@include(e_LANGUAGEDIR.e_LANGUAGE."/lan_user.php");
@include(e_LANGUAGEDIR."English/lan_user.php");
list($email,$with_link) = explode("-",$parm,2);
if($with_link != "")
{
	return ($user_hideemail && !ADMIN) ? "<i>".LAN_143."</i>" : "<a href='mailto:".$email."'>".$email."</a>";
}
else
{
	return ($user_hideemail && !ADMIN) ? "<i>".LAN_143."</i>" : $email;
}



