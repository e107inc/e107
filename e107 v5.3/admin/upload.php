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
if(!getperms("6")){ header("location:../index.php"); }
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
		
		// Change the directory for thumbails
		$small = $_POST['path']."/mini/".$file_name.".".$file_extention;
		
		
		
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

		$text = "Image <b>".$_FILES['userfile']['name']."</b> uploaded to ".$path." directory, image <b>".$_POST['path']."/mini/".$file_name.".".$file_extention."</b> created and resized to ".$newx."x".$newy.".";
		$ns -> tablerender("File uploaded", $text);
	}else{
		//no resize so just upload file

		$file = ereg_replace(" ", "", $_FILES['userfile']['name']);
		$file_extention = strtolower(substr(strrchr($file, "."), 1));
		$file_name = eregi_replace(".".$file_extention, "", $file);

		$large = $_POST['path']."/".$_FILES['userfile']['name'];

		copy($userfile, $large);
		$text = "File <b>".$_FILES['userfile']['name']."</b> uploaded to ".$path." directory.";
		$ns -> tablerender("File uploaded", $text);
	}
}
$text = "";


// Creation of new folders
if($addfolder2 == "yes"){
	if($newfolder!=""){
		$newpath = "../files/".$newfolder;
		$newpathmini = $newpath."/mini";
		mkdir($newpath,0700);
		mkdir($newpathmini,0700);
		$text .= "<br><b>Folders successful created !</b><br><br>";
		$ns -> tablerender("Management of your files and folders - Folders created", $text);
	}
	else{
		$text .= "<br><b>Folders not created ! Please give a name before to click on 'Create'</b><br><br>";
		$ns -> tablerender("Management of your files and folders - Error", $text);
	}
}

// Delete an existing folder
//rmdir
//unlink
if($deletefolder2 == "yes"){
	if($path2!=""){
		$text .= "<b style='font-size: 13px;'>Informations about thumbails</b><br><br>";
		$path2mini = $path2."/mini";
		$handle2=opendir($path2mini."/");
		$imini=array();
		$i=array();
		$id_filemini=0;
		$id_file=0;
		while ($filemini = readdir($handle2)){
			$imini[$$id_filemini]=$filemini;
			if($filemini != "." && $filemini != ".."){
					$deletefile = $path2mini."/".$filemini;
					unlink($deletefile);
					$text .= "This file <i> ".$imini[$$id_filemini]." </i> in the folder <i>".$path2mini." </i> was deleted<br>";	
			}
			$id_filemini++;
		}
		closedir($handle2);
		rmdir ($path2mini);
		$text .= "<br><b>The folder <i>".$path2mini."</i> was deleted</b><hr width=100%><br><b style='font-size: 13px;'>Informations about original pictures</b><br><br>";
		$handle3=opendir($path2);
		while ($fileoriginal = readdir($handle3)){	
			$i[$$id_file]=$fileoriginal;
			if($fileoriginal != "." && $fileoriginal != ".."){
					$deletefile = $path2."/".$fileoriginal;
					unlink($deletefile);
					$text .= "This file <i> ".$i[$$id_file]." </i> in the folder <i>".$path2." </i> was deleted<br>";	
			}
			$id_file++;
		}
		closedir($handle3);
		rmdir ($path2);
		$text .= "<br><b>The folder <i>".$path2."</i> was deleted</b>";
		
		$ns -> tablerender("Management of your files and folders - Folders deleted", $text);
	}
	else{
		$text .= "<br><b>Folders and files not deleted ! Please give a name before to click on 'Create'</b><br><br>";
	
		$ns -> tablerender("Management of your files and folders - Error", $text);
	}
	
}

$text = "";
$text .= "

<table style=\"width:95%\">
<tr> 
<td style=\"width:20%\"><form enctype=\"multipart/form-data\" action=\"$PHP_SELF\" method=\"post\">Upload folder: </td>
<td style=\"width:80%\">
<select name=\"path\" class=\"tbox\">";

$handle=opendir("../files");
while ($file2 = readdir($handle)){	
	if($file2 != "." && $file2 != ".." && !stristr($file2,'.')){
			$text .= "<option>../files/".$file2."</option>";
		
	}
}
closedir($handle);

$text .= "</select> 

</td>

<tr> 
<td style=\"width:20%\">File: </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"file\" name=\"userfile\" size=\"50\">

</td>
</tr>

<tr> 
<td style=\"width:20%\">If image (.gif or .jpg), make resized copy?: </td>
<td style=\"width:80%\">
<input type=\"checkbox\" name=\"resize\" value=\"1\">
<b>NOTE</b> : <b>This new image will be created in the subdirectory called \"mini\"</b>
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
<input class=\"button\" type=\"submit\" name=\"upload\" value=\"Upload\"  style=\"cursor: pointer;\"/>

</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\">Upload Image/File</div>", $text);

$text ="";
$text .="<table style=\"width:95%\">
<tr> 
<td style=\"width:20%\" colspan=2><b>Need to create a new upload folder ?</b>
<form action=\"\" name=\"addfolder\" action=\"$PHP_SELF\" method=\"post\">
<input name=\"addfolder2\" type=\"hidden\" value=\"yes\">
</td>
</tr>
<tr>
<td> Name of this folder : </td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"newfolder\" size=\"10\" value=\"\" maxlength=\"10\" />
&nbsp;
<input type=\"submit\" class=\"button\" name=\"Create\" value=\"Create\"  style=\"cursor: pointer;\"/>
</form>
</td>
</tr>
<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">&nbsp;</td>
</tr>
<tr> 
<td style=\"width:20%\" colspan=2><b>Need to delete an existing folder and all files in this folder ?</b>
<form action=\"\" name=\"deletefolder\" action=\"$PHP_SELF\" method=\"post\">
<input name=\"deletefolder2\" type=\"hidden\" value=\"yes\">
</td>
</tr>
<tr>
<td> Name of this folder : </td>
<td style=\"width:80%\">
<select name=\"path2\" class=\"tbox\">";

$handle=opendir("../files");
while ($file2 = readdir($handle)){	
	if($file2 != "." && $file2 != ".." && !stristr($file2,'.')){
		
			$text .= "<option>../files/".$file2."</option>";
		
	}
}
closedir($handle);



$text .= "</select>
&nbsp;
<input class=\"button\" name=\"Delete\" value=\"Delete\" onclick=\"confirmdelete=confirm('Are you sure ?');if(confirmdelete=='1'){submit();}else{javascript:void(0);}\" style=\"cursor: pointer;\">
</form>
</td>
</tr>
</table>
";


$ns -> tablerender("<div style=\"text-align:center\">Management of your folders</div>", $text);

require_once("footer.php");


?>	
