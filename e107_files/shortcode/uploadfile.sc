// $Id: uploadfile.sc,v 1.1 2006-06-29 22:15:28 e107coders Exp $

// Your <form> tag must include: enctype='multipart/form-data' - in order to work.
// parm is the path to the upload folder.

if($parm && !is_readable($parm))
{
	return LAN_UPLOAD_777." <b>".str_replace("../","",$parm)."</b>";
}

$name = "file_userfile[]";

$text .="
        <!-- Upload Shortcode -->
		<div style='width:90%'><div id='up_container' >
		<span id='upline' style='white-space:nowrap'>
		<input class='tbox' type='file' name='$name' size='40' />
        </span><br />
		</div>
		<span style='float:left'><input type='button' class='button' value=\"".LAN_UPLOAD_ADDFILE."\" onclick=\"duplicateHTML('upline','up_container');\"  /></span>
		<span style='float:right'><input class='button' type='submit' name='uploadfiles' value=\"".LAN_UPLOAD_FILES."\" onclick=\"return jsconfirm('".LAN_UPLOAD_CONFIRM."')\" /></span>
		</div>
		<!-- End Upload Shortcode -->
	";

return $text;