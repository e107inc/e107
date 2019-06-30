<?php
if (!defined('e107_INIT')) { exit(); }

e107::lan('forum', "search", true);
//include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/'.e_LANGUAGE.'_search.php');

class forum_search extends e_search // include plugin-folder in the name.
{

	function config()
	{

		$sql = e107::getDb();
		$catList = array();

		$catList[] = array('id' => 'all', 'title' => LAN_PLUGIN_FORUM_ALLFORUMS);

		if ($sql ->gen("SELECT f.forum_id, f.forum_name FROM #forum AS f LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent "))
		{
			while($row = $sql->fetch())
			{
				$catList[] = array('id' => $row['forum_id'], 'title' => $row['forum_name']);
			}
		}

		$matchList = array(
			0 => array('id' => 0, 'title' => FOR_SCH_LAN_4),
			1 => array('id' => 1, 'title' => LAN_SEARCH_54)
		);

		$search = array(
			'name'			=> LAN_PLUGIN_FORUM_NAME,
		//	'table'			=> 'forum',
			'table'			=> 'forum_thread AS t LEFT JOIN #user AS u ON t.thread_user = u.user_id
								LEFT JOIN #forum AS f ON t.thread_forum_id = f.forum_id
								LEFT JOIN #forum AS fp ON f.forum_parent = fp.forum_id
								LEFT JOIN #forum_post AS p ON p.post_thread = t.thread_id',

			'advanced' 		=> array(
								'forum'	=> array('type'	=> 'dropdown', 		'text' => FOR_SCH_LAN_2, 'list'=>$catList),
								'date'	=> array('type'	=> 'date', 		'text' => LAN_DATE_POSTED),
								'author'=> array('type'	=> 'author',	'text' => LAN_SEARCH_61),
								'match'=> array('type'	=> 'dropdown',	'text' => LAN_SEARCH_52, 'list'=>$matchList) // not functional yet.
							),

			'return_fields'	=> array('t.thread_id', 't.thread_name', 'p.post_id', 'p.post_entry', 't.thread_forum_id', 't.thread_datestamp', 't.thread_user', 'u.user_id', 'u.user_name', 'f.forum_class', 'f.forum_id', 'f.forum_name', 'f.forum_sef'),
			'search_fields'	=> array('t.thread_name'=>'1.2', 'p.post_entry'=>'0.6'), // fields and weights.

			'order'			=>  array('thread_datestamp' => DESC),
			'refpage'		=> 'forum'
		);

		$params = $this->getParams(); // retrieve URL query values.

		if(!empty($params['match']))
		{
			$search['search_fields'] = array('t.thread_name'=>'1.5');
		}


		return $search;
	}



	/* Compile Database data for output */
	function compile($row)
	{
		$tp = e107::getParser();

		$res = array();
		$datestamp = $tp->toDate($row['thread_datestamp'], "long");

		if ($row['thread_parent'])
		{
			$title = $row['parent_name'];
		}
		else
		{
			$title = $row['thread_name'];
		}

	$link_id = $row['thread_id'];

	$uparams = array('id' => $row['user_id'], 'name' => $row['user_name']);
	$link = e107::getUrl()->create('user/profile/view', $uparams);
	$userlink = "<a href='".$link."'>".$row['user_name']."</a>";

	$row['thread_sef'] = eHelper::title2sef($row['thread_name'],'dashl');

	$forumTitle = "<a href='".e107::url('forum','forum',$row)."'>".$row['forum_name']."</a>";

	$res['link'] 		= e107::url('forum','topic', $row, array('query'=>array('f'=>'post','id'=>$row['post_id']))); // e_PLUGIN."forum/forum_viewtopic.php?".$link_id.".post";
	$res['pre_title'] 	= ''; // $title ? FOR_SCH_LAN_5.": " : "";
	$res['title'] 		= $title ? $forumTitle . " | ". $title : LAN_SEARCH_9;
	$res['pre_summary'] = "";
	$res['summary'] 	= $row['post_entry'];
	$res['detail'] 		= LAN_SEARCH_7.$userlink.LAN_SEARCH_8.$datestamp;

		return $res;

	}



	/**
	 * Optional - Advanced Where
	 * @param $parm - data returned from $_GET (ie. advanced fields included. in this case 'date' and 'author' )
	 */
	function where($parm=null)
	{
		$tp = e107::getParser();

		$qry = " f.forum_parent != 0 AND fp.forum_class IN (".USERCLASS_LIST.") AND f.forum_class IN (".USERCLASS_LIST.") AND ";

		if (!empty($parm['forum']) && is_numeric($parm['forum']))
		{
			$qry .= " f.forum_id='".$parm['forum']."' AND";
		}

		if (!empty($parm['time']) && is_numeric($parm['time']))
		{
			$qry .= " t.thread_datestamp ".($parm['on'] == 'new' ? '>=' : '<=')." '".(time() - $parm['time'])."' AND";
		}

		if (!empty($parm['author']))
		{
			$qry .= " (u.user_id = '".$tp -> toDB($parm['author'])."' OR u.user_name = '".$tp -> toDB($parm['author'])."') AND";
		}

		return $qry;
	}


}


/*
$search_info[] = array(
	'sfile' => e_PLUGIN.'forum/search/search_parser.php', 
	'qtype' => LAN_PLUGIN_FORUM_NAME, 
	'refpage' => 'forum', 
	'advanced' => e_PLUGIN.'forum/search/search_advanced.php', 
	'id' => 'forum'
);*/
