<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/links.php,v $
|     $Revision: 1.37 $
|     $Date: 2009-11-28 15:34:45 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("I")) 
{
	header("location:".e_BASE."index.php");
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_links.php');


class links_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'links_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'links_admin_form_ui',
			'uipath' 		=> null
		)				
	);	

	protected $adminMenu = array(
		'main/list'		=> array('caption'=> LCLAN_62, 'perm' => 'I'),
		'main/create' 	=> array('caption'=> LCLAN_63, 'perm' => 'I'),
		'main/prefs' 	=> array('caption'=> LAN_OPTIONS, 'perm' => 'I'),
		'main/sublinks'	=> array('caption'=> LINKLAN_4, 'perm' => 'I')		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'links';
}

class links_admin_ui extends e_admin_ui
{	
		protected $pluginTitle 	= "Site links";
		protected $pluginName 	= 'core';
		protected $table 		= "links";		
		protected $listQry 		= "SELECT * FROM #links ORDER BY link_category,link_order, link_id ASC"; // without any Order or Limit. 
		protected $pid 			= "link_id";
		protected $perPage 		= 15;
		protected $batchDelete 	= true;
				
		protected $fields = array(
			'checkboxes' 		=> array('title'=> '',							'width' => '3%','forced' => true,'thclass' => 'center first','class' => 'center first'),
			'link_button'		=> array('title'=> LAN_ICON, 	'type'=>'icon',			'width'=>'5%', 'thclass' => 'center', 'class'=>'center'),		
			'link_id'			=> array('title'=> ID, 			'nolist'=>TRUE),
			'link_name'	   		=> array('title'=> LCLAN_15,	'width'=>'auto','type'=>'method'),
			'link_parent' 		=> array('title'=> 'Sublink of', 'type' => 'dropdown', 'width' => 'auto', 'batch'=>true, 'filter'=>true, 'thclass' => 'left first'),         
			'link_url'	   		=> array('title'=> LCLAN_93, 	'width'=>'auto', 'type'=>'text'),
			'link_class' 		=> array('title'=> LAN_USERCLASS, 	'type' => 'userclass', 'batch'=>true, 'filter'=>true, 'width' => 'auto'),	
			'link_description' 	=> array('title'=> LCLAN_17, 		'type' => 'bbarea', 'method'=>'tinymce_plugins', 'width' => 'auto'),	
			'link_category' 	=> array('title'=> LCLAN_12, 		'type' => 'dropdown', 'batch'=>true, 'filter'=>true, 'width' => 'auto'),
			'link_order' 		=> array('title'=> LAN_ORDER, 		'type' => 'text', 'width' => 'auto'),
			'link_open'			=> array('title'=> LCLAN_19, 		'type' => 'dropdown', 'width' => 'auto', 'batch'=>true, 'filter'=>true, 'thclass' => 'left first'),
			'link_function'		=> array('title'=> 'Function', 		'type' => 'method', 'data'=>'str', 'width' => 'auto', 'thclass' => 'left first'),						
		//	'increment' 		=> array('title'=> LCLAN_91,			'width' => '3%','forced' => true,'thclass' => 'center'),	  	
			'options' 			=> array('title'=> LAN_OPTIONS, 		'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class'=>'center')
		);
		
		protected $fieldpref =  array('checkboxes','link_id','link_name','link_class','link_order','options');

		protected $prefs = array(
			'linkpage_screentip'	=> array('title'=>LCLAN_78,	'type'=>'boolean', 'help'=>LCLAN_79),
			'sitelinks_expandsub'	=> array('title'=>LCLAN_80,	'type'=>'boolean', 'help'=>LCLAN_81)
		);
		
		
		//FIXME - need to use linkArray data instead of $listQry-returned data	
		protected $linkArray = array();
		
	
	function init()
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();
			
		$this->getLinks();
		
		$query = "SELECT link_id,link_name FROM #links ORDER BY link_name";
		$this->linkParent[0] = '-';
		$sql->db_Select_gen($query);
		while($row = $sql->db_Fetch())
		{
			$id = $row['link_id'];
			$this->linkParent[$id] = $row['link_name'];
		}
				
