<?php
include_once(e_HANDLER.'shortcode_handler.php');
$forum_post_shortcodes = e_shortcode::parse_scbatch(__FILE__);
	
/*
SC_BEGIN LATESTPOSTS
global $thread_info;
global $action;
global $gen;
global $tp;
$txt = "
<table style='width:100%' class='fborder'>
<tr>
<td colspan='2' class='fcaption' style='vertical-align:top'>".
LAN_101.(count($thread_info)-2).LAN_102."
</td>
</tr>";
for($i=1; $i<count($thread_info)-1;$i++) {
if ($thread_info[$i]['thread_user']) {
$uname = $thread_info[$i]['user_name'];
} else {
$tmp = explode(chr(1), $thread_info[$i]['thread_anon']);
$uname = $tmp[0];
}
$txt .= "<tr>
<td class='forumheader3' style='width:20%' style='vertical-align:top'><b>".$uname."</b></td>
<td class='forumheader3' style='width:80%'>
<div class='smallblacktext' style='text-align:right'>".IMAGE_post2." ".LAN_322.$gen->convert_date($thread_info[$i]['thread_datestamp'] , "forum")."</div>".$tp->toHTML($thread_info[$i]['thread_thread'],TRUE)."</td>
</tr>
";
}
$txt .= "</table>";
return $txt;
SC_END
	
SC_BEGIN THREADTOPIC
global $thread_info;
global $action;
global $gen;
global $tp;
$txt = "
<table style='width:100%' class='fborder'>
<tr>
<td colspan='2' class='fcaption' style='vertical-align:top'>".LAN_100."</td></tr>";
if (!$thread_info['head']['thread_forum_id']) {
$thread_info['head']['user_name'] = get_anon_name($thread_info['head']);
}
$txt .= "<tr>
<td class='forumheader3' style='width:20%' style='vertical-align:top'><b>".$thread_info['head']['user_name']."</b></td>
<td class='forumheader3' style='width:80%'>
<div class='smallblacktext' style='text-align:right'>".IMAGE_post2." ".LAN_322.$gen->convert_date($thread_info['head']['thread_datestamp'] , "forum")."</div>".$tp->toHTML($thread_info['head']['thread_thread'],TRUE)."</td>
</tr>
</table>";
return $txt;
SC_END
*/
?>