<?php

	global $sql;
	if(!$content_install = $sql -> db_Select("plugin", "*", "plugin_path = 'content' AND plugin_installflag = '1' ")){
		return;
	}

	global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
	global $RECENT_ICON, $RECENT_DATE, $HEADING, $RECENT_AUTHOR, $CATEGORY, $RECENT_INFO;
	global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

	require_once(e_PLUGIN."content/handlers/content_class.php");
	$aa = new content;

	$plugintable = "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
	$datequery = " AND (content_datestamp=0 || content_datestamp < ".time().") AND (content_enddate=0 || content_enddate>".time().") ";

	//get main parent types
	$sqlm = new db;
	if(!$mainparents = $sqlm -> db_Select($plugintable, "*", "content_parent = '0' ".$datequery." ORDER BY content_heading")){
		$RECENT_CAPTION = "content";
		$RECENT_DATA = "no content yet";
	}else{
		while($rowm = $sqlm -> db_Fetch()){

			//global var for this main parent
			$type_id = $rowm['content_id'];
			$type_id_recent = $rowm['content_id'];

			//get path variables
			$content_recent_pref = $aa -> getContentPref($type_id_recent);
			$content_recent_pref["content_icon_path_{$type_id_recent}"] = ($content_recent_pref["content_icon_path_{$type_id_recent}"] ? $content_recent_pref["content_icon_path_{$type_id_recent}"] : "{e_PLUGIN}content/images/icon/" );
			$content_icon_path = $aa -> parseContentPathVars($content_recent_pref["content_icon_path_{$type_id_recent}"]);

			//get unvalid content (check class and date)
			$unvalidcontent = $aa -> checkMainCat($type_id_recent);
			$unvalidcontent = ($unvalidcontent == "" ? "" : "AND ".substr($unvalidcontent, 0, -3) );

			//check so only the preferences from the correct content_type (article, content, review etc) are used and rendered
			if($arr[9] == $rowm['content_heading']){
				//get recent content for each main parent
				$sqli = new db;
				if($resultitem = $sqli -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_summary, content_text, content_author, content_icon, content_file, content_image, content_parent, content_comment, content_rate, content_pe, content_refer, content_datestamp, content_class", "content_refer !='sa' AND LEFT(content_parent,".(strlen($type_id_recent)).") = '".$type_id_recent."' ".$unvalidcontent." ".$datequery." AND content_class IN (".USERCLASS_LIST.") ORDER BY content_datestamp DESC LIMIT 0,".$arr[7]." ")){
					
					$RECENT_CAPTION = $rowm['content_heading'];
					$RECENT_DISPLAYSTYLE = ($arr[2] ? "" : "none");

					while($rowi = $sqli -> db_Fetch()){
					
						$rowheading = $this -> parse_heading($rowi['content_heading'], $mode);

						$HEADING = "<a href='".e_PLUGIN."content/content.php?type.".$type_id_recent.".content.".$rowi['content_id']."' title='".$rowi['content_heading']."'>".$rowheading."</a>";
						$CATEGORY = ($arr[4] ? $aa -> getCat($rowi['content_parent']) : "");
						$DATE = ($arr[5] ? $this -> getRecentDate($rowi['content_datestamp'], $mode) : "");

						//if($rowi['content_icon']){
						//	$ICON = "<img src='".$content_icon_path.$rowi['content_icon']."' style='padding-bottom:2px; width:25px; border:0; vertical-align:middle;' alt='' />";
						//}else{
							$ICON = $this -> getBullet($arr[6], $mode);
						//}

						//get author details
						if($arr[3]){
							$authordetails = $aa -> getAuthor($rowi['content_author']);
							if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0"){
								$AUTHOR = "<a href='".e_BASE."user.php?id.".$authordetails[0]."' >".$authordetails[1]."</a>";
							}else{
								$AUTHOR = $authordetails[1];
							}
						}else{
							$AUTHOR = "";
						}
						$INFO = "";

						$RECENT_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
					}
				}
			}
		}
	}


?>