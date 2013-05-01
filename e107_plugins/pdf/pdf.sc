//<?
// * e107 website system Copyright (C) 2008-2013 e107 Inc (e107.org)
/**
 *	e107 pdf generation plugin
 *
 */

if (!plugInstalled('pdf')) 
{
	return;
}
$parms = explode("^",$parm);

if (defined("ICONPRINTPDF") && file_exists(THEME."images/".ICONPRINTPDF)) 
{
	$icon = "<img src='".THEME_ABS."images/".ICONPRINTPDF."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' />";
}
else
{
	$icon = deftrue('e_BOOTSTRAP') ? "<i class='icon-book'></i>" : "<img src='".e_PLUGIN_ABS."pdf/images/pdf_16.png' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' />";
}

return " <a href='".e_PLUGIN_ABS."pdf/pdf.php?{$parms[1]}'>".$icon."</a>";
