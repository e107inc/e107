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

$qs = explode("-", $_SERVER['QUERY_STRING']);
$action = $qs[0];
$id = $qs[1];
$url = $qs[2];

if($action == "copy"){
	header("location:download.php?from.".$id);
}

if(!getperms("P")){ header("location:../index.php"); }
require_once("auth.php");

if($action == "image"){
	$text = "<img src=\"../".$url."\" alt=\"\" />";
	$ns -> tablerender($url, $text);
}

if($action == "delete"){
	$text = "Deleting ...";
	$ns -> tablerender("Delete", $text);
}

if(!$filecount = $sql -> db_Select("userfile", "*", "userfile_authorized='0'")){
	echo "<div style=\"text-align:center\">No files to authorize.</div>";
	require_once("footer.php");
	exit;
}

$text = "<div style=\"text-align:center\">\nYou have had ".$filecount." file(s) uploaded.<br /><br />\n<table style=\"width:100%\">\n";
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
	<td style=\"width:85%\" class=\"forumheader3\">".$userfile_file."</td>
	</tr>
	<tr>
	<td style=\"width:15%\" class=\"fcaption\">File Image</td>
	<td style=\"width:85%\" class=\"forumheader3\">";

		if($userfile_image != ""){
		$text .= "<a href=\"userfile.php?image-".$userfile_id."-".$userfile_image."\">".$userfile_image."</a>";
	}else{
		$text .= "no image";
	}	
	$text .= "</tr>

	<tr>
	<td style=\"width:15%\" class=\"fcaption\">Description</td>
	<td style=\"width:85%\" class=\"forumheader3\">$userfile_description</td>
	</tr>

	<tr>
	<td style=\"width:15%\" class=\"fcaption\">Options</td>
	<td style=\"width:85%\" class=\"forumheader3\">

	<a href=\"userfile.php?delete-".$userfile_id."\">Delete</a>
	&nbsp;-&nbsp;
	<a href=\"userfile.php?copy-".$userfile_id."\">Copy to download manager</a>
	&nbsp;-&nbsp;
	<a href=\"userfile.php?auth-".$userfile_id."\">Remove from list</a>

	&nbsp;-&nbsp;
	<a href=\"userfile.php?auth-".$userfile_id."\">Copy files to proper directories</a>
	
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
?>