<?php
/*
 * Copyright (C) 2008-2013 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin Language File
 *
*/

// Menu
define("LAN_IMA_M_01", "Media Library"); 
define("LAN_IMA_M_02", "Media Upload/Import"); 
define("LAN_IMA_M_03", "Media Categories"); 
define("LAN_IMA_M_04", "Create Category"); 
define("LAN_IMA_M_05", "Avatars"); 


// Errors / Info / Notices
define("LAN_IMA_001", "Modification is not permitted.");
define("LAN_IMA_002", "Not enough memory available to rotate");
define("LAN_IMA_003", "Rotated");
define("LAN_IMA_004", "Resizing");

// Options
define("LAN_IMA_O_001", "News Images");
define("LAN_IMA_O_002", "News [img] bbcode");
define("LAN_IMA_O_003", "Page [img] bbcode");
define("LAN_IMA_O_004", "Featurebox Images");
define("LAN_IMA_O_005", "Featurebox [img] bbcode");
define("LAN_IMA_O_006", "[img] bbcode");

// Work in progress (Moc)

define("IMALAN_1", "Enable image display");
define("IMALAN_2", "Display images, this will apply sitewide (comments, chatbox etc) to images posted using the [img] bbcode");
define("IMALAN_3", "Resize method");
define("IMALAN_4", "Method used to resize images, either GD1/2 library, or ImageMagick");
define("IMALAN_5", "Path to ImageMagick (if selected)");
define("IMALAN_6", "Full path to ImageMagick Convert utility");
define("IMALAN_7", "Image Settings");
define("IMALAN_8", "Update Image Settings");
define("IMALAN_9", "Image settings updated");
define("IMALAN_10", "Image display class");
define("IMALAN_11", "Restrict users who can view images (if enabled above)");
define("IMALAN_12", "Disabled image method");
define("IMALAN_13", "What to do with images if image display is disabled");
define("IMALAN_14", "Show image URL");
define("IMALAN_15", "Show nothing");
// define("IMALAN_16", "Show uploaded avatars");
// define("IMALAN_17", "Click here");
define("IMALAN_18", "Uploaded Avatar Images");
// define("IMALAN_19", "Show 'disabled' message");
define('IMALAN_20', "Nothing changed");
define("IMALAN_21", "Used by");
define("IMALAN_22", "Image not in use");
define("IMALAN_23", "Avatars");
define("IMALAN_24", "Photograph");
define("IMALAN_25", "Click here to delete all unused images");
define("IMALAN_26", "image(s) deleted");

define("IMALAN_28", "deleted");
define("IMALAN_29", "No images");
// define("IMALAN_30", "Everyone (public)");
// define("IMALAN_31", "Guests only");
// define("IMALAN_32", "Members only");
// define("IMALAN_33", "Admin only");
//define("IMALAN_34", "Enable PNG Fix");
//define("IMALAN_35", "Fixes transparent PNG-24's with alpha transparency in IE 5 / 6 (Applies Sitewide)");

define("IMALAN_36", "Validate avatar size and access");
define("IMALAN_37", "Avatar Validation");
define("IMALAN_38", "Maximum allowable width");
define("IMALAN_39", "Maximum allowable height");
define("IMALAN_40", "Too wide");
define("IMALAN_41", "Too high");
define("IMALAN_42", "Not found");
// define("IMALAN_43", "Delete uploaded avatar");
// define("IMALAN_44", "Delete external reference");
define("IMALAN_45", "Not found");
define("IMALAN_46", "Too large");
define("IMALAN_47", "Total uploaded avatars");
define("IMALAN_48", "Total external avatars");
define("IMALAN_49", "Users with avatars");
define("IMALAN_50", "Total");
define("IMALAN_51", "Avatar for ");

define("IMALAN_52", "Path to ImageMagick appears to be incorrect");
define("IMALAN_53", "Path to ImageMagick appears to be correct, but convert file may not be valid");
define("IMALAN_54", "GD version installed:");
define('IMALAN_55', "Not installed");

