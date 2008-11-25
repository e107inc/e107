<?php
// $Id: main.php,v 1.2 2008-11-25 16:26:03 mcfly_e107 Exp $
function url_pm_main($parms)
{
	if(isset($parms['box']))
	{
		return e_PLUGIN_ABS."pm/pm.php?{$parms['box']}";
	}

	if(isset($parms['send']))
	{
		return e_PLUGIN_ABS."pm/pm.php?send";
	}

}
