<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/admin.php														|
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

$qs = explode(".", $_SERVER['QUERY_STRING']);
$action = $qs[0];
$id = $qs[1];
$url = $qs[2];

if($action == "remove"){
	$sql -> db_Delete("userfile", "userfile_id='$id' ");
}

if($action == "plugin"){
	$sql -> db_Select("userfile", "*", "userfile_id='$id' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$file = substr(strrchr($userfile_file, "/"), 1); 
	$copy = file_copy("../".$userfile_file, "../files/plugins/", $file, 1, 1);
	$sql -> db_Update("userfile", "userfile_file='files/plugins/".$file."' WHERE userfile_id='$id' ");
}

if($action == "thm"){
	$sql -> db_Select("userfile", "*", "userfile_id='$id' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$file = substr(strrchr($userfile_file, "/"), 1); 
	$copy = file_copy("../".$userfile_file, "../files/themes/", $file, 1, 1);
	$sql -> db_Update("userfile", "userfile_file='files/themes/".$file."' WHERE userfile_id='$id' ");
}

if($action == "misc"){
	$sql -> db_Select("userfile", "*", "userfile_id='$id' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$file = substr(strrchr($userfile_file, "/"), 1); 
	$copy = file_copy("../".$userfile_file, "../files/misc/", $file, 1, 1);
	$sql -> db_Update("userfile", "userfile_file='files/misc/".$file."' WHERE userfile_id='$id' ");
}

if($action == "delete"){
	$sql -> db_Select("userfile", "*", "userfile_id='$id' ");
	$row = $sql -> db_Fetch();
	extract($row);
	unlink("../".$userfile_file);
	$sql -> db_Update("userfile", "userfile_file='DELETED' WHERE userfile_id='$id' ");
	$ns -> tablerender("", $userfile_file." deleted.");
}

if($action == "copy"){
	header("location:download.php?from.".$id);
}

if(!getperms("P")){ header("location:../index.php"); }
require_once("auth.php");

if($action == "image"){
	$text = "<img src=\"../".$url."\" alt=\"\" />";
	$ns -> tablerender($url, $text);
}

if(!$filecount = $sql -> db_Select("userfile", "*", "userfile_authorized='0'")){
	echo "<div style=\"text-align:center\">No files to authorize.</div>";
	require_once("footer.php");
	exit;
}

$text = "\n<div style=\"text-align:center\">\nYou have had ".$filecount." file(s) uploaded.<br /><br />
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?$blah\">
<table style=\"width:100%\">\n";
$con = new convert;
while($row = $sql -> db_Fetch()){
	extract($row);
	$userfile_datestamp = $con->convert_date($userfile_datestamp, "long");
	
	$text .= "<tr>
	<td style=\"width:15%\" class=\"fcaption\">Upload ID</td>
	<td style=\"width:85%\" class=\"forumheader3\">$userfile_id</td>
	</tr>
	<tr>
	<td style=\"width:15%\" class=\"fcaption\">Submit Date</td>
	<td style=\"width:85%\" class=\"forumheader3\">$userfile_datestamp</td>
	</tr>
	<tr>
	<td style=\"width:15%\" class=\"fcaption\">Sender</td>
	<td style=\"width:85%\" class=\"forumheader3\">$userfile_sender</td>
	</tr>
	<tr>
	<td style=\"width:15%\" class=\"fcaption\">Email</td>
	<td style=\"width:85%\" class=\"forumheader3\">$userfile_email</td>
	</tr>
	<tr>
	<td style=\"width:15%\" class=\"fcaption\">Website</td>
	<td style=\"width:85%\" class=\"forumheader3\">$userfile_site</td>
	</tr>
	<tr>
	<td style=\"width:15%\" class=\"fcaption\">File name</td>
	<td style=\"width:85%\" class=\"forumheader3\">$userfile_filename</td>
	</tr>
	<tr>
	<td style=\"width:15%\" class=\"fcaption\">File path</td>
	<td style=\"width:85%\" class=\"forumheader3\">".$userfile_file."

	<select name=\"userfile_options\" onChange=\"urljump(this.options[selectedIndex].value)\" class=\"tbox\">
	<option selected>Please choose ...</option>
	<option value=\"userfile.php?plugin.".$userfile_id."\">Copy to plugins</option>
	<option value=\"userfile.php?thm.".$userfile_id."\">Copy to themes</option>
	<option value=\"userfile.php?misc.".$userfile_id."\">Copy to misc</option>
	<option value=\"userfile.php?delete.".$userfile_id."\">DELETE</option>
	<option value=\"userfile.php?download.".$userfile_id."\">Download</option>
	</select>
	</td>
	</tr>
	<tr>
	<td style=\"width:15%\" class=\"fcaption\">File Image</td>
	<td style=\"width:85%\" class=\"forumheader3\">";

		if($userfile_image != ""){
		$text .= "<a href=\"../".$userfile_image."\">".$userfile_image."</a>
		<select name=\"userimage_options\" class=\"tbox\">
	<option selected>Please choose ...</option>
	<option value=\"c_pluginss.".$userfile_id."\">Copy to pluginss</option>
	<option value=\"c_themess.".$userfile_id."\">Copy to themess</option>
	<option value=\"c_miscss.".$userfile_id."\">Copy to misc</option>
	<option value=\"delete.".$userfile_id."\><b>DELETE</b></option>
	<option value=\"download.".$userfile_id."\">View</option>
	</select>
	<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Go\" />
	";
	}else{
		$text .= "no image";
	}	
	$text .= "
	</td>	

	</tr>
	<tr>
	<td style=\"width:15%\" class=\"fcaption\">Description</td>
	<td style=\"width:85%\" class=\"forumheader3\">$userfile_description</td>
	</tr>

	<tr>
	<td style=\"width:15%\" class=\"fcaption\">Options</td>
	<td style=\"width:85%\" class=\"forumheader3\">

	<a href=\"userfile.php?remove.".$userfile_id."\">Delete entry</a>
	&nbsp;-&nbsp;
	<a href=\"userfile.php?copy.".$userfile_id."\">Copy to download manager</a>
	&nbsp;-&nbsp;
	</td>
	</tr>

	<tr>
	<td colspan=\"2\">
	<br />
	</td>
	</tr>";

}

$text .= "</table></div>";
$ns -> tablerender("UserFiles", $text);

//userfile_id  userfile_sender  userfile_email  userfile_site  userfile_datestamp  userfile_filename  userfile_file  userfile_image  userfile_description  userfile_authorized 



require_once("footer.php");

function file_copy($file_origin, $destination_directory, $file_destination, $overwrite, $fatal) {

	if($fatal){
		$error_prefix = 'FATAL: File copy of \'' . $file_origin . '\' to \'' . $destination_directory . $file_destination . '\' failed.';
		$fp = @fopen($file_origin, "r");
		if(!$fp){
			echo $error_prefix . ' Originating file cannot be read or does not exist.';
			return;
		}

		$dir_check = @is_writeable($destination_directory);
		if(!$dir_check){
			echo $error_prefix . ' Destination directory is not writeable or does not exist.';
			return;
		}

		$dest_file_exists = file_exists($destination_directory . $file_destination);
		if($dest_file_exists){ 
			if($overwrite){
				$fp = @is_writeable($destination_directory . $file_destination);
				if (!$fp) {
					echo  $error_prefix . ' Destination file is not writeable [OVERWRITE].';
					return;
				}
				$copy_file = @copy($file_origin, $destination_directory . $file_destination); 
			}           
		}else{
			$copy_file = @copy($file_origin, $destination_directory . $file_destination);
		}
	}else{
		$copy_file = @copy($file_origin, $destination_directory . $file_destination);
	}
}






?>