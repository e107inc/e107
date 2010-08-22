<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
|
| links.php?debug shows stored data for each link after name (before constant conversion)
+----------------------------------------------------------------------------+
*/

require_once('../class2.php');
if (!getperms('I'))
{
  header('location:'.e_BASE.'index.php');
  exit;
}

$e_sub_cat = 'links';

if (!is_object($tp)) $tp = new e_parse;

// ----- Presets.----------
require_once(e_HANDLER."preset_class.php");
$pst = new e_preset;
$pst->form = "linkform";
$pst->page = "links.php?create";
$pst->id = "admin_links";
require_once('auth.php');
// --------------------
$pst->save_preset();

require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'form_handler.php');

$rs = new form;
$linkpost = new links;

$action = '';
if (e_QUERY)
{
  $tmp = explode('.', e_QUERY);
  $action = $tmp[0];
  $sub_action = $tmp[1];
  $id = $tmp[2];
  unset($tmp);
}

define("URL_SEPARATOR",'X');		// Used in names of 'inc' and 'dec' fields

$incdec_action = '';
foreach(array_keys($_POST) as $k)
{
  if (preg_match("#(.*?)_delete_(\d+)(.*)#", $k, $matches))
  {
	$delete = $matches[1];
	$del_id = $matches[2];
  }
  elseif (!$incdec_action && (preg_match("#^(inc|dec)".URL_SEPARATOR."(\d+)".URL_SEPARATOR."(\d+)_[x|y]#", $k, $matches)))
  {
    $incdec_action = $matches[1];
	$linkid = intval($matches[2]);
	$link_order = intval($matches[3]);
  }
}

if(isset($_POST['generate_sublinks']) && isset($_POST['sublink_type']) && $_POST['sublink_parent'] !="" )
{
	$subtype = $_POST['sublink_type'];
	$sublink = $linkpost->sublink_list($subtype);
    if(!is_object($sql2))
	{
      $sql2 = new db;
	}

	$sql -> db_Select("links", "*", "link_id = '".$_POST['sublink_parent']."'");
	$par = $sql-> db_Fetch();
	extract($par);

	$sql -> db_Select($sublink['table'], "*", $sublink['query']);
	$count = 1;
	while($row = $sql-> db_Fetch()){
		$subcat = $row[($sublink['fieldid'])];
		$name = $row[($sublink['fieldname'])];
		$subname = $name;  // eliminate old embedded hierarchy from names. (e.g. 'submenu.TopName.name')
		$suburl = str_replace("#",$subcat,$sublink['url']);
		$subicon = ($sublink['fieldicon']) ? $row[($sublink['fieldicon'])] : $link_button;
		$subdiz = ($sublink['fielddiz']) ? $row[($sublink['fielddiz'])] : $link_description;
		$subparent = $_POST['sublink_parent'];

		if($sql2->db_Insert("links", "0, '$subname', '$suburl', '$subdiz', '$subicon', '$link_category', '$count', '$subparent', '$link_open', '$link_class' ")){
			$message .= LAN_CREATED. " ($name)<br />";
		}else{
			$message .= LAN_CREATED_FAILED. " ($name)<br />";
		}
		$count++;
	}

    if($message){
		$ns -> tablerender(LAN_CREATED, $message);
	}
}

if ($incdec_action == 'inc')
{
  $sql->db_Update("links", "link_order=link_order+1 WHERE link_order='".intval($link_order-1)."'");
  $sql->db_Update("links", "link_order=link_order-1 WHERE link_id='".intval($linkid)."'");
}
elseif ($incdec_action =='dec')
{
  $sql->db_Update("links", "link_order=link_order-1 WHERE link_order='".intval($link_order+1)."'");
  $sql->db_Update("links", "link_order=link_order+1 WHERE link_id='".intval($linkid)."'");
}

