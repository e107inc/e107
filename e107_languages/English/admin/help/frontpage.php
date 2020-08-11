<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * Frontpage Admin Help
 * 
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Front Page Help";
/* FIXME - English native speakers: what should stay and what should go? 
	<p>
		The list of rules are scanned in turn, until the class of the current user matches. 
		This then determines the user's front (home) page, and also the page he sees immediately after login.
	</p>
 */
$text = "
<p>
	From this screen you can choose what to display as the front page of your site, the default is news. You can also determine whether
	users are sent to a particular page after logging in.
</p>
<p>
	The rules are searched in order, to find the first where the current user belongs to the class specified in the rule. 
	That rule then determines the front (home) page and any specific post-login page. If no rule matches, news.php is set as the home page.
</p>
<p>
	The user is sent to the specified &quot;Post-login page&quot; (if specified) immediately following a login.
</p>
";
$ns->tablerender($caption, $text, 'admin_help');
