
if(!defined("NEWSATTACH") || !is_numeric($parm))
{
	return;
}

$files = FALSE;
$attach = explode(chr(1), NEWSATTACH);
foreach($attach as $attachment)
{
	if(strstr($attachment, "file:"))
	{
		$files =  explode("|", str_replace("file:", "", $attachment));
	}
}

if(is_array($files))
{
	return "<img src='".e_IMAGE."generic/download.png' alt='' style='vertical-align: middle;' /> <a href='".e_FILE."downloads/".$files[($parm-1)]."'>".$files[($parm-1)]."</a>";
}
