<?php

function print_item($id)
{
		global $tp;
		$con = new convert;

		require_once(e_PLUGIN."content/handlers/content_class.php");
		$aa = new content;
		
		if(!is_object($sql)){ $sql = new db; }
		$sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_parent, content_datestamp, content_class", "content_id='$id' ");
		$row = $sql -> db_Fetch();
		extract($row);

		if(!check_class($content_class)){
			header("location:".e_PLUGIN."content/content.php"); exit;
		}
		$content_heading = $tp -> toHTML($content_heading);
		$content_subheading = $tp -> toHTML($content_subheading);
		$content_text = ereg_replace("\{EMAILPRINT\}|\[newpage\]", "", $tp -> toHTML($content_text, TRUE));
		$authordetails = $aa -> getAuthor($content_author);
		$content_datestamp = $con -> convert_date($content_datestamp, "long");
		$tmp = explode(".",$content_parent);
		$type_id = ($tmp[0] == "0" ? $tmp[1] : $tmp[0]);

		$text = "
		<b>".$content_heading."</b>
		<br />
		".$content_subheading."
		<br />
		".$authordetails[1].", ".$content_datestamp."
		<br /><br />
		".$content_text."
		<br /><br /><hr />
		this content item is from ".SITENAME."
		<br />
		( http://".$_SERVER[HTTP_HOST].e_HTTP.e_PLUGIN."content/content.php?type.".$type_id.".content.".$content_id." )        
		";

		return $text;
}

function email_item($id)
{
	$plugintable = "pcontent";
	if(!is_object($sql)){ $sql = new db; }
	if($sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_parent, content_datestamp, content_class", "content_id='$id' ")){
		while($row = $sql -> db_Fetch()){
		extract($row);
			$tmp = explode(".",$content_parent);
			$type_id = ($tmp[0] == "0" ? $tmp[1] : $tmp[0]);
			if(!check_class($content_class)){
				header("location:".e_PLUGIN."content/content.php?type.".$type_id); exit;
			}
			$message = SITEURL.e_PLUGIN."content/content.php?type.".$type_id.".content.".$id."\n\n".$content_heading."\n".$content_subheading."\n";
		}
		return $message;
	}
}

?>