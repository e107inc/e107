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
|		$Revision: 1.58 $
|		$Date: 2005-06-13 11:08:55 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

global $plugindir, $plugintable, $datequery;
$plugindir		= e_PLUGIN."content/";
$plugintable	= "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
$datequery		= " AND (content_datestamp=0 || content_datestamp < ".time().") AND (content_enddate=0 || content_enddate>".time().") ";

require_once($plugindir."handlers/content_defines.php");

if(!is_object($sql)){ $sql = new db; }

class content{

		function ContentDefaultPrefs($id){
				global $tp;

				if(!$id){ $id="0"; }

				//ADMIN CREATE FORM
				$content_pref["content_admin_icon_{$id}"] = "0";						//should icon be available to add when creating an item
				$content_pref["content_admin_attach_{$id}"] = "0";						//should file be available to add when creating an item
				$content_pref["content_admin_images_{$id}"] = "0";						//should image be available to add when creating an item
				$content_pref["content_admin_comment_{$id}"] = "1";						//should comment be available to add when creating an item
				$content_pref["content_admin_rating_{$id}"] = "1";						//should rating be available to add when creating an item
				$content_pref["content_admin_score_{$id}"] = "1";						//should score be available to add when creating an item
				$content_pref["content_admin_pe_{$id}"] = "1";							//should printemailicons be available to add when creating an item
				$content_pref["content_admin_visibility_{$id}"] = "1";					//should visibility be available to add when creating an item
				$content_pref["content_admin_meta_{$id}"] = "0";						//should metatags be available to add when creating an item
				$content_pref["content_admin_custom_number_{$id}"] = "0";				//how many customtags should be available to add when creating an item
				$content_pref["content_admin_images_number_{$id}"] = "0";				//how many images should be available to add when creating an item
				$content_pref["content_admin_files_number_{$id}"] = "0";				//how many files should be available to add when creating an item
				$content_pref["content_admin_layout_{$id}"] = "0";						//should the option for choosing a layout template be shown
				$content_pref["content_admin_customtags_{$id}"] = "0";					//should options for adding additional data be shown
				$content_pref["content_admin_presettags_{$id}"] = "0";					//should preset data tags be shown

				//SUBMIT FORM
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
				$content_pref["content_submit_meta_{$id}"] = "0";						//should metatags be available to add when submitting an item
				$content_pref["content_submit_custom_number_{$id}"] = "0";				//how many customtags should be available to add when submitting an item
				$content_pref["content_submit_images_number_{$id}"] = "0";				//how many images should be available to add when submitting an item
				$content_pref["content_submit_files_number_{$id}"] = "0";				//how many files should be available to add when submitting an item
				$content_pref["content_submit_layout_{$id}"] = "0";						//should the option for choosing a layout template be shown
				$content_pref["content_submit_customtags_{$id}"] = "0";					//should options for adding additional data be shown
				$content_pref["content_submit_presettags_{$id}"] = "0";					//should preset data tags be shown

				//PATH THEME CSS
				$content_pref["content_cat_icon_path_large_{$id}"] = "{e_PLUGIN}content/images/cat/48/";	//default path to large categry icons
				$content_pref["content_cat_icon_path_small_{$id}"] = "{e_PLUGIN}content/images/cat/16/";	//default path to small category icons
				$content_pref["content_icon_path_{$id}"] = "{e_PLUGIN}content/images/icon/";				//default path to item icons
				$content_pref["content_image_path_{$id}"] = "{e_PLUGIN}content/images/image/";				//default path to item images
				$content_pref["content_file_path_{$id}"] = "{e_PLUGIN}content/images/file/";				//default path to item file attachments
				$content_pref["content_theme_{$id}"] = "default";											//choose theme for main parent
				$content_pref["content_layout_{$id}"] = "content_content_template.php";						//choose default layout scheme
				//$content_pref["content_css_{$id}"] = "ctemp";												//choose css stylesheet to use (theme, ctemp, ctempdef)

				//GENERAL
				$content_pref["content_log_{$id}"] = "0";								//activate log
				$content_pref["content_blank_icon_{$id}"] = "0";						//use blank icon if no icon present
				$content_pref["content_blank_caticon_{$id}"] = "0";						//use blank caticon if no caticon present

				//$content_pref["content_breadcrumb_{$id}"] = "0";						//show breadcrumb
				$content_pref["content_breadcrumb_catall_{$id}"] = "0";					//show breadcrumb on all categories page
				$content_pref["content_breadcrumb_cat_{$id}"] = "0";					//show breadcrumb on single category page
				$content_pref["content_breadcrumb_authorall_{$id}"] = "0";				//show breadcrumb on all author page
				$content_pref["content_breadcrumb_author_{$id}"] = "0";					//show breadcrumb on single author page
				$content_pref["content_breadcrumb_recent_{$id}"] = "0";					//show breadcrumb on recent page
				$content_pref["content_breadcrumb_item_{$id}"] = "0";					//show breadcrumb on content item page
				$content_pref["content_breadcrumb_top_{$id}"] = "0";					//show breadcrumb on top rated page
				$content_pref["content_breadcrumb_archive_{$id}"] = "0";				//show breadcrumb on archive page

				$content_pref["content_breadcrumb_seperator{$id}"] = ">";				//seperator character between breadcrumb
				$content_pref["content_breadcrumb_rendertype_{$id}"] = "2";				//how to render the breadcrumb

				$content_pref["content_navigator_catall_{$id}"] = "0";					//show navigator on all categories page
				$content_pref["content_navigator_cat_{$id}"] = "0";						//show navigator on single category page
				$content_pref["content_navigator_authorall_{$id}"] = "0";				//show navigator on all author page
				$content_pref["content_navigator_author_{$id}"] = "0";					//show navigator on single author page
				$content_pref["content_navigator_recent_{$id}"] = "0";					//show navigator on recent page
				$content_pref["content_navigator_item_{$id}"] = "0";					//show navigator on content item page
				$content_pref["content_navigator_top_{$id}"] = "0";						//show navigator on top rated page
				$content_pref["content_navigator_archive_{$id}"] = "0";					//show navigator on archive page
				$content_pref["content_search_catall_{$id}"] = "0";						//show search keyword on all categories page
				$content_pref["content_search_cat_{$id}"] = "0";						//show search keyword on single category page
				$content_pref["content_search_authorall_{$id}"] = "0";					//show search keyword on all author page
				$content_pref["content_search_author_{$id}"] = "0";						//show search keyword on single author page
				$content_pref["content_search_recent_{$id}"] = "0";						//show search keyword on recent page
				$content_pref["content_search_item_{$id}"] = "0";						//show search keyword on content item page
				$content_pref["content_search_top_{$id}"] = "0";						//show search keyword on top rated page
				$content_pref["content_search_archive_{$id}"] = "0";					//show search keyword on archive page
				$content_pref["content_ordering_catall_{$id}"] = "0";					//show ordering on all categories page
				$content_pref["content_ordering_cat_{$id}"] = "0";						//show ordering on single category page
				$content_pref["content_ordering_authorall_{$id}"] = "0";				//show ordering on all author page
				$content_pref["content_ordering_author_{$id}"] = "0";					//show ordering on single author page
				$content_pref["content_ordering_recent_{$id}"] = "0";					//show ordering on recent page
				$content_pref["content_ordering_item_{$id}"] = "0";						//show ordering on content item page
				$content_pref["content_ordering_top_{$id}"] = "0";						//show ordering on top rated page
				$content_pref["content_ordering_archive_{$id}"] = "0";					//show ordering on archive page

				//$content_pref["content_searchmenu_{$id}"] = "0";						//show searchmenu
				$content_pref["content_searchmenu_rendertype_{$id}"] = "1";				//rendertype for searchmenu (1=echo, 2=in seperate menu)
				$content_pref["content_nextprev_{$id}"] = "1";							//use nextprev buttons
				$content_pref["content_nextprev_number_{$id}"] = "10";					//how many items on a page
				$content_pref["content_defaultorder_{$id}"] = "orderddate";				//default sort and order method


				//CONTENT ITEM PREVIEW
				$content_pref["content_list_icon_{$id}"] = "0";							//show icon
				$content_pref["content_list_subheading_{$id}"] = "1";					//show subheading
				$content_pref["content_list_summary_{$id}"] = "1";						//show summary
				$content_pref["content_list_text_{$id}"] = "0";							//show (part of) text
				$content_pref["content_list_date_{$id}"] = "0";							//show date
				$content_pref["content_list_authorname_{$id}"] = "0";					//show authorname
				$content_pref["content_list_authorprofile_{$id}"] = "0";				//show link to author profile
				$content_pref["content_list_authoremail_{$id}"] = "0";					//show authoremail
				$content_pref["content_list_authoricon_{$id}"] = "0";					//show link to author list
				$content_pref["content_list_rating_{$id}"] = "1";						//show rating system
				$content_pref["content_list_peicon_{$id}"] = "1";						//show printemailicons
				$content_pref["content_list_parent_{$id}"] = "0";						//show parent cat
				$content_pref["content_list_refer_{$id}"] = "0";						//show refer count
				$content_pref["content_list_subheading_char_{$id}"] = "100";			//how many subheading characters
				$content_pref["content_list_subheading_post_{$id}"] = "[...]";			//use a postfix for too long subheadings
				$content_pref["content_list_summary_char_{$id}"] = "100";				//how many summary characters
				$content_pref["content_list_summary_post_{$id}"] = "[...]";				//use a postfix for too long summary
				$content_pref["content_list_text_char_{$id}"] = "60";					//how many text words
				$content_pref["content_list_text_post_{$id}"] = "[read more]";			//use a postfix for too long text
				$content_pref["content_list_text_link_{$id}"] = "1";					//show link to content item on postfix
				$content_pref["content_list_authoremail_nonmember_{$id}"] = "0";		//show email non member author
				$content_pref["content_list_peicon_all_{$id}"] = "0";					//override printemail icons
				$content_pref["content_list_rating_all_{$id}"] = "0";					//override rating system
				$content_pref["content_list_editicon_{$id}"] = "0";						//show link to admin edit item
				$content_pref["content_list_datestyle_{$id}"] = "%d %b %Y";				//choose datestyle for given date

				//CATEGORY PAGES
				//sections of content category in 'view all categories page'
				$content_pref["content_catall_icon_{$id}"] = "1";						//show icon
				$content_pref["content_catall_subheading_{$id}"] = "1";					//show subheading
				$content_pref["content_catall_text_{$id}"] = "0";						//show text
				$content_pref["content_catall_date_{$id}"] = "0";						//show date
				$content_pref["content_catall_rating_{$id}"] = "1";						//show rating
				$content_pref["content_catall_authorname_{$id}"] = "0";					//show author name
				$content_pref["content_catall_authoremail_{$id}"] = "0";				//show author email
				$content_pref["content_catall_authorprofile_{$id}"] = "0";				//show link to author profile
				$content_pref["content_catall_authoricon_{$id}"] = "0";					//show link to author list
				$content_pref["content_catall_peicon_{$id}"] = "1";						//show pe icons
				$content_pref["content_catall_comment_{$id}"] = "1";					//show amount of comments
				$content_pref["content_catall_amount_{$id}"] = "0";						//show amount of items
				$content_pref["content_catall_text_char_{$id}"] = "65";					//define amount of words of text to display
				$content_pref["content_catall_text_post_{$id}"] = "[read more]";		//define postfix is text is too long
				$content_pref["content_catall_text_link_{$id}"] = "1";					//define if link to category should be added on postfix

				//sections of content category in 'view category' page
				$content_pref["content_cat_icon_{$id}"] = "1";							//show icon
				$content_pref["content_cat_subheading_{$id}"] = "1";					//show subheading
				$content_pref["content_cat_text_{$id}"] = "0";							//show text
				$content_pref["content_cat_date_{$id}"] = "0";							//show date
				$content_pref["content_cat_authorname_{$id}"] = "0";					//show author name
				$content_pref["content_cat_authoremail_{$id}"] = "0";					//show author email
				$content_pref["content_cat_authorprofile_{$id}"] = "0";					//show link to author profile
				$content_pref["content_cat_authoricon_{$id}"] = "0";					//show link to author list
				$content_pref["content_cat_rating_{$id}"] = "1";						//show rating
				$content_pref["content_cat_peicon_{$id}"] = "1";						//show pe icons
				$content_pref["content_cat_comment_{$id}"] = "1";						//show amount of comments
				$content_pref["content_cat_amount_{$id}"] = "1";						//show amount of items

				//sections of subcategories in 'view category page'
				$content_pref["content_catsub_icon_{$id}"] = "1";						//show icon
				$content_pref["content_catsub_subheading_{$id}"] = "1";					//show subheading
				$content_pref["content_catsub_amount_{$id}"] = "1";						//show amount of items

				$content_pref["content_cat_showparent_{$id}"] = "1";					//show parent item in category page
				$content_pref["content_cat_showparentsub_{$id}"] = "1";					//show subcategories in category page
				$content_pref["content_cat_listtype_{$id}"] = "0";						//also show items from subategories
				$content_pref["content_cat_menuorder_{$id}"] = "1";						//order of parent and child items
				$content_pref["content_cat_rendertype_{$id}"] = "2";					//render method of the menus
				$content_pref["content_cat_text_char_{$id}"] = "65";					//define amount of words of text to display
				$content_pref["content_cat_text_post_{$id}"] = "[read more]";			//define postfix is text is too long
				$content_pref["content_cat_text_link_{$id}"] = "1";						//define if link to category should be added on postfix
				$content_pref["content_cat_authoremail_nonmember_{$id}"] = "0";			//define if the email of a non-member will be displayed
				$content_pref["content_cat_peicon_all_{$id}"] = "0";					//override printemail icons
				$content_pref["content_cat_rating_all_{$id}"] = "0";					//override rating system

				//CONTENT PAGE
				$content_pref["content_content_icon_{$id}"] = "0";						//show icon
				$content_pref["content_content_subheading_{$id}"] = "1";				//show subheading
				$content_pref["content_content_summary_{$id}"] = "1";					//show summary
				$content_pref["content_content_date_{$id}"] = "0";						//show date
				$content_pref["content_content_authorname_{$id}"] = "1";				//show authorname
				$content_pref["content_content_authorprofile_{$id}"] = "0";				//show link to author profile
				$content_pref["content_content_authoremail_{$id}"] = "0";				//show suthoremail
				$content_pref["content_content_authoricon_{$id}"] = "0";				//show link to author list
				$content_pref["content_content_parent_{$id}"] = "0";					//show parent category
				$content_pref["content_content_rating_{$id}"] = "1";					//show rating system
				$content_pref["content_content_peicon_{$id}"] = "1";					//show printemailicons
				$content_pref["content_content_refer_{$id}"] = "0";						//show refer count
				$content_pref["content_content_comment_{$id}"] = "0";					//show amount of comments
				$content_pref["content_content_authoremail_nonmember_{$id}"] = "0";		//show email non member
				$content_pref["content_content_peicon_all_{$id}"] = "0";				//override printemail icons
				$content_pref["content_content_rating_all_{$id}"] = "0";				//override rating system
				$content_pref["content_content_comment_all_{$id}"] = "0";				//override comment system				
				$content_pref["content_content_editicon_{$id}"] = "0";					//show link in content page to admin edit item
				$content_pref["content_content_customtags_{$id}"] = "0";				//should additional data be shown
				$content_pref["content_content_presettags_{$id}"] = "0";				//should preset data tags be shown
				$content_pref["content_content_attach_{$id}"] = "0";					//show attachments
				$content_pref["content_content_images_{$id}"] = "0";					//show images

				//AUTHOR PAGE
				$content_pref["content_author_lastitem_{$id}"] = "0";					//show last item reference
				$content_pref["content_author_amount_{$id}"] = "1";						//show amount of items from this author
				$content_pref["content_author_nextprev_{$id}"] = "1";					//use next prev buttons
				$content_pref["content_author_nextprev_number_{$id}"] = "20";			//amount of items per page

				//ARCHIVE PAGE
				$content_pref["content_archive_nextprev_{$id}"] = "1";					//archive : choose to show next/prev links
				$content_pref["content_archive_nextprev_number_{$id}"] = "30";			//archive : choose amount to use in next/prev
				$content_pref["content_archive_letterindex_{$id}"] = "0";				//archive : letter index
				$content_pref["content_archive_datestyle_{$id}"] = "%d %b %Y";			//archive : choose datestyle for given date
				$content_pref["content_archive_date_{$id}"] = "1";						//archive : section: show date
				$content_pref["content_archive_authorname_{$id}"] = "0";				//archive : section: show authorname
				$content_pref["content_archive_authorprofile_{$id}"] = "0";				//archive : section: show link to author profile
				$content_pref["content_archive_authoricon_{$id}"] = "0";				//archive : section: show link to author list
				$content_pref["content_archive_authoremail_{$id}"] = "0";				//archive : section: show author email
				$content_pref["content_archive_authoremail_nonmember_{$id}"] = "0";		//archive : show link to email of non-member author

				//TOP RATED PAGE
				$content_pref["content_top_icon_{$id}"] = "0";							//top : section: show icon
				$content_pref["content_top_authorname_{$id}"] = "0";					//top : section: show authorname
				$content_pref["content_top_authorprofile_{$id}"] = "0";					//top : section: show link to author profile
				$content_pref["content_top_authoricon_{$id}"] = "0";					//top : section: show link to author list
				$content_pref["content_top_authoremail_{$id}"] = "0";					//top : section: show author email
				$content_pref["content_top_authoremail_nonmember_{$id}"] = "0";			//top : show link to email of non-member author

				//CONTENT MANAGER
				$content_pref["content_manager_class_{$id}"] = "0";						//contentmanager: class to narrow down the userlist

				//MENU OPTIONS
				$content_pref["content_menu_caption_{$id}"] = CONTENT_MENU_LAN_0;		//caption of menu
				$content_pref["content_menu_search_{$id}"] = "0";						//show search keyword
				$content_pref["content_menu_sort_{$id}"] = "0";							//show sorting methods

				$content_pref["content_menu_links_{$id}"] = "1";						//show content links
				$content_pref["content_menu_links_dropdown_{$id}"] = "0";				//rendertype of content links (in dropdown or as normal links)
				$content_pref["content_menu_links_icon_{$id}"] = "0";					//define icon for content links (only with normallinks)
				$content_pref["content_menu_links_caption_{$id}"] = CONTENT_MENU_LAN_4;	//define caption for link list (only is normallinks is selected)
				$content_pref["content_menu_viewallcat_{$id}"] = "1";					//menu: view link to all categories
				$content_pref["content_menu_viewallauthor_{$id}"] = "1";				//menu: view link to all authors
				$content_pref["content_menu_viewallitems_{$id}"] = "1";					//menu: view link to all items (archive)
				$content_pref["content_menu_viewtoprated_{$id}"] = "1";					//menu: view link to top rated items
				$content_pref["content_menu_viewrecent_{$id}"] = "1";					//menu: view link to recent items
				$content_pref["content_menu_viewsubmit_{$id}"] = "0";					//view link to submit content item (only if it is allowed)
				$content_pref["content_menu_viewicon_{$id}"] = "0";						//choose icon to display for links

				$content_pref["content_menu_cat_{$id}"] = "1";							//view categories
				$content_pref["content_menu_cat_main_{$id}"] = "1";						//show main parent in the category list				
				$content_pref["content_menu_cat_number_{$id}"] = "1";					//show number of items in category				
				$content_pref["content_menu_cat_icon_{$id}"] = "0";						//choose icon to display for categories
				$content_pref["content_menu_cat_icon_default_{$id}"] = "0";				//choose default icon is no icon present (only if category_icon is selected)
				$content_pref["content_menu_cat_caption_{$id}"] = CONTENT_MENU_LAN_3;	//define caption for category list
				$content_pref["content_menu_cat_dropdown_{$id}"] = "0";					//rendertype of categories (in dropdown or as normal links)

				$content_pref["content_menu_recent_{$id}"] = "1";						//view recent list
				$content_pref["content_menu_recent_caption_{$id}"] = CONTENT_MENU_LAN_2;	//caption of recent list
				$content_pref["content_menu_recent_number_{$id}"] = "5";				//number of recent items to show
				$content_pref["content_menu_recent_date_{$id}"] = "0";					//show date in recent list
				$content_pref["content_menu_recent_datestyle_{$id}"] = "%d %b %Y";		//choose datestyle for given date
				$content_pref["content_menu_recent_author_{$id}"] = "0";				//show author in recent list
				$content_pref["content_menu_recent_subheading_{$id}"] = "0";			//show subheading in recent list
				$content_pref["content_menu_recent_subheading_char_{$id}"] = "80";		//number of characters of subheading to show
				$content_pref["content_menu_recent_subheading_post_{$id}"] = "[...]";	//postfix for too long subheadings
				$content_pref["content_menu_recent_icon_{$id}"] = "0";					//choose icon to display for recent items
				$content_pref["content_menu_recent_icon_width_{$id}"] = "50";			//specify width of icon (only if content_icon is set)

				return $content_pref;
		}

