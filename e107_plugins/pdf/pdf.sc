if (defined("ICONPRINTPDF") && file_exists(THEME."images/".ICONPRINTPDF)) 
{
	$icon = THEME."images/".ICONPRINTPDF;
}
else
{
	$icon = e_PLUGIN."pdf/images/pdf_16.png";
}
$parms = explode("^",$parm);

//core		//return "<a href='".e_BASE."pdf.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' /></a>";
//plugin	//return "<a href='".e_PLUGIN."pdf/pdf.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' /></a>";

return " <a href='".e_PLUGIN."pdf/pdf.php?{$parms[1]}'><img src='".$icon."' style='border:0' alt='{$parms[0]}' title='{$parms[0]}' /></a>";
