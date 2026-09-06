<?php
/*
* e107 website system
*
* Copyright (C) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*	PM plugin - install/uninstall routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/pm/pm_setup.php,v $
* $Revision$
* $Date$
* $Author$
*
*/

/**
 *	e107 Private messenger plugin
 *
 *	install/uninstall routines
 *
 *	@package	e107_plugins
 *	@subpackage	pm
 *	@version 	$Id$;
 */

class pm_setup
{

	/**
	 *	Cover the attachment directories this site already holds.
	 *
	 *	A site whose attachments were stored before the deny rules existed has
	 *	this and nothing else: a send covers the sender's own directory and the
	 *	root above it and reads no other, and the members whose files are sitting
	 *	there exposed are the ones who are not sending any. The plugin's version is
	 *	raised in plugin.xml
	 *	so that this runs on every existing site rather than only on a fresh
	 *	install.
	 *
	 *	@return	void
	 */
	function install_post()
	{
		require_once(e_PLUGIN . 'pm/pm_class.php');

		$pm = new private_message();

		if(!$pm->protectStoredAttachments())
		{
			e107::lan('pm', null);
			e107::getMessage()->addWarning(defset('LAN_PM_116'));
		}
	}


	function upgrade_post()
	{
		$this->install_post();
	}


	function uninstall_post()
	{
		$sql = e107::getDb();
		$sql->createQueryBuilder()->delete('core')->where('e107_name', 'pm_prefs')->execute();
		$sql->createQueryBuilder()->delete('menus')->where('menu_name', 'private_msg_menu')->execute();
	}
	
}
