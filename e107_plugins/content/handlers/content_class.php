<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/review.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/handlers/content_class.php,v $
|		$Revision: 1.2 $
|		$Date: 2005-02-04 15:31:53 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

if (!defined('CONTENT_ICON_EDIT')) { define("CONTENT_ICON_EDIT", "<img src='".e_PLUGIN."content/images/maintain_16.png' alt='edit' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_DELETE')) { define("CONTENT_ICON_DELETE", "<img src='".e_PLUGIN."content/images/banlist_16.png' alt='delete' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_OPTIONS')) { define("CONTENT_ICON_OPTIONS", "<img src='".e_PLUGIN."content/images/cat_settings_16.png' alt='options' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_USER')) { define("CONTENT_ICON_USER", "<img src='".e_PLUGIN."content/images/users_16.png' alt='user details' style='border:0; cursor:pointer;' />"); }
//if (!defined('CONTENT_ICON_BREADCRUMP')) { define("CONTENT_ICON_BREADCRUMP", "<img src='".e_PLUGIN."content/images/arrow_16.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_FILE')) { define("CONTENT_ICON_FILE", "<img src='".e_PLUGIN."content/images/file_16.png' alt='' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_PERSONALMANAGER')) { define("CONTENT_ICON_PERSONALMANAGER", "<img src='".e_PLUGIN."content/images/userclass_16.png' alt='personal admin' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_NEW')) { define("CONTENT_ICON_NEW", "<img src='".e_PLUGIN."content/images/articles_16.png' alt='new' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_SUBMIT')) { define("CONTENT_ICON_SUBMIT", "<img src='".e_PLUGIN."content/images/redo.png' alt='submit content' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_CONTENTMANAGER')) { define("CONTENT_ICON_CONTENTMANAGER", "<img src='".e_PLUGIN."content/images/kgpg_identity.png' alt='personal contentmanager' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_AUTHORLIST')) { define("CONTENT_ICON_AUTHORLIST", "<img src='".e_PLUGIN."content/images/personal.png' alt='author list' style='border:0; cursor:pointer;' />"); }

$plugintable = "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
$datequery = " AND (content_datestamp=0 || content_datestamp < ".time().") AND (content_enddate=0 || content_enddate>".time().") ";

class content{

		function ContentDefaultPrefs($id){

				if(!$id){ $id="0"; }
				//default values
				$content_pref["content_admin_icon_{$id}"] = "1";						//should icon be available to add when creating an item
				$content_pref["content_admin_attach_{$id}"] = "1";						//should file be available to add when creating an item
				$content_pref["content_admin_images_{$id}"] = "1";						//should image be available to add when creating an item
				$content_pref["content_admin_comment_{$id}"] = "1";						//should comment be available to add when creating an item
				$content_pref["content_admin_rating_{$id}"] = "1";						//should rating be available to add when creating an item
				$content_pref["content_admin_score_{$id}"] = "1";						//should score be available to add when creating an item
				$content_pref["content_admin_pe_{$id}"] = "1";							//should printemailicons be available to add when creating an item
				$content_pref["content_admin_visibility_{$id}"] = "1";					//should visibility be available to add when creating an item
				$content_pref["content_admin_meta_{$id}"] = "1";						//should metatags be available to add when creating an item
				$content_pref["content_admin_custom_number_{$id}"] = "5";				//how many customtags should be available to add when creating an item
				$content_pref["content_admin_images_number_{$id}"] = "10";				//how many images should be available to add when creating an item
				$content_pref["content_admin_files_number_{$id}"] = "3";				//how many files should be available to add when creating an item

				$content_pref["content_submit_{$id}"] = "0";							//should users be able to submit an item
				$content_pref["content_submit_class_{$id}"] = "0";						//define which userclass is able to submit an item
				$content_pref["content_submit_directpost_{$id}"] ="0";					//should submission be direclty posted as an item, or have them validated by admins
				$content_pref["content_submit_icon_{$id}"] = "0";						//should icon be available to add when submitting an item
				$content_pref["content_submit_attach_{$id}"] = "0";						//should file be available to add when submitting an item
				$content_pref["content_submit_images_{$id}"] = "0";						//should image be available to add when submitting an item
				$content_pref["content_submit_comment_{$id}"] = "0";					//should comment be available to add when submitting an item
				$content_pref["content_submit_rating_{$id}"] = "0";						//should rating be available to add when submitting an item
				$content_pref["content_submit_score_{$id}"] = "0";						//should score be available to add when submitting an item
				$content_pref["content_submit_pe_{$id}"] = "0";							//should printemailicons be available to add when submitting an item
				$content_pref["content_submit_visibility_{$id}"] = "0";					//should visibility be available to add when submitting an item
				$content_pref["content_submit_meta_{$id}"] = "1";						//should metatags be available to add when submitting an item
				$content_pref["content_submit_custom_number_{$id}"] = "0";				//how many customtags should be available to add when submitting an item
				$content_pref["content_submit_images_number_{$id}"] = "0";				//how many images should be available to add when submitting an item
				$content_pref["content_submit_files_number_{$id}"] = "0";				//how many files should be available to add when submitting an item

				$content_pref["content_cat_icon_path_large_{$id}"] = "{e_PLUGIN}content/images/cat/48/";	//default path to large categry icons
				$content_pref["content_cat_icon_path_small_{$id}"] = "{e_PLUGIN}content/images/cat/16/";	//default path to small category icons
				$content_pref["content_icon_path_{$id}"] = "{e_PLUGIN}content/images/icon/";				//default path to item icons
				$content_pref["content_image_path_{$id}"] = "{e_PLUGIN}content/images/image/";				//default path to item images
				$content_pref["content_file_path_{$id}"] = "{e_PLUGIN}content/images/file/";				//default path to item file attachments
				$content_pref["content_theme_{$id}"] = "default";											//choose layout theme for main parent

				$content_pref["content_log_{$id}"] = "1";								//activate log
				$content_pref["content_blank_icon_{$id}"] = "1";						//use blank icon if no icon present
				$content_pref["content_blank_caticon_{$id}"] = "1";						//use blank caticon if no caticon present
				$content_pref["content_breadcrumb_{$id}"] = "1";						//show breadcrumb
				$content_pref["content_breadcrumb_rendertype_{$id}"] = "2";				//how to render the breadcrumb
				$content_pref["content_searchmenu_{$id}"] = "1";						//show searchmenu

				$content_pref["content_list_subheading_{$id}"] = "1";					//show subheading in listpages
				$content_pref["content_list_summary_{$id}"] = "1";						//show summary in listpages
				$content_pref["content_list_date_{$id}"] = "1";							//show date in listpages
				$content_pref["content_list_authorname_{$id}"] = "1";					//show authorname in listpages
				$content_pref["content_list_authoremail_{$id}"] = "0";					//show authoremail in listpages
				$content_pref["content_list_rating_{$id}"] = "1";						//show rating system in listpages
				$content_pref["content_list_peicon_{$id}"] = "1";						//show printemailicons in listpages
				$content_pref["content_list_parent_{$id}"] = "1";						//show parent cat in listpages
				$content_pref["content_list_refer_{$id}"] = "1";						//show refer count in listpages
				$content_pref["content_list_subheading_char_{$id}"] = "100";			//how many subheading characters
				$content_pref["content_list_subheading_post_{$id}"] = "[...]";			//use a postfix for too long subheadings
				$content_pref["content_list_summary_char_{$id}"] = "100";				//how many summary characters
				$content_pref["content_list_summary_post_{$id}"] = "[...]";				//use a postfix for too long summary
				$content_pref["content_list_authoremail_nonmember_{$id}"] = "0";		//show email non member author
				$content_pref["content_nextprev_{$id}"] = "1";							//use nextprev buttons
				$content_pref["content_nextprev_number_{$id}"] = "10";					//how many items on a page
				$content_pref["content_list_peicon_all_{$id}"] = "0";					//override printemail icons
				$content_pref["content_list_rating_all_{$id}"] = "0";					//override rating system
				$content_pref["content_defaultorder_{$id}"] = "orderddate";				//default sort and order method

				$content_pref["content_cat_showparent_{$id}"] = "1";					//show parent item in category page
				$content_pref["content_cat_showparentsub_{$id}"] = "1";					//show subcategories in category page
				$content_pref["content_cat_listtype_{$id}"] = "0";						//also show items from subategories
				$content_pref["content_cat_menuorder_{$id}"] = "1";						//order of parent and child items
				$content_pref["content_cat_rendertype_{$id}"] = "1";					//render method of the menus

				$content_pref["content_content_subheading_{$id}"] = "1";				//show subheading in content page
				$content_pref["content_content_summary_{$id}"] = "1";					//show summary in content page
				$content_pref["content_content_date_{$id}"] = "1";						//show date in content page
				$content_pref["content_content_authorname_{$id}"] = "1";				//show authorname in content page
				$content_pref["content_content_authoremail_{$id}"] = "0";				//show suthoremail in content page
				$content_pref["content_content_rating_{$id}"] = "1";					//show rating system in content page
				$content_pref["content_content_peicon_{$id}"] = "1";					//show printemailicons in content page
				$content_pref["content_content_refer_{$id}"] = "1";						//show refer count in content page
				$content_pref["content_content_authoremail_nonmember_{$id}"] = "0";		//show email non member in content page
				$content_pref["content_content_peicon_all_{$id}"] = "0";				//override printemail icons
				$content_pref["content_content_rating_all_{$id}"] = "0";				//override rating system

				$content_pref["content_menu_viewallcat_{$id}"] = "1";					//menu: view link to all categories
				$content_pref["content_menu_viewallauthor_{$id}"] = "1";				//menu: view link to all authors
				$content_pref["content_menu_viewtoprated_{$id}"] = "1";					//menu: view link to top rated items
				$content_pref["content_menu_viewrecent_{$id}"] = "1";					//menu: view link to recent items
				$content_pref["content_menu_cat_{$id}"] = "1";							//view categories
				$content_pref["content_menu_recent_{$id}"] = "1";						//view recent list
				$content_pref["content_menu_cat_number_{$id}"] = "1";					//show number of items in category
				$content_pref["content_menu_recent_caption_{$id}"] = "recent items";	//caption of recent list
				$content_pref["content_menu_caption_{$id}"] = "content menu";			//caption of menu
				$content_pref["content_menu_search_{$id}"] = "1";						//show search keyword
				$content_pref["content_menu_sort_{$id}"] = "1";							//show sorting methods

				return $content_pref;
		}

