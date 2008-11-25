<?php
// $Id: main.php,v 1.3 2008-11-25 17:38:56 mcfly_e107 Exp $
function url_pm_main($parms)
{
	switch($parms['f'])
	{
		case 'box':
			return e_PLUGIN_ABS."pm/pm.php?{$parms['box']}";
			break;

		case 'send':
			return e_PLUGIN_ABS."pm/pm.php?send";
			break;
	}
}
