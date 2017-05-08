<?php
/*
* e107 website system
*
* Copyright (C) 2008-2012 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom download install/uninstall/update routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/download/download_setup.php,v $
* $Revision$
* $Date$
* $Author$
*
*/

class download_setup
{
	
	function install_pre($var)
	{
		// print_a($var);
		$mes = eMessage::getInstance();
		// $mes->add("custom install 'pre' function.", E_MESSAGE_SUCCESS);
	}

	function install_post($var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		// $mes->add("custom install 'post' function.", E_MESSAGE_SUCCESS);
	}

	function uninstall_pre($var)
	{
		$sql = e107::getDb();
		$mes = eMessage::getInstance();
		// $mes->add("custom uninstall 'pre' function.", E_MESSAGE_SUCCESS);
	}



	function upgrade_required()
	{
			return false;
	}



	// IMPORTANT : This function below is for modifying the CONTENT of the tables only, NOT the table-structure. 
	// To Modify the table-structure, simply modify your {plugin}_sql.php file and an update will be detected automatically. 
	/*
	 * @var $needed - true when only a check for a required update is being performed.
	 * Return: Reason the upgrade is required, otherwise set it to return FALSE. 
	 */
	function upgrade_post($needed)
	{
		/*
		 * Currently Installed version (prior to upgrade): $needed->current_plug['plugin_version'];
		 * Add "IF" statements as needed, and other upgrade_x_y() methods as required. 
		 * eg.	if($needed->current_plug['plugin_version'] == '1.0')
		 * 		{
		 * 			$this->upgrade_from_1();
		 * 		}
		 */

		$config = e107::getPref('url_config');

		if(!empty($config['download']))
		{
			e107::getConfig()
			->removePref('url_config/download')
			->removePref('url_locations/download')
			->save(false,true);

			if(file_exists(e_PLUGIN."download/url/url.php"))
			{
				@unlink(e_PLUGIN."download/url/url.php");
				@unlink(e_PLUGIN."download/url/sef_url.php");
			}

			$bld = new eRouter;
			$bld->buildGlobalConfig();

		}

		return $this->upgradeFilePaths($needed);

	}


	private function upgradeFilePaths($needed)
	{



		$sql = e107::getDb();
		$mes = e107::getMessage();
		$qry = "SELECT * FROM #download WHERE download_image !='' AND SUBSTRING(download_image, 1, 3) != '{e_' ";

		if($sql->gen($qry))
		{
			if($needed == TRUE){ return "Incorrect download image paths"; } // Signal that an update is required.

			if($sql->db_Update("download","download_image = CONCAT('{e_FILE}downloadimages/',download_image) WHERE download_image !='' "))
			{
				$mes->addSuccess("Updated Download-Image paths");
			}
			else
			{
				$mes->addError("Failed to update Download-Image paths");
			}

			if($sql->db_Update("download"," download_thumb = CONCAT('{e_FILE}downloadthumbs/',download_thumb) WHERE download_thumb !='' "))
			{
				$mes->addSuccess("Updated Download-Thumbnail paths");
			}
			else
			{
				$mes->addError("Failed to update Download-Thumbnail paths");
			}
		}

		$qry = "SELECT * FROM #download_category WHERE download_category_icon !='' AND SUBSTRING(download_category_icon, 1, 3) != '{e_' ";
		if($sql->gen($qry))
		{
			// Signal that an update is required.
			if($needed == TRUE){ return "Downloads-Category icon paths need updating"; } // Must have a value if an update is needed. Text used for debug purposes.

			if($sql->db_Update("download_category","download_category_icon = CONCAT('{e_IMAGE}icons/',download_category_icon) WHERE download_category_icon !='' "))
			{
				$mes->addSuccess("Updated Download-Image paths");
			}
			else
			{
				$mes->addError("Failed to update Download-Image paths");
			}
		}

		if($needed == TRUE){ return FALSE; }



	}
}
