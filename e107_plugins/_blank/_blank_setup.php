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


if(!class_exists("_blank_setup"))
{
	class _blank_setup
	{

	    function install_pre($var)
		{
			// print_a($var);
			// echo "custom install 'pre' function<br /><br />";
		}

		/**
		 * For inserting default database content during install after table has been created by the blank_sql.php file.
		 */
		function install_post($var)
		{
			$sql = e107::getDb();
			$mes = e107::getMessage();

			$e107_blank = array(
				'blank_id'				=>'1',
				'blank_icon'			=>'{e_PLUGIN}_blank/images/blank_32.png',
				'blank_type'			=>'type_1',
				'blank_name'			=>'My Name',
				'blank_folder'			=>'Folder Value',
				'blank_version'			=>'1',
				'blank_author'			=>'bill',
				'blank_authorURL'		=>'http://e107.org',
				'blank_date'			=>'1352871240',
				'blank_compatibility'	=>'2',
				'blank_url'				=>'http://e107.org'
			);

			if($sql->insert('blank',$e107_blank))
			{
				$mes->add("Custom - Install Message.", E_MESSAGE_SUCCESS);
			}
			else
			{
				$mes->add("Custom - Failed to add default table data.", E_MESSAGE_ERROR);
			}

		}

		function uninstall_options()
		{

			$listoptions = array(0=>'option 1',1=>'option 2');

			$options = array();
			$options['mypref'] = array(
					'label'		=> 'Custom Uninstall Label',
					'preview'	=> 'Preview Area',
					'helpText'	=> 'Custom Help Text',
					'itemList'	=> $listoptions,
					'itemDefault'	=> 1
			);

			return $options;
		}


		function uninstall_post($var)
		{
			// print_a($var);
		}


		/*
		 * Call During Upgrade Check. May be used to check for existance of tables etc and if not found return TRUE to call for an upgrade.
		 *
		 * @return bool true = upgrade required; false = upgrade not required
		 */
		function upgrade_required()
		{
			// Check if a specific table exists and if not, return true to force a db update
			// In this example, it checks if the table "blank_table" exists
//			if(!e107::getDb()->isTable('blank_table'))
//			{
//				return true;	 // true to trigger an upgrade alert, and false to not.
//			}


			// Check if a specific field exists in the specified table
			// and if not return false to force a db update to add this field
			// from the "_blank_sql.php" file
			// In this case: Exists field "blank_id" in table "blank_table"
//			if(!e107::getDb()->field('blank_table','blank_id'))
//			{
//				return true;	 // true to trigger an upgrade alert, and false to not.
//			}


			// In case you need to delete a field that is not used anymore,
			// first check if the field exists, than run a sql command to drop (delete) the field
			// !!! ATTENTION !!!
			// !!! Deleting a field, deletes also the data stored in that field !!!
			// !!! Make sure you know what you are doing !!!
			//
			// In this example, the field "blank_unused_field" from table "blank_table"
			// isn't used anymore and will be deleted (dropped) if it still exists
//			if(e107::getDb()->field('blank_table', 'blank_unused_field'))
//			{
				// this statement directly deletes the field, an additional
				// db update isn't needed anymore, if this is the only change on the db/table.
//				e107::getDb()->gen("ALTER TABLE `#blank_table` DROP `blank_unused_field` ");
//			}


			// In case you need to delete a index that is not used anymore,
			// first check if the index exists, than run a sql command to drop (delete) the field
			// Be aware, that deleting an index is not very harmfull, as the data of the
			// index will be recreated when the index is added again.
//			if(e107::getDb()->index('blank_table','blank_unused_index'))
//			{
				// this statement directly deletes the index, an additional
				// db update isn't needed anymore, if this is the only change on the db/table.
//				e107::getDb()->gen("ALTER TABLE `#blank_table` DROP INDEX `blank_unused_index` ");
//			}

			// In case you need to check an index and which fields it is build of,
			// use the fourth parameter to return the index definition.
			// In this case, the index should be deleted if consists only of 1 field ("blank_fieldname"),
//			if(e107::getDb()->index('blank_table','blank_unused_index', array('blank_fieldname')))
//			{
				// this statement directly deletes the index, an additional
				// db update isn't needed anymore, if this is the only change on the db/table.
//				e107::getDb()->gen("ALTER TABLE `#blank_table` DROP INDEX `blank_unused_index` ");
//			}


			// In case you need to check an index and which fields it is build of,
			// use the third parameter to return the index definition.
			// In this case, the index should be deleted if consists only of 1 field ("blank_fieldname"),
//			if ($index_def = e107::getDb()->index('blank_table','blank_unused_index', array('blank_fieldname')))
//			{
				// Check if the key should be UNIQUE
//				$unique = array_count_values(array_column($index_def, 'Non_unique'));
//				if($unique[1] > 0) // Keys are not unique
//				{
					// this statement directly deletes the index, an additional
					// db update isn't needed anymore, if this is the only change on the db/table.
//					e107::getDb()->gen("ALTER TABLE `#blank_table` DROP INDEX `blank_unused_index` ");
//				}
//			}


			$legacyMenuPref = e107::getConfig('menu')->getPref();
			if(isset($legacyMenuPref['newforumposts_caption']))
			{

			}

			return false;
		}


		function upgrade_post($var)
		{
			// $sql = e107::getDb();
		}

	}

}