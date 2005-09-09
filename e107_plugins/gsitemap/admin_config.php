<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/gsitemap/admin_config.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-09-09 15:16:03 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); }
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");

$gsm = new gsitemap;


/* ERIC  - PLEASE SEE LINE 341! */




class gsitemap
{

	var $message;

/*+----------------------#######################################################################################---------------------+*/

	function gsitemap()
	{
		/* constructor */

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

/*+----------------------#######################################################################################---------------------+*/

	function showList()
	{
		global $sql,$ns,$tp;
		$gen = new convert; 
		$count = $sql -> db_Select("gsitemap", "*", "gsitemap_id !=0 ORDER BY gsitemap_id ASC");

		$text = "<div style='text-align:center'>
		
		";

		if (!$count)
		{
			$text .= "
			<form action='".e_SELF."?import' id='import' method='post'>
			No links in sitemap - import sitelinks?
			<input class='button' type='submit' name='import' value='".LAN_YES."' />
			</form>";
			$ns -> tablerender("<div style='text-align:center'>Google Sitemap Entries</div>", $text);
			require_once(e_ADMIN."footer.php");
			exit;
		}
		else
		{

			$text .= "
			
			<form action='".e_SELF."' id='display' method='post'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			
			<tr>
			<td style='width:5%; text-align: center;' class='fcaption'>Id</td>
			<td style='width:10%' class='fcaption'>Name</td>
			<td style='width:40%' class='fcaption'>URL</td>
			<td style='width:20%; text-align: center;' class='fcaption'>Lastmod</td>
			<td style='width:10%; text-align: center;' class='fcaption'>Freq.</td>
			<td style='width:10%; text-align: center;' class='fcaption'>Priority</td>
			<td style='width:5%; text-align: center;' class='fcaption'>".LAN_OPTIONS."</td>
			</tr>
			";

			$glArray = $sql -> db_getList();
			foreach($glArray as $row2)
			{

				$datestamp = $gen->convert_date($row2['gsitemap_lastmod'], "short");

				$text .= "<tr>
				<td class='forumheader3' style='; text-align: center;'>".$row2['gsitemap_id'] ."</td>
				<td class='forumheader3'>".$row2['gsitemap_name']."</td>
				<td class='forumheader3'>".str_replace(SITEURL,"",$row2['gsitemap_url'])."</td>
				<td class='forumheader3' style='; text-align: center;'>".$datestamp."</td>
				<td class='forumheader3' style='; text-align: center;'>".$row2['gsitemap_freq'] ."</td>
				<td class='forumheader3' style='; text-align: center;'>".$row2['gsitemap_priority'] ."</td>

				<td style='width:50px;white-space:nowrap' class='forumheader3'>
				<div>
				<input type='image' name='edit[{$row2['gsitemap_id']}]' value='edit' src='".e_IMAGE."admin_images/edit_16.png' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:0px' />
				<input type='image' name='delete[{$row2['gsitemap_id']}]' value='del' onclick=\"return jsconfirm('".$tp->toJS(LAN_CONFIRMDEL." [".$row2['gsitemap_name']."]")."') \" src='".e_IMAGE."admin_images/delete_16.png' alt='".LAN_DELETE."' title='".LAN_DELETE."' style='border:0px' />
				</div>
				</td>
				</tr>
				";
			}
		}

		$text .= "</table>\n</form><br /><br /><br /></div>";
		$ns -> tablerender("<div style='text-align:center'>Google Sitemap Entries</div>", $text);
	}

/*+----------------------#######################################################################################---------------------+*/

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

/*+----------------------#######################################################################################---------------------+*/

	function doForm($editArray=FALSE)
	{
		global $sql,$ns;
		$count = $sql -> db_Select("gsitemap", "*", "gsitemap_id !=0 ORDER BY gsitemap_id ASC");
		$text = "
		<form action='".e_SELF."' id='form' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td style='width:25%' class='forumheader3'>Name
		<span class='smalltext'></span></td>
		<td class='forumheader3'>
		<input class='tbox' type='text' style='width:90%' name='gsitemap_name' size='40' value='".$editArray['gsitemap_name']."' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td style='width:25%' class='forumheader3'>URL
		<span class='smalltext'></span></td>
		<td class='forumheader3'>
		<input class='tbox' type='text' style='width:90%' name='gsitemap_url' size='40' value='".$editArray['gsitemap_url']."' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td style='width:25%' class='forumheader3'>LastMod
		<span class='smalltext'></span></td>
		<td class='forumheader3'>
		<input class='tbox' type='text'  name='gsitemap_lastmod' size='40' value='".$editArray['gsitemap_lastmod']."' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td style='width:25%' class='forumheader3'>Change Freq.
		<span class='smalltext'></span></td>
		<td class='forumheader3'>
		<select class='tbox' name='gsitemap_freq' >\n";

		$freq_list = array("always","hourly","daily","weekly","monthly","yearly","never");

		foreach($freq_list as $fq){
			$sel = ($editArray['gsitemap_freq'] == $fq)? "selected='selected'" : "";
			$text .= "<option value='$fq' $sel>$fq</option>\n";
		}

		$text.="</select>
		</td>
		</tr>


		<tr>
		<td class='forumheader3'>Priority<br />
		<span class='smalltext'></span></td>
		<td class='forumheader3'>
		<select class='tbox' name='gsitemap_priority' >\n";

		for ($i=0.1; $i<1.0; $i=$i+0.1) {
			$sel = ($editArray['gsitemap_priority'] == number_format($i,1))? "selected='selected'" : "";
			$text .= "<option value='".number_format($i,1)."' $sel>".number_format($i,1)."</option>\n";
		};

		$text.="</select></td>
		</tr>


		<tr>
		<td class='forumheader3'>Display Order</td>
		<td class='forumheader3'><select name='gsitemap_order' class='tbox'>";

		for($i=0;$i<$count;$i++){
			$text .= $meet_order == $i ? "<option value='".$i."' selected='selected'>".$i."</option>" : "<option value='".$i."'>".$i."</option>";
		}
		$text .="
		</select>
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

		$ns -> tablerender("<div style='text-align:center'>Google Sitemap Configuration</div>", $text);


	}

/*+----------------------#######################################################################################---------------------+*/

	function addLink()
	{
		global $sql, $tp;
		$gsitemap_name = $tp -> toDB($_POST['gsitemap_name']);
		$gsitemap_url = $tp -> toDB($_POST['gsitemap_url']);
		if(!strstr($gsitemap_url, "http"))
		{
			$gsitemap_url = SITEURL.$gsitemap_url;
		}
		if(isset($_POST['gsitemap_id']))
		{
			$this -> message = $sql -> db_Update("gsitemap", "gsitemap_name='$gsitemap_name', gsitemap_url='$gsitemap_url', gsitemap_priority='".$_POST['gsitemap_priority']."', gsitemap_lastmod='".$_POST['gsitemap_lastmod']."', gsitemap_freq= '".$_POST['gsitemap_freq']."', gsitemap_order='".$_POST['gsitemap_order']."' WHERE gsitemap_id='".$_POST['gsitemap_id']."' ") ? LAN_UPDATED : LAN_UPDATED_FAILED;
		}
		else
		{
			$this -> message = ($sql -> db_Insert("gsitemap", "0, '".$_POST['gsitemap_name']."', '".$_POST['gsitemap_url']."', '".$_POST['gsitemap_lastmod']."', '".$_POST['gsitemap_freq']."', '".$_POST['gsitemap_priority']."', '".$_POST['meet_country']."', '".$_POST['meet_img']."', '".$_POST['meet_language']."', '".$_POST['gsitemap_order']."' ")) ? LAN_CREATED : LAN_CREATED_FAILED;
		}
	}

/*+----------------------#######################################################################################---------------------+*/

	function deleteSme()
	{
		global $sql;
		$d_idt = array_keys($_POST['delete']);
		$this -> message = ($sql -> db_Delete("gsitemap", "gsitemap_id='".$d_idt[0]."'")) ? LAN_DELETED : LAN_DELETED_FAILED;
	}

/*+----------------------#######################################################################################---------------------+*/

	function importSme()
	{
		global $sql, $PLUGINS_DIRECTORY, $ns;
		$importArray = array();

		/* sitelinks ... */
		$sql -> db_Select("links", "*", "ORDER BY link_order ASC", "no-where");
		$nfArray = $sql -> db_getList();
		foreach($nfArray as $row)
		{
			if(!$sql -> db_Select("gsitemap", "*", "gsitemap_name='".$row['link_name']."' "))
			{
				$importArray[] = array('name' => $row['link_name'], 'url' => SITEURL.$row['link_url'], 'type' => "Site Link");
			}
		}

		/* custom pages ... */
		$sql -> db_Select("page", "*", "ORDER BY page_datestamp ASC", "no-where");
		$nfArray = $sql -> db_getList();
		foreach($nfArray as $row)
		{
			if(!$sql -> db_Select("gsitemap", "*", "gsitemap_name='".$row['page_title']."' "))
			{
				$importArray[] = array('name' => $row['page_title'], 'url' => SITEURL."page.php?".$row['page_id'], 'type' => "Custom Page");
			}
		}

		

		/* forums ... */
		$sql -> db_Select("forum", "*", "forum_parent!='0' ORDER BY forum_order ASC");
		$nfArray = $sql -> db_getList();
		foreach($nfArray as $row)
		{
			if(!$sql -> db_Select("gsitemap", "*", "gsitemap_name='".$row['forum_name']."' "))
			{
				$importArray[] = array('name' => $row['forum_name'], 'url' => SITEURL.$PLUGINS_DIRECTORY."forum/forum_viewforum.php?".$row['forum_id'], 'type' => "Forum");
			}
		}


		/* content pages ... */


		/*	 Eric - see above examples for correct method - thankyou!	*/


		/* end */


		
		$text = "
		<form action='".e_SELF."' id='form' method='post'>
		<table style='".ADMIN_WIDTH."' class='fborder'>

		<tr>
		<td colspan='4' style='text-align:center' class='forumheader'><b>Tick links to mark them for import ...</b></td>
		</tr>

		<tr>
		<td style='width:5%; text-align: center;' class='forumheader'>Import?</td>
		<td style='width:15%' class='forumheader'>Type</td>
		<td style='width:40%' class='forumheader'>Name</td>
		<td style='width:40%' class='forumheader'>Url</td>
		</tr>
		";

		foreach($importArray as $ia)
		{
			$text .= "
			<tr>
			<td style='width:5%; text-align: center;' class='forumheader3'><input type='checkbox' name='importid[]' value='".$ia['name']."^".$ia['url']."^".$ia['type']."' /></td>
			<td style='width:15%' class='forumheader3'>".$ia['type']."</td>
			<td style='width:40%' class='forumheader3'>".$ia['name']."</td>
			<td style='width:40%' class='forumheader3'><span class='smalltext'>".$ia['url']."</span></td>
			</tr>
			";
		}
		$text .= "
		<tr>
		<td colspan='4' style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='import_links' value='Import ticked links' />
		</td>
		</tr>
		</table>
		</form>
		";

		$ns -> tablerender("<div style='text-align:center'>Import Links</div>", $text);
	}

/*+----------------------#######################################################################################---------------------+*/

	function importLink()
	{
		global $sql, $tp;
		foreach($_POST['importid'] as $import)
		{
			list($name, $url, $type) = explode("^", $import);
			$name = $tp -> toDB($name);
			$url = $tp -> toDB($url);
			$sql -> db_Insert("gsitemap", "0, '$name', '$url', '".time()."', 'always', '0.5', '$type', '0', '', '0' ");
		}
		$this -> message = count($_POST['importid'])." link(s) imported.";
	}

/*+----------------------#######################################################################################---------------------+*/

	function instructions()
	{
		global $ns, $PLUGINS_DIRECTORY;

		$text = "<b>GSiteMap Instructions</b><br /><br />
		<ul>
		<li>First, create the links you wish to have listed in your sitemap. You can import most of your links by clicking the 'Import' button on the right</li>
		<li>If you've chosen to import your links, click 'Import' and then check the links you wish to import</li>
		<li>You can also enter individual links manually by clicking 'Create New Entry'</li>
		<li>Once you have some entries, go to <a href='https://www.google.com/webmasters/sitemaps/stats'>https://www.google.com/webmasters/sitemaps/stats</a> and enter the following URL -> <b>".SITEURL."gsitemap.php</b> - if this url doesn't look right to you, please make sure your site url is correct in admin -> preferences</li>
		<li>For more information on Google Sitemap protocol, go to <a href='http://www.google.com/webmasters/sitemaps/docs/en/protocol.html'>http://www.google.com/webmasters/sitemaps/docs/en/protocol.html</a>.</li>
		<ul>
		";

		$ns -> tablerender("<div style='text-align:center'>How to use Google Sitemaps</div>", $text);

	}

/*+----------------------#######################################################################################---------------------+*/

}


require_once(e_ADMIN."footer.php");


function admin_config_adminmenu() {
	$action = (e_QUERY) ? e_QUERY : "list";
    $var['list']['text'] = "Listing";
	$var['list']['link'] = e_SELF;
	$var['list']['perm'] = "7";
	$var['instructions']['text'] = "Instructions";
	$var['instructions']['link'] = e_SELF."?instructions";
	$var['instructions']['perm'] = "7";
    $var['new']['text'] = "Create New Entry";
	$var['new']['link'] = e_SELF."?new";
	$var['new']['perm'] = "7";
	$var['import']['text'] = "Import";
	$var['import']['link'] = e_SELF."?import";
	$var['import']['perm'] = "0";
	show_admin_menu("Google Sitemap", $action, $var);
}

?>