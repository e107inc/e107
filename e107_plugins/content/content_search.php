<?php
$plugintable = "pcontent";
$search_info[$key]['qtype'] = "content";

if($results = $sql -> db_Select($plugintable, "*", "content_heading REGEXP('".$query."') OR content_subheading REGEXP('".$query."') OR content_summary REGEXP('".$query."') OR content_text REGEXP('".$query."') ")){
	while($row = $sql -> db_Fetch()){
	extract($row);
		$content_heading = parsesearch($content_heading, $query);
		$content_subheading = parsesearch($content_subheading, $query);
		$content_summary = parsesearch($content_summary, $query);
		$content_text = parsesearch($content_text , $query);

		$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_PLUGIN."content/content.php?".$content_id."'>".$content_heading."</a><br />
		".$content_author."<br />".$content_text."<br />";
	}
}else{
	$text .= "No matches.";
}

?>