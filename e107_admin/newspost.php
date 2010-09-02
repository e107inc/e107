<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');

if (!getperms('H')) 
{
	header('location:'.e_BASE.'index.php');
	exit;
}
require_once(e_HANDLER.'calendar/calendar_class.php');
$cal = new DHTML_Calendar(true);
function headerjs()
{
  	global $cal;
    $js = $cal->load_files();
	return $js;
}
$e_sub_cat = 'news';
$e_wysiwyg = 'data,news_extended';

// -------- Presets. ------------  // always load before auth.php
require_once(e_HANDLER.'preset_class.php');
$pst       = new e_preset;
$pst->form = 'dataform'; // form id of the form that will have it's values saved.
$pst->page = 'newspost.php?create'; // display preset options on which page(s).
$pst->id   = 'admin_newspost';
// ------------------------------

$newspost = new newspost;
require_once('auth.php');
$pst->save_preset('news_datestamp'); // save and render result using unique name. Don't save item datestamp
require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'news_class.php');
require_once(e_HANDLER.'ren_help.php');
require_once(e_HANDLER.'form_handler.php');
require_once(e_HANDLER.'file_class.php');

$fl = new e_file;
$rs = new form;
$ix = new news;

if (e_QUERY) 
{
	$tmp 		= explode('.', e_QUERY);
	$action 	= $tmp[0];
	$sub_action = varset($tmp[1],'');
	$id 		= intval(varset($tmp[2],0));
	$sort_order = varset($tmp[2],'desc');
	$from 		= intval(varset($tmp[3],0));
	unset($tmp);
}
$from = ($from ? $from : 0);
$amount = 10;

// ##### Main loop -----------------------------------------------------------
if(isset($_POST['news_userclass']))
{
	$_POST['news_class'] = implode(',', array_keys($_POST['news_userclass']));
}

if (isset($_POST['news_comments_recalc']))
{
	$qry = "SELECT 
			COUNT(`comment_id`) AS c_count,
			`comment_item_id`
			FROM `#comments`
			WHERE (`comment_type`='0') OR (`comment_type`='news')
			GROUP BY `comment_item_id`";
	if ($sql->db_Select_gen($qry))
	{
		while ($row = $sql->db_Fetch(MYSQL_ASSOC))
		{
			$sql2->db_Update('news', 'news_comment_total = '.$row['c_count'].' WHERE news_id='.$row['comment_item_id']);
		}
	}
	$newspost->show_message(LAN_NEWS_53);
}

if(isset($_POST['delete']))
{
	$tmp = array_keys($_POST['delete']);
	list($delete, $del_id) = explode('_', $tmp[0]);
}

if ($delete == 'main' && $del_id)
{
	$del_id = intval($del_id);
	if ($sql->db_Count('news','(*)',"WHERE news_id = '{$del_id}'"))
	{
		$e_event->trigger('newsdel', $del_id);
		if($sql->db_Delete('news', "news_id='{$del_id}'"))
		{
			$newspost->show_message(NWSLAN_31.' #'.$del_id.' '.NWSLAN_32);
			$e107cache->clear('news.php');
			$e107cache->clear('othernews');
			$e107cache->clear('othernews2');
			admin_purge_related('news', $del_id);
		}
	}
	unset($delete, $del);
}

if ($delete == 'category' && $del_id)
{
	if ($sql->db_Delete('news_category', "category_id='{$del_id}'"))
	{
		$newspost->show_message(NWSLAN_33.' #'.$del_id.' '.NWSLAN_32);
		unset($delete, $del_id);
	}
}

if($delete == 'sn' && $del_id)
{
	if ($sql->db_Delete('submitnews', "submitnews_id='{$del_id}'"))
	{
		$newspost->show_message(NWSLAN_34.' #'.$del_id.' '.NWSLAN_32);
		$e107cache->clear('news.php');
		$e107cache->clear('othernews');
		$e107cache->clear('othernews2');
		unset($delete, $del_id);
	}
}

if (isset($_POST['submitupload'])) 
{
	$pref['upload_storagetype'] = '1';
	require_once(e_HANDLER.'upload_handler.php');
	$uploaded = file_upload(e_IMAGE.'newspost_images/');
	foreach($_POST['uploadtype'] as $key=>$uploadtype)
	{
		if($uploadtype == 'thumb')
		{
			rename(e_IMAGE.'newspost_images/'.$uploaded[$key]['name'],e_IMAGE.'newspost_images/thumb_'.$uploaded[$key]['name']);
		}

		if($uploadtype == 'file')
		{
			rename(e_IMAGE.'newspost_images/'.$uploaded[$key]['name'],e_FILE.'downloads/'.$uploaded[$key]['name']);
		}

		if ($uploadtype == 'resize' && $_POST['resize_value'])
		{
			require_once(e_HANDLER.'resize_handler.php');
			resize_image(e_IMAGE.'newspost_images/'.$uploaded[$key]['name'], e_IMAGE.'newspost_images/'.$uploaded[$key]['name'], $_POST['resize_value'], 'copy');
		}
	}
}

// required.
if (isset($_POST['preview'])) 
{
	$_POST['news_title'] = $tp->toDB($_POST['news_title']);
	$_POST['news_summary'] = $tp->toDB($_POST['news_summary']);
	$newspost->preview_item($id);
}

if (isset($_POST['submit_news'])) 
{
	$newspost->submit_item($sub_action, $id);
	$e107cache->clear('news.php');
	$e107cache->clear('othernews');
	$e107cache->clear('othernews2');
	$action = 'main';
	unset($sub_action, $id);
}

