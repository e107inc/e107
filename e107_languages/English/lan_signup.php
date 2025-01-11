<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Language file - User signup
 *
*/
if(!defined('PAGE_NAME'))
{
	define("PAGE_NAME", "Register");
}
/*
//define("LAN_103", "That username is invalid. Please choose a different one");
//define("LAN_104", "That username is taken.  Please choose a different one");
//define("LAN_105", "The two passwords don't match");
//define("LAN_106", "That doesn"t appear to be a valid email address");
define("LAN_108", "Registration complete");
define("LAN_185", "You left required field(s) blank");
// define("LAN_201", "Yes");
// define("LAN_200", "No");
// define("LAN_399", "Continue");
define("LAN_407", "Please keep this email for your own information. Your password has been encrypted and cannot be retrieved if you misplace or forget it. You can, however, request a new password if this happens.\n\nThank you for registering.\n\nFrom");
//define("LAN_408", "A user with that email address already exists. Please use the "forgot password" screen to retrieve your password.");

//define("LAN_409", "Invalid characters in username");
//define("LAN_411", "That display name already exists in the database, please choose a different display name");
*/

define("LAN_EMAIL_01", "Dear");
define("LAN_EMAIL_04", "Please keep this email for your own information.");
define("LAN_EMAIL_05", "Your password has been encrypted and cannot be retrieved if you misplace or forget it. You can, however, request a new password if this happens.");
define("LAN_EMAIL_06", "Thank you for registering.");


define("LAN_SIGNUP_1", "Min.");
define("LAN_SIGNUP_2", "chars.");
define("LAN_SIGNUP_3", "Code verification failed.");
define("LAN_SIGNUP_4", "Your password must be at least ");
define("LAN_SIGNUP_5", " characters long.");
//define("LAN_SIGNUP_6", "Your ");			See LAN_USER_75
//define("LAN_SIGNUP_7", " is required");	See LAN_USER_75
define("LAN_SIGNUP_8", "Thank you!");
define("LAN_SIGNUP_9", "Unable to proceed.");
//define("LAN_SIGNUP_10", "Yes");
define("LAN_SIGNUP_11", ".");
define("LAN_SIGNUP_12", "please keep your username and password written down in a safe place as they cannot be retrieved if you lose them.");
define("LAN_SIGNUP_13", "You can now log in from the Login box, or from [here].");
define("LAN_SIGNUP_14", "here");
define("LAN_SIGNUP_15", "Please contact the main site admin");
define("LAN_SIGNUP_16", "if you require assistance.");
define("LAN_SIGNUP_17", "Please confirm that you are age 13 or over.");
define("LAN_SIGNUP_18", "Your registration has been received and created with the following login information:");
//define("LAN_SIGNUP_19", "Username:"); // now LAN_LOGINNAME
//define("LAN_SIGNUP_20", "Password:"); // now LAN_PASSWORD
define("LAN_SIGNUP_21", "Your account is currently marked as being inactive. To activate your account please go to the following link:");
define("LAN_SIGNUP_22", "click here");
define("LAN_SIGNUP_23", "to login.");
define("LAN_SIGNUP_24", "Thank you for registering at");
define("LAN_SIGNUP_25", "Upload your avatar");
define("LAN_SIGNUP_26", "Upload your photograph");
//define("LAN_SIGNUP_27", "Show"); //not found in signup.php 
//define("LAN_SIGNUP_28", "choice of Content/Mail-lists");		Now LAN_USER_73
//define("LAN_SIGNUP_29", "A verification email will be sent to the email address you enter here so it must be valid.");
define("LAN_SIGNUP_30", "If you do not wish to display your email address on this site, please select 'Yes' for the 'Hide email address?' option.");
//define("LAN_SIGNUP_31", "URL to your XUP file");
//define("LAN_SIGNUP_32", "What"s an XUP file?");
// define("LAN_SIGNUP_33", "Type path or choose avatar");
define("LAN_SIGNUP_34", "Please note: Any image uploaded to this server that is deemed inappropriate by the administrators will be deleted immediately.");
//define("LAN_SIGNUP_35", "Click here to register using an XUP file");
define("LAN_SIGNUP_36", "An error has occurred creating your user information, please contact the site admin");
define("LAN_SIGNUP_37", "This stage of registration is complete. The site admin will need to approve your membership.  Once this has been done you will receive a confirmation email alerting you that your membership has been approved.");
define("LAN_SIGNUP_38", "You entered two different email addresses. Please enter a valid email address in the two fields provided");
define("LAN_SIGNUP_39", "Re-type Email Address:");
define("LAN_SIGNUP_40", "Activation not necessary");
define("LAN_SIGNUP_41", "Your account is already activated.");
define("LAN_SIGNUP_42", "There was a problem, the registration mail was not sent, please contact the website administrator.");
define("LAN_SIGNUP_43", "Email Sent");
define("LAN_SIGNUP_44", "Activation email sent to:");
define("LAN_SIGNUP_45", "Please check your inbox.");
define("LAN_SIGNUP_47", "Resend Activation Email");
define("LAN_SIGNUP_48", "Username or Email");
define("LAN_SIGNUP_49", "If you registered with the wrong email address, as well as filling in the box above, type a new email address and your password here:");
define("LAN_SIGNUP_50", "New Email");
define("LAN_SIGNUP_51", "Old Password");
//define("LAN_SIGNUP_52", "Incorrect Password");//LAN_INCORRECT_PASSWORD
define("LAN_SIGNUP_53", "field failed validation test");
define("LAN_SIGNUP_54", "Click here to fill in your details to register");
//define("LAN_SIGNUP_55", "That display name is too long. Please choose another");
//define("LAN_SIGNUP_56", "That display name is too short. Please choose another");
//define("LAN_SIGNUP_57", "That login name is too long. Please choose another");
define("LAN_SIGNUP_58", "Signup Preview");
define("LAN_SIGNUP_59","**** If the link doesn't work, please check that part of it has not overflowed onto the next line. ****");
define("LAN_SIGNUP_60", "Signup email resend requested");
define("LAN_SIGNUP_61", "Send succeeded");
define("LAN_SIGNUP_62", "Send failed");
define("LAN_SIGNUP_63", "Password reset email resend requested");
define("LAN_SIGNUP_64", "That doesn't appear to be valid user information");
define("LAN_SIGNUP_65", "You have been assigned the following login name");
define("LAN_SIGNUP_66", "Please make a note of it.");
define("LAN_SIGNUP_67", "This will be assigned by the system after signup");
//define("LAN_SIGNUP_68","Error: Unable to open remote XUP file");


