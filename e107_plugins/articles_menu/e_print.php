<?php

		$qs = explode(".", e_QUERY);
		if($qs[0] == ""){ header("location:".e_BASE."index.php"); exit;}
		$table = $qs[0];
		$type_id = $qs[1];
		$id = $qs[2];

        require_once(e_PLUGIN."content/handlers/content_class.php");
		$aa = new content;
		
		$sql -> db_Select($plugintable, "content_id, content_heading, content_subheading, content_text, content_author, content_datestamp, content_class", "content_id='$id' ");
        $row = $sql -> db_Fetch();
        extract($row);

		if(!check_class($content_class)){
			header("location:".e_PLUGIN."content/content.php"); exit;
		}
		
		$content_heading = $aj -> tpa($content_heading);
        $content_subheading = $aj -> tpa($content_subheading);
        $content_text = ereg_replace("\{EMAILPRINT\}|\[newpage\]", "", $aj -> tpa($content_text));
		$authordetails = $aa -> getAuthor($content_author);
        $content_datestamp = $con -> convert_date($content_datestamp, "long");

        $text = "
		<font style=\"FONT-SIZE: 11px; COLOR: black; FONT-FAMILY: Tahoma, Verdana, Arial, Helvetica; TEXT-DECORATION: none\">
        <b>".$content_heading."</b>
        <br />
        ".$content_subheading."
        <br />
        ".$authordetails[1].", ".$content_datestamp."
        <br /><br />".
        $content_text."
        <br /><br /><hr />
        this content item is from ".SITENAME."
        <br />
        ( http://".$_SERVER[HTTP_HOST].e_HTTP.e_PLUGIN."content/content.php?type.".$type_id.".content.".$content_id." )
        </font>";

?>