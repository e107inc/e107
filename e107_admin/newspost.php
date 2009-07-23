<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News Administration
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/newspost.php,v $
 * $Revision: 1.46 $
 * $Date: 2009-07-23 08:01:48 $
 * $Author: secretr $
*/
require_once("../class2.php");

if (!getperms("H"))
{
	header("Location:".e_BASE."index.php");
	exit;
}


// -------- Presets. ------------  // always load before auth.php
require_once(e_HANDLER."preset_class.php");
$pst = new e_preset();
$pst->form = "core-newspost-create-form"; // form id of the form that will have it's values saved.
$pst->page = "newspost.php?create"; // display preset options on which page(s).
$pst->id = "admin_newspost";
// ------------------------------
// done in class2: require_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_admin.php"); // maybe this should be put in class2.php when 'admin' is detected.
$newspost = new admin_newspost(e_QUERY, $pst);
$gen = new convert();

//Handle Ajax Calls
if($newspost->ajax_observer()) exit;

function headerjs()
{
  	global $newspost;

	require_once(e_HANDLER.'js_helper.php');

    $ret .= "<script type='text/javascript'>

    function UpdateForm(id)
	{
        new e107Ajax.Updater('filterValue', '".e_SELF."?searchValue', {
            method: 'post',
			evalScripts: true,
            parameters: {filtertype: id}
		});
   }

   </script>";


	$ret .= "
		<script type='text/javascript'>
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			/**
			 * OnLoad Init Control
			 */
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}

			//fix form action if needed
			document.observe('dom:loaded', function() {
				if($('core-newspost-create-form')) {
					$('core-newspost-create-form').observe('submit', function(event) {
						var form = event.element();
						action = form.readAttribute('action') + document.location.hash;
						//if($('create-edit-stay-1') && $('create-edit-stay-1').checked)
							form.writeAttribute('action', action);
					});
				}
			});
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>

	";

	if($newspost->getAction() == 'cat')
	{
		$ret .= "
		<script type='text/javascript'>
			//Click observer
            document.observe('dom:loaded', function(){
            	\$\$('a.action[id^=core-news-catedit-]').each(function(element) {
					element.observe('click', function(event) {
						event.stop();
						var el = event.findElement('a');
						$('core-newspost-cat-create-form').fillForm(\$\$('body')[0], { handler: el.readAttribute('href') });
					});
				});
            });

    	</script>
		";
	}
	elseif ($newspost->getAction() == 'pref')
	{
		$ret .= "
			<script type='text/javascript'>
				document.observe('dom:loaded', function(){
					\$('newsposts').observe('change', function(event) { 
						new e107Ajax.Updater(
							'newsposts-archive-cont',
							'".e_SELF."?pref_archnum.' + (event.element().selectedIndex + 1) + '.' + event.element().readAttribute('tabindex'),
							{ overlayElement: 'newsposts-archive-cont' }
						);
					});
				});
			</script>
			";
	}
	$ret .= $newspost->_cal->load_files();

   	return $ret;
}
$e_sub_cat = 'news';

require_once('auth.php');
require_once (e_HANDLER.'message_handler.php');

/*
 * Observe for delete action
 */
$newspost->observer();

/*
 * Show requested page
 */
$newspost->show_page();



/* OLD JS? Can't find references to this func
echo "
<script type=\"text/javascript\">
function fclear() {
	document.getElementById('core-newspost-create-form').data.value = \"\";
	document.getElementById('core-newspost-create-form').news_extended.value = \"\";
}
</script>\n";
*/

require_once("footer.php");
exit;





class admin_newspost
{
	var $_request = array();
	var $_cal = array();
	var $_pst;
	var $_fields;
	var $_sort_order;
	var $_sort_link;
	var $fieldpref;

	function admin_newspost($qry, $pstobj)
	{
		global $user_pref;
		$this->parseRequest($qry);

		require_once(e_HANDLER."calendar/calendar_class.php");
		$this->_cal = new DHTML_Calendar(true);

		$this->_pst = &$pstobj;

		$this->fieldpref = $user_pref['admin_news_columns'];

		$this->_fields = array(
				"checkboxes"	   	=> array("title" => "", "forced"=> TRUE, "width" => "3%", "thclass" => "center first", "url" => ""),
				"news_id"			=> array("title" => LAN_NEWS_45, "type"=>"number", "width" => "5%", "thclass" => "center", "url" => e_SELF."?main.news_id.".$this->_sort_link.".".$this->getFrom()),
 				"news_title"		=> array("title" => NWSLAN_40, "type"=>"text", "width" => "30%", "thclass" => "", "url" => e_SELF."?main.news_title.".$this->_sort_link.".".$this->getFrom()),
    			"news_author"		=> array("title" => LAN_NEWS_50, "type"=>"user", "width" => "10%", "thclass" => "", "url" => ""),
				"news_datestamp"	=> array("title" => LAN_NEWS_32, "type"=>"datestamp", "width" => "15%", "thclass" => "", "url" => ""),
                "news_category"		=> array("title" => NWSLAN_6, "type"=>"dropdown", "width" => "auto", "thclass" => "", "url" => ""),
  				"news_class"		=> array("title" => NWSLAN_22, "type"=>"userclass", "width" => "auto", "thclass" => "", "url" => ""),
				"news_render_type"	=> array("title" => LAN_NEWS_49, "type"=>"dropdown", "width" => "auto", "thclass" => "center", "url" => ""),
			   	"news_thumbnail"	=> array("title" => LAN_NEWS_22, "width" => "auto", "thclass" => "", "url" => ""),
		  		"news_sticky"		=> array("title" => LAN_NEWS_28, "type"=>"boolean", "width" => "auto", "thclass" => "", "url" => ""),
                "news_allow_comments" => array("title" => NWSLAN_15, "type"=>"boolean", "width" => "auto", "thclass" => "", "url" => ""),
                "news_comment_total" => array("title" => LAN_NEWS_60, "type"=>"number", "width" => "auto", "thclass" => "", "url" => ""),
				"options"			=> array("title" => LAN_OPTIONS, "width" => "10%", "thclass" => "center last", "url" => "", 'forced'=>TRUE)

		);

	}

	function parseRequest($qry)
	{
		$tmp = explode(".", $qry);
		$action = varsettrue($tmp[0], 'main');
		$sub_action = varset($tmp[1], '');
		$id = isset($tmp[2]) && is_numeric($tmp[2]) ? intval($tmp[2]) : 0;
		$this->_sort_order = isset($tmp[2]) && !is_numeric($tmp[2]) ? $tmp[2] : 'desc';
		$from = intval(varset($tmp[3],0));
		unset($tmp);

        if ($this->_sort_order != 'asc') $this->_sort_order = 'desc';
		$this->_sort_link = ($this->_sort_order) == 'asc' ? 'desc' : 'asc';

		$this->_request = array($action, $sub_action, $id, $sort_order, $from);
	}

	function getAction()
	{
		return $this->_request[0];
	}

	function getSubAction()
	{
		return $this->_request[1];
	}

	function getId()
	{
		return $this->_request[2];
	}

	function getSortOrder()
	{
		return $this->_request[3];
	}

	function getFrom()
	{
		return $this->_request[4];
	}

	function clear_cache()
	{
		$e107 = &e107::getInstance();
		$e107->ecache->clear("news.php");
		$e107->ecache->clear("othernews");
		$e107->ecache->clear("othernews2");
	}

	function ajax_observer()
	{
		$method = 'ajax_exec_'.$this->getAction();

		if(e_AJAX_REQUEST && method_exists($this, $method))
		{
			$this->$method();
			return true;
		}
		return false;
	}

