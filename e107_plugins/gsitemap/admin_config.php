<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - gsitemap
 *
*/
require_once("../../class2.php");
if(!getperms("P") || !e107::isInstalled('gsitemap'))
{ 
	e107::redirect('admin');
	exit();
}
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");

e107::lan('gsitemap',true);

$gsm = new gsitemap;


class gsitemap
{

	var $message;
	var $error; 
	var $errortext;  
    var $freq_list = array();

	//function gsitemap()
	function __construct()
	{
		$mes = e107::getMessage();

		

		$this->freq_list = array
		(
			"always"	=>	GSLAN_11,
			"hourly"	=>	GSLAN_12,
			"daily"		=>	GSLAN_13,
			"weekly"	=>	GSLAN_14,
			"monthly"	=>	GSLAN_15,
			"yearly"	=>	GSLAN_16,
			"never"		=>	LAN_NEVER
		);

		if(isset($_POST['edit']))
		{
			$this->editSme();
		}

		if(isset($_POST['delete']))
		{
			$this->deleteSme();
		}

		if(isset($_POST['add_link']))
		{
			$this->addLink();
		}

		if(isset($_POST['import_links']))
		{
			$this->importLink();
		}


		if($this->message)
		{
			$mes->addSuccess($this->message);
			// echo "<br /><div style='text-align:center'><b>".$this->message."</b></div><br />";
		}

		if($this->error)
		{
			$mes->addError($this->error); 
			$mes->addDebug($this->errortext);
		}


		if(e_QUERY == "new")
		{
			$this->doForm();
		}
		else if(e_QUERY == "import")
		{
			$this->importSme();
		}
		else if(e_QUERY == "instructions")
		{
			$this->instructions();
		}
		else if(!vartrue($_POST['edit']))
		{
			$this->showList();
		}
	}


	function showList()
	{
		
		$mes 	= e107::getMessage();
		$sql 	= e107::getDb();
		$ns 	= e107::getRender();
		$tp 	= e107::getParser();
		$frm 	= e107::getForm();
		
		$gen = new convert;
		
		$count = $sql->select("gsitemap", "*", "gsitemap_id !=0 ORDER BY gsitemap_order ASC");

		if (!$count)
		{
			$text = "
			<form action='".e_SELF."?import' id='import' method='post'>
			".GSLAN_39."<br /><br />"
			.$frm->admin_button('import',LAN_YES,'submit')."
			</form>";
			
			$mes->addInfo($text);
			
			$ns->tablerender(GSLAN_24, $mes->render());
			return;
		}
		else
		{
			$text = "
			<form action='".e_SELF."' id='display' method='post'>
			<table class='table adminlist'>
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
				<th>".LAN_NAME."</th>
				<th>".LAN_URL."</th>
				<th style='text-align: center'>".GSLAN_27."</th>
				<th style='text-align: center' >".GSLAN_28."</th>
				<th style='text-align: center' >".GSLAN_9."</th>
				<th style='text-align: center'>".LAN_OPTIONS."</th>
				</tr>
				</thead>
				<tbody>
			";

			$glArray = $sql->db_getList();
			foreach($glArray as $row2)
			{
				$datestamp = $gen->convert_date($row2['gsitemap_lastmod'], "short");
				$rowStyle = (vartrue($rowStyle) == "odd") ? "even" : "odd";

				$text .= "<tr class='{$rowStyle}'>
				<td style='; text-align: center;'>".$row2['gsitemap_id'] ."</td>
				<td>".$tp->toHTML($row2['gsitemap_name'],"","defs")."</td>
				<td>".$row2['gsitemap_url']."</td>
				<td style='; text-align: center;'>".$datestamp."</td>
				<td style='; text-align: center;'>".$this->freq_list[($row2['gsitemap_freq'])]."</td>
				<td style='; text-align: center;'>".$row2['gsitemap_priority'] ."</td>

				<td class='center' style='white-space:nowrap'>
				<div>
				<button class='btn btn-default' type='submit' name='edit[{$row2['gsitemap_id']}]' value='edit' alt='".LAN_EDIT."' title='".LAN_EDIT."' style='border:0px' >".ADMIN_EDIT_ICON."</button>
				<button class='btn btn-default btn-secondary action delete' type='submit' name='delete[{$row2['gsitemap_id']}]' value='del' data-confirm='".$tp->toJS(LAN_CONFIRMDEL." [".$row2['gsitemap_name']."]")."' title='".LAN_DELETE."' >".ADMIN_DELETE_ICON."</button>
				</div>
				</td>
				</tr>
				";
			}
		}

		$text .= "</tbody></table>\n</form>";
		
		$ns->tablerender(GSLAN_24, $mes->render(). $text);
	}