		function getContentPref($id="") {
				global $plugintable, $sql;
				if(!is_object($sql)){ $sql = new db; }
				
				if($id){	//if $id; use prefs from content table
							$num_rows = $sql -> db_Select($plugintable, "content_pref", "content_id='$id' ");
							$row = $sql -> db_Fetch(); extract($row);
							if (empty($content_pref)) {
								$content_pref = $this -> ContentDefaultPrefs($id);
								$tmp = addslashes(serialize($content_pref));
								$sql -> db_Update($plugintable, "content_pref='$tmp' WHERE content_id='$id' ");
								$sql -> db_Select($plugintable, "content_pref", "content_id='$id' ");
							}
							$content_pref = unserialize(stripslashes($row['content_pref']));
							if(!is_array($content_pref)){ $content_pref = unserialize($row['content_pref']); }

				
				}else{		//if not $id; use prefs from default core table
							$num_rows = $sql -> db_Select("core", "*", "e107_name='$plugintable' ");
							if ($num_rows == 0) {
								$content_pref = $this -> ContentDefaultPrefs("0");
								$tmp = serialize($content_pref);
								$sql -> db_Insert("core", "'$plugintable', '$tmp' ");
								$sql -> db_Select("core", "*", "e107_name='$plugintable' ");
							}
							$row = $sql -> db_Fetch();
							$content_pref = unserialize(stripslashes($row['e107_value']));
							if(!is_array($content_pref)){ $content_pref = unserialize($row['e107_value']); }
				}

				return $content_pref;
		}