if (isset($_POST['update']))
{
	foreach ($_POST['link_order'] as $loid)
	{
	  $tmp = explode(".", $loid);
	  $sql->db_Update("links", "link_order=".intval($tmp[1])." WHERE link_id=".intval($tmp[0]));
	}
	foreach ($_POST['link_class'] as $lckey => $lcid)
	{
	 	$sql->db_Update("links", "link_class='".$lcid."' WHERE link_id=".intval($lckey));
	}
	$e107cache->clear("sitelinks");
	$linkpost->show_message(LAN_UPDATED);
}

if (isset($_POST['updateoptions'])) {
	$pref['linkpage_screentip'] = $_POST['linkpage_screentip'];
	$pref['sitelinks_expandsub'] = $_POST['sitelinks_expandsub'];
	save_prefs();
	$e107cache->clear("sitelinks");
	$linkpost->show_message(LCLAN_1);
}

if ($delete == 'main')
{
	if ($sql->db_Select("links", "link_id, link_name, link_order, link_parent", "link_id=".intval($del_id)))
	{
		$row = $sql->db_Fetch();
		$msg = $linkpost->delete_link($row);

		if ($msg)
		{
			$e107cache->clear("sitelinks");
			$linkpost->show_message($msg);
		}
	}
}

if (isset($_POST['add_link'])) {
	$linkpost->submit_link($sub_action, $_POST['link_id']);
	unset($id);
}

$linkArray = $linkpost->getLinks();

if ($action == 'create') {
	$linkpost->create_link($sub_action, $id);
}


if (!e_QUERY || $action == 'main') {
	$linkpost->show_existing_items();
}

if ($action == 'debug')
{
  $linkpost->show_existing_items(TRUE);
}

if ($action == 'opt') {
	$linkpost->show_pref_options();
}

if($action == "sublinks"){
  $linkpost->show_sublink_generator();
}

require_once(e_ADMIN.'footer.php');
exit;

// End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