		function getContentPref($id="") {
				global $sql, $plugintable, $tp, $eArrayStorage;

				$plugintable = "pcontent";

				if($id && $id!="0"){	//if $id; use prefs from content table
							$num_rows = $sql -> db_Select($plugintable, "content_pref", "content_id='$id' ");
							$row = $sql -> db_Fetch();

							if (empty($row['content_pref'])) {
								$content_pref = $this -> ContentDefaultPrefs($id);
								$tmp = $eArrayStorage->WriteArray($content_pref);
								$sql -> db_Update($plugintable, "content_pref='{$tmp}' WHERE content_id='$id' ");
								$sql -> db_Select($plugintable, "content_pref", "content_id='$id' ");
								$row = $sql -> db_Fetch();
							}
							$content_pref = $eArrayStorage->ReadArray($row['content_pref']);

				}else{		//if not $id; use prefs from default core table
							$num_rows = $sql -> db_Select("core", "*", "e107_name='$plugintable' ");

							if ($num_rows == 0) {
								$content_pref = $this -> ContentDefaultPrefs("0");
								$tmp = $eArrayStorage->WriteArray($content_pref);
								$sql -> db_Insert("core", "'$plugintable', '{$tmp}' ");
								$sql -> db_Select("core", "*", "e107_name='$plugintable' ");
							}
							$row = $sql -> db_Fetch();
							$content_pref = $eArrayStorage->ReadArray($row['e107_value']);
				}

				return $content_pref;
		}