	function editSme()
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		$e_idt = array_keys($_POST['edit']);

		if($sql->select("gsitemap", "*", "gsitemap_id='".$e_idt[0]."' "))
		{
			$foo = $sql->fetch();
			$foo['gsitemap_name'] = $tp->toForm($foo['gsitemap_name']);
			$foo['gsitemap_url'] = $tp->toForm($foo['gsitemap_url']);

			$this->doForm($foo);
		}
	}



	function doForm($editArray=FALSE)
	{
		$frm 	= e107::getForm();
		$sql 	= e107::getDb();
		$ns 	= e107::getRender();
		$mes 	= e107::getMessage();
		
		
		$count = $sql->select("gsitemap", "*", "gsitemap_id !=0 ORDER BY gsitemap_id ASC");
		
		$text = "
		<form action='".e_SELF."' id='form' method='post'>
		<table class='table adminform'>
	    <colgroup span='2'>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>
		<tr>
			<td>".LAN_NAME."</td>
			<td>".$frm->text('gsitemap_name', $editArray['gsitemap_name'], '100', array('class' => 'tbox input-text span3'))."</td>
		</tr>

		<tr>
			<td>".LAN_URL."</td>
			<td>".$frm->text('gsitemap_url', $editArray['gsitemap_url'], '100', array('class' => 'tbox input-text span3'))."
				<input class='tbox' type='hidden'  name='gsitemap_lastmod' size='40' value='".time()."' maxlength='100' /></td>
		</tr>
		<tr>
			<td>".GSLAN_10."</td>
			<td>
				<select class='tbox' name='gsitemap_freq'>\n";
				foreach($this->freq_list as $k=>$fq)
				{
					$sel = ($editArray['gsitemap_freq'] == $k)? "selected='selected'" : "";
					$text .= "<option value='$k' $sel>".$fq."</option>\n";
				}

				$text.="</select>
			</td>
		</tr>
		<tr>
			<td>".GSLAN_9."</td>
			<td>
				<select class='tbox' name='gsitemap_priority' >\n";

				for ($i=0.1; $i<1.0; $i=$i+0.1) 
				{
					$sel = ($editArray['gsitemap_priority'] == number_format($i,1))? "selected='selected'" : "";
					$text .= "<option value='".number_format($i,1)."' $sel>".number_format($i,1)."</option>\n";
				};

				$text.="</select></td>
		</tr>
		<tr>
			<td>".LAN_ORDER."</td>
			<td><select name='gsitemap_order' class='tbox'>";

				for($i=0;$i<$count;$i++){
					$text .= $editArray['gsitemap_order'] == $i ? "<option value='".$i."' selected='selected'>".$i."</option>" : "<option value='".$i."'>".$i."</option>";
				}
				$text .= "</select>
		</td>
		</tr>
		<tr>
			<td>".LAN_VISIBILITY."</td>
			<td>".r_userclass("gsitemap_active", $editArray['gsitemap_active'], 'off', "nobody,public,guest,member,admin,classes,language")."		</td>
		</tr>
		</table>
		<div class='buttons-bar center'>";
		if(is_array($editArray))
		{
			$text .= $frm->admin_button('add_link',LAN_UPDATE,'update')."
			<input type='hidden' name='gsitemap_id' value='".$editArray['gsitemap_id']."' />";
		}
		else
		{
			$text .= $frm->admin_button('add_link',LAN_CREATE,'create');
		}

		$text .= "</div>
		</form>
		";

		$ns->tablerender(GSLAN_29, $mes->render(). $text);
	}



	function addLink()
	{
		$log = e107::getAdminLog();
		$sql = e107::getDb();
		$tp  = e107::getParser();
		
		$gmap = array(
			'gsitemap_name' 	=> $tp->toDB($_POST['gsitemap_name']),
			'gsitemap_url' 		=> $tp->toDB($_POST['gsitemap_url']), 
			'gsitemap_priority' => $_POST['gsitemap_priority'],
			'gsitemap_lastmod' 	=> $_POST['gsitemap_lastmod'],
			'gsitemap_freq' 	=> $_POST['gsitemap_freq'],
			'gsitemap_order' 	=> $_POST['gsitemap_order'],
			'gsitemap_active' 	=> $_POST['gsitemap_active'],
			);

		// Check if we are updating an existing record
		if(!empty($_POST['gsitemap_id']))
		{
			// Add where statement to update query 
			$gmap['WHERE'] = "gsitemap_id= ".intval($_POST['gsitemap_id']); 
			
			if($sql->update("gsitemap", $gmap))
			{
				$this->message = LAN_UPDATED; 	
				
				// Log update
				$log->logArrayAll('GSMAP_04', $gmap);
			}
			else
			{
				$this->errortext = $sql->getLastErrorText(); 
				$this->error = LAN_UPDATED_FAILED;
			}
		}
		// Inserting new record
		else
		{
			$gmap['gsitemap_img'] = vartrue($_POST['gsitemap_img'], '');
			$gmap['gsitemap_cat'] = vartrue($_POST['gsitemap_cat'], '');
			
			if($sql->insert('gsitemap', $gmap))
			{
				$this->message = LAN_CREATED;

				// Log insert
				$log->logArrayAll('GSMAP_03',$gmap);
			}
			else
			{
				$this->errortext = $sql->getLastErrorText(); 
				$this->error = LAN_CREATED_FAILED;
			}
		}
	}

	function deleteSme()
	{
		$log = e107::getAdminLog();	
		$sql = e107::getDb();
		
		$d_idt = array_keys($_POST['delete']);

		if($sql->delete("gsitemap", "gsitemap_id='".$d_idt[0]."'"))
		{
			$this->message = LAN_DELETED;
			$log->log_event('GSMAP_02', $this->message.': '.$d_idt[0], E_LOG_INFORMATIVE,'');
		}
		else
		{
			$this->errortext = $sql->getLastErrorText();
			$this->error = LAN_DELETED_FAILED;
		}
	}

	// Import site links
	function importSme()
	{
		global $PLUGINS_DIRECTORY;
		
		$ns 	= e107::getRender();
		$sql 	= e107::getDb();
		//$sql2 	= e107::getDb('sql2'); not used?
		$frm 	= e107::getForm();
		$mes 	= e107::getMessage();
		
		$existing = array(); 
		$sql->select("gsitemap", "*");  
		while($row = $sql->fetch())
		{
			$existing[] = $row['gsitemap_name'];	
		}
			
		
		$importArray = array();

		/* sitelinks ... */
		$sql->select("links", "*", "ORDER BY link_order ASC", "no-where");
		$nfArray = $sql->db_getList();
		foreach($nfArray as $row)
		{
			if(!in_array($row['link_name'], $existing))
			{
				$importArray[] = array('name' => $row['link_name'], 'url' => $row['link_url'], 'type' => GSLAN_1);
			}
		}

		/* custom pages ... */
		$query = "SELECT p.page_id, p.page_title, p.page_sef, p.page_chapter, ch.chapter_sef as chapter_sef, b.chapter_sef as book_sef FROM #page as p
				LEFT JOIN #page_chapters as ch ON p.page_chapter = ch.chapter_id
				LEFT JOIN #page_chapters as b ON ch.chapter_parent = b.chapter_id
				WHERE page_title !='' ORDER BY page_datestamp ASC";
				
		$data = $sql->retrieve($query,true); 
		
		foreach($data as $row)
		{
			if(!in_array($row['page_title'], $existing))
			{
				$route = ($row['page_chapter'] == 0) ? "page/view/other" : "page/view/index";
				
				$importArray[] = array('name' => $row['page_title'], 'url' => e107::getUrl()->create($route, $row, array('full'=>1, 'allow' => 'page_sef,page_title,page_id, chapter_sef, book_sef')), 'type' => "Page");
			}
		}



		/* Plugins.. - currently: forums ... */
		$addons = e107::getAddonConfig('e_gsitemap', null, 'import');

		foreach($addons as $plug => $config)
		{

			foreach($config as $row)
			{
				if(!in_array($row['name'], $existing))
				{
					$importArray[] = $row;
				}
			}

		}

		$editArray = $_POST;

		$text = "
		<form action='".e_SELF."' id='form' method='post'>
		<table class='table adminlist table-striped table-condensed'>
		<colgroup>
			<col class='center' style='width:5%;' />
			<col style='width:15%' />
			<col style='width:40%' />
			<col style='width:40%' />
		</colgroup>
		<thead>
			<tr>
			<th class='center'>".GSLAN_2."</th>
			<th>".LAN_TYPE."</th>
			<th>".LAN_NAME."</th>
			<th>".LAN_URL."</th>
		</tr>
		</thead>
		<tbody>
		";

		foreach($importArray as $k=>$ia)
		{
			$id = 'gs-'.$k;
			$text .= "
			<tr>
				<td class='center'><input id='".$id."' type='checkbox' name='importid[]' value='".$ia['name']."^".$ia['url']."^".$ia['type']."' /></td>
				<td><label for='".$id."'>".$ia['type']."</label></td>
				<td>".$ia['name']."</td>
				<td><span class='smalltext'>".str_replace(SITEURL,"",$ia['url'])."</span></td>
			</tr>
			";
		}

		$text .= "
		<tr>
		<td colspan='4' class='center'>
		<div class='buttons-bar'> ".GSLAN_8." &nbsp; ".GSLAN_9." :&nbsp;<select class='tbox' name='import_priority' >\n";

		for ($i=0.1; $i<1.0; $i=$i+0.1) 
		{
			$sel = (vartrue($editArray['gsitemap_priority']) == number_format($i,1))? "selected='selected'" : "";
			$text .= "<option value='".number_format($i,1)."' $sel>".number_format($i,1)."</option>\n";
		}

		$text.="</select>&nbsp;&nbsp;&nbsp;".GSLAN_10."

		<select class='tbox' name='import_freq' >\n";
		foreach($this->freq_list as $k=>$fq)
		{
			$sel = (vartrue($editArray['gsitemap_freq']) == $k)? "selected='selected'" : "";
			$text .= "<option value='{$k}' {$sel}>{$fq}</option>\n";
		}

		$text .= "</select> <br /><br />

		</div>
		
		</td>
		</tr>
		</tbody>
		</table>
		<div class='buttons-bar center'>
		".
			$frm->admin_button('import_links',GSLAN_18,'submit')."
		</div>
		</form>
		";

		$ns->tablerender(GSLAN_7, $mes->render(). $text);
	}



	function importLink()
	{
		$sql 	= e107::getDb();
		$tp 	= e107::getParser();
		$log 	= e107::getAdminLog();
		
		foreach($_POST['importid'] as $import)
		{
			list($name, $url, $type) = explode("^", $import);
			
			$name 	= $tp->toDB($name);
			$url 	= $tp->toDB($url);
			
			$sql->insert("gsitemap", "0, '$name', '$url', '".time()."', '".$_POST['import_freq']."', '".$_POST['import_priority']."', '$type', '0', '', '0' ");
		}

		$this->message = count($_POST['importid'])." link(s) imported.";
		$log->log_event('GSMAP_01',$this->message, E_LOG_INFORMATIVE,'');
	}



	function instructions()
	{
		$mes = e107::getMessage();
		$ns = e107::getRender();
		
		
		$LINK_1 = "https://www.google.com/accounts/ServiceLogin?service=sitemaps";
		$LINK_2 = "http://www.google.com/support/webmasters/?hl=en";
		
		$srch[0] = "[URL]";
		$repl[0] = "<a href='".$LINK_1."'>".$LINK_1."</a>";
		
		$srch[1] = "[URL2]";
		$repl[1] = "<blockquote><b>".SITEURL."gsitemap.php</b></blockquote>";
		
		$srch[2] = "[";
		$repl[2] = "<a href='".e_ADMIN."prefs.php'>";
		
		$srch[3] = "]";
		$repl[3] = "</a>";		
		
		$text = "<b>".GSLAN_33."</b><br /><br />
		<ul>
			<li>".GSLAN_34."</li>
			<li>".GSLAN_35."</li>
			<li>".GSLAN_36."</li>
			<li>".str_replace($srch,$repl,GSLAN_37)."</li>
			<li>".str_replace("[URL]","<a href='".$LINK_2."'>".$LINK_2."</a>",GSLAN_38)."</li>
		<ul>
		";

		$ns->tablerender(GSLAN_32, $mes->render(). $text);
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
	
	show_admin_menu(LAN_PLUGIN_GSITEMAP_NAME, $action, $var);
}

