<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }
if (!isset($pref['plug_installed']['content']))
{
	header('location:'.e_BASE.'index.php');
	exit;
}

$plugindir		= e_PLUGIN."content/";
$plugintable	= "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
$datequery		= " AND content_datestamp < ".time()." AND (content_enddate=0 || content_enddate>".time().") ";

if (!defined('ADMIN_WIDTH')) { define("ADMIN_WIDTH", "width:98%;"); }

//icon, file, image upload
if(isset($_POST['uploadfile']))
{
	if($_POST['uploadtype'])
	{
		$pref['upload_storagetype'] = "1";
		require_once(e_HANDLER."upload_handler.php");
		$mainparent		= $aa -> getMainParent(intval($_POST['parent1']));
		$content_pref	= $aa -> getContentPref($mainparent);

		if($_POST['content_id']){
			$newpid = $_POST['content_id'];
		}else{
			$sql -> db_select("pcontent", "MAX(content_id) as aid", "content_id!='0' ");
			list($aid) = $sql -> db_Fetch();
			$newpid = $aid+1;
		}
	}

	//icon
	if($_POST['uploadtype'] == "1")
	{
		$pref['upload_storagetype'] = "1";
		$pathtmp		= $_POST['tmppathicon'];
		$uploaded		= file_upload($pathtmp);
		$new = "";
		if($uploaded){
			$uporg		= $uploaded[0]['name'];
			$resize		= (isset($content_pref["content_upload_icon_size"]) && $content_pref["content_upload_icon_size"] ? $content_pref["content_upload_icon_size"] : "100");
			if($uporg){
				$new = $newpid."_".$uporg;
				rename($pathtmp.$uporg, $pathtmp.$new);
				require_once(e_HANDLER."resize_handler.php");
				resize_image($pathtmp.$new, $pathtmp.$new, $resize, "nocopy");
			}
		}
		$message = ($new ? CONTENT_ADMIN_ITEM_LAN_106 : CONTENT_ADMIN_ITEM_LAN_107);

	//file
	}
	elseif($_POST['uploadtype'] == "2")
	{
		$pref['upload_storagetype'] = "1";
		$pathtmp		= $_POST['tmppathfile'];
		$uploaded		= file_upload($pathtmp);
		$new = "";
		if($uploaded){
			$uporg		= $uploaded[0]['name'];
			if($uporg){
				$new = $newpid."_".$uporg;
				rename($pathtmp.$uporg, $pathtmp.$new);
			}
		}
		$message = ($new ? CONTENT_ADMIN_ITEM_LAN_108 : CONTENT_ADMIN_ITEM_LAN_109);

	//image
	}
	elseif($_POST['uploadtype'] == "3")
	{
		$pref['upload_storagetype'] = "1";
		$pathtmp		= $_POST['tmppathimage'];
		$uploaded		= file_upload($pathtmp);
		$new = "";
		if($uploaded){
			$uporg		= $uploaded[0]['name'];
			$resize		= (isset($content_pref["content_upload_image_size"]) && $content_pref["content_upload_image_size"] ? $content_pref["content_upload_image_size"] : "500");
			$resizethumb	= (isset($content_pref["content_upload_image_size_thumb"]) && $content_pref["content_upload_image_size_thumb"] ? $content_pref["content_upload_image_size_thumb"] : "100");
			if($uporg){
				$new = $newpid."_".$uporg;
				rename($pathtmp.$uporg, $pathtmp.$new);
				require_once(e_HANDLER."resize_handler.php");
				resize_image($pathtmp.$new, $pathtmp.$new, $resizethumb, "copy");
				resize_image($pathtmp.$new, $pathtmp.$new, $resize, "nocopy");
			}
		}
		$message = ($new ? CONTENT_ADMIN_ITEM_LAN_110 : CONTENT_ADMIN_ITEM_LAN_111);
	}
}

