<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - gsitemap
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/gsitemap/admin_config.php,v $
 * $Revision: 1.7 $
 * $Date: 2009-07-17 07:53:13 $
 * $Author: e107coders $
 *
*/
require_once("../../class2.php");
if(!getperms("P") || !plugInstalled('gsitemap'))
{ 
	header("location:".e_BASE."index.php"); 
	exit();
}
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");
include_lan(e_PLUGIN."gsitemap/languages/".e_LANGUAGE."_admin_gsitemap.php");

$gsm = new gsitemap;



class gsitemap
{

	var $message;
    var $freq_list = array();

	function gsitemap()
	{
		/* constructor */

		$this->freq_list = array
		(
			"always"	=>	GSLAN_11,
			"hourly"	=>	GSLAN_12,
			"daily"		=>	GSLAN_13,
			"weekly"	=>	GSLAN_14,
			"monthly"	=>	GSLAN_15,
			"yearly"	=>	GSLAN_16,
			"never"		=>	GSLAN_17
		);

		if(isset($_POST['edit']))
		{
			$this -> editSme();
		}

		if(isset($_POST['delete']))
		{
			$this -> deleteSme();
		}

		if(isset($_POST['add_link']))
		{
			$this -> addLink();
		}

		if(isset($_POST['import_links']))
		{
			$this -> importLink();
		}


		if($this -> message)
		{
			echo "<br /><div style='text-align:center'><b>".$this -> message."</b></div><br />";
		}


		if(e_QUERY == "new")
		{
			$this -> doForm();
		}
		else if(e_QUERY == "import")
		{
			$this -> importSme();
		}
		else if(e_QUERY == "instructions")
		{
			$this -> instructions();
		}
		else if(!$_POST['edit'])
		{
			$this -> showList();
		}
	}


	function showList()
	{
		global $sql, $ns, $tp;
		$gen = new convert;
		$count = $sql -> db_Select("gsitemap", "*", "gsitemap_id !=0 ORDER BY gsitemap_order ASC");

		$text = "<div style='text-align:center'>

		";

		if (!$count)
		{
			$text .= "
			<form action='".e_SELF."?import' id='import' method='post'>
			".GSLAN_39."
			<input class='button' type='submit' name='import' value='".LAN_YES."' />
			</form>";
			$ns -> tablerender("<div style='text-align:center'>".GSLAN_40."</div>", $text);
			require_once(e_ADMIN."footer.php");
			exit;
		}
		else
		{
			$text .= "

			<form action='".e_SELF."' id='display' method='post'>
			<table class='adminlist' cellspacing='0' cellpadding='0'>
            	<colgroup span='2'>
					<col style='width:5%' />
					<col style='width:10%' />
					<col style='width:35%' />
					<col style='width:20%' />
					<col style='width:10%' />
					<col style='width:10%' />
					<col style='width:10%' />
				</colgroup>
                <thead>
				<tr class='first last' >
				<th style='text-align: center;'>Id</th>
				<th>".GSLAN_25."</th>
				<th>".GSLAN_26."</th>
				<th style='text-align: center'>".GSLAN_27."</th>
				<th style='text-align: center' >".GSLAN_28."</th>
				<th style='text-align: center' >".GSLAN_9."</th>
				<th style='text-align: center'>".LAN_OPTIONS."</th>
				</tr>
				</thead>
				<tbody>
			";

			$glArray = $sql -> db_getList();
			foreach($glArray as $row2)
			{
				$datestamp = $gen->convert_date($row2['gsitemap_lastmod'], "short");
				$rowStyle = ($rowStyle == "odd") ? "even" : "odd";

				$text .= "<tr class='{$rowStyle}'>
				<td style='; text-align: center;'>".$row2['gsitemap_id'] ."</td>
				<td>".$tp->toHTML($row2['gsitemap_name'],"","defs")."</td>
				<td>".$row2['gsitemap_url']."</td>
				<td style='; text-align: center;'>".$datestamp."</td>
				<td style='; text-align: center;'>".$this->freq_list[($row2['gsitemap_freq'])]."</td>
				<td style='; text-align: center;'>".$row2['gsitemap_priority'] ."</td>

				<td class='center' style='white-space:nowrap'>
				<div>
				<input type='image' name='edit[{$row2['gsitemap_id']}]' value='edit' src='".ADMIN_EDIT_ICON_PATH."' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:0px' />
				<input type='image' name='delete[{$row2['gsitemap_id']}]' value='del' onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [".$row2['gsitemap_name']."]")."') \" src='".ADMIN_DELETE_ICON_PATH."' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' />
				</div>
				</td>
				</tr>
				";
			}
		}

		$text .= "</tbody></table>\n</form><br /><br /><br /></div>";
		$ns -> tablerender("<div style='text-align:center'>".GSLAN_24."</div>", $text);
	}


