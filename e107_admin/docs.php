<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/admin_template.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!ADMIN){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");

$c=1;
$handle=opendir(e_DOCS);
while ($file = readdir($handle)){
	if($file != "." && $file != ".."){
		$helplist[$c] = $file;
		$c++;
	}
}
closedir($handle);


if(e_QUERY){
	$aj = new textparse;
	$filename = e_DOCS.$helplist[e_QUERY];
	$fd = fopen ($filename, "r");
	$text .= fread ($fd, filesize ($filename));
	fclose ($fd);

	$text = $aj -> tpa($text);
	$text = preg_replace("/Q\>(.*?)\n/si", "<b>Q>\\1</b>\n", $text);

	$ns -> tablerender($helplist[e_QUERY], $text."<br />");
	unset($text);
}

require_once("footer.php");
?>	