if (isset($_POST['create_category']))
{
	if ($_POST['category_name'])
	{
		if (empty($_POST['category_button']))
		{
			$handle = opendir(e_IMAGE.'icons');
			while ($file = readdir($handle))
			{
				if ($file != '.' && $file != '..' && $file != '/' && $file != 'null.txt' && $file != 'CVS')
				{
					$iconlist[] = $file;
				}
			}
			closedir($handle);
			$_POST['category_button'] = $iconlist[0];
		}
		$_POST['category_name'] = $tp->toDB($_POST['category_name']);
		$sql->db_Insert('news_category', "'0', '".$_POST['category_name']."', '".$_POST['category_button']."'");
		$newspost->show_message(NWSLAN_35);
	}
}

if (isset($_POST['update_category'])) 
{
	if ($_POST['category_name']) 
	{
		$category_button = ($_POST['category_button'] ? $_POST['category_button'] : '');
		$_POST['category_name'] = $tp->toDB($_POST['category_name']);
		$sql->db_Update('news_category', "category_name='".$_POST['category_name']."', category_icon='".$category_button."' WHERE category_id='".$_POST['category_id']."'");
		$newspost->show_message(NWSLAN_36);
	}
	$e107cache->clear('news.php');
	$e107cache->clear('othernews');
	$e107cache->clear('othernews2');
}

if (isset($_POST['save_prefs'])) 
{
	$pref['newsposts']					= $_POST['newsposts'];
	$pref['newsposts_archive']			= $_POST['newsposts_archive'];
	$pref['newsposts_archive_title']	= $tp->toDB($_POST['newsposts_archive_title']);
	$pref['news_cats']					= $_POST['news_cats'];
	$pref['nbr_cols']					= $_POST['nbr_cols'];
	$pref['subnews_attach']				= $_POST['subnews_attach'];
	$pref['subnews_resize']				= $_POST['subnews_resize'];
	$pref['subnews_class']				= $_POST['subnews_class'];
	$pref['subnews_htmlarea']			= $_POST['subnews_htmlarea'];
	$pref['subnews_hide_news']			= $_POST['subnews_hide_news'];
	$pref['news_subheader']				= $tp->toDB($_POST['news_subheader']);
	$pref['news_newdateheader']			= $_POST['news_newdateheader'];
	$pref['news_unstemplate']			= $_POST['news_unstemplate'];
	save_prefs();
	$e107cache->clear('news.php');
	$e107cache->clear('othernews');
	$e107cache->clear('othernews2');
	$newspost->show_message(NWSLAN_119);
}

if (!e_QUERY || $action == 'main') 
{
  $newspost->show_existing_items($action, $sub_action, $sort_order, $from, $amount);
}

if ($action == 'create') 
{
	$preset = $pst->read_preset('admin_newspost');  //only works here because $_POST is used.
	if ($sub_action == 'edit' && !$_POST['preview'] && !$_POST['submit_news']) 
	{
		if ($sql->db_Select('news', '*', "news_id='{$id}'"))
		{
			$row = $sql->db_Fetch();
			extract($row);
			$_POST['news_title'] = $news_title;
			$_POST['data'] = $news_body;
			$_POST['news_extended'] = $news_extended;
			$_POST['news_allow_comments'] = $news_allow_comments;
			$_POST['news_class'] = $news_class;
			$_POST['news_summary'] = $news_summary;
			$_POST['news_sticky'] = $news_sticky;
			$_POST['news_datestamp'] = ($_POST['news_datestamp']) ? $_POST['news_datestamp'] : $news_datestamp;
			$_POST['cat_id'] = $news_category;
			$_POST['news_start'] = $news_start;
			$_POST['news_end'] = $news_end;
			$_POST['comment_total'] = $sql->db_Count('comments', '(*)', "WHERE comment_item_id='{$news_id}' AND comment_type='0'");
			$_POST['news_rendertype'] = $news_render_type;
			$_POST['news_thumbnail'] = $news_thumbnail;
			$_POST['news_author'] = $news_author;
		}
	}
	$newspost->create_item($sub_action, $id);
}

if ($action == 'cat') 
{
	$newspost->show_categories($sub_action, $id);
}

if ($action == 'maint') 
{
	$newspost->show_maintenance($sub_action, $id);
}

if ($action == 'sn') 
{
	$newspost->submitted_news($sub_action, $id);
}

if ($action == 'pref') 
{
	$newspost->show_news_prefs($sub_action, $id);
}

echo "
<script type=\"text/javascript\">
function fclear() {
	document.getElementById('dataform').data.value = \"\";
	document.getElementById('dataform').news_extended.value = \"\";
}
</script>\n";
require_once('footer.php');
exit;