		function UpdateContentPref($_POST, $id){
				global $plugintable, $sql;

				if(!is_object($sql)){ $sql = new db; }

				foreach($_POST as $k => $v){
					if(preg_match("#^content_#",$k)){
						$content_pref[$k] = $v;
					}
				}
				$content_pref["content_admin_icon_{$id}"] = $_POST["content_admin_icon_{$id}"];
				$content_pref["content_admin_attach_{$id}"] = $_POST["content_admin_attach_{$id}"];
				$content_pref["content_admin_images_{$id}"] = $_POST["content_admin_images_{$id}"];
				$content_pref["content_admin_comment_{$id}"] = $_POST["content_admin_comment_{$id}"];
				$content_pref["content_admin_rating_{$id}"] = $_POST["content_admin_rating_{$id}"];
				$content_pref["content_admin_score_{$id}"] = $_POST["content_admin_score_{$id}"];
				$content_pref["content_admin_pe_{$id}"] = $_POST["content_admin_pe_{$id}"];
				$content_pref["content_admin_visibility_{$id}"] = $_POST["content_admin_visibility_{$id}"];
				$content_pref["content_admin_meta_{$id}"] = $_POST["content_admin_meta_{$id}"];
				$content_pref["content_admin_custom_number_{$id}"] = $_POST["content_admin_custom_number_{$id}"];
				$content_pref["content_admin_images_number_{$id}"] = $_POST["content_admin_images_number_{$id}"];
				$content_pref["content_admin_files_number_{$id}"] = $_POST["content_admin_files_number_{$id}"];

				$content_pref["content_submit_{$id}"] = ($_POST["content_submit_{$id}"] ? $_POST["content_submit_{$id}"] : "0");
				$content_pref["content_submit_class_{$id}"] = $_POST["content_submit_class_{$id}"];
				$content_pref["content_submit_directpost_{$id}"] = $_POST["content_submit_directpost_{$id}"];
				$content_pref["content_submit_icon_{$id}"] = $_POST["content_submit_icon_{$id}"];
				$content_pref["content_submit_attach_{$id}"] = $_POST["content_submit_attach_{$id}"];
				$content_pref["content_submit_images_{$id}"] = $_POST["content_submit_images_{$id}"];
				$content_pref["content_submit_comment_{$id}"] = $_POST["content_submit_comment_{$id}"];
				$content_pref["content_submit_rating_{$id}"] = $_POST["content_submit_rating_{$id}"];
				$content_pref["content_submit_score_{$id}"] = $_POST["content_submit_score_{$id}"];
				$content_pref["content_submit_pe_{$id}"] = $_POST["content_submit_pe_{$id}"];
				$content_pref["content_submit_visibility_{$id}"] = $_POST["content_submit_visibility_{$id}"];
				$content_pref["content_submit_meta_{$id}"] = $_POST["content_submit_meta_{$id}"];
				$content_pref["content_submit_custom_number_{$id}"] = $_POST["content_submit_custom_number_{$id}"];
				$content_pref["content_submit_images_number_{$id}"] = $_POST["content_submit_images_number_{$id}"];
				$content_pref["content_submit_files_number_{$id}"] = $_POST["content_submit_files_number_{$id}"];

				$content_pref["content_cat_icon_path_large_{$id}"] = $_POST["content_cat_icon_path_large_{$id}"];
				$content_pref["content_cat_icon_path_small_{$id}"] = $_POST["content_cat_icon_path_small_{$id}"];
				$content_pref["content_icon_path_{$id}"] = $_POST["content_icon_path_{$id}"];
				$content_pref["content_image_path_{$id}"] = $_POST["content_image_path_{$id}"];
				$content_pref["content_file_path_{$id}"] = $_POST["content_file_path_{$id}"];
				$content_pref["content_theme_{$id}"] = $_POST["content_theme_{$id}"];

				$content_pref["content_log_{$id}"] = $_POST["content_log_{$id}"];
				$content_pref["content_blank_icon_{$id}"] = $_POST["content_blank_icon_{$id}"];
				$content_pref["content_blank_caticon_{$id}"] = $_POST["content_blank_caticon_{$id}"];
				$content_pref["content_breadcrumb_{$id}"] = $_POST["content_breadcrumb_{$id}"];
				$content_pref["content_breadcrumb_rendertype_{$id}"] = $_POST["content_breadcrumb_rendertype_{$id}"];
				$content_pref["content_searchmenu_{$id}"] = $_POST["content_searchmenu_{$id}"];

				$content_pref["content_list_subheading_{$id}"] = $_POST["content_list_subheading_{$id}"];
				$content_pref["content_list_summary_{$id}"] = $_POST["content_list_summary_{$id}"];
				$content_pref["content_list_date_{$id}"] = $_POST["content_list_date_{$id}"];
				$content_pref["content_list_authorname_{$id}"] = $_POST["content_list_authorname_{$id}"];
				$content_pref["content_list_authoremail_{$id}"] = $_POST["content_list_authoremail_{$id}"];
				$content_pref["content_list_rating_{$id}"] = $_POST["content_list_rating_{$id}"];
				$content_pref["content_list_peicon_{$id}"] = $_POST["content_list_peicon_{$id}"];
				$content_pref["content_list_parent_{$id}"] = $_POST["content_list_parent_{$id}"];
				$content_pref["content_list_refer_{$id}"] = $_POST["content_list_refer_{$id}"];
				$content_pref["content_list_subheading_char_{$id}"] = $_POST["content_list_subheading_char_{$id}"];
				$content_pref["content_list_subheading_post_{$id}"] = $_POST["content_list_subheading_post_{$id}"];
				$content_pref["content_list_summary_char_{$id}"] = $_POST["content_list_summary_char_{$id}"];
				$content_pref["content_list_summary_post_{$id}"] = $_POST["content_list_summary_post_{$id}"];
				$content_pref["content_list_authoremail_nonmember_{$id}"] = $_POST["content_list_authoremail_nonmember_{$id}"];
				$content_pref["content_nextprev_{$id}"] = $_POST["content_nextprev_{$id}"];
				$content_pref["content_nextprev_number_{$id}"] = $_POST["content_nextprev_number_{$id}"];
				$content_pref["content_list_peicon_all_{$id}"] = $_POST["content_list_peicon_all_{$id}"];
				$content_pref["content_list_rating_all_{$id}"] = $_POST["content_list_rating_all_{$id}"];
				$content_pref["content_defaultorder_{$id}"] = $_POST["content_defaultorder_{$id}"];

				$content_pref["content_cat_showparent_{$id}"] = $_POST["content_cat_showparent_{$id}"];
				$content_pref["content_cat_showparentsub_{$id}"] = $_POST["content_cat_showparentsub_{$id}"];
				$content_pref["content_cat_listtype_{$id}"] = ($_POST["content_cat_listtype_{$id}"] ? $_POST["content_cat_listtype_{$id}"] : "0");
				$content_pref["content_cat_menuorder_{$id}"] = $_POST["content_cat_menuorder_{$id}"];
				$content_pref["content_cat_rendertype_{$id}"] = $_POST["content_cat_rendertype_{$id}"];

				$content_pref["content_content_subheading_{$id}"] = $_POST["content_content_subheading_{$id}"];
				$content_pref["content_content_summary_{$id}"] = $_POST["content_content_summary_{$id}"];
				$content_pref["content_content_date_{$id}"] = $_POST["content_content_date_{$id}"];
				$content_pref["content_content_authorname_{$id}"] = $_POST["content_content_authorname_{$id}"];
				$content_pref["content_content_authoremail_{$id}"] = $_POST["content_content_authoremail_{$id}"];
				$content_pref["content_content_rating_{$id}"] = $_POST["content_content_rating_{$id}"];
				$content_pref["content_content_peicon_{$id}"] = $_POST["content_content_peicon_{$id}"];
				$content_pref["content_content_refer_{$id}"] = $_POST["content_content_refer_{$id}"];
				$content_pref["content_content_authoremail_nonmember_{$id}"] = $_POST["content_content_authoremail_nonmember_{$id}"];
				$content_pref["content_content_peicon_all_{$id}"] = $_POST["content_content_peicon_all_{$id}"];
				$content_pref["content_content_rating_all_{$id}"] = $_POST["content_content_rating_all_{$id}"];

				$content_pref["content_menu_viewallcat_{$id}"] = $_POST["content_menu_viewallcat_{$id}"];
				$content_pref["content_menu_viewallauthor_{$id}"] = $_POST["content_menu_viewallauthor_{$id}"];
				$content_pref["content_menu_viewtoprated_{$id}"] = $_POST["content_menu_viewtoprated_{$id}"];
				$content_pref["content_menu_viewrecent_{$id}"] = $_POST["content_menu_viewrecent_{$id}"];
				$content_pref["content_menu_cat_{$id}"] = $_POST["content_menu_cat_{$id}"];
				$content_pref["content_menu_recent_{$id}"] = $_POST["content_menu_recent_{$id}"];
				$content_pref["content_menu_cat_number_{$id}"] = $_POST["content_menu_cat_number_{$id}"];
				$content_pref["content_menu_recent_caption_{$id}"] = $_POST["content_menu_recent_caption_{$id}"];
				$content_pref["content_menu_caption_{$id}"] = $_POST["content_menu_caption_{$id}"];
				$content_pref["content_menu_search_{$id}"] = $_POST["content_menu_search_{$id}"];
				$content_pref["content_menu_sort_{$id}"] = $_POST["content_menu_sort_{$id}"];

				$tmp = addslashes(serialize($content_pref));
				$sql -> db_Update($plugintable, "content_pref='$tmp' WHERE content_id='$id' ");

				return $content_pref;
		}

