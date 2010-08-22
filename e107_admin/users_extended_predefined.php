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
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

function get_extended_predefined()
{
	$ue_field['location'] = array(
	"text" => EXTLAN_PRE1,
	"type" => "textbox",
	"include_text" => "class='tbox' size='80' maxlength='254'"
	);

	$ue_field['aim'] = array(
	"text" => EXTLAN_PRE2,
	"type" => "textbox",
	"include_text" => "class='tbox' size='40' maxlength='254'"
	);

	$ue_field['icq'] = array(
	"text" => EXTLAN_PRE3,
	"type" => "textbox",
	"include_text" => "class='tbox' size='40' maxlength='254'"
	);

	$ue_field['yahoo'] = array(
	"text" => EXTLAN_PRE4,
	"type" => "textbox",
	"include_text" => "class='tbox' size='40' maxlength='254'"
	);

	$ue_field['homepage'] = array(
	"text" => EXTLAN_PRE5,
	"type" => "textbox",
	"include_text" => "class='tbox' size='40' maxlength='254'",
	"regex validation" => "#^[a-z0-9]+://#si"
	);

	return $ue_field;
}
?>