<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

// TODO - LANS - MAJOR LAN REWRITE NEEDED 

define("CORE_DATE_ORDER", "dmy");			// Temporary until we find somewhere better to put it.
											// Defines order of field entry/display in date boxes
											// Acceptable values: dmy, mdy, ymd
define("LAN_MAILOUT_01", "From Name");
define("LAN_MAILOUT_02", "From Email");
define("LAN_MAILOUT_03", "To");
define("LAN_MAILOUT_04", "Cc");
define("LAN_MAILOUT_05", "Bcc");
define("LAN_MAILOUT_06", "Subject");
define("LAN_MAILOUT_07", "Attachment");
define("LAN_MAILOUT_08", "Send Email");
define("LAN_MAILOUT_09", "Send format");
define("LAN_MAILOUT_10", "User Subscribed");
define("LAN_MAILOUT_11", "Insert Variables");
define("LAN_MAILOUT_12", "All Users");
define("LAN_MAILOUT_13", "All Unverified Users ");
define("LAN_MAILOUT_14", "Display Name");
define("LAN_MAILOUT_15", "Mailout");
define("LAN_MAILOUT_16", "Username");
define("LAN_MAILOUT_17", "Signup link");
define("LAN_MAILOUT_18", "User ID");
define("LAN_MAILOUT_19", "No target email address specified");
define("LAN_MAILOUT_20", "Sendmail-path");
define("LAN_MAILOUT_21", "Bulk mailing Entries");
define("LAN_MAILOUT_22", "There are currently no saved entries");
define("LAN_MAILOUT_23", "userclass: ");
define("LAN_MAILOUT_24", "email(s) are ready to be sent");
define("LAN_MAILOUT_25", "Bulk mailing controls");
define("LAN_MAILOUT_26", "Pause bulk mailing every");
define("LAN_MAILOUT_27", "emails for ");
define("LAN_MAILOUT_28", "Save Changes");
define("LAN_MAILOUT_29", "seconds");
define("LAN_MAILOUT_30", "Used mostly with SMTP keepalive. A pause of more than 30 seconds may cause the browser to time-out");
define("LAN_MAILOUT_31", "Bounced Email Processing");
// define("LAN_MAILOUT_32", "Email address");
define("LAN_MAILOUT_33", "Incoming Mail server");
define("LAN_MAILOUT_34", "Account (user) Name");
// define("LAN_MAILOUT_35", "Password");
define("LAN_MAILOUT_36", "Delete Bounced Mails after checking");
define("LAN_MAILOUT_37", "Proceed");
define("LAN_MAILOUT_38", "Cancel");
define("LAN_MAILOUT_39", "Emailing");
// define("LAN_MAILOUT_40", "You need to rename <b>e107.htaccess</b> to <b>.htaccess</b> in");
define("LAN_MAILOUT_41", "before sending mail from this page.");
//define("LAN_MAILOUT_42", "Warning");//NOT_USED
define("LAN_MAILOUT_43", "Username");
define("LAN_MAILOUT_44", "User Login");
define("LAN_MAILOUT_45", "User Email");
define("LAN_MAILOUT_46", "User-Match");
define("LAN_MAILOUT_47", "contains");
define("LAN_MAILOUT_48", "equals");
//define("LAN_MAILOUT_49", "Id");//LAN_ID
//define("LAN_MAILOUT_50", "Author");//LAN_AUTHOR
define("LAN_MAILOUT_51", "Subject");
define("LAN_MAILOUT_52", "Last mod");
define("LAN_MAILOUT_53", "Admins");
define("LAN_MAILOUT_54", "Self");
define("LAN_MAILOUT_55", "Userclass");
define("LAN_MAILOUT_56", "Last Visit");
define("LAN_MAILOUT_57", "Send bulk SMTP emails in blocks");			// SMTP KeepAlive option
//define("LAN_MAILOUT_58", "There is a problem with the attachment:");
//define("LAN_MAILOUT_59", "Mailing Progress");
//define("LAN_MAILOUT_60", "Sending...");
//define("LAN_MAILOUT_61", "There are no remaining emails to be sent.");
//define("LAN_MAILOUT_62", "Emails sent:");
//define("LAN_MAILOUT_63", "Emails failed:");
//define("LAN_MAILOUT_64", "Total time elapsed:");
//define("LAN_MAILOUT_65", "seconds");
//define("LAN_MAILOUT_66", "Cancelled Successfully");
define("LAN_MAILOUT_67", "The email could not be sent. Please review your SMTP settings, or select another mailing method and try again.");
define("LAN_MAILOUT_68", "Registered Users");
define("LAN_MAILOUT_69", "matches, after ");
define("LAN_MAILOUT_70", " duplicates stripped.");
define("LAN_MAILOUT_71", "Total emails to send");
define("LAN_MAILOUT_72", "Mailshot logging");
define("LAN_MAILOUT_73", "No logging");
define("LAN_MAILOUT_74", "Logging only (no send)");
define("LAN_MAILOUT_75", "Log and send");
define("LAN_MAILOUT_76", "Include email info in log");
define("LAN_MAILOUT_77", "Email address sources");
define("LAN_MAILOUT_78", "Mailshot Status");
define("LAN_MAILOUT_79", "No mailshots to display");
//define("LAN_MAILOUT_80", "Date");//LAN_DATE
define("LAN_MAILOUT_81", "The email has been successfully sent, please check your inbox.");
define("LAN_MAILOUT_82", "Mails sent");
define("LAN_MAILOUT_83", "Mails to go");
define("LAN_MAILOUT_84", "Mail ID");
define("LAN_MAILOUT_85", "Originator");
define("LAN_MAILOUT_86", "Re-send");
define("LAN_MAILOUT_87", "SMTP Server");
define("LAN_MAILOUT_88", "SMTP Username");
define("LAN_MAILOUT_89", "SMTP Password");
define("LAN_MAILOUT_90", "SMTP Features");
define("LAN_MAILOUT_91", "POP before SMTP");
define("LAN_MAILOUT_92", "SSL");
define("LAN_MAILOUT_93", "TLS");
define("LAN_MAILOUT_94", "(Use SSL for gmail/googlemail)");
define("LAN_MAILOUT_95", "Use VERP for bulk mailing");
//define("LAN_MAILOUT_96", "none");//LAN_NONE
define("LAN_MAILOUT_97", "Mailer Results");
define("LAN_MAILOUT_98", "Orphaned entries");
define("LAN_MAILOUT_99", "Confirm retry mailshot");
define("LAN_MAILOUT_100", "Message");
define("LAN_MAILOUT_101", "Email Detail");
define("LAN_MAILOUT_102", "Detail of mailshot");
define("LAN_MAILOUT_103", "Results of attempts to send");
define("LAN_MAILOUT_104", "No attempt to send, or error saving result");
define("LAN_MAILOUT_105", "Details of up to 10 failures");
define("LAN_MAILOUT_106", "The email could not be sent. It appears that your server is not correctly configured to send emails, please try again using SMTP, or contact your hosts and ask them to check their sendmail / email server settings.");
define("LAN_MAILOUT_107", "at");
define("LAN_MAILOUT_108", "Result");
define("LAN_MAILOUT_109", "Show detail");
define("LAN_MAILOUT_110", "Send test email");
define("LAN_MAILOUT_111", "Email Title (not sent)");
define("LAN_MAILOUT_112", "Send test email to");
define("LAN_MAILOUT_113", "Test email from");
define("LAN_MAILOUT_114", "This is a test email, it appears that your email settings are working ok! [br][br] Regards [br] from the e107 website system.");
define("LAN_MAILOUT_115", "Bulk Emailing method");
define("LAN_MAILOUT_116", "If unsure, leave as php");
define("LAN_MAILOUT_117", "complete");
define("LAN_MAILOUT_118", "Click on proceed' to start sending emails. Click on 'cancel' to stop the run. Once complete, select another page. Unsent emails cal be viewed through the 'Mailshot status' screen");
define("LAN_MAILOUT_119", "Logging only, with errors");
define("LAN_MAILOUT_120", "Account type");
define("LAN_MAILOUT_121", "Standard POP3");
define("LAN_MAILOUT_122", "POP3, TLS disabled");
define("LAN_MAILOUT_123", "POP3 with TLS");
define("LAN_MAILOUT_124", "IMAP");
define("LAN_MAILOUT_125", "Text only");
define("LAN_MAILOUT_126", "Text and HTML");
define("LAN_MAILOUT_127", "Include theme");
define("LAN_MAILOUT_128", "Send Error");
define("LAN_MAILOUT_129", "Expiry Date");
define("LAN_MAILOUT_130", "Creation Date");
define("LAN_MAILOUT_131", "Sending Started");
define("LAN_MAILOUT_132", "Sending Complete");
//define("LAN_MAILOUT_133", "Source"); // Moved to lan_admin.php
define("LAN_MAILOUT_134", "Priority");
//define("LAN_MAILOUT_135", "Title");//LAN_TITLE
define("LAN_MAILOUT_136", "Mailout Status");
define("LAN_MAILOUT_137", "Mail Ref");
define("LAN_MAILOUT_138", "Email status");
define("LAN_MAILOUT_139", "Date active");
define("LAN_MAILOUT_140", "Recipient Email");
define("LAN_MAILOUT_141", "Recipient Name");
define("LAN_MAILOUT_142", "Recipient User ID");
define("LAN_MAILOUT_143", "Recipient ref.");
define("LAN_MAILOUT_144", "Bounced");
define("LAN_MAILOUT_145", "New email saved");
define("LAN_MAILOUT_146", "Error saving email");
define("LAN_MAILOUT_147", "Email updated");
define("LAN_MAILOUT_148", "User values");
define("LAN_MAILOUT_149", "Sender Email");
define("LAN_MAILOUT_150", "Sender Name");
define("LAN_MAILOUT_151", "Copy to");
define("LAN_MAILOUT_152", "Blind copy to");
define("LAN_MAILOUT_153", "Attachments");
define("LAN_MAILOUT_154", "Send Format");
define("LAN_MAILOUT_155", "Selectors");
define("LAN_MAILOUT_156", "Maximum number of emails to send per scheduler tick");
define("LAN_MAILOUT_157", "Value will depend on a number of factors, including how often your mail queue scheduler job is triggered and the rate at which your ISP will accept outgoing mail. Zero to clear queue each time");
define("LAN_MAILOUT_158", "Send now");
define("LAN_MAILOUT_159", "Hold email");
define("LAN_MAILOUT_160", "Cancel send");
define("LAN_MAILOUT_161", "IMPORTANT! This file appears to not exist");
define("LAN_MAILOUT_162", "IMPORTANT! You need to make this file executable");
define("LAN_MAILOUT_163", "Edit/Send Mail");
define("LAN_MAILOUT_164", "Email information not found");
define("LAN_MAILOUT_165", "Confirm delete the following stored email, including any recipient records");
define("LAN_MAILOUT_166", "General error deleting mail ref: [x]");
define("LAN_MAILOUT_167", "Error deleting mail content ref: [x]");
define("LAN_MAILOUT_168", "Mail content deleted ref: [x]");
define("LAN_MAILOUT_169", "Error deleting mail recipients ref: [x]");
define("LAN_MAILOUT_170", "Deleted [y] recipients for mail ref: [x]");
define("LAN_MAILOUT_171", "Confirm email delete");
define("LAN_MAILOUT_172", "Mail Type/Status");
define("LAN_MAILOUT_173", "Recipients");
define("LAN_MAILOUT_174", "Security check fail: [x] [z]");
define("LAN_MAILOUT_175", "Before");
define("LAN_MAILOUT_176", "Equal to");
define("LAN_MAILOUT_177", "After");
define("LAN_MAILOUT_178", "Last site visit");
define("LAN_MAILOUT_179", "Confirm email send");
define("LAN_MAILOUT_180", "Selection criteria:");
define("LAN_MAILOUT_181", "Show recipients");
define("LAN_MAILOUT_182", "Tidy database tables");
define("LAN_MAILOUT_183", "Error tidying database");
define("LAN_MAILOUT_184", "Database tidied");
define("LAN_MAILOUT_185", "Emails added to send queue");
define("LAN_MAILOUT_186", "General error putting mail ref: [x] on hold");
define("LAN_MAILOUT_187", "Email [x] put on hold");
define("LAN_MAILOUT_188", "General error sending mail ref: [x]");
define("LAN_MAILOUT_189", "Test address");

