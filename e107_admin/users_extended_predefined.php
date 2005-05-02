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
|     $Source: /cvs_backup/e107_0.7/e107_admin/users_extended_predefined.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-05-02 03:29:49 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

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