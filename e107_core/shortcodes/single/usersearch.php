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
	
	// search by email - based on site settings
	$emailSrch = false;
	if(e107::getPref('predefinedLoginName'))
	{
		if($search_field != 'user_email')
		{
			$emailSrch = true;
		}
	}
	$ret = "<ul>";
	$needle = $tp->toDb($posted);

	$qb = $sql->createQueryBuilder();
	$qb->select('u.user_id', 'u.user_name', 'u.user_loginname', 'u.user_customtitle', 'u.user_email')
		->from('user', 'u')
		->where($qb->expr()->startsWith($search_field, $needle));
	if($emailSrch)
	{
		$qb->orWhere($qb->expr()->startsWith('user_email', $needle));
	}
	$rows = $qb->fetchEach();

	if($emailSrch) $info_field = 'user_email';
	foreach($rows as $row)
	{
		$ret .= "<li id='{$row['user_id']}'>{$row[$search_field]}<span class='informal'> [{$row['user_id']}] ".$row[$info_field].$email." </span></li>";
	}
	$ret .= "</ul>";
	return $ret;
}
