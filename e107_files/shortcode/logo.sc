parse_str($parm);		// Optional {LOGO=file=file_name} or {LOGO=link=url} or {LOGO=file=file_name&link=url}
						// Paths to image file, link are relative to site base

if (isset($file) && $file && is_readable($file))
{
	$logo = e_HTTP.$file;						// HTML path
	$path = e_BASE.$file;						// PHP path
}
else if (is_readable(THEME."images/e_logo.png"))
{
	$logo = THEME_ABS."images/e_logo.png";		// HTML path
	$path = THEME."images/e_logo.png";			// PHP path
}
else
{
	$logo = e_IMAGE_ABS."logo.png";				// HTML path
	$path = e_IMAGE.$logo;						// PHP path
}

$dimensions = getimagesize($path);

$image = "<img class='logo' src='".$logo."' style='width: ".$dimensions[0]."px; height: ".$dimensions[1]."px' alt='".SITENAME."' />\n";

if (isset($link) && $link) 
{
  if ($link == 'index') 
  {
	$image = "<a href='".e_HTTP."index.php'>".$image."</a>";
  }
  else
  {	
	$image = "<a href='".e_HTTP.$link."'>".$image."</a>";
  }
}

return $image;