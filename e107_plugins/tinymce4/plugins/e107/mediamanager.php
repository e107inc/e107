<?php

require_once("../../../../class2.php");
require_once(__DIR__."/../../wysiwyg_class.php");

if(!USER || !wysiwyg::isAllowed())
{
	http_response_code(403);
	header('Content-Length: 0');
	exit;
}

$types = array('img','video','glyph');

$bbcode = in_array(e_QUERY,$types) ? e_QUERY : 'img';

if($bbcode === 'video')
{
	$bbcode .= '&youtube=1';
}

$category = rawurlencode(varset($_SESSION['media_category'], ''));

header("Location: ".e_ADMIN_ABS.'image.php?mode=main&action=dialog&for='.$category.'&tagid=&iframe=1&bbcode='.$bbcode, true);
exit;


