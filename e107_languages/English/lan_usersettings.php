<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/lan_usersettings.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
define("PAGE_NAME", "User Settings");

/*
LANs rationalised - some common ones now in lan_user.php. Old numbers generally cross-referenced
// define("LAN_7", "Display Name: ");		See LAN_USER_01
define("LAN_11", "the name you use to login to the site - this cannot be changed, please contact an administrator if it needs to be changed for security purposes");

//define("LAN_20", "Error");
define("LAN_106", "That doesn"t appear to be a valid email address");
//define("LAN_112", "Email Address: ");		see LAN_USER_60
define("LAN_119", "Location: ");
//define("LAN_120", "Signature: ");
//define("LAN_121", "Avatar: ");
define("LAN_144", "Website URL: ");
//define("LAN_151", "OK");
define("LAN_185", "You left the password field blank ");
//define("LAN_308", "Real Name: ");
define("LAN_402", "Type path or choose avatar");

define("LAN_410", "Settings for");
define("LAN_411", "Update Your Settings");
define("LAN_412", "Change Your Password");
define("LAN_413", "Choose An Avatar");
//define("LAN_416", "Yes");
//define("LAN_417", "No");
define("LAN_419", "Personal / Contact Information");
//define("LAN_420", "Avatar");			LAN_USER_07
//define("LAN_425", "Photograph");		LAN_USER_06
//define("LAN_427", "Submit ...");
//define("LAN_428", "News Item");
//define("LAN_429", "Link");
//define("LAN_430", "Download");
//define("LAN_431", "Article");
//define("LAN_432", "Review");

//define("LAN_435", "XML User Protocol file");	// LAN_USER_11

//define("LAN_SIGNUP_1", "Min.");		See LAN_USER_78
//define("LAN_SIGNUP_2", "chars.");	See LAN_USER_79
//define("LAN_SIGNUP_4", "Your password must be at least ");	See LAN_USER_77
//define("LAN_SIGNUP_5", " characters long.");			See LAN_USER_77
//define("LAN_SIGNUP_6", "Your ");				See LAN_USER_75
//define("LAN_SIGNUP_7", " is required");		See LAN_USER_75


//define("LAN_CUSTOMTITLE", "Custom Title");		See LAN_USER_04
//define("LAN_ICQNUMBER", "ICQ number must contain only numbers");

//v.617
define("LAN_408", "A user with that email address already exists. ");
*/
define("MAX_AVWIDTH", "Maximum avatar size (wxh) is ");
define("MAX_AVHEIGHT", " x ");
// define("GIF_RESIZE", "Please resize gif image or convert to different format");
//define("RESIZE_NOT_SUPPORTED", "Resize method not supported by this server. Please resize image or choose another. File has been deleted.");


