<?php

if (!defined('e107_INIT')) { exit; }

function print_item($id)
{
		global $tp;
		global $content_pref, $mainparent, $aa, $row, $content_image_path;
		$con = new convert;

		require_once(e_PLUGIN."content/handlers/content_class.php");
		$aa = new content;

		require_once(e_PLUGIN."content/content_shortcodes.php");

		$lan_file = e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content.php";
		include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."content/languages/English/lan_content.php");

		if(!is_object($sql)){ $sql = new db; }
		$sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_image, content_parent, content_datestamp, content_class", "content_id='".intval($id)."' ");
		$row = $sql -> db_Fetch();

		if(!check_class($row['content_class'])){
			header("location:".e_PLUGIN."content/content.php"); exit;
		}
		$row['content_heading']		= $tp -> toHTML($row['content_heading']);
		$row['content_subheading']	= $tp -> toHTML($row['content_subheading']);
		$row['content_text']		= $tp -> replaceConstants($row['content_text']);
		$row['content_text']		= preg_replace("/\{EMAILPRINT\}|\[newpage\]/", "", $tp -> toHTML($row['content_text'], TRUE));
		$authordetails				= $aa -> getAuthor($row['content_author']);
		$row['content_datestamp']	= $con -> convert_date($row['content_datestamp'], "long");

		$mainparent					= $aa -> getMainParent($id);
		$content_pref				= $aa -> getContentPref($mainparent);
		$content_icon_path			= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
		$content_image_path			= $tp -> replaceConstants($content_pref["content_image_path_{$mainparent}"]);
		$img						= $tp -> parseTemplate('{CONTENT_PRINT_IMAGES}', FALSE, $content_shortcodes);

		$text = "
		<b>".$row['content_heading']."</b>
		<br />
		".$row['content_subheading']."
		<br />
		".$authordetails[1].", ".$row['content_datestamp']."
		<br /><br />
		<div style='float:left; padding-right:10px;'>".$img."</div>
		".$row['content_text']."
		<br /><br /><hr />
		".CONTENT_EMAILPRINT_LAN_1." ".SITENAME."
		<br />
		( ".SITEURLBASE.e_PLUGIN_ABS."content/content.php?content.".$row['content_id']." )        
		";

		require_once(e_HANDLER.'bbcode_handler.php');
		$e_bb = new e_bbcode;
		$text = $e_bb->parseBBCodes($text, '');

		return $text;
}

function email_item($id)
{
	$plugintable = "pcontent";
	if(!is_object($sql)){ $sql = new db; }
	if($sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_parent, content_datestamp, content_class", "content_id='".intval($id)."' ")){
		while($row = $sql -> db_Fetch()){
			$tmp = explode(".",$row['content_parent']);
			if(!check_class($row['content_class'])){
				header("location:".e_PLUGIN."content/content.php"); exit;
			}
			$message = SITEURL.e_PLUGIN."content/content.php?content.".$id."\n\n".$row['content_heading']."\n".$row['content_subheading']."\n";
		}
		return $message;
	}
}


function print_item_pdf($id){
	global $tp;
			
	//in this section you decide what to needs to be output to the pdf file
	$con = new convert;

	require_once(e_PLUGIN."content/handlers/content_class.php");
	$aa = new content;
	
	if(!is_object($sql)){ $sql = new db; }
	$sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_parent, content_datestamp, content_class", "content_id='".intval($id)."' ");
	$row = $sql -> db_Fetch();

	if(!check_class($row['content_class'])){
		header("location:".e_PLUGIN."content/content.php"); exit;
	}
	$authordetails				= $aa -> getAuthor($row['content_author']);
	$row['content_datestamp']	= $con -> convert_date($row['content_datestamp'], "long");

	$text = "
	<b>".$row['content_heading']."</b><br />
	".$row['content_subheading']."<br />
	".$authordetails[1].", ".$row['content_datestamp']."<br />
	<br />
	".$row['content_text']."<br />
	";

	//the following defines are processed in the document properties of the pdf file

	//Do NOT add parser function to the variables, leave them as raw data !
	//as the pdf methods will handle this !
	$text		= $text;							//define text
	$creator	= SITENAME;						//define creator
	$author		= $authordetails[1];				//define author
	$title		= $row['content_heading'];		//define title
	$subject	= $row['content_subheading'];	//define subject
	$keywords	= "";												//define keywords

	//define url and logo to use in the header of the pdf file
	//$url		= SITEURL.$PLUGINS_DIRECTORY."content/content.php?content.".$row['content_id'];
	$url		= SITEURLBASE.e_PLUGIN_ABS."content/content.php?content.".$row['content_id'];
	define('CONTENTPDFPAGEURL', $url);								//define page url to add in header

	if(file_exists(THEME."images/logopdf.png")){
		$logo = THEME."images/logopdf.png";
	}else{
		$logo = e_IMAGE."logo.png";
	}
	define('CONTENTPDFLOGO', $logo);								//define logo to add in header

	//always return an array with the following data:
	return array($text, $creator, $author, $title, $subject, $keywords, $url);
}

?>