	function editSme()
	{
		global $sql, $tp;
		$e_idt = array_keys($_POST['edit']);

		if($sql -> db_Select("gsitemap", "*", "gsitemap_id='".$e_idt[0]."' "))
		{
			$foo = $sql -> db_Fetch();
			$foo['gsitemap_name'] = $tp -> toFORM($foo['gsitemap_name']);
			$foo['gsitemap_url'] = $tp -> toFORM($foo['gsitemap_url']);

			$this -> doForm($foo);
		}
	}



	function doForm($editArray=FALSE)
	{
		global $sql,$ns;
		$count = $sql -> db_Select("gsitemap", "*", "gsitemap_id !=0 ORDER BY gsitemap_id ASC");
		$text = "
		<form action='".e_SELF."' id='form' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td style='width:25%' class='forumheader3'>".GSLAN_25."
		<span class='smalltext'>&nbsp;</span></td>
		<td class='forumheader3'>
		<input class='tbox' type='text' style='width:90%' name='gsitemap_name' size='40' value='".$editArray['gsitemap_name']."' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td style='width:25%' class='forumheader3'>".GSLAN_26."
		<span class='smalltext'>&nbsp;</span></td>
		<td class='forumheader3'>
		<input class='tbox' type='text' style='width:90%' name='gsitemap_url' size='40' value='".$editArray['gsitemap_url']."' maxlength='100' />
		<input class='tbox' type='hidden'  name='gsitemap_lastmod' size='40' value='".time()."' maxlength='100' />
		</td>
		</tr>


		<tr>
		<td style='width:25%' class='forumheader3'>".GSLAN_10."
		<span class='smalltext'>&nbsp;</span></td>
		<td class='forumheader3'>
		<select class='tbox' name='gsitemap_freq' >\n";

		foreach($this->freq_list as $k=>$fq){
			$sel = ($editArray['gsitemap_freq'] == $k)? "selected='selected'" : "";
			$text .= "<option value='$k' $sel>".$fq."</option>\n";
		}

		$text.="</select>
		</td>
		</tr>


		<tr>
		<td class='forumheader3'>".GSLAN_9."<br />
		<span class='smalltext'>&nbsp;</span></td>
		<td class='forumheader3'>
		<select class='tbox' name='gsitemap_priority' >\n";

		for ($i=0.1; $i<1.0; $i=$i+0.1) 
		{
			$sel = ($editArray['gsitemap_priority'] == number_format($i,1))? "selected='selected'" : "";
			$text .= "<option value='".number_format($i,1)."' $sel>".number_format($i,1)."</option>\n";
		};

		$text.="</select></td>
		</tr>


		<tr>
		<td class='forumheader3'>".GSLAN_30."</td>
		<td class='forumheader3'><select name='gsitemap_order' class='tbox'>";

		for($i=0;$i<$count;$i++){
			$text .= $editArray['gsitemap_order'] == $i ? "<option value='".$i."' selected='selected'>".$i."</option>" : "<option value='".$i."'>".$i."</option>";
		}
		$text .="
		</select>
		</td>
		</tr>

		<tr>
		<td class='forumheader3'>".GSLAN_31."</td>
		<td class='forumheader3'>";
		$text .= r_userclass("gsitemap_active", $editArray['gsitemap_active'], 'off', "nobody,public,guest,member,admin,classes,language");
		$text .="
		</td>
		</tr>



		<tr style='vertical-align:top'>
		<td colspan='2' style='text-align:center' class='forumheader'>";
		if(is_array($editArray))
		{
			$text .= "<input class='button' type='submit' name='add_link' value='".LAN_UPDATE."' />
			<input type='hidden' name='gsitemap_id' value='".$editArray['gsitemap_id']."' />";
		}
		else
		{
			$text .= "<input class='button' type='submit' name='add_link' value='".LAN_CREATE."' />";
		}

		$text .= "</td>
		</tr>
		</table>
		</form>
		";

		$ns -> tablerender("<div style='text-align:center'>".GSLAN_29."</div>", $text);
	}



