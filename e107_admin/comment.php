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
|     $Revision: 1.6 $
|     $Date: 2009-09-29 17:43:13 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("B")) 
{
	header("location:".e_BASE."index.php");
	exit;
}
require_once(e_ADMIN."auth.php");




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
	$cm= new comment_manager;
	$cm->commentList();
}
// echo "<script type='text/javascript'>window.history.go(-1);</script>\n";

require_once(e_ADMIN."footer.php");


class comment_manager
{

	var $fields;
	var $fieldpref;


	function comment_manager()
	{
		global $user_pref;
        $this->fieldpref = (varset($user_pref['admin_cpage_columns'])) ? $user_pref['admin_cpage_columns'] : array("comment_id","comment_pid","comment_item_id","comment_subject","comment_comment","comment_author","comment_datestamp");

		//TODO Add LANS

    	$this->fields = array(
			'comment_id'			=> array('title'=> ID, 'width'=>'5%', 'forced'=> TRUE),
       		'comment_item_id' 		=> array('title'=> "item id", 'type' => 'text', 'width' => 'auto'),
         	'comment_subject' 		=> array('title'=> "subject", 'type' => 'text', 'width' => 'auto', 'thclass' => 'left first'), // Display name
         	'comment_author' 		=> array('title'=> "author", 'type' => 'text', 'width' => 'auto'),	// User name
			'comment_comment' 		=> array('title'=> "comment", 'type' => 'text', 'width' => 'auto'), // Display name
		    'comment_datestamp' 	=> array('title'=> "date", 'type' => 'text', 'width' => 'auto'),	// User name
            'comment_blocked' 		=> array('title'=> "blocked", 'type' => 'text', 'width' => 'auto'),	 	// Photo
			'comment_ip' 			=> array('title'=> "IP", 'type' => 'text', 'width' => '10%', 'thclass' => 'center' ),	 // Real name (no real vetting)
			'comment_type' 			=> array('title'=> "Type", 'type' => 'text', 'width' => '10%', 'thclass' => 'center' ),	 // No real vetting
			'comment_lock' 			=> array('title'=> "Lock", 'type' => 'text', 'width' => 'auto'),

	   //	'page_ip_restrict' 		=> array('title'=> LAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar

			'options' 	=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last')
		);



	}



	function commentList()
	{
		global $pref;
         $sql = e107::getDb();
		 $frm = e107::getSingleton('e_form');

         $sql -> db_Select("comments", "*");

         $text = "<form method='post' action='".e_SELF."?".e_QUERY."'>
                        <fieldset id='core-comment-list'>
						<legend class='e-hideme'>".CUSLAN_5."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>".
							$frm->colGroup($this->fields,$this->fieldpref).
							$frm->thead($this->fields,$this->fieldpref).

							"<tbody>";

		 while($row = $sql-> db_Fetch())
		 {
            $text .= "<tr>
							<td>{$row['comment_id']}</td>";

				$text .= (in_array("comment_item_id",$this->fieldpref)) ? "<td>".($row['comment_item_id'])."</td>" : "";
                $text .= (in_array("comment_subject",$this->fieldpref)) ? "<td>".($row['comment_subject'])."</td>" : "";
                $text .= (in_array("comment_author",$this->fieldpref)) ? "<td>".($row['comment_author_name'])."</td>" : "";
				$text .= (in_array("comment_comment",$this->fieldpref)) ? "<td>".($row['comment_comment'])."</td>" : "";
				$text .= (in_array("comment_datestamp",$this->fieldpref)) ? "<td>".strftime($pref['shortdate'],$row['comment_datestamp'])."</td>" : "";
				$text .= (in_array("comment_ip",$this->fieldpref)) ? "<td>".($row['comment_ip'])."</td>" : "";
				$text .= (in_array("comment_type",$this->fieldpref)) ? "<td class='center'>".($row['comment_type'])."</td>" : "";
				$text .= (in_array("comment_lock",$this->fieldpref)) ? "<td class='center'>".($row['comment_lock'] ? ADMIN_TRUE_ICON : "&nbsp;")."</td>" : "";

		 }


         $text .= "
						</tbody>
					</table>
				</fieldset>
			</form>
		";


         e107::getRender()->tablerender($caption,$text);
	}













}
?>