		function checkUnValidContent($query){
				global $plugintable, $datequery;
				$UnValidArticleIds = "";

				if(!is_object($sqlcheckunvalidcontent)){ $sqlcheckunvalidcontent = new db; }
				if($sqlcheckunvalidcontent -> db_Select($plugintable, "content_id, content_parent, content_class", $query." ".$datequery)){
					while(list($content_id, $content_parent, $content_class) = $sqlcheckunvalidcontent -> db_Fetch()){
						if(!check_class($content_class)){
							$UnValidArticleIds .= " content_id != '".$content_id."' AND";
						}else{
							if($content_parent != "0"){
								$checkparent = $this -> checkParentValidity($content_parent);
								if($checkparent == FALSE){
									$UnValidArticleIds .= " content_id != '".$content_id."' AND";
								}
							}
						}
					}
					$UnValidArticleIds = ($UnValidArticleIds == "" ? "" : " AND ".substr($UnValidArticleIds, 0, -3) );
				}
				return $UnValidArticleIds;
		}

		function checkParentValidity($parent){
				global $plugintable, $datequery;

				if(!is_object($sqlcheckparentvalidity)){ $sqlcheckparentvalidity = new db; }
				if(strpos($parent, ".")){
					$tmp = explode(".", $parent);
					for($i=1;$i<count($tmp);$i++){
						if($sqlcheckparentvalidity -> db_Select($plugintable, "content_class", "content_id = '".$tmp[$i]."' ".$datequery." " )){
							while(list($content_class) = $sqlcheckparentvalidity -> db_Fetch()){
								if(!check_class($content_class)){
									return FALSE;
								}
							}
						}
					}
				}else{
					if($sqlcheckparentvalidity -> db_Select($plugintable, "content_class", "content_id = '".$parent."' ".$datequery." " )){
						while(list($content_class) = $sqlcheckparentvalidity -> db_Fetch()){
							if(!check_class($content_class)){
								return FALSE;
							}
						}
					}
				}
				return TRUE;
		}

		function getAuthor($content_author) {
				global $plugintable, $datequery;

				if(!is_object($sqlauthor)){ $sqlauthor = new db; }
				if(is_numeric($content_author)){
					$sqlauthor -> db_Select("user", "user_id, user_name, user_email", "user_id=$content_author");
					list($author_id, $author_name, $author_email) = $sqlauthor -> db_Fetch();
					$getAuthorName = array($author_id, $author_name, $author_email, $content_author);
				}else{
					$tmp = explode("^", $content_author);
					$author_id = $tmp[0];
					$author_name = $tmp[1];
					$author_email = $tmp[2];
					$getAuthorName = array($author_id, $author_name, $author_email, $content_author);
				}
				return $getAuthorName;
		}

		function getContent($id){
				global $plugintable, $datequery;

				if(!is_object($sqlgetcontent)){ $sqlgetcontent = new db; }
				if(!$sqlgetcontent -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class, content_pref as contentprefvalue", "content_id='".$id."' ".$datequery." ")){
					return FALSE;
				}else{
					$row = $sqlgetcontent -> db_Fetch();
					if(check_class($row['content_class'])){
						return $row;
					}else{
						return FALSE;
					}
				}
		}