	function observer()
	{
		//Required on create & savepreset action triggers
		if(isset($_POST['news_userclass']) && is_array($_POST['news_userclass']))
		{
			$_POST['news_class'] = implode(",", $_POST['news_userclass']);
			unset($_POST['news_userclass']);
		}

		if(isset($_POST['delete']) && is_array($_POST['delete']))
		{
			$this->_observe_delete();
		}
		elseif(isset($_POST['submit_news']))
		{
			$this->_observe_submit_item($this->getSubAction(), $this->getId());
		}
		elseif(isset($_POST['create_category']))
		{
			$this->_observe_create_category();
		}
		elseif(isset($_POST['update_category']))
		{
			$this->_observe_update_category();
		}
		elseif(isset($_POST['save_prefs']))
		{
			$this->_observe_save_prefs();
		}
		elseif(isset($_POST['submitupload']))
		{
			$this->_observe_upload();
		}
		elseif(isset($_POST['news_comments_recalc']))
		{
			$this->_observe_newsCommentsRecalc();
		}
		elseif(isset($_POST['submit-e-columns']))
		{
        	$this->_observe_saveColumns();
		}
	}

	function show_page()
	{
		switch ($this->getAction()) {
			case 'savepreset':
			case 'clr_preset':
				$this->_pst->save_preset('news_datestamp', false); // save and render result using unique name. Don't save item datestamp
				$_POST = array();
				$this->parseRequest('');
				$this->show_existing_items();
			break;
			case 'create':
				$this->_pst->read_preset('admin_newspost');  //only works here because $_POST is used.
				$this->show_create_item();
			break;

			case 'cat':
				$this->show_categories();
			break;

			case 'sn':
				$this->show_submitted_news();
			break;

			case 'pref':
				$this->show_news_prefs();
			break;

			case 'maint' :
				$this->showMaintenance();
				break;

			default:
				$this->show_existing_items();
			break;
		}
	}

	function _observe_delete()
	{
		global $admin_log;

		$tmp = array_keys($_POST['delete']);
		list($delete, $del_id) = explode("_", $tmp[0]);
		$del_id = intval($del_id);

		if(!$del_id) return false;

		$e107 = &e107::getInstance();

		switch ($delete) {
			case 'main':
				if ($e107->sql->db_Count('news','(*)',"WHERE news_id={$del_id}"))
				{
					$e107->e_event->trigger("newsdel", $del_id);
					if($e107->sql->db_Delete("news", "news_id={$del_id}"))
					{
						$admin_log->log_event('NEWS_01',$del_id,E_LOG_INFORMATIVE,'');
						$this->show_message(NWSLAN_31." #".$del_id." ".NWSLAN_32, E_MESSAGE_SUCCESS);
						$this->clear_cache();

						$data = array('method'=>'delete', 'table'=>'news', 'id'=>$del_id, 'plugin'=>'news', 'function'=>'delete');
						$this->show_message($e107->e_event->triggerHook($data), E_MESSAGE_WARNING);

						admin_purge_related("news", $del_id);
					}
				}
			break;

			case 'category':
					if ($e107->sql->db_Delete("news_category", "category_id={$del_id}"))
					{
						$admin_log->log_event('NEWS_02',$del_id,E_LOG_INFORMATIVE,'');
						$this->show_message(NWSLAN_33." #".$del_id." ".NWSLAN_32, E_MESSAGE_SUCCESS);
						$this->clear_cache();
					}
			break;

			case 'sn':
					if ($e107->sql->db_Delete("submitnews", "submitnews_id={$del_id}"))
					{
						$admin_log->log_event('NEWS_03',$del_id,E_LOG_INFORMATIVE,'');
						$this->show_message(NWSLAN_34." #".$del_id." ".NWSLAN_32);
						$this->clear_cache();
					}
			break;

			default:
				return  false;
		}

		return true;
	}

