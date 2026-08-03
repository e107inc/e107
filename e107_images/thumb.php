<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/*
 Compatibility shim for the 0.8 thumbnailer.

 Nothing in e107 calls this endpoint any more: thumbUrl() emits the thumb.php
 at the site root, and this file is the only place in the tree that names
 itself. An upgrade never deletes a file, though, so a replacement has to ship
 either way. The published grammar is still answered, by translating it into a
 request the site root thumbnailer serves:

   thumb.php?<path>+<size>[+<model>]

 <path> is relative to the site root and <size> is the width in pixels. The two
 shapes that were the vulnerability are refused rather than emulated:

   - an absolute path, or one containing "..", which reached resize_image()
     verbatim;
   - a source carrying a scheme, which getimagesize() and readfile() would
     both have fetched on the caller's behalf.

 <model> is accepted and ignored. It selected between three ways of handling a
 source already smaller than <size>, and one of them, "noscale", was the
 arbitrary read: a readfile() of the source straight to the browser. What the
 caller gets now is what e_thumbnail gives every other request, which is the
 same picture at the same size for any source wider than <size>. A narrower one
 comes back unenlarged below 111 pixels and enlarged above it, rather than by
 the model the caller named.

 Translated requests are re-encoded by e_thumbnail, so a JPEG now comes back
 at the thumbnail_quality preference rather than at im_quality.
*/

@ini_set('display_errors', '0'); // this endpoint answers in bytes, not in prose.

/**
 * Answer the caller with a status and nothing that describes the server.
 *
 * @param int    $status
 * @param string $message
 * @return void
 */
function legacyThumbRefuse($status, $message)
{
	if(!headers_sent())
	{
		http_response_code($status);
		header('Content-Type: text/plain; charset=utf-8');
		header('X-Content-Type-Options: nosniff');
	}

	echo $message;
	exit;
}

/**
 * @param Exception|Throwable $e
 * @return void
 */
function legacyThumbException($e)
{
	error_log(sprintf(
		'e107_images/thumb.php: %s in %s on line %d%s%s',
		$e->getMessage(),
		$e->getFile(),
		$e->getLine(),
		PHP_EOL,
		$e->getTraceAsString()
	));

	legacyThumbRefuse(500, 'Thumbnail error');
}

// Ahead of the bootstrap, which describes the server in prose of its own when
// e107_config.php or a handler is missing.
set_exception_handler('legacyThumbException');

$_E107['minimal'] = TRUE;

require_once("../class2.php");

if(!e_QUERY)
{
	legacyThumbRefuse(400, 'No image name.');
}

$tmp = array_pad(explode('+', rawurldecode(e_QUERY)), 3, '');

$source = trim($tmp[0]);
$newsize = (int) $tmp[1];

if($source === '')
{
	legacyThumbRefuse(400, 'No image name.');
}

if(preg_match('#^[a-z][a-z0-9+.-]*://#i', $source))
{
	legacyThumbRefuse(403, 'Bad image source.');
}

if(strpos($source, '/') === 0 || preg_match('#^[a-z]:[\\\\/]#i', $source) || strpos($source, '..') !== false)
{
	legacyThumbRefuse(403, 'Bad image source.');
}

if($newsize < 5 || $newsize > 4000)
{
	legacyThumbRefuse(400, 'Bad image size.');
}

require_once(e_HANDLER."e_thumbnail_class.php");

$thm = new e_thumbnail;
$thm->init(e107::getPref(), array('src' => '{e_BASE}'.$source, 'w' => $newsize));

if(!$thm->checkSrc())
{
	legacyThumbRefuse(403, 'Bad image source.');
}

$thm->sendImage();
exit;
