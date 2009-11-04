<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/comment.php,v $
|     $Revision: 1.7 $
|     $Date: 2009-11-04 03:07:39 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("B")) 
{
	header("location:".e_BASE."index.php");
	exit;
}

class comments_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'comments_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'comments_admin_form_ui',
			'uipath' 		=> null
		)				
	);	


	protected $adminMenu = array(
		'main/list'		=> array('caption'=> 'Manage', 'perm' => '0'),
	//	'main/create' 	=> array('caption'=> LAN_CREATE, 'perm' => '0'),
		'main/options' 	=> array('caption'=> 'Settings', 'perm' => '0'),
	//	'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'Comments';
}

class comments_admin_ui extends e_admin_ui
{
		
		protected $pluginTitle = LAN_COMMENTMAN;
		protected $pluginName = 'comments';
		protected $table = "comments";
		protected $pid = "comment_id";
		protected $perPage = 20;
		protected $batchDelete = true;

    	protected $fields = array(
			'checkboxes'			=> array('title'=> '', 			'type' => null, 	'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'comment_id'			=> array('title'=> ID, 			'type' => 'int',	'width' =>'5%', 'forced'=> TRUE, 'primary'=>TRUE),
       		'comment_item_id' 		=> array('title'=> "item id", 	'type' => 'text',	'width' => 'auto'),
         	'comment_subject' 		=> array('title'=> "subject", 	'type' => 'text',	'width' => 'auto', 'thclass' => 'left first'), // Display name
         	'comment_author_id' 	=> array('title'=> "authorID", 	'type' => 'text',	'width' => 'auto'),	// User name
         	'comment_author_name' 	=> array('title'=> "authorName", 	'type' => 'text',	'width' => 'auto'),	// User name
			'comment_comment' 		=> array('title'=> "comment",	'type' => 'text',	'width' => 'auto'), // Display name
		    'comment_datestamp' 	=> array('title'=> "datestamp",	'type' => 'text',	'width' => 'auto'),	// User name
            'comment_blocked' 		=> array('title'=> "blocked",	'type' => 'text',	'width' => 'auto'),	 	// Photo
			'comment_ip' 			=> array('title'=> "IP",		'type' => 'text',	'width' => '10%', 'thclass' => 'center' ),	 // Real name (no real vetting)
			'comment_type' 			=> array('title'=> "Type",		'type' => 'method',	'width' => '10%', 'thclass' => 'center', 'filter'=>TRUE,'batch'=>TRUE ),	 // No real vetting
			'comment_lock' 			=> array('title'=> "Lock",		'type' => 'text',	'width' => 'auto'),

	   //	'page_ip_restrict' 		=> array('title'=> LAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar

			'options' 	=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
		);
		//required (but should be optional) - default column user prefs 
		protected $fieldpref = array('checkboxes', 'comment_id', 'comment_item_id', 'comment_author_id', 'comment_author_name', 'comment_subject', 'comment_type', 'options');
		
		
		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array( 
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text'),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean'),	
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text')		
		);
		
		protected $listQry = "SELECT * FROM #comments"; // without any Order or Limit. 
		
		protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
}

//TODO Block and Unblock buttons
class comments_admin_form_ui extends e_admin_form_ui
{
	function comment_type($curVal,$mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function. 
	{
		if($mode == 'list')
		{
			return e107::getComment()->getTable($curVal);
			return $curVal.' (custom!)';
		}
		
		if($mode == 'filter') // Custom Filter List for release_type
		{
			$sql = e107::getDb();
			$sql->db_Select_gen('SELECT * FROM #comments GROUP BY comment_type');
			while($row = $sql->db_Fetch())
			{
				$id = $row['comment_type'];
				$list[$id] = e107::getComment()->getTable($id);
			}
			return $list;
		}
		
		if($mode == 'batch')
		{
			$types = e107::getComment()->known_types;
			sort($types);
			return $types;	
		}
	}
}

new comments_admin();


require_once(e_ADMIN."auth.php");

e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

/*
if (e_QUERY) 
{
	$temp = explode("-", e_QUERY);
	$action = $temp[0];
	$id = intval($temp[1]);
	$item = $temp[2];
	$c_item = $temp[3];
	if ($sql->select('comments','*', 'comment_id='.$id))
	{
		$comment = $sql->db_Fetch();
		if ($action == "block") 
		{
			$sql->db_Update("comments", "comment_blocked='1' WHERE comment_id=".$id);
	}
		if ($action == "unblock") 
		{
			$sql->db_Update("comments", "comment_blocked='0' WHERE comment_id=".$id);
		}
		if ($action == "delete") 
		{
			$sql->db_Delete("comments", "comment_id=".$id);
			switch ($comment['comment_type'])
			{
				case '0' :
				case 'news' :		// Need to update count in news record as well
					$sql2->db_Update('news', 'news_comment_total = CAST(GREATEST(CAST(news_comment_total AS SIGNED) - 1, 0) AS UNSIGNED) WHERE news_id='.$comment['comment_item_id']);
					break;
	}
	}
		if (!$e107cache->clear($item)) 
		{
		$tmp = explode("?", $item);
		$item = $tmp[0]."?news.".$c_item;
		$e107cache->clear($item);
		}
	}
}
else
{
	// $cm= new comment_manager;
	// $cm->commentList();
}
// echo "<script type='text/javascript'>window.history.go(-1);</script>\n";

*/





?>