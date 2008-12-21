/*
 * e107 website system Copyright (C) 2001-2008 e107 Inc (e107.org)
 * $Id: pdf.sc,v 1.3 2008-12-21 12:03:28 e107steved Exp $
*/

if (!plugInstalled('pdf')) 
{
	return;
}

if (defined("ICONPRINTPDF") && file_exists(THEME."images/".ICONPRINTPDF)) 
{
	$icon = THEME_ABS."images/".ICONPRINTPDF;
}
else
{
	$icon = e_PLUGIN_ABS."pdf/images/pdf_16.png";
}
$parms = explode("^",$parm);
return " <a href='".e_PLUGIN_ABS."pdf/pdf.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' /></a>";
