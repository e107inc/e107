<?php
/*
+---------------------------------------------------------------+
|	e107 website system					|
|	/admin/upload.php					|
|								|
|	©William Moffett 2001-2003				|
|	http://qnome.cjb.net					|
|	qnome@attbi.com						|
|								|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).		|
+---------------------------------------------------------------+
*/
require_once("../class2.php");

if(!getperms("6")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

$sql -> db_Select("core", "*", "e107_name='pref' ");
$row = $sql -> db_Fetch();
define("BASEPATH", $pref['upload_basefolder'][1]);
define(COORDS, $pref['upload_coords'][1]);

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

if(IsSet($_POST['upload'])){
	$path = BASEPATH."/".$_POST['uploadpath'];
	$realname = $HTTP_POST_FILES['userfile']['name'];
	$file = ereg_replace(" ", "", $HTTP_POST_FILES['userfile']['name']);
	$file_extention = strtolower(substr(strrchr($file, "."), 1));
	$coords = $_POST['coords'];
	$newx = substr($coords, 0, strpos($coords, "x"));
	$newy = substr(strrchr($coords, "x"), 1);
	if($_POST['resize'] == 1){
		if(!extension_loaded('gd')){
			if(!dl('gd.so')){
				$text = "It appears you do not have the gd extention loaded so the image cannot be resized. You will have to resize the image manually and upload it seperately until your webadmin upgrades your extentions. (The library is available from <a href=\"http://www.boutell.com/gd)\">http://www.boutell.com/gd/</a>)";
				$ns -> tablerender("<div style=\"text-align:center\">Error!</div>", $text);
				require_once("footer.php");
				exit;
			}
		}
		if(!is_dir("$path/mini")){

			mkdir("$path/mini",0700);
		}
		copy($HTTP_POST_FILES['userfile']['tmp_name'], "$path/$realname");
		copy($HTTP_POST_FILES['userfile']['tmp_name'], "$path/mini/$realname");
		$img_src = "$path/mini/$realname";
		$destimage = ImageCreateTrueColor($newx,$newy);
		if($file_extention == "jpg"){
			$image = imagecreatefromjpeg("$img_src");
			$imagex = imagesx($image);
			$imagey = imagesy($image);
			imagecopyresized($destimage, $image, 0,   0,   0,   0, $newx, $newy, $imagex, $imagey); 
			imagejpeg($destimage,$img_src); 
			$text = "Image <b>".$_FILES['userfile']['name']."</b> uploaded to ".$path." directory. <br />
                	Image ".$path."/mini/<b>".$realname."</b> was created and resized to ".$newx."x".$newy.".";
			$ns -> tablerender("File uploaded", $text);
			require_once("footer.php");
			exit;
		}
		if($file_extention == "gif"){
			if(!@$image = imagecreatefromgif("$img_src")){
				$text = "GIF support not compiled into your GD library - please use a different file type.";
				$ns -> tablerender("<div style=\"text-align:center\">Error!</div>", $text);
				require_once("footer.php");
				exit;
			}
			$imagex = imagesx($image);
			$imagey = imagesy($image);
			imagecopyresized($destimage, $image, 0,   0,   0,   0, $newx, $newy, $imagex, $imagey); 
			imagegif($destimage,$img_src);
			$text = "Image <b>".$_FILES['userfile']['name']."</b> uploaded to ".$path." directory. <br />
                	Image ".$path."/mini/<b>".$realname."</b> was created and resized to ".$newx."x".$newy.".";
			$ns -> tablerender("File uploaded", $text);
			require_once("footer.php");
			exit;
		}
		if($file_extention == "png"){

			$image = imagecreatefrompng("$img_src");
			$imagex = imagesx($image);
			$imagey = imagesy($image);
			imagecopyresized($destimage, $image, 0,   0,   0,   0, $newx, $newy, $imagex, $imagey); 
			imagepng($destimage,$img_src);
			$text = "Image <b>".$_FILES['userfile']['name']."</b> uploaded to ".$path." directory. <br />
                	Image ".$path."/mini/<b>".$realname."</b> was created and resized to ".$newx."x".$newy.".";
			$ns -> tablerender("File uploaded", $text);
			require_once("footer.php");
			exit;
		}


	}else{
		if (is_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'])){
			copy($HTTP_POST_FILES['userfile']['tmp_name'], "$path/$realname");
			$text = "File <b>".$HTTP_POST_FILES['userfile']['name']."</b> uploaded to ".$path." directory.";
  		}else{
  			$text = "The file did not upload. Filename: " . $HTTP_POST_FILES['userfile']['name'];
   		}
			
		$ns -> tablerender("File uploaded", $text);
	}


}

if(IsSet($_POST['updatecoords'])){
        $coords = $_POST['coords'];
	$pref['upload_coords'][1] = $_POST['coords'];
	$tmp = addslashes(serialize($pref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='pref' ");
	$text = "<table style=\"width:95%\">
  	  <tr> 
    	    <td style=\"width:20%\">Updated</td>
            <td style=\"width:80%\">Resize dimensions are set to: \"$coords\"<br /><b><a href=\"upload.php\">Continue</a></b></td>
          </tr>
        </table>";
	$ns -> tablerender("<div style=\"text-align:center\">Image size updated.</div>", $text);
	require_once("footer.php");
}

if(IsSet($_POST['changecoords'])){
	$coords = $_POST['coords'];
	$text = "<table style=\"width:95%\">
	  <form action=\"\" name=\"changecoords\" action=\"".e_SELF."\" method=\"post\">
	  <tr style=\"vertical-align:top\"> 
    	    <td style=\"width:20%\"><input class=\"tbox\" type=\"text\" name=\"coords\" size=\"10\" value=\"$coords\" maxlength=\"10\" /></td>
    	    <td style=\"width:80%\">Enter your preferred thubnail size and click submit. <input class=\"button\" type=\"submit\" name=\"updatecoords\" value=\"Submit\"  style=\"cursor: pointer;\"/></td>
   	  </tr>
          </form>
         </table>";
	$ns -> tablerender("<div style=\"text-align:center\">Change Image creation size</div>", $text);
	require_once("footer.php");
}

if(IsSet($_POST['addfolder'])){
        $path = BASEPATH;
	$newfolder = $_POST['newfolder'];
	if($newfolder !=""){
		$newpath = "$path/".$newfolder;
		$newpathmini = $newpath."/mini";
		mkdir($newpath,0700);
		mkdir($newpathmini,0700);
		$text = "<br><b>Folders successful created !</b><br><br>";
		$ns -> tablerender("Management of your files and folders - Folders created", $text);
	}else{
		$text = "<br><b>Folders not created ! Please give a name before to click on 'Create'</b><br><br>";
		$ns -> tablerender("Management of your files and folders - Error", $text);
	}
}

if(IsSet($_POST['deletefolder'])){
	unset($text);
        $basepath = "".BASEPATH."";
	$path2 = $_POST['path2'];
	$text .= "<br><b> $path2 folder Informatiom.</b><br><br>";
	$handle3=opendir("$basepath/".$path2);
	while ($fileoriginal = readdir($handle3)){	
		$i[$$id_file]=$fileoriginal;
		if($fileoriginal != "." && $fileoriginal != ".."){
			$deletefile = "$basepath/".$path2."/".$fileoriginal;
			if(!is_dir($deletefile)){
				if(!unlink($deletefile)){
					$text .= "The file <i> ".$i[$$id_file]." </i> Path: <i>".$deletefile." </i> could not be deleted.<br />";
				}else{
					$text .= "The file <i> ".$i[$$id_file]." </i> was deleted.<br />";
				}
			}else{
				$text .= "<b>$deletefile folder Informatiom</b><br /><br />";	
				$handle2=opendir($deletefile."/");
				$imini=array();
				$i=array();
				$id_filemini=0;
				$id_file=0;
				while ($filemini = readdir($handle2)){
					$imini[$$id_filemini]=$filemini;
					if($filemini != "." && $filemini != ".."){
						$deletefile2 = $deletefile."/".$filemini;
						chmod($deletefile2,0777);
						if(!unlink($deletefile2)){
							$text .= "The file <i> ".$deletefile2." </i> could not be deleted.<br />";
						}else{
							$text .= "The file <i> ".$deletefile2." </i> was deleted.<br />";
						}	
					}
					$id_filemini++;
				}
				closedir($handle2);
				chmod($deletefile,0777);
				if(!rmdir ($deletefile)){
					$text .= "<br>The folder <b><i>".$deletefile."</i> could not be deleted</b> rmdir failed! (Directory not empty).<br />";
				}else{
					$text .= "<br>The folder <b><i>".$deletefile."</i></b> was deleted<br />";
				}
			}
		}
		$id_file++;
	}
	closedir($handle3);
	chmod("$basepath/".$path2,0777);
	if(!rmdir ("$basepath/".$path2)){
		$text .= "<br>The folder <b><i>".$path2."</i> could not be deleted</b> rmdir failed! (Directory not empty).<br />";
	}else{
		$text .= "<br>The folder <b><i>".$path2."</i></b> was deleted<br />";
	}
	$ns -> tablerender("Management of your files and folders - Folders deleted", "$text2 $text");
}

if(IsSet($_POST['addbasefolder'])){
        $path = "".e_BASE."";
	$newfolder = $_POST['newfolder'];
	if($newfolder !=""){
		unset($text);
		$newpath = "$path".$newfolder;

		if(is_dir("$newpath")){
			$text .= "The Folder $newfolder exists!  Setting <b>$newfolder</b> as your default upload folder.";

			$text .= "<br><b>Folders added to Database.</b><br /><br />Your base path has been set to <b>$newfolder</b><br />
		        I have also created a <b>default</b> folder which you can now upload to.<br /><br />
			In order to create <b>hyper-links</b> to the files if uploaded to the default folder,<br />
			use the relative path to the files such as the following.<br />
			Example: <b>$newfolder/default/my-file.zip </b> <br />This would be the same as http://".$_SERVER['SERVER_NAME']."/$newfolder/default/my-file.zip<br/>
 			<b><a href=\"upload.php\">Continue</a></b>";
			mkdir("$newpath/default",0700);	
			mkdir("$newpath/default/mini",0700);
		}else{
			mkdir($newpath,0700);	
			mkdir("$newpath/default",0700);
			mkdir("$newpath/default/mini",0700);
			$text .= "<br><b>Two Folders were successfully created!</b><br /><br />Your base path has been set to <b>$newfolder</b><br />
			I have also created a <b>default</b> folder which you can now upload to.<br /><br />
			In order to create a <b>hyper-links</b> to the files if uploaded to the default folder,<br />
			use the relitive path to the files such as the following.<br />
			Example: <b>$newfolder/default/my-file.zip. </b> <br />This would be the same as http://".$_SERVER['SERVER_NAME']."/$newfolder/default/my-file.zip<br />
			<b><a href=\"upload.php\">Continue</a></b>";
                }
		$pref['upload_basefolder'][1] = "$newpath";
		$pref['upload_coords'][1] = "250x200";
		$tmp = addslashes(serialize($pref));
		$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='pref' ");
		$ns -> tablerender("Management of your files and folders - Base Folders created", $text);
		require_once("footer.php");
		exit;
	}else{
		unset($text);
		$text = "<br><b>Folders not created ! Please enter a name before you click on 'Create a Base Upload folder'</b><br><br>";
		$ns -> tablerender("Management of your files and folders - Error", $text);
	}

}


 if("".BASEPATH."" == ""){
		unset($text);
		$text .="<table style=\"width:95%\"><tr> 
		<td style=\"width:20%\" colspan=2><b>You Need to create a new upload folder!</b><br />
		This folder will be created in the E107 root directory!
		<form action=\"\" name=\"addfolder\" action=\"$PHP_SELF\" method=\"post\">
		</td>
		</tr>
		<tr>
		<td> Name of this folder : </td>
		<td style=\"width:80%\">
		<input class=\"tbox\" type=\"text\" name=\"newfolder\" size=\"10\" value=\"\" maxlength=\"10\" />
		<input type=\"submit\" class=\"button\" name=\"addbasefolder\" value=\"add a folder\"  style=\"cursor: pointer;\"/>
		</form>
		</td>
		</tr>
		</table>";
		$ns -> tablerender("<div style=\"text-align:center\">Create a Base Upload folder</div>", $text);
		require_once("footer.php");
		exit;
 }



// MAIN PAGE STARTS HERE

// UPLOAD FORM


$text = "<table style=\"width:95%\"><form enctype=\"multipart/form-data\" action=\"".e_SELF."\" method=\"post\">
  <tr> 
    <td style=\"width:20%\">Upload folder: </td>
    <td style=\"width:80%\"><select name=\"uploadpath\" class=\"tbox\">";
		$handle=opendir(BASEPATH);
		while ($file2 = readdir($handle)){	
			if($file2 != "." && $file2 != ".." && !stristr($file2,'.')){
				$text .= "<option>".$file2."</option>";
			}
		}
		closedir($handle);
$text .= "</select> 
    </td>
  <tr> 
    <td style=\"width:20%\">File: </td>
    <td style=\"width:80%\"><input class=\"tbox\" type=\"file\" name=\"userfile\" size=\"50\"></td>
  </tr>
  <tr> 
    <td style=\"width:20%\"><input type=\"checkbox\" name=\"resize\" value=\"1\">resized copy? </td>
    <td style=\"width:80%\">If checked upload will make resized copy of the image (.png, .gif or .jpg),?: <br /><b>NOTE</b> : <b>This new image will be created in the subdirectory called \"mini\"</b></td>
  </tr>
  <tr> 
    <td style=\"width:20%\"><input class=\"tbox\" type=\"text\" name=\"coords\" size=\"10\" value=\"".COORDS."\" maxlength=\"10\" /></td>
    <td style=\"width:80%\">Resize dimensions:  (ie 250x200) </td>

  </tr>
  <tr> 
    <td style=\"width:20%\"><input class=\"button\" type=\"submit\" name=\"changecoords\" value=\"Change\"  style=\"cursor: pointer;\"/></td>
    <td style=\"width:80%\">Change the default Resize Dimensions. </td>
  <tr>
  <tr style=\"vertical-align:top\"> 
    <td style=\"width:20%\"><input class=\"button\" type=\"submit\" name=\"upload\" value=\"Upload\"  style=\"cursor: pointer;\"/></td>
    <td style=\"width:80%\"></td>
  </tr></form>
</table>";
$ns -> tablerender("<div style=\"text-align:center\">Upload Image/File</div>", $text);
unset($text);

// CREATE NEW DIR FORM

$text .="<table style=\"width:95%\"><form action=\"\" name=\"addfolder\" action=\"$PHP_SELF\" method=\"post\">
  <tr> 
    <td style=\"width:20%\" colspan=2><b>Need to create a new upload folder ?</b>
    </td>
  </tr>
  <tr>
    <td> New Folder Name: </td>
    <td style=\"width:80%\">
    <input class=\"tbox\" type=\"text\" name=\"newfolder\" size=\"10\" value=\"\" maxlength=\"10\" />
    <input type=\"submit\" class=\"button\" name=\"addfolder\" value=\"Add Folder\"  style=\"cursor: pointer;\"/>
    </td>
  </tr></form>
</table>";
$ns -> tablerender("<div style=\"text-align:center\">Create a new folder</div>", $text);
unset($text);

$text .="<table style=\"width:95%\"><form action=\"\" name=\"deletefolder\" action=\"$PHP_SELF\" method=\"post\">
  <tr> 
    <td style=\"width:20%\" colspan=2><b>Need to delete an existing folder and all files in this folder ?</b>
    <input name=\"deletefolder\" type=\"hidden\" value=\"yes\">
    </td>
  </tr>
  <tr>
    <td> Folder Name: </td>
    <td style=\"width:80%\"><select name=\"path2\" class=\"tbox\">";
	$handle=opendir(BASEPATH);
	while ($file2 = readdir($handle)){	
		if($file2 != "." && $file2 != ".." && !stristr($file2,'.')){
			$text .= "<option>".$file2."</option>";
		}
	}
	closedir($handle);
    $text .= "</select>
    <input class=\"button\" name=\"Delete\" value=\"Delete\" onclick=\"confirmdelete=confirm('Are you sure ?');if(confirmdelete=='1'){submit();}else{javascript:void(0);}\" style=\"cursor: pointer;\">
    </td>
  </tr></form>
</table>";
$ns -> tablerender("<div style=\"text-align:center\">Delete folders</div>", $text);
require_once("footer.php");

?>	
