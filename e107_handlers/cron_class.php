<?php
/*
+ ----------------------------------------------------------------------------+
||     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc 
|     http://e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/cron_class.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

define ('CRON_MAIL_DEBUG', false);
define ('CRON_RETRIGGER_DEBUG', false);


/**
 *
 */
class _system_cron
{
	function __construct()
	{
		e107::coreLan('cron', true);


	}
	// See admin/cron.php to configure more core cron function to be added below.  


	/**
	 * @return void
	 */
	function myfunction()
	{
	    // Whatever code you wish.
	}
	
	/**
	 * Update the current Repo. of this e107 installation.  (eg. e107 on github)
	 */
	function gitrepo()
	{
		$mes = e107::getMessage();
		$fl = e107::getFile();
		
		if(is_dir(e_BASE.".git")) // Check it's a Git Repo
		{
			$return = $fl->gitPull();

			$mes->addSuccess($return);
			
			if(unlink(e_BASE."install.php"))
			{
				$mes->addDebug("Removed install.php");	
			}

			if(!deftrue('e_DEVELOPER')) // Leave development files intact if developer mode is active.
			{
				$fl->removeDir(e_BASE.'e107_tests');
				$fl->removeDir(e_BASE.'.github');
				unlink(e_BASE."composer.json");
				unlink(e_BASE."composer.lock");
			}
		}
		else
		{
			$mes->addError(LAN_CRON_66);
		}
		
		$fl->chmod(e_BASE."cron.php",0755);
		$fl->chmod(e_HANDLER."bounce_handler.php",0755);

		e107::getCache()->clearAll('system');
		e107::getCache()->clearAll('css');
		e107::getCache()->clearAll('js');
		e107::getCache()->clearAll('library');
		e107::getCache()->clearAll('browser');
	}




	/**
	 * Update the current Theme Repo
	 * When using private repos on Github, you'll need to get a personal access token @see https://help.github.com/en/articles/creating-a-personal-access-token-for-the-command-line
	 * (with 'repo' access) and then modify the .git/config file :
	 * @example:
	 * [remote "origin"]
	 * url = https://[TOKEN]@github.com/[USER]/[REPO].git
	 */
	function gitrepoTheme()
	{
		$mes = e107::getMessage();
		$fl = e107::getFile();
		$theme = e107::getPref('sitetheme');

		if(is_dir(e_THEME.$theme."/.git")) // Check it's a Git Repo
		{
			$return = $fl->gitPull($theme, 'theme');

			$mes->addSuccess($return);

		}
		else
		{
			$mes->addError(LAN_CRON_67);
		}

	}


	
	
	
	/**
	 * Burnsy - This is just a test
	 * 
	 */
	function checkCoreUpdate () // Check if there is an e107 Core update and email Site Admin if so
	{
		if(!$data = e107::coreUpdateAvailable())
		{
			return false;
		}

		$pref = e107::getPref();

		$message = "<p>There is a new version of e107 available.<br />
		 Please visit ".$data['infourl']." for further details.</p>
		 <a class='btn btn-primary' href=''>Download v".$data['version']."</a>";

		$eml = array(
					'subject' 		=> "e107 v".$data['version']." is now available.",
					'sender_name'	=> SITENAME . " Automation",
					'html'			=> true,
					'template'		=> 'default',
					'body'			=> $message
				);

		e107::getEmail()->sendEmail($pref['siteadminemail'],  $pref['siteadmin'], $eml);

		return null;
	}