// Admin menu text
define("LAN_MAILOUT_190", "Create/Send Mail");
define("LAN_MAILOUT_191", "Saved emails");
define("LAN_MAILOUT_192", "Completed Mailshots");
define("LAN_MAILOUT_193", "Pending Mailshots");
define("LAN_MAILOUT_194", "Held Mailshots");
// define("LAN_MAILOUT_195", "");
// define("LAN_MAILOUT_196", "");

// Block of error messages kept together
define("LAN_MAILOUT_200", "No subject specified");
define("LAN_MAILOUT_201", "No meaningful data for email");
define("LAN_MAILOUT_202", "No text in email body");
define("LAN_MAILOUT_203", "No sender name specified");
define("LAN_MAILOUT_204", "No sender email address specified");
define("LAN_MAILOUT_205", "Email send format error");
define("LAN_MAILOUT_206", "Invalid mail ID ([x]) specified");
define("LAN_MAILOUT_207", "Template load error");
define("LAN_MAILOUT_208", "Template conversion error");


// Block of status messages kept together
define("LAN_MAILOUT_211", "Sent");
define("LAN_MAILOUT_212", "Failed");
define("LAN_MAILOUT_213", "Bounced");
define("LAN_MAILOUT_214", "To send");
define("LAN_MAILOUT_215", "Saved");
define("LAN_MAILOUT_216", "Code error");
define("LAN_MAILOUT_217", "Held");
define("LAN_MAILOUT_218", "Cancelled");
define("LAN_MAILOUT_219", "Partial");

