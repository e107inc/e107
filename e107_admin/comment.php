<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("../class2.php");
if (!getperms("B")) 
{
	e107::redirect('admin');
	exit;
}

// include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_prefs.php');

e107::lan('core', 'comment');
e107::lan('core', 'prefs', true);

e107::css('inline', "td.status  span.label { display:block; width: 100%; padding: 6px 6px; }  ");

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
		'main/list'		=> array('caption'=> LAN_MANAGE, 'perm' => '0'),
	//	'main/create' 	=> array('caption'=> LAN_CREATE, 'perm' => '0'),
		'main/prefs' 	=> array('caption'=> LAN_PREFS, 'perm' => '0'),
		//	'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
		'main/tools' 	=> array('caption'=> ADLAN_CL_6, 'perm' => '0'),
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = LAN_COMMENTMAN;

		protected $adminMenuIcon = 'e-comments-24';
}

class comments_admin_ui extends e_admin_ui
{
		
		protected $pluginTitle = LAN_COMMENTMAN;
		protected $pluginName = 'core';
		protected $eventName = 'comment';
		protected $table = "comments";
		
		protected $listQry = "SELECT c.*,u.user_name FROM #comments as c LEFT JOIN #user AS u ON c.comment_author_id = u.user_id ";
		protected $listOrder	= "comment_id desc";
		//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
		protected $pid = "comment_id";
		protected $perPage = 10;
		protected $batchDelete = true;
		