class links
{
	var $link_total;
	var $aIdOptPrep, $aIdOptData, $aIdOptTest;
	var $debug_dis = FALSE;

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
			$tmp = explode(".",$text);
			switch (count($tmp)) {
			case 3: // submenu.parent.node
				$tmp = $tmp[2]; break;
			case 5: // submenu.parent.midlev.child.node
				$tmp = $tmp[4]; break;
			case 2: // submenu.parent (invalid?)
			default:
				$parentLen = strlen($tmp[1]);
				$tmp = substr($text,8+$parentLen+1); // Skip submenu.parent.
			}
			return $tmp;
		}
		else
		{
			return $text;
		}
	}

	function dropdown($curval="", $lid=0, $indent=0)
	{   // Drop-down list using on the parent_id. :)
		global $linkArray,$id,$sub_action;

		if(0 == $indent) {$ret = "<option value=''>".LINKLAN_3."</option>\n";}
		foreach($linkArray[$lid] as $l)
		{
			$s = ($l['link_id'] == $curval ? " selected='selected' " : "" );
			$thename = $this->linkName($l['link_name']);
			 // prevent making self the parent.
			if ($l['link_id'] == $id) { $thename = "(".$thename.")"; }
			if($sub_action == "sub")
			{
				$thelink = ($l['link_id'] != $lid) ? $l['link_id'] : $l['link_parent'] ;
            }
			else
			{
            	$thelink = ($l['link_id'] != $id) ? $l['link_id'] : $l['link_parent'] ;
			}
			$ret .= "<option value='".$thelink."' {$s}>".str_pad("", $indent*36, "&nbsp;").$thename." </option>\n";

			if(array_key_exists($l['link_id'], $linkArray))
			{
				$ret .= $this->dropdown($curval, $l['link_id'], $indent+1);
			}
		}
		return $ret;
	}


	function existing($id=0, $level=0)
	{
		global $linkArray;
		$ret = "";
		foreach($linkArray[$id] as $l)
		{
			$s = ($l['link_parent'] == $curval ? " selected='selected' " : "" );
			$ret .= $this->display_row($l, $level);
			if(array_key_exists($l['link_id'], $linkArray))
			{
				$ret .= $this->existing($l['link_id'], $level+1);
			}
		}
		return $ret;
	}

	function show_existing_items($dbg_display=FALSE)
	{
		global $sql, $rs, $ns, $tp, $linkArray;
		$this->debug_dis = $dbg_display;

		if (count($linkArray))
		{

			$this->prepIdOpts(); // Prepare the options list for all links
			$text = $rs->form_open("post", e_SELF, "myform_{$link_id}", "", "");
			$text .= "<div style='text-align:center'>
				<table class='fborder' style='".ADMIN_WIDTH."'>
				<colgroup>
      		<col width=\"5%\" />
      		<col width=\"60%\" />
      		<col width=\"15%\" />
      		<col width=\"10%\" />
      		<col width=\"5%\" />
      		<col width=\"5%\" />
				</colgroup>
				<tr>
				<td class='fcaption'>".LCLAN_89."</td>
				<td class='fcaption'>".LCLAN_15."</td>
				<td class='fcaption'>".LAN_OPTIONS."</td>
				<td class='fcaption'>".LCLAN_95."</td>
				<td class='fcaption'>".LCLAN_91."</td>
				<td class='fcaption'>".LAN_ORDER."</td>
				</tr>";
				$text .= $this->existing(0);

			$text .= "<tr>
				<td class='forumheader' colspan='6' style='text-align:center'><input class='button' type='submit' name='update' value='".LAN_UPDATE."' /></td>
				</tr>";
			$text .= "</table></div>";
			$text .= $rs->form_close();
		} else {
			$text .= "<div style='text-align:center'>".LCLAN_61."</div>";
		}
		$ns->tablerender(LCLAN_8, $text);
	}

	function prepIdOpts() {
		for($a = 1; $a <= $this->link_total; $a++) {
			$sTxt = "".$a;
			$this->aIdOptData[] = array('val'=>'|||.'.$a, 'txt'=>$sTxt);	// Later, ||| becomes Id
			$this->aIdOptTest[] = $sTxt;
		}
		$this->aIdOptPrep = $this->prepOpts($this->aIdOptData);
	}

	function display_row($row2, $indent = FALSE)
	{
		global $sql, $rs, $ns, $tp, $linkArray, $previous_cat;
		extract($row2);

		if($link_category > 1 && $link_category != $previous_cat)
		{
        	$text .= "
				<tr>
					<td class='fcaption'>".LCLAN_89."</td>
					<td class='fcaption'>".LCLAN_15." (".LCLAN_12.": ".$link_category.")</td>
					<td class='fcaption'>".LAN_OPTIONS."</td>
					<td class='fcaption'>".LCLAN_95."</td>
					<td class='fcaption'>".LCLAN_91."</td>
					<td class='fcaption'>".LAN_ORDER."</td>
				</tr>";
			$previous_cat = $link_category;
		}

		if(strpos($link_name, "submenu.") !== FALSE || $link_parent !=0) // 'submenu' for upgrade compatibility only.
		{
			$link_name = $this->linkName( $link_name );
		}

		if ($this->debug_dis)
		{
		  $link_name.= ' ['.$link_url.']';
		}

		if ($indent)
		{
		  $subimage = "<img src='".e_IMAGE."admin_images/sublink.png' alt='' />";
		  $subspacer = ($indent > 1) ? " style='padding-left: ".(($indent - 1) * 16)."px'" : "";
		  $subindent = "<td".$subspacer.">".$subimage."</td>";
		}

		$text .= "<tr><td class='forumheader3' style='text-align: center; vertical-align: middle' title='".$link_description."'>";
		$text .= $link_button ? "<img src='".e_IMAGE."icons/".$link_button."' alt='' /> ":
		"";
		$text .= "</td><td class='forumheader3' title='".$link_description."'>
				<table cellspacing='0' cellpadding='0' border='0' style='width: 100%'>
				<tr>
			".$subindent."
				<td style='".($indent ? "" : "font-weight:bold;")."width: 100%'><a href='".$tp->replaceConstants(e_BASE.$row2['link_url'], true, true)."'>".$link_name."</a></td>
				</tr>
				</table>
				</td>";
		$text .= "<td style='text-align:center; white-space: nowrap' class='forumheader3'>";
		$text .= "<a href='".e_SELF."?create.sub.{$link_id}'><img src='".e_IMAGE."admin_images/sublink_16.png' title='".LINKLAN_10."' alt='".LINKLAN_10."' /></a>&nbsp;";
		$text .= "<a href='".e_SELF."?create.edit.{$link_id}'>".ADMIN_EDIT_ICON."</a>&nbsp;";
		$text .= "<input type='image' title='".LAN_DELETE."' name='main_delete_{$link_id}' src='".ADMIN_DELETE_ICON_PATH."' onclick=\"return jsconfirm('".$tp->toJS(LCLAN_58." [ $link_name ]")."') \" />";
		$text .= "</td>";
		$text .= "<td style='text-align:center' class='forumheader3'>".r_userclass("link_class[".$link_id."]", $link_class, "off", "public,guest,nobody,main,member,admin,classes")."</td>";
		$text .= "<td style='text-align:center; white-space: nowrap' class='forumheader3'>";
		$name_suffix = URL_SEPARATOR.$link_id.URL_SEPARATOR.$link_order;
		$text .= "<input name='inc".$name_suffix."' type='image' src='".e_IMAGE."admin_images/up.png' title='".LCLAN_30."' />";
		$text .= "<input name='dec".$name_suffix."' type='image' src='".e_IMAGE."admin_images/down.png' title='".LCLAN_31."' />";
		$text .= "</td>";
		$text .= "<td style='text-align:center' class='forumheader3'>";
		$text .= "<select name='link_order[]' class='tbox'>\n";
		$text .= $this->genOpts( $this->aIdOptPrep, $this->aIdOptTest, $link_order, $link_id );
		$text .= "</select>";
		$text .= "</td>";
		$text .= "</tr>";

	  return $text;
	}


	function show_message($message) {
		global $ns;
		$ns->tablerender(LAN_UPDATE, "<div style='text-align:center'><b>".$message."</b></div>");
	}



	function create_link($sub_action, $id) {
		global $sql, $rs, $ns, $pst,$tp;
		$preset = $pst->read_preset("admin_links");
		extract($preset);

		if ($sub_action == "edit" && !$_POST['submit'])
		{
			if ($sql->db_Select("links", "*", "link_id='$id' "))
			{
				$row = $sql->db_Fetch();
				extract($row);
			}
		}

		if("sub" == $sub_action)
		{
			$link_parent = $id;
		}

		if(strpos($link_name, "submenu.") !== FALSE){  // 'submenu' for upgrade compatibility only.
			$link_name = $this->linkName( $link_name );
		}

        require_once(e_HANDLER."file_class.php");
        $fl = new e_file;

        if($iconlist = $fl->get_files(e_IMAGE."icons/", ".jpg|.gif|.png|.JPG|.GIF|.PNG")){
        	sort($iconlist);
        }

		$text = "<div style='text-align:center'>
			<form method='post' action='".e_SELF."' id='linkform'>
			<table style='".ADMIN_WIDTH."' class='fborder'>";

		$text .= "<tr>
			<td style='width:30%' class='forumheader3'>".LINKLAN_2.": </td>
			<td style='width:70%' class='forumheader3'>
			<select class='tbox' name='link_parent' >";
			$text .= $this->dropdown($link_parent);

		$text .= "</select></td>
			</tr>
			<tr>
			<td style='width:30%' class='forumheader3'>".LCLAN_15.": </td>
			<td style='width:70%' class='forumheader3'>
			<input class='tbox' type='text' name='link_name' size='60' value='$link_name' maxlength='100' />
			</td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".LCLAN_16.": </td>
			<td style='width:70%' class='forumheader3'>
			<input class='tbox' type='text' name='link_url' size='60' value='".$tp->replaceConstants($link_url,TRUE)."' maxlength='200' />";
            if(e_MENU == "debug")
			{
				$text .= $link_url;
			}
			$text .= "
            </td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".LCLAN_17.": </td>
			<td style='width:70%' class='forumheader3'>
			<textarea class='tbox' name='link_description' cols='59' rows='3'>$link_description</textarea>
			</td>
			</tr>

			<tr>
			<td style='width:30%' class='forumheader3'>".LCLAN_18.": </td>
			<td style='width:70%' class='forumheader3'>
			<input class='tbox' type='text' id='link_button' name='link_button' size='42' value='$link_button' maxlength='100' />

					<input class='button' type ='button' style='cursor:pointer' size='30' value='".LCLAN_39."' onclick='expandit(this)' />
			<div id='linkicn' style='display:none;{head}'>";

		foreach($iconlist as $icon)
		{
			$filepath = str_replace(e_IMAGE."icons/","",$icon['path'].$icon['fname']);
			$text .= "<a href=\"javascript:insertext('".$filepath."','link_button','linkicn')\"><img src='".$icon['path'].$icon['fname']."' style='border:0' alt='' /></a> ";
		}

		// 1 = _blank
		// 2 = _parent   not in use.
		// 3 = _top   not in use.
		$linkop[0] = LCLAN_20;  // 0 = same window
		$linkop[1] = LCLAN_23;
		$linkop[4] = LCLAN_24;  // 4 = miniwindow  600x400
		$linkop[5] = LINKLAN_1;  // 5 = miniwindow  800x600

		$text .= "</div></td>
			</tr>
			<tr>
			<td style='width:30%' class='forumheader3'>".LCLAN_19.": </td>
			<td style='width:70%' class='forumheader3'>
			<select name='linkopentype' class='tbox'>\n";
			foreach($linkop as $key=>$val){
				$selectd = ($link_open == $key) ? "selected='selected'" : "";
				$text .= "<option value='$key' $selectd>".$val."</option>\n";
			}

			$text .="</select>
			</td>
			</tr>
			<tr>
			<td style='width:30%' class='forumheader3'>".LCLAN_12.": </td>
			<td style='width:70%' class='forumheader3'>
			<select name='linkrender' class='tbox'>";
			$rentype = array("","Main","Alt","Alt", "Alt");
			for ($i=1; $i<count($rentype); $i++) {
				$sel = ($link_category == $i) ? "selected='selected'" : "";
				$text .="<option value='$i' $sel>$i - ".$rentype[$i]."</option>";
			};

			$text .="</select><span class='smalltext'> ".LCLAN_96." {SITELINKS=flat:[rendertype number]}</span>
			</td>
			</tr>
			<tr>
			<td style='width:30%' class='forumheader3'>".LCLAN_25.":<br /><span class='smalltext'>(".LCLAN_26.")</span></td>
			<td style='width:70%' class='forumheader3'>".r_userclass("link_class", $link_class, "off", "public,guest,nobody,member,main,admin,classes")."
			</td></tr>

			<tr style='vertical-align:top'>
			<td colspan='2' style='text-align:center' class='forumheader'>";
		if ($id && $sub_action == "edit") {
			$text .= "<input class='button' type='submit' name='add_link' value='".LCLAN_27."' />\n
						<input type='hidden' name='link_id' value='$link_id' />";
		} else {
			$text .= "<input class='button' type='submit' name='add_link' value='".LCLAN_28."' />";
		}
		$text .= "</td>
			</tr>
			</table>
			</form>
			</div>";
		$ns->tablerender(LCLAN_29, $text);
	}


	function submit_link($sub_action, $id)
	{
		global $sql, $e107cache, $tp;
		if(!is_object($tp))
		{
		  $tp=new e_parse;
		}

		$id = intval($id);
		$parent_id = ($_POST['link_parent']) ? intval($_POST['link_parent']) : 0;

		$link_name = $tp->toDB($_POST['link_name']);
		$link_url = $tp->createConstants($_POST['link_url']);
		$link_url = str_replace("&","&amp;",$link_url); // xhtml compliant links.

		$link_description = $tp->toDB($_POST['link_description']);
		$link_button = $tp->toDB($_POST['link_button']);
		$link_render = intval($_POST['linkrender']);
		$link_open = intval($_POST['linkopentype']);
		$link_class = $tp->toDB($_POST['link_class']);

		$link_t = $sql->db_Count("links", "(*)");
		if ($id)
		{
		  $sql->db_Update("links", "link_parent='{$parent_id}', link_name='{$link_name}', link_url='{$link_url}', link_description='{$link_description}', link_button= '{$link_button}', link_category='{$link_render}', link_open='{$link_open}', link_class='{$link_class}' WHERE link_id='{$id}'");
			//rename all sublinks to eliminate old embedded 'submenu' etc hierarchy.
		    // this is for upgrade compatibility only. Current hierarchy uses link_parent.
		  $e107cache->clear("sitelinks");
		  $this->show_message(LCLAN_3);
		}
		else
		{
		  $sql->db_Insert("links", "0, '$link_name', '$link_url', '$link_description', '$link_button', ".$link_render.", ".($link_t+1).", ".$parent_id.", ".$link_open.", ".$link_class);
		  $e107cache->clear("sitelinks");
		  $this->show_message(LCLAN_2);
		}
	}



	function show_pref_options() {
		global $pref, $ns;
		$text = "<div style='text-align:center'>
			<form method='post' action='".e_SELF."?".e_QUERY."'>\n
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td style='width:70%' class='forumheader3'>
			".LCLAN_78."<br />
			<span class='smalltext'>".LCLAN_79."</span>
			</td>
			<td class='forumheader3' style='width:30%;text-align:center'>". ($pref['linkpage_screentip'] ? "<input type='checkbox' name='linkpage_screentip' value='1' checked='checked' />" : "<input type='checkbox' name='linkpage_screentip' value='1' />")."
			</td>
			</tr>

			<tr>
			<td style='width:70%' class='forumheader3'>
			".LCLAN_80."<br />
			<span class='smalltext'>".LCLAN_81."</span>
			</td>
			<td class='forumheader3' style='width:30%;text-align:center'>". ($pref['sitelinks_expandsub'] ? "<input type='checkbox' name='sitelinks_expandsub' value='1' checked='checked' />" : "<input type='checkbox' name='sitelinks_expandsub' value='1' />")."
			</td>
			</tr>



			<tr style='vertical-align:top'>
			<td colspan='2' style='text-align:center' class='forumheader'>
			<input class='button' type='submit' name='updateoptions' value='".LAN_UPDATE."' />
			</td>
			</tr>

			</table>
			</form>
			</div>";
		$ns->tablerender(LCLAN_88, $text);
	}



	// Delete link
	// We need to update the 'order' number of other links with the same parentage - may be top level or a sub-level
	function delete_link($linkInfo)
	{
		global $sql;

		if ($sql->db_Select("links", "link_id", "link_order > '{$linkInfo['link_order']}' AND `link_parent`={$linkInfo['link_parent']} "))
		{
			$linkList = $sql->db_getList();
			foreach($linkList as $l)
			{
				$sql->db_Update("links", "link_order = link_order -1 WHERE link_id = '{$l['link_id']}'");
			}
		}


		if ($sql->db_Delete("links", "link_id='".$linkInfo['link_id']."'"))
		{
			// Update orphaned sublinks - just hide them, and make them top level. And delete any obsolete naming while we're there
			$sql->db_Update("links", "link_name = SUBSTRING_INDEX(link_name, '.', -1) , link_parent = '0', link_class='255' WHERE link_parent= '".$linkInfo['link_id']."'");

			return LCLAN_53." #".$linkInfo['link_id']." ".LCLAN_54."<br />";
		}else{
        	return DELETED_FAILED;
		}


	}

