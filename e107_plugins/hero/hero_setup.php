<?php
/*
* e107 website system
*
* Copyright (C) 2008-2013 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom install/uninstall/update routines for heros plugin
**
*/

e107::lan('hero',true, true);

if(!class_exists("hero_setup"))
{
	class hero_setup
	{

	    function install_pre($var)
		{
			// print_a($var);
			// echo "custom install 'pre' function<br /><br />";
		}

		/**
		 * For inserting default database content during install after table has been created by the hero_sql.php file.
		 */
		function install_post($var)
		{
			if(!e107::getDb()->isEmpty('hero'))
			{
				return null;
			}

			$ret = e107::getXml(true)->e107Import(e_PLUGIN."hero/xml/install.xml");

			if(!empty($ret['success']))
			{
				e107::getMessage()->addSuccess(LAN_HERO_ADMIN_001);
			}

			if(!empty($ret['failed']))
			{
				e107::getMessage()->addError(LAN_HERO_ADMIN_002);
				e107::getMessage()->addDebug(print_a($ret['failed'],true));
			}

		}

		function uninstall_options()
		{

			/*$listoptions = array(0=>'option 1',1=>'option 2');

			$options = array();
			$options['mypref'] = array(
					'label'		=> 'Custom Uninstall Label',
					'preview'	=> 'Preview Area',
					'helpText'	=> 'Custom Help Text',
					'itemList'	=> $listoptions,
					'itemDefault'	=> 1
			);

			return $options;*/
		}


		function uninstall_post($var)
		{
			// print_a($var);
		}

		function upgrade_post($var)
		{
			// $sql = e107::getDb();
		}

	}

}
