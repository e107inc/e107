// $Id$
// use $parm to restrict visibility based on matching part of the URL
if($parm && !strpos(e_SELF,$parm))
{
	return;
}
else
{
	include(e_PLUGIN."newforumposts_main/newforumposts_main.php");
}
