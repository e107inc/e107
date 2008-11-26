<?php
// $Id: forum.php,v 1.1 2008-11-26 03:24:51 mcfly_e107 Exp $
function url_forum_forum($parms)
{
	switch($parms['func'])
	{
		case 'view':
			return e_PLUGIN."forum/viewforum.php?{$parms['id']}";
			break;
	}	
}
