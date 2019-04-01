<?php
/*
 * Copyright 2008-2010 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * Validator Handler - Language File
*/

/*
 * Default error messages by Error code number
 */ 
define("LAN_VALIDATE_0",   "Unknown Error");
define("LAN_VALIDATE_101", "Missing value");
define("LAN_VALIDATE_102", "Unexpected value type");
define("LAN_VALIDATE_103", "Invalid characters found");
define("LAN_VALIDATE_104", "Not a valid email address");
define("LAN_VALIDATE_105", "Fields don\"t match" );
define("LAN_VALIDATE_131", "String too short");
define("LAN_VALIDATE_132", "String too long");
define("LAN_VALIDATE_133", "Number too low");
define("LAN_VALIDATE_134", "Number too high");
define("LAN_VALIDATE_135", "Array count too low");
define("LAN_VALIDATE_136", "Array count too high");
define("LAN_VALIDATE_151", "Number of type integer expected");
define("LAN_VALIDATE_152", "Number of type float expected");
define("LAN_VALIDATE_153", "Instance type expected");
define("LAN_VALIDATE_154", "Array type expected");
define("LAN_VALIDATE_191", "Empty value");
define("LAN_VALIDATE_201", "File not exists");
define("LAN_VALIDATE_202", "File not writable");
define("LAN_VALIDATE_203", "File exceeds allowed file size");
define("LAN_VALIDATE_204", "File size lower than allowed minimal file size");
//define("LAN_VALIDATE_", "");

/*
 * TRANSLATION INSTRUCTIONS:
 * Don"t translate %1$s, %2$s, %3$s, etc.
 * 
 * These are substituted by validator handler:
 * %1$s - field name
 * %2$d - validation error code (number)
 * %3$s - validation error message (string)
 */

// define("LAN_VALIDATE_FAILMSG", "<strong>&quot;%1$s&quot;</strong> validation error: [#%2$d] %3$s.");

 //FIXME - use this instead: 
define("LAN_VALIDATE_FAILMSG", "[x] validation error: [y] [z].");