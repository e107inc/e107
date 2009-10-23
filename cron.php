#!/usr/bin/php -q
<?php
/*
+ ----------------------------------------------------------------------------+
||     e107 website system
|
|     Copyright (C) 2001-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/cron.php,v $
|     $Revision: 1.4 $
|     $Date: 2009-10-23 09:08:15 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

// Usage: [full path to this script]cron.php --u=admin --p=password // use your admin login. 

$_E107['cli'] = TRUE;
require_once(realpath(dirname(__FILE__)."/class2.php"));
// from the plugin directory:
// realpath(dirname(__FILE__)."/../../")."/";

//echo "\n\nUSERNAME= ".USERNAME."\n";
//echo "\nUSEREMAIL= ".USEREMAIL."\n";

if($pref['e_cron_pref']) // grab cron
{
	foreach($pref['e_cron_pref'] as $func=>$cron)
	{
    	if($cron['active']==1)
		{
        	$list[$func] = $cron;
		}
	}
}

require_once(e_HANDLER."cron_class.php");

$cron = new CronParser();
foreach($list as $func=>$val)
{
	$cron->calcLastRan($val['tab']);

	$due = $cron->getLastRanUnix();
    if($due > (time()-45))
	{
		if(is_readable(e_PLUGIN.$val['path']."/e_cron.php"))
		{
			//	echo date("r")." ".$func."\n";
			require_once(e_PLUGIN.$val['path']."/e_cron.php");

			require_once(e_HANDLER."mail.php");
			$message = "Your Cron Job worked correctly. Sent at ".date("r").".";
	    	sendemail($pref['siteadminemail'], "e107 - TEST Email Sent by cron.", $message, $pref['siteadmin'],$pref['siteadminemail'], $pref['siteadmin']);

	      	if(call_user_func($func)===FALSE)
			{
	        	// echo "\nerror running the function ".$func.".\n"; log the error.
			}
		}
	}
    //  echo "Cron Unix = ". $cron->getLastRanUnix();
  	//	echo "<br />Now = ".time();

}




// echo "<br />Cron '$cron_str0' last due at: " . date('r', $cron->getLastRanUnix()) . "<p>";
// $cron->getLastRan() returns last due time in an array
// print_a($cron->getLastRan());
// echo "Debug:<br />" . nl2br($cron->getDebug());
 /*
$cron_str1 = "3 12 * * *";
if ($cron->calcLastRan($cron_str1))
{
   echo "<p>Cron '$cron_str1' last due at: " . date('r', $cron->getLastRanUnix()) . "<p>";
   print_r($cron->getLastRan());
}
else
{
   echo "Error parsing";
}
echo "Debug:<br />" . nl2br($cron->getDebug());
*/

exit;
?>