	/**
	 * @return void
	 */
	function sendEmail() // Test Email.
	{
		global $pref, $_E107;

		if($_E107['debug'])
		{
			echo "sendEmail() executed";
			error_log('e107: Cron running _system_cron::sendEmail(); ', E_USER_NOTICE);
		}

		
	  //  require_once(e_HANDLER.'mail.php');
		$message = "Your Cron test worked correctly. Sent on ".date("r").".";

		$message .= "<h2>Environment Variables</h2>";

		$userCon = get_defined_constants(true);
		ksort($userCon['user']);

		$userVars = array();
		foreach($userCon['user'] as $k=>$v)
		{
			if(strpos($k, 'e_') === 0)
			{
				$userVars[$k] = $v;
			}
		}

		$message .= "<h3>e107 PATHS</h3>";
		$message .= $this->renderTable($userVars);

		$message .= "<h3>_SERVER</h3>";
		$message .= $this->renderTable($_SERVER);
		$message .= "<h3>_ENV</h3>";
		$message .= $this->renderTable($_ENV);
		$message .= "<h3>LAST ERROR</h3>";
		$message .= "<pre>".print_r(error_get_last(), true)."</pre>";
		$message .= "<h3>HEADERS LIST</h3>";
		$message .= "<pre>".print_r(headers_list(),true)."</pre>";
	//	$message .= "<h3>Included Files</h3>";
	/*	$included_files = get_included_files();

		foreach ($included_files as $filename)
		{
		    $message .= $filename."<br />";
		}*/

		$eml = array(
					'subject' 		=> "TEST Email Sent by cron. ".date("r"),
				//	'sender_email'	=> $email,
					'sender_name'	=> SITENAME . " Automation",
			//		'replyto'		=> $email,
					'html'			=> true,
					'template'		=> 'default',
					'body'			=> $message
				);

		if(!e107::getEmail()->sendEmail($pref['siteadminemail'],  $pref['siteadmin'], $eml))
		{
			error_log('e107: Cron _system_cron::sendEmail() failed to send email.', E_ERROR);
		}

	   // sendemail($pref['siteadminemail'], "e107 - TEST Email Sent by cron.".date("r"), $message, $pref['siteadmin'],SITEEMAIL, $pref['siteadmin']);
	}

	/**
	 * @param $array
	 * @return string
	 */
	private function renderTable($array)
	{
		$text = "<table class='table table-striped table-bordered' style='width:600px'>";

		foreach($array as $k=>$v)
		{
			$text .= "<tr>
				<td>".$k."</td>
				<td>".print_a($v,true)."</td>
				</tr>
				";

		}

		$text .= "</table>";
		return $text;
	}

	/**
	 * Process the Mail Queue
	 * First create a mail queue then debug with the following:
	   require_once(e_HANDLER."cron_class.php");
	   $cron = new _system_cron;
	   $cron->procEmailQueue(true);
	 * @param bool $debug
	 */
	function procEmailQueue($debug= false)
	{

		$sendPerHit = e107::getConfig()->get('mail_workpertick',5);
		$pauseCount =  e107::getConfig()->get('mail_pause',5);
		$pauseTime =  e107::getConfig()->get('mail_pausetime',2);
			
		if (CRON_MAIL_DEBUG)
		{
			e107::getLog()->addEvent(10,debug_backtrace(),'DEBUG','CRON Email','Email run started',FALSE,LOG_TO_ROLLING);
		}

		$mailManager = e107::getBulkEmail();

		if($debug === true)
		{
			$mailManager->controlDebug(1);
		}

		$mailManager->doEmailTask($sendPerHit,$pauseCount,$pauseTime);
		
		if (CRON_MAIL_DEBUG)
		{
			e107::getLog()->addEvent(10,debug_backtrace(),'DEBUG','CRON Email','Email run completed',FALSE,LOG_TO_ROLLING);
		}
	}

	/**
	 * @return void
	 */
	function procEmailBounce()
	{
		//global $pref;
		if (defset('CRON_MAIL_DEBUG'))
		{
			e107::getLog()->addEvent(10,debug_backtrace(),'DEBUG','CRON Bounce','Bounce processing started',FALSE,LOG_TO_ROLLING);
		}
		require_once(e_HANDLER.'pop_bounce_handler.php');
		$mailBounce = new pop3BounceHandler();
		$mailBounce->processBounces();
		if (defset('CRON_MAIL_DEBUG'))
		{
			e107::getLog()->addEvent(10,debug_backtrace(),'DEBUG','CRON Bounce','Bounce processing completed',FALSE,LOG_TO_ROLLING);
		}
	}

	/**
	 * @return void
	 */
	function procBanRetrigger()
	{
		//global $pref;
		if (CRON_RETRIGGER_DEBUG)
		{
			$e107 = e107::getInstance();
			$e107->admin_log->addEvent(10,debug_backtrace(),'DEBUG','CRON Ban retrigger','Retrigger processing started',FALSE,LOG_TO_ROLLING);
		}
		require_once(e_HANDLER.'iphandler_class.php');
		$ipManager = new banlistManager();
		$ipManager->banRetriggerAction();
		if (CRON_RETRIGGER_DEBUG)
		{
			e107::getLog()->addEvent(10,debug_backtrace(),'DEBUG','CRON Ban Retrigger','Retrigger processing completed',FALSE,LOG_TO_ROLLING);
		}
	}
	