		//admin
		function UpdateContentPref($_POST, $id){
				global $plugintable, $sql, $tp, $eArrayStorage;

				if(!is_object($sql)){ $sql = new db; }

				//insert default preferences into core
				if($id == "0"){
					$num_rows = $sql -> db_Select("core", "*", "e107_name='$plugintable' ");
					if ($num_rows == 0) {
						$sql -> db_Insert("core", "'$plugintable', '{$tmp}' ");
					}else{
						$row = $sql -> db_Fetch();

						//get current preferences
						$content_pref = $eArrayStorage->ReadArray($row['e107_value']);

						//assign new preferences
						foreach($_POST as $k => $v){
							if(preg_match("#^content_#",$k)){
								$content_pref[$k] = $tp->toDB($v, true);
							}
						}

						//create new array of preferences
						$tmp = $eArrayStorage->WriteArray($content_pref);

						$sql -> db_Update("core", "e107_value = '{$tmp}' WHERE e107_name = '$plugintable' ");
					}

				//insert category preferences into plugintable
				}else{
					$sql -> db_Select($plugintable, "content_pref", "content_id='$id' ");
					$row = $sql -> db_Fetch();

					//get current preferences
					//$content_pref = $eArrayStorage->ReadArray($row['content_pref']);

					//assign new preferences
					foreach($_POST as $k => $v){
						if(preg_match("#^content_#",$k)){
							$content_pref[$k] = $tp->toDB($v, true);
						}
					}

					//create new array of preferences
					$tmp = $eArrayStorage->WriteArray($content_pref);

					$sql -> db_Update($plugintable, "content_pref='{$tmp}' WHERE content_id='$id' ");
				}

				return $content_pref;
		}

		function CONTENTREGEXP($var){
			return "(^|,)(".str_replace(",", "|", $var).")(,|$)";
		}

		function getCategoryTree($id, $parent, $classcheck=TRUE){
				//id	:	content_parent of an item
				global $plugintable, $datequery;
				global $agc;

				if($parent){
					$agc = "";
					$qrygc = " content_id = '".$parent."' ";
				}else{
					$qrygc = " content_parent = '0' ";
				}
				if($id){
					$qrygc = " content_parent = '0.".$id."' ";
				}

				if($classcheck == TRUE){
					$qrygc .= " AND content_class REGEXP '".e_CLASS_REGEXP."' ";
				}

				$sqlgetcat = new db;
				if($sqlgetcat -> db_Select($plugintable, "content_id, content_heading, content_parent", " ".$qrygc." ".$datequery." " )){
					while($row = $sqlgetcat -> db_Fetch()){

						if($agc){
							if($row['content_parent'] != "0"){
								if(array_key_exists(substr($row['content_parent'],2), $agc)){
									if(is_array($agc[substr($row['content_parent'],2)])){
										$agc[$row['content_id']] = array_merge_recursive($agc[substr($row['content_parent'],2)], array($row['content_id'], $row['content_heading']));
									}else{
										$agc[$row['content_id']] = array($agc[substr($row['content_parent'],2)], array($row['content_id'], $row['content_heading']));
									}

								}else{
									$agc[$row['content_id']] = array($row['content_id'], $row['content_heading']);
								}
							}else{
								$agc[$row['content_id']] = array($row['content_id'], $row['content_heading']);
							}
						}else{
							$agc[$row['content_id']] = array($row['content_id'], $row['content_heading']);
						}

						$this -> getCategoryTree($row['content_id'], "", $classcheck);
					}
				}
				return $agc;
		}

		function getCrumbItem($id, $arr){
			//$id	:	content_parent of item
			//$arr	:	array of all categories
			$crumb = "";
			if(array_key_exists($id, $arr)){
				for($i=0;$i<count($arr[$id]);$i++){
					$crumb .= "<a href='".e_SELF."?cat.".$arr[$id][$i]."'>".$arr[$id][$i+1]."</a> > ";
					$i++;
				}
				$crumb = substr($crumb,0,-3);
			}
			return $crumb;
		}

		function getCrumbPage($arr, $parent){
			global $qs, $content_pref, $mainparent;

			if(array_key_exists($parent, $arr)){
				$sep = (isset($content_pref["content_breadcrumb_seperator_{$mainparent}"]) ? $content_pref["content_breadcrumb_seperator_{$mainparent}"] : ">");
				$crumb = "<a href='".e_BASE."'>".CONTENT_LAN_58."</a> ".$sep." <a href='".e_SELF."'>".CONTENT_LAN_59."</a>";
				for($i=0;$i<count($arr[$parent]);$i++){
					$crumb .= " ".$sep." <a href='".e_SELF."?cat.".$arr[$parent][$i]."'>".$arr[$parent][$i+1]."</a>";
					$i++;
				}
			}
			if($qs[0] == "recent"){
				$crumb .= " ".$sep." <a href='".e_SELF."?recent.".$arr[$parent][0]."'>".CONTENT_LAN_60."</a>";
			}
			if($qs[0] == "author"){
				$crumb .= " ".$sep." <a href='".e_SELF."?author.list.".$arr[$parent][0]."'>".CONTENT_LAN_85."</a>";
			}
			if($qs[0] == "list"){
				$crumb .= " ".$sep." <a href='".e_SELF."?list.".$arr[$parent][0]."'>list</a>";
			}
			if($qs[0] == "top"){
				$crumb .= " ".$sep." <a href='".e_SELF."?top.".$arr[$parent][0]."'>".CONTENT_LAN_8."</a>";
			}
			return $crumb."<br /><br />";
		}

		function countCatItems($id){
			global $sqlcountitemsincat, $plugintable, $datequery;
			//$id	:	category content_id

			if(!is_object($sqlcountitemsincat)){ $sqlcountitemsincat = new db; }
			$n = $sqlcountitemsincat -> db_Count($plugintable, "(*)", "WHERE content_class REGEXP '".e_CLASS_REGEXP."' AND content_parent='".$id."' AND content_refer != 'sa' ".$datequery." ");

			return $n;
		}

		function setPageTitle(){
			global $plugintable, $sql;

			//content page
			if(e_PAGE == "content.php"){
				//main parent overview
				if(!e_QUERY){
					$page = CONTENT_PAGETITLE_LAN_0;
				}else{
					$qs = explode(".", e_QUERY);
					$sql -> db_Select($plugintable, "content_heading", "content_id = '".$qs[1]."' ");
					$row = $sql -> db_Fetch();

					//$page = CONTENT_PAGETITLE_LAN_0." / ".$row['content_heading'];
					$page = CONTENT_PAGETITLE_LAN_0;

					//recent of parent='2'
					if($qs[0] == "recent" && is_numeric($qs[1]) && !isset($qs[2])){
						$page .= " / ".$row['content_heading']." / ".CONTENT_PAGETITLE_LAN_2;

					//item
					}elseif($qs[0] == "content" && isset($qs[1]) && is_numeric($qs[1]) ){
						$sql -> db_Select($plugintable, "content_heading", "content_id='".$qs[1]."' ");
						$row2 = $sql -> db_Fetch();
						$page .= " / ".$row2['content_heading'];

					//all categories of parent='2'
					}elseif($qs[0] == "cat" && $qs[1] == "list" && is_numeric($qs[2])){
						$sql -> db_Select($plugintable, "content_heading", "content_id='".$qs[2]."' ");
						$row2 = $sql -> db_Fetch();
						$page .= " / ".$row2['content_heading']." / ".CONTENT_PAGETITLE_LAN_13;

					//category of parent='2' and content_id='5'
					}elseif($qs[0] == "cat" && is_numeric($qs[1]) && !isset($qs[2])){
						$page .= " / ".CONTENT_PAGETITLE_LAN_3." / ".$row['content_heading'];

					//top rated of parent='2'
					}elseif($qs[0] == "top" && is_numeric($qs[1]) && !isset($qs[2])){
						$sql -> db_Select($plugintable, "content_heading", "content_id='".$qs[1]."' ");
						$row2 = $sql -> db_Fetch();
						$page .= " / ".$row2['content_heading']." / ".CONTENT_PAGETITLE_LAN_4;

					//authorlist of parent='2'
					}elseif($qs[0] == "author" && $qs[1] == "list" && is_numeric($qs[2])){
						$sql -> db_Select($plugintable, "content_heading", "content_id='".$qs[2]."' ");
						$row2 = $sql -> db_Fetch();
						$page .= " / ".$row2['content_heading']." / ".CONTENT_PAGETITLE_LAN_14;

					//authorlist of parent='2' and content_id='5'
					}elseif($qs[0] == "author" && is_numeric($qs[1]) && !isset($qs[2])){
						$sql -> db_Select($plugintable, "content_author", "content_id='".$qs[1]."' ");
						$row2 = $sql -> db_Fetch();
						$authordetails = $this -> getAuthor($row2['content_author']);
						$page .= " / ".CONTENT_PAGETITLE_LAN_5." / ".$authordetails[1];

					//archive of parent='2'
					}elseif($qs[0] == "list" && is_numeric($qs[1]) && !isset($qs[2])){
						$page .= " / ".CONTENT_PAGETITLE_LAN_6;
					}
				}

			}elseif(e_PAGE == "content_submit.php"){
				//submit page : view categories
				if(!e_QUERY){
					$page = CONTENT_PAGETITLE_LAN_0." / ".CONTENT_PAGETITLE_LAN_7;

				}else{
					$qs = explode(".", e_QUERY);
					$page = CONTENT_PAGETITLE_LAN_0;

					//submit page : submit item
					if($qs[0] == "content" && $qs[1] == "submit" && is_numeric($qs[2]) ){
						$page = " / ".CONTENT_PAGETITLE_LAN_8;
					}
				}

			}elseif(e_PAGE == "content_manager.php"){
				//manager page : view categories
				if(!e_QUERY){
					$page = CONTENT_PAGETITLE_LAN_0." / ".CONTENT_PAGETITLE_LAN_9;

				}else{
					$qs = explode(".", e_QUERY);
					$page = CONTENT_PAGETITLE_LAN_0." / ".CONTENT_PAGETITLE_LAN_9;

					//manager page : view items
					if($qs[0] == "content" && is_numeric($qs[1]) ){
						$page .= " / ".CONTENT_PAGETITLE_LAN_10;

					//manager page : edit item
					}elseif($qs[0] == "content" && $qs[1] == "edit" && is_numeric($qs[2]) ){
						$page .= " / ".CONTENT_PAGETITLE_LAN_11;

					//manager page : create new item
					}elseif($qs[0] == "content" && $qs[1] == "create" && is_numeric($qs[2]) ){
						$page .= " / ".CONTENT_PAGETITLE_LAN_12;
					}
				}

			}
			define("e_PAGETITLE", strtolower($page));

		}


