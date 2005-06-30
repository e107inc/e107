<?php
include_once(e_HANDLER.'shortcode_handler.php');
$comment_shortcodes = e_shortcode::parse_scbatch(__FILE__);
/*

SC_BEGIN SUBJECT
global $SUBJECT, $comrow, $pref, $NEWIMAGE, $tp;
if ($pref['nested_comments']) {
	$SUBJECT = $NEWIMAGE." ".(empty($comrow['comment_subject']) ? $subject : $tp->toHTML($comrow['comment_subject'], TRUE));
} else {
	$SUBJECT = '';
}
return $SUBJECT;
SC_END

SC_BEGIN USERNAME
global $USERNAME, $comrow;
if ($comrow['user_id']) {
	$USERNAME = "<a href='".e_BASE."user.php?id.".$comrow['user_id']."'>".$comrow['user_name']."</a>\n";
}else{
	$comrow['user_id'] = 0;
	$USERNAME = eregi_replace("[0-9]+\.", '', $comrow['comment_author']);
}
return $USERNAME;
SC_END

SC_BEGIN TIMEDATE
global $TIMEDATE, $comrow, $datestamp, $gen;
$datestamp = $gen->convert_date($comrow['comment_datestamp'], "short");
return $datestamp;
SC_END

SC_BEGIN REPLY
global $REPLY, $comrow, $action, $pref, $table, $id, $thisaction, $thistable, $thisid;
$REPLY = '';
if($comrow['comment_lock'] != "1"){
	if ($thisaction == "comment" && $pref['nested_comments']) {
		$REPLY = "<a href='".e_BASE."comment.php?reply.".$thistable.".".$comrow['comment_id'].".".$thisid."'>".COMLAN_6."</a>";
	}
}
return $REPLY;
SC_END

SC_BEGIN AVATAR
global $AVATAR, $comrow;
if ($comrow['user_id']) {
	if ($comrow['user_image']) {
		require_once(e_HANDLER."avatar_handler.php");
		$comrow['user_image'] = avatar($comrow['user_image']);
		$comrow['user_image'] = "<div class='spacer'><img src='".$comrow['user_image']."' alt='' /></div><br />";
	}else{
		$comrow['user_image'] = '';
	}
}else{
	$comrow['user_image'] = '';
}
return $comrow['user_image'];
SC_END

SC_BEGIN COMMENTS
global $COMMENTS, $comrow;
return ($comrow['user_id'] ? LAN_99.": ".$comrow['user_comments'] : LAN_194)."<br />";
SC_END

SC_BEGIN JOINED
global $JOINED, $comrow, $gen;
$JOINED = '';
if ($comrow['user_id'] && !$comrow['user_admin']) {
	$comrow['user_join'] = $gen->convert_date($comrow['user_join'], "short");
	$JOINED = ($comrow['user_join'] ? LAN_145." ".$comrow['user_join'] : '');
}
return $JOINED;
SC_END

//SC_BEGIN COMMENT
//global $COMMENT, $comrow, $tp, $pref;
//return ($comrow['comment_blocked'] ? LAN_0 : $tp->toHTML($comrow['comment_comment'], TRUE, FALSE, $comrow['user_id'])).($pref['allowCommentEdit'] && USER && $comrow['user_id'] == USERID && !strstr(e_QUERY, "edit") ? "<br /><div style='text-align: right;'><a href='".e_SELF."?".e_QUERY.".edit.".$comrow['comment_id']."'><img src='".e_IMAGE."generic/".IMODE."/edit.png' alt='".LAN_318."' title='".LAN_318."' style='border: 0;' /></a></div>" : "");
//SC_END

SC_BEGIN COMMENT
global $COMMENT, $comrow, $tp, $pref;
return ($comrow['comment_blocked'] ? LAN_0 : $tp->toHTML($comrow['comment_comment'], TRUE, FALSE, $comrow['user_id']));
SC_END

SC_BEGIN COMMENTEDIT
global $COMMENTEDIT, $pref, $comrow;
return ($pref['allowCommentEdit'] && (ADMIN || (USER && $comrow['user_id'] == USERID && $comrow['comment_lock'] != "1")) && !strstr(e_QUERY, "edit") ? "<a href='".e_SELF."?".e_QUERY.".edit.".$comrow['comment_id']."'><img src='".e_IMAGE."generic/".IMODE."/newsedit.png' alt='".LAN_318."' title='".LAN_318."' style='border: 0;' /></a>" : "");
SC_END

SC_BEGIN RATING
global $RATING;
return $RATING;
SC_END

SC_BEGIN IPADDRESS
global $IPADDRESS, $comrow;
require_once(e_HANDLER."encrypt_handler.php");
return (ADMIN ? "<a href='".e_BASE."userposts.php?0.comments.".$comrow['comment_ip']."'>IP: ".decode_ip($comrow['comment_ip'])."</a>" : "");
SC_END

SC_BEGIN LEVEL
global $LEVEL, $comrow, $pref;
$ldata = get_level($comrow['user_id'], $comrow['user_forums'], $comrow['user_comments'], $comrow['user_chats'], $comrow['user_visits'], $comrow['user_join'], $comrow['user_admin'], $comrow['user_perms'], $pref);
return ($comrow['user_admin'] ? $ldata[0] : $ldata[1]);
SC_END

SC_BEGIN LOCATION
global $LOCATION, $comrow, $tp;
return ($comrow['user_location'] ? LAN_313.": ".$tp->toHTML($comrow['user_location'], TRUE) : '');
SC_END

SC_BEGIN SIGNATURE
global $SIGNATURE, $comrow, $tp;
$SIGNATURE = ($comrow['user_signature'] ? $tp->toHTML($comrow['user_signature'], true) : '');
return $SIGNATURE;
SC_END

*/

?>


