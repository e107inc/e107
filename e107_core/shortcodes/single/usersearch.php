<?php

// $Id$

function usersearch_shortcode($parm)
{
	// FIXME - permissions, sql query
	if(!ADMIN || !e_AJAX_REQUEST)
	{
		return '<ul></ul>';
	}
	parse_str(str_replace('--', '&', $parm), $parm);
	
	$tp = e107::getParser();
	$sql = e107::getDb();
	$search_field = 'user_'.vartrue($parm['searchfld'], 'name');
	$info_field = $search_field == 'user_name' ? 'user_loginname' : 'user_name';
	$posted = $_POST[vartrue($parm['srcfld'], 'user_name')];
	
	if(!$posted)
	{
		return '<ul></ul>';
	}
	
	$allowed = array('user_id', 'user_name', 'user_loginname', 'user_customtitle', 'user_email');
	if(!in_array($search_field, $allowed))
	{
		$search_field = 'user_name';
	}

	$ret = "<ul>";
	$qry = "
		SELECT u.user_id, u.user_name, u.user_loginname, u.user_customtitle, u.user_email FROM #user AS u
		WHERE {$search_field} LIKE '".$tp->toDb($posted)."%'
	";
	
	if($sql->db_Select_gen($qry))
	{
		while($row = $sql->db_Fetch())
		{
			$ret .= "<li id='{$row['user_id']}'>{$row[$search_field]}<span class='informal'> [{$row['user_id']}] ".$row[$info_field]." </span></li>";
		}
	}
	$ret .= "</ul>";
	return $ret;
}
?>