		/*
		function setContentCss($id="", $main=""){
			//$id : category id
			//$main : main category parent id
			//only use one of the two values, if mainid is given, that will be used
			global $sql, $plugintable, $plugindir;

			if($main){
				$cssmainparent		= $main;

			}elseif($id){
				$cssmainparent		= $this -> getMainParent($id);

			}elseif(!$main && !$id){
				if(e_QUERY){
					$cssqs			= explode(".", e_QUERY);

					if(is_numeric($cssqs[0])){
						$cssfrom	= array_shift($cssqs);
					}
					$cssmainid		= (is_numeric($cssqs[1]) ? $cssqs[1] : $cssqs[2]);
					$cssmainparent	= $this -> getMainParent($cssmainid);
				}else{
					$cssmainparent	= "";
				}
			}
			$csspref				= $this -> getContentPref($cssmainparent);

			//use content_css from THEME
			if($csspref["content_css_{$cssmainparent}"] == "theme"){
				if(file_exists(THEME."content_css.css")){						
					$eplug_css = THEME."content_css.css";
				}else{
					$eplug_css = $plugindir."templates/default/content_css.css";
				}

			//use content_css from CURRENT CONTENT THEME
			}elseif($csspref["content_css_{$cssmainparent}"] == "ctemp"){
				if(file_exists($plugindir."templates/".$csspref["content_theme_{$cssmainparent}"]."/content_css.css")){
					$eplug_css = $plugindir."templates/".$csspref["content_theme_{$cssmainparent}"]."/content_css.css";
				}else{
					$eplug_css = $plugindir."templates/default/content_css.css";
				}

			//use content_css from DEFAULT CONTENT THEME
			//}elseif($csspref["content_css_{$cssmainparent}"] == "ctempdef"){
			}else{
				$eplug_css = $plugindir."templates/default/content_css.css";
			}

			return $eplug_css;
		}
		*/


		function getAuthor($content_author) {
				global $sql, $plugintable, $datequery;

				if(is_numeric($content_author)){
					if(!$sql -> db_Select("user", "user_id, user_name, user_email", "user_id=$content_author")){
						$author_id = "0";
						$author_name = "";
						$author_email = "";
						//$author_id = USERID;
						//$author_name = USERNAME;
						//$author_email = USEREMAIL;
					}else{
						list($author_id, $author_name, $author_email) = $sql -> db_Fetch();
					}
					$getauthor = array($author_id, $author_name, $author_email, $content_author);
				}else{
					$tmp = explode("^", $content_author);
					$author_id = $tmp[0];
					$author_name = $tmp[1];
					$author_email = $tmp[2];
					$getauthor = array($author_id, $author_name, $author_email, $content_author);
				}
				return $getauthor;
		}

		function getMainParent($id){
			global $sql, $plugintable;

			$category_total = $sql -> db_Select($plugintable, "content_id, content_parent", "content_id='".$id."' ");
			$row = $sql -> db_Fetch();
			if($row['content_parent'] == 0){
				$mainparent = $row['content_id'];
			}else{
				if(strpos($row['content_parent'], ".")){
					$newid = substr($row['content_parent'],2);
				}else{
					$newid = $row['content_parent'];
				}
				$mainparent = $this -> getMainParent( $newid );
			}
			return ($mainparent ? $mainparent : "0");
		}

		//admin
		function ShowOptionCat($currentparent=""){
				global $qs, $sql, $rs, $plugintable, $tp, $content_pref, $stylespacer;
				$string = "";

				if($currentparent == "submit"){
					$mainparent		= $this -> getMainParent( $qs[2] );
					$catarray		= $this -> getCategoryTree("", $mainparent, FALSE);
				}else{
					$catarray		= $this -> getCategoryTree("", "", FALSE);
				}
				$array = array_keys($catarray);

				foreach($array as $catid){
					$category_total = $sql -> db_Select($plugintable, "content_id, content_heading, content_parent", "content_id='".$catid."' ");
					$row = $sql -> db_Fetch();

					$pre = "";
					if($row['content_parent'] == "0"){		//main parent level
					}else{									//sub level
						for($b=0;$b<(count($catarray[$catid])/2)-1;$b++){
							$pre .= "_";
						}
					}
					$emptystring = "----------------";

					if($qs[0] == "cat"){

						if($qs[1] == "create"){
							$checkid	= (isset($qs[2]) && is_numeric($qs[2]) ? $qs[2] : "");
							$selectjs	= "if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }";
							$label		= $catid;
							$sel		= ($catid == $checkid ? "1" : "0");
							$value		= e_SELF."?cat.create.".$catid;
							$catstring	= "";
							if($row['content_parent'] == 0){
								$name	= strtoupper($row['content_heading']);
								$js		= "style='font-weight:bold;'";
								//$string	.= $rs -> form_option($emptystring, "0", "none", "label='none'");
							}else{
								$name	= $pre.$row['content_heading'];
								$js		= "";
							}

						}elseif($qs[1] == "edit"){
							$checkid	= ($currentparent ? $currentparent : "");
							$selectjs	= "if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }";
							$label		= $catid;
							$sel		= ($catid == $checkid ? "1" : "0");
							$value		= e_SELF."?cat.edit.".$qs[2].".".$catid;
							$catstring	= "";
							if($row['content_parent'] == 0){
								$name	= strtoupper($row['content_heading']);
								$js		= "style='font-weight:bold;'";
								//$string	.= $rs -> form_option($emptystring, "0", "none", "label='none'");
							}else{
								$name	= $pre.$row['content_heading'];
								$js		= "";
							}
						}
					//manage items
					}elseif($qs[0] == "" || $qs[0] == "content"){

							if($qs[1] == "create" || $qs[1] == "submit"){
								$checkid	= (isset($qs[2]) && is_numeric($qs[2]) ? $qs[2] : "");
								$sel		= ($catid == $checkid ? "1" : "0");
								$selectjs	= "if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }";
								$label		= $catid;
								$catstring	= "";
								$value		= e_SELF."?content.".$qs[1].".".$catid;
								if($row['content_parent'] == 0){
									$name	= strtoupper($row['content_heading']);
									$js		= "style='font-weight:bold;'";
									//$string	.= $rs -> form_option($emptystring, "0", "none", "label='none'");

								}else{
									$name	= $pre.$row['content_heading'];
									$js		= "";
								}

							}else{
								$checkid	= ($currentparent ? $currentparent : "");
								$selectjs	= "if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }";
								$label		= $catid;
								$sel		= ($catid == $checkid ? "1" : "0");
								if($qs[1] == "" || is_numeric($qs[1])){
									$value	= e_SELF."?content.".$catid;
								}else{
									$value	= e_SELF."?content.".$qs[1].".".$qs[2].".".$catid;
								}
								$catstring	= "";
								if($row['content_parent'] == 0){
									$name	= strtoupper($row['content_heading']);
									$js		= "style='font-weight:bold;'";
									//$string	.= $rs -> form_option($emptystring, "0", "none", "label='none'");
								}else{
									$name	= $pre.$row['content_heading'];
									$js		= "";
								}
							}
					}
					$string	.= $rs -> form_option($name, $sel, $value, ($label ? "label='".$label."'" : "label='none'")." ".$js ).$catstring;

				}
				$selectjs	= " onchange=\" document.getElementById('parent').value=this.options[this.selectedIndex].label; ".$selectjs." \"";
				$text		= $rs -> form_select_open("parent1", $selectjs);

				if(!isset($qs[0])){
					$text .= $rs -> form_option("choose category ...", "0", "none", "label='none'");
				}elseif( $qs[0] == "content" && $qs[1] == "edit" && is_numeric($qs[2]) ){
					$text .= $rs -> form_option("choose category ...", "0", "none", "label='none'");
				}elseif( $qs[0] == "content" && ($qs[1] == "create" || $qs[1] == "submit") ){
					$text .= $rs -> form_option("choose category ...", "0", "none", "label='none'");
				}elseif( $qs[0] == "content" && is_numeric($qs[1]) ){
					$text .= $rs -> form_option("choose category ...", "0", "none", "label='none'");
				}elseif($qs[0] == "cat" && $qs[1] == "create"){
					$text .= $rs -> form_option("NEW MAIN CATEGORY", (isset($qs[2]) ? "0" : "1"), e_SELF."?cat.create", "label='0' style='font-weight:bold;'");
				}else{
					$text .= $rs -> form_option("NEW MAIN CATEGORY", (isset($qs[2]) ? "0" : "1"), e_SELF."?cat.edit.".$qs[2].".0", "label='0' style='font-weight:bold;'");
				}

				$text .= $string;
				$text .= $rs -> form_select_close();

				return $text;
		}


