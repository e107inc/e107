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

if(deftrue('BOOTSTRAP')) //v2.x
{
	$MEMBERSONLY_TEMPLATE = e107::getCoretemplate('membersonly');
}
else // Legacy
{
	if(is_readable(THEME . "membersonly_template.php"))
	{
		require_once(THEME . "membersonly_template.php");
	}
	else
	{
		require_once(e_CORE . "templates/membersonly_template.php");
	}

	$MEMBERSONLY_TEMPLATE['default']['caption'] = $MEMBERSONLY_CAPTION;
	$MEMBERSONLY_TEMPLATE['default']['header'] = $MEMBERSONLY_BEGIN;
	$MEMBERSONLY_TEMPLATE['default']['body'] = $MEMBERSONLY_TABLE;
	$MEMBERSONLY_TEMPLATE['default']['footer'] = $MEMBERSONLY_END;
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
