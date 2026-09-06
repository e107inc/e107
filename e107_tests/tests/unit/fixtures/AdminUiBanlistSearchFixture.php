<?php

/**
 * {@see e_admin_ui} carrying the custom search handler that
 * e107_admin/banlist.php declares for its banlist_ip field.
 *
 * e_HANDLER.'admin_ui.php' must already be loaded when this file is included.
 */
class AdminUiBanlistSearchFixture extends e_admin_ui
{
	public function handleListBanlistIpSearch($srch)
	{
		$ret = array(
			"banlist_ip = '".$srch."'"
		);

		if($ip6 = e107::getIPHandler()->ipEncode($srch,true))
		{
			$ip = str_replace('x', '', $ip6);
			$ret[] = "banlist_ip LIKE '%".$ip."%'";
		}

		return implode(" OR ",$ret);
	}
}