class contentdb
{
	// Method to update content in DB
	function dbContent($mode, $type)
	{
		//$mode		: create or update
		//$type		: none(=admin), submit, contentmanager
		global $pref, $qs, $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache, $eArrayStorage, $e_event;

		$_POST['content_heading']		= $tp -> toDB(trim($_POST['content_heading']));
		$_POST['content_subheading']	= $tp -> toDB($_POST['content_subheading']);
		$_POST['content_summary']		= $tp -> toDB($_POST['content_summary']);

		if(e_WYSIWYG)
		{
			$_POST['content_text']		= $tp->createConstants($_POST['content_text']); // convert e107_images/ to {e_IMAGE} etc.
		}
		//the problem with tiny_mce is it's storing e_HTTP with an image path, while it should only use the {e_xxx} variables
		//this small check resolves this, and stores the paths correctly
		if(strstr($_POST['content_text'],e_HTTP."{e_"))
		{
			$_POST['content_text'] = str_replace(e_HTTP."{e_", "{e_", $_POST['content_text']);
		}
			
		$_POST['content_text']			= $tp -> toDB($_POST['content_text']);
		$_POST['content_class']			= ($_POST['content_class'] ? intval($_POST['content_class']) : "0");
		$_POST['content_meta']			= $tp -> toDB($_POST['content_meta']);

		//content create
		if( isset($qs[0]) && $qs[0]=='content' && isset($qs[1]) && ($qs[1]=='create' || $qs[1]=='submit') && isset($qs[2]) && is_numeric($qs[2]) )
		{
			$parent = intval($_POST['parent1']);
			//content edit
		}
		elseif ( isset($qs[0]) && $qs[0]=='content' && isset($qs[1]) && ($qs[1]=='edit' || $qs[1]=='sa') && isset($qs[2]) && is_numeric($qs[2]) )
		{
			if( isset($_POST['parent1']) && strpos($_POST['parent1'], ".") )
			{
				$tmp = explode(".", $_POST['parent1']);
				$parent = $tmp[1];
			}
			elseif(isset($_POST['preview_parent1']) && $_POST['preview_parent1'])
			{
				$parent = $_POST['preview_parent1'];
			}
			else
			{
				$parent = $_POST['parent1'];
			}
		}
		$_POST['parent'] = $parent;

		if(USER)
		{
			if($_POST['content_author_id'])
			{
				if(!($_POST['content_author_id'] == USERID && $_POST['content_author_name'] == USERNAME && $_POST['content_author_email'] == USEREMAIL) )
				{
					$author = $_POST['content_author_id'];
						
					if ($_POST['content_author_name'] != CONTENT_ADMIN_ITEM_LAN_14)
					{
						$author .= "^".$_POST['content_author_name'];
					}
					if ($_POST['content_author_email'] != CONTENT_ADMIN_ITEM_LAN_15)
					{
						$author .= "^".$_POST['content_author_email'];
					}
				}
				else
				{
					$author = $_POST['content_author_id'];
				}
			}
			else
			{
				$author = $_POST['content_author_name'];
				if($_POST['content_author_email'] != "" && $_POST['content_author_email'] != CONTENT_ADMIN_ITEM_LAN_15)
				{
					$author .= "^".$_POST['content_author_email'];
				}
			}
		}
		else
		{	// Non-user posting content
			if ($type != 'submit')
			{	// Naughty!
				header("location:".$plugindir."content.php"); 	// but be kind
				exit;
			}
			$author = $_POST['content_author_name'];
			if($_POST['content_author_email'] != "" && $_POST['content_author_email'] != CONTENT_ADMIN_ITEM_LAN_15)
			{
				$author .= "^".$_POST['content_author_email'];
			}
		}

		$mainparent						= $aa -> getMainParent(intval($_POST['parent']));
		$content_pref					= $aa -> getContentPref($mainparent);
			
		$content_pref["content_icon_path_tmp"] = ($content_pref["content_icon_path_tmp"] ? $content_pref["content_icon_path_tmp"] : $content_pref["content_icon_path"]."tmp/");
		$content_pref["content_file_path_tmp"] = ($content_pref["content_file_path_tmp"] ? $content_pref["content_file_path_tmp"] : $content_pref["content_file_path"]."tmp/");
		$content_pref["content_image_path_tmp"] = ($content_pref["content_image_path_tmp"] ? $content_pref["content_image_path_tmp"] : $content_pref["content_image_path"]."tmp/");
			
		$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large"]);
		$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small"]);
		$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path"]);
		$content_image_path				= $tp -> replaceConstants($content_pref["content_image_path"]);
		$content_file_path				= $tp -> replaceConstants($content_pref["content_file_path"]);
		$content_tmppath_icon			= $tp -> replaceConstants($content_pref["content_icon_path_tmp"]);
		$content_tmppath_file			= $tp -> replaceConstants($content_pref["content_file_path_tmp"]);
		$content_tmppath_image			= $tp -> replaceConstants($content_pref["content_image_path_tmp"]);

			//move icon to correct folder
			if($_POST['content_icon']){
				$icon = $tp->toDB($_POST['content_icon']);	
				if($icon && file_exists($content_tmppath_icon.$icon)){
					rename($content_tmppath_icon.$icon, $content_icon_path.$icon);
				}
			}

			$sumf = 0;
			$sumi = 0;
			foreach($_POST as $k => $v){
				if(strpos($k, "content_files") === 0){
					$sumf = $sumf+1;
				}
				if(strpos($k, "content_images") === 0){
					$sumi = $sumi+1;
				}
			}
			//move attachments to correct folder
			$totalattach = "";
			for($i=0;$i<$sumf;$i++){
				$attach{$i} = $tp->toDB($_POST["content_files{$i}"]);
				if($attach{$i} && file_exists($content_tmppath_file.$attach{$i})){
					rename($content_tmppath_file.$attach{$i}, $content_file_path.$attach{$i});
				}
				if($attach{$i} && file_exists($content_file_path.$attach{$i})){
					$totalattach .= "[file]".$attach{$i};
				}
			}
			//move images to correct folder
			$totalimages = "";
			for($i=0;$i<$sumi;$i++){
				$image{$i} = $tp->toDB($_POST["content_images{$i}"]);
				if($image{$i} && file_exists($content_tmppath_image.$image{$i})){
					rename($content_tmppath_image.$image{$i}, $content_image_path.$image{$i});
				}
				if($image{$i} && file_exists($content_tmppath_image."thumb_".$image{$i})){
					rename($content_tmppath_image."thumb_".$image{$i}, $content_image_path."thumb_".$image{$i});
				}
				if($image{$i} && file_exists($content_image_path.$image{$i})){
					$totalimages .= "[img]".$image{$i};
				}
			}

			if($_POST['update_datestamp']){
				$starttime = time();
			}else{
				if( isset($_POST['ne_day']) && $_POST['ne_day']!='' && $_POST['ne_day']!='0' && $_POST['ne_day'] != "none" 
				&& isset($_POST['ne_month']) && $_POST['ne_month']!='' && $_POST['ne_month']!='0' && $_POST['ne_month'] != "none" 
				&& isset($_POST['ne_year']) && $_POST['ne_year']!='' && $_POST['ne_year']!='0' && $_POST['ne_year'] != "none" ){
					$newstarttime = mktime( 0, 0, 0, intval($_POST['ne_month']), intval($_POST['ne_day']), intval($_POST['ne_year']));
				}else{
					$newstarttime = time();
				}
				if(isset($_POST['content_datestamp']) && $_POST['content_datestamp'] != "" && $_POST['content_datestamp'] != "0"){
					if($newstarttime != $starttime){
						$starttime = $newstarttime;
					}else{
						$starttime = intval($_POST['content_datestamp']);
					}
				}else{
					$starttime = time();
				}
			}

			if( isset($_POST['end_day']) && $_POST['end_day']!='' && $_POST['end_day']!='0' && $_POST['end_day'] != "none" 
				&& isset($_POST['end_month']) && $_POST['end_month']!='' && $_POST['end_month']!='0' && $_POST['end_month'] != "none" 
				&& isset($_POST['end_year']) && $_POST['end_year']!='' && $_POST['end_year']!='0' && $_POST['end_year'] != "none" ){
				$endtime = mktime( 0, 0, 0, intval($_POST['end_month']), intval($_POST['end_day']), intval($_POST['end_year']));
			}else{
				$endtime = "0";
			}

			//custom additional data tags
			for($i=0;$i<$content_pref["content_admin_custom_number"];$i++){
				if(isset($_POST["content_custom_key_{$i}"]) && isset($_POST["content_custom_value_{$i}"]) && $_POST["content_custom_value_{$i}"] != ""){
					$keystring = $tp->toDB($_POST["content_custom_key_{$i}"]);
					$custom["content_custom_{$keystring}"] = $tp->toDB($_POST["content_custom_value_{$i}"]);
				}
			}
			//preset additional data tags
			if(isset($_POST['content_custom_preset_key']) && $_POST['content_custom_preset_key']){
				$custom['content_custom_presettags'] = $tp->toDB($_POST['content_custom_preset_key']);
			}
			if($custom){
				$contentprefvalue = $eArrayStorage->WriteArray($custom);
			}else{
				$contentprefvalue = "";
			}

			$_POST['content_layout'] = (!$_POST['content_layout'] || $_POST['content_layout'] == "content_content_template.php" ? "" : $tp->toDB($_POST['content_layout']));
			
			//content_order : not added in the sql
			//content_refer : only added in sql if posting submitted item
			//$refer = (isset($_POST['content_refer']) && $_POST['content_refer']=='sa' ? ", content_refer='' " : "");

			
		if($mode == "create")
		{
				if($type == "submit"){
					$refer = ($content_pref["content_submit_directpost"] ? "" : "sa");
				}else{
					$refer = "";
				}
				$sql -> db_Insert($plugintable, "'0', '".$_POST['content_heading']."', '".$_POST['content_subheading']."', '".$_POST['content_summary']."', '".$_POST['content_text']."', '".$tp->toDB($author)."', '".$icon."', '".$totalattach."', '".$totalimages."', '".$_POST['parent']."', '".intval($_POST['content_comment'])."', '".intval($_POST['content_rate'])."', '".intval($_POST['content_pe'])."', '".$refer."', '".$starttime."', '".$endtime."', '".$_POST['content_class']."', '".$contentprefvalue."', '0', '".intval($_POST['content_score'])."', '".$_POST['content_meta']."', '".$_POST['content_layout']."' ");

				$e107cache->clear("$plugintable");

				//trigger event for notify
				$edata_cs = array("content_heading" => $_POST['content_heading'], "content_subheading" => $_POST['content_subheading'], "content_author" => $_POST['content_author_name']);
				$e_event->trigger("content", $edata_cs);

				if(!$type || $type == "admin"){
					js_location(e_SELF."?".e_QUERY.".cc");
				}elseif($type == "contentmanager"){
					js_location(e_SELF."?c");
				}elseif($type == "submit"){
					if($content_pref["content_submit_directpost"]){
						js_location(e_SELF."?s");
					}else{
						js_location(e_SELF."?d");
					}							
				}
		}

		if($mode == "update")
		{
			if($type == "submit")
			{
				if(isset($_POST['content_refer']) && $_POST['content_refer']=='sa')
				{
					$refer = ", content_refer='' ";
				}
				else
				{
					$refer = "";
				}
			}
			else
			{
				if(isset($_POST['content_refer']) && $_POST['content_refer']=='sa')
				{
					$refer = ", content_refer='' ";
				}
				else
				{
					$refer = "";
				}
			}
			$sql -> db_Update($plugintable, "content_heading = '".$_POST['content_heading']."', content_subheading = '".$_POST['content_subheading']."', content_summary = '".$_POST['content_summary']."', content_text = '".$_POST['content_text']."', content_author = '".$tp->toDB($author)."', content_icon = '".$icon."', content_file = '".$totalattach."', content_image = '".$totalimages."', content_parent = '".$_POST['parent']."', content_comment = '".intval($_POST['content_comment'])."', content_rate = '".intval($_POST['content_rate'])."', content_pe = '".intval($_POST['content_pe'])."' ".$refer.", content_datestamp = '".$starttime."', content_enddate = '".$endtime."', content_class = '".$_POST['content_class']."', content_pref = '".$contentprefvalue."', content_score='".intval($_POST['content_score'])."', content_meta='".$_POST['content_meta']."', content_layout='".$_POST['content_layout']."' WHERE content_id = '".intval($_POST['content_id'])."' ");

				$e107cache->clear("$plugintable");
				$e107cache->clear("comment.$plugintable.{$_POST['content_id']}");
				if(!$type || $type == "admin"){
					js_location(e_SELF."?".e_QUERY.".cu");
				}elseif($type == "contentmanager"){
					js_location(e_SELF."?u");
				}
			}
		}