		function getOrder(){
				global $qs, $content_pref;

				if(isset($qs[0]) && substr($qs[0],0,5) == "order"){
					$orderstring	= $qs[0];
				}elseif(isset($qs[1]) && substr($qs[1],0,5) == "order"){
					$orderstring	= $qs[1];
				}elseif(isset($qs[2]) && substr($qs[2],0,5) == "order"){
					$orderstring	= $qs[2];
				}elseif(isset($qs[3]) && substr($qs[3],0,5) == "order"){
					$orderstring	= $qs[3];
				}else{
					$checkmi		= (is_numeric($qs[1]) ? $qs[1] : $qs[2]);
					$checkmp		= $this -> getMainParent($checkmi);
					$orderstring	= ($content_pref["content_defaultorder_{$checkmp}"] ? $content_pref["content_defaultorder_{$checkmp}"] : "orderddate" );
				}

				if(substr($orderstring,6) == "heading"){
					$orderby		= "content_heading";
					$orderby2		= "";
				}elseif(substr($orderstring,6) == "date"){
					$orderby		= "content_datestamp";
					$orderby2		= ", content_heading ASC";
				}elseif(substr($orderstring,6) == "parent"){
					$orderby		= "content_parent";
					$orderby2		= ", content_heading ASC";
				}elseif(substr($orderstring,6) == "refer"){
					$orderby		= "content_refer";
					$orderby2		= ", content_heading ASC";
				}elseif(substr($orderstring,6) == "author"){

				}elseif(substr($orderstring,6) == "order"){
					if($qs[0] == "cat"){
						$orderby	= "SUBSTRING_INDEX(content_order, '.', 1)+0";
					}elseif($qs[0] != "cat"){
						$orderby	= "SUBSTRING_INDEX(content_order, '.', -1)+0";
					}
					$orderby2		= ", content_heading ASC";
				}else{
					$orderstring	= "orderddate";
					$orderby		= "content_datestamp";
					$orderby2		= ", content_heading ASC";
				}
				$order = " ORDER BY ".$orderby." ".(substr($orderstring,5,1) == "a" ? "ASC" : "DESC")." ".$orderby2." ";

				return $order;
		}


