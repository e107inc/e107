<?php

// ##### give $email and $print a value : the value would be the same and used exclusively for your plugin
$email = "pcontent";
$print = "pcontent";

//method: include this handler in your file from where you want to be able to email/print content
	//require_once(e_HANDLER."emailprint_class.php");
	//$ep = new emailprint;
//calling: use this methode to call the email and print icons
	//$ep -> render_emailprint($plugintable, $content_id, "0");
	//0=both email and print icons; 1=email only; 2=print only

// ##### EMAIL ROUTINE ----------------------------------------------------------------------------
if(eregi('email.php', e_SELF)){

		//create your own [$message .= "blah"] text to be used in the email that will be send
		//$id is inherited from the email.php file and holds the unique id from the table
		
		$sql = new db;
		if($sql -> db_Select($table, "content_id, content_heading, content_subheading, content_text, content_author, content_parent, content_datestamp, content_class", "content_id='$id' ")){
			while($row = $sql -> db_Fetch()){
				extract($row);

				$tmp = explode(".",$content_parent);
				$type_id = ($tmp[0] == "0" ? $tmp[1] : $tmp[0]);

				//just create your message here, this will be added to the already existing $message var, so start with [$message .= "blah"] and not with [$message = "blah"] !
				$message .= SITEURL.e_PLUGIN."content/content.php?type.".$type_id.".content.".$id."\n\n";
				$message .= $content_heading."\n";
				$message .= $content_subheading."\n";
			}
		}
}


// ##### PRINT ROUTINE ----------------------------------------------------------------------------
if(eregi('print.php', e_SELF)){
		
		//create your own print page layout with the content you need
		//use $text = "blah" and have it contain a full html page as it will be echo'ed out
		//$id is inherited from the email.php file and holds the unique id from the table

		global $tp;
		$con = new convert;

		require_once(e_PLUGIN."content/handlers/content_class.php");
		$aa = new content;
		
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
		<html>
		<head>
		<style type='text/css'>
		body{ font-size:11px; color: #000; font-family: Tahoma, Verdana, Arial, Helvetica; text-decoration: none; }
		table tr td{ font-size:11px; color: #000; font-family: Tahoma, Verdana, Arial, Helvetica; text-decoration: none; }
		</style>
		</head>
		<body>        
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
		</body>
		</html>";
}

?>