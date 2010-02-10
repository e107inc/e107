<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/check_inspector.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/*
This file 'authorises' the standalone file inspector mode. If it is missing, or in its default state, File Inspector can only
be run in the 'normal' mode, which is protected by normal E107 security.

To run File Inspector in standalone mode (for checking a totally dead system), edit this file in the four places listed.

Then browse to the URL:   yoursite/e107_admin/fileinspector.php?alone

=================================================================================================================
	BE SURE TO REVERSE THE EDITS, OR DELETE THIS FILE, ONCE YOU HAVE IDENTIFIED THE PROBLEM WITH YOUR SYSTEM
=================================================================================================================

*/


// 1. Comment out the next line
	exit;

// 2. Uncomment the next line
//	define('e107_INIT', TRUE);
	
// 3. Comment out the next line
	define('e107_FILECHECK',TRUE);

// 4. Uncomment the next line
//	define('e107_STANDALONE',TRUE);

?>