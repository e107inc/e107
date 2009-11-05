<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Site Links
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/links.php,v $
 * $Revision: 1.32 $
 * $Date: 2009-11-05 17:32:17 $
 * $Author: secretr $
 *
*/

require_once ('../class2.php');
if(!getperms('I'))
{
	header('location:'.e_BASE.'index.php');
	exit();
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'links';

if(!is_object($tp))
	$tp = new e_parse();

// ----- Presets.----------
require_once (e_HANDLER."preset_class.php");
$pst = new e_preset();
$pst->form = "core-links-edit-form";
$pst->page = "links.php?create";
$pst->id = "admin_links";
require_once ('auth.php');
// --------------------
$pst->save_preset();

require_once (e_HANDLER.'userclass_class.php');
require_once (e_HANDLER.'form_handler.php');
require_once(e_HANDLER."message_handler.php");
require_once (e_HANDLER."ren_help.php");

$rs = new form();

define("URL_SEPARATOR", 'X'); // Used in names of 'inc' and 'dec' fields

$linkpost = new links();
$emessage = &eMessage::getInstance();
/*
$action = '';
if(e_QUERY)
{
	$tmp = explode('.', e_QUERY);
	$action = $tmp[0];
	$sub_action = varset($tmp[1], '');
	$id = varset($tmp[2], '');
	unset($tmp);
}
*/


/*
$incdec_action = '';
foreach(array_keys($_POST) as $k)
{
	if(preg_match("#(.*?)_delete_(\d+)(.*)#", $k, $matches))
	{
		$delete = $matches[1];
		$del_id = $matches[2];
	} elseif(!$incdec_action && (preg_match("#^(inc|dec)".URL_SEPARATOR."(\d+)".URL_SEPARATOR."(\d+)_[x|y]#", $k, $matches)))
	{
		$incdec_action = $matches[1];
		$linkid = intval($matches[2]);
		$link_order = intval($matches[3]);
	}
}*/

if(isset($_POST['generate_sublinks']) && isset($_POST['sublink_type']) && $_POST['sublink_parent'] != "")
{
	$subtype = $_POST['sublink_type'];
	$sublink = $linkpost->sublink_list($subtype);

	$sql2 = e107::getDb('sql2');

	$sql->db_Select("links", "*", "link_id = '".$_POST['sublink_parent']."'");
	$par = $sql->db_Fetch();
	extract($par);

	$sql->db_Select($sublink['table'], "*", $sublink['query']);
	$count = 1;
	while($row = $sql->db_Fetch())
	{
		$subcat = $row[($sublink['fieldid'])];
		$name = $row[($sublink['fieldname'])];
		$subname = $name; // eliminate old embedded hierarchy from names. (e.g. 'submenu.TopName.name')
		$suburl = str_replace("#", $subcat, $sublink['url']);
		$subicon = ($sublink['fieldicon']) ? $row[($sublink['fieldicon'])] : $link_button;
		$subdiz = ($sublink['fielddiz']) ? $row[($sublink['fielddiz'])] : $link_description;
		$subparent = $_POST['sublink_parent'];

		if($sql2->db_Insert("links", "0, '$subname', '$suburl', '$subdiz', '$subicon', '$link_category', '$count', '$subparent', '$link_open', '$link_class' "))
		{
			$message .= LAN_CREATED." ({$name})[!br!]";
			$emessage->add(LAN_CREATED." ({$name})", E_MESSAGE_SUCCESS);
		} else
		{
			$message .= LAN_CREATED_FAILED." ({$name})[!br!]";
			$emessage->add(LAN_CREATED_FAILED." ({$name})", E_MESSAGE_ERROR);
		}
		$count++;
	}

	if($message)
	{
		sitelinks_adminlog('01', $message); // 'Sublinks generated'
	}
}
// DEPRECATED. 
/*
if($incdec_action == 'inc')
{
	$sql->db_Update("links", "link_order=link_order+1 WHERE link_order='".intval($link_order - 1)."'");
	$sql->db_Update("links", "link_order=link_order-1 WHERE link_id='".intval($linkid)."'");
	sitelinks_adminlog('02', 'Id: '.$linkid);
}
elseif($incdec_action == 'dec')
{
	$sql->db_Update("links", "link_order=link_order-1 WHERE link_order='".intval($link_order + 1)."'");
	$sql->db_Update("links", "link_order=link_order+1 WHERE link_id='".intval($linkid)."'");
	sitelinks_adminlog('03', 'Id: '.$linkid);
}*/

// DEPRECATED - use batch method instead. 
/*if(isset($_POST['update']))
{
	foreach($_POST['link_order'] as $loid)
	{
		$tmp = explode(".", $loid);
		$sql->db_Update("links", "link_order=".intval($tmp[1])." WHERE link_id=".intval($tmp[0]));
	}
	foreach($_POST['link_class'] as $lckey => $lcid)
	{
		$sql->db_Update("links", "link_class='".$lcid."' WHERE link_id=".intval($lckey));
	}
	$e107cache->clear("sitelinks");
	$emessage->add(LAN_UPDATED, E_MESSAGE_SUCCESS);
	sitelinks_adminlog('04', '');
}*/

if(isset($_POST['updateoptions']))
{
	$changed = FALSE;
	foreach(array('linkpage_screentip', 'sitelinks_expandsub') as $opt)
	{
		$temp = intval($_POST[$opt]);
		if($temp != $pref[$opt])
		{
			$pref[$opt] = $temp;
			$changed = TRUE;
		}
	}
	if($changed)
	{
		save_prefs();
		$e107cache->clear("sitelinks");
		sitelinks_adminlog('05', $pref['linkpage_screentip'].','.$pref['sitelinks_expandsub']);
		$emessage->add(LCLAN_1, E_MESSAGE_SUCCESS);
	}
	else
	{
		// Nothing changed
		$emessage->add(LINKLAN_11);
	}
}






require_once ('footer.php');
exit();

// End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


class links
{
	var $link_total;
	var $aIdOptPrep, $aIdOptData, $aIdOptTest;
	var $debug_dis = FALSE;
	var $linkArray = array();
	var $linkCategory = array();
	var $linkOpen = array();
	var $mode = 'main';
	
	function __construct()
	{
		global $user_pref;
		$sql = e107::getDb();
		
		if(varset($_GET['mode']))
		{
			$this->mode = $_GET['mode'];
		}
		
		if (varset($_POST['etrigger_ecolumns']))
		{
			$user_pref['admin_links_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}
		
		$this->linkCategory = array(
			1	=> "1 - Main",
			2	=> "2 - Alt",
			3	=> "3 - Alt",
			4	=> "4 - Alt",
			5	=> "5 - Alt",
			6	=> "6 - Alt",
			7	=> "7 - Alt",
			8	=> "8 - Alt",
			9	=> "9 - Alt",
			10	=> "10 - Alt"
		);
		
		$this->linkOpen = array(
			0 => LCLAN_20, // 0 = same window
			4 => LCLAN_24, // 4 = miniwindow  600x400
			5 => LINKLAN_1 // 5 = miniwindow  800x600
		);					
		
		$this->fields = array(
			'checkboxes' 		=> array('title'=> '','width' => '3%','forced' => true,'thclass' => 'center first'),
			'link_button'		=> array('title'=> LCLAN_89, 'width'=>'5%', 'thclass' => 'center'),		
			'link_id'			=> array('title'=> ID, 'width'=>'5%', 'forced'=> TRUE, 'primary'=>TRUE),
			'link_name'	   		=> array('title'=> LCLAN_15, 'width'=>'auto','type'=>'text'),
			'link_url'	   		=> array('title'=> LCLAN_93, 'width'=>'auto','type'=>'text'),
			'link_class' 		=> array('title'=> LAN_USERCLASS, 'type' => 'array', 'method'=>'tinymce_class', 'width' => 'auto'),	
			'link_description' 	=> array('title'=> LCLAN_17, 'type' => 'array', 'method'=>'tinymce_plugins', 'width' => 'auto'),	
			'link_category' 	=> array('title'=> LCLAN_12, 'type' => 'text', 'method'=>'tinymce_buttons', 'methodparms'=>1, 'width' => 'auto'),
			'link_order' 		=> array('title'=> LAN_ORDER, 'type' => 'text', 'method'=>'tinymce_buttons', 'methodparms'=>2, 'width' => 'auto'),
			'link_parent' 		=> array('title'=> LINKLAN_2, 'type' => 'text', 'method'=>'tinymce_buttons', 'methodparms'=>3, 'width' => 'auto', 'thclass' => 'left first'), 
         	'link_open'		 	=> array('title'=> LCLAN_19, 'type' => 'text', 'method'=>'tinymce_buttons', 'methodparms'=>4, 'width' => 'auto', 'thclass' => 'left first'),
			'increment' 		=> array('title'=> LCLAN_91,'width' => '3%','forced' => true,'thclass' => 'center'),	  	
			'options' 			=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last')
		);
		
		$this->fieldpref = (varset($user_pref['admin_links_columns'])) ? $user_pref['admin_links_columns'] : array_keys($this->fields);
	
		if(varset($_POST['inc']))
		{
			list($link_id,$link_order) = explode(URL_SEPARATOR,key($_POST['inc']));
			$sql->db_Update("links", "link_order=link_order+1 WHERE link_order='".intval($link_order - 1)."'");
			$sql->db_Update("links", "link_order=link_order-1 WHERE link_id='".intval($link_id)."'");
			sitelinks_adminlog('02', 'Id: '.$link_id);
		}
		
		if(varset($_POST['dec']))
		{			
			list($link_id,$link_order) = explode(URL_SEPARATOR,key($_POST['dec']));
			$sql->db_Update("links", "link_order=link_order-1 WHERE link_order='".intval($link_order + 1)."'");
			$sql->db_Update("links", "link_order=link_order+1 WHERE link_id='".intval($link_id)."'");
			sitelinks_adminlog('03', 'Id: '.$link_id);
		}
		
		if(varset($_POST['execute_batch']))
		{
			$this->process_batch($_POST['link_selected']);
		}
		
		if(varset($_POST['add_link']))
		{
			$this->submit_link($sub_action, $_POST['link_id']);
		}
		
		if(varset($_POST['delete']))
		{
			$del_id = key($_POST['delete']);
			if($sql->db_Select("links", "link_id, link_name, link_order, link_parent", "link_id=".intval($del_id)))
			{
				$row = $sql->db_Fetch();
				$this->delete_link($row); // Admin logging in class routine
			}
		}
		
		$this->linkArray = $this->getLinks();
		
		if(varset($_POST['edit']))
		{
			$this->create_link('edit', key($_POST['edit']));
			return;
		}
		
		if(varset($_POST['sub']))
		{
			$this->mode = 'sub';
			$this->create_link('sub', key($_POST['sub']));
			return;
		}
					
			
		switch ($this->mode) // page display mode
		{
			
			case 'main':
				
				$this->show_existing_items();
			break;
			
			case 'create':
				$this->create_link($sub_action,0);
			break;
		
			case 'debug':
				$linkpost->show_existing_items(TRUE);
			break;
		
			case 'opt':
				$this->show_pref_options();
			break;
		
			case 'sub':
				$this->show_sublink_generator();
			break;
		
			case 'savepreset':
			case 'clr_preset':
			default: //handles preset urls as well
				$action = 'main';
				$sub_action = $id = '';
			//	$linkpost->show_existing_items();
			break;
		}
				
	}
	

	function getLinks()
	{
		global $sql;
		if($this->link_total = $sql->db_Select("links", "*", "ORDER BY link_category,link_order, link_id ASC", "nowhere"))
		{
			while($row = $sql->db_Fetch())
			{
				$ret[$row['link_parent']][] = $row;
			}
		}
		return $ret;
	}

	function linkName($text)
	{
		// This function is ONLY needed for link databases that have been upgraded from
		// before 0.7+ -- all new link collections make use of link_parent instead
		// of hierarchy embedded in the link_name. (Unfortunately, the upgraded
		// data still includes embedded coding.)


		if(substr($text, 0, 8) == "submenu.") // for backwards compatibility only.
		{
			$tmp = explode(".", $text);
			switch(count($tmp))
			{
				case 3: // submenu.parent.node
					$tmp = $tmp[2];
					break;
				case 5: // submenu.parent.midlev.child.node
					$tmp = $tmp[4];
					break;
				case 2: // submenu.parent (invalid?)
				default:
					$parentLen = strlen($tmp[1]);
					$tmp = substr($text, 8 + $parentLen + 1); // Skip submenu.parent.
			}
			return $tmp;
		}
		else
		{
			return $text;
		}
	}

	function dropdown($curval = "", $lid = 0, $indent = 0)
	{ // Drop-down list using on the parent_id. :)
	
	
		global $linkArray, $id, $sub_action;

		if(0 == $indent)
		{
			$ret = "<option value=''>".LINKLAN_3."</option>\n";
		}
		foreach($this->linkArray[$lid] as $l)
		{
			$s = ($l['link_id'] == $curval ? " selected='selected' " : "");
			$thename = $this->linkName($l['link_name']);
			// prevent making self the parent.
			if($l['link_id'] == $id)
			{
				$thename = "(".$thename.")";
			}
			if($sub_action == "sub")
			{
				$thelink = ($l['link_id'] != $lid) ? $l['link_id'] : $l['link_parent'];
			}
			else
			{
				$thelink = ($l['link_id'] != $id) ? $l['link_id'] : $l['link_parent'];
			}
			$ret .= "<option value='".$thelink."' {$s}>".str_pad("", $indent * 36, "&nbsp;").$thename." </option>\n";

			if(array_key_exists($l['link_id'], $this->linkArray))
			{
				$ret .= $this->dropdown($curval, $l['link_id'], $indent + 1);
			}
		}
		return $ret;
	}


	function existing($id = 0, $level = 0)
	{
		global $linkArray;
		$ret = "";
		foreach($linkArray[$id] as $l)
		{
			$s = ($l['link_parent'] == $curval ? " selected='selected' " : "");
			$ret .= $this->display_row($l, $level);
			if(array_key_exists($l['link_id'], $this->linkArray))
			{
				$ret .= $this->existing($l['link_id'], $level + 1);
			}
		}
		return $ret;
	}


	function show_existing_items($dbg_display = FALSE)
	{
		global $rs, $emessage;
		
		$sql = e107::getDb();
		$frm = e107::getForm();
		$tp = e107::getParser();
		$ns = e107::getRender();
				
		$this->debug_dis = $dbg_display;

		if(count($this->linkArray))
		{
			$text = $rs->form_open("post", e_SELF, "myform_{$link_id}", "", "");
			$text .= "
        		<fieldset id='core-links-list'>
				<legend class='e-hideme'>".$this->pluginTitle."</legend>
				<table cellpadding='0' cellspacing='0' class='adminlist'>".
				$frm->colGroup($this->fields,$this->fieldpref).
				$frm->thead($this->fields,$this->fieldpref).
				"<tbody>";
		
			foreach($this->linkArray[0] as $field)
			{
				$text .= "<tr>\n";
				foreach($this->fields as $key=>$att)
				{	
					$class = vartrue($this->fields[$key]['thclass']) ? "class='".$this->fields[$key]['thclass']."'" : "";		
					$text .= (in_array($key,$this->fieldpref) || $att['forced']==TRUE) ? "\t<td ".$class.">".$this->renderValue($key,$field)."</td>\n" : "";						
				}
				$text .= "</tr>\n";				
			}
		
			$text .= "
				</tbody>
				</table>";
			
			$text .= "<div class='buttons-bar center'>".$this->show_batch_options()."</div>";	
				
				$text .= "
				</fieldset>
			</form>
			";
			
			/*

			$this->prepIdOpts(); // Prepare the options list for all links
			$text = $rs->form_open("post", e_SELF, "myform_{$link_id}", "", "");
			$text .= "
			<fieldset id='core-links-list-1'>
				<legend class='e-hideme'>".LCLAN_12.": 1</legend>
				<table cellpadding='0' cellspacing='0' class='adminlist'>
					<colgroup>
						<col style='width:  5%' />
						<col style='width: 60%' />
						<col style='width: 15%' />

						<col style='width:  5%' />
						<col style='width:  5%' />
						<col style='width: 10%' />
					</colgroup>
					<thead>
						<tr>
							<th class='center'>".LCLAN_89."</th>
							<th>".LCLAN_15."</th>
                   							<th class='center'>".LCLAN_95."</th>
							<th class='center'>".LCLAN_91."</th>
							<th class='center last'>".LAN_ORDER."</th>
							<th class='center'>".LAN_OPTIONS."</th>
						</tr>
					</thead>
					<tbody>
				";
			$text .= $this->existing(0);

			$text .= "
					</tbody>
				</table>
				<div class='buttons-bar center'>
					<button class='update' type='submit' name='update' value='no-value'><span>".LAN_UPDATE."</span></button>
				</div>
			</fieldset>

			";	
			
			$text .= $rs->form_close();
			*/
			
		}
		else
		{
			$text .= "<div class='center'>".LCLAN_61."</div>";
		}
		
		$ns->tablerender(LCLAN_8, $emessage->render().$text);
	}
	
	
	
	function renderValue($key,$row)
	{
		$frm = e107::getForm();
		$tp = e107::getParser();
		
		$text = "";
		$att = $this->fields[$key];	
		
		if($key == 'checkboxes')
		{
			$rowid = "link_selected[".$row["link_id"]."]";
			return $frm->checkbox($rowid, $row['link_id']);
		}
		
		if($key == "link_name") // FIXME - incorrect links. 
		{
			$link = (substr($row['link_url'],0,3)=='{e_') ? $tp->replaceConstants($row['link_url']) : e_BASE.$row['link_url'];
			return "<a href='".$link."'>".$this->linkName($row['link_name'])."</a>";	
		}
		
		if($key == "link_button")
		{
			$button = $tp->replaceConstants($row['link_button']);
			return ($button) ? "<img src='".$button."' alt='' />" : "&nbsp;";
		}
		
		if($key == "link_class")
		{
			return $frm->uc_label($row['link_class']);
		}
		
		if($key == "link_category")
		{
			$cat = $row['link_category'];
			return $this->linkCategory[$cat];
		}
		
		if($key == "link_open")
		{
			return $this->linkOpen[$row['link_open']];
		}
		
		if($key == "increment")
		{
			$name_suffix = $row["link_id"].URL_SEPARATOR.$row["link_order"];
			$text .= "<input type='image' class='action' name='inc[{$name_suffix}]' src='".ADMIN_UP_ICON_PATH."' title='".LCLAN_30."' />";
			$text .= "<input type='image' class='action' name='dec[{$name_suffix}]'  src='".ADMIN_DOWN_ICON_PATH."' title='".LCLAN_31."' />";
			return $text;
		}
		
		
		if($key == "options")
		{
			$id = $row['link_id'];
			
			$text .= "<input type='image' class='action' name='sub[{$id}]' src='".ADMIN_ADD_ICON_PATH."' title='".LINKLAN_10."' />";
			$text .= "<input type='image' class='action edit' name='edit[{$id}]' src='".ADMIN_EDIT_ICON_PATH."' title='".LAN_EDIT."' />";
			$text .= "<input type='image' class='action delete' name='delete[{$id}]' src='".ADMIN_DELETE_ICON_PATH."' title='".LAN_DELETE." [ ID: {$id} ]' />";
			return $text;
		}
		
		return $row[$key];
	}




	function prepIdOpts()
	{
		for($a = 1; $a <= $this->link_total; $a++)
		{
			$sTxt = "".$a;
			$this->aIdOptData[] = array('val' => '|||.'.$a, 'txt' => $sTxt); // Later, ||| becomes Id
			$this->aIdOptTest[] = $sTxt;
		}
		$this->aIdOptPrep = $this->prepOpts($this->aIdOptData);
	}

	function display_row($row2, $indent = FALSE)
	{
		global $sql, $rs, $tp, $linkArray, $previous_cat;
		extract($row2);

		if($link_category > 1 && $link_category != $previous_cat)
		{
				$text .= "
				</tbody>
			</table>
		</fieldset>
		<fieldset id='core-links-list-".$link_category."'>
			<legend class='e-hideme'>".LCLAN_12.": ".$link_category."</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup>
					<col style='width:  5%' />
					<col style='width: 60%' />
					<col style='width: 15%' />
	                <col style='width: 10%' />
					<col style='width:  5%' />
					<col style='width:  5%' />
				</colgroup>
				<thead>
					<tr>
						<th class='center'>".LCLAN_89."</th>
						<th>".LCLAN_15." (".LCLAN_12.": ".$link_category.")</th>
	                    <th class='center'>".LAN_OPTIONS." hihihi</th>
						<th class='center'>".LCLAN_95."</th>
						<th class='center'>".LCLAN_91."</th>
						<th class='center last'>".LAN_ORDER."</th>
					</tr>
				</thead>
				<tbody>
			";
				$previous_cat = $link_category;
			}
	
			if(strpos($link_name, "submenu.") !== FALSE || $link_parent != 0) // 'submenu' for upgrade compatibility only.
			{
				$link_name = $this->linkName($link_name);
			}
	
			if($this->debug_dis)
			{
				$link_name .= ' ['.$link_url.']';
			}
	
			if($indent)
			{
				$subimage = "<img src='".e_IMAGE."generic/branchbottom.gif' alt='' />";
				$subspacer = ($indent > 1) ? " style='padding-left: ".(($indent - 1) * 16)."px'" : "";
			}
	
			$text .= "
				<tr>
					<td title='".$link_description."'>
			";
			$text .= $link_button ? "<img class='icon S16' src='".e_IMAGE_ABS."icons/".$link_button."' alt='' /> " : "";
			$text .= "
					</td>
					<td title='".$link_description."'".$subspacer.">
						".$subimage." <a href='".$tp->replaceConstants(e_BASE.$row2['link_url'], true, true)."'>".$link_name."</a>
					</td>
			";
			$text .= "
	
					<td>".r_userclass("link_class[".$link_id."]", $link_class, "off", "public,guest,nobody,member,main,admin,classes")."</td>
					<td class='center'>
			";
			$name_suffix = URL_SEPARATOR.$link_id.URL_SEPARATOR.$link_order;
			$text .= "
						<input name='inc".$name_suffix."' type='image' src='".ADMIN_UP_ICON_PATH."' title='".LCLAN_30."' />
						<input name='dec".$name_suffix."' type='image' src='".ADMIN_DOWN_ICON_PATH."' title='".LCLAN_31."' />
					</td>
					<td>
						<select name='link_order[]' class='tbox select order'>\n
			";
			$text .= $this->genOpts($this->aIdOptPrep, $this->aIdOptTest, $link_order, $link_id);
			$text .= "
						</select>
					</td>
	                <td class='center'>
						<a href='".e_SELF."?create.sub.{$link_id}' title='".LINKLAN_10."'>".ADMIN_ADD_ICON."</a>&nbsp;
						<a href='".e_SELF."?create.edit.{$link_id}'>".ADMIN_EDIT_ICON."</a>&nbsp;
						<input class='action delete' type='image' name='main_delete_{$link_id}' src='".ADMIN_DELETE_ICON_PATH."' title='".$tp->toJS(LCLAN_58." [ $link_name ]")."' />
					</td>
				</tr>
			";
	
			return $text;
	}

	function show_message($message)
	{
		global $ns;
		$ns->tablerender(LAN_UPDATE, "<div style='text-align:center'><b>".$message."</b></div>");
	}
	
	
	function show_batch_options()
	{
		$e107 = e107::getInstance();
		$classObj = $e107->getUserClass();
		$frm = new e_form();
		$classes = $classObj->uc_get_classlist();
		$comments_array = array("Disable Comments","Allow Comments");
			
		return $frm->batchoptions(
			array(
					'delete_selected'	=> LAN_DELETE,
					'rendertype' 			=> array('Modify Render-type', $this->linkCategory),
					'opentype'				=> array('Modify Open-Type', $this->linkOpen),
					// 'comments'			=> array('Modify Comments', $comments_array)
			),
		      array(
		         	'userclass'    		=> array('Assign Visibility...',$classes),
			)
	   );
	}
	
	
	function process_batch($id_array)
	{
		list($type,$tmp,$value) = explode("_",$_POST['execute_batch']);
		$method = "batch_".$type;
		
		if (method_exists($this,$method) && isset($id_array) )
		{
			$this->$method($id_array,$value);
		}
	}
	
		
	
	function batch_opentype($ids,$value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Update("links","link_open = ".$value." WHERE link_id IN (".implode(",",$ids).") ");
	}	

	function batch_rendertype($ids,$value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Update("links","link_category = ".$value." WHERE link_id IN (".implode(",",$ids).") ");
	}
	
	function batch_userclass($ids,$value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Update("links","link_class = ".$value." WHERE link_id IN (".implode(",",$ids).") ");
	}
	
	function batch_delete($ids,$value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Delete("links","link_id IN (".implode(",",$ids).") ");
	}	
	
	
		

	// Show the form for link create/edit
	function create_link($sub_action, $id)
	{
		global $sql, $e107, $pst, $tp, $emessage;

		$frm = new e_form();

		$preset = $pst->read_preset("admin_links");
		extract($preset);

		if($sub_action == "edit" && !$_POST['submit'])
		{
			if($sql->db_Select("links", "*", "link_id='$id' "))
			{
				$row = $sql->db_Fetch();
				extract($row);
			}
		}

		if("sub" == $sub_action)
		{
			$link_parent = $id;
		}

		if(strpos($link_name, "submenu.") !== FALSE)
		{ // 'submenu' for upgrade compatibility only.
			$link_name = $this->linkName($link_name);
		}

		require_once (e_HANDLER."file_class.php");
		$fl = new e_file();

		if($iconlist = $fl->get_files(e_IMAGE."icons/", '\.jpg|\.gif|\.png|\.JPG|\.GIF|\.PNG'))
		{
			sort($iconlist);
		}
		$text = "

			<form method='post' action='".e_SELF."' id='core-links-edit-form'>
				<fieldset id='core-links-edit'>
					<legend class='e-hideme'>".LCLAN_29."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label'></col>
							<col class='col-control'></col>
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".LINKLAN_2."</td>
								<td class='control'>
									<select class='tbox select' name='link_parent' >
										".$this->dropdown($link_parent)."
									</select>
								</td>
							</tr>
							<tr>
								<td class='label'>".LCLAN_15.": </td>
								<td class='control'>
									<input class='tbox input-text' type='text' name='link_name' size='60' value='{$link_name}' maxlength='100' />
								</td>
							</tr>
							<tr>
								<td class='label'>".LCLAN_16.": </td>
								<td class='control'>
									<input class='tbox input-text' type='text' name='link_url' size='60' value='".$tp->replaceConstants($link_url, TRUE)."' maxlength='200' />
									".((e_MENU == "debug") ? $link_url : "")."
								</td>
							</tr>
							<tr>
								<td class='label'>".LCLAN_17.": </td>
								<td class='control'>
									<textarea class='tbox textarea' id='link_description' name='link_description' cols='70' rows='5' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this)'>".$tp->toForm($link_description)."</textarea>
									<br/>".display_help("helpb", "admin")."

								</td>
							</tr>
							<tr>
								<td class='label'>".LCLAN_18.": </td>
								<td class='control'>

			";

		//SecretR - more nice view now handled by e_form (inner use of new sc {ICONPICKER})
		//Example sc opts (4th func argument)
		//$opts = 'path='.e_IMAGE.'icons/|'.e_IMAGE.'generic/';
		//$opts .= '&path_omit='.e_IMAGE.'icons/';
		$text .= $frm->iconpicker('link_button', $link_button, LCLAN_39);

		// 1 = _blank
		// 2 = _parent   not in use.
		// 3 = _top   not in use.
		$linkop[0] = LCLAN_20; // 0 = same window
		$linkop[1] = LCLAN_23;
		$linkop[4] = LCLAN_24; // 4 = miniwindow  600x400
		$linkop[5] = LINKLAN_1; // 5 = miniwindow  800x600


		$text .= "</td>
			</tr>
			<tr>
			<td class='label'>".LCLAN_19.": </td>
			<td class='control'>
			<select name='linkopentype' class='tbox select'>
			";
			foreach($linkop as $key => $val)
			{
				$selectd = ($link_open == $key) ? " selected='selected'" : "";
				$text .= "<option value='$key'{$selectd}>".$val."</option>\n";
			}

		$text .= "
			</select>
			</td>
			</tr>
			<tr>
				<td class='label'>".LCLAN_12.": </td>
				<td class='control'>
					<select name='linkrender' class='tbox select'>
			";
		$rentype = array("", "Main", "Alt", "Alt", "Alt", "Alt", "Alt", "Alt", "Alt", "Alt", "Alt");
		for($i = 1; $i < count($rentype); $i++)
		{
			$sel = ($link_category == $i) ? " selected='selected'" : "";
			$text .= "<option value='$i'{$sel}>$i - ".$rentype[$i]."</option>";
		}
		;

		$text .= "
									</select>
									<div class='smalltext field-help'>".LCLAN_96." {SITELINKS=flat:[rendertype number]}</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".LCLAN_25.":

								</td>
								<td class='control'>
									".r_userclass("link_class", $link_class, "off", "public,guest,nobody,member,main,admin,classes")."
									<div class='smalltext field-help'>(".LCLAN_26.")</div>
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
			";
		if($id && $sub_action == "edit")
		{
			$text .= "
						<button class='update' type='submit' name='add_link' value='no-value'><span>".LCLAN_27."</span></button>
						<input type='hidden' name='link_id' value='{$link_id}' />
			";
		} else
		{
			$text .= "
						<button class='create' type='submit' name='add_link' value='no-value'><span>".LCLAN_28."</span></button>
			";
		}
		$text .= "
					</div>
				</fieldset>
			</form>
			";
		$e107->ns->tablerender(LCLAN_29, $emessage->render().$text);
	}

	function submit_link($sub_action, $id)
	{
		global $e107cache,$emessage;
		$sql = e107::getDb();
		$tp = e107::getParser();
		

		$id = intval($id);
		$parent_id = ($_POST['link_parent']) ? intval($_POST['link_parent']) : 0;

		$link_name = $tp->toDB($_POST['link_name']);
		$link_url = $tp->createConstants($_POST['link_url']);
		$link_url = str_replace("&", "&amp;", $link_url); // xhtml compliant links.


		$link_description = $tp->toDB($_POST['link_description']);
		$link_button = $tp->toDB($_POST['link_button']);
		$link_render = intval($_POST['linkrender']);
		$link_open = intval($_POST['linkopentype']);
		$link_class = $tp->toDB($_POST['link_class']);

		$message = implode('[!br!]', array($link_name, $link_url, $link_class)); // Probably enough to log
		$link_t = $sql->db_Count("links", "(*)");
		if($id)
		{
			$sql->db_Update("links", "link_parent='{$parent_id}', link_name='{$link_name}', link_url='{$link_url}', link_description='{$link_description}', link_button= '{$link_button}', link_category='{$link_render}', link_open='{$link_open}', link_class='{$link_class}' WHERE link_id='{$id}'");
			//rename all sublinks to eliminate old embedded 'submenu' etc hierarchy.
			// this is for upgrade compatibility only. Current hierarchy uses link_parent.
			$e107cache->clear("sitelinks");
			sitelinks_adminlog('08', $message);
			$emessage->add(LCLAN_3, E_MESSAGE_SUCCESS);
		}
		else
		{ // New link
			$sql->db_Insert("links", "0, '$link_name', '$link_url', '$link_description', '$link_button', ".$link_render.", ".($link_t + 1).", ".$parent_id.", ".$link_open.", ".$link_class,TRUE);
			$e107cache->clear("sitelinks");
			sitelinks_adminlog('07', $message);
			$emessage->add(LCLAN_2, E_MESSAGE_SUCCESS);
		}
	}

	function show_pref_options()
	{
		global $pref, $e107, $emessage;
		$text = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
			<fieldset id='core-links-options'>
				<legend class='e-hideme'>".LCLAN_88."</legend>
				<table cellpadding='0' cellspacing='0' class='adminform'>
					<colgroup span='2'>
						<col class='col-label'></col>
						<col class='col-control'></col>
					</colgroup>
					<tbody>
						<tr>
							<td class='label'>".LCLAN_78."</td>
							<td class='control'>
								<div class='auto-toggle-area autocheck'>
									<input type='checkbox' class='checkbox' name='linkpage_screentip' value='1'".($pref['linkpage_screentip'] ? " checked='checked'" : "")." />
									<div class='smalltext field-help'>".LCLAN_79."</div>
								</div>
							</td>
						</tr>
						<tr>
							<td class='label'>".LCLAN_80."</td>
							<td class='control'>
								<div class='auto-toggle-area autocheck'>
									<input type='checkbox' class='checkbox' name='sitelinks_expandsub' value='1'".($pref['sitelinks_expandsub'] ? " checked='checked'" : "")." />
									<div class='smalltext field-help'>".LCLAN_81."</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<div class='buttons-bar center'>
					<button class='update' type='submit' name='updateoptions' value='no-value'><span>".LAN_UPDATE."</span></button>
				</div>
			</fieldset>
		</form>


		";
		$e107->ns->tablerender(LCLAN_88, $emessage->render().$text);
	}

	// Delete link
	// We need to update the 'order' number of other links with the same parentage - may be top level or a sub-level
	function delete_link($linkInfo)
	{
		global $sql, $emessage, $e107cache;

		if($sql->db_Select("links", "link_id", "link_order > '{$linkInfo['link_order']}' AND `link_parent`={$linkInfo['link_parent']} "))
		{
			$linkList = $sql->db_getList();
			foreach($linkList as $l)
			{
				$sql->db_Update("links", "link_order = link_order -1 WHERE link_id = '{$l['link_id']}'");
			}
		}

		if($sql->db_Delete("links", "link_id='".$linkInfo['link_id']."'"))
		{
			// Update orphaned sublinks - just hide them, and make them top level. And delete any obsolete naming while we're there
			$sql->db_Update("links", "link_name = SUBSTRING_INDEX(link_name, '.', -1) , link_parent = '0', link_class='255' WHERE link_parent= '".$linkInfo['link_id']."'");

			$message = LCLAN_53." #".$linkInfo['link_id']." ".LCLAN_54;
			$emessage->add($message, E_MESSAGE_SUCCESS);
			sitelinks_adminlog('06', $message.'[!br!]'.$linkInfo['link_name']);
			$e107cache->clear("sitelinks");
		} else
		{
			$emessage->add($message, E_MESSAGE_ERROR);
		}

	}

	// -------------------------- Sub links generator ------------->


	function show_sublink_generator()
	{
		global $e107, $sql, $emessage;

		$sublinks = $this->sublink_list();

		$text = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
			<fieldset id='core-links-generator'>
				<legend class='e-hideme'>".LINKLAN_4."</legend>
				<table cellpadding='0' cellspacing='0' class='adminform'>
					<colgroup span='2'>
						<col class='col-label'></col>
						<col class='col-control'></col>
					</colgroup>
					<tbody>
						<tr>
							<td class='label'>".LINKLAN_6."</td>
							<td class='control'>
								<select name='sublink_type' class='tbox select'>
									<option value=''></option>";
		foreach($sublinks as $key => $type)
		{
			$text .= "
									<option value='$key'>".$type['title']."</option>
		";
		}
		$text .= "
								</select>
							</td>
						</tr>
						<tr>
							<td class='label'>".LINKLAN_7."</td>
							<td class='control'>
								<select name='sublink_parent' class='tbox select'>
								<option value=''></option>";
		$sql->db_Select("links", "*", "link_parent='0' ORDER BY link_name ASC");
		while($row = $sql->db_Fetch())
		{
			$text .= "
								<option value='".$row['link_id']."'>".$row['link_name']."</option>
		";
		}
		$text .= "
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<div class='buttons-bar center'>
					<button class='create' type='submit' name='generate_sublinks' value='no-value'><span>".LINKLAN_5."</span></button>
				</div>
			</fieldset>
		</form>
		";
		$e107->ns->tablerender(LINKLAN_4, $emessage->render().$text);
	}

	function sublink_list($name = "")
	{
		global $sql, $PLUGINS_DIRECTORY;
		$sublink_type['news']['title'] = LINKLAN_8; // "News Categories";
		$sublink_type['news']['table'] = "news_category";
		$sublink_type['news']['query'] = "category_id !='-2' ORDER BY category_name ASC";
		$sublink_type['news']['url'] = "news.php?cat.#";
		$sublink_type['news']['fieldid'] = "category_id";
		$sublink_type['news']['fieldname'] = "category_name";
		$sublink_type['news']['fieldicon'] = "category_icon";

		$sublink_type['downloads']['title'] = LINKLAN_9; //"Download Categories";
		$sublink_type['downloads']['table'] = "download_category";
		$sublink_type['downloads']['query'] = "download_category_parent ='0' ORDER BY download_category_name ASC";
		$sublink_type['downloads']['url'] = "download.php?list.#";
		$sublink_type['downloads']['fieldid'] = "download_category_id";
		$sublink_type['downloads']['fieldname'] = "download_category_name";
		$sublink_type['downloads']['fieldicon'] = "download_category_icon";

		if($sql->db_Select("plugin", "plugin_path", "plugin_installflag = '1'"))
		{
			while($row = $sql->db_Fetch())
			{
				$sublink_plugs[] = $row['plugin_path'];
			}
		}

		foreach($sublink_plugs as $plugin_id)
		{
			if(is_readable(e_PLUGIN.$plugin_id.'/e_linkgen.php'))
			{
				require_once (e_PLUGIN.$plugin_id.'/e_linkgen.php');
			}
		}
		if($name)
		{
			return $sublink_type[$name];
		}

		return $sublink_type;

	}

	function prepOpts($aData)
	{
		//
		// Prepare an array that can rapidly (no looping)
		// generate an HTML option string, with one item possibly selected.
		// prepOpts returns a prepared array containing the possible values in this form:
		//
		// <option value="xxxxx"
		// >text for first</option><option value="yyyy"
		// >text for next</option>
		//
		// $aData is an array containing value/text pairs:
		// each entry is array( 'val'=>value, 'txt'=>text )
		//


		$i = 0;
		foreach($aData as $aVal)
		{
			$sVal = $aVal['val'];
			$sTxt = $aVal['txt'];
			$sOut = "";

			if($i)
				$sOut = '>'.$sTxtPrev.'</option>';
			$sOut .= '<option value="'.$sVal.'"';

			$aPrep[$i++] = $sOut;
			$sTxtPrev = $sTxt;
		}
		if($i)
		{ // terminate final option
			$aPrep[$i] = '>'.$sTxtPrev.'</option>';
		}

		return $aPrep;
	}

	function genOpts($aPrep, $aTest, $sSelected, $sId)
	{
		//
		// Generate an HTML option string, with one item possibly selected.
		// aGen is a prepared array containing the possible values in this form.
		// if sSelected matches an aTest entry, that entry is selected.
		// aTest can be any array that matches one-for-one with the options
		//
		// if $sId is nonblank, a global search/replace is done to change all "|||" to $sId.


		$iKey = array_search($sSelected, $aTest);
		if($iKey !== FALSE)
		{
			$aNew = $aPrep;
			$aNew[$iKey] .= " selected='selected'";
			$sOut = implode($aNew);
		} else
		{
			$sOut = implode($aPrep);
		}
		if(strlen($sId))
			$sOut = str_replace("|||", $sId, $sOut);
		return $sOut;
	}

} // End - class 'links'


// Log event to admin log
function sitelinks_adminlog($msg_num = '00', $woffle = '')
{
	global $pref, $admin_log;
	//  if (!varset($pref['admin_log_log']['admin_sitelinks'],0)) return;
	$admin_log->log_event('SLINKS_'.$msg_num, $woffle, E_LOG_INFORMATIVE, '');
}

function links_adminmenu()
{
	global $action,$linkpost;
	
	$action = $linkpost->mode;
	
	$var['main']['text'] = LCLAN_62;
	$var['main']['link'] = e_SELF;

	$var['create']['text'] = LCLAN_63;
	$var['create']['link'] = e_SELF."?mode=create";

	$var['opt']['text'] = LAN_OPTIONS;
	$var['opt']['link'] = e_SELF."?mode=opt";

	$var['sub']['text'] = LINKLAN_4;
	$var['sub']['link'] = e_SELF."?mode=sub";

	//	$var['debug']['text'] = "List DB";
	//	$var['debug']['link'] = e_SELF."?debug";


	e_admin_menu(LCLAN_68, $action, $var);
}

/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			//add required core lan - delete confirm message
			(".e_jshelper::toString(LCLAN_58).").addModLan('core', 'delete_confirm');
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
?>