		function countItemsInCat($id, $parent, $nolan=""){
				global $plugintable, $datequery;

				$itemswithparent = ($parent == "0" ? $id.".".$id : substr($parent,2).".".substr($parent,2).".".$id );

				if(!is_object($sqlcountitemsincat)){ $sqlcountitemsincat = new db; }
				$n = $sqlcountitemsincat -> db_Select($plugintable, "content_class", "content_parent='".$itemswithparent."' AND content_refer != 'sa' ".$datequery." ");
				if($n){
					while($row = $sqlcountitemsincat -> db_Fetch()){
					extract($row);
						if(!check_class($content_class)){
							$n = $n - 1;
						}
					}
				}else{
					$n = "0";
				}
				if($nolan){
					return $n;
				}else{
					return $n." ".($n == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
				}
		}


		function getParent($id, $level="", $mode="", $classcheck=""){
				global $plugintable, $datequery;

				if(!$id){
					$id = "0";
				}elseif(is_numeric($id)){
					$id = "0.".$id;
				}
				$query = ($mode != "" ? " AND content_id = '".$mode."' " : "");
				if(!$level) { $level = "0"; }
				if(!is_object($sqlgetparent)){ $sqlgetparent = new db; }
				if(!$sqlgetparent -> db_Select($plugintable, "*", "content_parent='".$id."' ".$query." ".$datequery." ")){
					$parent = FALSE;
				}else{
					while($row = $sqlgetparent -> db_Fetch()){
					extract($row);

						if($classcheck=="1"){
							if(check_class($content_class)){
								$parent[] = array($content_id, $content_heading, $content_subheading, $content_summary, $content_text, $content_author, $content_icon, $content_file, $content_image, $content_parent, $content_comment, $content_rate, $content_pe, $content_refer, $content_datestamp, $content_class, $level);
								$reloop = TRUE;
							}else{
								$reloop = FALSE;
							}
						}else{
								$parent[] = array($content_id, $content_heading, $content_subheading, $content_summary, $content_text, $content_author, $content_icon, $content_file, $content_image, $content_parent, $content_comment, $content_rate, $content_pe, $content_refer, $content_datestamp, $content_class, $level);
								$reloop = TRUE;
						}
						if($reloop == TRUE){
							$level2 = $level+1;
							$nid = ($content_parent == "0" ? $content_id : $content_parent.".".$content_id);
							$parentchild = $this -> getParent($nid, $level2, "", ($classcheck=="1" ? "1" : ""));
							if($parentchild == TRUE){
								if(is_array($parentchild[0])){
									for($a=0;$a<count($parentchild);$a++){
										$parent[] = $parentchild[$a];
									}
								}else{
									$parent[] = $parentchild;
								}
							}
						}
					}
				}
				return $parent;
		}


		function printParent($array, $level, $currentparent, $mode="option"){
				global $rs, $type, $type_id, $plugintable, $aa;
				$string = "";

				$content_pref = $this -> getContentPref($type_id);
				$content_cat_icon_path_large = $this -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
				$content_cat_icon_path_small = $this -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);

				if(empty($array)){ return FALSE; }

				for($a=0;$a<count($array);$a++){
						if(!$array[$a][16] || $array[$a][16] == "0"){
							$pre = "";
							$class = "forumheader";
							$style = " font-weight:bold; ";
						}else{
							$pre = "";
							$class = "forumheader3";
							$style = " font-weight:normal; ";
							for($b=0;$b<$array[$a][16];$b++){
								$pre .= "_";
								//$pre .= "&nbsp;";
							}
						}
						
						if($mode == "array"){
							if(strpos($currentparent, ".")){
								$tmp = explode(".", $currentparent);
								$currentparent = $tmp[1];
							}
							$string[] = array($array[$a][0], $pre.$array[$a][1], $array[$a][9]);

						}elseif($mode == "optionadminmenu"){
							if(strpos($array[$a][9], ".")){
								$tmp = explode(".", $array[$a][9]);
								$mainparent = $tmp[1];
								$id = $tmp[1]."-".$tmp[1];
								for($i=2;$i<count($tmp);$i++){
									$id .= "-".$tmp[$i];
								}
								$id .= "-".$array[$a][0];
							}else{
								$id = $array[$a][0]."-".$array[$a][0];
								$mainparent = $array[$a][0];
							}
							$string[] = array($array[$a][0], $pre.$array[$a][1], $array[$a][9], $id, $mainparent);

						}elseif($mode == "optioncat"){
							if(strpos($array[$a][9], ".")){
								$tmp1 = explode(".", $array[$a][9]);
								$id = $array[$a][9].".".$array[$a][0];
							}else{
								$id = $array[$a][9].".".$array[$a][0];
							}
							$string .= $rs -> form_option($pre.$array[$a][1], ($id == $currentparent ? "1" : "0"), $id);

						}elseif($mode == "optioncontent"){
							$tmp = array_reverse( explode(".", $currentparent) );
							$currentparent = $tmp[0];
							
							if(strpos($array[$a][9], ".")){
								$tmp = explode(".", $array[$a][9]);
								$id = $tmp[1].".".$tmp[1];
								for($i=2;$i<count($tmp);$i++){
									$id .= ".".$tmp[$i];
								}
								$id .= ".".$array[$a][0];
							}else{
								$id = $array[$a][0].".".$array[$a][0];
							}
							$string .= $rs -> form_option($pre.$array[$a][1], ($array[$a][0] == $currentparent ? "1" : "0"), $id);

						}elseif($mode == "table"){
							if(strpos($array[$a][9], ".")){
								$tmp1 = explode(".", $array[$a][9]);
								$id = $tmp1[1].".".$array[$a][0];
							}else{
								$id = $array[$a][0].".".$array[$a][0];
							}
							$delete_heading = str_replace("&#39;", "\'", $array[$a][1]);
							$authordetails = $this -> getAuthor($array[$a][5]);
							$caticon = $content_cat_icon_path_large.$array[$a][6];

							$string .= "
							<tr>
								<td class='".$class."' style='".$style." width:5%; text-align:left'>".$array[$a][9].".".$array[$a][0]."</td>
								<td class='".$class."' style='".$style." width:5%; text-align:center'>".($array[$a][6] ? "<img src='".$caticon."' alt='' style='vertical-align:middle' />" : "&nbsp;")."</td>
								<td class='".$class."' style='".$style." width:15%'>[".$authordetails[0]."] ".$authordetails[1]."</td>
								<td class='".$class."' style='".$style." width:70%; white-space:nowrap;'>".$pre.$array[$a][1]." [".$array[$a][2]."]</td>
								<td class='".$class."' style='".$style." width:5%; text-align:left; white-space:nowrap;'>
								".$rs -> form_open("post", e_SELF."?".$type.".".$type_id.".cat.manage", "myform_{$array[$a][0]}","","", "")."
								<a href='".e_SELF."?".$type.".".$type_id.".cat.edit.".$array[$a][0]."'>".CONTENT_ICON_EDIT."</a> 
								<a onclick=\"if(confirm_('cat','$delete_heading','".$array[$a][0]."')){document.forms['myform_{$array[$a][0]}'].submit();}\" >".CONTENT_ICON_DELETE."</a>
								".($array[$a][9] == "0" ? "<a href='".e_SELF."?".$type.".".$type_id.".cat.options.".$array[$a][0]."'>".CONTENT_ICON_OPTIONS."</a>" : "")."
								".($array[$a][9] != "0" && getperms("0") ? "<a href='".e_SELF."?".$type.".".$type_id.".cat.contentmanager.".$array[$a][0]."'>".CONTENT_ICON_PERSONALMANAGER."</a>" : "")."
								".$rs -> form_hidden("cat_delete_{$array[$a][0]}", "delete")."
								".$rs -> form_close()."
								</td>
							</tr>";
						}
				}
				return $string;
		}

