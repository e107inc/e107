<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/membersonly.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:51:38 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
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
if (file_exists(THEME."theme.js")) {
	echo "<script type='text/javascript' src='".THEME."theme.js'></script>";
}
if (file_exists(e_BASE."e107_files/user.js")) {
	echo "<script type='text/javascript' src='".e_BASE."e107_files/user.js'></script>\n";
}
?>

<?php
	
print "</head>
	<body>
	<div><br /></div>\n";
	
$text = "<div style='text-align:center'><table class='fborder' style='width:75%;margin-right:auto;margin-left:auto'>
	<tr><td class='forumheader3' style='text-align:center'><br />".LAN_MEMBERS_1."
	".LAN_MEMBERS_2." <a href='".e_SIGNUP."'>". LAN_MEMBERS_3."</a><br /><br /></td></tr>
	<tr><td class='forumheader' style='text-align:center;margin-right:auto;margin-left:auto'><a href='".e_BASE."index.php'>".LAN_MEMBERS_4."</a></td></tr>
	</table></div>";
	
echo "<div style='text-align:center'>";
echo"<div style='width:65%;margin-left:auto;margin-right:auto;text-align:center'>";
	
$ns->tablerender(LAN_MEMBERS_0, $text);
echo "</div></div>";
	
?>
</body>
</html>