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
|		$Revision: 1.15 $
|		$Date: 2005-02-12 09:52:53 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

/*
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
if (!defined('CONTENT_ICON_WARNING')) { define("CONTENT_ICON_WARNING", "<img src='".e_PLUGIN."content/images/warning_16.png' alt='warning' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_OK')) { define("CONTENT_ICON_OK", "<img src='".e_PLUGIN."content/images/ok_16.png' alt='ok' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_ERROR')) { define("CONTENT_ICON_ERROR", "<img src='".e_PLUGIN."content/images/error_16.png' alt='error' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_ORDERCAT')) { define("CONTENT_ICON_ORDERCAT", "<img src='".e_PLUGIN."content/images/view_remove.png' alt='order items in category' style='border:0; cursor:pointer;' />"); }
if (!defined('CONTENT_ICON_ORDERALL')) { define("CONTENT_ICON_ORDERALL", "<img src='".e_PLUGIN."content/images/window_new.png' alt='order items in main parent' style='border:0; cursor:pointer;' />"); }
*/



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
				$content_pref["content_breadcrumb_seperator{$id}"] = ">";				//seperator character between breadcrumb
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

				$content_pref["content_menu_caption_{$id}"] = "content menu";			//caption of menu
				$content_pref["content_menu_search_{$id}"] = "1";						//show search keyword
				$content_pref["content_menu_sort_{$id}"] = "1";							//show sorting methods
				$content_pref["content_menu_viewallcat_{$id}"] = "1";					//menu: view link to all categories
				$content_pref["content_menu_viewallauthor_{$id}"] = "1";				//menu: view link to all authors
				$content_pref["content_menu_viewtoprated_{$id}"] = "1";					//menu: view link to top rated items
				$content_pref["content_menu_viewrecent_{$id}"] = "1";					//menu: view link to recent items
				$content_pref["content_menu_viewsubmit_{$id}"] = "1";					//view link to submit content item (only if it is allowed)
				$content_pref["content_menu_viewicon_{$id}"] = "1";						//choose icon to display for links
				$content_pref["content_menu_cat_{$id}"] = "1";							//view categories
				$content_pref["content_menu_cat_number_{$id}"] = "1";					//show number of items in category				
				$content_pref["content_menu_cat_icon_{$id}"] = "5";						//choose icon to display for categories
				$content_pref["content_menu_recent_{$id}"] = "1";						//view recent list
				$content_pref["content_menu_recent_caption_{$id}"] = "recent items";	//caption of recent list
				$content_pref["content_menu_recent_number_{$id}"] = "5";				//number of recent items to show
				$content_pref["content_menu_recent_date_{$id}"] = "1";					//show date in recent list
				$content_pref["content_menu_recent_author_{$id}"] = "1";				//show author in recent list
				$content_pref["content_menu_recent_subheading_{$id}"] = "1";			//show subheading in recent list
				$content_pref["content_menu_recent_subheading_char_{$id}"] = "80";		//number of characters of subheading to show
				$content_pref["content_menu_recent_subheading_post_{$id}"] = "[...]";	//postfix for too long subheadings
				$content_pref["content_menu_recent_icon_{$id}"] = "5";					//choose icon to display for recent items
				$content_pref["content_menu_recent_icon_width_{$id}"] = "50";			//specify width of icon (only if content_icon is set)

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
				$content_pref["content_breadcrumb_seperator{$id}"] = $_POST["content_breadcrumb_seperator{$id}"];
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

				$content_pref["content_menu_caption_{$id}"] = $_POST["content_menu_caption_{$id}"];
				$content_pref["content_menu_search_{$id}"] = $_POST["content_menu_search_{$id}"];
				$content_pref["content_menu_sort_{$id}"] = $_POST["content_menu_sort_{$id}"];
				$content_pref["content_menu_viewallcat_{$id}"] = $_POST["content_menu_viewallcat_{$id}"];
				$content_pref["content_menu_viewallauthor_{$id}"] = $_POST["content_menu_viewallauthor_{$id}"];
				$content_pref["content_menu_viewtoprated_{$id}"] = $_POST["content_menu_viewtoprated_{$id}"];
				$content_pref["content_menu_viewrecent_{$id}"] = $_POST["content_menu_viewrecent_{$id}"];
				$content_pref["content_menu_viewsubmit_{$id}"] = $_POST["content_menu_viewsubmit_{$id}"];
				$content_pref["content_menu_viewicon_{$id}"] = $_POST["content_menu_viewicon_{$id}"];
				$content_pref["content_menu_cat_{$id}"] = $_POST["content_menu_cat_{$id}"];
				$content_pref["content_menu_cat_number_{$id}"] = $_POST["content_menu_cat_number_{$id}"];				
				$content_pref["content_menu_cat_icon_{$id}"] = $_POST["content_menu_cat_icon_{$id}"];
				$content_pref["content_menu_recent_{$id}"] = $_POST["content_menu_recent_{$id}"];
				$content_pref["content_menu_recent_caption_{$id}"] = $_POST["content_menu_recent_caption_{$id}"];
				$content_pref["content_menu_recent_number_{$id}"] = $_POST["content_menu_recent_number_{$id}"];
				$content_pref["content_menu_recent_date_{$id}"] = $_POST["content_menu_recent_date_{$id}"];
				$content_pref["content_menu_recent_author_{$id}"] = $_POST["content_menu_recent_author_{$id}"];
				$content_pref["content_menu_recent_subheading_{$id}"] = $_POST["content_menu_recent_subheading_{$id}"];
				$content_pref["content_menu_recent_subheading_char_{$id}"] = $_POST["content_menu_recent_subheading_char_{$id}"];
				$content_pref["content_menu_recent_subheading_post_{$id}"] = $_POST["content_menu_recent_subheading_post_{$id}"];
				$content_pref["content_menu_recent_icon_{$id}"] = $_POST["content_menu_recent_icon_{$id}"];
				$content_pref["content_menu_recent_icon_width_{$id}"] = $_POST["content_menu_recent_icon_width_{$id}"];

				$tmp = addslashes(serialize($content_pref));
				$sql -> db_Update($plugintable, "content_pref='$tmp' WHERE content_id='$id' ");

				return $content_pref;
		}


		function checkMainCat($type_id){
				global $plugintable, $datequery;
				$sqlcheckmaincat = "";
				$UnValidArticleIds = "";

				if(!is_object($sqlcheckmaincat)){ $sqlcheckmaincat = new db; }
				if($sqlcheckmaincat -> db_Select($plugintable, "content_id, content_parent, content_class", "content_id = '".$type_id."' ".$datequery." " )){
					while(list($content_id, $content_parent, $content_class) = $sqlcheckmaincat -> db_Fetch()){
						if(!check_class($content_class)){
							$UnValidArticleIds = " content_parent != '".$type_id.".".$type_id."' AND";
						}
					}
				}
				$UnValidArticleIds .= $this -> checkSubCat("0.".$type_id);
				return $UnValidArticleIds;
		}


		function checkSubCat($id){
				global $plugintable, $datequery, $type_id;
				$sqlchecksubcat = "";
				$UnValidArticleIds = "";

				if(!is_object($sqlchecksubcat)){ $sqlchecksubcat = new db; }
				if($sqlchecksubcat -> db_Select($plugintable, "content_id, content_parent, content_class", "content_parent = '".$id."' ".$datequery." " )){
					while(list($content_id, $content_parent, $content_class) = $sqlchecksubcat -> db_Fetch()){
						if(!check_class($content_class)){
							if($type_id != "0"){
								$type_id = substr($id,2);
							}
							$UnValidArticleIds .= " content_parent != '".$type_id.".".$type_id.".".$content_id."' AND";							
						}
						$UnValidArticleIds .= $this -> checkSubCat($id.".".$content_id);
					}
				}
				return $UnValidArticleIds;
		}

		
		function getAuthor($content_author) {
				global $plugintable, $datequery;
				$sqlauthor = "";

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


		function countItemsInCat($id, $parent, $nolan=""){
				global $plugintable, $datequery, $type_id;
				$sqlcountitemsincat = "";

				if($parent == "0"){
					$itemswithparent = $id.".".$id;
				}else{
					$tmp = explode(".", $parent);
					$itemswithparent = $tmp[1].".".substr($parent,strlen($tmp[1])+1).".".$id;
				}

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
				$sqlgetparent = "";

				if(!$id){
					$id = "0";
				}elseif(is_numeric($id)){
					$id = "0.".$id;
				}
				$query = ($mode != "" ? " AND content_id = '".$mode."' " : "");
				$classquery = ($classcheck == "1" ? "AND content_class IN (".USERCLASS_LIST.")" : "");
				if(!$level) { $level = "0"; }
				if(!is_object($sqlgetparent)){ $sqlgetparent = new db; }
				if(!$sqlgetparent -> db_Select($plugintable, "*", "content_parent='".$id."' ".$query." ".$datequery." ".$classquery." ORDER BY content_order")){
					$parent = FALSE;
				}else{
					while($row = $sqlgetparent -> db_Fetch()){
					extract($row);

						$parent[] = array($content_id, $content_heading, $content_subheading, $content_summary, $content_text, $content_author, $content_icon, $content_file, $content_image, $content_parent, $content_comment, $content_rate, $content_pe, $content_refer, $content_datestamp, $content_class, $content_order, $level);

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
				return $parent;
		}


		function printParent($array, $level, $currentparent, $mode="option"){
				global $rs, $type, $type_id, $plugintable, $aa, $tp, $content_pref;
				$string = "";

				$content_pref = $this -> getContentPref($type_id);
				$content_cat_icon_path_large = $this -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
				$content_cat_icon_path_small = $this -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);

				if(empty($array)){ return FALSE; }
				 
				for($a=0;$a<count($array);$a++){
						//usort($array, create_function('$x,$y','return $x[16]==$y[16]?0:($x[16]<$y[16]?-1:1);'));
						if(!$array[$a][17] || $array[$a][17] == "0"){
							$pre = "";
							$class = "forumheader";
							$style = " font-weight:bold; ";
						}else{
							$pre = "";
							$class = "forumheader3";
							$style = " font-weight:normal; ";
							for($b=0;$b<$array[$a][17];$b++){
								$pre .= "_";
								//$pre .= "&nbsp;";
							}
						}
						
						if($mode == "array"){
							if(strpos($currentparent, ".")){
								$tmp = explode(".", $currentparent);
								$currentparent = $tmp[1];
							}
							//$string[] = array($array[$a][0], $pre.$array[$a][1], $array[$a][9], $array[$a][6]);
							$string[] = array($array[$a][0], $array[$a][1], $array[$a][9], $array[$a][6]);

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
							//print_r($array[$a]);
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
								<td class='".$class."' style='".$style." width:65%; white-space:nowrap;'>".$pre.$array[$a][1]." [".$array[$a][2]."]</td>
								<td class='".$class."' style='".$style." width:10%; text-align:left; white-space:nowrap;'>
									".$rs -> form_open("post", e_SELF."?".$type.".".$type_id.".cat.manage", "myform_{$array[$a][0]}","","", "")."
									<a href='".e_SELF."?".$type.".".$type_id.".cat.edit.".$array[$a][0]."'>".CONTENT_ICON_EDIT."</a>
									<a onclick=\"if(jsconfirm('".$tp->toJS(CONTENT_ADMIN_JS_LAN_0."\\n\\n[".CONTENT_ADMIN_JS_LAN_6." ".$array[$a][0]." : ".$delete_heading."]\\n\\n".CONTENT_ADMIN_JS_LAN_9."")."')){document.forms['myform_{$array[$a][0]}'].submit();}\" >".CONTENT_ICON_DELETE."</a>
									".($array[$a][9] == "0" ? "<a href='".e_SELF."?".$type.".".$type_id.".cat.options.".$array[$a][0]."'>".CONTENT_ICON_OPTIONS."</a>" : "")."
									".($array[$a][9] != "0" && getperms("0") ? "<a href='".e_SELF."?".$type.".".$type_id.".cat.contentmanager.".$array[$a][0]."'>".CONTENT_ICON_CONTENTMANAGER_SMALL."</a>" : "")."
									".$rs -> form_hidden("cat_delete_{$array[$a][0]}", "delete")."".$rs -> form_close()."
								</td>
							</tr>";
						}
				}
				return $string;
		}


		/*
		Array (
			[0] => Array ( [0] => 2 [1] => article [2] => 0 [3] => 2.2 )
			[1] => Array ( [0] => 24 [1] => recensies [2] => 0.2 [3] => 2.2.24 )
			[2] => Array ( [0] => 25 [1] => interviews [2] => 0.2 [3] => 2.2.25 )
			[3] => Array ( [0] => 95 [1] => interview sub 1 [2] => 0.2.25 [3] => 2.2.25.95 )
			[4] => Array ( [0] => 26 [1] => essays [2] => 0.2 [3] => 2.2.26 )
		)
		*/		
		function prefetchBreadCrumb($id, $mode="", $style=""){
				global $plugintable, $datequery, $type_id;
				$sqlprefetchbreadcrumb = "";

				if($id != ""){
					if($id == $type_id){		// || ($style == "admin" && !strpos($id, "."))
						$query = " content_id='".$id."' ";
					}else{
						$query = " content_parent='".$id."' ";
					}
					if(!is_object($sqlprefetchbreadcrumb)){ $sqlprefetchbreadcrumb = new db; }
					if(!$sqlprefetchbreadcrumb -> db_Select($plugintable, "content_id, content_heading, content_parent, content_order", " ".$query."  ")){
						$parent = FALSE;
					}else{
						while(list($parent_id, $parent_heading, $parent_parent, $parent_order) = $sqlprefetchbreadcrumb -> db_Fetch()){
							
							if($parent_parent == "0"){
								$cat = $type_id.".".$type_id;
								//$cat = ($style == "admin" ? $id.".".$id : $type_id.".".$type_id);
							}else{
								$cat = $type_id.".".substr($parent_parent,2).".".$parent_id;
								/*
								if($style == "admin"){
									//&& substr($id,0,2) == "0."){ $id = substr($id,2).".".substr($id,2); }
									$tmp = explode(".", $id);
									$cat = $tmp[1].".".$tmp[1].".".$tmp[2];
									echo $cat."<br />";
									
									echo $tmp[0]." - ".$tmp[1]." - ".$tmp[2]." - ".$tmp[3]."<br />";
								}else{
									$cat = $type_id.".".substr($parent_parent,2).".".$parent_id;
								}
								*/
							}
							$parent[] = array($parent_id, $parent_heading, $parent_parent, $cat, $parent_order);
							if($mode == "" || $mode == "0"){
								if($parent_parent == "0"){	//maincat
									$parentchild = $this -> prefetchBreadCrumb("0.".$parent_id, "", $style);
								}else{
									$parentchild = $this -> prefetchBreadCrumb($parent_parent.".".$parent_id, "", $style);
								}
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


		function drawBreadcrumb($prefetchbreadcrumb, $content_parent, $base="", $nolink=""){
				global $plugintable, $type_id, $content_pref;

				$sep = ($content_pref["content_breadcrumb_seperator{$type_id}"] ? $content_pref["content_breadcrumb_seperator{$type_id}"] : ">");

				if(strpos($content_parent, ".")){
					$checkid = $content_parent;
				}else{
					if($content_parent == $prefetchbreadcrumb[0][0]){	//is main cat
						$checkid = $prefetchbreadcrumb[0][3];
					}else{												//is sub cat
						for($i=0; $i<count($prefetchbreadcrumb); $i++){
							if($content_parent == $prefetchbreadcrumb[$i][0]){
								$checkid = $prefetchbreadcrumb[$i][3];
							}
						}
					}
				}
				for($i=0; $i<count($prefetchbreadcrumb); $i++){
					if($checkid == $prefetchbreadcrumb[$i][3]){
						$parentmatch = $prefetchbreadcrumb[$i][3];
					}
				}
				$tmp = explode(".", $parentmatch);
				$parentparentname = "";
				for($k=1; $k<count($tmp); $k++){
					for($i=0; $i<count($prefetchbreadcrumb); $i++){
						if($tmp[$k] == $prefetchbreadcrumb[$i][0]){
							if($nolink){
								$parentparentname .= $prefetchbreadcrumb[$i][1]." ".$sep." ";
							}else{
								$parentparentname .= "<a href='".e_SELF."?type.".$type_id.".cat.".$prefetchbreadcrumb[$i][0]."'>".$prefetchbreadcrumb[$i][1]."</a> ".$sep." ";
							}
						}
					}
				}
				if($base == "base"){
					if($nolink){
						$parentparentname = CONTENT_LAN_58." ".$sep." ".CONTENT_LAN_59." ".$sep." ".CONTENT_LAN_60." ".$sep." ".$parentparentname;
					}else{
						$parentparentname = "<a href='".e_BASE."'>".CONTENT_LAN_58."</a> ".$sep." <a href='".e_SELF."'>".CONTENT_LAN_59."</a> ".$sep." <a href='".e_SELF."?type.".$type_id."'>".CONTENT_LAN_60."</a> ".$sep." ".$parentparentname;
					}
				}
				$drawbreadcrumb = substr($parentparentname,0,-strlen($sep)-2);
				return $drawbreadcrumb;
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
				}elseif(substr($orderstring,6) == "order"){
					if($action == "cat"){
						$orderby = "SUBSTRING_INDEX(content_order, '.', 1)+0";
					}elseif($action != "cat"){
						$orderby = "SUBSTRING_INDEX(content_order, '.', -1)+0";
					}
					$orderby2 = ", content_heading ASC";
				}else{
					$orderstring = "orderddate";
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
				$checkquery = (getperms("0") ? "" : " AND FIND_IN_SET('".USERID."',content_pref) ");
				if(!$sql -> db_Select($plugintable, "content_id, content_heading, content_parent, content_class, content_pref as contentprefvalue", "LEFT(content_parent,2) = '0.' AND content_id = '".$tmp[0]."' ".$checkquery." ")){
					header("location:".e_SELF); exit;
				}else{
					return;
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
				$data .= "global ".chr(36)."plugintable, ".chr(36)."gen;\n";
				$data .= "require_once(e_PLUGIN.\"content/handlers/content_class.php\");\n";
				$data .= chr(36)."aa = new content;\n";
				$data .= "require_once(e_HANDLER.\"form_handler.php\");\n";
				$data .= chr(36)."rs = new form;\n\n";
				$data .= chr(36)."lan_file = e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content.php';\n";
				$data .= "include_once(file_exists(".chr(36)."lan_file) ? ".chr(36)."lan_file : e_PLUGIN.'content/languages/English/lan_content.php');\n\n";

				$data .= chr(36)."content_pref = ".chr(36)."aa -> getContentPref(\"$parentid\");\n";
				$data .= chr(36)."content_pref[\"content_cat_icon_path_small_{$parentid}\"] = (".chr(36)."content_pref[\"content_cat_icon_path_small_{$parentid}\"] ? ".chr(36)."content_pref[\"content_cat_icon_path_small_{$parentid}\"] : \"{e_PLUGIN}content/images/cat/16/\" );\n";
				$data .= chr(36)."content_cat_icon_path_small = ".chr(36)."aa -> parseContentPathVars(".chr(36)."content_pref[\"content_cat_icon_path_small_{$parentid}\"]);\n\n";

				$data .= chr(36)."content_pref[\"content_icon_path_{$parentid}\"] = (".chr(36)."content_pref[\"content_icon_path_{$parentid}\"] ? ".chr(36)."content_pref[\"content_icon_path_{$parentid}\"] : \"{e_PLUGIN}content/images/icon/\" );\n";
				$data .= chr(36)."content_icon_path = ".chr(36)."aa -> parseContentPathVars(".chr(36)."content_pref[\"content_icon_path_{$parentid}\"]);\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_viewicon_{$parentid}\"] == \"0\"){ ".chr(36)."viewicon = \"\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_viewicon_{$parentid}\"] == \"1\"){ ".chr(36)."viewicon = \"<img src='\".THEME.\"images/bullet2.gif'>\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_viewicon_{$parentid}\"] == \"2\"){ ".chr(36)."viewicon = \"&middot\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_viewicon_{$parentid}\"] == \"3\"){ ".chr(36)."viewicon = \"º\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_viewicon_{$parentid}\"] == \"4\"){ ".chr(36)."viewicon = \"&raquo;\";\n";
				$data .= "}else{ ".chr(36)."viewicon = \"<img src='\".THEME.\"images/bullet2.gif'>\"; }\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_cat_icon_{$parentid}\"] == \"0\"){ ".chr(36)."caticon = \"\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_{$parentid}\"] == \"1\"){ ".chr(36)."caticon = \"<img src='\".THEME.\"images/bullet2.gif'>\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_{$parentid}\"] == \"2\"){ ".chr(36)."caticon = \"&middot\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_{$parentid}\"] == \"3\"){ ".chr(36)."caticon = \"º\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_{$parentid}\"] == \"4\"){ ".chr(36)."caticon = \"&raquo;\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_{$parentid}\"] == \"5\"){ ".chr(36)."caticon = \"\";\n";
				$data .= "}else{ ".chr(36)."caticon = \"<img src='\".THEME.\"images/bullet2.gif'>\"; }\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_recent_icon_{$parentid}\"] == \"0\"){ ".chr(36)."recenticon = \"\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_{$parentid}\"] == \"1\"){ ".chr(36)."recenticon = \"<img src='\".THEME.\"images/bullet2.gif'>\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_{$parentid}\"] == \"2\"){ ".chr(36)."recenticon = \"&middot\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_{$parentid}\"] == \"3\"){ ".chr(36)."recenticon = \"º\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_{$parentid}\"] == \"4\"){ ".chr(36)."recenticon = \"&raquo;\";\n";
				$data .= "}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_{$parentid}\"] == \"5\"){ ".chr(36)."recenticon = \"\";\n";
				$data .= "}else{ ".chr(36)."caticon = \"<img src='\".THEME.\"images/bullet2.gif'>\"; }\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_recent_icon_{$parentid}\"] == \"5\"){\n";
				$data .= "   if(".chr(36)."content_pref[\"content_menu_recent_icon_width_{$parentid}\"]){\n";
				$data .= "      ".chr(36)."recenticonwidth = \" width:\".".chr(36)."content_pref[\"content_menu_recent_icon_width_{$parentid}\"].\"px; \";\n";
				$data .= "   }else{ ".chr(36)."recenticonwidth = \" width:50px; \"; }\n";
				$data .= "}else{ ".chr(36)."recenticonwidth = \"\"; }\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_search_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(36)."rs -> form_open(\"post\", e_PLUGIN.\"content/content.php?type.$parentid\", \"contentsearchmenu_{$parentid}\", \"\", \"enctype='multipart/form-data'\");\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<input class='tbox' size='20' type='text' id='searchfieldmenu_{$parentid}' name='searchfieldmenu_{$parentid}' value='".chr(34).".(isset(".chr(36)."_POST['searchfieldmenu_{$parentid}']) ? ".chr(36)."_POST['searchfieldmenu_{$parentid}'] : CONTENT_LAN_18).".chr(34)."' maxlength='100' onfocus=\\\"document.forms['contentsearchmenu_{$parentid}'].searchfieldmenu_{$parentid}.value='';\\\" />".chr(34).";\n";
				$data .= "   ".chr(36)."text .= ".chr(34)." <input class='button' type='submit' name='searchsubmit' value='\".CONTENT_LAN_19.\"' />".chr(34).";\n";
				$data .= "   ".chr(36)."text .= ".chr(36)."rs -> form_close().".chr(34)."<br />".chr(34).";\n";
				$data .= "}\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_sort_{$parentid}\"]){\n";
				$data .= "   if(eregi('content.php', e_SELF) && eregi('type.$parentid', e_QUERY)){\n";
				$data .= "      if(isset(".chr(36)."action) && ".chr(36)."action == \"content\"){\n";
				$data .= "      }elseif(isset(".chr(36)."action) && ".chr(36)."action == \"author\" && !".chr(36)."sub_action){\n";
				$data .= "      }elseif(isset(".chr(36)."action) && ".chr(36)."action == \"cat\" && !".chr(36)."sub_action){\n";
				$data .= "      }elseif(isset(".chr(36)."action) && ".chr(36)."action == \"top\"){\n";
				$data .= "      }else{\n";

				$data .= "         if(e_QUERY){\n";
				$data .= "            ".chr(36)."tmp = explode(\".\", e_QUERY);\n";
				$data .= "            ".chr(36)."type = (isset(".chr(36)."tmp[0]) ? ".chr(36)."tmp[0] : \"\");\n";
				$data .= "            ".chr(36)."type_id = (isset(".chr(36)."tmp[1]) ? ".chr(36)."tmp[1] : \"\");\n";
				$data .= "            ".chr(36)."action = (isset(".chr(36)."tmp[2]) ? ".chr(36)."tmp[2] : \"\");\n";
				$data .= "            ".chr(36)."sub_action = (isset(".chr(36)."tmp[3]) ? ".chr(36)."tmp[3] : \"\");\n";
				$data .= "            ".chr(36)."id = (isset(".chr(36)."tmp[4]) ? ".chr(36)."tmp[4] : \"\");\n";
				$data .= "            ".chr(36)."id2 = (isset(".chr(36)."tmp[5]) ? ".chr(36)."tmp[5] : \"\");\n";
				
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
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_73, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderaorder\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_option(CONTENT_LAN_74, 0, e_PLUGIN.\"content/content.php?type.$parentid\".".chr(36)."querystring.\".orderdorder\" );\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_select_close();\n";
				$data .= "         ".chr(36)."text .= ".chr(36)."rs -> form_close().".chr(34)."<br />".chr(34).";\n";
				$data .= "      }\n";
				$data .= "   }\n";
				$data .= "}\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_viewallcat_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(36)."viewicon.\" <a href='\".e_PLUGIN.\"content/content.php?type.$parentid.cat'>\".CONTENT_LAN_62.\"</a><br />".chr(34).";\n\n";
				$data .= "}\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_viewallauthor_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(36)."viewicon.\" <a href='\".e_PLUGIN.\"content/content.php?type.$parentid.author'>\".CONTENT_LAN_63.\"</a><br />".chr(34).";\n\n";
				$data .= "}\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_viewtoprated_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(36)."viewicon.\" <a href='\".e_PLUGIN.\"content/content.php?type.$parentid.top'>\".CONTENT_LAN_64.\"</a><br />".chr(34).";\n\n";
				$data .= "}\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_viewrecent_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(36)."viewicon.\" <a href='\".e_PLUGIN.\"content/content.php?type.$parentid'>\".CONTENT_LAN_61.\"</a><br />".chr(34).";\n\n";
				$data .= "}\n\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_viewsubmit_{$parentid}\"] && ".chr(36)."content_pref[\"content_submit_{$parentid}\"] && check_class(".chr(36)."content_pref[\"content_submit_class_{$parentid}\"])){\n";
				$data .= "   ".chr(36)."text .= ".chr(36)."viewicon.\" <a href='\".e_PLUGIN.\"content/content_submit.php?type.$parentid'>\".CONTENT_LAN_75.\"</a><br />".chr(34).";\n\n";
				$data .= "}\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_cat_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<br />".chr(34).";\n\n";
				$data .= "   ".chr(36)."parentdetails = ".chr(36)."aa -> getParent(\"\", \"\", $parentid, \"1\");\n";
				$data .= "   ".chr(36)."parentarray = ".chr(36)."aa -> printParent(".chr(36)."parentdetails, \"0\", $parentid, \"array\");\n";
				$data .= "   for(".chr(36)."i=0;".chr(36)."i<count(".chr(36)."parentarray);".chr(36)."i++){\n\n";
				$data .= "      ".chr(36)."n = ".chr(36)."aa -> countItemsInCat(".chr(36)."parentarray[".chr(36)."i][0], ".chr(36)."parentarray[".chr(36)."i][2], \"nolan\");\n";

				$data .= "      if(".chr(36)."content_pref[\"content_menu_cat_icon_{$parentid}\"] == \"5\"){\n";
				$data .= "         if(".chr(36)."parentarray[".chr(36)."i][3] != \"\" && file_exists(".chr(36)."content_cat_icon_path_small.".chr(36)."parentarray[".chr(36)."i][3]) ){\n";
				$data .= "            ".chr(36)."caticon = \"<img src='\".".chr(36)."content_cat_icon_path_small.".chr(36)."parentarray[".chr(36)."i][3].\"' alt='' style='border:0;'>\";\n";
				$data .= "         }else{\n";
				$data .= "            ".chr(36)."caticon = \"<img src='\".".chr(36)."content_icon_path.\"blank.gif' alt='' style='width:16px; border:0;'>\";\n";
				$data .= "         }\n";
				$data .= "      }\n";

				$data .= "      ".chr(36)."text .= ".chr(36)."caticon.\" <a href='\".e_PLUGIN.\"content/content.php?type.$parentid.cat.\".".chr(36)."parentarray[".chr(36)."i][0].\"'>\".".chr(36)."parentarray[".chr(36)."i][1].\"</a>\";\n";
				$data .= "      if(".chr(36)."content_pref[\"content_menu_cat_number_{$parentid}\"]){\n";
				$data .= "         ".chr(36)."text .= \" <span class='smalltext'>(\".".chr(36)."n.\")</span>\";\n";
				$data .= "      }\n";
				$data .= "      ".chr(36)."text .= ".chr(34)."<br />".chr(34).";\n";
				$data .= "   }\n";
				$data .= "}\n\n";

				$data .= "if(".chr(36)."content_pref[\"content_menu_recent_{$parentid}\"]){\n";
				$data .= "   ".chr(36)."text .= ".chr(34)."<br />".chr(34).";\n\n";
				$data .= "   ".chr(36)."gen = new convert;\n";
				$data .= "   if(!is_object(".chr(36)."sql)){ ".chr(36)."sql = new db; }\n";

				$data .= "   ".chr(36)."UnValidArticleIds2 = ".chr(36)."aa -> checkMainCat($parentid);\n";
				$data .= "   ".chr(36)."UnValidArticleIds2 = (".chr(36)."UnValidArticleIds2 == \"\" ? \"\" : \"AND \".substr(".chr(36)."UnValidArticleIds2, 0, -3) );\n";
				$data .= "   ".chr(36)."order = ".chr(36)."aa -> getOrder();\n";

				$data .= "   if(".chr(36)."sql -> db_Select(".chr(36)."plugintable, \"content_id, content_heading, content_subheading, content_author, content_icon, content_parent, content_datestamp, content_class\", \"content_refer !='sa' AND LEFT(content_parent,\".(strlen($parentid)).\") = '$parentid' \".".chr(36)."UnValidArticleIds2.\" AND (content_datestamp=0 || content_datestamp < \".time().\") AND (content_enddate=0 || content_enddate>\".time().\") AND content_class IN (".USERCLASS_LIST.") ORDER BY content_datestamp DESC LIMIT 0,\".".chr(36)."content_pref[\"content_menu_recent_number_{$parentid}\"].\" \")){\n\n";
		
				$data .= "      ".chr(36)."text .= (".chr(36)."content_pref[\"content_menu_recent_caption_{$parentid}\"] != \"\" ? ".chr(36)."content_pref[\"content_menu_recent_caption_{$parentid}\"] : \"recent items: ".$row['content_heading']."\").".chr(34)."<br />".chr(34).";\n";
				$data .= "      while(".chr(36)."row = ".chr(36)."sql -> db_Fetch()){\n";
				$data .= "      extract(".chr(36)."row);\n";

				$data .= "         ".chr(36)."datestamp = ereg_replace(\" -.*\", \"\", ".chr(36)."gen -> convert_date(".chr(36)."row['content_datestamp'], \"short\"));\n";
				$data .= "         ".chr(36)."authordetails = ".chr(36)."aa -> getAuthor(".chr(36)."row['content_author']);\n";

				$data .= "         if(".chr(36)."content_pref[\"content_menu_recent_subheading_{$parentid}\"] && ".chr(36)."row['content_subheading']){\n";
				$data .= "            if(".chr(36)."content_pref[\"content_menu_recent_subheading_char_{$parentid}\"] && ".chr(36)."content_pref[\"content_menu_recent_subheading_char_{$parentid}\"] != \"\" && ".chr(36)."content_pref[\"content_menu_recent_subheading_char_{$parentid}\"] != \"0\"){\n";
				$data .= "               if(strlen(".chr(36)."row['content_subheading']) > ".chr(36)."content_pref[\"content_menu_recent_subheading_char_{$parentid}\"]) {\n";
				$data .= "                  ".chr(36)."row['content_subheading'] = substr(".chr(36)."row['content_subheading'], 0, ".chr(36)."content_pref[\"content_menu_recent_subheading_char_{$parentid}\"]).".chr(36)."content_pref[\"content_menu_recent_subheading_post_{$parentid}\"];\n";
				$data .= "               }\n";
				$data .= "            }\n";
				$data .= "            ".chr(36)."subheading = (".chr(36)."row['content_subheading'] != \"\" && ".chr(36)."row['content_subheading'] != \" \" ? ".chr(36)."row['content_subheading'] : \"\");\n";
				$data .= "         }else{\n";
				$data .= "            ".chr(36)."subheading = (".chr(36)."row['content_subheading'] != \"\" && ".chr(36)."row['content_subheading'] != \" \" ? ".chr(36)."row['content_subheading'] : \"\");\n";
				$data .= "         }\n\n";

				$data .= "         if(".chr(36)."content_pref[\"content_menu_recent_icon_{$parentid}\"] == \"5\"){\n";
				$data .= "            if(".chr(36)."row['content_icon'] != \"\" && file_exists(".chr(36)."content_icon_path.".chr(36)."row['content_icon']) ){\n";
				$data .= "               ".chr(36)."recenticon = \"<img src='\".".chr(36)."content_icon_path.".chr(36)."row['content_icon'].\"' alt='' style=' \".".chr(36)."recenticonwidth.\" border:0;'>\";\n";
				$data .= "            }else{\n";
				$data .= "               ".chr(36)."recenticon = \"<img src='\".".chr(36)."content_icon_path.\"blank.gif' alt='' style='\".".chr(36)."recenticonwidth.\" border:0;'>\";\n";
				$data .= "            }\n";
				$data .= "         }\n\n";

				$data .= "         ".chr(36)."text .= \"<table style='border:0;'>\";\n";
				$data .= "         ".chr(36)."text .= \"<tr>\";\n";
				$data .= "         ".chr(36)."text .= \"<td style='width:1%; white-space:nowrap; vertical-align:top;'>\".".chr(36)."recenticon.\"</td>\";\n";
				$data .= "         ".chr(36)."text .= \"<td style='width:99%; vertical-align:top;'><a href='\".e_PLUGIN.\"content/content.php?type.$parentid.content.\".".chr(36)."content_id.\"'>\".".chr(36)."content_heading.\"</a><br />\".(".chr(36)."datestamp ? ".chr(36)."datestamp.\"<br />\" : \"\" ).(".chr(36)."authordetails[1] ? ".chr(36)."authordetails[1].\"<br />\" : \"\" ).(".chr(36)."subheading ? ".chr(36)."subheading.\"<br />\" : \"\" ).\"</td>\";\n";
				$data .= "         ".chr(36)."text .= \"</tr>\";\n";
				$data .= "         ".chr(36)."text .= \"</table><br />\";\n";

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


		/*
		function checkUnValidContent($query){
				global $plugintable, $datequery;
				$UnValidArticleIds = "";
				$checkid2 = FALSE;
				$sqlcheckunvalidcontent = "";

				if(!is_object($sqlcheckunvalidcontent)){ $sqlcheckunvalidcontent = new db; }
				if($sqlcheckunvalidcontent -> db_Select($plugintable, "content_id, content_parent, content_class", $query." ".$datequery." ")){
					$count = 0;
					while(list($content_id, $content_parent, $content_class) = $sqlcheckunvalidcontent -> db_Fetch()){
						if(!check_class($content_class)){
							$UnValidArticleIds .= " content_id != '".$content_id."' AND";
						}else{
							if($content_parent != "0"){
								$parentidarray[$count] = $content_parent;
							}
						}
						$count = $count + 1;
					}
					
					$result = array_unique($parentidarray);
					for($i=0;$i<count($result);$i++){
						$checkparent = $this -> checkParentValidity2($result[$i]);
						$UnValidArticleIds .= $checkparent;
					}

					$UnValidArticleIds = ($UnValidArticleIds == "" ? "" : " AND ".substr($UnValidArticleIds, 0, -3) );
				}
				return $UnValidArticleIds;
		}
		*/
		/*
		function checkParentValidity2($parent){
				global $plugintable, $datequery, $type_id;
				$UnValidArticleIds = "";
				$sqlcheckparentvalidity = "";

				if(strpos($parent, ".")){
					$tmp = explode(".", $parent);
					for($i=1;$i<count($tmp);$i++){
					}
				}
				if(!is_object($sqlcheckparentvalidity)){ $sqlcheckparentvalidity = new db; }
				if($sqlcheckparentvalidity -> db_Select($plugintable, "content_id, content_class", "content_id = '".$parent."' ".$datequery." " )){
					while(list($content_id, $content_class) = $sqlcheckparentvalidity -> db_Fetch()){
						if(!check_class($content_class)){
							$UnValidArticleIds .= " content_id != '".$content_id."' AND";
							$UnValidArticleIds .= " content_parent != '".$type_id.".".$type_id.".".$content_id."' AND";
							
						}
					}
				}
				if($sqlcheckparentvalidity -> db_Select($plugintable, "content_id, content_class", "content_parent = '".$parent."' ".$datequery." " )){
					while(list($content_id, $content_class) = $sqlcheckparentvalidity -> db_Fetch()){
						if(!check_class($content_class)){
							$UnValidArticleIds .= " content_id != '".$content_id."' AND";
							$UnValidArticleIds .= " content_parent != '".$type_id.".".$type_id.".".$content_id."' AND";
							
						}
					}
				}
				return $UnValidArticleIds;		
		}
		*/
		/*
		function checkItem($query){
				global $plugintable, $datequery, $type_id;
				//AND content_class IN (".USERCLASS_LIST.")
				if(!is_object($sqlcheckparentvalidity)){ $sqlcheckparentvalidity = new db; }
				if($sqlcheckparentvalidity -> db_Select($plugintable, "content_id, content_parent, content_class", " ".$query." ".$datequery." ORDER BY content_datestamp" )){
					while(list($content_id, $content_parent, $content_class) = $sqlcheckparentvalidity -> db_Fetch()){
						if(!check_class($content_class)){
							$UnValidArticleIds .= " content_parent != '".$type_id.".".$type_id.".".$content_id."' AND";
						}
						$UnValidArticleIds .= $this -> checkSubCat($id.".".$content_id);
					}
				}
				return $UnValidArticleIds;				
		}
		*/
		/*
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
		*/
		/*
		function getContent($id){
				global $plugintable, $datequery;
				$sqlgetcontent = "";

				if(!is_object($sqlgetcontent)){ $sqlgetcontent = new db; }
				if(!$sqlgetcontent -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class, content_pref as contentprefvalue", "content_id='".$id."' ".$datequery." AND content_class IN (".USERCLASS_LIST.")")){
					return FALSE;
				}else{
					$row = $sqlgetcontent -> db_Fetch();
					return $row;
				}
		}
		*/

}

?>