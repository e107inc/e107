<?php
/*
 * e107 website system
 *
 * Copyright (C) 2009-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


if (!defined('e107_INIT'))
{ 
	require_once("../../class2.php");
}

if (!e107::isInstalled('banner'))
{
	e107::redirect();
	exit;
}

e107::includeLan(e_PLUGIN."banner/languages/".e_LANGUAGE."_banner.php"); // TODO
e107::lan('banner');



$sql = e107::getDb();
$mes = e107::getMessage();
$frm = e107::getForm();

// When a banner is clicked 
if(e_QUERY) 
{
	$query_string = intval(e_QUERY);
	$row = $sql->retrieve("banner", "*", "banner_id = '{$query_string}'"); // select the banner
	$ip = e107::getIPHandler()->getIP(FALSE);
	$newip = (strpos($row['banner_ip'], "{$ip}^") !== FALSE) ? $row['banner_ip'] : "{$row['banner_ip']}{$ip}^"; // what does this do?
	$sql->update("banner", "banner_clicks = banner_clicks + 1, `banner_ip` = '{$newip}' WHERE `banner_id` = '{$query_string}'");
//	header("Location: {$row['banner_clickurl']}");
	e107::redirect($row['banner_clickurl']);
	exit;
}

if (empty($BANNER_LOGIN_TABLE) || empty($BANNER_TABLE) || empty($BANNER_TABLE_START) || empty($BANNER_TABLE_END))
{
	$BANNER_TABLE_START = '';
	$BANNER_TABLE_END = '';
	$BANNER_TABLE = '';
	$BANNER_LOGIN_TABLE = '';

	if(file_exists(THEME.'templates/banner/banner_template.php')) // v2.x location.
	{
		require_once (THEME.'templates/banner/banner_template.php');
	}
	elseif(file_exists(THEME."banner_template.php")) 
	{
		require_once(THEME."banner_template.php");
	} 
	else 
	{
		require_once("banner_template.php");
	}
}

	
require_once(HEADERF);
	
if (isset($_POST['clientsubmit'])) 
{
	
	$clean_login 	= $tp->toDB($_POST['clientlogin']);
	$clean_password = $tp->toDB($_POST['clientpassword']);
	
	// check login 
	// TODO: massive clean-up (integrate e107 users, proper login handling, password encryption for new and existing records)
	if (!$sql->select("banner", "*", "`banner_clientlogin` = '{$clean_login}' AND `banner_clientpassword` = '{$clean_password}'")) {
		$mes->addError(BANNERLAN_20);
		$ns->tablerender(PAGE_NAME, $mes->render());
		require_once(FOOTERF);
		exit;
	}
	 
	$row = $sql->fetch();
	$banner_total = $sql->select("banner", "*", "`banner_clientname` = '{$row['banner_clientname']}'");
	
	// check 
	if(!$banner_total) 
	{	
		$mes->addInfo(LAN_NO_RECORDS_FOUND.": ".LAN_PLUGIN_BANNER_NAME); 
		$ns->tablerender(PAGE_NAME, $mes->render());
		require_once(FOOTERF);
		exit;
	} 
	else 
	{
		while ($row = $sql->fetch()) 
		{			 
			$start_date = ($row['banner_startdate'] ? strftime("%d %B %Y", $row['banner_startdate']) : BANNERLAN_31);
			$end_date 	= ($row['banner_enddate'] ? strftime("%d %B %Y", $row['banner_enddate']) : BANNERLAN_31);

			$scArray = array();
			$scArray['BANNER_TABLE_CLICKPERCENTAGE'] 		= ($row['banner_clicks'] && $row['banner_impressions'] ? round(($row['banner_clicks'] / $row['banner_impressions']) * 100)."%" : "-");
			$scArray['BANNER_TABLE_IMPRESSIONS_LEFT']		= ($row['banner_impurchased'] ? $row['banner_impurchased'] - $row['banner_impressions'] : BANNERLAN_30);
			$scArray['BANNER_TABLE_IMPRESSIONS_PURCHASED'] = ($row['banner_impurchased'] ? $row['banner_impurchased'] : BANNERLAN_30);
			$scArray['BANNER_TABLE_CLIENTNAME'] 			= $row['banner_clientname'];
			$scArray['BANNER_TABLE_BANNER_ID']			= $row['banner_id'];
			$scArray['BANNER_TABLE_BANNER_CLICKS'] 		= $row['banner_clicks'];
			$scArray['BANNER_TABLE_BANNER_IMPRESSIONS'] 	= $row['banner_impressions'];
			$scArray['BANNER_TABLE_ACTIVE'] 				= LAN_VISIBILITY." ".($row['banner_active'] != "255" ? LAN_YES : "<b>".LAN_NO."</b>");
			$scArray['BANNER_TABLE_STARTDATE']				= LAN_START." ".$start_date;
			$scArray['BANNER_TABLE_ENDDATE']				= LAN_END." ".$end_date;
			
			if ($row['banner_ip']) 
			{
				$tmp = explode("^", $row['banner_ip']);
				$scArray['BANNER_TABLE_IP_LAN'] = (count($tmp)-1);
				
				for($a = 0; $a <= (count($tmp)-2); $a++) {
					$scArray['BANNER_TABLE_IP'] .= $tmp[$a]."<br />";
				}
			}

			$textstring .= $tp->parseTemplate($BANNER_TABLE, true, $scArray);
		}
	}
	

	$textstart = $tp->parseTemplate($BANNER_TABLE_START, true, $scArray);
	$textend = $tp->parseTemplate($BANNER_TABLE_END, true, $scArray);
	$text = $textstart.$textstring.$textend;
	 
	$ns->tablerender(PAGE_NAME, $text);
	 
	require_once(FOOTERF);
	exit;
}
	
$scArray = array();
$scArray['BANNER_LOGIN_TABLE_LOGIN'] 	= $frm->text("clientlogin", $id);
$scArray['BANNER_LOGIN_TABLE_PASSW'] 	= $frm->password("clientpassword", '');
$scArray['BANNER_LOGIN_TABLE_SUBMIT'] 	= $frm->button("clientsubmit", LAN_CONTINUE, "submit");

$text = $tp->parseTemplate($BANNER_LOGIN_TABLE, true, $scArray);

$ns->tablerender(BANNERLAN_19, $text);
	
require_once(FOOTERF);



