<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 requires PHP >= 5 - implement functions only supported in later versions
 *
 * $URL$
 * $Id$
 *
*/
if (!defined('e107_INIT'))
{
	exit;
}

/**
 * Handle system messages
 * 
 * @package e107
 *	@subpackage	e107_handlers
 * @copyright Copyright (C) 2008-2016 e107 Inc (e107.org)
 */


if (!function_exists('strptime'))
{
	function strptime($date, $format)
	{
		return eShims::strptime($date, $format);
	}
}

//PHP < 5.2 compatibility
if (!function_exists('json_encode'))
{
    require_once(e_HANDLER.'json_compat_handler.php');
    function json_encode($array)
    {
        $json = new Services_JSON();
        return $json->encode($array);
    }

    function json_decode($json_obj)
    {
        $json = new Services_JSON();
        return $json->decode($json_obj);
    }
}

// Fix for exim missing.
if(!function_exists('exif_imagetype'))
{
    function exif_imagetype($filename)
    {
        if((list($width, $height, $type, $attr) = getimagesize( $filename ) ) !== false)
        {
            return $type;
        }

         return false;
    }
}
