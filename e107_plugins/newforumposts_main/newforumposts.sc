// $Id: newforumposts.sc,v 1.1 2006-11-10 07:15:33 e107coders Exp $
// use $parm to restrict visibility based on matching part of the URL
if($parm && !strpos(e_SELF,$parm))
{
	return;
}
else
{
	include(e_PLUGIN."newforumposts_main/newforumposts_main.php");
}
