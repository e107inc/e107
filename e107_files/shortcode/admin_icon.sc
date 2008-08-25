/*
* e107 website system (c) 2001-2008 Steve Dunstan (e107.org)
* $Id: admin_icon.sc,v 1.2 2008-08-25 13:34:45 e107steved Exp $
*/
if (ADMIN) 
{
	global $e_sub_cat, $e_icon_array, $PLUGINS_DIRECTORY;
	if (strstr(e_SELF, $PLUGINS_DIRECTORY)) 
	{
		if (is_readable('plugin.xml'))
		{
			require_once(e_HANDLER.'xml_class.php');
			$xml = new xmlClass;	
			$xml->filter = array('folder' => FALSE, 'administration' => FALSE);		// Just need one variable
			$readFile = $xml->loadXMLfile('plugin.xml', true, true);
			$eplug_icon = $readFile['folder'].'/'.$readFile['administration']['icon'];
			$eplug_folder = $readFile['folder'];
		}
		elseif (is_readable('plugin.php'))
		{
			include('plugin.php');
		}
		else
		{
			$icon = E_32_CAT_PLUG;
			return $icon;
		}
		$icon = ($eplug_icon && file_exists(e_PLUGIN.$eplug_icon)) ? "<img src='".e_PLUGIN.$eplug_icon."' alt='' style='border:0px; width: 32px; height: 32px' />" : E_32_CAT_PLUG;
	} 
	else 
	{
		$icon = $e_icon_array[$e_sub_cat];
	}
	return $icon;
} 
else 
{
	return E_32_LOGOUT;
}