		//function dbCategoryUpdate($mode){
		function dbCategory($mode){
			global $pref, $sql, $ns, $qs, $rs, $aa, $tp, $plugintable, $e107cache, $content_cat_icon_path_large, $content_cat_icon_path_small;

			$_POST['cat_heading']		= $tp -> toDB($_POST['cat_heading']);
			$_POST['cat_subheading']	= $tp -> toDB($_POST['cat_subheading']);
			if(e_WYSIWYG){
				$_POST['cat_text']		= $tp->createConstants($_POST['cat_text']); // convert e107_images/ to {e_IMAGE} etc.
			}
			$_POST['cat_text']			= $tp -> toDB($_POST['cat_text']);
			$_POST['cat_class']			= ($_POST['cat_class'] ? intval($_POST['cat_class']) : "0");

			//category create
			if( isset($qs[0]) && $qs[0]=='cat' && isset($qs[1]) && $qs[1]=='create' ){
				if( isset($qs[2]) && is_numeric($qs[2]) ){
					$parent = "0.".intval($qs[2]);
				}else{
					$parent = 0;
				}

			//category edit
			}elseif( isset($qs[0]) && $qs[0]=='cat' && isset($qs[1]) && $qs[1]=='edit' ){
				if( isset($qs[2]) && is_numeric($qs[2]) ){

					if( isset($qs[3]) && is_numeric($qs[3]) ){
						if(intval($qs[3]) == 0){
							$parent = 0;
						}elseif( $qs[2] == $qs[3] ){
							$parent = 0;
						}else{
							$parent = "0.".intval($qs[3]);
						}
					}else{
						if($qs[2]==$_POST['cat_id']){
							$parent = intval($_POST['parent_id']);
							$parent = ($parent!=0 ? "0.".$parent : 0);
						}else{
						}
					}
				}else{
					$parent = 0;
				}
			}
			$_POST['parent'] = $parent;

			if( isset($_POST['ne_day']) && $_POST['ne_day']!='' && $_POST['ne_day']!='0' && $_POST['ne_day'] != "none" 
				&& isset($_POST['ne_month']) && $_POST['ne_month']!='' && $_POST['ne_month']!='0' && $_POST['ne_month'] != "none" 
				&& isset($_POST['ne_year']) && $_POST['ne_year']!='' && $_POST['ne_year']!='0' && $_POST['ne_year'] != "none" ){
				$starttime = mktime( 0, 0, 0, intval($_POST['ne_month']), intval($_POST['ne_day']), intval($_POST['ne_year']));
			}else{
				$starttime = time();
			}

			if( isset($_POST['end_day']) && $_POST['end_day']!='' && $_POST['end_day']!='0' && $_POST['end_day'] != "none" 
				&& isset($_POST['end_month']) && $_POST['end_month']!='' && $_POST['end_month']!='0' && $_POST['end_month'] != "none" 
				&& isset($_POST['end_year']) && $_POST['end_year']!='' && $_POST['end_year']!='0' && $_POST['end_year'] != "none" ){
				$endtime = mktime( 0, 0, 0, intval($_POST['end_month']), intval($_POST['end_day']), intval($_POST['end_year']));
			}else{
				$endtime = "0";
			}

			if($mode == "create"){
				$sql -> db_Insert($plugintable, "'0', '".$_POST['cat_heading']."', '".$_POST['cat_subheading']."', '', '".$_POST['cat_text']."', '".ADMINID."', '".$tp->toDB($_POST["cat_icon"])."', '', '', '".$_POST['parent']."', '".intval($_POST['cat_comment'])."', '".intval($_POST['cat_rate'])."', '".intval($_POST['cat_pe'])."', '', '".$starttime."', '".$endtime."', '".$_POST['cat_class']."', '', '0', '0', '', '' ");

				// check and insert default pref values if new main parent + create menu file
				if($_POST['parent'] == "0"){
					$iid = mysql_insert_id();
					$content_pref = $aa -> getContentPref($iid);
					$aa -> CreateParentMenu($iid);
				}
				$e107cache->clear("$plugintable");
				js_location(e_SELF."?".e_QUERY.".pc");

			}elseif($mode == "update"){
				$sql -> db_Update($plugintable, "content_heading = '".$_POST['cat_heading']."', content_subheading = '".$_POST['cat_subheading']."', content_summary = '', content_text = '".$_POST['cat_text']."', content_author = '".ADMINID."', content_icon = '".$tp->toDB($_POST["cat_icon"])."', content_image = '', content_parent = '".$_POST['parent']."', content_comment = '".intval($_POST['cat_comment'])."', content_rate = '".intval($_POST['cat_rate'])."', content_pe = '".intval($_POST['cat_pe'])."', content_refer = '0', content_datestamp = '".$starttime."', content_enddate = '".$endtime."', content_class = '".intval($_POST['cat_class'])."' WHERE content_id = '".intval($_POST['cat_id'])."' ");

				// check and insert default pref values if new main parent + create menu file
				if($_POST['parent'] == "0"){
					@unlink(e_PLUGIN."content/menus/content_".$_POST['menuheading']."_menu.php");
					$content_pref = $aa -> getContentPref($_POST['cat_id']);
					$aa -> CreateParentMenu($_POST['cat_id']);
				}
				$e107cache->clear("$plugintable");
				js_location(e_SELF."?".e_QUERY.".pu");
			}
		}


