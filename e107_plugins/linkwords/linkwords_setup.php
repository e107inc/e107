<?php
/*
* e107 website system
*
* Copyright (C) 2008-2013 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom install/uninstall/update routines for blank plugin
**
*/


if(!class_exists("linkwords_setup"))
{
	class linkwords_setup
	{
/*
	    function install_pre($var)
		{

		}


		function install_post($var)
		{

		}

		function uninstall_options()
		{

		}


		function uninstall_post($var)
		{

		}
*/

		/*
		 * Call During Upgrade Check.
		 *
		 * @return bool true = upgrade required; false = upgrade not required
		 */
		function upgrade_required()
		{

			$pref = e107::pref();

			if(isset($pref['lw_page_visibility']) || isset($pref['lw_ajax_enable']))
			{
				e107::getMessage()->addDebug("Prefs need to be migrated out of core prefs and into linkwords prefs.");
				return true;
			}

			return false;
		}


		function upgrade_post($var)
		{

				$plugPrefs = array(
					'lw_context_visibility'	=> 'lw_context_visibility',
					'lw_ajax_enable'		=> 'lw_ajax_enable',
					'lw_notsamepage'		=> 'lw_notsamepage',
					'linkword_omit_pages'	=> 'linkword_omit_pages',
					'lw_custom_class'       => 'lw_custom_class',
					'lw_max_per_word'       => 'lw_max_per_word',
					'lw_page_visibility'    => 'lw_page_visibility',
				);

				if($saveData = e107::getConfig()->migrateData($plugPrefs, true))
				{
					e107::getPlugConfig('linkwords')->setPref($saveData)->save(true,true,true);
				}

		}

	}

}