		//get parent tree from a content item (content_parent)
		function getBreadCrumb($id=""){
				global $plugintable, $datequery;
				$sqlgetbreadcrumb = "";

				if(strpos($id, ".")){
					$tmp = array_reverse( explode(".", $id) );
					$id = $tmp[0];
				}
				if($id != ""){					
					if(!is_object($sqlgetbreadcrumb)){ $sqlgetbreadcrumb = new db; }
					if(!$sqlgetbreadcrumb -> db_Select($plugintable, "content_id, content_heading, content_parent", "content_id='".$id."' ".$datequery." ")){
						$parent = FALSE;
					}else{
						while(list($parent_id, $parent_heading, $parent_parent) = $sqlgetbreadcrumb -> db_Fetch()){
							$parent[] = array($parent_id, $parent_heading, $parent_parent);
							if(strpos($parent_parent, ".")){
								$parentchild = $this -> getBreadCrumb($parent_parent);
								if($parentchild == TRUE){
									if(is_array($parentchild[0])){
										for($a=0;$a<count($parentchild);$a++){
											$parent[] = $parentchild[$a];
										}
									}else{
										$parent[] = $parentchild;
									}
								}
							}
						}
					}
				}else{
					$parent = "";
				}
				return $parent;
		}

		function printBreadCrumb($breadcrumb="", $mode="", $nolink=""){
				global $content_pref, $type, $type_id;
				$contentpreflimit = "5";

				if($breadcrumb != ""){
					$result = array_reverse($breadcrumb);
					if(count($result) > $contentpreflimit){
						$limit = count($result)-$contentpreflimit;
						$pre = "... > ";
					}else{
						$limit = "0";
						$pre = "";
					}
				}

				if($mode == "nobase"){
					$breadcrumbstring = "";
				}else{
					if($nolink){
						$breadcrumbstring = CONTENT_LAN_58." > ".CONTENT_LAN_59." > ".CONTENT_LAN_60." > ";
					}else{
						$breadcrumbstring = "<a href='".e_BASE."'>".CONTENT_LAN_58."</a> > <a href='".e_SELF."'>".CONTENT_LAN_59."</a> > <a href='".e_SELF."?".$type.".".$type_id."'>".CONTENT_LAN_60."</a> > ";
					}
				}
				if($breadcrumb != ""){
					$breadcrumbstring .= $pre;
					for($a=$limit;$a<count($result);$a++){
						if($result[$a][1]){
							if($nolink){
								$breadcrumbstring .= $result[$a][1]." > ";
							}else{
								$breadcrumbstring .= "<a href='".e_SELF."?".$type.".".$type_id.".cat.".$result[$a][0]."'>".$result[$a][1]."</a> > ";
							}
						}
					}
				}
				return substr($breadcrumbstring, 0, -3);
		}


		function getOrder(){
				global $type, $type_id, $action, $sub_action, $id, $content_pref;

				if(substr($action,0,5) == "order"){
					$orderstring = $action;
				}elseif(substr($sub_action,0,5) == "order"){
					$orderstring = $sub_action;
				}elseif(substr($id,0,5) == "order"){
					$orderstring = $id;
				}else{
					$orderstring = ($content_pref["content_defaultorder_{$type_id}"] ? $content_pref["content_defaultorder_{$type_id}"] : "orderddate" );
				}

				if(substr($orderstring,6) == "heading"){
					$orderby = "content_heading";
					$orderby2 = "";
				}elseif(substr($orderstring,6) == "date"){
					$orderby = "content_datestamp";
					$orderby2 = ", content_heading ASC";
				}elseif(substr($orderstring,6) == "parent"){
					$orderby = "content_parent";
					$orderby2 = ", content_heading ASC";
				}elseif(substr($orderstring,6) == "refer"){
					$orderby = "content_refer";
					$orderby2 = ", content_heading ASC";
				}else{
					$orderby = "content_datestamp";
					$orderby2 = ", content_heading ASC";
				}
				$order = " ORDER BY ".$orderby." ".(substr($orderstring,5,1) == "a" ? "ASC" : "DESC")." ".$orderby2." ";

				return $order;
		}


		function getIcon($mode, $icon, $path="", $linkid="", $width="", $blank=""){
				global $content_cat_icon_path_small, $content_cat_icon_path_large, $content_icon_path, $content_pref;

				if($mode == "item"){
					$path = (!$path ? $content_icon_path : $path);
					$hrefpre = ($linkid ? "<a href='".e_SELF."?".$linkid."'>" : "");
					$hrefpost = ($linkid ? "</a>" : "");
					$width = ($width ? "width:".$width."px;" : "");
					$border = "border:1px solid #000;";
					$blank = (!$blank ? "0" : $blank);
					$icon = ($icon ? $path.$icon : ($blank ? $content_icon_path."blank.gif" : ""));

				}elseif($mode == "catsmall"){
					$path = (!$path ? $content_cat_icon_path_small : $path);
					$hrefpre = ($linkid ? "<a href='".e_SELF."?".$linkid."'>" : "");
					$hrefpost = ($linkid ? "</a>" : "");
					$border = "border:0;";
					$blank = (!$blank ? "0" : $blank);
					$icon = ($icon ? $path.$icon : "");

				}elseif($mode == "catlarge"){
					$path = (!$path ? $content_cat_icon_path_large : $path);
					$hrefpre = ($linkid ? "<a href='".e_SELF."?".$linkid."'>" : "");
					$hrefpost = ($linkid ? "</a>" : "");
					$border = "border:0;";
					$blank = (!$blank ? "0" : $blank);
					$icon = ($icon ? $path.$icon : "");
				}else{
					$path = (!$path ? $content_icon_path : $path);
					$hrefpre = "";
					$hrefpost = "";
					$width = "";
					$border = "border:0;";
					$blank = (!$blank ? "0" : $blank);
					$icon = ($icon ? $path.$icon : ($blank ? $content_icon_path."blank.gif" : ""));
				}
				if($icon && file_exists($icon)){
					$iconstring = $hrefpre."<img src='".$icon."' alt='' style='".$width." ".$border."' />".$hrefpost;
				}else{
					if($blank){
						if(file_exists($content_icon_path."blank.gif")){
							if($mode == "catsmall"){
								$width = ($width ? "width:".$width."px;" : "width:16px;");
							}elseif($mode == "catlarge"){
								$width = ($width ? "width:".$width."px;" : "width:48px;");
							}
							$iconstring = $hrefpre."<img src='".$content_icon_path."blank.gif' alt='' style='".$width." ".$border."' />".$hrefpost;
						}else{
							$iconstring = "&nbsp;";
						}
					}else{
						$iconstring = "&nbsp;";
					}
				}

				return $iconstring;
		}


		function parseContentPathVars($srstring){

				$search = array("{e_BASE}", "{e_ADMIN}", "{e_IMAGE}", "{e_THEME}", "{e_PLUGIN}", "{e_FILE}", "{e_HANDLER}", "{e_LANGUAGEDIR}", "{e_DOCS}", "{e_DOCROOT}");
				$replace = array(e_BASE, e_ADMIN, e_IMAGE, e_THEME, e_PLUGIN, e_FILE, e_HANDLER, e_LANGUAGEDIR, e_DOCS, e_DOCROOT);
				return(str_replace($search, $replace, $srstring));
		}


