<?php
// $Id$
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