// -------------------------- Sub links generator ------------->

function show_sublink_generator()
{
	global $ns,$sql;

    $sublinks = $this->sublink_list();

	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?".e_QUERY."'>\n
	<table style='".ADMIN_WIDTH."' class='fborder'>

	<tr>
	<td style='width:50%' class='forumheader3'>
	".LINKLAN_6."<br />
	</td>
	<td class='forumheader3' style='width:50%;text-align:center'>
	<select name='sublink_type' class='tbox'>\n
	<option value=''></option>";
    foreach($sublinks as $key=>$type){
    	$text .= "<option value='$key'>".$type['title']."</option>\n";
	}
	$text .="</select>\n
	</td>
	</tr>

    	<tr>
	<td style='width:50%' class='forumheader3'>
	".LINKLAN_7."<br />
	</td>
	<td class='forumheader3' style='width:50%;text-align:center'>
	<select name='sublink_parent' class='tbox'>\n
	<option value=''></option>";
    $sql -> db_Select("links", "*", "link_parent='0' ORDER BY link_name ASC");
	while($row = $sql-> db_Fetch()){
		$text .= "<option value='".$row['link_id']."'>".$row['link_name']."</option>\n";
	}
	$text .="</select>\n
	</td>
	</tr>

	<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='generate_sublinks' value='".LINKLAN_5."' />
	</td>
	</tr>

	</table>
	</form>
	</div>";
	$ns->tablerender(LINKLAN_4, $text);
}



