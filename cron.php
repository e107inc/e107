#!/usr/bin/php -q
<?php
/*
+ ----------------------------------------------------------------------------+
||     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/cron.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

// Usage: [full path to this script]cron.php --u=admin --p=password // use your admin login. 
// test

$_E107['cli'] = TRUE;
$_E107['debug'] = FALSE;
$_E107['no_online'] = TRUE;

require_once(realpath(dirname(__FILE__)."/class2.php"));

	$pwd = ($_E107['debug'] && $_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : trim($_SERVER['argv'][1]);
		
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

	$list = array();

	$sql = e107::getDb();
	if($sql->db_Select("cron",'cron_function,cron_tab','cron_active =1'))
	{
		while($row = $sql->db_Fetch(MYSQL_ASSOC))
		{
			list($class,$function) = explode("::",$row['cron_function'],2);			
			$key = $class."__".$function;
			
			$list[$key] = array(
				'path'		=> $class,
				'active'	=> 1,	
				'tab'		=> $row['cron_tab'],
				'function' 	=> $function,
				'class'		=> $class				
			);				
		}	
	}
	
	
	// foreach($pref['e_cron_pref'] as $func=>$cron)
	// {
    	// if($cron['active']==1)
		// {
        	// $list[$func] = $cron;
		// }
	// }



if($_E107['debug'] && $_SERVER['QUERY_STRING'])
{
	echo "<h1>Cron Lists</h1>";
	print_a($list);
}

require_once(e_HANDLER."cron_class.php");


$cron = new CronParser();


foreach($list as $func=>$val)
{
	$cron->calcLastRan($val['tab']);
	$due = $cron->getLastRanUnix();
	
	if($_E107['debug'])
	{
		echo "<br />Cron: ".$val['function'];
	}
		
    if($due > (time()-45))
	{
		if($_E107['debug'])	{ 	echo "<br />Running Now...<br />path: ".$val['path']; }
		
		if(($val['path']=='_system') || is_readable(e_PLUGIN.$val['path']."/e_cron.php"))
		{
			if($val['path'] != '_system') // this is correct. 
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
					if($_E107['debug'])	{ echo "<br />Method Found: ".$classname."::".$val['function']."()"; }
					
					$status = call_user_func(array($obj,$val['function']));
					if(!$status)
					{
						//TODO log error in admin log. 
						// echo "\nerror running the function ".$func.".\n"; // log the error.
						if($_E107['debug'])	{ 	echo "<br />Method returned False: ".$val['function']; }
					}					 					
				}
				else
				{
					if($_E107['debug'])	{ 	echo "<br />Couldn't find method: ".$val['function']; }
				}
			}
			else
			{
				if($_E107['debug'])	{ 	echo "<br />Couldn't find class: ".$classname; }
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