class newspost 
{
	function show_existing_items($action, $sub_action, $sort_order, $from, $amount) 
	{
		// ##### Display scrolling list of existing news items
		global $sql, $ns, $tp;
		$text = "<div style='text-align:center'>";

		if (!$sort_order) $sort_order = 'desc';
		if ($sort_order != 'asc') $sort_order = 'desc';
		$sort_link = $sort_order == 'asc' ? 'desc' : 'asc';		// Effectively toggle setting for headings
	  
		if (isset($_POST['searchquery'])) 
		{
			$query = "news_title REGEXP('".$_POST['searchquery']."') OR news_body REGEXP('".$_POST['searchquery']."') OR news_extended REGEXP('".$_POST['searchquery']."') ORDER BY news_datestamp DESC";
		} 
		else 
		{
			$query = "ORDER BY ".($sub_action ? $sub_action : "news_datestamp")." ".strtoupper($sort_order)." LIMIT {$from}, {$amount}";
		}
		
		if ($sql->db_Select('news', '*', $query, ($_POST['searchquery'] ? 0 : 'nowhere')))
		{
			$newsarray = $sql -> db_getList();
			$text .= "
			<form action='".e_SELF."' id='newsform' method='post'>
			<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td style='width:5%' class='fcaption'><a href='".e_SELF."?main.news_id.{$sort_link}.{$from}'>".LAN_NEWS_45."</a></td>
			<td style='width:55%' class='fcaption'><a href='".e_SELF."?main.news_title.{$sort_link}.{$from}'>".NWSLAN_40."</a></td>
			<td style='width:15%' class='fcaption'>".LAN_NEWS_49."</td>
			<td style='width:15%' class='fcaption'>".LAN_OPTIONS."</td>
			</tr>";
			$ren_type = array('default','title','other-news','other-news 2');
			foreach($newsarray as $row)
			{
				extract($row);
				// Note: To fix the alignment bug. Put both buttons inside the Form.
				// But make EDIT a 'button' and DELETE 'submit'
				$text .= "<tr>
				<td style='width:5%' class='forumheader3'>{$news_id}</td>
				<td style='width:55%' class='forumheader3'><a href='".e_BASE."news.php?item.{$news_id}.{$news_category}'>".($news_title ? $tp->toHTML($news_title,"","no_hook,emotes_off,no_make_clickable") : "[".NWSLAN_42."]")."</a></td>
				<td style='20%' class='forumheader3'>";
				$text .= $ren_type[$news_render_type];
				if($news_sticky)
				{
					$sicon = (file_exists(THEME.'images/sticky.png') ? THEME.'images/sticky.png' : e_IMAGE.'generic/'.IMODE.'/sticky.png');
					$text .= " <img src='".$sicon."' alt='' />";
				}
				$text .= "
				</td>

				<td style='width:15%; text-align:center' class='forumheader3'>
				<a href='".e_SELF."?create.edit.{$news_id}'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[main_{$news_id}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".NWSLAN_39." [ID: $news_id ]')\"/>
				</td>
				</tr>";
			}
			$text .= '</table></form>';
		} 
		else 
		{
			$text .= "<div style='text-align:center'>".NWSLAN_43."</div>";
		}

		$newsposts = $sql->db_Count('news');
		if (!$_POST['searchquery']) 
		{
			$parms = $newsposts.','.$amount.','.$from.','.e_SELF.'?'.(e_QUERY ? "{$action}.{$sub_action}.{$sort_order}." : "main.news_datestamp.desc.")."[FROM]";
			$text .= '<br />'.$tp->parseTemplate("{NEXTPREV={$parms}}");
		}
		$text .= "<br /><form method='post' action='".e_SELF."'>\n<p>\n<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />\n<input class='button' type='submit' name='searchsubmit' value='".NWSLAN_63."' />\n</p>\n</form>\n</div>";
		$ns->tablerender(NWSLAN_4, $text);
	}

	function show_options($action) 
	{
		global $sql;

		if ($action == '') {
			$action = 'main';
		}
		$var['main']['text'] = NWSLAN_44;
		$var['main']['link'] = e_SELF;

		$var['create']['text'] = NWSLAN_45;
		$var['create']['link'] = e_SELF.'?create';

		$var['cat']['text'] = NWSLAN_46;
		$var['cat']['link'] = e_SELF.'?cat';
		$var['cat']['perm'] = '7';

		$var['pref']['text'] = NWSLAN_90;
		$var['pref']['link'] = e_SELF.'?pref';
		$var['pref']['perm'] = 'N';
		if ($sql->db_Select('submitnews', '*', "submitnews_auth ='0'"))
		{
			$var['sn']['text'] = NWSLAN_47;
			$var['sn']['link'] = e_SELF.'?sn';
			$var['sn']['perm'] = 'N';
		}
		if (getperms('0'))
		{
		$var['maint']['text'] = LAN_NEWS_50;
		$var['maint']['link'] = e_SELF.'?maint';
		$var['maint']['perm'] = 'N';
		}
		show_admin_menu(NWSLAN_48, $action, $var);
	}

	function create_item($sub_action, $id)
	{
		global $cal;
		// ##### Display creation form 
		global $sql, $rs, $ns, $pref, $tp, $pst, $e107;

		if ($sub_action == 'sn' && !$_POST['preview']) 
		{
			if ($sql->db_Select('submitnews', '*', 'submitnews_id='.$id, TRUE))
			{
				list($id, $submitnews_name, $submitnews_email, $_POST['news_title'], $submitnews_category, $_POST['data'], $submitnews_datestamp, $submitnews_ip, $submitnews_auth, $submitnews_file) = $sql->db_Fetch();
				if (e_WYSIWYG)
				{
					if (substr($_POST['data'],-7,7) == '[/html]') $_POST['data'] = substr($_POST['data'],0,-7);
					if (substr($_POST['data'],0,6) == '[html]') $_POST['data'] = substr($_POST['data'],6);
					$_POST['data'] .= '<br /><b>'.NWSLAN_49.' '.$submitnews_name.'</b>';
					$_POST['data'] .= ($submitnews_file)? "<br /><br /><img src='{e_IMAGE}newspost_images/".$submitnews_file."' style='float:right; margin-left:5px;margin-right:5px;margin-top:5px;margin-bottom:5px; border:1px solid' />":'';
				}
				else
				{
					$_POST['data'] .= "\n[[b]".NWSLAN_49.' '.$submitnews_name.'[/b]]';
					$_POST['data'] .= ($submitnews_file)?"\n\n[img]{e_IMAGE}newspost_images/".$submitnews_file.'[/img]': '';
				}
				$_POST['cat_id'] = $submitnews_category;
				$_POST['data'] = $tp->dataFilter($_POST['data']);		// Filter any nasties
				$_POST['news_title'] = $tp->dataFilter($_POST['news_title']);
			}
		}

		if ($sub_action == 'upload' && !$_POST['preview']) 
		{
			if ($sql->db_Select('upload', '*', 'upload_id='.$id)) {
				$row = $sql->db_Fetch();
				extract($row);
				$post_author_id = substr($upload_poster, 0, strpos($upload_poster, '.'));
				$post_author_name = substr($upload_poster, (strpos($upload_poster, '.')+1));
				$upload_file = 'pub_' . (preg_match("#Binary\s(.*?)\/#", $upload_file, $match) ? $match[1] : $upload_file);
				$_POST['news_title'] = LAN_UPLOAD.': '.$upload_name;
				$_POST['data'] = $upload_description."\n[b]".NWSLAN_49." <a href='user.php?id.".$post_author_id."'>".$post_author_name."</a>[/b]\n\n[file=request.php?".$upload_file."]".$upload_name."[/file]\n";
			}
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."' id='dataform' ".(FILE_UPLOADS ? "enctype='multipart/form-data'" : '')." >
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_6.": </td>
		<td style='width:80%' class='forumheader3'>";

		if (!$sql->db_Select('news_category'))
		{
			$text .= NWSLAN_10;
		}
		else
		{
			$text .= "\t<select name='cat_id' class='tbox'>\n";
			while (list($cat_id, $cat_name, $cat_icon) = $sql->db_Fetch())
			{
				$sel = ($_POST['cat_id'] == $cat_id) ? "selected='selected'" : '';
				$text .= "<option value='{$cat_id}' {$sel}>".$tp->toHTML($cat_name,FALSE,'defs')."</option>\n";
			}
			$text .= "</select>";
		}
		$text .= "</td>
		</tr>
		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_12.":</td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='text' name='news_title' size='80' value='".$tp->post_toForm($_POST['news_title'])."' maxlength='200' style='width:95%'/>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".LAN_NEWS_27.":</td>
		<td style='width:80%' class='forumheader3'>
		<input class='tbox' type='text' name='news_summary' size='80' value='".$tp->post_toForm($_POST['news_summary'])."' maxlength='250' style='width:95%'/>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_13.":<br /></td>
		<td style='width:80%;margin-left:auto' class='forumheader3'>";

		$insertjs = (!e_WYSIWYG) ? "rows='15' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'": "rows='25' ";
//		$_POST['data'] = $tp->toForm($_POST['data']);
		$text .= "<textarea class='tbox' id='data' name='data'  cols='80'  style='width:100%' {$insertjs}>".(strstr($tp->post_toForm($_POST['data']), '[img]http') ? $tp->post_toForm($_POST['data']) : str_replace('[img]../', '[img]', $tp->post_toForm($_POST['data'])))."</textarea>";
        $text .= display_help('helpb', 'news');

		//Extended news form textarea
		if(e_WYSIWYG){ $ff_expand = "tinyMCE.execCommand('mceResetDesignMode')";  } // Fixes Firefox issue with hidden wysiwyg textarea.
		$text .= "
		</td>
		</tr>
		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_14.":</td>
		<td style='width:80%' class='forumheader3'>
		<a style='cursor: pointer' onclick=\"expandit(this);{$ff_expand}\">".NWSLAN_83."</a>
		<div style='display:none'>
		<textarea class='tbox' id='news_extended' name='news_extended' cols='80' style='width:95%' {$insertjs}>".(strstr($tp->post_toForm($_POST['news_extended']), "[img]http") ? $tp->post_toForm($_POST['news_extended']) : str_replace("[img]../", "[img]", $tp->post_toForm($_POST['news_extended'])))."</textarea>
		". display_help('helpc', 'extended')."
		</div>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_66.":</td>
		<td style='width:80%' class='forumheader3'>
		<a style='cursor: pointer' onclick='expandit(this);'>".NWSLAN_69."</a>
		<div style='display: none;'>";

		if (!FILE_UPLOADS)
		{
			$text .= '<b>'.LAN_UPLOAD_SERVEROFF.'</b>';
		}
		else
		{
			if (!is_writable(e_FILE.'downloads'))
			{
				$text .= LAN_UPLOAD_777.'<b>'.str_replace('../','',e_FILE.'downloads/').'</b><br /><br />';
			}
			if (!is_writable(e_IMAGE.'newspost_images'))
			{
				$text .= LAN_UPLOAD_777.'<b>'.str_replace('../','',e_IMAGE.'newspost_images/').'</b><br /><br />';
			}

			$up_name = array(LAN_NEWS_24,NWSLAN_67,LAN_NEWS_22,NWSLAN_68);
			$up_value = array('resize','image','thumb','file');

			$text .= "<div id='up_container' >
			<span id='upline' style='white-space:nowrap'>
			<input class='tbox' type='file' name='file_userfile[]' size='40' />
			<select class='tbox' name='uploadtype[]'>";
			for ($i=0; $i<count($up_value); $i++)
			{
				$selected = ($_POST['uploadtype'] == $up_value[$i]) ? "selected='selected'" : '';
				$text .= "<option value='".$up_value[$i]."' {$selected}>".$up_name[$i]."</option>\n";
			}

			$text .="</select>&nbsp;</span>
			</div>
			<table style='width:100%'>
			<tr><td><input type='button' class='button' value='".LAN_NEWS_26."' onclick=\"duplicateHTML('upline','up_container');\"  /></td>
			<td><span class='smalltext'>".LAN_NEWS_25."</span>&nbsp;<input class='tbox' type='text' name='resize_value' value='".($_POST['resize_value'] ? $_POST['resize_value'] : '100')."' size='3' />&nbsp;px</td>
			<td><input class='button' type='submit' name='submitupload' value='".NWSLAN_66."' /></td>
			</tr></table>";
		}
		$text .= "</div>
		</td>
		</tr>

		<tr>
		<td class='forumheader3'>".NWSLAN_67.":</td>
		<td class='forumheader3'>
		<a style='cursor: pointer' onclick='expandit(this);'>".LAN_NEWS_23."</a>
		<div style='display: none'><br />";

        $parms = 'name=news_thumbnail';
		$parms .= '&path='.e_IMAGE.'newspost_images/';
		$parms .= '&default='.$_POST['news_thumbnail'];
		$parms .= '&width=100px';
		$parms .= '&height=100px';
		$parms .= '&multiple=TRUE';
		$parms .= '&label=-- '.LAN_NEWS_48.' --';

        $text .= $tp->parseTemplate("{IMAGESELECTOR={$parms}}");

		$text .= "</div>
		</td>
		</tr>
		";

		$text .= "<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_15.":</td>
		<td style='width:80%' class='forumheader3'>
		<a style='cursor: pointer' onclick='expandit(this);'>".NWSLAN_18."</a>
		<div style='display: none;'>

		". ($_POST['news_allow_comments'] ? "<input name='news_allow_comments' type='radio' value='0' />".LAN_ENABLED."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1' checked='checked' />".LAN_DISABLED : "<input name='news_allow_comments' type='radio' value='0' checked='checked' />".LAN_ENABLED."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1' />".LAN_DISABLED)."
		</div>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_73.":</td>
		<td style='width:80%' class='forumheader3'>
		<a style='cursor: pointer' onclick='expandit(this);'>".NWSLAN_74."</a>
		<div style='display: none;'>";
		$ren_type = array(NWSLAN_75,NWSLAN_76,NWSLAN_77,NWSLAN_77.' 2');
		foreach($ren_type as $key=>$value) 
		{
			$checked = ($_POST['news_rendertype'] == $key) ? "checked='checked'" : '';
			$text .= "<input name='news_rendertype' type='radio' value='{$key}' {$checked} />";
			$text .= $value.'<br />';
		}

		$text .="</div>
		</td>
		</tr>

		<tr>
		<td style='width:20%' class='forumheader3'>".NWSLAN_19.":</td>
		<td style='width:80%' class='forumheader3'>

		<a style='cursor: pointer' onclick='expandit(this);'>".NWSLAN_72."</a>
		<div style='display: none;'>

		<br />
		".NWSLAN_21.":<br />";

		$_startdate = ($_POST['news_start'] > 0) ? date('d/m/Y', $_POST['news_start']) : '';

		$cal_options['showsTime'] = false;
		$cal_options['showOthers'] = false;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = '%d/%m/%Y';
		$cal_attrib['class'] = 'tbox';
		$cal_attrib['size'] = '10';
		$cal_attrib['name'] = 'news_start';
		$cal_attrib['value'] = $_startdate;
		$text .= $cal->make_input_field($cal_options, $cal_attrib);
		$text .= ' - ';
		$_enddate = ($_POST['news_end'] > 0) ? date('d/m/Y', $_POST['news_end']) : '';

		unset($cal_options);
		unset($cal_attrib);
		$cal_options['showsTime'] = false;
		$cal_options['showOthers'] = false;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = '%d/%m/%Y';
		$cal_attrib['class'] = 'tbox';
		$cal_attrib['size'] = '10';
		$cal_attrib['name'] = 'news_end';
		$cal_attrib['value'] = $_enddate;
		$text .= $cal->make_input_field($cal_options, $cal_attrib);

		$text .= "
		</div>
		</td>
		</tr>";
		$text .="<tr>
		<td class='forumheader3'>
		".LAN_NEWS_32.":
		</td>
		<td class='forumheader3'>
		<a style='cursor: pointer' onclick='expandit(this);'>".LAN_NEWS_33."</a>
		<div style='display: none;'>";
		$update_checked = ($_POST['update_datestamp']) ? "checked='checked'" : '';

		$_update_datestamp = ($_POST['news_datestamp'] > 0 && !strpos($_POST['news_datestamp'],'/')) ? date('d/m/Y H:i:s', $_POST['news_datestamp']) : trim($_POST['news_datestamp']);
		unset($cal_options);
		unset($cal_attrib);
		$cal_options['showsTime'] = true;
		$cal_options['showOthers'] = true;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = '%d/%m/%Y %H:%M:%S';
		$cal_options['timeFormat'] = '24';
		$cal_attrib['class'] = 'tbox';
		$cal_attrib['name'] = 'news_datestamp';
		$cal_attrib['value'] = $_update_datestamp;
		$text .= $cal->make_input_field($cal_options, $cal_attrib);

		$text .= "<br />
		<input type='checkbox' value='1' name='update_datestamp' {$update_checked} />".NWSLAN_105."
		</div>
		</td></tr>";

		// -------- end of datestamp ---------------------

		$text .="	<tr>
		<td class='forumheader3'>
		".NWSLAN_22.":
		</td>
		<td class='forumheader3'>

		<a style='cursor: pointer' onclick='expandit(this);'>".NWSLAN_84."</a>
		<div style='display: none;'>
		".r_userclass_check('news_userclass', $_POST['news_class'], 'nobody,public,guest,member,admin,classes,language')."
		</div>
		</td></tr>

		<tr>
		<td class='forumheader3'>
		".LAN_NEWS_28.":
		</td>
		<td class='forumheader3'>

		<a style='cursor: pointer' onclick='expandit(this);'>".LAN_NEWS_29."</a>
		<div style='display: none;'>
		";
		if($_POST['news_sticky'])
		{
			$sel = " checked='checked' ";
		}
		else
		{
			$sel = '';
		}
		$text .= "<input type='checkbox' {$sel} name='news_sticky' value='1' /> ".LAN_NEWS_30."\n</div>\n</td>\n</tr>\n";

		if($pref['trackbackEnabled'])
		{
			$text .= "<tr>
			<td class='forumheader3'>".LAN_NEWS_34.":</td>
			<td class='forumheader3'><a style='cursor: pointer' onclick='expandit(this);'>".LAN_NEWS_35."</a>
			<div style='display: none;'>
			<span class='smalltext'>";
			/* pingback */
			//	<input type='checkbox' name='pingback_urls' ".($_POST['pingback_urls'] ? " checked='checked'" : "")." />".LAN_NEWS_36."<br />
			$text .= LAN_NEWS_37."</span><br />
			<textarea class='tbox' name='trackback_urls' style='width:95%;height:100px'>".$_POST['trackback_urls']."</textarea>
			</div>
			</td>
			</tr>\n";
		}
		
		if($_POST['news_author'] == 0)
		{	// create new article with current author
			$_POST['news_author'] = USERID;
		}
		$user = get_user_data($tp->post_toForm($_POST['news_author']));
		$text .= "
			<tr>
				<td class='forumheader3'>".LAN_NEWS_55.":</td>
				<td class='forumheader3'><a style='cursor: pointer' onclick='expandit(this);'>".$user['user_name']."</a>
					<div style='display: none;'>
						<select class='tbox' name='news_author'>";
		$query = "SELECT * FROM #user WHERE (user_admin = '1' AND user_perms = '0') OR (user_perms REGEXP 'H.') ORDER BY user_id";
		$sql->db_Select_Gen($query);
		while (list($user_id,$user_name) = $sql->db_Fetch())
		{
			$selected = (intval($user_id) == intval($user['user_id'])) ? "selected='selected'" : '';
			$text .= "		<option value='".$user_id."' {$selected}>".$user_name."</option>";
		}
		$text .= "
						</select>
					</div>
				</td>
			</tr>\n";

		$text .= "<tr style='vertical-align: top;'>
		<td colspan='2'  style='text-align:center' class='forumheader'>".

		(isset($_POST['preview']) ? "<input class='button' type='submit' name='preview' value='".NWSLAN_24."' /> " : "<input class='button' type='submit' name='preview' value='".NWSLAN_27."' /> ").
		($id && $sub_action != 'sn' && $sub_action != 'upload' ? "<input class='button' type='submit' name='submit_news' value='".NWSLAN_25."' /> " : "<input class='button' type='submit' name='submit_news' value='".NWSLAN_26."' /> ")."

		<input type='hidden' name='news_id' value='{$news_id}' />  \n</td>
		</tr>
		</table>

		</form>
		</div>";
		$ns->tablerender(NWSLAN_29, $text);
	}

	function preview_item($id) 
	{
		// ##### Display news preview -----------------------------------------
		global $tp, $sql, $ix, $IMAGES_DIRECTORY;

		$_POST['news_id'] = $id;

		if($_POST['news_start'])
		{
			$tmp = explode('/', $_POST['news_start']);
			$_POST['news_start'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$tmp = explode('/', $_POST['news_end']);
			$_POST['news_end'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_end'] = 0;
		}

		if(preg_match("#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#", $_POST['news_datestamp'], $matches))
		{
			$_POST['news_datestamp'] = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
		}
		else
		{
			$_POST['news_datestamp'] = time();
		}

		if($_POST['update_datestamp'])
		{
			$_POST['news_datestamp'] = time();
		}

		$sql->db_Select('news_category', '*', "category_id='".intval($_POST['cat_id'])."'");
		list($_POST['category_id'], $_POST['category_name'], $_POST['category_icon']) = $sql->db_Fetch();
		$_POST['user_id'] = USERID;
		$_POST['user_name'] = USERNAME;
		$_POST['comment_total'] = $comment_total;
		$_PR = $_POST;

/*		$_PR['news_body'] = $tp->post_toHTML($_PR['data'],FALSE);
		$_PR['news_title'] = $tp->post_toHTML($_PR['news_title'],FALSE,"emotes_off, no_make_clickable");
		$_PR['news_summary'] = $tp->post_toHTML($_PR['news_summary']);
		$_PR['news_extended'] = $tp->post_toHTML($_PR['news_extended']);  */
		$_PR['news_body'] = $tp->toDB($_PR['data']);
		$_PR['news_title'] = $tp->toDB($_PR['news_title']);
		$_PR['news_summary'] = $tp->toDB($_PR['news_summary']);
		$_PR['news_extended'] = $tp->toDB($_PR['news_extended']);
		$_PR['news_file'] = $_POST['news_file'];
		$_PR['news_image'] = $_POST['news_image'];

		$ix->render_newsitem($_PR);
		echo $tp -> parseTemplate('{NEWSINFO}', FALSE, $news_shortcodes);
	}

	function submit_item($sub_action, $id)
	{
		// ##### Format and submit item ---------------------------------------------------------------------------------------------------------
		global $tp, $ix, $sql;
		if($_POST['news_start'])
		{
			$tmp = explode('/', $_POST['news_start']);
			$_POST['news_start'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$tmp = explode('/', $_POST['news_end']);
			$_POST['news_end'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_end'] = 0;
		}

		if(preg_match("#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#", $_POST['news_datestamp'], $matches))
		{
			$_POST['news_datestamp'] = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
		}
		else
		{
			$_POST['news_datestamp'] = time();
		}

		if($_POST['update_datestamp'])
		{
			$_POST['news_datestamp'] = time();
		}

		if($sub_action == 'edit')
		{
			if(!isset($_POST['news_author']))
			{
				$_POST['news_author'] = -1;
			}
		}

		if ($id && $sub_action != 'sn' && $sub_action != 'upload')
		{
			$_POST['news_id'] = $id;
		}
		else
		{
			$sql->db_Update('submitnews', "submitnews_auth='1' WHERE submitnews_id ='".$id."' ");
		}
		if (!$_POST['cat_id']) {
			$_POST['cat_id'] = 1;
		}
		$this->show_message($ix->submit_item($_POST));
		unset($_POST['news_title'], $_POST['cat_id'], $_POST['data'], $_POST['news_extended'], $_POST['news_allow_comments'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear'], $_POST['news_id'], $_POST['news_class']);
	}

	function show_message($message)
	{
		// ##### Display comfort ----------------------------------------------
		global $ns;
		$ns->tablerender('', "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function show_categories($sub_action, $id) 
	{
		global $sql, $rs, $ns, $tp;
		$handle = opendir(e_IMAGE.'icons');
		while ($file = readdir($handle))
		{
			if ($file != '.' && $file != '..' && $file != '/' && $file != 'null.txt' && $file != 'CVS') 
			{
				$iconlist[] = $file;
			}
		}
		closedir($handle);

		if ($sub_action == 'edit') {
			if ($sql->db_Select('news_category', '*', "category_id='{$id}'"))
			{
				$row = $sql->db_Fetch();
				extract($row);
			}
		}

		$text = "<div style='text-align:center'>
		".$rs->form_open('post', e_SELF.'?cat', 'dataform')."
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".NWSLAN_52."</span></td>
		<td class='forumheader3' style='width:70%'>".$rs->form_text('category_name', 30, $category_name, 200)."</td>
		</tr>
		<tr>
		<td class='forumheader3' style='width:30%'><span class='defaulttext'>".NWSLAN_53."</span></td>
		<td class='forumheader3' style='width:70%'>
		".$rs->form_text('category_button', 60, $category_icon, 100)."
		<br />
		<input class='button' type ='button' style='cursor: pointer' size='30' value='".NWSLAN_54."' onclick='expandit(this)' />
		<div id='caticn' style='display:none'>";
		while (list($key, $icon) = each($iconlist))
		{
			$text .= "<a href=\"javascript:insertext('{$icon}','category_button','caticn')\"><img src='".e_IMAGE."icons/".$icon."' style='border:0' alt='' /></a>\n ";
		}
		$text .= "</div></td>
		</tr>

		<tr><td colspan='2' style='text-align:center' class='forumheader'>";
		if ($id) 
		{
			$text .= "<input class='button' type='submit' name='update_category' value='".NWSLAN_55."' />
			".$rs->form_button('submit', 'category_clear', NWSLAN_79). $rs->form_hidden('category_id', $id)."
			</td></tr>";
		}
		else
		{
			$text .= "<input class='button' type='submit' name='create_category' value='".NWSLAN_56."' /></td></tr>";
		}
		$text .= "</table>
		".$rs->form_close()."
		</div>";

		$ns->tablerender(NWSLAN_56, $text);

		unset($category_name, $category_icon);

		$text = "<div style='text-align: center'>";
		if ($category_total = $sql->db_Select('news_category'))
		{
			$text .= "
			<form action='".e_SELF."?cat' id='newscatform' method='post'>
			<table class='fborder' style='".ADMIN_WIDTH."'>
			<tr>
			<td style='width:5%' class='fcaption'>".LAN_NEWS_45."</td>
			<td style='width:5%' class='fcaption'>&nbsp;</td>
			<td style='width:70%' class='fcaption'>".NWSLAN_6."</td>
			<td style='width:20%; text-align:center' class='fcaption'>".LAN_OPTIONS."</td>
			</tr>";
			while ($row = $sql->db_Fetch())
			{
				extract($row);
				if ($category_icon) 
				{
					$icon = (strstr($category_icon, 'images/') ? THEME.$category_icon : e_IMAGE.'icons/'.$category_icon);
				}

				$text .= "<tr>
				<td style='width:5%; text-align:center' class='forumheader3'>{$category_id}</td>
				<td style='width:5%; text-align:center' class='forumheader3'><img src='{$icon}' alt='' style='vertical-align:middle' /></td>
				<td style='width:70%' class='forumheader3'>{$category_name}</td>
				<td style='width:20%; text-align:center' class='forumheader3'>
				<a href='".e_SELF."?cat.edit.{$category_id}'>".ADMIN_EDIT_ICON."</a>
				<input type='image' title='".LAN_DELETE."' name='delete[category_{$category_id}]' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(NWSLAN_37." [ID: {$category_id} ]")."') \"/>
				</td>
				</tr>\n";
			}
			$text .= "</table></form>";
		}
		else
		{
			$text .= "<div style='text-align:center'><div style='vertical-align:middle'>".NWSLAN_10."</div>";
		}
		$text .= "</div>";
		$ns->tablerender(NWSLAN_51, $text);
	}

	function show_news_prefs() 
	{
		global $sql, $rs, $ns, $pref;

		$text = "<div style='text-align:center'>
		".$rs->form_open('post', e_SELF.'?pref', 'dataform')."
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_86."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='news_cats' value='1' ".($pref['news_cats'] == 1 ? " checked='checked'" : '')." />
		</td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_87."</span></td>
		<td class='forumheader3' style='width:40%'>
		<select class='tbox' name='nbr_cols'>";
		for($i = 1; $i < 7; $i++) 
		{	// Up to 6 category columns
			$text .= "<option value='{$i}' ".($pref['nbr_cols'] == $i ? "selected='selected'>" : '').">{$i}</option>";
		}
		$text .= "
		</select></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_88."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input class='tbox' type='text' style='width:30px' name='newsposts' value='".$pref['newsposts']."' />
		</td>
		</tr>";

		//			<tr>
		//			<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_108."</span><br /><i>".NWSLAN_109."</i></td>
		//			<td class='forumheader3' style='width:40%'>
		//			<input type='checkbox' name='subnews_hide_news' value='1' ".($pref['subnews_hide_news'] == 1 ? " checked='checked'" : "")." />
		//			</td>
		//			</tr>";

		// ##### ADDED FOR NEWS ARCHIVE ---------------------------------------
		// the possible archive values are from "0" to "< $pref['newsposts']"
		// this should really be made as an onchange event on the selectbox for $pref['newsposts'] ...
		$text .= "
		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_115."</span><br />
		<span class='defaulttext'><i>".NWSLAN_116."</i></span>
		</td>
		<td class='forumheader3' style='width:40%'>
		<select class='tbox' name='newsposts_archive'>";
		for($i = 0; $i < $pref['newsposts']; $i++) 
		{
			$text .= ($i == $pref['newsposts_archive'] ? "<option value='".$i."' selected='selected'>".$i."</option>" : " <option value='".$i."'>".$i."</option>");
		}
		$text .= "</select></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_117."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input class='tbox' type='text' style='width:150px' name='newsposts_archive_title' value='".$pref['newsposts_archive_title']."' />
		</td>
		</tr>
		";
		// ##### END --------------------------------------------------------------------------------------

		require_once(e_HANDLER.'userclass_class.php');
		$text .= " <tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_106."</span></td>
		<td class='forumheader3' style='width:40%'>
		".r_userclass('subnews_class', $pref['subnews_class'],'off','nobody,public,guest,member,admin,classes'). "</td></tr>";

		$text .= "
		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_107."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='subnews_htmlarea' value='1' ".($pref['subnews_htmlarea'] == 1 ? " checked='checked'" : '')." />
		</td>
		</tr>";

		$text .= "
		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_100."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='subnews_attach' value='1' ".($pref['subnews_attach'] == 1 ? " checked='checked'" : '')." />
		</td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_101."</span></td>
		<td class='forumheader3' style='width:40%'>
		<input class='tbox' type='text' style='width:50px' name='subnews_resize' value='".$pref['subnews_resize']."' />
		<span class='smalltext'>".NWSLAN_102."</span></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_111."</span><br /><i>".NWSLAN_112."</i></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='news_newdateheader' value='1' ".($pref['news_newdateheader'] == 1 ? " checked='checked'" : '')." />
		</td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_113."</span><br /><i>".NWSLAN_114."</i></td>
		<td class='forumheader3' style='width:40%'>
		<input type='checkbox' name='news_unstemplate' value='1' ".($pref['news_unstemplate'] == 1 ? " checked='checked'" : '')." />
		</td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:60%'><span class='defaulttext'>".NWSLAN_120."</span><br /></td>
		<td class='forumheader3' style='width:40%'>
		<textarea name='news_subheader' style='width:95%;' rows='6' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);' class='tbox'>".stripcslashes($pref['news_subheader'])." </textarea><br />" . display_help('helpb', 2) . "
		</td>
		</tr>

		<tr><td colspan='2' style='text-align:center' class='forumheader'>";
		$text .= "<input class='button' type='submit' name='save_prefs' value='".NWSLAN_89."' /></td></tr>";

		$text .= "</table>
		".$rs->form_close()."
		</div>";
		$ns->tablerender(NWSLAN_90, $text);
	}

	function show_maintenance() 
	{
		global $sql, $rs, $ns, $pref;

		$text = "<div style='text-align:center;'>
		".$rs->form_open('post', e_SELF.'?maint', 'dataform')."
		<table class='fborder' style='".ADMIN_WIDTH."'>
		<tr><td class='forumheader3'>".LAN_NEWS_51."</td><td style='text-align:center;' class='forumheader3'>";
		$text .= "<input class='button' type='submit' name='news_comments_recalc' value='".LAN_NEWS_52."' /></td></tr>";
		$text .= "</table>
		".$rs->form_close()."
		</div>";
		$ns->tablerender(LAN_NEWS_54, $text);
	}

	function submitted_news($sub_action, $id) 
	{
		global $rs, $ns, $tp;
		$sql2 = new db;
		$text = "<div style='text-align: center'>";
		if ($category_total = $sql2->db_Select('submitnews', '*', "submitnews_id !='' ORDER BY submitnews_id DESC")) 
		{
			$text .= "
			<table class='fborder' style='".ADMIN_WIDTH."'>
				<tr>
					<td style='width:5%' class='fcaption'>ID</td>
					<td style='width:70%' class='fcaption'>".NWSLAN_57."</td>
					<td style='width:25%; text-align:center' class='fcaption'>".LAN_OPTIONS."</td>
				</tr>";
			while ($row = $sql2->db_Fetch()) 
			{
				extract($row);
				if (substr($submitnews_item,-7,7) == '[/html]') $submitnews_item = substr($submitnews_item,0,-7);
				if (substr($submitnews_item,0,6) == '[html]') $submitnews_item = substr($submitnews_item,6);
				$submitnews_item = $tp->dataFilter($submitnews_item);		// Remove any nasties
				$submitnews_title = $tp->dataFilter($submitnews_title);
				$text .= "
				<tr>
					<td style='width:5%; text-align:center; vertical-align:top' class='forumheader3'>$submitnews_id</td>
					<td style='width:70%' class='forumheader3'>";
				$text .= ($submitnews_auth == 0)? '<b>'.$tp->toHTML($submitnews_title,FALSE,'emotes_off, no_make_clickable').'</b>': $tp->toHTML($submitnews_title,FALSE,'emotes_off, no_make_clickable');
				$text .= ' [ '.NWSLAN_104.' '.$submitnews_name.' '.NWSLAN_108.' '.date('D dS M y, g:ia', $submitnews_datestamp).']<br />'.$tp->toHTML($submitnews_item).
				"</td><td style='width:25%; text-align:right; vertical-align:top' class='forumheader3'>";
				$buttext = ($submitnews_auth == 0)? NWSLAN_58 :	NWSLAN_103;
				$text .= $rs->form_open('post', e_SELF.'?sn', 'myform__{$submitnews_id}', '', '', " onsubmit=\"return jsconfirm('".$tp->toJS(NWSLAN_38." [ID: {$submitnews_id} ]")."')\"   ")
				."<div>".$rs->form_button('button', 'category_edit_'.$submitnews_id, $buttext, "onclick=\"document.location='".e_SELF."?create.sn.{$submitnews_id}'\"")."
				".$rs->form_button('submit', 'delete[sn_'.$submitnews_id.']', LAN_DELETE)."
				</div>".$rs->form_close()."
				</td>
				</tr>\n";
			}
			$text .= "</table>";
		}
		else 
		{
			$text .= "<div style='text-align:center'>".NWSLAN_59."</div>";
		}
		$text .= "</div>";
		$ns->tablerender(NWSLAN_47, $text);
	}
} // End of class newspost

function newspost_adminmenu() 
{
	global $newspost;
	global $action;
	$newspost->show_options($action);
}
?>