	function _observe_submit_item($sub_action, $id)
	{
		// ##### Format and submit item to DB
		global $admin_log;

		$e107 = &e107::getInstance();

		require_once(e_HANDLER."news_class.php");
		$ix = new news;

		if($_POST['news_start'])
		{
			$tmp = explode("/", $_POST['news_start']);
			$_POST['news_start'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$tmp = explode("/", $_POST['news_end']);
			$_POST['news_end'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_end'] = 0;
		}

		$matches = array();
		if(preg_match('#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#', $_POST['news_datestamp'], $matches))
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

		if ($id && $sub_action != "sn" && $sub_action != "upload")
		{
			$_POST['news_id'] = $id;
		}
		else
		{
			$e107->sql->db_Update('submitnews', "submitnews_auth=1 WHERE submitnews_id ={$id}");
			$admin_log->log_event('NEWS_07',$id,E_LOG_INFORMATIVE,'');
		}
		if (!$_POST['cat_id'])
		{
			$_POST['cat_id'] = 1;
		}

		if(isset($_POST['news_thumbnail']))
		{
			$_POST['news_thumbnail'] = urldecode(basename($_POST['news_thumbnail']));
		}

        $tmp = explode(chr(35), $_POST['news_author']);
        $_POST['news_author'] = $tmp[0];

        $ret = $ix->submit_item($_POST, true);
        $this->clear_cache();

        if(isset($_POST['create_edit_stay']) && !empty($_POST['create_edit_stay']))
        {
			if($this->getSubAction() != 'edit')
			{
	        	session_write_close();
				$rurl = e_SELF.(varsettrue($ret['id']) ? "?create.edit.".$ret['id'] : '');
				header('Location:'.($rurl ? $rurl : e_SELF));
				exit;
			}
        }
        else
        {
			session_write_close();
			header('Location:'.e_SELF);
			exit;
        }
	}

	function _observe_create_category()
	{
		global $admin_log;
		if ($_POST['category_name'])
		{
			$e107 = &e107::getInstance();
			if (empty($_POST['category_button']))
			{
				$handle = opendir(e_IMAGE."icons");
				while ($file = readdir($handle))
				{
					if ($file != "." && $file != ".." && $file != "/" && $file != "null.txt" && $file != "CVS") {
						$iconlist[] = $file;
					}
				}
				closedir($handle);
				$_POST['category_button'] = $iconlist[0];
			}
			else
			{
				$_POST['category_button'] = $e107->tp->toDB($_POST['category_button']);
			}
			$_POST['category_name'] = $e107->tp->toDB($_POST['category_name']);
			$e107->sql->db_Insert('news_category', "'0', '{$_POST['category_name']}', '{$_POST['category_button']}'");
			$admin_log->log_event('NEWS_04',$_POST['category_name'].', '.$_POST['category_button'],E_LOG_INFORMATIVE,'');
			$this->show_message(NWSLAN_35, E_MESSAGE_SUCCESS);
			$this->clear_cache();
		}
	}
	function _observe_update_category()
	{
		global $admin_log;
		if ($_POST['category_name'])
		{
			$e107 = &e107::getInstance();
			$category_button = $e107->tp->toDB(($_POST['category_button'] ? $_POST['category_button'] : ""));
			$_POST['category_name'] = $e107->tp->toDB($_POST['category_name']);
			$e107->sql->db_Update("news_category", "category_name='".$_POST['category_name']."', category_icon='".$category_button."' WHERE category_id=".intval($_POST['category_id']));
			$admin_log->log_event('NEWS_05',intval($_POST['category_id']).':'.$_POST['category_name'].', '.$category_button,E_LOG_INFORMATIVE,'');
			$this->show_message(NWSLAN_36, E_MESSAGE_SUCCESS);
			$this->clear_cache();
		}
	}

	function _observe_save_prefs()
	{
		global $pref, $admin_log;

		$e107 = e107::getInstance();

		$temp = array();
		$temp['newsposts'] 				= intval($_POST['newsposts']);
	   	$temp['newsposts_archive'] 		= intval($_POST['newsposts_archive']);
		$temp['newsposts_archive_title'] = $e107->tp->toDB($_POST['newsposts_archive_title']);
		$temp['news_cats'] 				= intval($_POST['news_cats']);
		$temp['nbr_cols'] 				= intval($_POST['nbr_cols']);
		$temp['subnews_attach'] 		= intval($_POST['subnews_attach']);
		$temp['subnews_resize'] 		= intval($_POST['subnews_resize']);
		$temp['subnews_class'] 			= intval($_POST['subnews_class']);
		$temp['subnews_htmlarea'] 		= intval($_POST['subnews_htmlarea']);
		$temp['news_subheader'] 		= $e107->tp->toDB($_POST['news_subheader']);
		$temp['news_newdateheader'] 	= intval($_POST['news_newdateheader']);
		$temp['news_unstemplate'] 		= intval($_POST['news_unstemplate']);
		$temp['news_editauthor']		= intval($_POST['news_editauthor']);

		if ($admin_log->logArrayDiffs($temp, $pref, 'NEWS_06'))
		{
			save_prefs();		// Only save if changes
			$this->clear_cache();
			$this->show_message(NWSLAN_119, E_MESSAGE_SUCCESS);
		}
		else
		{
			$this->show_message(LAN_NEWS_47);
		}
	}

	function _observe_upload()
	{
		//$pref['upload_storagetype'] = "1";
		require_once(e_HANDLER."upload_handler.php");

		$uploaded = file_upload(e_NEWSIMAGE);

		foreach($_POST['uploadtype'] as $key=>$uploadtype)
		{
			if($uploadtype == "thumb")
			{
				rename(e_NEWSIMAGE.$uploaded[$key]['name'],e_NEWSIMAGE."thumb_".$uploaded[$key]['name']);
			}

			if($uploadtype == "file")
			{
				rename(e_NEWSIMAGE.$uploaded[$key]['name'],e_DOWNLOAD.$uploaded[$key]['name']);
			}

			if ($uploadtype == "resize" && $_POST['resize_value'])
			{
				require_once(e_HANDLER."resize_handler.php");
				resize_image(e_NEWSIMAGE.$uploaded[$key]['name'], e_NEWSIMAGE.$uploaded[$key]['name'], $_POST['resize_value'], "copy");
			}
		}
	}


	function _observe_saveColumns()
	{
		global $user_pref,$admin_log;
		$user_pref['admin_news_columns'] = $_POST['e-columns'];
		save_prefs('user');
		$this->fieldpref = $user_pref['admin_news_columns'];
	}

	function show_existing_items()
	{
		global $user_pref,$gen;

		require_once(e_HANDLER."form_handler.php");
		$frm = new e_form(true); //enable inner tabindex counter

	   	// Effectively toggle setting for headings


		$amount = 10;//TODO - pref

		if(!is_array($user_pref['admin_news_columns']))
		{
        	$user_pref['admin_news_columns'] = array("news_id","news_title","news_author","news_render_type");
		}


		$field_columns = $this->_fields;

		$e107 = &e107::getInstance();

		// Grab news Category Names;
			$e107->sql->db_Select('news_category', '*');
	        $newscatarray = $e107->sql->db_getList();
			$news_category = array();

	        foreach($newscatarray as $val)
			{
	        	$news_category[$val['category_id']] = $val['category_name'];
			}


        // ------ Search Filter ------

        $text .= "
			<form method='post' action='".e_SELF."'>
			<div class='center' style='padding:20px'>
			<table style='width:auto' cellspacing='2'>
			<tr>
            <td class='left'>".
				$frm->admin_button('dupfield', "Add Filter", 'action', '', array('other' => 'onclick="duplicateHTML(\'filterline\',\'srch_container\');"'))
			."</td>
			<td>
				<div id='srch_container' class='nowrap'><span id='filterline' >".$frm->filterType($field_columns)."<span id='filterValue'>".$frm->filterValue()."</span></span></div>
		  	</td>
			<td>".
				$frm->admin_button('searchsubmit', NWSLAN_63, 'search')."
			</td>

		  </tr>
		  </table>
			</div></form>
		";

        // --------------------------------------------


		if (varsettrue($_POST['searchquery']))
		{
			$query = "news_title REGEXP('".$_POST['searchquery']."') OR news_body REGEXP('".$_POST['searchquery']."') OR news_extended REGEXP('".$_POST['searchquery']."') ORDER BY news_datestamp DESC";
		}
		else
		{
			$query = "ORDER BY ".($this->getSubAction() ? $this->getSubAction() : "news_datestamp")." ".strtoupper($this->_sort_order)."  LIMIT ".$this->getFrom().", {$amount}";
		}

		if ($e107->sql->db_Select('news', '*', $query, ($_POST['searchquery'] ? 0 : "nowhere")))
		{
			$newsarray = $e107->sql->db_getList();

			$text .= "
				<form action='".e_SELF."' id='newsform' method='post'>
					<fieldset id='core-newspost-list'>
						<legend class='e-hideme'>".NWSLAN_4."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>
							".$frm->colGroup($this->_fields,$this->fieldpref).
                             $frm->thead($this->_fields,$this->fieldpref,"main.[FIELD].[ASC].[FROM]")."
							<tbody>";

			$ren_type = array("default","title","other-news","other-news 2");

			foreach($newsarray as $field=>$row)
			{
			   	$author = get_user_data($row['news_author']);
				$thumbnail = ($row['news_thumbnail'] && is_readable(e_NEWSIMAGE.$row['news_thumbnail'])) ? "<img src='".e_NEWSIMAGE.$row['news_thumbnail']."' alt='' />" : "";
                $sticky = ($row['news_sticky'] == 1) ? ADMIN_TRUE_ICON : "&nbsp;";
                $comments = ($row['news_allow_comments'] == 1) ? ADMIN_TRUE_ICON : "&nbsp;";

				$text .= "<tr>\n";

				// Below must be in the same order as the field_columns above.

		        $rowid = "news_selected[".$row["news_id"]."]";
                $text .= "<td class='center'>".$frm->checkbox($rowid, $row['news_id'])."</td>\n";

				$text .= (in_array("news_id",$user_pref['admin_news_columns'])) ? "<td class='center'>".$row['news_id']."</td>\n" : "";
                $text .= (in_array("news_title",$user_pref['admin_news_columns'])) ? "<td><a href='".$e107->url->getUrl('core:news', 'main', "action=item&value1={$row['news_id']}&value2={$row['news_category']}")."'>".($row['news_title'] ? $e107->tp->toHTML($row['news_title'], false,"TITLE") : "[".NWSLAN_42."]")."</a></td> \n" : "";
                $text .= (in_array("news_author",$user_pref['admin_news_columns'])) ? "<td>".$author['user_name']."</td>\n" : "";
				$text .= (in_array("news_datestamp",$user_pref['admin_news_columns'])) ? "<td>".$gen->convert_date($row['news_datestamp'],'short')." </td>\n" : "";
				$text .= (in_array("news_category",$user_pref['admin_news_columns'])) ? "<td>".$news_category[$row['news_category']]." </td>\n" : "";
				$text .= (in_array("news_class",$user_pref['admin_news_columns'])) ? "<td class='nowrap'>".r_userclass_name($row['news_class'])." </td>\n" : "";
                $text .= (in_array("news_render_type",$user_pref['admin_news_columns'])) ? "<td class='center nowrap'>".$ren_type[$row['news_render_type']]."</td>\n" : "";
				$text .= (in_array("news_thumbnail",$user_pref['admin_news_columns'])) ? "<td class='center nowrap'>".$thumbnail."</td>\n" : "";
                $text .= (in_array("news_sticky",$user_pref['admin_news_columns'])) ? "<td class='center'>".$sticky."</td>\n" : "";
                $text .= (in_array("news_allow_comments",$user_pref['admin_news_columns'])) ? "<td class='center'>".$comments."</td>\n" : "";
                $text .= (in_array("news_comment_total",$user_pref['admin_news_columns'])) ? "<td class='center'>".$row['news_comment_total']."</td>\n" : "";

				$text .= "
					<td class='center'>
						<a class='action' href='".e_SELF."?create.edit.{$row['news_id']}' tabindex='".$frm->getNext()."'>".ADMIN_EDIT_ICON."</a>
						".$frm->submit_image("delete[main_{$row['news_id']}]", LAN_DELETE, 'delete', NWSLAN_39." [ID: {$row['news_id']}]")."
		  			</td>
					</tr>
				";
			}

			$text .= "
							</tbody>
						</table>
					</fieldset>
				</form>
			";
		}
		else
		{
			$text .= "<div class='center'>".isset($_POST['searchquery']) ? sprintf(NWSLAN_121, '<em>&quot;'.$_POST['searchquery'])."&quot;</em> <a href='".e_SELF."'>&laquo; ".LAN_BACK."</a>" : NWSLAN_43."</div>";
		}

		$newsposts = $e107->sql->db_Count('news');

		if (!varset($_POST['searchquery']))
		{
			$parms = $newsposts.",".$amount.",".$this->getFrom().",".e_SELF."?".$this->getAction().'.'.($this->getSubAction() ? $this->getSubAction() : 0).'.'.$this->_sort_order.".[FROM]";
			$nextprev = $e107->tp->parseTemplate("{NEXTPREV={$parms}}");
			if ($nextprev) $text .= "<div class='nextprev-bar'>".$nextprev."</div>";

		}

		$emessage = &eMessage::getInstance();
		$e107->ns->tablerender(NWSLAN_4, $emessage->render().$text);
	}


	function _pre_create()
	{

		if($this->getSubAction() == "edit" && !$_POST['preview'] && !$_POST['submit_news'])
		{
			$e107 = &e107::getInstance();
			if($e107->sql->db_Select("news", "*", "news_id='".$this->getId()."'"))
			{
				$row = $e107->sql->db_Fetch();

				$_POST['news_title'] = $row['news_title'];
				$_POST['data'] = $row['news_body'];
				$_POST['news_author'] = $row['news_author'];
				$_POST['news_extended'] = $row['news_extended'];
				$_POST['news_allow_comments'] = $row['news_allow_comments'];
				$_POST['news_class'] = $row['news_class'];
				$_POST['news_summary'] = $row['news_summary'];
				$_POST['news_sticky'] = $row['news_sticky'];
				$_POST['news_datestamp'] = ($_POST['news_datestamp']) ? $_POST['news_datestamp'] : $row['news_datestamp'];

				$_POST['cat_id'] = $row['news_category'];
				$_POST['news_start'] = $row['news_start'];
				$_POST['news_end'] = $row['news_end'];
				$_POST['comment_total'] = $e107->sql->db_Count("comments", "(*)", " WHERE comment_item_id={$row['news_id']} AND comment_type='0'");
				$_POST['news_rendertype'] = $row['news_render_type'];
				$_POST['news_thumbnail'] = $row['news_thumbnail'];
			}
		}
	}

	function show_create_item()
	{
		global $pref;

		$this->_pre_create();

		require_once(e_HANDLER."userclass_class.php");
		require_once(e_HANDLER."form_handler.php");
		$frm = new e_form(true); //enable inner tabindex counter

		$text = '';
		if (isset($_POST['preview']))
		{
			$text = $this->preview_item($this->getId());
		}


		$sub_action = $this->getSubAction();
		$id = $this->getSubAction() != 'sn' && $this->getSubAction() != 'upload' ? $this->getId() : 0;

		$e107 = &e107::getInstance();

		if ($sub_action == "sn" && !varset($_POST['preview']))
		{
			if ($e107->sql->db_Select("submitnews", "*", "submitnews_id=".$this->getId(), TRUE))
			{
				//list($id, $submitnews_name, $submitnews_email, $_POST['news_title'], $submitnews_category, $_POST['data'], $submitnews_datestamp, $submitnews_ip, $submitnews_auth, $submitnews_file) = $sql->db_Fetch();
				$row = $e107->sql->db_Fetch();
				$_POST['news_title'] = $row['submitnews_title'];
				$_POST['data'] = $row['submitnews_item'];
				$_POST['cat_id'] = $row['submitnews_category'];

				if (defsettrue('e_WYSIWYG'))
				{
				  if (substr($_POST['data'],-7,7) == '[/html]') $_POST['data'] = substr($_POST['data'],0,-7);
				  if (substr($_POST['data'],0,6) == '[html]') $_POST['data'] = substr($_POST['data'],6);
					$_POST['data'] .= "<br /><b>".NWSLAN_49." {$row['submitnews_name']}</b>";
					$_POST['data'] .= ($row['submitnews_file'])? "<br /><br /><img src='{e_NEWSIMAGE}{$row['submitnews_file']}' class='f-right' />": '';
				}
				else
				{
					$_POST['data'] .= "\n[[b]".NWSLAN_49." {$row['submitnews_name']}[/b]]";
					$_POST['data'] .= ($row['submitnews_file'])?"\n\n[img]{e_NEWSIMAGE}{$row['submitnews_file']}[/img]": "";
				}

			}
		}

		if ($sub_action == "upload" && !varset($_POST['preview']))
		{
			if ($e107->sql->db_Select('upload', '*', "upload_id=".$this->getId())) {
				$row = $e107->sql->db_Fetch();
				$post_author_id = substr($row['upload_poster'], 0, strpos($row['upload_poster'], "."));
				$post_author_name = substr($row['upload_poster'], (strpos($row['upload_poster'], ".")+1));
				$match = array();
				//XXX DB UPLOADS STILL SUPPORTED?
				$upload_file = "pub_" . (preg_match('#Binary\s(.*?)\/#', $row['upload_file'], $match) ? $match[1] : $row['upload_file']);
				$_POST['news_title'] = LAN_UPLOAD.": ".$row['upload_name'];
				$_POST['data'] = $row['upload_description']."\n[b]".NWSLAN_49." [link=".$e107->url->getUrl('core:user', 'main', 'id='.$post_author_id)."]".$post_author_name."[/link][/b]\n\n[file=request.php?".$upload_file."]{$row['upload_name']}[/file]\n";
			}
		}

		$text .= "
		<div class='admintabs' id='tab-container'>
			<ul class='e-tabs e-hideme' id='core-emote-tabs'>
				<li id='tab-general'><a href='#core-newspost-create'>".LAN_NEWS_52."</a></li>
				<li id='tab-advanced'><a href='#core-newspost-edit-options'>".LAN_NEWS_53."</a></li>
			</ul>
			<form method='post' action='".e_SELF."?".e_QUERY."' id='core-newspost-create-form' ".(FILE_UPLOADS ? "enctype='multipart/form-data'" : "")." >
				<fieldset id='core-newspost-create'>
					<legend>".LAN_NEWS_52."</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".NWSLAN_6.": </td>
								<td class='control'>
		";

		if (!$e107->sql->db_Select("news_category"))
		{
			$text .= NWSLAN_10;
		}
		else
		{
			$text .= "
									".$frm->select_open('cat_id')."
			";

			while ($row = $e107->sql->db_Fetch())
			{
				$text .= $frm->option($e107->tp->toHTML($row['category_name'], FALSE, "LINKTEXT"), $row['category_id'], varset($_POST['cat_id']) == $row['category_id']);
			}
			$text .= "
									</select>
			";
		}
		$text .= "
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_12.":</td>
								<td class='control'>
									".$frm->text('news_title', $e107->tp->post_toForm($_POST['news_title']))."
								</td>
							</tr>

							<tr>
								<td class='label'>".LAN_NEWS_27.":</td>
								<td class='control'>
									".$frm->text('news_summary', $e107->tp->post_toForm($_POST['news_summary']), 250)."
								</td>
							</tr>
		";


		// -------- News Author ---------------------
        $text .="
							<tr>
								<td class='label'>
									".LAN_NEWS_50.":
								</td>
								<td class='control'>
		";

		if(!getperms('0') && !check_class($pref['news_editauthor']))
		{
			$auth = ($_POST['news_author']) ? intval($_POST['news_author']) : USERID;
			$e107->sql->db_Select("user", "user_name", "user_id={$auth} LIMIT 1");
           	$row = $e107->sql->db_Fetch(MYSQL_ASSOC);
			$text .= "<input type='hidden' name='news_author' value='".$auth.chr(35).$row['user_name']."' />";
			$text .= "<a href='".$e107->url->getUrl('core:user', 'main', 'id='.$_POST['news_author'])."'>".$row['user_name']."</a>";
		}
        else // allow master admin to
		{
			$text .= $frm->select_open('news_author');
			$qry = "SELECT user_id,user_name FROM #user WHERE user_perms = '0' OR user_perms = '0.' OR user_perms REGEXP('(^|,)(H)(,|$)') ";
			if($pref['subnews_class'] && $pref['subnews_class']!= e_UC_GUEST && $pref['subnews_class']!= e_UC_NOBODY)
			{
				if($pref['subnews_class']== e_UC_MEMBER)
				{
					$qry .= " OR user_ban != 1";
				}
				elseif($pref['subnews_class']== e_UC_ADMIN)
				{
	            	$qry .= " OR user_admin = 1";
				}
				else
				{
	            	$qry .= " OR FIND_IN_SET(".intval($pref['subnews_class']).", user_class) ";
				}
			}

	        $e107->sql->db_Select_gen($qry);
	        while($row = $e107->sql->db_Fetch())
	        {
	        	if($_POST['news_author'])
				{
		        	$sel = ($_POST['news_author'] == $row['user_id']);
		        }
				else
				{
		        	$sel = (USERID == $row['user_id']);
				}
				$text .= $frm->option($row['user_name'], $row['user_id'].chr(35).$row['user_name'], $sel);
			}

			$text .= "</select>
			";
		}

		$text .= "
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_13.":<br /></td>
								<td class='control'>";

		$val = (strstr($e107->tp->post_toForm($_POST['data']), "[img]http") ? $e107->tp->post_toForm($_POST['data']) : str_replace("[img]../", "[img]", $e107->tp->post_toForm($_POST['data'])));
        $text .= $frm->bbarea('data', $val, 'news', 'helpb');

		// Extended news form textarea
		// Fixes Firefox issue with hidden wysiwyg textarea.
		// XXX - WYSIWYG is already plugin, this should go
  //		if(defsettrue('e_WYSIWYG')) $ff_expand = "tinyMCE.execCommand('mceResetDesignMode')";
		$val = (strstr($e107->tp->post_toForm($_POST['news_extended']), "[img]http") ? $e107->tp->post_toForm($_POST['news_extended']) : str_replace("[img]../", "[img]", $e107->tp->post_toForm($_POST['news_extended'])));
		$text .= "
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_14.":</td>
								<td class='control'>
									<a href='#news-extended-cont' class='e-expandit' onclick=\"$ff_expand\">".NWSLAN_83."</a>
									<div class='e-hideme' id='news-extended-cont'>
										".$frm->bbarea('news_extended', $val, 'extended', 'helpc')."
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_66.":</td>
								<td class='control'>
									<a href='#news-upload-cont' class='e-expandit'>".NWSLAN_69."</a>
									<div class='e-hideme' id='news-upload-cont'>
		";

		if (!FILE_UPLOADS)
		{
			$text .= "<b>".LAN_UPLOAD_SERVEROFF."</b>";
		}
		else
		{
			if (!is_writable(e_DOWNLOAD))
			{
				$text .= LAN_UPLOAD_777."<b>".str_replace("../","",e_DOWNLOAD)."</b><br /><br />";
			}
			if (!is_writable(e_NEWSIMAGE))
			{
				$text .= LAN_UPLOAD_777."<b>".str_replace("../","",e_NEWSIMAGE)."</b><br /><br />";
			}

			$up_name = array(LAN_NEWS_24, NWSLAN_67, LAN_NEWS_22, NWSLAN_68);
			$up_value = array("resize", "image", "thumb", "file");

			$text .= "
										<div class='field-spacer'>
											".$frm->admin_button('dupfield', LAN_NEWS_26, 'action', '', array('other' => 'onclick="duplicateHTML(\'upline\',\'up_container\');"'))."
										</div>
										<div id='up_container' class='field-spacer'>
											<div id='upline' class='left nowrap'>
												".$frm->file('file_userfile[]')."
												".$frm->select_open('uploadtype[]')."
			";

			for ($i=0; $i<count($up_value); $i++)
			{
				$text .= $frm->option($up_name[$i], $up_value[$i], varset($_POST['uploadtype']) == $up_value[$i]);
			}
			//FIXME - upload shortcode, flexible enough to be used everywhere
			// Note from Cameron: should include iframe and use ajax as to not require a full refresh of the page.

			$text .= "
												</select>
											</div>
										</div>
										<div class='field-spacer'>
											<span class='smalltext'>".LAN_NEWS_25."</span>&nbsp;".$frm->text('resize_value', ($_POST['resize_value'] ? $_POST['resize_value'] : '100'), 4, 'size=3&class=tbox')."&nbsp;px
										</div>
										<div class='field-spacer'>
											".$frm->admin_button('submitupload', NWSLAN_66, 'upload')."
										</div>
			";

		}
		$text .= "
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_67.":</td>
								<td class='control'>
									<a href='#news-images-cont' class='e-expandit'>".LAN_NEWS_23."</a>
									<div class='e-hideme' id='news-images-cont'>
		";

		$parms = "name=news_thumbnail";
		$parms .= "&path=".e_NEWSIMAGE;
		$parms .= "&filter=0";
		$parms .= "&fullpath=1";
		$parms .= "&default=".urlencode(basename($_POST['news_thumbnail']));
		$parms .= "&multiple=FALSE";
		$parms .= "&label=-- ".LAN_NEWS_48." --";
		$parms .= "&subdirs=0";
		$parms .= "&tabindex=".$frm->getNext();
		//$parms .= "&click_target=data";
		//$parms .= "&click_prefix=[img][[e_IMAGE]]newspost_images/";
		//$parms .= "&click_postfix=[/img]";

		$text .= "<div class='field-section'>".$e107->tp->parseTemplate("{IMAGESELECTOR={$parms}&scaction=select}")."</div>";
		$text .= "<div class='field-spacer'>".$e107->tp->parseTemplate("{IMAGESELECTOR={$parms}&scaction=preview}")."</div>";

		$text .= "
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
		";

		//Begin Options block
		$text .= "
				<fieldset id='core-newspost-edit-options'>
					<legend>".LAN_NEWS_53."</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".NWSLAN_15.":</td>
								<td class='control'>
									".$frm->radio('news_allow_comments', 0, $_POST['news_allow_comments'])."".$frm->label(LAN_ENABLED, 'news_allow_comments', 0)."&nbsp;&nbsp;
									".$frm->radio('news_allow_comments', 1, $_POST['news_allow_comments'])."".$frm->label(LAN_DISABLED, 'news_allow_comments', 1)."
									<div class='field-help'>
										".NWSLAN_18."
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_73.":</td>
								<td class='control'>

		";
		$ren_type = array(NWSLAN_75,NWSLAN_76,NWSLAN_77,NWSLAN_77." 2");
		$r_array = array();
		foreach($ren_type as $key=>$value) {
			$r_array[$key] = $value;
		}


		$text .= "
										".$frm->radio_multi('news_rendertype', $r_array, $_POST['news_rendertype'], true)."
										<div class='field-help'>
											".NWSLAN_74."
										</div>
									</td>
								</tr>
								<tr>
									<td class='label'>".NWSLAN_19.":</td>
									<td class='control'>
										<div class='field-spacer'>".NWSLAN_21.":</div>
										<div class='field-spacer'>
		";

		$_startdate = ($_POST['news_start'] > 0) ? date("d/m/Y", $_POST['news_start']) : "";

		$cal_options['showsTime'] = false;
		$cal_options['showOthers'] = false;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = "%d/%m/%Y";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['size'] = "10";
		$cal_attrib['name'] = "news_start";
		$cal_attrib['value'] = $_startdate;
		$cal_attrib['tabindex'] = $frm->getNext();
		$text .= $this->_cal->make_input_field($cal_options, $cal_attrib);

		$text .= " - ";

		$_enddate = ($_POST['news_end'] > 0) ? date("d/m/Y", $_POST['news_end']) : "";

		unset($cal_options);
		unset($cal_attrib);
		$cal_options['showsTime'] = false;
		$cal_options['showOthers'] = false;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = "%d/%m/%Y";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['size'] = "10";
		$cal_attrib['name'] = "news_end";
		$cal_attrib['value'] = $_enddate;
		$cal_attrib['tabindex'] = $frm->getNext();
		$text .= $this->_cal->make_input_field($cal_options, $cal_attrib);

		$text .= "
										</div>
										<div class='field-help'>
											".NWSLAN_72."
										</div>
									</td>
								</tr>
								<tr>
									<td class='label'>".LAN_NEWS_32.":</td>
									<td class='control'>
										<div class='field-spacer'>
		";

		$_update_datestamp = ($_POST['news_datestamp'] > 0 && !strpos($_POST['news_datestamp'],"/")) ? date("d/m/Y H:i:s", $_POST['news_datestamp']) : trim($_POST['news_datestamp']);
		unset($cal_options);
		unset($cal_attrib);
		$cal_options['showsTime'] = true;
		$cal_options['showOthers'] = true;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = "%d/%m/%Y %H:%M:%S";
		$cal_options['timeFormat'] = "24";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['name'] = "news_datestamp";
		$cal_attrib['value'] = $_update_datestamp;
		$text .= $this->_cal->make_input_field($cal_options, $cal_attrib);

		$text .= "
										</div>
										<div class='field-spacer'>
											".$frm->checkbox('update_datestamp', '1', $_POST['update_datestamp']).$frm->label(NWSLAN_105, 'update_datestamp', '1')."
										</div>
										<div class='field-help'>
											".LAN_NEWS_33."
										</div>
									</td>
								</tr>
		";




        // --------------------- News Userclass ---------------------------

		$text .= "
								<tr>
									<td class='label'>".NWSLAN_22.":</td>
									<td class='control'>
										".$frm->uc_checkbox('news_userclass', $_POST['news_class'], 'nobody,public,guest,member,admin,classes,language', 'description=1')."
										<div class='field-help'>
											".NWSLAN_84."
										</div>
									</td>
								</tr>
								<tr>
									<td class='label'>".LAN_NEWS_28.":</td>
									<td class='control'>
										".$frm->checkbox('news_sticky', '1', $_POST['news_sticky']).$frm->label(LAN_NEWS_30, 'news_sticky', '1')."
										<div class='field-help'>
											".LAN_NEWS_29."
										</div>
									</td>
								</tr>
		";

		if($pref['trackbackEnabled']){
			$text .= "
								<tr>
									<td class='label'>".LAN_NEWS_34.":</td>
									<td class='control'>
										<a class='e-pointer' onclick='expandit(this);'>".LAN_NEWS_35."</a>
										<div class='e-hideme'>
											<div class='field-spacer'>
												<span class='smalltext'>".LAN_NEWS_37."</span>
											</div>
											<div class='field-spacer'>
												<textarea class='tbox textarea' name='trackback_urls' style='width:95%' cols='80' rows='5'>".$_POST['trackback_urls']."</textarea>
											</div>
										</div>
									</td>
								</tr>
			";
		}
		//triggerHook
		$data = array('method'=>'form', 'table'=>'news', 'id'=>$id, 'plugin'=>'news', 'function'=>'create_item');
		$hooks = $e107->e_event->triggerHook($data);
		if(!empty($hooks))
		{
			$text .= "
								<tr>
									<td colspan='2' >".LAN_HOOKS." </td>
								</tr>
			";
			foreach($hooks as $hook)
			{
				if(!empty($hook))
				{
					$text .= "
								<tr>
									<td class='label'>".$hook['caption']."</td>
									<td class='control'>".$hook['text']."</td>
								</tr>
					";
				}
			}
		}

		$text .= "
						</tbody>
					</table>
				</fieldset>
				<div class='buttons-bar center'>
					".$frm->admin_button('preview', isset($_POST['preview']) ? NWSLAN_24 : NWSLAN_27 , 'submit')."
					".$frm->admin_button('submit_news', ($id && $sub_action != "sn" && $sub_action != "upload") ? NWSLAN_25 : NWSLAN_26 , 'update')."
					".$frm->checkbox('create_edit_stay', 1, isset($_POST['create_edit_stay'])).$frm->label(LAN_NEWS_54, 'create_edit_stay', 1)."
					<input type='hidden' name='news_id' value='{$id}' />
				</div>
			</form>
		</div>

		";

		$emessage = &eMessage::getInstance();
		$e107->ns->tablerender($this->getSubAction() == 'edit' ? NWSLAN_29a : NWSLAN_29, $emessage->render().$text);
	}


	function preview_item($id)
	{

		require_once(e_HANDLER."news_class.php");
		$ix = new news;

		$e107 = &e107::getInstance();

		$_POST['news_title'] = $e107->tp->toDB($_POST['news_title']);
		$_POST['news_summary'] = $e107->tp->toDB($_POST['news_summary']);

		$_POST['news_id'] = $id;

		if($_POST['news_start'])
		{
			$tmp = explode("/", $_POST['news_start']);
			$_POST['news_start'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$tmp = explode("/", $_POST['news_end']);
			$_POST['news_end'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_end'] = 0;
		}

		$matches = array();
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

		$e107->sql->db_Select("news_category", "*", "category_id='".intval($_POST['cat_id'])."'");
		list($_POST['category_id'], $_POST['category_name'], $_POST['category_icon']) = $e107->sql->db_Fetch();

	   	list($_POST['user_id'],$_POST['user_name']) = explode(chr(35), $_POST['news_author']);
		$_POST['news_author'] = $_POST['user_id'];
		$_POST['comment_total'] = $id ? $e107->sql->db_Count("comments", "(*)", " WHERE comment_item_id={$id} AND comment_type='0'") : 0;
		$_PR = $_POST;

		$_PR['news_body'] = $e107->tp->post_toHTML($_PR['data'],FALSE);
		$_PR['news_title'] = $e107->tp->post_toHTML($_PR['news_title'],FALSE,"emotes_off, no_make_clickable");
		$_PR['news_summary'] = $e107->tp->post_toHTML($_PR['news_summary']);
		$_PR['news_extended'] = $e107->tp->post_toHTML($_PR['news_extended']);
		$_PR['news_file'] = $_POST['news_file'];
		$_PR['news_thumbnail'] = basename($_POST['news_thumbnail']);

		//$ix->render_newsitem($_PR);

		return "
				<fieldset id='core-newspost-preview'>
					<legend>".NWSLAN_27."</legend>
					<table cellpadding='0' cellspacing='0' class='admininfo'>
					<tbody>
						<tr>
							<td class='label' colspan='2'>
								".$e107->tp->parseTemplate('{NEWSINFO}').$ix->render_newsitem($_PR, 'return')."
							</td>
						</tr>
					</tbody>
					</table>
				</fieldset>
		";
	}

	function ajax_exec_cat()
	{
		require_once (e_HANDLER.'js_helper.php');
		$e107 = &e107::getInstance();

		$category = array();
		if ($e107->sql->db_Select("news_category", "*", "category_id=".$this->getId()))
		{
			$category = $e107->sql->db_Fetch();
		}

		if(empty($category))
		{
			e_jshelper::sendAjaxError(404, 'Page not found!', 'Requested news category was not found in the DB.', true);
		}
		$jshelper = new e_jshelper();

		//show cancel and update, hide create buttons; disable create button (just in case)
		$jshelper->addResponseAction('element-invoke-by-id', array(
			'show' => 		'category-clear,update-category',
			'disabled,1' => 'create-category',
			'hide' => 		'create-category'
		));

		//category icon alias
		$category['category-button'] = $category['category_icon'];

		//Send the prefered response type
		$jshelper->sendResponse('fill-form', $category);
	}

	function show_categories()
	{
		require_once(e_HANDLER."form_handler.php");
		$frm = new e_form(true); //enable inner tabindex counter

		$e107 = &e107::getInstance();

		$category = array();
		if ($this->getSubAction() == "edit")
		{
			if ($e107->sql->db_Select("news_category", "*", "category_id=".$this->getId()))
			{
				$category = $e107->sql->db_Fetch();
			}
		}

		$text = "
			<form method='post' action='".e_SELF."?cat' id='core-newspost-cat-create-form'>
				<fieldset id='core-newspost-cat-create'>
					<legend>".NWSLAN_56."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".NWSLAN_52."</td>
								<td class='control'>
									".$frm->text('category_name', $category['category_name'], 200)."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_53."</td>
								<td class='control'>
									".$frm->iconpicker('category_button', $category['category_icon'], NWSLAN_54)."
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
		";
		if($category)
		{
			$text .= "
				".$frm->admin_button('update_category', NWSLAN_55, 'update')."
				".$frm->admin_button('category_clear', LAN_CANCEL, 'cancel')."
				".$frm->hidden("category_id", $this->getId())."
			";
		}
		else
		{
			$text .= "
				".$frm->admin_button('create_category', NWSLAN_56, 'create')."
				".$frm->admin_button('update_category', NWSLAN_55, 'update', '', 'other=style="display:none"')."
				".$frm->admin_button('category_clear', LAN_CANCEL, 'cancel', '', 'other=style="display:none"')."
				".$frm->hidden("category_id", 0)."
			";
		}

		$text .= "
					</div>
				</fieldset>
			</form>
		";

		$text .= "
			<form action='".e_SELF."?cat' id='core-newspost-cat-list-form' method='post'>
				<fieldset id='core-newspost-cat-list'>
					<legend>".NWSLAN_51."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='4'>
							<col style='width: 	5%'></col>
							<col style='width:  10%'></col>
							<col style='width:  70%'></col>
							<col style='width:  15%'></col>
						</colgroup>
						<thead>
							<tr>
								<th class='center'>".LAN_NEWS_45."</th>
								<th class='center'>".NWSLAN_122."</th>
								<th>".NWSLAN_6."</th>
								<th class='center last'>".LAN_OPTIONS."</th>
							</tr>
						</thead>
						<tbody>
		";
		if ($category_total = $e107->sql->db_Select("news_category")) {
			while ($category = $e107->sql->db_Fetch()) {

				if ($category['category_icon']) {
					$icon = (strstr($category['category_icon'], "images/") ? THEME_ABS.$category['category_icon'] : e_IMAGE_ABS."icons/".$category['category_icon']);
				}

				$text .= "
							<tr>
								<td class='center middle'>{$category['category_id']}</td>
								<td class='center middle'><img class='icon action' src='{$icon}' alt='' /></td>
								<td class='middle'>{$category['category_name']}</td>
								<td class='center middle'>
									<a class='action' id='core-news-catedit-{$category['category_id']}' href='".e_SELF."?cat.edit.{$category['category_id']}' tabindex='".$frm->getNext()."'>".ADMIN_EDIT_ICON."</a>
									".$frm->submit_image("delete[category_{$category['category_id']}]", $category['category_id'], 'delete', $e107->tp->toJS(NWSLAN_37." [ID: {$category['category_id']} ]"))."

								</td>
							</tr>
				";
			}
			$text .= "
						</tbody>
					</table>
			";
			} else {
			$text .= "<div class='center'>".NWSLAN_10."</div>";
		}

		$text .= "

			</fieldset>
		</form>
		";
		$emessage = &eMessage::getInstance();
		$e107->ns->tablerender(NWSLAN_46a, $emessage->render().$text);
	}

	function _optrange($num , $zero = true)
	{
		$tmp = range(0, $num < 0 ? 0 : $num);
		if(!$zero) unset($tmp[0]);

		return $tmp;
	}

	function ajax_exec_pref_archnum()
	{
		global $pref;

		require_once(e_HANDLER."form_handler.php");
		$frm = new e_form();

		echo $frm->selectbox('newsposts_archive', $this->_optrange(intval($this->getSubAction()) - 1), intval($pref['newsposts_archive']), 'class=tbox&tabindex='.intval($this->getId()));
	}


    function ajax_exec_searchValue()
	{
		require_once(e_HANDLER."form_handler.php");
		$frm = new e_form(true);
		echo $frm->filterValue($_POST['filtertype'],$this->_fields);
	}


	function show_news_prefs()
	{
		global $pref;

		require_once(e_HANDLER."form_handler.php");
		$frm = new e_form(true); //enable inner tabindex counter

		$e107 = &e107::getInstance();

		$text = "
			<form method='post' action='".e_SELF."?pref' id='core-newspost-settings-form'>
				<fieldset id='core-newspost-settings'>
					<legend class='e-hideme'>".NWSLAN_90."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".NWSLAN_86."</td>
								<td class='control'>
									".$frm->checkbox_switch('news_cats', 1, $pref['news_cats'])."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_87."</td>
								<td class='control'>
									".$frm->selectbox('nbr_cols', $this->_optrange(6, false), $pref['nbr_cols'], 'class=tbox')."
								</td>
							</tr>
							<tr>
							<td class='label'>".NWSLAN_88."</td>
							<td class='control'>
								".$frm->selectbox('newsposts', $this->_optrange(50, false), $pref['newsposts'], 'class=tbox')."
							</td>
							</tr>
		";


		// ##### ADDED FOR NEWS ARCHIVE --------------------------------------------------------------------
		// the possible archive values are from "0" to "< $pref['newsposts']"
		// this should really be made as an onchange event on the selectbox for $pref['newsposts'] ...
		//SecretR - Done
		$text .= "
							<tr>
								<td class='label'>".NWSLAN_115."</td>
								<td class='control'>
									<div id='newsposts-archive-cont'>".$frm->selectbox('newsposts_archive', $this->_optrange(intval($pref['newsposts']) - 1), intval($pref['newsposts_archive']), 'class=tbox')."</div>
									<div class='field-help'>".NWSLAN_116."</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_117."</td>
								<td class='control'>
									".$frm->text('newsposts_archive_title', $pref['newsposts_archive_title'])."
								</td>
							</tr>
		";
		// ##### END --------------------------------------------------------------------------------------

		$text .= "
							<tr>
								<td class='label'>".LAN_NEWS_51."</td>
								<td class='control'>
									".$frm->uc_select('news_editauthor', $pref['news_editauthor'], 'nobody,main,admin,classes')."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_106."</td>
								<td class='control'>
									".$frm->uc_select('subnews_class', $pref['subnews_class'], 'nobody,public,guest,member,admin,classes')."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_107."</td>
								<td class='control'>
									".$frm->checkbox_switch('subnews_htmlarea', '1', $pref['subnews_htmlarea'])."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_100."</td>
								<td class='control'>
									".$frm->checkbox_switch('subnews_attach', '1', $pref['subnews_attach'])."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_101."</td>
								<td class='control'>
									".$frm->text('subnews_resize', $pref['subnews_resize'], 5, 'size=6&class=tbox')."
									<div class='field-help'>".NWSLAN_102."</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_111."</td>
								<td class='control'>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox_switch('news_newdateheader', '1', $pref['news_newdateheader'])."
										<div class='field-help'>".NWSLAN_112."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_113."</td>
								<td class='control'>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox_switch('news_unstemplate', '1', $pref['news_unstemplate'])."
										<div class='field-help'>".NWSLAN_114."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_120."</td>
								<td class='control'>
									".$frm->bbarea('news_subheader', stripcslashes($pref['news_subheader']), 2, 'helpb')."
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('save_prefs', NWSLAN_89, 'update')."
					</div>
				</fieldset>
			</form>
		";
		$emessage = &eMessage::getInstance();
		$e107->ns->tablerender(NWSLAN_90, $emessage->render().$text);
	}


	function show_submitted_news()
	{

		$e107 = &e107::getInstance();

		require_once(e_HANDLER."form_handler.php");
		$frm = new e_form(true); //enable inner tabindex counter

		if ($e107->sql->db_Select("submitnews", "*", "submitnews_id !='' ORDER BY submitnews_id DESC"))
		{
			$text .= "
			<form action='".e_SELF."?sn' method='post'>
				<fieldset id='core-newspost-sn-list'>
					<legend class='e-hideme'>".NWSLAN_47."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='6'>
							<col style='width: 5%'></col>
							<col style='width: 75%'></col>
							<col style='width: 20%'></col>
						</colgroup>
						<thead>
							<tr>
								<th class='center'>ID</th>
								<th>".NWSLAN_57."</th>
								<th class='center last'>".LAN_OPTIONS."</th>
							</tr>
						</thead>
						<tbody>
			";
			while ($row = $e107->sql->db_Fetch())
			{
				$buttext = ($row['submitnews_auth'] == 0)? NWSLAN_58 :	NWSLAN_103;

				if (substr($row['submitnews_item'], -7, 7) == '[/html]') $row['submitnews_item'] = substr($row['submitnews_item'], 0, -7);
				if (substr($row['submitnews_item'],0 , 6) == '[html]') $row['submitnews_item'] = substr($row['submitnews_item'], 6);

				$text .= "
					<tr>
						<td class='center'>{$row['submitnews_id']}</td>
						<td>
							<strong>".$e107->tp->toHTML($row['submitnews_title'], FALSE, "TITLE")."</strong><br/>".$e107->tp->toHTML($row['submitnews_item'])."
						</td>
						<td>
							<div class='field-spacer'><strong>".NWSLAN_123.":</strong> ".(($row['submitnews_auth'] == 0) ? LAN_NO : LAN_YES)."</div>
							<div class='field-spacer'><strong>".LAN_DATE.":</strong> ".date("D dS M y, g:ia", $row['submitnews_datestamp'])."</div>
							<div class='field-spacer'><strong>".NWSLAN_124.":</strong> {$row['submitnews_name']}</div>
							<div class='field-spacer'><strong>".NWSLAN_125.":</strong> {$row['submitnews_email']}</div>
							<div class='field-spacer'><strong>".NWSLAN_126.":</strong> ".$e107->ipDecode($row['submitnews_ip'])."</div>
							<br/>
							<div class='field-spacer center'>
								".$frm->admin_button("category_edit_{$row['submitnews_id']}", $buttext, 'action', '', array('id'=>false, 'other'=>"onclick=\"document.location='".e_SELF."?create.sn.{$row['submitnews_id']}'\""))."
								".$frm->admin_button("delete[sn_{$row['submitnews_id']}]", LAN_DELETE, 'delete', '', array('id'=>false, 'title'=>$e107->tp->toJS(NWSLAN_38." [".LAN_NEWS_45.": {$row['submitnews_id']} ]")))."
							</div>
						</td>
					</tr>
				";
			}
			$text .= "
						</tbody>
					</table>
				</fieldset>
			</form>
			";
		}
		else
		{
			$text .= "<div class='center'>".NWSLAN_59."</div>";
		}
		$emessage = &eMessage::getInstance();
		$e107->ns->tablerender(NWSLAN_47, $emessage->render().$text);
	}



	function showMaintenance()
	{
		require_once(e_HANDLER."form_handler.php");
		$frm = new e_form(true); //enable inner tabindex counter

		$e107 = &e107::getInstance();

		$text = "
			<form method='post' action='".e_SELF."?maint' id='core-newspost-maintenance-form'>
				<fieldset id='core-newspost-maintenance'>
					<legend class='e-hideme'>".LAN_NEWS_59."</legend>
					<table class='adminform' cellpadding='0' cellspacing='0'>
					<colgroup span='2'>
						<col class='col-label'></col>
						<col class='col-control'></col>
					</colgroup>
					<tbody>
						<tr>
							<td class='label'>".LAN_NEWS_56."</td>
							<td class='control'>
								".$frm->admin_button('news_comments_recalc', LAN_NEWS_57, 'update')."
							</td>
						</tr>
					</tbody>
					</table>
				</fieldset>
			</form>
		";

		$emessage = &eMessage::getInstance();
		$e107->ns->tablerender(LAN_NEWS_59, $emessage->render().$text);
	}


	function _observe_newsCommentsRecalc()
	{
		global $sql2;

		$e107 = &e107::getInstance();
		$qry = "SELECT
			COUNT(`comment_id`) AS c_count,
			`comment_item_id`
			FROM `#comments`
			WHERE (`comment_type`='0') OR (`comment_type`='news')
			GROUP BY `comment_item_id`";

		if ($e107->sql->db_Select_gen($qry))
		{
			while ($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
			{
				$sql2->db_Update('news', 'news_comment_total = '.$row['c_count'].' WHERE news_id='.$row['comment_item_id']);
			}
		}
		$this->show_message(LAN_NEWS_58, E_MESSAGE_SUCCESS);
	}



	function show_message($message, $type = E_MESSAGE_INFO, $session = false)
	{
		// ##### Display comfort ---------
		$emessage = &eMessage::getInstance();
		$emessage->add($message, $type, $session);
	}

	function show_options()
	{
		$e107 = &e107::getInstance();

		$var['main']['text'] = NWSLAN_44;
		$var['main']['link'] = e_SELF;

		$var['create']['text'] = NWSLAN_45;
		$var['create']['link'] = e_SELF."?create";

		$var['cat']['text'] = NWSLAN_46;
		$var['cat']['link'] = e_SELF."?cat";
		$var['cat']['perm'] = "7";

		$var['pref']['text'] = NWSLAN_90;
		$var['pref']['link'] = e_SELF."?pref";
		$var['pref']['perm'] = "N";

		$c = $e107->sql->db_Count('submitnews');
		if ($c) {
			$var['sn']['text'] = NWSLAN_47." ({$c})";
			$var['sn']['link'] = e_SELF."?sn";
			$var['sn']['perm'] = "N";
		}

		if (getperms('0'))
		{
			$var['maint']['text'] = LAN_NEWS_55;
			$var['maint']['link'] = e_SELF."?maint";
			$var['maint']['perm'] = "N";
		}

		e_admin_menu(NWSLAN_48, $this->getAction(), $var);
	}

}

function newspost_adminmenu()
{
	global $newspost;
	$newspost->show_options();
}
