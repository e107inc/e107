<?php
// search module for Event calendar.

	$results = $sql -> db_Select("event", "*", "event_stake LIKE('%".$query."%') OR event_ward LIKE('%".$query."%') OR event_organisation LIKE('%".$query."%') OR event_title LIKE('%".$query."%') 
                     OR event_location LIKE('%".$query."%') OR event_details LIKE('%".$query."%') OR event_thread LIKE('%".$query."%') ");
	while(list($event_id, $event_stake, $event_ward, $event_organisation, $event_start, $event_end, $event_allday,,, $event_title, $event_location, $event_details, $event_author, $event_contact, $event_category, $event_url ) = $sql -> db_Fetch()){

	$sql2 -> db_select("event_cat", "event_cat_name, event_cat_icon", "event_cat_id='".$event_category."' ");
	list($event_cat_name, $event_cat_icon ) = $sql2 -> db_Fetch();

		$event_stake_ = parsesearch($event_stake, $query);
		if(!$event_stake_){
			$event_stake_ = $event_stake;
		}

		$event_ward_ = parsesearch($event_ward, $query);
		if(!$event_ward_){
			$event_ward_ = $event_ward;
		}

		$event_organisation_ = parsesearch($event_organisation, $query);
		if(!$event_organisation_){
			$event_organisation_ = $event_organisation;
		}

		$event_title_ = parsesearch($event_title, $query);
		if(!$event_title_){
			$event_title_ = $event_title;
		}

		$event_details_ = parsesearch($event_details, $query);
		if(!$event_details_){
			$event_details_ = $event_details;
		}

		$event_cat_name_ = parsesearch($event_cat_name, $query);
		if(!$event_cat_name_){
			$event_cat_name_ = $event_cat_name;
		}
		$event_threat_ = parsesearch($event_threat, $query);
		if(!$event_url_){
			$event_threat_ = $event_threat;
		}
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"event.php?".$event_start."\">$event_title</a>$event_details<br />";
	}
	$qtype = LAN_911;
?>