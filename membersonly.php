<?php
	require_once("class2.php");


echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo SITENAME." - ".PAGE_NAME; ?></title>
<link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
<!-- <link rel="stylesheet" href="<?php echo e_BASE."e107_files/"; ?>e107.css" />-->
<?php
echo "\n<link rel='stylesheet' href='".THEME."style.css' />\n";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
<?php
echo $pref['meta_tag'][1]."\n";
?>

<script type="text/javascript" src="e107_files/e107.js"></script>
<?php
if(file_exists(THEME."theme.js")){echo "<script type='text/javascript' src='".THEME."theme.js'></script>";}
if(file_exists(e_BASE."e107_files/user.js")){echo "<script type='text/javascript' src='".e_BASE."e107_files/user.js'></script>\n";}
?>
</head>
<body>
<br />
<?php

	echo "<table align='center'><tr><td>";	
	$text .= "<table class='fborder'>
	<tr><td class='forumheader3'><br />".LAN_MEMBERS_1."
	".LAN_MEMBERS_2." <a href='".e_BASE.e_SIGNUP."'>".
	LAN_MEMBERS_3."</a><br /><br /></td></tr>
	<tr><td class='forumheader' style='text-align:center;'><a href='".e_BASE."index.php'>".LAN_MEMBERS_4."</td></tr>
	</table>";
	$ns -> tablerender(LAN_MEMBERS_0,$text);
	echo "</td></tr></table>";

?>
</body>
</html>