		function ContentManagerValidCategoryCheck($fullcat){
				global $plugintable, $datequery;

				$tmp = array_reverse( explode("-", $fullcat) );
				if(!is_object($sql)){ $sql = new db; }
				if(!$sql -> db_Select($plugintable, "content_id, content_heading, content_parent, content_class, content_pref as contentprefvalue", "LEFT(content_parent,1) = '0' AND content_id = '".$tmp[0]."' ".$datequery." ")){
					header("location:".e_SELF); exit;
				}else{
					while($row = $sql -> db_Fetch()){
						extract($row);
						$tmp = explode(".", $contentprefvalue);
						if(($tmp[0] != "" && in_array($userid, $tmp)) || getperms("0") ){
							return;
						}else{
							header("location:".e_SELF); exit;
						}
					}
				}
		}


		function CreateParentMenu($parentid){
				global $plugintable, $tp, $datequery;

				if(!is_object($sqlcreatemenu)){ $sqlcreatemenu = new db; }
				if(!$sqlcreatemenu -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class, content_pref as contentprefvalue", "content_id='".$parentid."' ".$datequery." ")){
					return FALSE;
				}else{
					$row = $sqlcreatemenu -> db_Fetch();
				}
				$menufile = $row['content_heading'];

				$data = chr(60)."?php\n". chr(47)."*\n+---------------------------------------------------------------+\n|        e107 website system\n|        ".e_PLUGIN."content/menus/".$row['content_heading'].".php\n|\n|        ©Steve Dunstan 2001-2002\n|        http://e107.org\n|        jalist@e107.org\n|\n|        Released under the terms and conditions of the\n|        GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n\nThis file has been generated by ".e_PLUGIN."content/handlers/content_class.php.\n\n*". chr(47)."\n\n";

				$data .= "unset(".chr(36)."text);\n";
				$data .= chr(36)."text = \"\";\n";
				$data .= "global ".chr(36)."plugintable;\n";
				$data .= "require_once(e_PLUGIN.\"content/handlers/content_class.php\");\n";
				$data .= chr(36)."aa = new content;\n";
				$data .= "require_once(e_HANDLER.\"form_handler.php\");\n";
				$data .= chr(36)."rs = new form;\n\n";
				$data .= chr(36)."lan_file = e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content.php';\n";
				$data .= "include(file_exists(".chr(36)."lan_file) ? ".chr(36)."lan_file : e_PLUGIN.'content/languages/English/lan_content.php');\n\n";

				$data .= chr(36)."content_pref = ".chr(36)."aa -> getContentPref(\"$parentid\");\n";
				$data .= chr(36)."content_pref[\"content_cat_icon_path_small_{$parentid}\"] = (".chr(36)."content_pref[\"content_cat_icon_path_small_{$parentid}\"] ? ".chr(36)."content_pref[\"content_cat_icon_path_small_{$parentid}\"] : \"{e_PLUGIN}content/images/cat/16/\" );\n";
				$data .= chr(36)."content_icon_path = ".chr(36)."aa -> parseContentPathVars(".chr(36)."content_pref[\"content_icon_path_{$parentid}\"]);\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_search_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(36)."rs -> form_open(\"post\", e_PLUGIN.\"content/content.php?type.$parentid\", \"contentsearchmenu_{$parentid}\", \"\", \"enctype='multipart/form-data'\");\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<input class='tbox' size='20' type='text' id='searchfieldmenu_{$parentid}' name='searchfieldmenu_{$parentid}' value='".chr(34).".(isset(".chr(36)."_POST['searchfieldmenu_{$parentid}']) ? ".chr(36)."_POST['searchfieldmenu_{$parentid}'] : CONTENT_LAN_18).".chr(34)."' maxlength='100' onfocus=\\\"document.forms['contentsearchmenu_{$parentid}'].searchfieldmenu_{$parentid}.value='';\\\" />".chr(34).";\n";
				$data .= "   ".chr(36)."text .= ".chr(34)." <input class='button' type='submit' name='searchsubmit' value='\".CONTENT_LAN_19.\"' />".chr(34).";\n";
				$data .= "   ".chr(36)."text .= ".chr(36)."rs -> form_close().".chr(34)."<br />".chr(34).";\n";
				$data .= "}\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_sort_{$parentid}\"]){\n";
				$data .= "   if(eregi('content.php', e_SELF) && eregi('type.$parentid', e_QUERY)){\n";
				$data .= "      if(".chr(36)."action == \"content\"){\n";
				$data .= "      }elseif(".chr(36)."action == \"author\" && !".chr(36)."sub_action){\n";
				$data .= "      }elseif(".chr(36)."action == \"cat\" && !".chr(36)."sub_action){\n";
				$data .= "      }elseif(".chr(36)."action == \"top\"){\n";
				$data .= "      }else{\n";

				$data .= "         if(e_QUERY){\n";
				$data .= "            ".chr(36)."tmp = explode(\".\", e_QUERY);\n";
				$data .= "            ".chr(36)."type = ".chr(36)."tmp[0];\n";
				$data .= "            ".chr(36)."type_id = ".chr(36)."tmp[1];\n";
				$data .= "            ".chr(36)."action = ".chr(36)."tmp[2];\n";
				$data .= "            ".chr(36)."sub_action = ".chr(36)."tmp[3];\n";
				$data .= "            ".chr(36)."id = ".chr(36)."tmp[4];\n";
				$data .= "            ".chr(36)."id2 = ".chr(36)."tmp[5];\n";
				$data .= "            unset(".chr(36)."tmp);\n";
				$data .= "         }\n";
				$data .= "         if(".chr(36)."type == \"0\" || is_numeric(".chr(36)."type)){\n";
				$data .= "            ".chr(36)."from = ".chr(36)."type;\n";
				$data .= "            ".chr(36)."type = ".chr(36)."type_id;\n";
				$data .= "            ".chr(36)."type_id = ".chr(36)."action;\n";
				$data .= "            ".chr(36)."action = ".chr(36)."sub_action;\n";
				$data .= "            ".chr(36)."sub_action = ".chr(36)."id;\n";
				$data .= "            ".chr(36)."id = ".chr(36)."id2;\n";
				$data .= "         }else{\n";
				$data .= "            ".chr(36)."from = \"0\";\n";
				$data .= "         }\n\n";
				
				$data .= "         ".chr(36)."querystring = (".chr(36)."action && substr(".chr(36)."action,0,5) != \"order\" ? \".\".".chr(36)."action : \"\").(".chr(36)."sub_action && substr(".chr(36)."sub_action,0,5) != \"order\" ? \".\".".chr(36)."sub_action : \"\").(".chr(36)."id && substr(".chr(36)."id,0,5) != \"order\" ? \".\".".chr(36)."id : \"\").(".chr(36)."id2 && substr(".chr(36)."id2,0,5) != \"order\" ? \".\".".chr(36)."id2 : \"\");\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_open(\"post\", e_PLUGIN.\"content/content.php?type.$parentid\", \"contentsearchorder\", \"\", \"enctype='multipart/form-data'\");\n";
				$data .= "         ".chr(36)."text .= \"<select id='ordervalue' name='ordervalue' class='tbox' onchange=\\\"document.location=this.options[this.selectedIndex].value;\\\">".chr(34).";\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_9, 0, \"none\");\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_10, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderaheading\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_11, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderdheading\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_12, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderadate\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_13, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderddate\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_14, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderarefer\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_15, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderdrefer\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_16, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderaparent\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_17, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderdparent\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_select_close();\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_close().".chr(34)."<br />".chr(34).";\n";
				$data .= "      }\n";
				$data .= "   }\n";
				$data .= "}\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_viewallcat_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<img src='\".THEME.\"images/bullet2.gif' alt='' /> <a href='\".e_PLUGIN.\"content/content.php?type.$parentid.cat'>\".CONTENT_LAN_62.\"</a><br />".chr(34).";\n\n";
				$data .= "}\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_viewallauthor_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<img src='\".THEME.\"images/bullet2.gif' alt='' /> <a href='\".e_PLUGIN.\"content/content.php?type.$parentid.author'>\".CONTENT_LAN_63.\"</a><br />".chr(34).";\n\n";
				$data .= "}\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_viewtoprated_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<img src='\".THEME.\"images/bullet2.gif' alt='' /> <a href='\".e_PLUGIN.\"content/content.php?type.$parentid.top'>\".CONTENT_LAN_64.\"</a><br />".chr(34).";\n\n";
				$data .= "}\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_viewrecent_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<img src='\".THEME.\"images/bullet2.gif' alt='' /> <a href='\".e_PLUGIN.\"content/content.php?type.$parentid'>\".CONTENT_LAN_61.\"</a><br />".chr(34).";\n\n";
				$data .= "}\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_cat_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<br />".chr(34).";\n\n";
				$data .= "   ".chr(36)."parentdetails = ".chr(36)."aa -> getParent(\"\", \"\", $parentid, \"1\");\n";
				$data .= "   ".chr(36)."parentarray = ".chr(36)."aa -> printParent(".chr(36)."parentdetails, \"0\", $parentid, \"array\");\n";
				//$data .= "   if(!is_object(".chr(36)."sql2)){ ".chr(36)."sql2 = new db; }\n";
				$data .= "   for(".chr(36)."i=0;".chr(36)."i<count(".chr(36)."parentarray);".chr(36)."i++){\n\n";
				$data .= "      ".chr(36)."n = ".chr(36)."aa -> countItemsInCat(".chr(36)."parentarray[".chr(36)."i][0], ".chr(36)."parentarray[".chr(36)."i][2], \"nolan\");\n";
				$data .= "      ".chr(36)."text .= \"<img src='\".THEME.\"images/bullet2.gif' alt='' /> <a href='\".e_PLUGIN.\"content/content.php?type.$parentid.cat.\".".chr(36)."parentarray[".chr(36)."i][0].\"'>\".".chr(36)."parentarray[".chr(36)."i][1].\"</a>\";\n";
				$data .= "      if(".chr(36)."content_pref[\"content_menu_cat_number_{$parentid}\"]){\n";
				$data .= "         ".chr(36)."text .= \" <span class='smalltext'>(\".".chr(36)."n.\")</span>\";\n";
				$data .= "      }\n";
				$data .= "      ".chr(36)."text .= ".chr(34)."<br />".chr(34).";\n";
				$data .= "   }\n";
				$data .= "}\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_recent_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<br />".chr(34).";\n\n";
				$data .= "   if(!is_object(".chr(36)."sql)){ ".chr(36)."sql = new db; }\n";
				$data .= "   ".chr(36)."query = \" LEFT(content_parent,\".(strlen($parentid)).\") = '$parentid' \";\n";
				$data .= "   ".chr(36)."UnValidArticleIds = ".chr(36)."aa -> checkUnValidContent(".chr(36)."query);\n";
				$data .= "   if(".chr(36)."sql -> db_Select(".chr(36)."plugintable, \"content_id, content_heading, content_subheading, content_author, content_parent, content_datestamp, content_class\", \"content_refer != 'sa' AND \".".chr(36)."query.\" \".".chr(36)."UnValidArticleIds.\" AND (content_datestamp=0 || content_datestamp < \".time().\") AND (content_enddate=0 || content_enddate>\".time().\") ORDER BY content_datestamp DESC LIMIT 0,5\")){\n\n";
				$data .= "      ".chr(36)."text .= (".chr(36)."content_pref[\"content_menu_recent_caption_{$parentid}\"] != \"\" ? ".chr(36)."content_pref[\"content_menu_recent_caption_{$parentid}\"] : \"recent items: ".$row['content_heading']."\").".chr(34)."<br />".chr(34).";\n";
				$data .= "      while(".chr(36)."row = ".chr(36)."sql -> db_Fetch()){\n";
				$data .= "      extract(".chr(36)."row);\n";
				$data .= "         ".chr(36)."text .= ".chr(34)."<img src='\".THEME.\"images/bullet2.gif' alt='' /> <a href='\".e_PLUGIN.\"content/content.php?type.$parentid.content.\".".chr(36)."content_id.\"'>\".".chr(36)."content_heading.\"</a><br />".chr(34).";\n";
				$data .= "      }\n";
				$data .= "   }\n";
				$data .= "}\n\n";

				$data .= "if(!isset(".chr(36)."text)){ ".chr(36)."text = \"no items in ".$row['content_heading']." yet\"; }\n";

				$data .= chr(36)."caption = (".chr(36)."content_pref[\"content_menu_caption_{$parentid}\"] != \"\" ? ".chr(36)."content_pref[\"content_menu_caption_{$parentid}\"] : ".chr(34).$row['content_heading'].chr(34).");\n";

				$data .= chr(36)."ns -> tablerender(".chr(36)."caption, ".chr(36)."text);\n";
				$data .= "?".chr(62);
				 
				if(file_exists(e_PLUGIN."content/menus/".$menufile."_menu.php")){
					$message = "";
				}else{
					$fp = @fopen(e_PLUGIN."content/menus/".$menufile."_menu.php", "w");
					if (!@fwrite($fp, $data)) {
						$message = CONTENT_ADMIN_OPT_LAN_81;
					} else {
						fclose($fp);
						$message = CONTENT_ADMIN_OPT_LAN_82;
					}
				}
				return $message;
		}


}

?>