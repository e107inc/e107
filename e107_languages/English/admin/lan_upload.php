<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_0.7/e107_languages/English/admin/lan_upload.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
define("UPLLAN_1", "Upload removed from list.");
define("UPLLAN_2", "Settings saved in database");
define("UPLLAN_3", "Upload ID");

define("UPLLAN_5", "Poster");
define("UPLLAN_6", "Email");
define("UPLLAN_7", "Website");
define("UPLLAN_8", "File Name");

define("UPLLAN_9", "Version");
define("UPLLAN_10", "File");
define("UPLLAN_11", "File Size");
define("UPLLAN_12", "Screenshot");
define("UPLLAN_13", "Description");
define("UPLLAN_14", "Demo");

define("UPLLAN_16", "copy to newspost");
define("UPLLAN_17", "remove upload from list");
define("UPLLAN_18", "View details");
define("UPLLAN_19", "There are no unmoderated public uploads");
define("UPLLAN_20", "There");
define("UPLLAN_21", "unmoderated public upload");
define("UPLLAN_22", "ID");
define("UPLLAN_23", "Name");
define("UPLLAN_24", "Filetype");
define("UPLLAN_25", "Uploads Enabled?");
define("UPLLAN_26", "No public uploads will be permitted if disabled");
define("UPLLAN_27", "unmoderated public uploads");

define("UPLLAN_29", "Storage type");
define("UPLLAN_30", "Choose how to store uploaded files, either as normal files on server or as binary info in database<br /><b>Note</b> binary is only suitable for smaller files under approximately 500kb");
define("UPLLAN_31", "Flatfile");
define("UPLLAN_32", "Binary");
define("UPLLAN_33", "Maximum file size");
define("UPLLAN_34", "Maximum upload size in bytes - leave blank to use settings from php.ini");
define("UPLLAN_35", "Allowed file types");
define("UPLLAN_36", "Please enter one type per line");
define("UPLLAN_37", "Permission");
define("UPLLAN_38", "Select to allow only certain users to upload");
define("UPLLAN_39", "Submit");

define("UPLLAN_41", "Please note - file uploads are disabled from your php.ini, it will not be possible to upload files until you set it to On.");

define("UPLLAN_42", "Actions");
define("UPLLAN_43", "Uploads");
define("UPLLAN_44", "Upload");

define("UPLLAN_45", "Are you sure you want to delete the following file...");

define("UPLAN_COPYTODLM", "copy to download manager");
define("UPLAN_IS", "is ");
define("UPLAN_ARE", "are ");
define("UPLAN_COPYTODLS", "Copy to Downloads");

define("UPLLAN_48", "For security reasons allowed file types has been moved out of the database into a 
flatfile located in your admin directory. To use, rename the file e107_admin/filetypes_.php to e107_admin/filetypes.php 
and add a comma delimited list of file type extensions to it. You should not allow the upload of .html, .txt, etc., as an attacker may upload a file of this type which includes malicious javascript. You should also, of course, not allow 
the upload of .php files or any other type of executable script.");


?>