function sublink_list($name="")
{
    global $sql,$PLUGINS_DIRECTORY;
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
	$sublink_type['downloads']['url'] =   "download.php?list.#";
	$sublink_type['downloads']['fieldid'] = "download_category_id";
	$sublink_type['downloads']['fieldname'] = "download_category_name";
	$sublink_type['downloads']['fieldicon'] = "download_category_icon";


	if ($sql -> db_Select("plugin", "plugin_path", "plugin_installflag = '1'"))
	{
		while ($row = $sql -> db_Fetch())
		{
			$sublink_plugs[] = $row['plugin_path'];
		}
	}

	foreach ($sublink_plugs as $plugin_id)
	{
		if (is_readable(e_PLUGIN.$plugin_id.'/e_linkgen.php'))
		{
		  	require_once(e_PLUGIN.$plugin_id.'/e_linkgen.php');
		}
	}
    if($name){
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

	$i=0;
	foreach($aData as $aVal)
	{
		$sVal = $aVal['val'];
		$sTxt = $aVal['txt'];
		$sOut="";

		if ($i) $sOut = '>'.$sTxtPrev.'</option>';
		$sOut .= '<option value="'.$sVal.'"';

		$aPrep[$i++] = $sOut;
		$sTxtPrev = $sTxt;
	}
	if ($i)
	{  // terminate final option
		$aPrep[$i] = '>'.$sTxtPrev.'</option>';
	}

	return $aPrep;
}

function genOpts($aPrep, $aTest,$sSelected, $sId)
{
//
// Generate an HTML option string, with one item possibly selected.
// aGen is a prepared array containing the possible values in this form.
// if sSelected matches an aTest entry, that entry is selected.
// aTest can be any array that matches one-for-one with the options
//
// if $sId is nonblank, a global search/replace is done to change all "|||" to $sId.

  $iKey = array_search( $sSelected, $aTest);
  if ($iKey !== FALSE) {
  	$aNew = $aPrep;
  	$aNew[$iKey] .= " selected='selected'";
  	$sOut = implode($aNew);
  }
  else {
		$sOut = implode($aPrep);
	}
	if (strlen($sId)) $sOut = str_replace("|||",$sId,$sOut);
	return $sOut;
}


}

function links_adminmenu()
{
	global $action;
	if ($action == "")
	{
		$action = "main";
	}
	$var['main']['text'] = LCLAN_62;
	$var['main']['link'] = e_SELF;

	$var['create']['text'] = LCLAN_63;
	$var['create']['link'] = e_SELF."?create";

	$var['opt']['text'] = LAN_OPTIONS;
	$var['opt']['link'] = e_SELF."?opt";

	$var['sub']['text'] = LINKLAN_4;
	$var['sub']['link'] = e_SELF."?sublinks";

//	$var['debug']['text'] = "List DB";
//	$var['debug']['link'] = e_SELF."?debug";

	show_admin_menu(LCLAN_68, $action, $var);
}

?>
