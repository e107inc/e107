<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Language file - user-related (many generic definitions)
 *
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/lan_user.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
/*
The language defines in this file have been renumbered - old number as a comment, and those apparently not used commented out below.
define("LAN_115", "ICQ Number");
define("LAN_116", "AIM Address");
define("LAN_117", "MSN Messenger");
define("LAN_118", "Birthday");
define("LAN_119", "Location");
define("LAN_144", "Website URL");
define("LAN_405", "days ago");
define("LAN_407", "none");
define("LAN_409", "points");
define("LAN_416", "You must be logged in to access this page");
define("LAN_426", "ago");
*/


// LAN_USER_01..LAN_USER_30 - Descriptions specifically for user-related DB field names
define("LAN_USER_01","Display name");
define("LAN_USER_02","Login Name");
define("LAN_USER_03","Real Name");
define("LAN_USER_04","Custom title");
// define("LAN_USER_05","Password"); //LAN_PASSWORD
define("LAN_USER_06","Photograph");
define("LAN_USER_07","Avatar");
// define("LAN_USER_08","Email address");
define("LAN_USER_09","Signature");
define("LAN_USER_10","Hide email");
//define("LAN_USER_11","XUP file");
define("LAN_USER_12","User class");
define("LAN_USER_13","ID");
define("LAN_USER_14","Join Date");
define("LAN_USER_15","Last Visit");
define("LAN_USER_16","Current Visit");
// define("LAN_USER_17","Comments");
define("LAN_USER_18","IP Address");
define("LAN_USER_19","Ban");
define("LAN_USER_20","Prefs");
define("LAN_USER_21","Visits");
define("LAN_USER_22","Admin");
define("LAN_USER_23","Perms");
define("LAN_USER_24","Password Change");


// Start here when assigning new messages to leave space for more field names
define("LAN_USER_31", "Main site administrator");				// LAN_417
define("LAN_USER_32", "Site administrator");					// LAN_418
define("LAN_USER_33", "no information");						// LAN_401
define("LAN_USER_34", "ago");									// LAN_426
define("LAN_USER_35", "[hidden by request]");					// LAN_143
define("LAN_USER_36", "Click here to View User Comments");		// LAN_423
define("LAN_USER_37", "Click here to View Forum Posts");		// LAN_424
define("LAN_USER_38", "Click here to update your information");	// LAN_411
define("LAN_USER_39", "Click here to edit this user's information");	// LAN_412
define("LAN_USER_40", "previous member");						// LAN_414
define("LAN_USER_41", "next member");							// LAN_415
define("LAN_USER_42", "no photo");								// LAN_408
define("LAN_USER_43", "delete photo");							// LAN_413
define("LAN_USER_44", "Miscellaneous");							// LAN_410
define("LAN_USER_45", "DESC");									// LAN_420
define("LAN_USER_46", "ASC");									// LAN_421
// define("LAN_USER_47", "Go");									// LAN_422
// define("LAN_USER_48", "Error");									// LAN_20
define("LAN_USER_49", "There is no information for that user as they are not registered at");			// LAN_137
define("LAN_USER_50", "Member Profile");						// LAN_402
define("LAN_USER_51", "That is not a valid user.");				// LAN_400
define("LAN_USER_52", "Registered members");					// LAN_140
define("LAN_USER_53", "No registered members yet.");			// LAN_141
define("LAN_USER_54", "Level");							     // USERLAN_1
define("LAN_USER_55", "You do not have access to view this page.");	// USERLAN_2
define("LAN_USER_56", "Registered members: ");					// LAN_138
define("LAN_USER_57", "Order: ");								// LAN_139
define("LAN_USER_58", "Member");								// LAN_142
define("LAN_USER_59", "Joined");								// LAN_145
define("LAN_USER_60", "Email Address: ");						// LAN_112
//define("LAN_USER_61", "Rating");								// LAN_406 now LAN_RATING
define("LAN_USER_62", "Send Private Message");					// LAN_425
define("LAN_USER_63", "Real Name: ");							// LAN_308
define("LAN_USER_64", "Site Stats");							// LAN_403
define("LAN_USER_65", "Last visit");							// LAN_404
define("LAN_USER_66", "Visits to site since registration");		// LAN_146
define("LAN_USER_67", "Chatbox posts");							// LAN_147
define("LAN_USER_68", "Comments posted");						// LAN_148
define("LAN_USER_69", "Forum posts");							// LAN_149
//define("LAN_USER_70", "Show");									// LAN_419
define("LAN_USER_71", "Signature: ");							// LAN_120
define("LAN_USER_72", "Avatar: ");								// LAN_121
define("LAN_USER_73", "choice of Content/Mail-lists");
define("LAN_USER_74", "Custom Title");
define("LAN_USER_75", "Your [x] is required");		// Replaces LAN_SIGNUP_6, LAN_SIGNUP_7 combination
define("LAN_USER_76", "Subscribed to");							// LAN_USET_5
define("LAN_USER_77", "Your password must be at least [x] characters long.");	// Replaces LAN_SIGNUP_4, LAN_SIGNUP_5 combination
define("LAN_USER_78", "Min.");									// LAN_SIGNUP_1
define("LAN_USER_79", "chars.");								// LAN_SIGNUP_2
define("LAN_USER_80", "the name displayed on site");			// LAN_8
define("LAN_USER_81", "Username: ");							// LAN_9
define("LAN_USER_82", "the name you use to login to the site");	// LAN_10
define("LAN_USER_83", "Hide email address?: ");					// LAN_113
define("LAN_USER_84", "This will prevent your email address from being displayed on site");	// LAN_114
define("LAN_USER_85", "If you want to change your user name, you must ask a site administrator");
define("LAN_USER_86", "Maximum avatar size is [x]- x [y] pixels");
define("LAN_USER_87", "Login to rate this user!");

