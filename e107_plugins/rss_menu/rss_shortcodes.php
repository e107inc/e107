<?php
// $Id$
if (!defined('e107_INIT')) { exit; }   

include_once(e_HANDLER.'shortcode_handler.php');
$rss_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*
SC_BEGIN RSS_FEED
global $row, $tp;
$url2 = e_PLUGIN."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".2".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
return "<a href='".$url2."'>".$tp->toHTML($row['rss_name'], TRUE)."</a>";
SC_END

SC_BEGIN RSS_ICON
global $row, $tp;
$url2 = e_PLUGIN."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".2".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
return "<a href='".$url2."'>".RSS_ICON."</a>";
SC_END

SC_BEGIN RSS_TEXT
global $row, $tp;
return $tp->toHTML($row['rss_text'], TRUE, "defs");
SC_END

SC_BEGIN RSS_TYPES
global $row, $tp;
$url1 = e_PLUGIN_ABS."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".1".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
$url2 = e_PLUGIN_ABS."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".2".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
$url3 = e_PLUGIN_ABS."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".3".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');
$url4 = e_PLUGIN_ABS."rss_menu/rss.php?".e_LANQRY.$tp->toHTML($row['rss_url'], TRUE, 'constants').".4".($row['rss_topicid'] ? ".".$row['rss_topicid'] : '');

$text = "
<a href='".$url1."' class='rss'><img src='".e_PLUGIN."rss_menu/images/rss1.png' class='icon' alt='RSS 0.92' /></a>
<a href='".$url2."' class='rss'><img src='".e_PLUGIN."rss_menu/images/rss2.png' class='icon' alt='RSS 2.0' /></a>
<a href='".$url3."' class='rss'><img src='".e_PLUGIN."rss_menu/images/rss3.png' class='icon' alt='RDF' /></a>
<a href='".$url4."' class='rss'><img src='".e_PLUGIN."rss_menu/images/rss4.png' class='icon' alt='ATOM' /></a>
";
return $text;
SC_END

//##### ADMIN --------------------------------------------------
SC_BEGIN RSS_ADMIN_CAPTION
global $sort;
list($field,$txt) = explode(",",$parm);
$txt = constant($txt);
return "<a href='".e_SELF."?list.{$field}.".($sort == "desc" ? "asc" : "desc")."'>".$txt."</a>\n";
SC_END

SC_BEGIN RSS_ADMIN_ID
global $row;
return $row['rss_id'];
SC_END

SC_BEGIN RSS_ADMIN_NAME
global $row;
return $row['rss_name'];
SC_END

SC_BEGIN RSS_ADMIN_PATH
global $row;
return $row['rss_path'];
SC_END

SC_BEGIN RSS_ADMIN_URL
global $row;
return "<a href='".e_PLUGIN."rss_menu/rss.php?".e_LANQRY.$row['rss_url']."'>".$row['rss_url']."</a>";
SC_END

SC_BEGIN RSS_ADMIN_TOPICID
global $row;
return $row['rss_topicid'];
SC_END

SC_BEGIN RSS_ADMIN_LIMIT
global $row, $rs;
$id = $row['rss_id'];
$frm = e107::getForm();
 return $frm->number("limit[$id]",$row['rss_limit']);
return "<input class='tbox' type='text' name=\"limit[$id]\" title=\"".RSS_LAN05."\" value='".intval($row['rss_limit'])."' size='3' maxlength='3' />";
SC_END

SC_BEGIN RSS_ADMIN_LIMITBUTTON
	$frm = e107::getForm();
	return $frm->admin_button('update_limit',LAN_UPDATE,'update');
SC_END

SC_BEGIN RSS_ADMIN_OPTIONS
global $row, $tp;
$delname = $row['rss_name'];
$delid = $row['rss_id'];
$options = "
<a href='".e_SELF."?create.edit.".$row['rss_id']."' >".ADMIN_EDIT_ICON."</a>
<input type='image' title=\"".LAN_DELETE."\" name='delete[{$delid}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL ." [".RSS_LAN_ADMIN_2.": ".$delid." : ".$delname."]\\n\\n")."')\"/>";
return $options;
SC_END

SC_BEGIN RSS_ADMIN_FORM_NAME
global $row;
return "<input class='tbox' type='text' name='rss_name' size='74' value=\"".$row['rss_name']."\" />\n";
SC_END

