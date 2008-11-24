<?php

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
