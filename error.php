<?php
require_once("class2.php");
require_once(HEADERF);

switch(e_QUERY){
	case 401:
		$text = "<div class='installe'>Error 401 - Permission Denied</div><br /><div class='installh'>You do not have permission to retrieve the URL or link you requested.</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
	break;
	case 403:
		$text = "<div class='installe'>Error 403 - Authentication Failed</div><br /><div class='installh'>The URL you've requested requires a correct username and password. Either you entered an incorrect username/password, or your browser doesn't support this feature.</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
	break;
	case 404:
		$text = "<div class='installe'>Error 404 - Document Not Found</div><br /><div class='installh'>The requested URL could not be found on this server. The link you followed is either outdated, inaccurate, or the server has been instructed not to allow access to it.</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
	break;
	case 500:
		$text = "<div class='installe'>Error 500 - Malformed Header</div><br /><div class='installh'>The server encountered an internal error or misconfiguration and was unable to complete your request</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
	break;
	default:
		$text = "<div class='installe'>Error - Unknown (".$_SERVER['QUERY_STRING'].")</div><br /><div class='installh'>The server encountered an error</div><br /><div class='smalltext'>Please inform the administrator of the referring page if you think this error page has been shown by mistake.</div>
		<br /><div class='installh'><a href='index.php'>Please click here to return to the front page</a>";
}
$ns -> tablerender("Error ".e_QUERY, $text);
require_once(FOOTERF);
?>