	/**
	 * Creates a backup of the entire database and gzips it into the backup folder.
	 * Also works with large databases.
	 * @return null|void
	 */
	public function dbBackup()
	{
		
		$sql = e107::getDb();
		$file = $sql->backup('*', null, array('gzip'=>true));

		if(empty($file))
		{
			e107::getLog()->addError(LAN_CRON_55)->save('BACKUP');
			return;
		}
		elseif(file_exists($file))
		{
			e107::getLog()->addSuccess(LAN_CRON_56." ".basename($file))->save('BACKUP');
		}

		return null;

		
	}
	
	
	
}






 /* $Id$ */

/**####################################################################################################**\
   Version: V1.01
   Release Date: 12 Sep 2005
   Licence: GPL
   By: Nikol S
   Please send bug reports to ns@eyo.com.au
\**####################################################################################################**/

/* This class is based on the concept in the CronParser class written by Mick Sear http://www.ecreate.co.uk
 * The following functions are direct copies from or based on the original class:
 * getLastRan(), getDebug(), debug(), expand_ranges()
 *
 * Who can use this class?
 * This class is idea for people who can not use the traditional Unix cron through shell.
 * One way of using is embedding the calling script in a web page which is often visited.
 * The script will work out the last due time, by comparing with run log timestamp. The scrip
 * will invoke any scripts needed to run, be it deleting older table records, or updating prices.
 * It can parse the same cron string used by Unix.
 */

/* Usage example:

$cron_str0 = "0,12,30-51 3,21-23,10 1-25 9-12,1 0,3-7";
require_once("CronParser.php");
$cron = new CronParser();
$cron->calcLastRan($cron_str0);
// $cron->getLastRanUnix() returns an Unix timestamp
echo "Cron '$cron_str0' last due at: " . date('r', $cron->getLastRanUnix()) . "<p>";
// $cron->getLastRan() returns last due time in an array
print_r($cron->getLastRan());
echo "Debug:<br />" . nl2br($cron->getDebug());

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

 *#######################################################################################################
 */

class CronParser
{

 	var $bits = Array(); //exploded String like 0 1 * * *
 	var $now = Array();	//Array of cron-style entries for time()
 	var $lastRan; 		//Timestamp of last ran time.
 	private $lastDue;
 	var $taken;
 	var $debug;
	var $year;
	var $month;
	var $day;
	var $hour;
	var $minute;
	var $minutes_arr = array();	//minutes array based on cron string
	var $hours_arr = array();	//hours array based on cron string
	var $months_arr = array();	//months array based on cron string

	/**
	 * @return string[]
	 */
	function getLastRan()
	{
		return explode(",", eShims::strftime("%M,%H,%d,%m,%w,%Y", $this->lastRan)); //Get the values for now in a format we can use
	}

	public function getLastDue()
	{
		return $this->lastDue;
	}

	public function getNow()
	{
		return $this->now;
	}

	/**
	 * @return mixed
	 */
	function getLastRanUnix()
	{
		return $this->lastRan;
	}

	/**
	 * @return mixed
	 */
	function getDebug()
	{
 		return $this->debug;
	}

	function setDebug($bool)
	{
		$this->debug = (bool) $bool;
	}

	/**
	 * @param $str
	 * @return void
	 */
	function debug($str)
	{
		if(!$this->debug)
		{
			return;
		}

		$text = (is_array($str) ? print_r($str,true) : $str);
		error_log('e107: Cron '.$text);
		echo $text."\n";
	}

	/**
	 * Assumes that value is not *, and creates an array of valid numbers that
	 * the string represents.  Returns an array.
	 */
	function expand_ranges($str)
	{
		$ret = [];

		if (strpos($str, ",") !== false)
		{
			$arParts = explode(',', $str);
			foreach ($arParts AS $part)
			{
				if (strpos($part, '-') !== false)
				{
					$arRange = explode('-', $part);
					for ($i = $arRange[0]; $i <= $arRange[1]; $i++)
					{
						$ret[] = $i;
					}
				}
				else
				{
					$ret[] = $part;
				}
			}
		}
		elseif (strpos($str, '-') !== false)
		{
			$arRange = explode('-', $str);
			for ($i = $arRange[0]; $i <= $arRange[1]; $i++)
			{
				$ret[] = $i;
			}
		}
		else
		{
			$ret[] = $str;
		}
		$ret = array_unique($ret);
		sort($ret);
		return $ret;
	}

	/**
	 * @param $month
	 * @param $year
	 * @return string
	 */
	function daysinmonth($month, $year)
	{
		return date('t', mktime(0, 0, 0, $month, 1, $year));
	}

