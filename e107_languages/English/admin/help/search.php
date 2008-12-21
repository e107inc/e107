<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id: search.php,v 1.2 2008-12-21 11:39:34 secretr Exp $
 *
 * Search Admin Help
 * 
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Search Help";
$text = "
<p>
	If your MySQL server version supports it you can switch 
	to the MySQL sort method which is faster than the PHP sort method. See preferences.
</p>
<p>
	If your site includes Ideographic languages such as Chinese and Japanese you must 
	use the PHP sort method and switch whole word matching off.
</p>
";
$ns->tablerender($caption, $text);
?>