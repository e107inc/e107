<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.7/e107_plugins/forum/forum_post.php $
|     $Revision: 11678 $
|     $Id: forum_post.php 11678 2010-08-22 00:43:45Z e107coders $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/


// Forum Notify Types 
define('NT_LAN_FT_1', 'Forum Events');
define('NT_LAN_FO_1', 'Forum thread posted');
define('NT_LAN_MP_1', 'Forum message posted');
define('NT_LAN_FD_1', 'Forum thread deleted');
define('NT_LAN_FP_1', 'Forum message deleted');
define('NT_LAN_FM_1', 'Forum thread moved');

// Forum thread posted
define('NT_LAN_FO_3', 'Forum thread created by');
define('NT_LAN_FO_4', 'Forum name');
define('NT_LAN_FO_5', 'Subject');
define('NT_LAN_FO_6', 'Message');
define('NT_LAN_FO_7', 'New forum thread created');

// Forum message posted
define('NT_LAN_MP_3', 'Forum message created by');
define('NT_LAN_MP_4', 'Forum name');
define('NT_LAN_MP_6', 'Message');
define('NT_LAN_MP_7', 'New forum message created');

// Forum thread deleted
define('NT_LAN_FD_3', 'Forum thread created by');
define('NT_LAN_FD_4', 'Forum name');
define('NT_LAN_FD_5', 'Subject');
define('NT_LAN_FD_6', 'Message');
define('NT_LAN_FD_7', 'Forum thread is deleted');
define('NT_LAN_FD_8', 'Forum thread deleted by');

// Forum message deleted
define('NT_LAN_FP_3', 'Forum message created by');
define('NT_LAN_FP_4', 'Forum name');
define('NT_LAN_FP_6', 'Message');
define('NT_LAN_FP_7', 'Forum message is deleted');
define('NT_LAN_FP_8', 'Forum message deleted by');

// Forum thread moved
define('NT_LAN_FM_3', 'Forum thread created by');
define('NT_LAN_FM_4', 'Old subject');
define('NT_LAN_FM_5', 'New subject');
define('NT_LAN_FM_6', 'Old (source) forum name');
define('NT_LAN_FM_7', 'New (destination) forum name');
define('NT_LAN_FM_8', 'Forum thread is moved');
define('NT_LAN_FM_9', 'Forum thread is moved by');

?>
