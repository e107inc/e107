<?php

// New Comments menu item for e107.  Thankz go to rufe for the idea ....
// This menu item will display the most recent five Comment posts made to your news and d/l.

$text = "<span class=\"smalltext\">";

              if(!$sql -> db_Select("comments", "*", "comment_id ORDER BY comment_datestamp DESC LIMIT 0,5")){
	$text = "<span class=\"mediumtext\">No News comments yet</span>";
}else{
	while(list($comment_id_, $comment_item_id_, $comment_author_, $comment_author_email_, $comment_datestamp_, $comment_comment_, $comment_blocked_, $comment_ip_, $comment_type_) = $sql-> db_Fetch()){
		$poster = eregi_replace("[0-9]+.", "", $comment_author_);
                $gen = new convert;
		$datestamp = $gen->convert_date($comment_datestamp_, "short");
		$message_array = explode(" ", $comment_comment_);
		for($i=0; $i<=(count($message_array)-1); $i++){
			if(strlen($message_array[$i]) > 30){
				$message_array[$i] = preg_replace("/([^\s]{30})/", "$1<br />", $message_array[$i]);
			}
		}
		$comment_comment_ = implode(" ", $message_array);
		$aj = new textparse;
		$comment_comment_ = $aj -> tpa($comment_comment_);
                if($comment_type_ == "0"){
		$text .= "<a href=\"comment.php?$comment_item_id_.\"><b>".$poster."</b> on ".$datestamp."</a><br />";
                }
		if($comment_type_ == "1"){
		$text .= "<a href=\"comment.php?$comment_item_id_.\"><b>".$poster."</b> on ".$datestamp."</a><br />";
                }
		if($comment_type_ == "2"){
		$text .= "<a href=\"download_comment.php?$comment_item_id_.\"><b>".$poster."</b> on ".$datestamp."</a><br />";
                }
	}

}
$text .= "</span>";
$ns -> tablerender("Latest Comments", $text);




?>