	/**
	 *  Calculate the last due time before this moment
	 */
	function calcLastRan($string)
	{
		$this->debug(__METHOD__.' ('.__LINE__.'): '.date_default_timezone_get());

 		$tstart = microtime(true);

		$this->lastRan = 0;
		$this->year = NULL;
		$this->month = NULL;
		$this->day = NULL;
		$this->hour = NULL;
		$this->minute = NULL;
		$this->hours_arr = array();
		$this->minutes_arr = array();
		$this->months_arr = array();

		$string = preg_replace('/\s{2,}/', ' ', $string);

		if (preg_match('/[^-,* \\d]/', $string) !== 0)
		{
			$this->debug("e107: Cron string contains invalid character: ".$string);
			return false;
		}

		$this->debug("\n----- Working on cron schedule: $string ----");
 		$this->bits = @explode(" ", $string);

		if (count($this->bits) != 5)
		{
			$this->debug("e107: Cron string is invalid. Too many or too little sections after explode.".print_r($this->bits,true));
			return false;
		}

		//put the current time into an array
		$t = eShims::strftime("%M,%H,%d,%m,%w,%Y", time());
		$this->now = explode(",", $t);

		$this->year = $this->now[5];

		$arMonths = $this->_getMonthsArray();

		do
		{
			$this->month = array_pop($arMonths);
		}
		while ($this->month > $this->now[3]);

		if ($this->month === NULL)
		{
			$this->year = $this->year - 1;
			$this->debug("Not due within this year. So checking the previous year " . $this->year);
			$arMonths = $this->_getMonthsArray();
			$this->_prevMonth($arMonths);
		}
		elseif ($this->month == $this->now[3]) //now Sep, month = array(7,9,12)
		{
			$this->debug("Cron is due this month, getting days array.");
			$arDays = $this->_getDaysArray($this->month, $this->year);

			do
			{
				$this->day = array_pop($arDays);
			}
			while ($this->day > $this->now[2]);

			if ($this->day === NULL)
			{
				$this->debug("Smallest day is even greater than today");
				$this->_prevMonth($arMonths);
			}
			elseif ($this->day == $this->now[2])
			{
				$this->debug("Due to run today");
				$arHours = $this->_getHoursArray();

				do
				{
					$this->hour = array_pop($arHours);
				}
				while ($this->hour > $this->now[1]);

				if ($this->hour === NULL) // now =2, arHours = array(3,5,7)
				{
					$this->debug("Not due this hour and some earlier hours, so go for previous day");
					$this->_prevDay($arDays, $arMonths);
				}
				elseif ($this->hour < $this->now[1]) //now =2, arHours = array(1,3,5)
				{
					$this->minute = $this->_getLastMinute();
				}
				else // now =2, arHours = array(1,2,5)
				{
					$this->debug("Due this hour");
					$arMinutes = $this->_getMinutesArray();
					do
					{
						$this->minute = array_pop($arMinutes);
					}
					while ($this->minute > $this->now[0]);

					if ($this->minute === NULL)
					{
						$this->debug("Not due this minute, so go for previous hour.");
						$this->_prevHour($arHours, $arDays, $arMonths);
					}
					else
					{
						$this->debug("Due this very minute or some earlier minutes before this moment within this hour.");
					}
				}
			}
			else
			{
				$this->debug("Cron was due on " . $this->day . " of this month");
				$this->hour = $this->_getLastHour();
				$this->minute = $this->_getLastMinute();
			}
		}
		else //now Sep, arrMonths=array(7, 10)
		{
			$this->debug("Cron was due before this month. Previous month is: " . $this->year . '-' . $this->month);
			$this->day = $this->_getLastDay($this->month, $this->year);
			if ($this->day === NULL)
			{
				//No scheduled date within this month. So we will try the previous month in the month array
				$this->_prevMonth($arMonths);
			}
			else
			{
				$this->hour = $this->_getLastHour();
				$this->minute = $this->_getLastMinute();
			}
		}

		$tend = microtime(true);
		$this->taken = $tend - $tstart;
		$this->debug("Parsing $string taken " . $this->taken . " seconds");

		//if the last due is beyond 1970
		if ($this->minute === NULL)
		{
			$this->debug("Error calculating last due time");
			return false;
		}
		else
		{
			$lastDue = $this->year.'-'.(strlen($this->month) === 1 ? '0'.$this->month : $this->month).'-'.(strlen($this->day) === 1 ? '0'.$this->day : $this->day).'T'.(strlen($this->hour) === 1 ? '0'.$this->hour : $this->hour) . ":" . (strlen($this->minute) === 1 ? '0'.$this->minute : $this->minute);
			$this->debug(__METHOD__.' ('.__LINE__.'): '.date_default_timezone_get());
			$this->debug(__METHOD__.' ('.__LINE__.'): Setting lastDue to ' . $lastDue);
			$this->lastDue = $lastDue;
			$this->lastRan = mktime($this->hour, $this->minute, 0, $this->month, $this->day, $this->year);
			$this->debug(__METHOD__.' ('.__LINE__.'): Setting lastRan to '.date('c', $this->lastRan));
			return true;
		}
	}

