<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/banner.php,v $
|     $Revision: 1.5 $
|     $Date: 2005-01-27 19:51:37 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
	
if (e_QUERY) {
	$sql->db_Select("banner", "*", "banner_id='".e_QUERY."' ");
	$row = $sql->db_Fetch();
	 extract($row);
	$ip = getip();
	$newip = (preg_match("/".$ip."\^/", $banner_ip) ? $banner_ip : $banner_ip.$ip."^");
	$sql->db_Update("banner", "banner_clicks=banner_clicks+1, banner_ip='$newip' WHERE banner_id='".e_QUERY."' ");
	header("location: ".$banner_clickurl);
	exit;
}
	
require_once(HEADERF);
	
if (isset($_POST['clientsubmit'])) {
	 
	if (!$sql->db_Select("banner", "*", "banner_clientlogin='".$_POST['clientlogin']."' AND banner_clientpassword='".$_POST['clientpassword']."' ")) {
		$ns->tablerender(LAN_38, "<br /><div style='text-align:center'>".LAN_20."</div><br />");
		require_once(FOOTERF);
		exit;
	}
	 
	$row = $sql->db_Fetch();
	 extract($row);
	 
	$banner_total = $sql->db_Select("banner", "*", "banner_clientname='$banner_clientname' ");
	 
	if (!$banner_total) {
		$ns->tablerender(LAN_38, "<br /><div style='text-align:center'>".LAN_29."</div><br />");
		require_once(FOOTERF);
		exit;
	} else {
		while ($row = $sql->db_Fetch()) {
			extract($row);
			 
			$start_date = ($banner_startdate ? strftime("%d %B %Y", $banner_startdate) : LAN_31);
			$end_date = ($banner_enddate ? strftime("%d %B %Y", $banner_enddate) : LAN_31);
			 
			$BANNER_TABLE_CLICKPERCENTAGE = ($banner_clicks && $banner_impressions ? round(($banner_clicks / $banner_impressions) * 100)."%" : "-");
			$BANNER_TABLE_IMPRESSIONS_LEFT = ($banner_impurchased ? $banner_impurchased - $banner_impressions : LAN_30);
			$BANNER_TABLE_IMPRESSIONS_PURCHASED = ($banner_impurchased ? $banner_impurchased : LAN_30);
			$BANNER_TABLE_CLIENTNAME = $banner_clientname;
			$BANNER_TABLE_BANNER_ID = $banner_id;
			$BANNER_TABLE_BANNER_CLICKS = $banner_clicks;
			$BANNER_TABLE_BANNER_IMPRESSIONS = $banner_impressions;
			$BANNER_TABLE_ACTIVE = LAN_36.($banner_active != "255" ? LAN_32 : "<b>".LAN_33."</b>");
			$BANNER_TABLE_STARTDATE = LAN_37." ".$start_date;
			$BANNER_TABLE_ENDDATE = LAN_34." ".$end_date;
			 
			if ($banner_ip) {
				$tmp = explode("^", $banner_ip);
				$BANNER_TABLE_IP_LAN = LAN_35.": ".(count($tmp)-1);
				for($a = 0; $a <= (count($tmp)-2); $a++) {
					$BANNER_TABLE_IP .= $tmp[$a]."<br />";
				}
			}
			 
			if (!$BANNER_TABLE) {
				if (file_exists(THEME."banner_template.php")) {
					require_once(THEME."banner_template.php");
				} else {
					require_once(e_BASE.$THEMES_DIRECTORY."templates/banner_template.php");
				}
			}
			$textstring .= preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_TABLE);
		}
	}
	 
	if (!$BANNER_TABLE) {
		if (file_exists(THEME."banner_template.php")) {
			require_once(THEME."banner_template.php");
		} else {
			require_once(e_BASE.$THEMES_DIRECTORY."templates/banner_template.php");
		}
	}
	$textstart = preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_TABLE_START);
	$textend = preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_TABLE_END);
	$text = $textstart.$textstring.$textend;
	 
	echo $text;
	 
	require_once(FOOTERF);
	exit;
}
	
	
$BANNER_LOGIN_TABLE_LOGIN = $rs->form_text("clientlogin", 30, $id, 20, "tbox");
$BANNER_LOGIN_TABLE_PASSW = $rs->form_password("clientpassword", 30, "", 20, "tbox");
$BANNER_LOGIN_TABLE_SUBMIT = $rs->form_button("submit", "clientsubmit", LAN_18);
	
if (!$BANNER_LOGIN_TABLE) {
	if (file_exists(THEME."banner_template.php")) {
		require_once(THEME."banner_template.php");
	} else {
		require_once(e_BASE.$THEMES_DIRECTORY."templates/banner_template.php");
	}
}
$text = preg_replace("/\{(.*?)\}/e", '$\1', $BANNER_LOGIN_TABLE);
$ns->tablerender(LAN_19, $text);
	
	
require_once(FOOTERF);
	
?>