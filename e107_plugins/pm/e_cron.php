<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin configuration module - gsitemap
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/pm/e_cron.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

/**
 *	e107 Private Messenger plugin
 *
 *	@package	e107_plugins
 *	@subpackage	pm
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }


e107::includeLan(e_PLUGIN.'/pm/languages/English_mailer.php');

class pm_cron // include plugin-folder in the name.
{
	private $logRequirement = 0;			// Flag to determine logging level
	private $debugLevel = 0;				// Used for internal debugging
	private $logHandle = NULL;
	private	$pmClass;						// Calendar library routines
	private $e107;
	private	$mailManager;
	private	$ourDB;							// Used for some things


	public function __construct()
	{
		$this->e107 = e107::getInstance();
		$this->ourDB = new db;
		//$this->debugLevel = 2;
	}



	/**
	 * Cron configuration
	 *
	 * Defines one or more cron tasks to be performed
	 *
	 * @return array of task arrays
	 */
	public function config()
	{
		$cron = array();
		$cron[] = array(
			'name' 			=> LAN_EC_PM_04,
			'category'		=> 'plugin',
			'function' 		=> 'processPM',
			'description' 	=> LAN_EC_PM_05
			);
		return $cron;
	}
	
	
	
	/**
	 * Logging routine - writes lines to a text file
	 *
	 * Auto-opens log file (if necessary) on first call
	 * 
	 * @param string $logText - body of text to write
	 * @param boolean $closeAfter - if TRUE, log file closed before exit; otherwise left open
	 *
	 * @return none
	 */
	function logLine($logText, $closeAfter = FALSE, $addTimeDate = FALSE)
	{
		if ($this->logRequirement == 0) return;

		$logFilename = e_LOG.'pm_bulk.txt';
		if ($this->logHandle == NULL)
		{
			if (!($this->logHandle = fopen($logFilename, "a"))) 
			{ // Problem creating file?
				echo "File open failed!<br />";
				$this->logRequirement = 0; 
				return; 
			}
		}
	  
		if (fwrite($this->logHandle,($addTimeDate ? date('D j M Y G:i:s').': ' : '').$logText."\r\n") == FALSE) 
		{
			$this->logRequirement = 0; 
			echo 'File write failed!<br />';
		}
	  
		if ($closeAfter)
		{
			fclose($this->logHandle);
			$this->logHandle = NULL;
		}
	}

	
	
	/**
	 * Called to process outstanding PMs (which are always bulk lists)
	 * 
	 * Emails are added to the queue.
	 * Various events are logged in a text file
	 *
	 * @return none
	 */
	public function processPM()
	{
		global $pref;

		require_once(e_PLUGIN.'pm/pm_class.php');

		$this->startTime = mktime(0, 0, 0, date('n'), date('d'), date('Y'));	// Date for start processing

		$this->logRequirement = varset($pref['eventpost_emaillog'], 0);
		if ($this->debugLevel >= 2) $this->logRequirement = 2;		// Force full logging if debug


		// Start of the 'real' code

		if ($this->ourDB->select('generic', '*', "`gen_type` = 'pm_bulk' LIMIT 1"))
		{
			$pmRow = $this->ourDB->fetch();
			$this->logLine("\r\n\r\n".str_replace('[y]',$pmRow['gen_intdata'],LAN_EC_PM_06).date('D j M Y G:i:s'));

			$this->ourDB->delete('generic', "`gen_type` = 'pm_bulk' AND `gen_id` = ".$pmRow['gen_id']);

			$pmData = e107::unserialize($pmRow['gen_chardata']);
			unset($pmRow);
			$this->pmClass = new private_message;
			$this->pmClass->add($pmData);
			$this->logLine(' .. Run completed',TRUE, TRUE);
		}
		return TRUE;
	}




	private function checkMailManager()
	{
		if ($this->mailManager == NULL)
		{
			require_once(e_HANDLER .'mail_manager_class.php');
			$this->mailManager = new e107MailManager();
		}
	}

}



?>