	//get the due time before current month

	/**
	 * @param $arMonths
	 * @return void
	 */
	function _prevMonth($arMonths)
	{
		$this->month = array_pop($arMonths);
		if ($this->month === NULL)
		{
			$this->year = $this->year -1;
			if ($this->year <= 1970)
			{
				$this->debug("Can not calculate last due time. At least not before 1970..");
			}
			else
			{
				$this->debug("Have to go for previous year " . $this->year);
				$arMonths = $this->_getMonthsArray();
				$this->_prevMonth($arMonths);
			}
		}
		else
		{
			$this->debug("Getting the last day for previous month: " . $this->year . '-' . $this->month);
			$this->day = $this->_getLastDay($this->month, $this->year);

			if ($this->day === NULL)
			{
				//no available date schedule in this month
				$this->_prevMonth($arMonths);
			}
			else
			{
				$this->hour = $this->_getLastHour();
				$this->minute = $this->_getLastMinute();
			}
		}

	}

	//get the due time before current day

	/**
	 * @param $arDays
	 * @param $arMonths
	 * @return void
	 */
	function _prevDay($arDays, $arMonths)
	{
		$this->debug("Go for the previous day");
		$this->day = array_pop($arDays);
		if ($this->day === NULL)
		{
			$this->debug("Have to go for previous month");
			$this->_prevMonth($arMonths);
		}
		else
		{
			$this->hour = $this->_getLastHour();
			$this->minute = $this->_getLastMinute();
		}
	}

	//get the due time before current hour

	/**
	 * @param $arHours
	 * @param $arDays
	 * @param $arMonths
	 * @return void
	 */
	function _prevHour($arHours, $arDays, $arMonths)
	{
		$this->debug("Going for previous hour");
		$this->hour = array_pop($arHours);
		if ($this->hour === NULL)
		{
			$this->debug("Have to go for previous day");
			$this->_prevDay($arDays, $arMonths);
		}
		else
		{
			$this->minute = $this->_getLastMinute();
		}
	}

	//not used at the moment

	/**
	 * @return mixed|null
	 */
	function _getLastMonth()
	{
		$months = $this->_getMonthsArray();

		return array_pop($months);
	}

	/**
	 * @param $month
	 * @param $year
	 * @return mixed|null
	 */
	function _getLastDay($month, $year)
	{
		//put the available days for that month into an array
		$days = $this->_getDaysArray($month, $year);

		return array_pop($days);
	}

	/**
	 * @return mixed|null
	 */
	function _getLastHour()
	{
		$hours = $this->_getHoursArray();

		return array_pop($hours);
	}

	/**
	 * @return mixed|null
	 */
	function _getLastMinute()
	{
		$minutes = $this->_getMinutesArray();

		return array_pop($minutes);
	}

	//remove the out of range array elements. $arr should be sorted already and does not contain duplicates

	/**
	 * @param $arr
	 * @param $low
	 * @param $high
	 * @return mixed
	 */
	function _sanitize ($arr, $low, $high)
	{
		$count = count($arr);
		for ($i = 0; $i <= ($count - 1); $i++)
		{
			if ($arr[$i] < $low)
			{
				$this->debug("Remove out of range element. $arr[$i] is outside $low - $high");
				unset($arr[$i]);
			}
			else
			{
				break;
			}
		}

		for ($i = ($count - 1); $i >= 0; $i--)
		{
			if ($arr[$i] > $high)
			{
				$this->debug("Remove out of range element. $arr[$i] is outside $low - $high");
				unset ($arr[$i]);
			}
			else
			{
				break;
			}
		}

		//re-assign keys
		sort($arr);
		return $arr;
	}

	//given a month/year, list all the days within that month fell into the week days list.

