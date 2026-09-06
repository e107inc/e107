<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2021
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).

+----------------------------------------------------------------------------+
*/
require_once("class2.php");
e107::coreLan('membersonly');

$MEMBERSONLY_TEMPLATE = e107::getCoreTemplate('membersonly');

$legacyBody = vartrue($MEMBERSONLY_TABLE, varset($MEMBERSONLY_TEMPLATE['MEMBERSONLY_TABLE'], ''));

if(!empty($legacyBody))
{
	$MEMBERSONLY_TEMPLATE['default'] = array(
		'caption' => vartrue($MEMBERSONLY_CAPTION, varset($MEMBERSONLY_TEMPLATE['MEMBERSONLY_CAPTION'], '')),
		'header'  => vartrue($MEMBERSONLY_BEGIN, varset($MEMBERSONLY_TEMPLATE['MEMBERSONLY_BEGIN'], '')),
		'body'    => $legacyBody,
		'footer'  => vartrue($MEMBERSONLY_END, varset($MEMBERSONLY_TEMPLATE['MEMBERSONLY_END'], '')),
	);
}

if(!defined('e_IFRAME'))
{
	define('e_IFRAME', true);
}

$sc = e107::getScBatch('membersonly');
$sc->wrapper('membersonly/default');
require_once(HEADERF);


$BODY = e107::getParser()->parseTemplate($MEMBERSONLY_TEMPLATE['default']['body'], true, $sc);

echo $MEMBERSONLY_TEMPLATE['default']['header'];
e107::getRender()->tablerender($MEMBERSONLY_TEMPLATE['default']['caption'], $BODY, 'membersonly');
echo $MEMBERSONLY_TEMPLATE['default']['footer'];

require_once(FOOTERF);
