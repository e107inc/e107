<?php
$mph = new manualparse;
class manualparse{

	var $backend;
	var $cache;
	var $retrieve;
	var $contents;
	var $timeout;

	function manualparse(){

		$sql = new db;
		$sql2 = new db;
		$sql3 = new db;
		if(!$sql3 -> db_Select("headlines", "*", "headline_active='1' ")){
			return;
		}
		

		while(list($headline_id, $headline_url, $headline_data, $headline_timestamp, $headline_description, $headline_webmaster, $headline_copyright, $headline_tagline, $headline_image, $headline_active) = $sql3-> db_Fetch()){
		$nc = "";
		$headline_update = 3600;
		$ts = $headline_timestamp;
		if($headline_timestamp+$headline_update < time()){
			$url = parse_url($headline_url);
			if(!$headline_url){ return FALSE; }
			$remote = fsockopen ($url['host'], 80 ,$errno, $errstr, 20);
			if(!$remote || ereg("php", $headline_url)){
				$remote = fopen ($headline_url, "r");
			}
			fputs($remote, "GET ".$headline_url." HTTP/1.0\r\n\r\n");
			$c=0;
			while (!feof($remote)){
				$nc .= fgets ($remote,128);
				$c++;
			}
			fclose ($remote);
			
			unset($title, $description, $url, $editor, $copyright, $webmaster, $link);
			$line = explode("\n", $nc);
			for($c=0; $c<count($line); $c++){
				if(eregi("<description>(.*)", $line[$c], $token) && $description == ""){
					$description = ereg_replace("</description>.*", "", $token[1]);
				}
				if(eregi("<url>(.*)", $line[$c], $token)){
					$url = ereg_replace("</url>.*", "", $token[1]);
				}
				if(eregi("<managingEditor>(.*)", $line[$c], $token)){
					$editor = ereg_replace("</managingEditor>.*", "", $token[1]);
				}
				if(eregi("<copyright>(.*)", $line[$c], $token)){
					$copyright = ereg_replace("</copyright>.*", "", $token[1]);
				}
				if(eregi("<webMaster>(.*)", $line[$c], $token)){
					$webmaster = ereg_replace("</webMaster>.*", "", $token[1]);
				}
				if(eregi("<title>(.*)", $line[$c], $token)){
					$title[] = ereg_replace("</title>.*", "", $token[1]);
				}
				if(eregi("<link>(.*)", $line[$c], $token)){
					$link[] = ereg_replace("</link>.*", "", $token[1]);
				}
			}
			if($headline_image == "none"){
				$nc = "<a href=\"".$link[0]."\"><b>".$title[0]."</b></a><br />";
			}else{
				if($headline_image == ""){
					if($url == ""){
						$nc = "<a href=\"".$link[0]."\"><b>".$title[0]."</b></a><br />";
					}else{
						$nc = "<a href=\"".$link[0]."\"><img src=\"".$url."\" alt=\"\" style=\"border:0\" /></a><br />";
					}
				}else{
					$nc = "<a href=\"".$link[0]."\"><img src=\"".$headline_image."\" alt=\"\" style=\"border:0\" /></a><br />";	
				}
			}
			if($headline_description == 1){
				$nc .= "<span class=\"defaulttext\">[".$description."]</span><br />";
			}

			if($headline_webmaster == 1){	 
				$nc .= "<span class=\"defaulttext\">".NFMENU_160."".$webmaster."</span><br />";
			}

			if($headline_copyright == 1){
				$nc .= "<span class=\"defaulttext\"><i>[".$copyright."]</i></span><br />";
			}
			$nc .= "<span class=\"smalltext\">";
			$c=1;
			while($title[$c]){

				if($title[$c] != $title[0]){
					$nc .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"*\" /> <a href=\"".$link[$c]."\">".$title[$c]."</a><br />";
				}
				$c++;
			}

			$nc .= "</span>";
			$timestamp = time();
			$nc=addslashes($nc);
			$sql -> db_Update ("headlines", "headline_data='$nc', headline_timestamp='$timestamp' WHERE headline_id='$headline_id' ");
		}else{
			$nc = $headline_data;
		}
		
	$text .= $nc."<br />";
	}

	$nc = new convert;
	$datestamp = $nc->convert_date($ts, "short");
	$text .= "<span class=\"smalltext\">Last updated: ".$datestamp."</span>";
	$text = "<div style=\"text-align:center\"><table style=\"width:95%\" cellspacing=\"1\"><tr><td>".$text."</td></tr></table></div>";
	$ns = new e107table;

	$text = eregi_replace("<img src=\"themes" ,"<img src=\"".e_BASE."themes", $text);


	$ns -> tablerender(NFMENU_161, stripslashes($text));
	}
}
?>