	function addLink()
	{
		global $sql, $tp, $admin_log;
		$gmap = array(
			'gsitemap_name' 	=> $tp->toDB($_POST['gsitemap_name']),
			'gsitemap_url' 		=> $tp->toDB($_POST['gsitemap_url']), 
			'gsitemap_priority' => $_POST['gsitemap_priority'],
			'gsitemap_lastmod' 	=> $_POST['gsitemap_lastmod'],
			'gsitemap_freq' 	=> $_POST['gsitemap_freq'],
			'gsitemap_order' 	=> $_POST['gsitemap_order'],
			'gsitemap_active' 	=> $_POST['gsitemap_active']
			);

		if(isset($_POST['gsitemap_id']))
		{
			$this -> message = $sql -> db_UpdateArray("gsitemap", $gmap, ' WHERE gsitemap_id= '.intval($_POST['gsitemap_id'])) ? LAN_UPDATED : LAN_UPDATED_FAILED;
			$admin_log->logArrayAll('GSMAP_04',$gmap);
		}
		else
		{
			$gmap['gsitemap_img'] = $_POST['gsitemap_img'];
			$gmap['gsitemap_cat'] = $_POST['gsitemap_cat'];
			$this -> message = ($sql -> db_Insert('gsitemap',$gmap)) ? LAN_CREATED : LAN_CREATED_FAILED;
			$admin_log->logArrayAll('GSMAP_03',$gmap);
		}
	}


	function deleteSme()
	{
		global $sql, $admin_log;
		$d_idt = array_keys($_POST['delete']);
		$this -> message = ($sql -> db_Delete("gsitemap", "gsitemap_id='".$d_idt[0]."'")) ? LAN_DELETED : LAN_DELETED_FAILED;
		$admin_log->log_event('GSMAP_02',$this->message.': '.$d_idt[0], E_LOG_INFORMATIVE,'');
	}



	// Import site links
	function importSme()
	{
		global $sql, $sql2, $PLUGINS_DIRECTORY, $ns;
		$importArray = array();

		/* sitelinks ... */
		$sql -> db_Select("links", "*", "ORDER BY link_order ASC", "no-where");
		$nfArray = $sql -> db_getList();
		foreach($nfArray as $row)
		{
			if(!$sql -> db_Select("gsitemap", "*", "gsitemap_name='".$row['link_name']."' "))
			{
				$importArray[] = array('name' => $row['link_name'], 'url' => $row['link_url'], 'type' => GSLAN_1);
			}
		}

		/* custom pages ... */
		$sql -> db_Select("page", "*", "ORDER BY page_datestamp ASC", "no-where");
		$nfArray = $sql -> db_getList();
		foreach($nfArray as $row)
		{
			if(!$sql -> db_Select("gsitemap", "*", "gsitemap_name='".$row['page_title']."' "))
			{
				$importArray[] = array('name' => $row['page_title'], 'url' => "page.php?".$row['page_id'],'type' => "Custom Page");
			}
		}



		/* forums ... */
		if(plugInstalled('forum'))
		{ 
			$sql -> db_Select("forum", "*", "forum_parent!='0' ORDER BY forum_order ASC");
			$nfArray = $sql -> db_getList();
			foreach($nfArray as $row)
			{
				if(!$sql -> db_Select("gsitemap", "*", "gsitemap_name='".$row['forum_name']."' "))
				{
					$importArray[] = array('name' => $row['forum_name'], 'url' => $PLUGINS_DIRECTORY."forum/forum_viewforum.php?".$row['forum_id'], 'type' => "Forum");
				}
			}
		}


		/* content pages ... */
		if(plugInstalled('content'))
		{ 	
			$sql -> db_Select("pcontent", "content_id, content_heading", "LEFT(content_parent,1) = '0' ORDER BY content_heading");
			$nfArray = $sql -> db_getList();
			foreach($nfArray as $row)
			{
				$sql2 -> db_Select("pcontent", "content_id, content_heading", "content_parent = '".$row['content_id']."' AND content_refer != 'sa' ORDER BY content_heading");
				$nfArray2 = $sql2 -> db_getList();
				foreach($nfArray2 as $row2)
				{
					if(!$sql -> db_Select("gsitemap", "*", "gsitemap_name='".$row2['content_heading']."' "))
					{
						$importArray[] = array('name' => $row2['content_heading'], 'url' => $PLUGINS_DIRECTORY."content/content.php?content.".$row2['content_id'], 'type' => $row['content_heading']);
					}
				}
			}
		}


		$text = "
		<form action='".e_SELF."' id='form' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td colspan='4' style='text-align:center' class='forumheader'><b>".GSLAN_6."</b></td>
		</tr>

		<tr>
		<td style='width:5%; text-align: center;' class='forumheader'>".GSLAN_2."</td>
		<td style='width:15%' class='forumheader'>".GSLAN_3."</td>
		<td style='width:40%' class='forumheader'>".GSLAN_4."</td>
		<td style='width:40%' class='forumheader'>".GSLAN_5."</td>
		</tr>
		";

		foreach($importArray as $ia)
		{
			$text .= "
			<tr>
			<td style='width:5%; text-align: center;' class='forumheader3'><input type='checkbox' name='importid[]' value='".$ia['name']."^".$ia['url']."^".$ia['type']."' /></td>
			<td style='width:15%' class='forumheader3'>".$ia['type']."</td>
			<td style='width:40%' class='forumheader3'>".$ia['name']."</td>
			<td style='width:40%' class='forumheader3'><span class='smalltext'>".str_replace(SITEURL,"",$ia['url'])."</span></td>
			</tr>
			";
		}

		$text .= "
		<tr>
		<td colspan='4' style='text-align:center' class='forumheader'>
		<div> ".GSLAN_8." &nbsp; ".GSLAN_9." :&nbsp;<select class='tbox' name='import_priority' >\n";

		for ($i=0.1; $i<1.0; $i=$i+0.1) 
		{
			$sel = ($editArray['gsitemap_priority'] == number_format($i,1))? "selected='selected'" : "";
			$text .= "<option value='".number_format($i,1)."' $sel>".number_format($i,1)."</option>\n";
		};

		$text.="</select>&nbsp;&nbsp;&nbsp;".GSLAN_10."


		<select class='tbox' name='import_freq' >\n";
		foreach($this->freq_list as $k=>$fq)
		{
			$sel = ($editArray['gsitemap_freq'] == $k)? "selected='selected'" : "";
			$text .= "<option value='{$k}' {$sel}>{$fq}</option>\n";
		}

		$text.="</select> <br /><br />

		</div>
		<input class='button' type='submit' name='import_links' value='".GSLAN_18."' />
		</td>
		</tr>
		</table>
		</form>
		";

		$ns -> tablerender("<div style='text-align:center'>".GSLAN_7."</div>", $text);
	}



