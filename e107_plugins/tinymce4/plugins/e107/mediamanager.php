<?php

require_once("../../../../class2.php");

$types = array('img','video','glyph');

$bbcode = in_array(e_QUERY,$types) ? e_QUERY : 'img';

if($bbcode === 'video')
{
	$bbcode .= '&youtube=1';
}

header("Location: ".e_ADMIN_ABS.'image.php?mode=main&action=dialog&for='.$_SESSION['media_category'].'&tagid=&iframe=1&bbcode='.$bbcode, true);
exit; 