//v0.8

//uploaded avatar list
define('IMALAN_56', "Click to select");
define('IMALAN_57', "Image too big - click to enlarge");

//avatar check
// define('IMALAN_61', "Options");
define('IMALAN_62', "Reason");

define('IMALAN_65', "Nothing found");

define('IMALAN_66', "Filename");
define('IMALAN_68', "Close");
define('IMALAN_69', "Folder");
define('IMALAN_70', "Non-system folder is found!");

// define("IMALAN_72", "Icons");

define('IMALAN_73', "Thumbnail Quality");
define('IMALAN_74', "Set this as low as possible before quality loss is apparent. Max. 100");
define('IMALAN_75', "Avatar Width");
define('IMALAN_76', "Avatar images will be constrained to these dimensions (in pixels)");
define('IMALAN_77', "Avatar Height");
define('IMALAN_78', ""); // Unused
define('IMALAN_79', "Resize-Image Dimensions");
define('IMALAN_80', "Watermark Activation");
define('IMALAN_81', "All images with a width or height greater than this value will be given a watermark during resizing.");
define('IMALAN_82', "Watermark Text");
define('IMALAN_83', "Optional Watermark Text");
define('IMALAN_84', "Watermark Font");
define('IMALAN_85', "Optional Watermark Font. Upload more .ttf fonts to the /fonts folder in your theme directory.");
define('IMALAN_86', "Watermark Size");
define('IMALAN_87', "Size of the font in pts");
define('IMALAN_88', "Watermark Position");
define('IMALAN_89', ""); // Unused
define('IMALAN_90', "Watermark Margin");
define('IMALAN_91', "The distance that watermark will appear from the edge of the image.");
define('IMALAN_92', "Watermark Color");
define('IMALAN_93', "Color of the watermark eg. 000000");
define('IMALAN_94', "Watermark Shadow-Color");
define('IMALAN_95', "Shadow Color of the watermark eg. ffffff");
define('IMALAN_96', "Watermark Opacity");
define('IMALAN_97', "Enter a number between 1 and 100");
define('IMALAN_98', "Default YouTube account");
define('IMALAN_99', "Used by the Media-Manager Youtube browser. Enter account name. eg. e107inc");
define('IMALAN_100', "Show Related Videos");
define('IMALAN_101', "Show Video Info");
define('IMALAN_102', "Show Closed-Captions by default");
define('IMALAN_103', "Use Modest Branding");
define('IMALAN_104', "Make the YouTube bbcode responsive");
define('IMALAN_105', "Resize images during media import");
define('IMALAN_106', "Leave empty to disable");
define('IMALAN_107', "Couldn't generated path from upload data");
define('IMALAN_108', "Couldn't move file from [x] to [y]");
define('IMALAN_109', "Couldn't get path");
define('IMALAN_110', ""); // Unused
define('IMALAN_111', "Couldn't detect mime-type([x]). Upload failed.");
define('IMALAN_112', "Couldn't create folder ([x]).");
define('IMALAN_113', "Scanning for new media (images, videos, files) in folder:");
define('IMALAN_114', "No media Found! Please upload some files.");
define('IMALAN_115', "Title (internal use)");
define('IMALAN_116', "Caption (seen by public)");
//define('IMALAN_117', "Author"); // use LAN_AUTHOR
define('IMALAN_118', "Mime Type");
define('IMALAN_119', "File Size");
define('IMALAN_120', "Dimensions");
define('IMALAN_121', "Preview"); // use LAN_PREVIEW
define('IMALAN_122', "[x] couldn't be renamed. Check file perms.");
define('IMALAN_123', "Import into Category:");
define('IMALAN_124', "Import Selected Files");
define('IMALAN_125', "Delete Selected Files");
define('IMALAN_126', "Please check at least one file.");
define('IMALAN_127', "Couldn't get file info from:");
define('IMALAN_128', "Importing Media:");

?>