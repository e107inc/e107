if (ADMIN) {
	global $e_sub_cat, $e_icon_array, $PLUGINS_DIRECTORY;
	if (strstr(e_SELF, $PLUGINS_DIRECTORY)) {
		include('plugin.php');
		$e_plugin_icon = $eplug_icon ? "<img src='".e_PLUGIN.$eplug_icon."' alt='' style='border:0px; width: 32px; height: 32px' />" : E_32_CAT_PLUG;
		$icon = $e_plugin_icon;
	} else {
		$icon = $e_icon_array[$e_sub_cat];
	}
	return $icon;
}