// v0.7
define("LAN_USET_1", "Your avatar is too wide");
define("LAN_USET_2", "Maximum allowable width is");
define("LAN_USET_3", "Your avatar is too high");
define("LAN_USET_4", "Maximum allowable height is");
//define("LAN_USET_5", "Subscribed to");					// Now LAN_USER_76
//define("LAN_USET_6", "Subscribe to our mailing-list(s) and/or sections of this site.");		Now LAN_USER_73
define("LAN_USET_7", "Miscellaneous");
define("LAN_USET_8", "User signature");
define("LAN_USET_9", "Some of the required fields (marked with a *) are missing from your settings.");
define("LAN_USET_10","Please update your settings now, in order to proceed.");
define("LAN_USET_11", "That user name cannot be accepted as valid, please choose a different user name");
define("LAN_USET_12", "That display name is too short. Please choose another");
define("LAN_USET_13", "Invalid characters in Username. Please choose another");
define("LAN_USET_14", "Login name too long. Please choose another");
define("LAN_USET_15", "Display name too long. Please choose another");
define("LAN_USET_16", "Tick box to delete existing photo without uploading another");
define("LAN_USET_17", "Display name already used. Please choose another");
define("LAN_USET_18", "User data changed by admin: [x], login name: [y]");
//define("LAN_USET_19", "Custom Title");			Now LAN_USER_74
define("LAN_USET_20", "You must also change the user's password if you are changing their login name or email address");
define("LAN_USET_21", "Please validate the changes by re-entering your password: ");
//define("LAN_USET_22", "Invalid password!"); // LAN_INCORRECT_PASSWORD
define("LAN_USET_23", "Leave blank to keep existing password");		// LAN_401
define("LAN_USET_24", "New password: ");							// LAN_152
define("LAN_USET_25", "Re-type new password: ");					// LAN_153
define("LAN_USET_26", "Upload your avatar");						// LAN_415
define("LAN_USET_27", "Upload your photograph");					// LAN_414
define("LAN_USET_28", "This will be shown on your profile page");	// LAN_426
//define("LAN_USET_29", "URL to your XUP file");						// LAN_433
define("LAN_USET_30", "what's this?");								// LAN_434
define("LAN_USET_31", "Registration information");					// LAN_418
define("LAN_USET_32", "Please note: Any image uploaded to this server that is deemed inappropriate by the administrators will be deleted immediately.");	// LAN_404
define("LAN_USET_33", "Choose site-stored avatar");					// LAN_421
define("LAN_USET_34", "Use remote avatar");							// LAN_422
define("LAN_USET_35", "Please type full address to image");			// LAN_423
define("LAN_USET_36", "Click button to see avatars stored on this site");	// LAN_424
define("LAN_USET_37", "Save settings");								// LAN_154 //TODO common LAN?
define("LAN_USET_38", "Choose avatar");								// LAN_403
define("LAN_USET_39", "Update user settings");						// LAN_155
define("LAN_USET_40", "The two passwords do not match");			// LAN_105
define("LAN_USET_41", "Settings updated and saved into database.");	// LAN_150 //TODO Common LAN?
define("LAN_USET_42", "Mismatch on validation key");
define("LAN_USET_43", "Error updating user data");

// BC for v1.x template
//TODO Move to usersettings.php with bcDefs() method.
define("LAN_7", "Display Name: ");
define("LAN_8", "the name displayed on site");
define("LAN_9", "Username: ");
define("LAN_10", "the name you use to login to the site");
define("LAN_112", "Email address: ");
define("LAN_113", "Hide email address?: ");
define("LAN_114", "This will prevent your email address from being displayed on site");
define("LAN_120", "Signature: ");
define("LAN_122", "Time zone:");
define("LAN_152", "New password: ");
define("LAN_153", "Re-type new password: ");
define("LAN_154", "Save settings");
define("LAN_308", "Real name: ");
define("LAN_401", "Leave blank to keep existing password");
define("LAN_404", "Please note: Any image uploaded to this server that is deemed inappropriate by the administrators will be deleted immediately.");
define("LAN_414", "Upload your photograph");
define("LAN_415", "Upload your avatar");
define("LAN_418", "Registration information");
define("LAN_420", "Avatar");
define("LAN_421", "Choose site-stored avatar");
define("LAN_422", "Use remote avatar");
define("LAN_423", "Please type full address to image");
define("LAN_424", "Click button to see avatars stored on this site");
define("LAN_425", "Photograph");
define("LAN_426", "This will be shown on your profile page");
define("LAN_433", "URL to your XUP file");
define("LAN_434", "What's this?");
define("LAN_435", "XML User Protocol file");
define("LAN_CUSTOMTITLE", "Custom title");
define("LAN_USET_5", "Subscribed to");
define("LAN_USET_6", "Subscribe to our mailing-list(s) and/or sections of this site.");
// define("LAN_USET_8", "Signature / Time zone");

define("LAN_USET_50", "Delete Account");
define("LAN_USET_51", "Are you sure? This procedure cannot be reversed! Once completed, your account and any personal data that you have entered on this site will be permanently lost and you will no longer be able to login.");
define("LAN_USET_52", "A confirmation email has been sent to [x]. Please click the link in the email to permanently delete your account.");
define("LAN_USET_53", "Account Removal Confirmation");
define("LAN_USET_54", "Confirmation Email Sent");
define("LAN_USET_55", "Please click the following link to complete the deletion of your account.");
define("LAN_USET_56", "Your account has been successfully deleted.");