		function dbAssignAdmins($mode, $id, $value){
			global $plugintable, $qs, $sql, $eArrayStorage;

			if($mode == "admin"){
				$id = intval($id);
				$sql -> db_Select($plugintable, "content_pref", "content_id = '".intval($id)."' ");
				$row = $sql -> db_Fetch();

				//get current preferences
				$content_pref = $eArrayStorage->ReadArray($row['content_pref']);

				//assign new preferences
				if($value == "clear"){
					$content_pref["content_manager_allowed"] = "";
				}else{
					$content_pref["content_manager_allowed"] = $value;
				}
				
				//create new array of preferences
				$tmp = $eArrayStorage->WriteArray($content_pref);

				$sql -> db_Update($plugintable, "content_pref = '{$tmp}' WHERE content_id = '".intval($id)."' ");

				$message = CONTENT_ADMIN_CAT_LAN_34;
				return $message;
			}else{
				return FALSE;
			}
		}



		function dbDelete($mode, $cat, $del_id){
			global $plugintable, $sql, $_POST, $e107cache;

			if($mode == "admin"){
				if($cat == "cat"){
					if($sql -> db_Delete($plugintable, "content_id='".intval($del_id)."' ")){
						$e107cache->clear("$plugintable");
						$message = CONTENT_ADMIN_CAT_LAN_23;
						return $message;
					}
				}elseif($cat == "content"){
					if($sql -> db_Delete($plugintable, "content_id='".intval($del_id)."' ")){
						$e107cache->clear("$plugintable");
						$message = CONTENT_ADMIN_ITEM_LAN_3;
						return $message;
					}
				}
			}else{
				return FALSE;
			}						
		}


		
		function dbSetOrder($mode, $type, $order){
			global $plugintable, $sql, $aa, $qs, $_POST, $e107cache;
			//$mode		:	all, inc, dec
			//$type		:	cc (category order), ai (global all items), ci (items in category)
			//$order	:	posted values or id-currentorder

			if($mode == "all"){
				foreach ($order as $cid){
					//each order value in the db has two numbers (a-b) where a = category item order, and b = global item order
					//146.3.cat		:	category order
					//35.3.ci.1-0	:	category item order
					//35.3.ai.1-0	:	global item order
					
					$tmp		= explode(".", $cid);
					$old		= explode("-", $tmp[3]);
					$old[0]		= ($old[0] == "" ? "0" : $old[0]);
					$old[1]		= ($old[1] == "" ? "0" : $old[1]);	
					
					$tmp[0]		= intval($tmp[0]);
					$tmp[1]		= intval($tmp[1]);
					$old[0]		= intval($old[0]);
					$old[1]		= intval($old[1]);

					if($tmp[2] == "cat"){
						$sql->db_Update($plugintable, "content_order='".$tmp[1]."' WHERE content_id='".$tmp[0]."' " );

					}elseif($tmp[2] == "ci"){
						$sql->db_Update($plugintable, "content_order='".$tmp[1].".".$old[1]."' WHERE content_id='".$tmp[0]."' " );

					}elseif($tmp[2] == "ai"){
						$sql->db_Update($plugintable, "content_order='".$old[0].".".$tmp[1]."' WHERE content_id='".$tmp[0]."' " );
					}
					$message = CONTENT_ADMIN_ORDER_LAN_2;
				}

			}elseif($mode == "inc"){

				$tmp = explode("-", $order);
				$tmp[0]		= intval($tmp[0]);
				$tmp[1]		= intval($tmp[1]);
				$tmp[2]		= intval($tmp[2]);

				if($type == "cc"){
					$mainparent		= $aa -> getMainParent($tmp[0]);
					$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
					$validparent	= implode(",", array_keys($array));
					$qry			= " content_id REGEXP '".$aa -> CONTENTREGEXP($validparent)."' AND content_order='".($tmp[1]-1)."' ";
					$sql->db_Update($plugintable, "content_order=content_order+1 WHERE ".$qry." " );
					$sql->db_Update($plugintable, "content_order=content_order-1 WHERE content_id='".$tmp[0]."' " );

				}elseif($type == "ci"){
					$sql->db_Update($plugintable, "content_order='".$tmp[1].".".$tmp[2]."' WHERE content_parent = '".intval($qs[2])."' AND SUBSTRING_INDEX(content_order, '.', 1) = '".($tmp[1]-1)."' " );
					$sql->db_Update($plugintable, "content_order='".($tmp[1]-1).".".$tmp[2]."' WHERE content_id='".$tmp[0]."' " );

				}elseif($type == "ai"){
					$array			= $aa -> getCategoryTree("", intval($qs[1]), TRUE);
					$validparent	= implode(",", array_keys($array));
					$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' AND SUBSTRING_INDEX(content_order, '.', -1) = '".($tmp[2]-1)."' ";
					$sql->db_Update($plugintable, " content_order=content_order+0.1 WHERE ".$qry." " );
					$sql->db_Update($plugintable, "content_order='".$tmp[1].".".($tmp[2]-1)."' WHERE content_id='".$tmp[0]."' " );

				}
				$message = CONTENT_ADMIN_ORDER_LAN_0;

			}elseif($mode == "dec"){

				$tmp = explode("-", $order);
				$tmp[0]		= intval($tmp[0]);
				$tmp[1]		= intval($tmp[1]);
				$tmp[2]		= intval($tmp[2]);
				if($type == "cc"){
					$mainparent		= $aa -> getMainParent($tmp[0]);
					$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
					$validparent	= implode(",", array_keys($array));
					$qry			= " content_id REGEXP '".$aa -> CONTENTREGEXP($validparent)."' AND content_order='".($tmp[1]+1)."' ";
					$sql->db_Update($plugintable, "content_order=content_order-1 WHERE ".$qry." " );
					$sql->db_Update($plugintable, "content_order=content_order+1 WHERE content_id='".$tmp[0]."' " );

				}elseif($type == "ci"){
					$sql->db_Update($plugintable, "content_order='".$tmp[1].".".$tmp[2]."' WHERE content_parent = '".intval($qs[2])."' AND SUBSTRING_INDEX(content_order, '.', 1) = '".($tmp[1]+1)."' " );
					$sql->db_Update($plugintable, "content_order='".($tmp[1]+1).".".$tmp[2]."' WHERE content_id='".$tmp[0]."' " );

				}elseif($type == "ai"){
					$array			= $aa -> getCategoryTree("", intval($qs[1]), TRUE);
					$validparent	= implode(",", array_keys($array));
					$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' AND SUBSTRING_INDEX(content_order, '.', -1) = '".($tmp[2]+1)."' ";
					$sql->db_Update($plugintable, "content_order=content_order-0.1 WHERE ".$qry." " );
					$sql->db_Update($plugintable, "content_order='".$tmp[1].".".($tmp[2]+1)."' WHERE content_id='".$tmp[0]."' " );
				}
				$message = CONTENT_ADMIN_ORDER_LAN_1;
			}
			$e107cache->clear("$plugintable");
			return $message;
		}
}

?>