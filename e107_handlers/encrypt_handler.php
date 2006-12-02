<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/encrypt_handler.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:44 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

function encode_ip($ip)
{
	$ipa = explode('.', $ip);
	return sprintf('%02x%02x%02x%02x', $ipa[0], $ipa[1], $ipa[2], $ipa[3]);
}

function decode_ip($encodedIP)
{
	$hexip = explode('.', chunk_split($encodedIP, 2, '.'));
	return hexdec($hexip[0]). '.' . hexdec($hexip[1]) . '.' . hexdec($hexip[2]) . '.' . hexdec($hexip[3]);
}




?>