	/**
	 * @param $month
	 * @param $year
	 * @return array
	 */
	function _getDaysArray($month, $year = 0)
	{
		if ($year == 0)
		{
			$year = $this->year;
		}

		$days = array();

		//return everyday of the month if both bit[2] and bit[4] are '*'
		if ($this->bits[2] == '*' AND $this->bits[4] == '*')
		{
			$days = $this->getDays($month, $year);
		}
		else
		{
			//create an array for the weekdays
			if ($this->bits[4] == '*')
			{
				for ($i = 0; $i <= 6; $i++)
				{
					$arWeekdays[] = $i;
				}
			}
			else
			{
				$arWeekdays = $this->expand_ranges($this->bits[4]);
				$arWeekdays = $this->_sanitize($arWeekdays, 0, 7);

				//map 7 to 0, both represents Sunday. Array is sorted already!
				if (in_array(7, $arWeekdays))
				{
					if (in_array(0, $arWeekdays))
					{
						array_pop($arWeekdays);
					}
					else
					{
						$tmp[] = 0;
						array_pop($arWeekdays);
						$arWeekdays = array_merge($tmp, $arWeekdays);
					}
				}
			}
			$this->debug("Array for the weekdays");
			$this->debug($arWeekdays);

			if ($this->bits[2] == '*')
			{
				$daysmonth = $this->getDays($month, $year);
			}
			else
			{
				$daysmonth = $this->expand_ranges($this->bits[2]);
				// so that we do not end up with 31 of Feb
				$daysinmonth = $this->daysinmonth($month, $year);
				$daysmonth = $this->_sanitize($daysmonth, 1, $daysinmonth);
			}

			//Now match these days with weekdays
			foreach ($daysmonth AS $day)
			{
				$wkday = date('w', mktime(0, 0, 0, $month, $day, $year));
				if (in_array($wkday, $arWeekdays))
				{
					$days[] = $day;
				}
			}
		}
		$this->debug("Days array matching weekdays for $year-$month");
		//$this->debug($days);
		return $days;
	}

	//given a month/year, return an array containing all the days in that month

	/**
	 * @param $month
	 * @param $year
	 * @return array
	 */
	function getDays($month, $year)
	{
		$daysinmonth = $this->daysinmonth($month, $year);
		$this->debug("Number of days in $year-$month : $daysinmonth");
		$days = array();
		for ($i = 1; $i <= $daysinmonth; $i++)
		{
			$days[] = $i;
		}
		return $days;
	}

	/**
	 * @return array|mixed
	 */
	function _getHoursArray()
	{
		if (empty($this->hours_arr))
		{
			$hours = array();

			if ($this->bits[1] == '*')
			{
				for ($i = 0; $i <= 23; $i++)
				{
					$hours[] = $i;
				}
			}
			else
			{
				$hours = $this->expand_ranges($this->bits[1]);
				$hours = $this->_sanitize($hours, 0, 23);
			}

		//	$this->debug("Hour array");
		//	$this->debug($hours);
			$this->hours_arr = $hours;
		}
		return $this->hours_arr;
	}

	/**
	 * @return array|mixed
	 */
	function _getMinutesArray()
	{
		if (empty($this->minutes_arr))
		{
			$minutes = array();

			if ($this->bits[0] == '*')
			{
				for ($i = 0; $i <= 60; $i++)
				{
					$minutes[] = $i;
				}
			}
			else
			{
				$minutes = $this->expand_ranges($this->bits[0]);
				$minutes = $this->_sanitize($minutes, 0, 59);
			}
		//	$this->debug("Minutes array");
		//	$this->debug($minutes);
			$this->minutes_arr = $minutes;
		}
		return $this->minutes_arr;
	}

	/**
	 * @return array|mixed
	 */
	function _getMonthsArray()
	{
		if (empty($this->months_arr))
		{
			$months = array();
			if ($this->bits[3] == '*')
			{
				for ($i = 1; $i <= 12; $i++)
				{
					$months[] = $i;
				}
			}
			else
			{
				$months = $this->expand_ranges($this->bits[3]);
				$months = $this->_sanitize($months, 1, 12);
			}
		//	$this->debug("Months array");
		//	$this->debug($months);
			$this->months_arr = $months;
		}
		return $this->months_arr;
	}

}


/**
 * Class cronScheduler.
 *
 * @see cron.php
 *
 * TODO:
 * - Log error in admin log.
 * - Pref for sending email to Administrator.
 * - LANs
 */
class cronScheduler
{

	/**
	 * Cron parser class.
	 *
	 * @var \CronParser.
	 */
	private $cron;

