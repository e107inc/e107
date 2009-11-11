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
|     $Revision: 1.19 $
|     $Date: 2009-11-11 20:57:34 $
|     $Author: secretr $
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
		'main/prefs' 	=> array('caption'=> 'Settings', 'perm' => '0'),
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
		protected $pluginName = 'core';
		protected $table = "comments";
		
		/**
		 * If present this array will be used to build your list query
		 * You can link fileds from $field array with 'table' parameter, which should equal to a key (table) from this array
		 * 'leftField', 'rightField' and 'fields' attributes here are required, the rest is optional
		 * 
		 * @var array [optional]
		 */
		protected $tableJoin = array (
			'u.user' => array('leftField' => 'comment_author_id', 'rightField' => 'user_id', 'fields' => '*'/*, 'leftTable' => '', 'joinType' => 'LEFT JOIN', 'whereJoin' => 'AND u.user_ban=0', 'where' => ''*/)
		);
		
		//protected $listQry = "SELECT SQL_CALC_FOUND_ROWS * FROM #comments"; // without any Order or Limit. 
		
		//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
		protected $pid = "comment_id";
		protected $perPage = 10;
		protected $batchDelete = true;
		
		//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
    	protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'comment_id'			=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE),
       		'comment_item_id' 		=> array('title'=> "item id",		'type' => 'number',			'width' => '5%'),
         	'comment_subject' 		=> array('title'=> "subject",		'type' => 'text',			'width' => 'auto', 'thclass' => 'left first'), // Display name
         	'comment_comment' 		=> array('title'=> "comment",		'type' => 'bbarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1'), // Display name
		 	'comment_author_id' 	=> array('title'=> "author",		'type' => 'user',			'data' => 'int',	'width' => 'auto', 'writeParms' => 'nameField=comment_author_name'),	// User id
         	'comment_author_name' 	=> array('title'=> "authorName",	'type' => 'user',			'width' => 'auto', 'readParms'=>'idField=comment_author_id&link=1', 'noedit' => true, 'forceSave' => true),	// User name
         	'u.user_name' 			=> array('title'=> "System user",	'type' => 'user',			'width' => 'auto', 'readParms'=>'idField=comment_author_id&link=1', 'noedit' => true),	// User name
		    'comment_datestamp' 	=> array('title'=> "datestamp",		'type' => 'datestamp',		'width' => 'auto'),	// User date
            'comment_blocked' 		=> array('title'=> "blocked",		'type' => 'boolean',		'data'=> 'int', 'thclass' => 'center', 'class'=>'center', 'filter' => true, 'batch' => true,	'width' => 'auto'),	 	// Photo
			'comment_ip' 			=> array('title'=> "IP",			'type' => 'ip',			'width' => '10%', 'thclass' => 'center' ),	 // Real name (no real vetting)
			'comment_type' 			=> array('title'=> "Type",			'type' => 'method',			'width' => '10%',  'filter'=>TRUE,'batch'=>TRUE ),	 // No real vetting
			'comment_lock' 			=> array('title'=> "Lock",			'type' => 'boolean',		'data'=> 'int', 'thclass' => 'center', 'class'=>'center', 'filter' => true, 'batch' => true,	'width' => 'auto'),
			'options' 				=> array('title'=> LAN_OPTIONS,		'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
		);
		//required (but should be optional) - default column user prefs 
		protected $fieldpref = array('checkboxes', 'comment_id', 'comment_item_id', 'comment_author_id', 'comment_author_name', 'comment_subject', 'comment_comment', 'comment_type', 'options');
		
		
		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		protected $prefs = array( 
			'pref_type'	   				=> array('title'=> 'type', 'type'=>'text'),
			'pref_folder' 				=> array('title'=> 'folder', 'type' => 'boolean'),	
			'pref_name' 				=> array('title'=> 'name', 'type' => 'text')		
		);
		
}

//TODO Block and Unblock buttons, moderated comments?
class comments_admin_form_ui extends e_admin_form_ui
{
	function comment_type($curVal,$mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function. 
	{ 
		if($mode == 'read')
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
			asort($types);
			
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