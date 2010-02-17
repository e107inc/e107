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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/encrypt_handler.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

function encode_ip($ip)
{
  if (strpos($ip,':') !== FALSE) return $ip;			// IPV6 addresses - return unaltered
	$ipa = explode('.', $ip);
	return sprintf('%02x%02x%02x%02x', $ipa[0], $ipa[1], $ipa[2], $ipa[3]);
}

function decode_ip($encodedIP)
{
  if (strpos($encodedIP,':') !== FALSE) return $encodedIP;			// IPV6 addresses - return unaltered
	$hexip = explode('.', chunk_split($encodedIP, 2, '.'));
	return hexdec($hexip[0]). '.' . hexdec($hexip[1]) . '.' . hexdec($hexip[2]) . '.' . hexdec($hexip[3]);
}




?>