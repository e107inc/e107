<?php
echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><error <?php echo $errornumber; ?>."></title>
<link rel="stylesheet" href="themes/e107/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
</head>
<body>

<div style="text-align:center">
<table style="width:100%" cellspacing="0" cellpadding="0">
<tr>
<td style="width:66%; background-color:#E2E2E2; text-align:left'>
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
<div class='mediumtext'>&nbsp;Error!</div>
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

<?php

switch($_SERVER['QUERY_STRING']){
	case 401:
		echo "<div class='installe'>Error 401 - Permission Denied</div><br /><div class='installh'>You do not have permission to retrieve the URL or link you requested.</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
	break;
	case 403:
		echo "<div class='installe'>Error 403 - Authentication Failed</div><br /><div class='installh'>The URL you've requested requires a correct username and password. Either you entered an incorrect username/password, or your browser doesn't support this feature.</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
	break;
	case 404:
		echo "<div class='installe'>Error 404 - Document Not Found</div><br /><div class='installh'>The requested URL could not be found on this server. The link you followed is either outdated, inaccurate, or the server has been instructed not to allow access to it.</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
	break;
	case 500:
		echo "<div class='installe'>Error 500 - Malformed Header</div><br /><div class='installh'>The server encountered an internal error or misconfiguration and was unable to complete your request</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
	break;
	default:
		echo "<div class='installe'>Error - Unknown (".$_SERVER['QUERY_STRING'].")</div><br /><div class='installh'>The server encountered an error</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
}

?>

</td>
</tr>
</table>
</body>
</html>