// social plugin
define("LAN_XUP_ERRM_01", "Signup failed! This feature is disabled.");
define("LAN_XUP_ERRM_02", "Signup failed! Wrong provider.");
define("LAN_XUP_ERRM_03", "Log in Failed! Wrong provider.");
define("LAN_XUP_ERRM_04", "Signup failed! User already signed in.");
define("LAN_XUP_ERRM_05", "Signup failed! User already exists. Please use 'login' instead.");
define("LAN_XUP_ERRM_06", "Signup failed! Can't access user email - registration without an email is impossible.");
define("LAN_XUP_ERRM_07", "Social Login Tester");
define("LAN_XUP_ERRM_08", "Please log out of e107 before testing the user login/signup procedure.");
define("LAN_XUP_ERRM_10", "Test signup/login with [x]");
define("LAN_XUP_ERRM_11", "Logged in:");
define("LAN_XUP_ERRM_12", "Test logout");

// Error messages for when user data is missing. Done this way so that other code can override the default messages

// 	- [Berckoff] Used in validator_class for error handling, maybe moved to a more suitable place?
if (!defined("USER_ERR_01")) { define("USER_ERR_01","Missing value");  }
if (!defined("USER_ERR_02")) { define("USER_ERR_02","Unexpected value");  }
if (!defined("USER_ERR_03")) { define("USER_ERR_03","Value contains invalid characters");  }
if (!defined("USER_ERR_04")) { define("USER_ERR_04","Value too short");  }
if (!defined("USER_ERR_05")) { define("USER_ERR_05","Value too long");  }
if (!defined("USER_ERR_06")) { define("USER_ERR_06","Duplicate value");  }
if (!defined("USER_ERR_07")) { define("USER_ERR_07","Value not allowed");  }
if (!defined("USER_ERR_08")) { define("USER_ERR_08","Entry disabled");  }
if (!defined("USER_ERR_09")) { define("USER_ERR_09","Invalid word");  }
if (!defined("USER_ERR_10")) { define("USER_ERR_10","Password fields different");  }
if (!defined("USER_ERR_11")) { define("USER_ERR_11","Banned email address");  }
if (!defined("USER_ERR_12")) { define("USER_ERR_12","Invalid format for email address");  }
if (!defined("USER_ERR_13")) { define("USER_ERR_13","Data error");  }
if (!defined("USER_ERR_14")) { define("USER_ERR_14","Banned user");  }
if (!defined("USER_ERR_15")) { define("USER_ERR_15","User name and display name cannot be different");  }
if (!defined("USER_ERR_16")) { define("USER_ERR_16","Software error");  }
if (!defined("USER_ERR_17")) { define("USER_ERR_17","Value too low");  }
if (!defined("USER_ERR_18")) { define("USER_ERR_18","Value too high");  }
if (!defined("USER_ERR_19")) { define("USER_ERR_19","General error");  }
if (!defined("USER_ERR_20")) { define("USER_ERR_20","Image too wide");  }
if (!defined("USER_ERR_21")) { define("USER_ERR_21","Image too high");  }
if (!defined("USER_ERR_22")) { define("USER_ERR_22","Unspecified error");  }
if (!defined("USER_ERR_23")) { define("USER_ERR_23","Disallowed value (exact match)");  }



