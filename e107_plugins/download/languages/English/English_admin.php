<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */
// define("LAN_PLUGIN_DOWNLOAD_NAME",	   "Downloads");
define("LAN_DL_OPTIONS",               "Options"); //FIXME Use Generic
define("LAN_DL_DOWNLOAD_OPT_GENERAL",  "General");
define("LAN_DL_DOWNLOAD_OPT_BROKEN",   "Reporting");
define("LAN_DL_DOWNLOAD_OPT_AGREE",    "Agreements");
define("LAN_DL_DOWNLOAD_OPT_SECURITY", "Protection");
define("LAN_DL_UPLOAD",                "Upload"); //FIXME Use Generic
define("LAN_DL_USE_PHP",               "Use PHP");
define("LAN_DL_USE_PHP_INFO",          "Checking this will send all download requests through PHP");
define("LAN_DL_SUBSUB_CAT",            "Show sub-sub-categories");
define("LAN_DL_SUBSUB_CAT_INFO",       "Checking this will show the sub-sub-categories on the main download page");
define("LAN_DL_SUBSUB_COUNT",          "Combine category counts");
define("LAN_DL_SUBSUB_COUNT_INFO",     "Include sub-sub-category counts in sub-category counts");

define("DOWLAN_1",   "Download added to database.");
define("DOWLAN_2",   "Download updated in database.");
// define("DOWLAN_3",   "Download deleted.");
// define("DOWLAN_4",   "Please tick the confirm box to delete the download");
define("DOWLAN_5",   "There are no download categories defined yet, until you define some you cannot enter any downloads.");
// define("DOWLAN_6",   "No existing downloads");
// define("DOWLAN_7",   "Existing Downloads"); //FIXME Use Generic
define("DOWLAN_8",   "Nothing changed - not saved");
define("DOWLAN_9",   "Download detail:");
define("DOWLAN_10",  "Uploads"); //FIXME Use Generic
define("DOWLAN_11",  "Category"); //FIXME Use Generic
define("DOWLAN_12",  "Name"); //FIXME Use Generic
define("DOWLAN_13",  "File");
// define("DOWLAN_14",  "Enter address if download is an external file");
define("DOWLAN_15",  "Author"); //FIXME Use Generic
define("DOWLAN_16",  "Author Email"); //FIXME Use Generic
define("DOWLAN_17",  "Author Website");
define("DOWLAN_18",  "Description"); //FIXME Use Generic
define("DOWLAN_19",  "Main image");
define("DOWLAN_20",  "Thumbnail image");
define("DOWLAN_21",  "Status"); //FIXME Use Generic
define("DOWLAN_22",  "List uploads");
define("DOWLAN_23",  "File types");
define("DOWLAN_24",  "Update Download");
define("DOWLAN_25",  "Submit Download");
// define("DOWLAN_26",  "Uploads enabled?");
define("DOWLAN_27",  "Download");
//define("DOWLAN_28",  "None");//LAN_NONE
define("DOWLAN_29", "Requested");
define("DOWLAN_31",  "Categories");
define("DOWLAN_32",  "Downloads");
define("DOWLAN_33",  "Are you sure you want to delete this download?"); //FIXME Use Generic
// define("DOWLAN_34",  "Are you sure you want to delete this download category?"); //FIXME Use Generic
// define("DOWLAN_35",  "Maximum file size");
// define("DOWLAN_36",  "deleted"); //FIXME Use Generic
// define("DOWLAN_37",  "Parent");
// define("DOWLAN_38",  "No existing categories");
// define("DOWLAN_39",  "Download categories");
// define("DOWLAN_40",  "None - main parent");
// define("DOWLAN_41",  "Icon"); //FIXME Use Generic
define("DOWLAN_42",  "View Images");
define("DOWLAN_43",  "Visible to");
// define("DOWLAN_44",  "Selection will make the category visible to only users in that class");
// define("DOWLAN_45",  "Create Category"); //FIXME Use Generic
// define("DOWLAN_46",  "Update Category"); //FIXME Use Generic
// define("DOWLAN_47",  "Category created"); //FIXME Use Generic
// define("DOWLAN_48",  "Category Updated"); //FIXME Use Generic
// define("DOWLAN_49",  "Download Category"); //FIXME Use Generic
// define("DOWLAN_50",  "Download Category"); //FIXME Use Generic
// define("DOWLAN_51",  "No public uploads will be permitted if disabled");
// define("DOWLAN_52",  "Files");
// define("DOWLAN_53",  "Subcategory");
// define("DOWLAN_54",  "Subcategories");
define("DOWLAN_55",  "Number of downloads to display per page");
define("DOWLAN_56",  "Sort by ");
// define("DOWLAN_57",  "Clear filters");
// define("DOWLAN_58",  "Absolute maximum upload size in bytes. Further limited by settings from php.ini, and by the settings in filetypes.xml (upload_max_filesize = %1, post_max_size = %2)");
define("DOWLAN_59",  "Filename");
// define("DOWLAN_60",  "Select to allow only certain users to upload");
// define("DOWLAN_61",  "Permissions");
define("DOWLAN_62",  "Ascending");
define("DOWLAN_63",  "Descending");
define("DOWLAN_64",  "Update Options");
define("DOWLAN_65",  "Options Updated");
define("DOWLAN_66",  "Filesize");
//define("DOWLAN_67",  "ID"); //FIXME Use Generic // LAN_ID
define("DOWLAN_68",  "File Missing!");
// define("DOWLAN_69",  "Downloads handled by PHP");
// define("DOWLAN_70",  "Checking this will send all download requests through PHP.");
// define("DOWLAN_71",  "This page helps you create a file for managing file upload permissions. The file is saved as ../e107_files/temp/filetypes_.xml, and must be copied to ../e107_admin/filetypes.xml before it takes effect.");
// define("DOWLAN_72",  "Source for values: ");
// define("DOWLAN_73",  "Userclass"); //FIXME Use Generic
// define("DOWLAN_74",  "File extensions");
// define("DOWLAN_75",  "Max upload size");
// define("DOWLAN_76",  "Delete"); //FIXME Use Generic
// define("DOWLAN_77",  "Save and generate file");
// define("DOWLAN_78",  "Date");
// define("DOWLAN_79",  "Uploader");
// define("DOWLAN_80",  "There");
// define("DOWLAN_81",  "is");
// define("DOWLAN_82",  "are");
// define("DOWLAN_83",  "unmoderated public download");
// define("DOWLAN_84",  "unmoderated public downloads");
// define("DOWLAN_85",  "This page helps you create a file for managing file upload permissions. The file is saved as <strong>--SOURCE--</strong>, and must be copied to <strong>--DEST--</strong>  before it takes effect.");
// define("DOWLAN_86",  "Settings written to ");
// define("DOWLAN_87",  "Now move this file to ");
// define("DOWLAN_88",  "Error writing file: ");
// define("DOWLAN_90",  "Add new entry");
// define("DOWLAN_91",  "Copy to download manager");
define("DOWLAN_100", "Activate Download Agreement");
define("DOWLAN_101", "Agreement Text");
define("DOWLAN_102", "Allow Comments?");
define("DOWLAN_103", "Remove from Uploads");
define("DOWLAN_104", "was removed from public uploads");
define("DOWLAN_105", "Back to Public Uploads");
define("DOWLAN_106", "May be download by");
define("DOWLAN_107", "Limit download count");
define("DOWLAN_108", "Limit download bandwidth");
define("DOWLAN_109", "every");
define("DOWLAN_110", "days");
define("DOWLAN_111", "kb"); //FIXME Use Generic
define("DOWLAN_112", "Limits");
define("DOWLAN_113", "Userclass"); //FIXME Use Generic
define("DOWLAN_114", "Add New Limit");
define("DOWLAN_115", "Update limits");
// define("DOWLAN_116", "Limit for that userclass already exists");
// define("DOWLAN_117", "Limit successfully added");
// define("DOWLAN_118", "Limit not added - unknown error");
// define("DOWLAN_119", "Limit successfully removed");
// define("DOWLAN_120", "Limit not removed - unknown error");
// define("DOWLAN_121", "Limit successfully updated");
define("DOWLAN_122", "Inactive");
define("DOWLAN_123", "Active - File is subject to download limits");
define("DOWLAN_124", "Active - File is NOT subject to download limits");
define("DOWLAN_125", "Download limits active");
// define("DOWLAN_126", "Activation status updated");
// define("DOWLAN_127", "Only enter filesize if the download is an external file"); // TODO not used?
define("DOWLAN_128", "Mirrors");
define("DOWLAN_129", "leave blank if not using mirrors");
define("DOWLAN_130", "Add another mirror");
define("DOWLAN_131", "Select local file");
define("DOWLAN_132", "Please enter mirror to use, then address to download and filesize");
define("DOWLAN_133", "Mirror updated in database");
define("DOWLAN_134", "Mirror saved in database");
define("DOWLAN_135", "Mirror deleted");
define("DOWLAN_136", "image");
define("DOWLAN_137", "Are you sure you want to delete this mirror?");
define("DOWLAN_138", "Existing Mirrors");
define("DOWLAN_139", "Address");
define("DOWLAN_140", "Upload local images to e107_files/downloadimages to show them here, or enter full address if image is remote");
define("DOWLAN_141", "Location");
define("DOWLAN_142", "Update Mirror");
define("DOWLAN_143", "Create Mirror");
define("DOWLAN_144", "No mirrors defined in mirror section.");
define("DOWLAN_145", "Download visible to");
define("DOWLAN_146", "Custom Download-denial message or URL");
// define("DOWLAN_147", "Icon for empty category");
define("DOWLAN_148", "Check to update date stamp to current time");
define("DOWLAN_149", "URL"); //FIXME Use Generic
define("DOWLAN_150", "Email admin when broken download reported");
define("DOWLAN_151", "Broken-download reporting available to");
define("DOWLAN_152", "Couldn't move file");
define("DOWLAN_153", "Move file into download folder");
define("DOWLAN_154", "if using mirrors, select how they will be displayed");
define("DOWLAN_155", "Mirror display type:");
define("DOWLAN_156", "show mirror list, allow user to choose mirror");
define("DOWLAN_157", "use random mirror - no user choice");
// define("DOWLAN_158", "Show sub-sub-categories on main download page");
// define("DOWLAN_159", "Include sub-sub-category counts in subcategory counts");
define("DOWLAN_160", "Mirror list order");
define("DOWLAN_161", "Random");
// define("DOWLAN_162", "Copy to newspost");

