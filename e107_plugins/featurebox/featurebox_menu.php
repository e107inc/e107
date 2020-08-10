<?php
/*
* Copyright (c) 2014 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
*
* Gallery Template 
*/

if (!defined('e107_INIT')) { exit; }

// e107::Lan('featurebox', 'front');
e107::includeLan(e_PLUGIN.'featurebox/languages/'.e_LANGUAGE.'_admin_featurebox.php'); // This line added to admin warning

$type 	= vartrue(e107::getPlugPref('featurebox','menu_category'),'bootstrap_carousel');		
$text = e107::getParser()->parseTemplate("{FEATUREBOX|".$type."}");

if(empty($text))
{
	e107::getMessage()->addDebug("DEBUG: There are no featurebox items using the ".$type." template");
}

echo $text;
unset($text);


