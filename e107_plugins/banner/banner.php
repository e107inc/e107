<?php
/*
 * e107 website system
 *
 * Copyright (C) 2009-2014 e107 Inc (e107.org)
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

if (!$BANNER_LOGIN_TABLE) 
{
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
		$mes->addInfo(BANNERLAN_29); 
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
			 
			$BANNER_TABLE_CLICKPERCENTAGE 		= ($row['banner_clicks'] && $row['banner_impressions'] ? round(($row['banner_clicks'] / $row['banner_impressions']) * 100)."%" : "-");
			$BANNER_TABLE_IMPRESSIONS_LEFT 		= ($row['banner_impurchased'] ? $row['banner_impurchased'] - $row['banner_impressions'] : BANNERLAN_30);
			$BANNER_TABLE_IMPRESSIONS_PURCHASED = ($row['banner_impurchased'] ? $row['banner_impurchased'] : BANNERLAN_30);
			$BANNER_TABLE_CLIENTNAME 			= $row['banner_clientname'];
			$BANNER_TABLE_BANNER_ID 			= $row['banner_id'];
			$BANNER_TABLE_BANNER_CLICKS 		= $row['banner_clicks'];
			$BANNER_TABLE_BANNER_IMPRESSIONS 	= $row['banner_impressions'];
			$BANNER_TABLE_ACTIVE 				= BANNERLAN_36.($row['banner_active'] != "255" ? LAN_YES : "<b>".LAN_NO."</b>");
			$BANNER_TABLE_STARTDATE 			= BANNERLAN_37." ".$start_date;
			$BANNER_TABLE_ENDDATE 				= BANNERLAN_34." ".$end_date;
			
			if ($row['banner_ip']) 
			{
				$tmp = explode("^", $row['banner_ip']);
				$BANNER_TABLE_IP_LAN = (count($tmp)-1);
				
				for($a = 0; $a <= (count($tmp)-2); $a++) {
					$BANNER_TABLE_IP .= $tmp[$a]."<br />";
				}
			}

			$textstring .= preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_TABLE);
		}
	}
	

	$textstart = preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_TABLE_START);
	$textend = preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_TABLE_END);
	$text = $textstart.$textstring.$textend;
	 
	$ns->tablerender(PAGE_NAME, $text);
	 
	require_once(FOOTERF);
	exit;
}
	

$BANNER_LOGIN_TABLE_LOGIN 	= $frm->text("clientlogin", $id);
$BANNER_LOGIN_TABLE_PASSW 	= $frm->password("clientpassword", $pw);
$BANNER_LOGIN_TABLE_SUBMIT 	= $frm->button("clientsubmit", LAN_CONTINUE, "submit");
	


$text = preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_LOGIN_TABLE);
$ns->tablerender(BANNERLAN_19, $text);
	
require_once(FOOTERF);
?>