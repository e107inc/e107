<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/userfile.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).	
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

if(IsSet($_POST['upload'])){

	extract($_POST);

	if($sender && $email && $filename && $description){

		$aj = new textparse;
		$description = $aj -> tp($description, "off");
		$datestamp = time();

		$path_to_file = "upload/".$_POST['path'];
		$files = $HTTP_POST_FILES['files'];
		if(!ereg("/$", $path_to_file)){
			$path_to_file = $path_to_file."/";
		}
		foreach ($files['name'] as $key=>$name){
			if($files['size'][$key]){
				$name = ereg_replace("[^a-z0-9._]", "", str_replace(" ", "_", str_replace("%20", "_", strtolower($name))));
				$location = $path_to_file.$name;
				if($mainfile == ""){
					$mainfile = $location;
				}else{
					$imagefile = $location;
				}
				copy($files['tmp_name'][$key],$location);
				unlink($files['tmp_name'][$key]);
			}
		}

		$sql -> db_Insert("userfile", " 0, '$sender', '$email', '$website', '$datestamp', '$filename', '$mainfile', '$imagefile', '$description', 0 ");

		$caption = "File Uploaded";
		$text = "<div style=\"text-align:center\"><b>Thankyou!</b>.<br />Your upload will be reviewed by a site administrator and will be made available if appropriate.</div>";
		

	}else{

		$caption = "Error!";
		$text = "<div style=\"text-align:center\">You left field(s) blank - please re-submit</div>";
	}
	$ns -> tablerender($caption, $text);
}


if(USER == TRUE){

	$sql -> db_Select("user", "*", "user_id='".USERID."'");
	$row = $sql -> db_Fetch();
	extract($row);
	$email = $user_email;
	$website = $user_website;
	$sender = USERNAME;
}

$text = "<div style=\"text-align:center\">
All fields are required except for image and website, maximum filesize is 0.5mb, anything deemed not suitable will be immediately deleted.
</div>
<br />
<br />


<form enctype=\"multipart/form-data\" action=\"$PHP_SELF\" method=\"post\">
<table style=\"width:95%\">

<tr> 
<td style=\"width:30%\">Your name/nick: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"sender\" size=\"10\" value=\"$sender\" maxlength=\"100\" />
</td>
</tr>

<tr> 
<td style=\"width:30%\">Your email address: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"email\" size=\"50\" value=\"$email\" maxlength=\"150\" />
</td>
</tr>

<tr> 
<td style=\"width:30%\">Your website: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"website\" size=\"50\" value=\"$website\" maxlength=\"150\" />
</td>
</tr>

<tr> 
<td style=\"width:30%\">Name of file: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"filename\" size=\"50\" value=\"$filename\" maxlength=\"150\" />
</td>
</tr>

<tr> 
<td style=\"width:20%\">Upload folder: </td>
<td style=\"width:80%\">
<select name=\"path\" class=\"tbox\">";

$handle=opendir("upload/");
while ($file2 = readdir($handle)){	
	if($file2 != "." && $file2 != ".." && !stristr($file2,'.')){
			$text .= "<option>".$file2."</option>";
		
	}
}
closedir($handle);

$text .= "</select> 

</td>
</tr>

<tr> 
<td style=\"width:20%\">File: </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"file\" name=\"files[]\" size=\"50\">
</td>
</tr>

<tr> 
<td style=\"width:30%\">Image/screenshot?: </td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"file\" name=\"files[]\" size=\"50\">
</td>
</tr>

<tr> 
<td style=\"width:30%\">Description: </td>
<td style=\"width:70%\">
<textarea class=\"tbox\" name=\"description\" cols=\"64\" rows=\"8\">$description</textarea>
</td>
</tr>


<tr>
<td colspan=\"2\" style=\"text-align:center\">
<br />
<input class=\"button\" type=\"submit\" name=\"upload\" value=\"Upload File\" />
<br />

</td>
</tr>
</table>
<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"500000\">
</form>";



$ns -> tablerender("Upload File", $text);











require_once(FOOTERF);
?>