define("DOWLAN_164", "Recent downloads age (in days)");
define("DOWLAN_165", "Download Maintenance");
define("DOWLAN_166", "Duplicates");
define("DOWLAN_167", "Orphans");
define("DOWLAN_168", "Missing");
define("DOWLAN_169", "Inactive");

define("DOWLAN_171", "Log");
define("DOWLAN_172", "No entries");
define("DOWLAN_173", "Are you sure you want to delete this file?");
define("DOWLAN_174", "No orphaned files found");
define("DOWLAN_175", "Local");
define("DOWLAN_176", "External");
// define("DOWLAN_177", "Maintenance options");
define("DOWLAN_178", "No category");
define("DOWLAN_179", "Select an option from the Maintenance Options menu");
define("DOWLAN_180", "File size (database/disk)");
define("DOWLAN_181", "Not readable");
define("DOWLAN_182", "Timestamp");
// define("DOWLAN_183", "Advanced search");
// define("DOWLAN_184", "");
define("DOWLAN_185", "Files referenced multiple times in the database");
define("DOWLAN_186", "Files not referenced in the database");
define("DOWLAN_187", "Database entries referencing non-existent files");
define("DOWLAN_188", "Database entries marked as inactive");
define("DOWLAN_189", "Database entires not associated with a category");
define("DOWLAN_190", "Size differences between database entry and the file itself");
define("DOWLAN_191", "Downloads log entries");
define("DOWLAN_192", "Execute selected option");
define("DOWLAN_193", "Select option");
// define("DOWLAN_194", "Search"); //FIXME Use Generic
define("DOWLAN_195", "Mirror type");
define("DOWLAN_196", "list"); //FIXME Use Generic
define("DOWLAN_197", "random");
// define("DOWLAN_198", "Filter"); //FIXME Use Generic


