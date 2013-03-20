<?php
/*
* Copyright (c) 2013 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Gallery Template 
*/
if (!defined('e107_INIT')) { exit; }

// e107::Lan('featurebox', 'front');

$type 	= vartrue(e107::getPlugPref('featurebox','menu_category'),'bootstrap_carousel');		
$text = e107::getParser()->parseTemplate("{FEATUREBOX|".$type."}");

if(!$text)
{
	echo "<div class='alert alert-block'>There are no featurebox items assigned to the ".$type." template</div>";
//	e107::getMessage()->addDebug("There are no featurebox items using the ".$type." template");
}

echo $text;
unset($text);

?>
