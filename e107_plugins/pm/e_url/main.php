<?php
// $Id: main.php,v 1.1 2008-12-02 00:33:29 secretr Exp $
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