define("LAN_SIGNUP_71", "You have reached the site limit for account registrations. Please login using one of your other accounts.");		// LAN_202
define("LAN_SIGNUP_72", "Thanks for signing up on [sitename]! We just sent you a confirmation email to [email]. Please click on the confirmation link in the email to complete your sign up and activate your account.");  	// LAN_405
define("LAN_SIGNUP_73", "Thank you!");											// LAN_406
define("LAN_SIGNUP_74", "Your account has now been activated, please");			// LAN_401
define("LAN_SIGNUP_75", "Registration activated");								// LAN_402
define("LAN_SIGNUP_76", "Thank you! You are now a registered member of");		// LAN_107
define("LAN_SIGNUP_77", "This site complies with The Children's Online Privacy Protection Act of 1998 (COPPA) and as such cannot accept registrations from users under the age of 13 without a written permission document from their parent or guardian. For more information you can read the legislation");	// LAN_109
define("LAN_SIGNUP_78", "Registration");										// LAN_110
define("LAN_SIGNUP_79", "Register");											// LAN_123
define("LAN_SIGNUP_80", "Please enter your details below.");					// LAN_309
define("LAN_SIGNUP_81", "Username: ");											// LAN_9
define("LAN_SIGNUP_82", "the name that you use to login");						// LAN_10
define("LAN_SIGNUP_83", "Password: ");											// LAN_17
define("LAN_SIGNUP_84", "Re-type Password: ");									// LAN_111
define("LAN_SIGNUP_85", "Usernames and passwords are case-sensitive.");	// LAN_400
//define("LAN_SIGNUP_86", "Email Address: ");										// LAN_112 = LAN_USER_60
//define("LAN_SIGNUP_87", "Hide email address?: ");								// LAN_113 = LAN_USER_83
//define("LAN_SIGNUP_88", "This will prevent your email address from being displayed on site");	// LAN_114
define("LAN_SIGNUP_89", "Display Name: ");										// LAN_7
define("LAN_SIGNUP_90", "the name that will be displayed on site");				// LAN_8
define("LAN_SIGNUP_91", "Real Name: ");											// LAN_308
//define("LAN_SIGNUP_92", "your real name, including first and last name");		// LAN_310
define("LAN_SIGNUP_93", "Signature: ");											// LAN_120
define("LAN_SIGNUP_94", "Avatar: ");											// LAN_121
define("LAN_SIGNUP_95", "Enter code visible in the image");						// LAN_410
define("LAN_SIGNUP_96", "Registration details for");							// LAN_404 (used in email)
define("LAN_SIGNUP_97", "Welcome to");  										// LAN_403 (used in email)

define("LAN_SIGNUP_98", "Confirm Your Email Address");
define("LAN_SIGNUP_99", "Problem Encountered");
define("LAN_SIGNUP_100", "Admin Approval Pending");
define("LAN_SIGNUP_101", "Update of records failed - please contact the site administrator");
//define("LAN_SIGNUP_102", "Signup refused");
define("LAN_SIGNUP_103", "Too many users already using IP address: ");
define("LAN_SIGNUP_105", "Unable to action your request - please contact the site administrator");		// Two people with same password.
define("LAN_SIGNUP_106", "Unable to action your request - do you already have an account here?");		// Trying to set email same as existing

define("LAN_LOGINNAME", "Username");
//define("LAN_PASSWORD", "Password");
define("LAN_USERNAME", "Display Name");

define("LAN_SIGNUP_107", "Password must be a minimum of [x] characters and include at least one UPPERCASE letter and a digit");
define("LAN_SIGNUP_108", "Must be a valid email address");
define("LAN_SIGNUP_109", "Is CaSe sensitive and must not contain spaces.");//TODO check against regex requirements
define("LAN_SIGNUP_110", "Your full name");
define("LAN_SIGNUP_111", "Enter a URL to your image or choose an existing avatar.");
define("LAN_SIGNUP_112", "You are currently logged in as Main Admin.");

define("LAN_SIGNUP_113", "Subscription(s)");

define("LAN_SIGNUP_114", "User registration is currently disabled.");
define("LAN_SIGNUP_115", "Preview Activation Email");
define("LAN_SIGNUP_116", "Preview After Form Submit");
define("LAN_SIGNUP_117", "Send a Test Activation");
define("LAN_SIGNUP_118", "To [x]");
define("LAN_SIGNUP_119", "Don't send email");
define("LAN_SIGNUP_120", "OR");
define("LAN_SIGNUP_121", "Use a different email address");

define("LAN_SIGNUP_122", "Privacy Policy");
define("LAN_SIGNUP_123", "Terms and conditions");
define("LAN_SIGNUP_124", "By signing up you agree to our [x] and our [y].");
define("LAN_SIGNUP_125", "Min. [x] chars.");