	function importLink()
	{
		global $sql, $tp, $admin_log;
		foreach($_POST['importid'] as $import)
		{
			list($name, $url, $type) = explode("^", $import);
			$name = $tp -> toDB($name);
			$url = $tp -> toDB($url);
			$sql -> db_Insert("gsitemap", "0, '$name', '$url', '".time()."', '".$_POST['import_freq']."', '".$_POST['import_priority']."', '$type', '0', '', '0' ");
		}
		$this -> message = count($_POST['importid'])." link(s) imported.";
		$admin_log->log_event('GSMAP_01',$this->message, E_LOG_INFORMATIVE,'');
	}



	function instructions()
	{
		global $ns, $PLUGINS_DIRECTORY;

		$text = "<b>".GSLAN_33."</b><br /><br />
		<ul>
		<li>".GSLAN_34."</li>
		<li>".GSLAN_35."</li>
		<li>".GSLAN_36."</li>
		<li>".GSLAN_37."</li>
		<li>".GSLAN_38."</li>
		<ul>
		";

		$ns -> tablerender("<div style='text-align:center'>".GSLAN_32."</div>", $text);
	}

}


require_once(e_ADMIN."footer.php");


function admin_config_adminmenu() 
{
	$action = (e_QUERY) ? e_QUERY : "list";
    $var['list']['text'] = GSLAN_20;
	$var['list']['link'] = e_SELF;
	$var['list']['perm'] = "7";
	$var['instructions']['text'] = GSLAN_21 ;
	$var['instructions']['link'] = e_SELF."?instructions";
	$var['instructions']['perm'] = "7";
    $var['new']['text'] = GSLAN_22 ;
	$var['new']['link'] = e_SELF."?new";
	$var['new']['perm'] = "7";
	$var['import']['text'] = GSLAN_23;
	$var['import']['link'] = e_SELF."?import";
	$var['import']['perm'] = "0";
	show_admin_menu(GSLAN_19, $action, $var);
}

?>