		function getIcon($mode, $icon, $path="", $linkid="", $width="", $blank=""){
				global $content_cat_icon_path_small, $content_cat_icon_path_large, $content_icon_path, $content_pref;

				$blank			= (!$blank ? "0" : $blank);
				$border			= "border:0;";
				
				if($mode == "item"){
					$path		= (!$path ? $content_icon_path : $path);
					$hrefpre	= ($linkid ? "<a href='".e_SELF."?".$linkid."'>" : "");
					$hrefpost	= ($linkid ? "</a>" : "");
					$width		= ($width ? "width:".$width."px;" : "");
					$border		= "border:1px solid #000;";
					$icon		= ($icon ? $path.$icon : ($blank ? $content_icon_path."blank.gif" : ""));

				}elseif($mode == "catsmall"){
					$path		= (!$path ? $content_cat_icon_path_small : $path);
					$hrefpre	= ($linkid ? "<a href='".e_SELF."?".$linkid."'>" : "");
					$hrefpost	= ($linkid ? "</a>" : "");
					$icon		= ($icon ? $path.$icon : "");

				}elseif($mode == "catlarge"){
					$path		= (!$path ? $content_cat_icon_path_large : $path);
					$hrefpre	= ($linkid ? "<a href='".e_SELF."?".$linkid."'>" : "");
					$hrefpost	= ($linkid ? "</a>" : "");
					$icon		= ($icon ? $path.$icon : "");
				}else{
					$path		= (!$path ? $content_icon_path : $path);
					$hrefpre	= "";
					$hrefpost	= "";
					$width		= "";
					$icon		= ($icon ? $path.$icon : ($blank ? $content_icon_path."blank.gif" : ""));
				}

				if($icon && file_exists($icon)){
					$iconstring	= $hrefpre."<img src='".$icon."' alt='' style='".$width." ".$border."' />".$hrefpost;
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
							$iconstring = "";
						}
					}else{
						$iconstring = "";
					}
				}

				return $iconstring;
		}


		function parseContentPathVars($srstring){
				$search		= array("{e_BASE}", "{e_ADMIN}", "{e_IMAGE}", "{e_THEME}", "{e_PLUGIN}", "{e_FILE}", "{e_HANDLER}", "{e_LANGUAGEDIR}", "{e_DOCS}", "{e_DOCROOT}");
				$replace	= array(e_BASE, e_ADMIN, e_IMAGE, e_THEME, e_PLUGIN, e_FILE, e_HANDLER, e_LANGUAGEDIR, e_DOCS, e_DOCROOT);
				return(str_replace($search, $replace, $srstring));
		}

		//admin
		function popup($image, $thumb, $maxwidth, $title, $text){
					//$image	:	full path to the large image you want to popup
					//$thumb	:	full path to the small image to show on screen
					//$maxwidth	:	the maximum size (width or height) an image may be popup'ed
					//$title	:	the window title of the popup
					//$text		:	the additional text to add into the popup

					if(file_exists($image)){
						
						//use $image if $thumb doesn't exist
						if(!file_exists($thumb)){
							$thumb = $image;
						}
						$imagearray = getimagesize(trim($image));
						//$imagearray holds width and height parameters of the image
						//$imagearray[0] is width - $imagearray[1] is height

						if($imagearray[1] > $imagearray[0]){
							if($imagearray[1] > $maxwidth){
								$width		= round(($maxwidth*$imagearray[0])/$imagearray[1],0);
								$height		= $maxwidth;
							}else{
								$width		= $imagearray[0];
								$height		= $imagearray[1];
							}
						}else{
							if($imagearray[0] > $maxwidth){
								$width		= $maxwidth;
								$height		= round(($maxwidth*$imagearray[1])/$imagearray[0],0);
							}else{
								$width		= $imagearray[0];
								$height		= $imagearray[1];
							}
						}
						$iconwidth = ($title == "help" ? "" : "width:100px;");

						$popup = "<a href=\"javascript:openPerfectPopup('".$image."',".$width.",'".$title."','".$text."')\" style='cursor:pointer;' onmouseover=\"window.status='click to enlarge image'; return true;\" onmouseout=\"window.status=''; return true;\" ><img src='".$thumb."' style='border:1px solid #000; ".$iconwidth."' alt='' /></a><br /><br />";

					}else{
						$popup = "";
					}
					return $popup;
		}

		//admin
		function popupHelp($text, $image="", $width="320", $title=""){
					//$image	:	full path to the image you want to show on screen (uses a default doc image)
					//$width	:	the width of the popup (uses a default width of 500)
					//$title	:	the window title of the popup (uses a default title of ...)
					//$text		:	the help text to show into the popup

					if(!$image || !file_exists($image)){
						$image = e_IMAGE."admin_images/docs_16.png";
					}
					if(!$width){ $width = "320"; }
					if(!$title){ $title = "content management help area"; }

					$popup = "<a href=\"javascript:openHelpPopup(".$width.",'".$title."','".$text."')\" style='cursor:pointer;' onmouseover=\"window.status='click for help on this page'; return true;\" onmouseout=\"window.status=''; return true;\" ><img src='".$image."' style='border:0;' alt='' /></a>";

					return $popup;
		}

		//search by keyword
		function showOptionsSearch($mode, $searchtypeid=""){
						global $plugindir, $plugintable, $qs, $rs;

						if(!is_object($rs)){
							require_once(e_HANDLER."form_handler.php");
							$rs = new form;
						}
						if(!isset($searchtypeid)){
							$searchtypeid = (is_numeric($qs[1]) ? $qs[1] : $qs[2]);
						}

						if($mode == "menu"){
							$CONTENT_SEARCH_TABLE_KEYWORD = $rs -> form_open("post", $plugindir."content.php?recent.$searchtypeid", "contentsearchmenu_{$mode}", "", "enctype='multipart/form-data'")."<input class='tbox' size='20' type='text' id='searchfieldmenu_{$mode}' name='searchfieldmenu_{$mode}' value='".(isset($_POST['searchfieldmenu_{$mode}']) ? $_POST['searchfieldmenu_{$mode}'] : CONTENT_LAN_18)."' maxlength='100' onfocus=\"document.forms['contentsearchmenu_{$mode}'].searchfieldmenu_$mode.value='';\" /> <input class='button' type='submit' name='searchsubmit' value='".CONTENT_LAN_19."' />".$rs -> form_close();
						}else{
							$searchfieldname = "searchfield_{$mode}";
							$CONTENT_SEARCH_TABLE_KEYWORD = $rs -> form_open("post", $plugindir."content.php?recent.$searchtypeid", "contentsearch_{$mode}", "", "enctype='multipart/form-data'")."
							<input class='tbox' size='27' type='text' id='$searchfieldname' name='$searchfieldname' value='".(isset($_POST[$searchfieldname]) ? $_POST[$searchfieldname] : CONTENT_LAN_18)."' maxlength='100' onfocus=\"document.forms['contentsearch_{$mode}'].$searchfieldname.value='';\" />
							<input class='button' type='submit' name='searchsubmit' value='".CONTENT_LAN_19."' />
							".$rs -> form_close();
						}

						return $CONTENT_SEARCH_TABLE_KEYWORD;
		}

		//redirection links in dropdown
		function showOptionsSelect($mode, $searchtypeid=""){
						global $plugindir, $plugintable, $rs, $qs, $content_pref;

						if(!is_object($rs)){
							require_once(e_HANDLER."form_handler.php");
							$rs = new form;
						}
						if(!isset($searchtypeid)){
							$searchtypeid = (is_numeric($qs[1]) ? $qs[1] : $qs[2]);
						}

						$catarray = "";
						$mainparent	= $this -> getMainParent( $searchtypeid );
						$parent		= $this -> getCategoryTree("", $mainparent, TRUE);

						//if(!is_array($content_pref)){
							$content_pref		= $this -> getContentPref($mainparent);
						//}

						$parent		= array_merge_recursive($parent);
						for($a=0;$a<count($parent);$a++){
							for($b=0;$b<count($parent[$a]);$b++){
								$newparent[$parent[$a][$b]] = $parent[$a][$b+1];
								$b++;
							}
						}
						if($newparent){
							$emptystring = "-- categories --";
							$catarray = $rs -> form_option($emptystring, "0", "none");
						}
						foreach($newparent as $key => $value){
							if($mode == "page" || ($mode == "menu" && isset($content_pref["content_menu_cat_number_{$mainparent}"])) ){
								$n = $this -> countCatItems($key);
								$n = " (".$n." ".($n == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54).")";
							}else{
								$n = "";
							}
							if( ($content_pref["content_menu_cat_main_$mainparent"] && $key == $mainparent) || $key != $mainparent ){
								$value = (strlen($value) > 25 ? substr($value,0,25)."..." : $value);
								$catarray .= $rs -> form_option($value.$n, 0, $plugindir."content.php?cat.".$key);
							}
						}

						if($mode == "page" || ($mode == "menu" && ($content_pref["content_menu_links_$mainparent"] && $content_pref["content_menu_links_dropdown_$mainparent"]) || ($content_pref["content_menu_cat_$mainparent"] && $content_pref["content_menu_cat_dropdown_$mainparent"]) ) ){

							$CONTENT_SEARCH_TABLE_SELECT = "
							".$rs -> form_open("post", $plugindir."content.php".(e_QUERY ? "?".e_QUERY : ""), "contentredirect".$mode, "", "enctype='multipart/form-data'")."				
							<select id='{$mode}value' name='{$mode}value' class='tbox' style='width:100%;' onchange=\"if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }\">";					
							

							if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_links_$mainparent"] && $content_pref["content_menu_links_dropdown_$mainparent"]) ){
								$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_56, 1, "none").$rs -> form_option("&nbsp;", "0", "none");

								if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewallcat_$mainparent"])){
								   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_6, 0, $plugindir."content.php?cat.list.".$mainparent);
								}
								if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewallauthor_$mainparent"])){
								   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_7, 0, $plugindir."content.php?author.list.".$mainparent);
								}
								if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewallitems_$mainparent"])){
								   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_83, 0, $plugindir."content.php?list.".$mainparent);
								}
								if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewtoprated_$mainparent"])){
								   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_8, 0, $plugindir."content.php?top.".$mainparent);
								}
								if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewrecent_$mainparent"])){
								   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_61, 0, $plugindir."content.php?recent.".$mainparent);
								}
								if( ($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewsubmit_$mainparent"]) && $content_pref["content_submit_$mainparent"] && check_class($content_pref["content_submit_class_$mainparent"]) ) ){
									$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_75, 0, $plugindir."content_submit.php");
								}
								$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option("&nbsp;", "0", "none");

							}

							if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_cat_$mainparent"] && $content_pref["content_menu_cat_dropdown_$mainparent"])){
								$CONTENT_SEARCH_TABLE_SELECT .= $catarray;
							}

							$CONTENT_SEARCH_TABLE_SELECT .= "
							".$rs -> form_select_close()."
							".$rs -> form_close();
						}
						return $CONTENT_SEARCH_TABLE_SELECT;
		}


		//ordering in dropdown
		function showOptionsOrder($mode, $ordertypeid=""){
					global $plugindir, $rs, $qs;

					if(!is_object($rs)){
						require_once(e_HANDLER."form_handler.php");
						$rs = new form;
					}
					if(!isset($ordertypeid)){
						$ordertypeid = (is_numeric($qs[1]) ? $qs[1] : $qs[2]);
					}

					$text = "";
					if(eregi('content.php', e_SELF)){
						if(e_QUERY){
							//if($qs[0] == "cat" && is_numeric($qs[1]) && !isset($qs[2]) ){
							//}else{

								$check = "";
								for($i=0;$i<count($qs);$i++){
									if($qs[$i] && substr($qs[$i],0,5) == "order"){
										$check = $qs[$i];
										break;
									}
								}

								$baseurl = $plugindir."content.php";
								$qry = (isset($qs[0]) && substr($qs[0],0,5) != "order" ? $qs[0] : "").(isset($qs[1]) && substr($qs[1],0,5) != "order" ? ".".$qs[1] : "").(isset($qs[2]) && substr($qs[2],0,5) != "order" ? ".".$qs[2] : "").(isset($qs[3]) && substr($qs[3],0,5) != "order" ? ".".$qs[3] : "");
								$text = $rs -> form_open("post", $baseurl."?$qs[0].$ordertypeid", "contentsearchorder{$mode}", "", "enctype='multipart/form-data'");
								$text .= "<select id='ordervalue{$mode}' name='ordervalue{$mode}' class='tbox' onchange=\"if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }\">";
								$text .= $rs -> form_option(CONTENT_ORDER_LAN_0, 1, "none");

								if($qs[0] == "author" && $qs[1] == "list"){
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_11, ($check == "orderaauthor" ? "1" : "0"), $baseurl."?".$qry.".orderaauthor" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_12, ($check == "orderdauthor" ? "1" : "0"), $baseurl."?".$qry.".orderdauthor" );
								}else{
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_1, ($check == "orderaheading" ? "1" : "0"), $baseurl."?".$qry.".orderaheading" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_2, ($check == "orderdheading" ? "1" : "0"), $baseurl."?".$qry.".orderdheading" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_3, ($check == "orderadate" ? "1" : "0"), $baseurl."?".$qry.".orderadate" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_4, ($check == "orderddate" ? "1" : "0"), $baseurl."?".$qry.".orderddate" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_5, ($check == "orderarefer" ? "1" : "0"), $baseurl."?".$qry.".orderarefer" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_6, ($check == "orderdrefer" ? "1" : "0"), $baseurl."?".$qry.".orderdrefer" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_7, ($check == "orderaparent" ? "1" : "0"), $baseurl."?".$qry.".orderaparent" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_8, ($check == "orderdparent" ? "1" : "0"), $baseurl."?".$qry.".orderdparent" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_9, ($check == "orderaorder" ? "1" : "0"), $baseurl."?".$qry.".orderaorder" );
									$text .= $rs -> form_option(CONTENT_ORDER_LAN_10, ($check == "orderdorder" ? "1" : "0"), $baseurl."?".$qry.".orderdorder" );
								}
								$text .= $rs -> form_select_close();
								$text .= $rs -> form_close();
							//}
						}
					}
					return $text;
		}





		function CreateParentMenu($parentid){
				global $plugintable, $plugindir, $tp, $datequery;

				if(!is_object($sqlcreatemenu)){ $sqlcreatemenu = new db; }
				if(!$sqlcreatemenu -> db_Select($plugintable, "*", "content_id='".$parentid."'  ")){
					return FALSE;
				}else{
					$row = $sqlcreatemenu -> db_Fetch();
				}
				$menufile = "content_".$row['content_heading'];
				$menuname = $row['content_heading'];

				$data = chr(60)."?php\n". chr(47)."*\n+---------------------------------------------------------------+\n|        e107 website system\n|        ".e_PLUGIN."content/menus/".$menufile."_menu.php\n|\n|        ©Steve Dunstan 2001-2002\n|        http://e107.org\n|        jalist@e107.org\n|\n|        Released under the terms and conditions of the\n|        GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n\nThis file has been generated by ".e_PLUGIN."content/handlers/content_class.php.\n\n*". chr(47)."\n\n";
				$data .= "\n";
				$data .= "unset(".chr(36)."text);\n";
				$data .= chr(36)."text = \"\";\n";
				$data .= chr(36)."menutypeid		= \"$parentid\";\n";
				$data .= chr(36)."menuname		= \"$menuname\";\n";
				$data .= "\n";
				$data .= chr(36)."plugindir		= e_PLUGIN.'content/';\n";
				$data .= chr(36)."plugintable	= \"pcontent\";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)\n";
				$data .= chr(36)."datequery		= \" AND (content_datestamp=0 || content_datestamp < \".time().\") AND (content_enddate=0 || content_enddate>\".time().\") \";\n";
				$data .= "\n";
				$data .= "require_once(e_PLUGIN.'content/handlers/content_class.php');\n";
				$data .= chr(36)."aa = new content;\n";
				$data .= "require_once(e_HANDLER.'form_handler.php');\n";
				$data .= chr(36)."rs = new form;\n";
				$data .= chr(36)."gen = new convert;\n";
				$data .= "\n";
				$data .= chr(36)."lan_file = e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content.php';\n";
				$data .= "include_once(file_exists(".chr(36)."lan_file) ? ".chr(36)."lan_file : e_PLUGIN.'content/languages/English/lan_content.php');\n";
				$data .= "\n";
				$data .= chr(36)."content_pref					= ".chr(36)."aa -> getContentPref(".chr(36)."menutypeid);\n";
				$data .= chr(36)."content_icon_path				= ".chr(36)."aa -> parseContentPathVars(".chr(36)."content_pref[\"content_icon_path_".chr(36)."menutypeid\"]);\n";
				$data .= chr(36)."content_cat_icon_path_small	= ".chr(36)."aa -> parseContentPathVars(".chr(36)."content_pref[\"content_cat_icon_path_small_".chr(36)."menutypeid\"]);\n";
				$data .= "\n";
				$data .= "//##### SEARCH SELECT ORDER --------------------------------------------------\n";
				$data .= "//show search box\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_search_".chr(36)."menutypeid\"]){\n";
				$data .= "	".chr(36)."text .= ".chr(36)."aa -> showOptionsSearch(\"menu\", ".chr(36)."menutypeid);\n";
				$data .= "}\n";
				$data .= "//show select box (with either links to other content pages, to categories, to both, or don't show at all)\n";
				$data .= "if( (".chr(36)."content_pref[\"content_menu_links_".chr(36)."menutypeid\"] && ".chr(36)."content_pref[\"content_menu_links_dropdown_".chr(36)."menutypeid\"]) || (".chr(36)."content_pref[\"content_menu_cat_".chr(36)."menutypeid\"] && ".chr(36)."content_pref[\"content_menu_cat_dropdown_".chr(36)."menutypeid\"]) ){\n";
				$data .= "	".chr(36)."text .= ".chr(36)."aa -> showOptionsSelect(\"menu\", ".chr(36)."menutypeid);\n";
				$data .= "}\n";
				$data .= "//show order box\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_sort_".chr(36)."menutypeid\"]){\n";
				$data .= "	".chr(36)."text .= ".chr(36)."aa -> showOptionsOrder(\"menu\", ".chr(36)."menutypeid);\n";
				$data .= "}\n";
				$data .= "\n";
				$data .= "//show links list if chosen so\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_links_".chr(36)."menutypeid\"] && !".chr(36)."content_pref[\"content_menu_links_dropdown_".chr(36)."menutypeid\"]){\n";
				$data .= "	".chr(36)."text .= \"<br />\";\n";
				$data .= "	".chr(36)."text .= (".chr(36)."content_pref[\"content_menu_links_caption_".chr(36)."menutypeid\"] != \"\" ? ".chr(36)."content_pref[\"content_menu_links_caption_".chr(36)."menutypeid\"] : CONTENT_MENU_LAN_4).\"<br />\";\n";
				$data .= "\n";
				$data .= "	//define icon\n";
				$data .= "	if(".chr(36)."content_pref[\"content_menu_links_icon_".chr(36)."menutypeid\"] == \"0\"){ ".chr(36)."linksicon = \"\";\n";
				$data .= "	}elseif(".chr(36)."content_pref[\"content_menu_links_icon_".chr(36)."menutypeid\"] == \"1\"){ ".chr(36)."linksicon = \"<img src='\".THEME.\"images/bullet2.gif' alt='' />\";\n";
				$data .= "	}elseif(".chr(36)."content_pref[\"content_menu_links_icon_".chr(36)."menutypeid\"] == \"2\"){ ".chr(36)."linksicon = \"&middot\";\n";
				$data .= "	}elseif(".chr(36)."content_pref[\"content_menu_links_icon_".chr(36)."menutypeid\"] == \"3\"){ ".chr(36)."linksicon = \"º\";\n";
				$data .= "	}elseif(".chr(36)."content_pref[\"content_menu_links_icon_".chr(36)."menutypeid\"] == \"4\"){ ".chr(36)."linksicon = \"&raquo;\";\n";
				$data .= "	}\n";
				$data .= "\n";
				$data .= "	if(".chr(36)."content_pref[\"content_menu_viewallcat_".chr(36)."menutypeid\"]){\n";
				$data .= "		".chr(36)."text .= ".chr(36)."linksicon.\" <a href='\".".chr(36)."plugindir.\"content.php?cat.list.\".".chr(36)."menutypeid.\"'>\".CONTENT_LAN_6.\"</a><br />\";\n";
				$data .= "	}\n";
				$data .= "	if(".chr(36)."content_pref[\"content_menu_viewallauthor_".chr(36)."menutypeid\"]){\n";
				$data .= "		".chr(36)."text .= ".chr(36)."linksicon.\" <a href='\".".chr(36)."plugindir.\"content.php?author.list.\".".chr(36)."menutypeid.\"'>\".CONTENT_LAN_7.\"</a><br />\";\n";
				$data .= "	}\n";
				$data .= "	if(".chr(36)."content_pref[\"content_menu_viewallitems_".chr(36)."menutypeid\"]){\n";
				$data .= "		".chr(36)."text .= ".chr(36)."linksicon.\" <a href='\".".chr(36)."plugindir.\"content.php?list.\".".chr(36)."menutypeid.\"'>\".CONTENT_LAN_83.\"</a><br />\";\n";
				$data .= "	}\n";
				$data .= "	if(".chr(36)."content_pref[\"content_menu_viewtoprated_".chr(36)."menutypeid\"]){\n";
				$data .= "		".chr(36)."text .= ".chr(36)."linksicon.\" <a href='\".".chr(36)."plugindir.\"content.php?top.\".".chr(36)."menutypeid.\"'>\".CONTENT_LAN_8.\"</a><br />\";\n";
				$data .= "	}\n";
				$data .= "	if(".chr(36)."content_pref[\"content_menu_viewrecent_".chr(36)."menutypeid\"]){\n";
				$data .= "		".chr(36)."text .= ".chr(36)."linksicon.\" <a href='\".".chr(36)."plugindir.\"content.php?recent.\".".chr(36)."menutypeid.\"'>\".CONTENT_LAN_61.\"</a><br />\";\n";
				$data .= "	}\n";
				$data .= "	if( ".chr(36)."content_pref[\"content_menu_viewsubmit_".chr(36)."menutypeid\"] && ".chr(36)."content_pref[\"content_submit_".chr(36)."menutypeid\"] && check_class(".chr(36)."content_pref[\"content_submit_class_".chr(36)."menutypeid\"]) ){\n";
				$data .= "		".chr(36)."text .= ".chr(36)."linksicon.\" <a href='\".".chr(36)."plugindir.\"content_submit.php'>\".CONTENT_LAN_75.\"</a><br />\";\n";
				$data .= "	}\n";
				$data .= "	".chr(36)."text .= \"<br />\";\n";
				$data .= "}\n";
				$data .= "\n";
				$data .= "//get category array\n";
				$data .= chr(36)."array = ".chr(36)."aa -> getCategoryTree(\"\", ".chr(36)."menutypeid, TRUE);\n";
				$data .= "\n";
				$data .= "//##### CATEGORY LIST --------------------------------------------------\n";
				$data .= "if(!".chr(36)."content_pref[\"content_menu_cat_dropdown_".chr(36)."menutypeid\"]){\n";
				$data .= "	if(".chr(36)."content_pref[\"content_menu_cat_".chr(36)."menutypeid\"]){\n";
				$data .= "		".chr(36)."text .= (".chr(36)."content_pref[\"content_menu_cat_caption_".chr(36)."menutypeid\"] != \"\" ? ".chr(36)."content_pref[\"content_menu_cat_caption_".chr(36)."menutypeid\"] : CONTENT_MENU_LAN_3).\"<br />\";\n";
				$data .= "\n";
				$data .= "		".chr(36)."newarray = array_merge_recursive(".chr(36)."array);\n";
				$data .= "		for(".chr(36)."a=0;".chr(36)."a<count(".chr(36)."newarray);".chr(36)."a++){\n";
				$data .= "			for(".chr(36)."b=0;".chr(36)."b<count(".chr(36)."newarray[".chr(36)."a]);".chr(36)."b++){\n";
				$data .= "				".chr(36)."newparent[".chr(36)."newarray[".chr(36)."a][".chr(36)."b]] = ".chr(36)."newarray[".chr(36)."a][".chr(36)."b+1];\n";
				$data .= "				".chr(36)."b++;\n";
				$data .= "			}\n";
				$data .= "		}\n";
				$data .= "		if(!is_object(".chr(36)."sql)){ ".chr(36)."sql = new db; }\n";
				$data .= "			foreach(".chr(36)."newparent as ".chr(36)."key => ".chr(36)."value){\n";
				$data .= "				if( (".chr(36)."content_pref[\"content_menu_cat_main_".chr(36)."menutypeid\"] && ".chr(36)."key == ".chr(36)."menutypeid) || ".chr(36)."key != ".chr(36)."menutypeid ){\n";
				$data .= "					".chr(36)."sql -> db_Select(".chr(36)."plugintable, \"*\", \"content_id = '\".".chr(36)."key.\"' \");\n";
				$data .= "					".chr(36)."row = ".chr(36)."sql -> db_Fetch();\n";
				$data .= "\n";
				$data .= "					//define icon\n";
				$data .= "					".chr(36)."ICON = \"\";\n";
				$data .= "					if(".chr(36)."content_pref[\"content_menu_cat_icon_".chr(36)."menutypeid\"] == \"0\"){ ".chr(36)."ICON = \"\";\n";
				$data .= "					}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_".chr(36)."menutypeid\"] == \"1\"){ ".chr(36)."ICON = \"<img src='\".THEME.\"images/bullet2.gif' alt='' style='border:0;' />\";\n";
				$data .= "					}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_".chr(36)."menutypeid\"] == \"2\"){ ".chr(36)."ICON = \"&middot\";\n";
				$data .= "					}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_".chr(36)."menutypeid\"] == \"3\"){ ".chr(36)."ICON = \"º\";\n";
				$data .= "					}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_".chr(36)."menutypeid\"] == \"4\"){ ".chr(36)."ICON = \"&raquo;\";\n";
				$data .= "					}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_".chr(36)."menutypeid\"] == \"5\"){\n";
				$data .= "					if(".chr(36)."row['content_icon'] != \"\" && file_exists(".chr(36)."content_cat_icon_path_small.".chr(36)."row['content_icon']) ){\n";
				$data .= "						".chr(36)."ICON = \"<img src='\".".chr(36)."content_cat_icon_path_small.".chr(36)."row['content_icon'].\"' alt='' style='border:0;' />\";\n";
				$data .= "					}else{\n";
				$data .= "						//default category icon\n";
				$data .= "						if(".chr(36)."content_pref[\"content_menu_cat_icon_default_".chr(36)."menutypeid\"] == \"0\"){ ".chr(36)."ICON = \"\";\n";
				$data .= "						}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_default_".chr(36)."menutypeid\"] == \"1\"){ ".chr(36)."ICON = \"<img src='\".THEME.\"images/bullet2.gif' alt='' style='border:0;' />\";\n";
				$data .= "						}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_default_".chr(36)."menutypeid\"] == \"2\"){ ".chr(36)."ICON = \"&middot\";\n";
				$data .= "						}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_default_".chr(36)."menutypeid\"] == \"3\"){ ".chr(36)."ICON = \"º\";\n";
				$data .= "						}elseif(".chr(36)."content_pref[\"content_menu_cat_icon_default_".chr(36)."menutypeid\"] == \"4\"){ ".chr(36)."ICON = \"&raquo;\";\n";
				$data .= "						}\n";
				$data .= "					}\n";
				$data .= "				}\n";
				$data .= "				//display category list\n";
				$data .= "				".chr(36)."text .= \"<table style='width:98%; text-align:left; border:0;' cellpadding='0' cellspacing='0'>\";\n";
				$data .= "				".chr(36)."text .= \"<tr>\";\n";
				$data .= "				".chr(36)."text .= (".chr(36)."ICON ? \"<td style='width:2%; white-space:nowrap; padding-right:5px;'><a href='\".e_PLUGIN.\"content/content.php?cat.\".".chr(36)."row['content_id'].\"'>\".".chr(36)."ICON.\"</a></td>\" : \"\");\n";
				$data .= "				".chr(36)."text .= \"<td colspan='2'>\";\n";
				$data .= "				".chr(36)."text .= \"<a href='\".e_PLUGIN.\"content/content.php?cat.\".".chr(36)."row['content_id'].\"'>\".".chr(36)."row['content_heading'].\"</a>\";\n";
				$data .= "				".chr(36)."text .= (".chr(36)."content_pref[\"content_menu_cat_number_".chr(36)."menutypeid\"] ? \" <span class='smalltext'>(\".".chr(36)."aa -> countCatItems(".chr(36)."key).\")</span>\" : \"\");\n";
				$data .= "				".chr(36)."text .= \"</td>\";\n";
				$data .= "				".chr(36)."text .= \"</tr>\";\n";
				$data .= "				".chr(36)."text .= \"</table>\";\n";
				$data .= "			}\n";
				$data .= "		}\n";
				$data .= "	}\n";
				$data .= "}\n";
				$data .= "\n";
				$data .= "//##### RECENT --------------------------------------------------\n";
				$data .= "if(".chr(36)."content_pref[\"content_menu_recent_".chr(36)."menutypeid\"]){\n";
				$data .= chr(36)."text .= \"<br />\";\n";
				$data .= "\n";
				$data .= "//prepare query paramaters\n";
				$data .= chr(36)."validparent = implode(\",\", array_keys(".chr(36)."array));\n";
				$data .= chr(36)."qry = \" content_parent REGEXP '\".".chr(36)."aa -> CONTENTREGEXP(".chr(36)."validparent).\"' \";\n";
				$data .= "\n";
				$data .= chr(36)."sql1 = new db;\n";
				$data .= chr(36)."contenttotal = ".chr(36)."sql1 -> db_Count(".chr(36)."plugintable, \"(*)\", \"WHERE content_refer != 'sa' AND \".".chr(36)."qry.\" \".".chr(36)."datequery.\" AND content_class REGEXP '\".e_CLASS_REGEXP.\"' \" );\n";
				$data .= "\n";
				$data .= "if(".chr(36)."resultitem = ".chr(36)."sql1 -> db_Select(".chr(36)."plugintable, \"*\", \"content_refer !='sa' AND \".".chr(36)."qry.\" \".".chr(36)."datequery.\" AND content_class REGEXP '\".e_CLASS_REGEXP.\"' ORDER BY content_datestamp DESC LIMIT 0,\".".chr(36)."content_pref[\"content_menu_recent_number_".chr(36)."menutypeid\"] )){\n";
				$data .= "\n";
				$data .= "	".chr(36)."text .= (".chr(36)."content_pref[\"content_menu_recent_caption_".chr(36)."menutypeid\"] != \"\" ? ".chr(36)."content_pref[\"content_menu_recent_caption_".chr(36)."menutypeid\"] : CONTENT_MENU_LAN_2).\"<br />\";\n";
				$data .= "	while(".chr(36)."row = ".chr(36)."sql1 -> db_Fetch()){\n";
				$data .= "\n";
				$data .= "		".chr(36)."ICON = \"\";\n";
				$data .= "		".chr(36)."DATE = \"\";\n";
				$data .= "		".chr(36)."AUTHOR = \"\";\n";
				$data .= "		".chr(36)."SUBHEADING = \"\";\n";
				$data .= "\n";
				$data .= "		if(".chr(36)."content_pref[\"content_menu_recent_date_".chr(36)."menutypeid\"]){\n";
				$data .= "			".chr(36)."datestyle = (".chr(36)."content_pref[\"content_archive_datestyle_".chr(36)."menutypeid\"] ? ".chr(36)."content_pref[\"content_archive_datestyle_".chr(36)."menutypeid\"] : \"%d %b %Y\");\n";
				$data .= "			".chr(36)."DATE = strftime(".chr(36)."datestyle, ".chr(36)."row['content_datestamp']);\n";
				$data .= "		}\n";
				$data .= "		if(".chr(36)."content_pref[\"content_menu_recent_author_".chr(36)."menutypeid\"]){\n";
				$data .= "			".chr(36)."authordetails = ".chr(36)."aa -> getAuthor(".chr(36)."row['content_author']);\n";
				$data .= "			".chr(36)."AUTHOR = ".chr(36)."authordetails[1];\n";
				$data .= "		}\n";
				$data .= "\n";
				$data .= "		//subheading\n";
				$data .= "		if(".chr(36)."content_pref[\"content_menu_recent_subheading_".chr(36)."menutypeid\"] && ".chr(36)."row['content_subheading']){\n";
				$data .= "			if(".chr(36)."content_pref[\"content_menu_recent_subheading_char_".chr(36)."menutypeid\"] && ".chr(36)."content_pref[\"content_menu_recent_subheading_char_".chr(36)."menutypeid\"] != \"\" && ".chr(36)."content_pref[\"content_menu_recent_subheading_char_".chr(36)."menutypeid\"] != \"0\"){\n";
				$data .= "				if(strlen(".chr(36)."row['content_subheading']) > ".chr(36)."content_pref[\"content_menu_recent_subheading_char_".chr(36)."menutypeid\"]) {\n";
				$data .= "					".chr(36)."row['content_subheading'] = substr(".chr(36)."row['content_subheading'], 0, ".chr(36)."content_pref[\"content_menu_recent_subheading_char_".chr(36)."menutypeid\"]).".chr(36)."content_pref[\"content_menu_recent_subheading_post_".chr(36)."menutypeid\"];\n";
				$data .= "				}\n";
				$data .= "			}\n";
				$data .= "			".chr(36)."SUBHEADING = ".chr(36)."row['content_subheading'];\n";
				$data .= "		}\n";
				$data .= "\n";
				$data .= "		//define icon\n";
				$data .= "		".chr(36)."recenticonwidth = \"\";\n";
				$data .= "		if(".chr(36)."content_pref[\"content_menu_recent_icon_".chr(36)."menutypeid\"] == \"0\"){ ".chr(36)."ICON = \"\";\n";
				$data .= "		}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_".chr(36)."menutypeid\"] == \"1\"){ ".chr(36)."ICON = \"<img src='\".THEME.\"images/bullet2.gif' alt='' style='border:0;' />\";\n";
				$data .= "		}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_".chr(36)."menutypeid\"] == \"2\"){ ".chr(36)."ICON = \"&middot\";\n";
				$data .= "		}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_".chr(36)."menutypeid\"] == \"3\"){ ".chr(36)."ICON = \"º\";\n";
				$data .= "		}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_".chr(36)."menutypeid\"] == \"4\"){ ".chr(36)."ICON = \"&raquo;\";\n";
				$data .= "		}elseif(".chr(36)."content_pref[\"content_menu_recent_icon_".chr(36)."menutypeid\"] == \"5\"){\n";
				$data .= "			if(".chr(36)."content_pref[\"content_menu_recent_icon_".chr(36)."menutypeid\"] == \"5\"){\n";
				$data .= "				if(".chr(36)."content_pref[\"content_menu_recent_icon_width_".chr(36)."menutypeid\"]){\n";
				$data .= "					".chr(36)."recenticonwidth = \" width:\".".chr(36)."content_pref[\"content_menu_recent_icon_width_".chr(36)."menutypeid\"].\"px; \";\n";
				$data .= "				}else{\n";
				$data .= "					".chr(36)."recenticonwidth = \" width:50px; \";\n";
				$data .= "				}\n";
				$data .= "			}\n";
				$data .= "			if(".chr(36)."content_pref[\"content_menu_recent_icon_".chr(36)."menutypeid\"] == \"5\" && ".chr(36)."row['content_icon'] != \"\" && file_exists(".chr(36)."content_icon_path.".chr(36)."row['content_icon'])){\n";
				$data .= "				".chr(36)."ICON = \"<img src='\".".chr(36)."content_icon_path.".chr(36)."row['content_icon'].\"' alt='' style='\".".chr(36)."recenticonwidth.\" border:0;' />\";\n";
				$data .= "			}\n";
				$data .= "		}\n";
				$data .= "\n";
				$data .= "		//display recent list\n";
				$data .= "		".chr(36)."text .= \"<table style='width:98%; text-align:left; border:0; margin-bottom:10px;' cellpadding='0' cellspacing='0'>\";\n";
				$data .= "		".chr(36)."text .= \"<tr>\";\n";
				$data .= "		".chr(36)."text .= (".chr(36)."ICON ? \"<td style='width:1%; white-space:nowrap; vertical-align:top; padding-right:10px;'><a href='\".e_PLUGIN.\"content/content.php?content.\".".chr(36)."row['content_id'].\"'>\".".chr(36)."ICON.\"</a></td>\" : \"\");\n";
				$data .= "		".chr(36)."text .= \"<td style='width:99%; vertical-align:top;'>\";\n";
				$data .= "		".chr(36)."text .= \"<a href='\".e_PLUGIN.\"content/content.php?content.\".".chr(36)."row['content_id'].\"'>\".".chr(36)."row['content_heading'].\"</a><br />\";\n";
				$data .= "		".chr(36)."text .= (".chr(36)."DATE ? ".chr(36)."DATE.\"<br />\" : \"\" );\n";
				$data .= "		".chr(36)."text .= (".chr(36)."AUTHOR ? ".chr(36)."AUTHOR.\"<br />\" : \"\" );\n";
				$data .= "		".chr(36)."text .= (".chr(36)."SUBHEADING ? ".chr(36)."SUBHEADING.\"<br />\" : \"\" );\n";
				$data .= "		".chr(36)."text .= \"</td>\";\n";
				$data .= "		".chr(36)."text .= \"</tr>\";\n";
				$data .= "		".chr(36)."text .= \"</table>\";\n";
				$data .= "	}\n";
				$data .= "}\n";
				$data .= "}\n";
				$data .= "\n";				
				$data .= "if(!isset(".chr(36)."text)){ ".chr(36)."text = CONTENT_MENU_LAN_1; }\n";
				$data .= chr(36)."caption = (".chr(36)."content_pref[\"content_menu_caption_".chr(36)."menutypeid\"] != \"\" ? ".chr(36)."content_pref[\"content_menu_caption_".chr(36)."menutypeid\"] : CONTENT_MENU_LAN_0.\" \".".chr(36)."menuname);\n";
				$data .= chr(36)."ns -> tablerender(".chr(36)."caption, ".chr(36)."text);\n";
				$data .= "\n";
				$data .= "?".chr(62);
				 
				if(file_exists($plugindir."menus/".$menufile."_menu.php")){
					$message = "";
				}else{
					$fp = @fopen($plugindir."menus/".$menufile."_menu.php", "w");
					if (!@fwrite($fp, $data)) {
						$message = CONTENT_ADMIN_CAT_LAN_51;
					} else {
						fclose($fp);
						$message = CONTENT_ADMIN_CAT_LAN_50;
					}
				}
				return $message;
		}


}	//close class

?>