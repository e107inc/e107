if (ADMIN) {
	if (!function_exists('admin_status')) {
		function admin_status() {
			global $sql, $ns, $pref;
			$members = $sql -> db_Count("user");
			$unverified = $sql -> db_Count("user", "(*)", "WHERE user_ban=2");
			$banned = $sql -> db_Count("user", "(*)", "WHERE user_ban=1");
			$comments = $sql -> db_Count("comments");
			$unver = ($unverified ? " <a href='".e_ADMIN."users.php?unverified'>".ADLAN_111."</a>" : ADLAN_111);

			$text = "<div style='padding-bottom: 2px;'>".E_16_USER." ".ADLAN_110.": ".$members."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_USER." {$unver}: ".$unverified."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_BANLIST." ".ADLAN_112.": ".$banned."</div>";
			$text .= "<div style='padding-bottom: 2px;'>".E_16_COMMENT." ".ADLAN_114.": ".$comments."</div>";

			foreach($pref['e_status_list'] as $val)
			{
				if (is_readable(e_PLUGIN.$val."/e_status.php"))
				{
				   		include_once(e_PLUGIN.$val."/e_status.php");
				}
			}


			if($flo = $sql -> db_Count("generic", "(*)", "WHERE gen_type='failed_login'"))
			{
				$text .= "<img src='".e_IMAGE."admin_images/failedlogin_16.png' alt='' style='vertical-align: middle;' /> <a href='".e_ADMIN."fla.php'>".ADLAN_146.": $flo</a>";
			}


			return $ns -> tablerender(ADLAN_134, $text, '', TRUE);
		}
	}

	if ($parm == 'request') {
		if (function_exists('status_request')) {
			if (status_request()) {
				return admin_status();
			}
		}
	} else {
		return admin_status();
	}
}