		$tmp = e107::getAddonConfig('e_sitelink','sitelinks');

		foreach($tmp as $cat=> $array)
		{
			$func = array();
			foreach($array as $val)
			{
				$newkey = $cat.'::'.$val['function'];
				$func[$newkey] = $val['name'];
			}
			$this->linkFunctions[$cat] = $func;
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
			1 => LCLAN_23, // new window
			4 => LCLAN_24, // 4 = miniwindow  600x400
			5 => LINKLAN_1 // 5 = miniwindow  800x600
		);
		
		$sitelinksTemplates = e107::getLayouts(null, 'sitelinks');
		
		//TODO review. 
		$this->setDropDown('link_parent',$this->linkParent);
		$this->setDropDown('link_category',$this->linkCategory);
		$this->setDropDown('link_open',$this->linkOpen);
	//	$this->setDropDown('link_function',$this->linkFunctions);
		// $this->setDropDown('link_template',$sitelinksTemplates);

		
		if(isset($_POST['generate_sublinks']) && isset($_POST['sublink_type']) && $_POST['sublink_parent'] != "")
		{
			$this->generateSublinks();	
		}
	}




	
	
	/**
	 * Get linklist in it's proper order. 
	 * @return
	 */
	function getLinks()
	{
		$sql = e107::getDb();
		
		if($this->link_total = $sql->db_Select("links", "*", "ORDER BY link_category,link_order, link_id ASC", "nowhere"))
		{
			while($row = $sql->db_Fetch())
			{
				$ret[$row['link_parent']][] = $row;
			}
		}
		
		$this->linkArray = $ret;
		
		// print_a($this->linkArray);
	}






	function sublinksPage()
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
		//$e107->ns->tablerender(LINKLAN_4, $emessage->render().$text);
		echo  $emessage->render().$text;
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




		
	function generateSublinks()
	{
		$subtype = $_POST['sublink_type'];
			$sublink = $this->sublink_list($subtype);
		
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
				
		
				$insert_array = array(				
						'link_name'			=> $subname,
						'link_url'			=> $suburl,
						'link_description'	=> $subdiz,
						'link_button'		=> $subicon,
						'link_category'		=> $link_category,
						'link_order'		=> $count,
						'link_parent'		=> $subparent,
						'link_open'			=> $link_open,
						'link_class'		=> $link_class,
						'link_function'		=> ''
				);				
		
				if($sql2->db_Insert("links",$insert_array))
				{
					$message .= LAN_CREATED." ({$name})[!br!]";
					$mes->add(LAN_CREATED." ({$name})", E_MESSAGE_SUCCESS);
				} else
				{
					$message .= LAN_CREATED_FAILED." ({$name})[!br!]";
					$mes->add(LAN_CREATED_FAILED." ({$name})", E_MESSAGE_ERROR);
				}
				$count++;
			}
		
			if($message)
			{
				// sitelinks_adminlog('01', $message); // 'Sublinks generated'
			}
	}	
}


class links_admin_form_ui extends e_admin_form_ui
{
		
	function init()
	{
				
	}
	
	
	function link_name($curVal,$mode,$parm)
	{		
		//FIXME - I need access to the full array of $row, so I can check for the value of $link_parent;
		if($mode == "read")
		{
			return "<img src='".e_IMAGE."generic/branchbottom.gif' alt='' />".$curVal;		
		}
		else
		{
			return $curVal;	
		}
		
	}



	function link_function($curVal,$mode)
	{
		if($mode == 'read')
		{
			return $curVal; //  $this->linkFunctions[$curVal];
		}
		
		if($mode == 'write')
		{
			return $this->selectbox('link_function',$this->linkFunctions,$curVal,array('default'=> "(".LAN_OPTIONAL.")"));
		}
		
		else
		{
			return $this->linkFunctions;
		}
	}
	

}

new links_admin();


require_once(e_ADMIN."auth.php");

e107::getAdminUI()->runPage();
// TODO Link Preview. (similar to userclass preview)
/*
echo "<h2>Preview (To-Do)</h2>";
echo $tp->parseTemplate("{SITELINKS_ALT}");
*/
require_once(e_ADMIN."footer.php");
exit;







?>