<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/lan_error.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
if(!defined('PAGE_NAME')) // FIXME.
{
	define("PAGE_NAME", "Error");
}
define("LAN_ERROR_TITLE", "Oops!");

define("LAN_ERROR_1", "Error 401 - Authentication Failed");
define("LAN_ERROR_2", "The URL you've requested requires a username and password. Either you entered one incorrectly, or your browser doesn't support this feature.");
define("LAN_ERROR_3", "Please inform the administrator of the referring page if you think this error page has been shown by mistake.");

define("LAN_ERROR_4", "Error 403 - Access forbidden");
define("LAN_ERROR_5", "You are not permitted to retrieve the document or page you requested.");
//define("LAN_ERROR_6", "Please inform the administrator of the referring page if you think this error page has been shown by mistake."); // use LAN_ERROR_3

define("LAN_ERROR_7", "Error 404 - Document Not Found");
//define("LAN_ERROR_9", "Please inform the administrator of the referring page if you think this error message has been shown by mistake."); // use LAN_ERROR_3
define("LAN_ERROR_10", "Error 500 - Internal server error");
define("LAN_ERROR_11", "The server encountered an internal error or misconfiguration and was unable to complete your request");
//define("LAN_ERROR_12", "Please inform the administrator of the referring page if you think this error page has been shown by mistake."); // use LAN_ERROR_3
define("LAN_ERROR_13", "Error - Unknown");
define("LAN_ERROR_14", "The server encountered an error");
//define("LAN_ERROR_15", "Please inform the administrator of the referring page if you think this error page has been shown by mistake."); // use LAN_ERROR_3
define("LAN_ERROR_16", "Your unsuccessful attempt to access");
define("LAN_ERROR_17", "has been recorded.");
define("LAN_ERROR_18", "Apparently, you were referred here by");
define("LAN_ERROR_19", "Unfortunately, there's an obsolete link at that address.");
define("LAN_ERROR_20", "Please click here to go to this site's home page");
define("LAN_ERROR_21", "The requested URL could not be found on this server. The link you followed is probably outdated.");
define("LAN_ERROR_22", "Please click here to go to this site's search page");
define("LAN_ERROR_23", "Your attempt to access ");
define("LAN_ERROR_24", " was unsuccessful.");

// 0.7.6
define("LAN_ERROR_25", "[1]: Unable to read core settings from database - Core settings exist but cannot be unserialized. Attempting to restore core backup ...");
define("LAN_ERROR_26", "[2]: Unable to read core settings from database - non-existent core settings.");
define("LAN_ERROR_27", "[3]: Core settings saved - backup made active.");
define("LAN_ERROR_28", "[4]: No core backup found. Check that your database has valid content. ");
define("LAN_ERROR_29", "[5]: Field(s) have been left blank. Please resubmit the form and fill in the required fields.");
define("LAN_ERROR_30", "[6]: Unable to form a valid connection to mySQL. Please check that your e107_config.php contains the correct information.");
define("LAN_ERROR_31", "[7]: mySQL is running but database [x] couldn't be connected to.<br />Please check it exists and that your configuration file contains the correct information.");
define("LAN_ERROR_32", "To complete the upgrade, copy the following text into your e107_config.php file:");

define("LAN_ERROR_33", "Processing error! Normally, I would redirect to the home page.");
define("LAN_ERROR_34", "Unknown error! Please inform the site administrator you saw this:");

define("LAN_ERROR_35", "Error 400 - Bad Request");
define("LAN_ERROR_36", "There is a formatting error in the URL you are trying to access.");
define("LAN_ERROR_37", "Error Icon");
define("LAN_ERROR_38", "Sorry, but the site is unavailable due to a temporary fault");
define("LAN_ERROR_39", "Please try again in a few minutes");
define("LAN_ERROR_40", "If the problem persists, please contact the site administrator");
define("LAN_ERROR_41", "The reported error is:");
define("LAN_ERROR_42", "Additional error information: ");
define("LAN_ERROR_43", "Site unavailable temporarily");
define("LAN_ERROR_44", "Site logo");

define("LAN_ERROR_45", "What can you do now?");
define("LAN_ERROR_46", "Check log for details.");
define("LAN_ERROR_47", "Validation error: News title can't be empty!");
define("LAN_ERROR_48", "Validation error: News SEF URL value is required field and can't be empty!");
define("LAN_ERROR_49", "Validation error: News SEF URL is unique field - current value already in use! Please choose another SEF URL value.");
define("LAN_ERROR_50", "Validation error: News category can't be empty!");

