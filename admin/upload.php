<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/upload.php														|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(ADMINPERMS != 0 && ADMINPERMS != 1 && ADMINPERMS != 2){
	header("location:../index.php");
}
require_once("auth.php");

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

if(IsSet($_POST['upload'])){

	if($_POST['resize'] == 1){

		if(!extension_loaded('gd')){
			if(!dl('gd.so')){
				$text = "It appears you do not have the gd extention loaded so the image cannot be resized. You will have to resize the image manually and upload it seperately until your webadmin upgrades your extentions. (The library is available from <a href=\"http://www.boutell.com/gd)\">http://www.boutell.com/gd/</a>)";
				$ns -> tablerender("<div style=\"text-align:center\">Error!</div>", $text);
				require_once("footer.php");
				exit;
			}
		}

		$file = ereg_replace(" ", "", $_FILES['userfile']['name']);
		$file_extention = strtolower(substr(strrchr($file, "."), 1));
		$file_name = eregi_replace(".".$file_extention, "", $file);

		$large = $_POST['path']."/".$_FILES['userfile']['name'];
		$small = $_POST['path']."/".$file_name.".thumb.".$file_extention;
		
		copy($userfile, $large);
		copy($userfile, $small);

		if($file_extention == "jpg"){
			$image = imagecreatefromjpeg("$small");
			$imagex = imagesx($image);
			$imagey = imagesy($image);
			$newx = substr($coords, 0, strpos($coords, "x"));
			$newy = substr(strrchr($coords, "x"), 1);
			$destimage = imagecreate($newx,$newy);
			imagecopyresized($destimage, $image, 0,   0,   0,   0, $newx, $newy, $imagex, $imagey); 
			imagejpeg($destimage,$small);
		}else if($file_extention == "gif"){
			if(!@$image = imagecreatefromgif("$small")){
				echo  "GIF support not compiled into your GD library - please use a different file type.";
				$ns -> tablerender("<div style=\"text-align:center\">Error!</div>", $text);
				require_once("footer.php");
				exit;
			}
			$imagex = imagesx($image);
			$imagey = imagesy($image);
			$newx = substr($coords, 0, strpos($coords, "x"));
			$newy = substr(strrchr($coords, "x"), 1);
			$destimage = imagecreate($newx,$newy);
			imagecopyresized($destimage, $image, 0,   0,   0,   0, $newx, $newy, $imagex, $imagey); 
			imagegif($destimage,$small);
		}else if($file_extention == "png"){
			$image = imagecreatefrompng("$small");
			$imagex = imagesx($image);
			$imagey = imagesy($image);
			$newx = substr($coords, 0, strpos($coords, "x"));
			$newy = substr(strrchr($coords, "x"), 1);
			$destimage = imagecreate($newx,$newy);
			imagecopyresized($destimage, $image, 0,   0,   0,   0, $newx, $newy, $imagex, $imagey); 
			imagepng($destimage,$small);
		}

		$text = "Image <b>".$_FILES['userfile']['name']."</b> uploaded to ".$path." directory, image <b>".$_POST['path']."/".$file_name.".thumb.".$file_extention."</b> created and resized to ".$newx."x".$newy.".";
		$ns -> tablerender("File uploaded", $text);
	}else{
		//no resize so just upload file

		$file = ereg_replace(" ", "", $_FILES['userfile']['name']);
		$file_extention = strtolower(substr(strrchr($file, "."), 1));
		$file_name = eregi_replace(".".$file_extention, "", $file);

		$large = $_POST['path']."/".$_FILES['userfile']['name'];

		copy($_FILES['userfile']['tmp_name'], $large); // fix by db 15.10.02
		$text = "File <b>".$_FILES['userfile']['name']."</b> uploaded to ".$path." directory.";
		$ns -> tablerender("File uploaded", $text);
	}
}

$text = "
<form enctype=\"multipart/form-data\" action=\"$PHP_SELF\" method=\"post\">
<table style=\"width:95%\">

<tr> 
<td style=\"width:20%\">File: </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"file\" name=\"userfile\" size=\"50\">


</td>
</tr>

<tr> 
<td style=\"width:20%\">Upload folder: </td>
<td style=\"width:80%\">
<select name=\"path\" class=\"tbox\">
<option>../files/images</option>
<option>../files/files</option>
<option>../files/temp</option>";


//$handle=opendir("../files");
//while ($file = readdir($handle)){	
//	if($file != "." && $file != ".." && is_dir($file)){
//	if(is_dir($file)){
//		$text .= "<option>".$file."</option>";
//	}
//}
//closedir($handle);

$text .= "</select> 

</td>
</tr>

<tr> 
<td style=\"width:20%\">If image, make resized copy?: </td>
<td style=\"width:80%\">
<input type=\"checkbox\" name=\"resize\" value=\"1\">
</td>
</tr>

<tr> 
<td style=\"width:20%\">Resize dimensions: </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"coords\" size=\"10\" value=\"$coords\" maxlength=\"10\" /> (ie 250x200)
</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">
<input class=\"button\" type=\"submit\" name=\"upload\" value=\"Upload\" />

</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Upload Image/File</div>", $text);

require_once("footer.php");


?>	