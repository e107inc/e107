<?php
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><e107 error></title>
<link rel="stylesheet" href="themes/e107/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
</head>
<body>

<div style="text-align:center">
<table style="width:100%" cellspacing="0" cellpadding="0">
<tr>
<td style="width:66%; background-color:#E2E2E2; text-align:left\">
<img src="themes/shared/logo.png" alt="Logo" />
</td>
<td style="background-color:#E2E2E2; text-align:right; vertical-align:bottom" class="smalltext">
©Steve Dunstan 2002. See gpl.txt for licence details.
</td>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<tr> 
<td colspan="2" style="background-color:#ccc; vertical-align: top;">
<div class="mediumtext">&nbsp;&nbsp;Error!</div>
</td>
</tr>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<td colspan="2" style="vertical-align: top; text-align:center"><br />
<table style="width:66%" class="fborder">
<tr>
<td class="installb">
<br /><img src="themes/e107/images/installlogo.png" alt="" /><br /><span class="smalltext">php/mySQL website system</span><br />
<br />
<span class="installe">There has been a fatal error!</span>
<br /><br />

<span class="installh">&middot; Have you installed e107 correctly?</span><br />
You must run the install script until it has completed with no warnings<br /><br />
<span class="installh">&middot; Have you made changes to your database which could have corrupted information held within?</span><br />
If this is the case you may need to delete the tables inside the database and install from scratch, or reinstall a previously saved backup<br /><br />
<span class="installh">&middot; Has the config.php file in the root e107 directory been deleted or overwritten?</span><br />
If the config.php file is empty then you need to run the install script, this script saves your settings inside this file. If you have a backup of config.php try copying it into your root directory.<br /><br />
<span class="installh">&middot; Have your core settings been deleted or corrupted?</span><br />
You can check this using phpMyAdmin. Browse the 'core' table, you should see a setting called 'pref', make sure this exists and is full of information relating to your site, like site theme etc. If this isn't there or is corrupted you can try running <a href="files/resetcore.php">resetcore.php</a> which will reset your core settings to default, but you ca nonly run this after a successful installation.<br /><br />
<span class="installh">&middot; Everything looks ok?</span><br />
Read the <a href="http://e107.org/faq.php?1">FAQ</a> and search for related messages on the <a href="http://e107.org/forum.php">e107 forums</a>, if you can't find anything try posting a message in the 'Problems' forum.<br /><br />

</td>
</tr>
</table>
<br />
</body>
</html>