SC_BEGIN RSS_ADMIN_FORM_URL
global $row,$PLUGINS_DIRECTORY;
return SITEURL.$PLUGINS_DIRECTORY."rss_menu/rss.php?".e_LANQRY." <input class='tbox' type='text' name='rss_url' size='10' value=\"".$row['rss_url']."\" maxlength='50' /> .{".LAN_TYPE."}.{".RSS_LAN_ADMIN_12."}";
SC_END

SC_BEGIN RSS_ADMIN_FORM_TOPICID
global $row;
return "<input class='tbox' type='text' name='rss_topicid' size='74' value=\"".$row['rss_topicid']."\" maxlength='250' />";
SC_END

SC_BEGIN RSS_ADMIN_FORM_PATH
global $row;
return "<input class='tbox' type='text' name='rss_path' size='74' value=\"".$row['rss_path']."\" maxlength='250' />";
SC_END

SC_BEGIN RSS_ADMIN_FORM_TEXT
global $row;
return "<textarea class='tbox' name='rss_text' cols='74' rows='5' >".$row['rss_text']."</textarea>\n";
SC_END

SC_BEGIN RSS_ADMIN_FORM_CLASS
global $row;
$vals = array(RSS_LAN_ADMIN_21,RSS_LAN_ADMIN_22,RSS_LAN_ADMIN_23);
$text = "<select class='tbox' name='rss_class'>";
foreach($vals as $key=>$val)
{
	$sel = ($row['rss_class'] == $key) ? " selected='selected'" : "";
	$text .= "<option value='{$key}'{$sel}>$val</option>\n";
}
$text .= "</select>";
return $text;
SC_END

SC_BEGIN RSS_ADMIN_FORM_LIMIT
global $row;
$frm = e107::getForm();

return $frm->number('rss_limit',$row['rss_limit'],3);
return "<input class='tbox' name='rss_limit' size='3' title=\"".RSS_LAN05."\" value='".intval($row['rss_limit'])."' maxlength='3' />";
SC_END


SC_BEGIN RSS_ADMIN_FORM_CREATEBUTTON
global $row;
$qs = explode(".", e_QUERY);
$frm = e107::getForm();
 
if(isset($qs[1]) && $qs[1] == "edit" && isset($qs[2]) && is_numeric($qs[2]) )
{
	$text = "<input type='hidden' name='rss_datestamp' value='".$row['rss_datestamp']."' />
	<input type='hidden' name='rss_id' value='".$row['rss_id']."' />";
 	
  	$text .= $frm->admin_button('update_rss',LAN_UPDATE,'update');
	
}
else
{
 	$text = $frm->admin_button('create_rss',LAN_CREATE,'submit');

}
return $text;
SC_END

SC_BEGIN RSS_ADMIN_IMPORT_CHECK
global $feed, $rs, $tp, $i;
if($feed['description'])
{
	$feed['text'] = $feed['description'];
} 

$text  = "<input type='checkbox' name='importid[$i]' value='1' />";
$text .= "<input type='hidden' name='name[$i]' value='".$tp->toForm($feed['name'])."' />";
$text .= "<input type='hidden' name='url[$i]' value='".$tp->toForm($feed['url'])."' />";
$text .= "<input type='hidden' name='topic_id[$i]' value='".$tp->toForm($feed['topic_id'])."' />";
$text .= "<input type='hidden' name='path[$i]' value='".$tp->toForm($feed['path'])."' />";
$text .= "<input type='hidden' name='text[$i]' value='".$tp->toForm($feed['text'])."' />";
$text .= "<input type='hidden' name='class[$i]' value='".$tp->toForm($feed['class'])."' />";
$text .= "<input type='hidden' name='limit[$i]' value='".intval($feed['limit'])."' />";
return $text;
SC_END

SC_BEGIN RSS_ADMIN_IMPORT_PATH
global $feed;
return $feed['path'];
SC_END

SC_BEGIN RSS_ADMIN_IMPORT_NAME
global $feed;
return $feed['name'];
SC_END

SC_BEGIN RSS_ADMIN_IMPORT_TEXT
global $feed;
return ($feed['description'])  ? $feed['description'] : $feed['text'];
SC_END

SC_BEGIN RSS_ADMIN_IMPORT_URL
global $feed;
return $feed['url'];
SC_END

SC_BEGIN RSS_ADMIN_IMPORT_TOPICID
global $feed;
return $feed['topic_id'];
SC_END
*/
?>