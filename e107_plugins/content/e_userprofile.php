<?php

if(!function_exists('e_userprofile_content')){
	function e_userprofile_content(){
		global $qs, $sql, $tp;

		include_lan(e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content.php");
		require_once(e_PLUGIN.'content/handlers/content_class.php');
		$aa = new content;

		$userid=intval($qs[1]);

		$caption = array();
		$data = array();

		//get main parent types
		$sqlm = new db;
		if($sqlm -> db_Select("pcontent", "*", "content_class REGEXP '".e_CLASS_REGEXP."' AND content_parent = '0' ".$datequery." ".$headingquery." ORDER BY content_heading")){
			while($rowm = $sqlm -> db_Fetch()){
				//global var for this main parent
				$mainparent = $rowm['content_id'];
				$maincaption = $rowm['content_heading'];

				$text = '';

				//prepare query paramaters
				$array				= $aa -> getCategoryTree("", $mainparent, TRUE);
				$validparent		= implode(",", array_keys($array));
				$qry				= " p.content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' ";
				$datequery			= " AND p.content_datestamp < ".time()." AND (p.content_enddate=0 || p.content_enddate>".time().") ";
				$content_pref		= $aa -> getContentPref($mainparent);
				$content_icon_path	= $tp -> replaceConstants($content_pref["content_icon_path"]);
				$l = strlen($userid)+1;
				$userquery			= " AND (p.content_author = '".$userid."' || LEFT(p.content_author, ".$l.") = '".$userid."^' OR SUBSTRING_INDEX(p.content_author, '^', 1) = '".$userid."' ) ";

				$qry = "
				SELECT p.content_id, p.content_heading, p.content_subheading, p.content_icon, p.content_datestamp
				FROM #pcontent AS p
				WHERE LEFT(p.content_parent,1) != '0' 
				AND ".$qry." ".$datequery." 
				AND p.content_class REGEXP '".e_CLASS_REGEXP."' 
				".$userquery." 
				ORDER BY p.content_heading ";
				$qry1 = $qry." LIMIT 0,3";

				$found=false;
				$sqlc = new db;
				$total = $sqlc -> db_Select_gen($qry);
				if($sqlc -> db_Select_gen($qry1)){

					while($rowc = $sqlc -> db_Fetch()){

						$icon='';
						if($rowc['content_icon'] && is_readable($content_icon_path.$rowc['content_icon'])){
							$icon = "<a href='".e_PLUGIN."content/content.php?content.".$rowc['content_id']."' ><img src='".$content_icon_path.$rowc['content_icon']."' style='width:50px; height:50px;' alt='' /></a>";
						}else{
							$icon = "<a href='".e_PLUGIN."content/content.php?content.".$rowc['content_id']."' ><img src='".$content_icon_path."blank.gif' style='width:50px; height:50px;' alt='' /></a>";
						}

						$date = strftime("%d %b %Y", $rowc['content_datestamp']);
						$subheading = ($row['content_subheading'] ? $row['content_subheading']."<br />" : '');

						$text .= "
						<div style='clear:both; padding-bottom:10px;'>
							<div style='float:left; padding-bottom:10px;'>".$icon."</div> 
							<div style='margin-left:60px; padding-bottom:10px;'>
								<a href='".e_PLUGIN."content/content.php?content.".$rowc['content_id']."'>".$tp->toHTML($rowc['content_heading'], TRUE, "")."</a><br />
								".$subheading."
								<span class='smalltext'>".$date."</span>
							</div>
						</div>";

						$id = $rowc['content_id'];
						$found=true;
					}
					$lan0 = str_replace('{caption}',$maincaption, CONTENT_USERPROFILE_LAN_1);
					$text .= "<div style='clear:both; padding-bottom:10px;'><a href='".e_PLUGIN."content/content.php?author.".$id."'>".$lan0."</a></div>";
				}
				if($found){
					$caption[] = str_replace(array('{caption}','{total}'),array($maincaption, $total), CONTENT_USERPROFILE_LAN_2);
					$data[] = $text;
				}
			}
			return array('caption'=>$caption, 'text'=>$data);
		}
	}
}

?>