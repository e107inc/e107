// $Id: newforumposts.sc,v 1.1.1.1 2006-12-02 04:35:30 mcfly_e107 Exp $
// use $parm to restrict visibility based on matching part of the URL
if($parm && !strpos(e_SELF,$parm))
{
	return;
}
else
{
	include(e_PLUGIN."newforumposts_main/newforumposts_main.php");
}