		//TODO - finish 'user' type, set 'data' to all editable fields, set 'noedit' for all non-editable fields
    	protected $fields = array(
			'checkboxes'			=> array('title'=> '',				'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'comment_id'			=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 'forced'=> TRUE),
            'comment_blocked' 		=> array('title'=> LAN_STATUS,		'type' => 'method',	 	'inline'=>false, /*'writeParms' => array("approved","blocked","pending"), */'data'=> 'int', 'thclass' => 'center', 'class'=>'status center', 'filter' => true, 'batch' => true,	'width' => 'auto'),	 	// Photo
	
	   		'comment_type' 			=> array('title'=> LAN_TYPE,			'type' => 'method',			'width' => '10%',  'filter'=>TRUE),	
			
			'comment_item_id' 		=> array('title'=> LAN_ITEM,		'type' => 'text',	'readonly'=>2, 'data'=>'int',		'width' => '5%'),
         	'comment_subject' 		=> array('title'=> LAN_SUBJECT,		'type' => 'text',			'width' => 'auto', 'thclass' => 'left first', 'writeParms'=>array('size'=>'xxlarge')), // Display name
         	'comment_comment' 		=> array('title'=> LAN_COMMENTS,		'type' => 'textarea',			'width' => '30%', 'readParms' => 'expand=...&truncate=50&bb=1', 'writeParms'=>'size=xxlarge'), // Display name
		 	'comment_author_id' 	=> array('title'=> LAN_AUTHOR,		'type' => 'user',			'data' => 'int',	'width' => 'auto', 'writeParms' => 'nameField=comment_author_name'),	// User id
         	'comment_author_name' 	=> array('title'=> LAN_USER,	'type' => 'text',			'width' => 'auto', 'readParms'=>'idField=comment_author_id&link=1', 'noedit' => true, 'forceSave' => true),	// User name
         	'u.user_name' 			=> array('title'=> LAN_SYSTEM_USER,	'type' => 'user',			'width' => 'auto', 'readParms'=>'idField=comment_author_id&link=1', 'noedit' => true),	// User name
		    'comment_datestamp' 	=> array('title'=> LAN_DATESTAMP,	'type' => 'datestamp',		'width' => 'auto'),	// User date
      		'comment_ip' 			=> array('title'=> LAN_IP,			'type' => 'ip',			'width' => '10%', 'thclass' => 'center' ),	 // Real name (no real vetting)
			'comment_lock' 			=> array('title'=> LAN_LOCK,			'type' => 'boolean',		'data'=> 'int', 'thclass' => 'center', 'class'=>'center', 'filter' => true, 'batch' => true,	'width' => 'auto'),
			'options' 				=> array('title'=> LAN_OPTIONS,		'type' => null,				'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
		);
		//required (but should be optional) - default column user prefs 
		protected $fieldpref = array('checkboxes', 'comment_id', 'comment_item_id', 'comment_author_id', 'comment_author_name', 'comment_subject', 'comment_comment', 'comment_type', 'options');
		
		
		// optional, if $pluginName == 'core', core prefs will be used, else e107::getPluginConfig($pluginName);
		
		protected $prefs = array(
			'comments_engine'		=> array('title'=>LAN_ENGINE, 	'type'=>'dropdown', 'writeParms'=>array()),
			'comments_disabled'		=> array('title'=>PRFLAN_161, 	'type'=>'boolean', 'writeParms'=>'inverse=1'), // Same as 'writeParms'=>'reverse=1&enabled=LAN_DISABLED&disabled=LAN_ENABLED'  
			'anon_post'				=> array('title'=>PRFLAN_32, 	'type'=>'boolean'),
			'comments_icon'			=> array('title'=>PRFLAN_89, 	'type'=>'boolean'),
			'nested_comments'		=> array('title'=>PRFLAN_88, 	'type'=>'boolean'),
			'allowCommentEdit'		=> array('title'=>PRFLAN_90, 	'type'=>'boolean'),			
			'comments_emoticons'	=> array('title'=>PRFLAN_166, 	'type'=>'boolean')
		);


		public function init()
		{
			$engine = e107::pref('core', 'comments_engine');

			if($engine != 'e107') // Hide all other prefs.
			{
				$this->prefs = array(
					'comments_engine'		=> array('title'=>LAN_ENGINE, 	'type'=>'dropdown', 'writeParms'=>array()),
					'comments_disabled'		=> array('title'=>PRFLAN_161, 	'type'=>'boolean', 'writeParms'=>'inverse=1'),
				);

			}



			$this->prefs['comments_engine']['writeParms']['optArray'] = array('e107'=>'e107');

			$addons = e107::getAddonConfig('e_comment');
			foreach($addons as $plugin=>$config)
			{
				foreach($config as $val)
				{
					$id = $plugin."::".$val['function'];
					$this->prefs['comments_engine']['writeParms']['optArray'][$id] = $val['name'];
				}
			}




		//	print_a($addons);
		}


		public function afterUpdate($new_data, $old_data, $id)
		{
			if(($new_data['comment_type'] == 0 || $new_data['comment_type'] == 'news' ))
			{
				$total = e107::getDb()->select('comments', 'comment_id', "(comment_type = 0 OR comment_type = 'news') AND comment_item_id = ".$new_data['comment_item_id']." AND comment_blocked = 0");
				e107::getDb()->update("news", "news_comment_total= ".intval($total)." WHERE news_id=".intval($new_data['comment_item_id']));
				// e107::getMessage()->addInfo("Total Comments for this item: ".$total);
			}
		}

		public function beforeUpdate($new_data, $old_data, $id)
		{

			if(is_numeric($new_data['comment_author_name']) && !empty($new_data['comment_author_name']))
			{
				$userData = e107::user($new_data['comment_author_name']);
				$new_data['comment_author_id'] = $new_data['comment_author_name'];
				$new_data['comment_author_name'] = $userData['user_name'];
			}

			return $new_data;

		}

				
		public function beforeDelete($data, $id)
		{
			return true;
		}
	
		/**
		 * User defined after-delete logic
		 */
		public function afterDelete($deleted_data, $id, $deleted_check)
		{
			$sql = e107::getDb();
			
			// Update 'user_comments' column in #user table 
			if($deleted_data['comment_author_id'] != '0')
			{
				if(!$sql->update('user', 'user_comments = user_comments - 1 WHERE user_id='.$deleted_data['comment_author_id']))
				{
					$commentcount_update_error = $sql->getLastErrorText();
					
					e107::getMessage()->addDebug($commentcount_update_error);
					e107::getMessage()->addError(LAN_DELETED_FAILED)->render();
				} 
			}

			switch ($deleted_data['comment_type'])
			{
				case '0' :
				case 'news' :		// Need to update count in news record as well
					$sql->update('news', 'news_comment_total = CAST(GREATEST(CAST(news_comment_total AS SIGNED) - 1, 0) AS UNSIGNED) WHERE news_id='.$deleted_data['comment_item_id']);
				break;
			}

			
		}
		

		public function toolsPage()
		{

			$this->toolsProcessPage();

			$text = "<form method='post' action='".e_SELF."?".e_QUERY."'>";
			$text .= e107::getForm()->admin_button('recalcComments', LAN_RECALCULATE_COMMENT_COUNT);

			$text .= "</form>";
			return $text;

		}

		public function toolsProcessPage()
		{
			$mes = e107::getMessage();
			$sql = e107::getDb();
			$sql2 = e107::getDb('replace');

			if (isset($_POST['recalcComments']))
			{
				//
				// Recalculate the comment count
				//

				$qry = 'SELECT u.user_id, u.user_comments, COUNT(c.comment_id) as new_comments
				FROM e107_user u 
				LEFT JOIN e107_comments AS c ON (u.user_id = c.comment_author_id)
				GROUP BY u.user_id';

				if ($sql->gen($qry))
				{
					while($row = $sql->fetch())
					{
						if (intval($row['user_id'])>0 && intval($row['user_comments']) != intval($row['new_comments']))
						{
							$sql2->update('user', array('data' => array('user_comments' => $row['new_comments']), 'WHERE' => 'user_id = "'.$row['user_id'].'"'));
						}
					}
				}
				$mes->addSuccess(LAN_SUCC_RECALCULATE_COMMENT_COUNT);
			}

		}

}

//TODO Block and Unblock buttons, moderated comments?
class comments_admin_form_ui extends e_admin_form_ui
{
	function comment_type($curVal,$mode) // not really necessary since we can use 'dropdown' - but just an example of a custom function. 
	{ 
		if($mode == 'read' || $mode == 'write')
		{
			return e107::getComment()->getTable($curVal);
		//	return $curVal.' (custom!)';
		}
		
		if($mode == 'filter') // Custom Filter List for release_type
		{
			$sql = e107::getDb();
			$sql->gen('SELECT * FROM #comments GROUP BY comment_type');
			while($row = $sql->fetch())
			{
				$id = $row['comment_type'];
				$list[$id] = e107::getComment()->getTable($id);
			}
			return vartrue($list);
		}
		
		if($mode == 'batch')
		{
			$types = e107::getComment()->known_types;
			asort($types);
			
			return $types;	
		}
	}

	function comment_blocked($curVal,$mode, $parms) // not really necessary since we can use 'dropdown' - but just an example of a custom function. 
	{
		$frm = e107::getForm();
		
	//	$blocked = array("approved", "blocked", "pending");
		$blocked = array(COMLAN_400, COMLAN_401, COMLAN_402);

		if($mode == 'filter' || $mode == 'batch' || $mode == 'inline') // Custom Filter List for release_type
		{			
			return $blocked;
		}
		
		if($mode == 'read')
		{
			// $blocked = array("","blocked","pending");

			$blockedDisp = array(
				"<span class='label label-success'>".COMLAN_400."</span>",
				"<span class='label label-danger'>".COMLAN_401."</span>",
				"<span class='label label-warning'>".COMLAN_402."</span>"
			);

			return varset($blockedDisp[$curVal], ''); // $blocked[$curVal];
		}
		
		if($mode == 'write')
		{
			return $frm->select("comment_blocked", $blocked, $curVal);	
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