// General messages continued
define("LAN_MAILOUT_220", "Email ID [x] cancelled");
define("LAN_MAILOUT_221", "Error cancelling email with ID [x]");
define("LAN_MAILOUT_222", "Default email format");
define("LAN_MAILOUT_223", "(Used for some system-generated emails)");
define("LAN_MAILOUT_224", "Inc. Images");
define("LAN_MAILOUT_225", "Include images in email");
define("LAN_MAILOUT_226", "[x] orphaned recipient record(s) removed");
define("LAN_MAILOUT_227", "Deleted [x] records from [y]");
define("LAN_MAILOUT_228", "[x] anomalies in mail_content corrected; records: [y]");
define("LAN_MAILOUT_229", "Email ID [x] put on hold");
define("LAN_MAILOUT_230", "Error holding email with ID [x]");
define("LAN_MAILOUT_231", "Bounced emails - Processing method");
define("LAN_MAILOUT_232", "None");
define("LAN_MAILOUT_233", "Auto-process script");
define("LAN_MAILOUT_234", "Mail account");
define("LAN_MAILOUT_235", "(Your server must forward or 'pipe' from the email address above to the script path above.)");
define("LAN_MAILOUT_236", "Last Bounce Processed");
define("LAN_MAILOUT_237", "Summary counters updated on [x] emails");
define("LAN_MAILOUT_238", "Earliest time to send");
define("LAN_MAILOUT_239", "Latest time to send");
define("LAN_MAILOUT_240", "Notify me when run complete");
define("LAN_MAILOUT_241", " (This is in addition to the standard e107 notify options)");
define("LAN_MAILOUT_242", "Additional options (only when sending)");
define("LAN_MAILOUT_243", "Notify");
define("LAN_MAILOUT_244", "Email sent: ");
define("LAN_MAILOUT_245", "Check for bounces automatically");
define("LAN_MAILOUT_246", "If checked, you will need to activate the task in the scheduler");
define("LAN_MAILOUT_247", "Email information:");
define("LAN_MAILOUT_248", "Completion status: ");
define("LAN_MAILOUT_249", "Send results:");
define("LAN_MAILOUT_250", "--- End of notification ---");
define("LAN_MAILOUT_251", "Copy and edit");
define("LAN_MAILOUT_252", "Does various consistency checks on the data, corrects counts, deletes temporary data");
define("LAN_MAILOUT_253", "No recipients found - check for database corruption");
define("LAN_MAILOUT_254", "View templated email");
define("LAN_MAILOUT_255", "Templated Email, ID: ");
define("LAN_MAILOUT_256", "Return");
define("LAN_MAILOUT_257", "Generated template");
//define("LAN_MAILOUT_258", "Template: ");//LAN_TEMPLATE

define("LAN_SEND", "Send");
define("LAN_HOLD", "Hold");

define("LAN_MAILOUT_260", "User-Type");
define("LAN_MAILOUT_261", "SMTP Port");
define("LAN_MAILOUT_262", "Template Preview");
define("LAN_MAILOUT_263", "Total Recipients");
define("LAN_MAILOUT_264", "Embed Media");
define("LAN_MAILOUT_265", "Pending");
define("LAN_MAILOUT_266", "Max Active");
define("LAN_MAILOUT_267", "Generate Public/Private keys");
define("LAN_MAILOUT_268", "Developer Mode Only");
define("LAN_MAILOUT_269", "Send Later");

define("LAN_MAILOUT_270", "Test SMTP Connection");
define("LAN_MAILOUT_271", "Authentication failed with username ([x]) and password ([y]):");




