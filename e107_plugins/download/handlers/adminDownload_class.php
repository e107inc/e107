<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/handlers/adminDownload_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!e107::isInstalled('download')) { exit(); }

require_once(e_PLUGIN.'download/handlers/download_class.php');
require_once(e_HANDLER.'upload_handler.php');
require_once(e_HANDLER.'xml_class.php');

class adminDownload extends download
{
   var $searchField;
   var $advancedSearchFields;
   var $userclassOptions;

   function __construct()
   {
      global $pref;
      parent::__construct();
      $this->userclassOptions = 'blank,nobody,guest,public,main,admin,member,classes';

      // Save basic search string
      if (isset($_POST['download-search-text']))
      {
         $this->searchField = $_POST['download-search-text'];
      }

      // Save advanced search criteria
      if (isset($_POST['download_advanced_search_submit']))
      {
         $this->advancedSearchFields = $_POST['download_advanced_search'];
      }
   }



	function observer()
	{
		//Required on create & savepreset action triggers
//		if(isset($_POST['news_userclass']) && is_array($_POST['news_userclass']))
//		{
//			$_POST['news_class'] = implode(",", $_POST['news_userclass']);
//			unset($_POST['news_userclass']);
//		}
//
//		if(isset($_POST['delete']) && is_array($_POST['delete']))
//		{
//			$this->_observe_delete();
//		}
//		elseif(isset($_POST['submit_news']))
//		{
//			$this->_observe_submit_item($this->getSubAction(), $this->getId());
//		}
//		elseif(isset($_POST['create_category']))
//		{
//			$this->_observe_create_category();
//		}
//		elseif(isset($_POST['update_category']))
//		{
//			$this->_observe_update_category();
//		}
//		elseif(isset($_POST['save_prefs']))
//		{
//			$this->_observe_save_prefs();
//		}
//		elseif(isset($_POST['submitupload']))
//		{
//			$this->_observe_upload();
//		}
//		elseif(isset($_POST['news_comments_recalc']))
//		{
//			$this->_observe_newsCommentsRecalc();
//		}
		//if(isset($_POST['etrigger_ecolumns']))
		//{
       // 	$this->_observe_saveColumns();
	//	}
	}

 
}
