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
|     $Revision: 1.6 $
|     $Date: 2009-10-24 10:07:30 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

// Usage: [full path to this script]cron.php --u=admin --p=password // use your admin login. 

$_E107['cli'] = TRUE;
require_once(realpath(dirname(__FILE__)."/class2.php"));
	
	$pwd = trim($_SERVER['argv'][1]);
	if($pref['e_cron_pwd'] != $pwd)
	{
		require_once(e_HANDLER."mail.php");
		$message = "Your Cron Schedule is not configured correctly. Your passwords do not match.
		<br /><br /> 
		Sent from cron: ".$pwd."<br />
		Stored in e107: ".$pref['e_cron_pwd']."<br /><br />
		You should regenerate the cron command in admin and enter it again in your server configuration. 
		";
						
	    sendemail($pref['siteadminemail'], "e107 - Cron Schedule Misconfigured.", $message, $pref['siteadmin'],$pref['siteadminemail'], $pref['siteadmin']);
		exit;
	}

e107::getCache()->CachePageMD5 = '_';
e107::getCache()->set('cronLastLoad',time(),TRUE,FALSE,TRUE);



// from the plugin directory:
// realpath(dirname(__FILE__)."/../../")."/";


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
		if(($val['path']=='_system') || is_readable(e_PLUGIN.$val['path']."/e_cron.php"))
		{
			if($val['path'] != '_system')
			{
				include_once(e_PLUGIN.$val['path']."/e_cron.php");
			}
				
			$classname = $val['class']."_cron";
			if(class_exists($classname))
			{
				$obj = new $classname;
				if(method_exists($obj,$val['function']))
				{
					//	$mes->add("Executing config function <b>".$key." : ".$method_name."()</b>", E_MESSAGE_DEBUG);
					$status = call_user_func(array($obj,$val['function']));
					if(!$status)
					{
						//TODO log error in admin log. 
						// echo "\nerror running the function ".$func.".\n"; log the error.
					}					 					
				}
			}

		}
	}
    //  echo "Cron Unix = ". $cron->getLastRanUnix();
  	//	echo "<br />Now = ".time();

}



class _system_cron 
{
	
	// See admin/cron.php to configure more core cron function to be added below.  
	
	
	function myfunction() 
	{
	    // Whatever code you wish.
	}
	
		
	function sendEmail() // Test Email. 
	{
		global $pref;
	    require_once(e_HANDLER."mail.php");
		$message = "Your Cron test worked correctly. Sent at ".date("r").".";
		
	    sendemail($pref['siteadminemail'], "e107 - TEST Email Sent by cron.".date("r"), $message, $pref['siteadmin'],$pref['siteadminemail'], $pref['siteadmin']);
	}
	
	
	
	
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