	/**
	 * Debug mode.
	 *
	 * @var bool
	 */
	private $debug;

	/**
	 * System preferences.
	 *
	 * @var array|mixed
	 */
	private $pref;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		global $_E107;

		$this->cron = new CronParser();
		$this->debug = $_E107['debug'];
		$this->cron->setDebug($_E107['debug']);
		$this->pref = e107::getPref();
	}

	/**
	 * Runs all cron jobs.
	 *
	 * @return bool
	 */
	public function run()
	{
		$valid = $this->validateToken();

		if(!$valid)
		{
			if($this->debug)
			{
				$this->cron->debug('e107: Invalid token used for cron class');
			}
			return false;
		}

		if(!@file_put_contents(e_CACHE . 'cronLastLoad.php', time()))
		{
			$this->cron->debug('e107: Unable to write to: '.e_CACHE . 'cronLastLoad.php. Permissions issue?');
		}


		// Get active cron jobs.
		$cron_jobs = $this->getCronJobs(true);

		if($this->debug)
		{
			$this->cron->debug('e107: Cron Jobs Found: '.print_r($cron_jobs,true));
		}

		foreach($cron_jobs as $job)
		{
			$this->runJob($job);
		}

		return null;
	}

	/**
	 * Runs a cron job.
	 *
	 * @param array $job
	 *   Contains the current cron job. Each element is an array with following
	 *   properties:
	 *   - 'path'     string  '_system' or plugin name.
	 *   - 'active'   int     1 if active, 0 if inactive
	 *   - 'tab'      string  cron tab
	 *   - 'function' string  function name
	 *   - 'class'    string  class name
	 *
	 * @return bool $status
	 */
	public function runJob($job)
	{
		$status = false;

		$this->cron->debug(__METHOD__.' ('.__LINE__.'): '.date_default_timezone_get());
		$this->cron->debug(__METHOD__.' ('.__LINE__.'): Current time: '.date('c'));

		if(empty($job['active']))
		{
			if($this->debug)
			{
				error_log('e107: Cron job not active: '.print_r($job,true), E_NOTICE);
			}

			return false;
		}

		// Calculate the last due time before this moment.
		$this->cron->calcLastRan($job['tab']);
		$due = $this->cron->getLastRanUnix();

		$this->cron->debug(__METHOD__.' ('.__LINE__.'): '.date_default_timezone_get());
		$triggerTime = (time() - 45);


		$this->cron->debug(__METHOD__.' ('.__LINE__.'): Current Time (-45): '.date('c',$triggerTime));

		if($due <= $triggerTime)
		{
			if($this->debug)
			{
				$job['_lastRun'] = date('c',$due);
				$job['_triggerTime'] = date('c',$triggerTime);
				$this->cron->debug(__METHOD__.' ('.__LINE__.'): NOT running cron method because: _lastRun < _triggerTime ');
				$this->cron->debug($job);
			}
			return false;
		}

		if($job['path'] != '_system' && !is_readable(e_PLUGIN . $job['path'] . "/e_cron.php"))
		{
			$this->cron->debug('e107: Cron file not readable: '.e_PLUGIN . $job['path'] . "/e_cron.php", E_ERROR);
			return false;
		}

		if($this->debug)
		{
			$this->cron->debug('e107: Cron is running: '.print_r($job,true), E_NOTICE);
		}

		// This is correct.
		if($job['path'] != '_system')
		{
			include_once(e_PLUGIN . $job['path'] . "/e_cron.php");
		}

		$class = $job['class'] . "_cron";

		if(!class_exists($class, false))
		{
			if($this->debug)
			{
				$this->cron->debug('e107: Cron could not find class: '.$class, E_ERROR);
			}

			return $status;
		}

		$obj = new $class;

		if(!method_exists($obj, $job['function']))
		{
			if($this->debug)
			{
				$this->cron->debug('Cron could not find method: '.$job['function'], E_ERROR);
			}

			return $status;
		}


		// Exception handling.
		$method = $job['function'];

		try
		{
			$status = $obj->$method();
		}
		catch(Exception $e)
		{
			$msg = $e->getFile() . ' ' . $e->getLine();
			$msg .= "\n\n" . $e->getCode() . ' ' . $e->getMessage();
			$msg .= "\n\n" . implode("\n", $e->getTrace());

			$mail = array(
				'to_mail'   => $this->pref['siteadminemail'],
				'to_name'   => $this->pref['siteadmin'],
				'from_mail' => $this->pref['siteadminemail'],
				'from_name' => $this->pref['siteadmin'],
				'message'   => $msg,
				'subject'   => 'e107 - Cron Schedule Exception',
			);

			error_log('e107: Cron Exception occurred: '.$msg, E_ERROR);

			$this->sendMail($mail);
		}

		// If task returns value which is not boolean (BC), it will be used as a
		// message (send email, logs).
		if($status && true !== $status)
		{

			$msg = 'Method returned message: [{' . $class . '}::' . $job['function'] . '] ' . $status;

			error_log('e107: Cron Method returned message: '.$msg, E_NOTICE);

			$mail = array(
				'to_mail'   => $this->pref['siteadminemail'],
				'to_name'   => $this->pref['siteadmin'],
				'from_mail' => $this->pref['siteadminemail'],
				'from_name' => $this->pref['siteadmin'],
				'message'   => $msg,
				'subject'   => 'e107 - Cron Schedule Task Report',
			);

			$this->sendMail($mail);
		}

		$update = [
			'cron_lastrun'  => time(),
			'WHERE'         => 'cron_id = '.$job['id']
		];

		e107::getDb()->update('cron',$update);

		return $status;
	}

	/**
	 * Validate Cron Token.
	 *
	 * @return bool
	 */
	public function validateToken()
	{
		$pwd = '';

		if($this->debug && !empty($_SERVER['QUERY_STRING']))
		{
			$pwd = $_SERVER['QUERY_STRING'];
		}
		elseif(!empty($_SERVER['argv'][1]))
		{
			$pwd = trim($_SERVER['argv'][1]);
		}

		if(!empty($_GET['token']))
		{
			$pwd = e107::getParser()->filter($_GET['token']);
		}
		else
		{
			$pwd = str_replace('token=', '', $pwd);
		}

		if($this->debug)
		{
			error_log("Cron Token: ".$pwd, E_NOTICE);
		}

		if(empty($this->pref['e_cron_pwd']) || (varset($this->pref['e_cron_pwd']) != $pwd))
		{
			if(!empty($pwd))
			{
				$msg = "Your Cron Schedule is not configured correctly. Your passwords do not match.";
				$msg .= "<br /><br />";
				$msg .= "Sent from cron: " . $pwd;
				$msg .= "<br />";
				$msg .= "Stored in e107: " . varset($this->pref['e_cron_pwd'], "(none set)");
				$msg .= "<br /><br />";
				$msg .= "You should regenerate the cron command in admin and enter it again in your server configuration.";

				$msg .= "<h2>" . "Debug Info" . "</h2>";
				$msg .= "<h3>_SERVER</h3>";
				$msg .= print_a($_SERVER, true);
				$msg .= "<h3>_ENV</h3>";
				$msg .= print_a($_ENV, true);
				$msg .= "<h3>_GET</h3>";
				$msg .= print_a($_GET, true);

				$mail = array(
					'to_mail'   => $this->pref['siteadminemail'],
					'to_name'   => $this->pref['siteadmin'],
					'from_mail' => $this->pref['siteadminemail'],
					'from_name' => $this->pref['siteadmin'],
					'message'   => $msg,
					'subject'   => 'e107 - Cron Schedule Misconfigured',
				);

				$this->sendMail($mail);
			}

			return false;
		}

		return true;
	}

	/**
	 * Get available Cron jobs.
	 *
	 * @param bool $only_active
	 *   Set to TRUE for active cron jobs.
	 *
	 * @return array
	 *   Array contains cron jobs.
	 */
	public function getCronJobs($only_active = false)
	{
		$list = array();

		$sql = e107::getDb();

		$where = '1';

		if($only_active === true)
		{
			$where = 'cron_active = 1';
		}

		if($sql->select("cron", 'cron_id,cron_function,cron_tab,cron_active', $where))
		{
			while($row = $sql->fetch())
			{
				list($class, $function) = explode("::", $row['cron_function'], 2);
				$key = $class . "__" . $function;

				$list[$key] = array(
					'path'     => $class,
					'active'   => $row['cron_active'],
					'tab'      => $row['cron_tab'],
					'function' => $function,
					'class'    => $class,
					'id'       => (int) $row['cron_id']
				);
			}
		}

		return $list;
	}

	/**
	 * Helper method to send email message.
	 *
	 * @param array $mail
	 */
	public function sendMail($mail)
	{
		require_once(e_HANDLER . "mail.php");
		sendemail($mail['to_mail'], $mail['subject'], $mail['message'], $mail['to_name'], $mail['from_mail'], $mail['from_name']);
	}

}
