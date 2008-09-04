/*
 * e107 website system (c) 2001-2008 Steve Dunstan (e107.org)
 * $Id: pdf.sc,v 1.2 2008-09-04 20:07:34 e107steved Exp $
*/

if (defined("ICONPRINTPDF") && file_exists(THEME."images/".ICONPRINTPDF)) 
{
	$icon = THEME_ABS."images/".ICONPRINTPDF;
}
else
{
	$icon = e_PLUGIN_ABS."pdf/images/pdf_16.png";
}
$parms = explode("^",$parm);

//core		//return "<a href='".e_BASE."pdf.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' /></a>";
//plugin	//return "<a href='".e_PLUGIN."pdf/pdf.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' /></a>";

return " <a href='".e_PLUGIN_ABS."pdf/pdf.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' /></a>";