// define('DOWLAN_FP_01', 'Front page');
// define('DOWLAN_FP_02', 'Category list');


define("DOWLAN_HELP_1", "Help");
define("DOWLAN_HELP_2", "<p>Create/edit a download.</p><p>Enter only one of: File, URL or Mirror.</p><p>Ensure you select a category, otherwise your download will not be visible on the downloads page.</p>");
define("DOWLAN_HELP_3", "Help for cat");
define("DOWLAN_HELP_4", "Help for opt");
define("DOWLAN_HELP_5", "Use the maintenance pages to find duplicate downloads, find orphaned files, find missing (broken) entries, manage inactive downloads, refresh file sizes and view the downlaod log.");
define("DOWLAN_HELP_6", "Help for limits");
define("DOWLAN_HELP_7", "Help for mirror");
define("DOWLAN_HELP_8", "Help for upload list");
define("DOWLAN_HELP_9", "Help for upload types");
define("DOWLAN_HELP_10", "Help for upload options");

// define("DOWLAN_INSTALL_DONE", "Your download plugin is now installed");
// define("DOWLAN_DESCRIPTION", "This plugin is a fully featured Download system");
// define("DOWLAN_CAPTION", "Configure Download");

define("LAN_DL_SECURITY_DESCRIPTION", "Downloads can make use of server-side URL protection features to prevent hotlinking and/or enforce link expiry. " .
	"This section should be configured before the download server is configured to reduce the chance of disruption to downloaders.");
define("LAN_DL_SECURITY_MODE", "URL protection mode");
define("LAN_DL_SECURITY_MODE_NONE", "None (Default)");
define("LAN_DL_SECURITY_MODE_NGINX_SECURELINKMD5", "NGINX secure_link_md5");
define("LAN_DL_SECURITY_NGINX_SUPPORTED_VARIABLES_TOGGLE", "Click to toggle list of supported NGINX variables");
define("LAN_DL_SECURITY_NGINX_SECURELINKMD5_EXPRESSION",
	"<a target='_blank' href='https://nginx.org/en/docs/http/ngx_http_secure_link_module.html#secure_link_md5'>NGINX secure_link_md5 expression</a>");
define("LAN_DL_SECURITY_NGINX_SECURELINKMD5_EXPRESSION_HELP", "Same expression as configured on the server");
define("LAN_DL_SECURITY_LINK_EXPIRY", "Duration of validity in seconds");
define("LAN_DL_SECURITY_LINK_EXPIRY_HELP", "Number of seconds the download link should last after being generated. " .
	"Only effective if the expression supports expiry time. " .
    "Defaults to a very long time if this field is left blank.");