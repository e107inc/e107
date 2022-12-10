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
 * $Source: /cvs_backup/e107_0.8/e107_handlers/search/search_event.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$query = $tp -> toDB($query);
$results = $sql->select("event", "*", "event_stake REGEXP('".$query."') OR event_ward REGEXP('".$query."') OR event_organisation REGEXP('".$query."') OR event_title REGEXP('".$query."')
	OR event_location REGEXP('".$query."') OR event_details REGEXP('".$query."') OR event_thread REGEXP('".$query."') ");
while (list($event_id, $event_stake, $event_ward, $event_organisation, $event_start, $event_end, $event_allday, , , $event_title, $event_location, $event_details, $event_author, $event_contact, $event_category, $event_url ) = $sql->fetch()) {
	 
	$sql2->select("event_cat", "event_cat_name, event_cat_icon", "event_cat_id='".$event_category."' ");
	list($event_cat_name, $event_cat_icon ) = $sql2->fetch();
	 
	$event_stake_ = parsesearch($event_stake, $query);
	if (!$event_stake_) {
		$event_stake_ = $event_stake;
	}
	 
	$event_ward_ = parsesearch($event_ward, $query);
	if (!$event_ward_) {
		$event_ward_ = $event_ward;
	}
	 
	$event_organisation_ = parsesearch($event_organisation, $query);
	if (!$event_organisation_) {
		$event_organisation_ = $event_organisation;
	}
	 
	$event_title_ = parsesearch($event_title, $query);
	if (!$event_title_) {
		$event_title_ = $event_title;
	}
	 
	$event_details_ = parsesearch($event_details, $query);
	if (!$event_details_) {
		$event_details_ = $event_details;
	}
	 
	$event_cat_name_ = parsesearch($event_cat_name, $query);
	if (!$event_cat_name_) {
		$event_cat_name_ = $event_cat_name;
	}
	$event_threat_ = parsesearch($event_threat, $query);
	if (!$event_url_) {
		$event_threat_ = $event_threat;
	}
	$bullet = '';
	if(defined('BULLET'))
	{
		$bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
	}
	elseif(file_exists(THEME.'images/bullet2.gif'))
	{
		$bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
	}
	$text .= $bullet." <a href=\"event.php?".$event_start."\">{$event_title}</a>{$event_details}<br />";
}
$qtype = LAN_911;
