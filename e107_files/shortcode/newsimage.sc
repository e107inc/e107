
if(!defined("NEWSATTACH") || !is_numeric($parm))
{
	return;
}

$images = FALSE;
$attach = explode(chr(1), NEWSATTACH);
foreach($attach as $attachment)
{
	if(strstr($attachment, "image:"))
	{
		$images =  explode("|", str_replace("image:", "", $attachment));
	}
}

if(is_array($images))
{
	return "<img src='".e_IMAGE."newspost_images/".$images[($parm-1)]."' alt='' />";
}
