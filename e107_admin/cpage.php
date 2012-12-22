<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Custom Menus/Pages Administration
 *
 * $URL$
 * $Id$
 *
*/

/**
 *
 * @package     e107
 * @subpackage	admin
 * @version     $Revision$
 * @author      $Author$

 *	Admin-related functions for custom page and menu creation
*/

require_once('../class2.php');

if (!getperms("5|J")) { header('location:'.e_ADMIN.'admin.php'); exit; }

e107::css('inline',"

.e-wysiwyg { height: 400px }
");

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'custom';

require_once(e_HANDLER.'userclass_class.php');
require_once(e_HANDLER.'message_handler.php');
require_once(e_HANDLER.'form_handler.php');
$frm = new e_form(true);
$emessage = eMessage::getInstance();


// $page = new page;

/*
if (e_QUERY)
{
	$tmp        = explode(".", e_QUERY);
	$action     = $tmp[0];
	$sub_action = varset($tmp[1]);
	$id         = intval(varset($tmp[2], 0));
	$from       = intval(varset($tmp[3], 0));
}
*/





/*
if(isset($_POST['delete']) || varset($_POST['etrigger_delete']))
{
	if($_POST['etrigger_delete'])
	{
		$delArray = array_keys($_POST['etrigger_delete']);
	}
	else
	{
		$delArray = array_keys($_POST['delete']);
	}

	foreach($delArray as $pid)
	{
		$page->delete_page($pid);
	}
}
*/


/*
if(!e_QUERY)
{
	if(getperms('5'))
	{
		$page->showExistingPages();
	}
	else
	{
    	$page->showExistingPages('menus');
	}

}
elseif(varset($_GET['action'],'')=='edit')
{
	$action = 'create';
	$sub_action = 'edit';
	$id = intval($_GET['id']);

	$mod 	= (vartrue($_GET['menus'])) ? 'menus' : "";
	$page->createPage($mod);
}
elseif(vartrue($_GET['menus']))
{
	$page->menusPage();
}
else
{
	$function = $action.'Page';
	$page->$function();
}
*/


class page_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'page'		=> array(
			'controller' 	=> 'page_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'page_admin_form_ui',
			'uipath' 		=> null
		),
		'cat'		=> array(
			'controller' 	=> 'page_chapters_ui',
			'path' 			=> null,
			'ui' 			=> 'page_chapters_form_ui',
			'uipath' 		=> null
		),
		'menu'		=> array(
			'controller' 	=> 'page_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'page_admin_form_ui',
			'uipath' 		=> null
		),
		'dialog'		=> array(
			'controller' 	=> 'page_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'page_admin_form_ui',
			'uipath' 		=> null
		)							
	);	
	
	protected $adminMenu = array(
		'page/list'		=> array('caption'=> CUSLAN_48, 'perm' => '5'),
		'page/create' 	=> array('caption'=> CUSLAN_12, 'perm' => '5'),
		'cat/list' 		=> array('caption'=> "List Books/Chapters", 'perm' => '5'), // Create Category. 
		'cat/create' 	=> array('caption'=> "Add Book/Chapter", 'perm' => '5'), // Category List
		
		'menu/list'		=> array('caption'=> CUSLAN_49, 'perm' => 'J'),	
		'menu/create' 	=> array('caption'=> CUSLAN_31, 'perm' => 'J'),
		'page/options'	=> array('caption'=> LAN_OPTIONS, 'perm' => '0')		
	);
	

	protected $adminMenuAliases = array(
		'page/edit'		=> 'page/list',
		'menu/edit'		=> 'menu/list'				
	);	
	
	protected $menuTitle = 'Custom Pages';
}

class page_admin_form_ui extends e_admin_form_ui
{
	
	function page_title($curVal,$mode,$parm)
	{
	
		if($mode == 'read') 
		{
			$id = $this->getController()->getListModel()->get('page_id');
			return "<a href='".e_BASE."page.php?".$id."' >".$curVal."</a>";
		}
			
		if($mode == 'write')
		{
			return;	
		}
			
		if($mode == 'filter')
		{
			return;	
		}
		if($mode == 'batch')
		{
			return;
		}		
	}
	

}

//FIXME - needs a layout similar to the admin sitelinks page. ie. showing chapters as we would 'sublinks'. 

class page_chapters_ui extends e_admin_ui
{
		protected $pluginTitle	= 'Page Chapters';
		protected $pluginName	= 'core';
		protected $table 		= "page_chapters";
		protected $pid			= "chapter_id";
		protected $perPage = 0; //no limit
		protected $batchDelete = false;
		protected $listOrder 	= 'chapter_parent,chapter_order asc'; 
		
		protected $fields = array(
			'checkboxes'				=> array('title'=> '',					'type' => null, 			'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'chapter_id'				=> array('title'=> LAN_ID,				'type' => 'number',			'width' =>'5%', 'forced'=> TRUE, 'readonly'=>TRUE),
         	'chapter_icon' 				=> array('title'=> LAN_ICON,			'type' => 'icon', 			'data' => 'str',		'width' => '100px',	'thclass' => 'center', 'class'=>'center', 'readParms'=>'thumb=60&thumb_urlraw=0&thumb_aw=60','readonly'=>FALSE,	'batch' => FALSE, 'filter'=>FALSE),			       		
         	'chapter_parent' 			=> array('title'=> "Book",				'type' => 'dropdown',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE, 'filter'=>true),                   	
         	'chapter_name' 				=> array('title'=> "Book or Chapter Title",			'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),       
         	'chapter_meta_description'	=> array('title'=> LAN_DESCRIPTION,		'type' => 'textarea',			'width' => 'auto', 'thclass' => 'left','readParms' => 'expand=...&truncate=150&bb=1', 'readonly'=>FALSE),
			'chapter_meta_keywords' 	=> array('title'=> "Meta Keywords",		'type' => 'text',			'width' => 'auto', 'thclass' => 'left', 'readonly'=>FALSE),		
			'chapter_sef' 				=> array('title'=> "SEF Url String",	'type' => 'text',			'width' => 'auto', 'readonly'=>FALSE), // Display name
			'chapter_manager' 			=> array('title'=> "Can be edited by",	'type' => 'userclass',		'width' => 'auto', 'data' => 'int','batch'=>TRUE, 'filter'=>TRUE),
			'chapter_order' 			=> array('title'=> LAN_ORDER,			'type' => 'text',			'width' => 'auto', 'thclass' => 'right', 'class'=> 'right' ),										
			'options' 					=> array('title'=> LAN_OPTIONS,			'type' => null,				'width' => '10%', 'forced'=>TRUE, 'thclass' => 'center last', 'class' => 'center')
		
		);

		protected $fieldpref = array('checkboxes', 'chapter_icon', 'chapter_id', 'chapter_name', 'chapter_description','chapter_manager', 'chapter_order', 'options');
		
		protected $books = array();
	
		function init()
		{
			$sql = e107::getDb();
			$sql->db_Select_gen("SELECT chapter_id,chapter_name FROM #page_chapters WHERE chapter_parent =0");
			$this->books[0] = "(New Book)";
			while($row = $sql->db_Fetch())
			{
				$bk = $row['chapter_id'];
				$this->books[$bk] = $row['chapter_name'];
			}
			asort($this->books);			
			
			$this->fields['chapter_parent']['writeParms'] = $this->books;	
		}
		
	//	function createPage()
	//	{
		//	$this->newspost->show_categories();
	//	}
		
		public function beforeCreate($new_data)
		{
	
		}
		
		
		public function beforeUpdate($new_data, $old_data, $id)
		{
	
		}

}

class page_chapters_form_ui extends e_admin_form_ui
{

}


class page_admin_ui extends e_admin_ui
{
		protected $pluginTitle = ADLAN_42;
		protected $pluginName = 'core';
		protected $table = "page";
		
		protected $listQry = "SELECT p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id WHERE p.page_theme = '' "; // without any Order or Limit.
		//protected $editQry = "SELECT * FROM #comments WHERE comment_id = {ID}";
		
		protected $pid 			= "page_id";
		protected $listOrder 	= 'p.page_order asc'; // desc would require changes to ajax sorting. 
		protected $perPage 		= 10;
		protected $batchDelete 	= true;
		protected $batchCopy 	= true;	
	//		protected $listSorting = true; 
		
		protected $fields = array(
			'checkboxes'		=> array('title'=> '',				'type' => null, 		'width' =>'5%', 'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'page_id'			=> array('title'=> 'ID',			'width'=>'5%', 			'forced'=> TRUE),
            'page_chapter' 		=> array('title'=> 'Book/Chapter', 		'type' => 'dropdown', 	'width' => '20%', 'filter' => true, 'batch'=>true),
            'page_title'	   	=> array('title'=> LAN_TITLE, 		'type' => 'text', 		'width'=>'25%','readParms'=>'link={e_BASE}page.php?[id]&dialog=1'),
			'page_theme' 		=> array('title'=> CUSLAN_2, 		'type' => 'text', 		'width' => 'auto','nolist'=>true),
			
			'page_template' 	=> array('title'=> 'Template', 		'type' => 'text', 		'width' => 'auto','filter' => true, 'batch'=>true),
         	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'type' => 'user', 		'width' => 'auto', 'thclass' => 'left'),
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'type' => 'datestamp', 	'width' => 'auto'),
            'page_class' 		=> array('title'=> LAN_USERCLASS, 	'type' => 'userclass', 	'width' => 'auto',  'filter' => true, 'batch' => true),
			'page_rating_flag' 	=> array('title'=> LAN_RATING, 		'type' => 'boolean', 	'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
			'page_comment_flag' => array('title'=> ADLAN_114,		'type' => 'boolean', 	'width' => '5%', 'thclass' => 'center', 'class' => 'center' ),
		//	'page_password' 	=> array('title'=> LAN_USER_05, 	'type' => 'text', 'width' => 'auto'),								
			'page_order' 		=> array('title'=> LAN_ORDER, 		'type' => 'number', 'width' => 'auto', 'nolist'=>true),
	   //	'page_ip_restrict' 		=> array('title'=> LAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar

			'options' 	=> array('title'=> LAN_OPTIONS, 'type' => null,	'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center','readParms'=>'sort=1')
		);
	
		protected $fieldpref = array("page_id","page_title","page_chapter","page_template","page_author","page_class");


		protected $books = array();
		protected $cats = array();

		function init()
		{
			
			if(e_AJAX_REQUEST) // ajax sorting. 
			{
				$sql = e107::getDb();
				$c= ($_GET['from']) ? intval($_GET['from']) : 0;
				if(isset($_POST['all']))
				{
					foreach($_POST['all'] as $row)
					{
						list($tmp,$id) = explode("-",$row);
						$sql->db_Update("page","page_order = ".intval($c)." WHERE page_id = ".intval($id));
						$c++;		
					}
				}
			//	echo "<script>alert('hello');</script>";
				exit;
			}
						
			
			
			
			
			$sql = e107::getDb();
			$sql->db_Select_gen("SELECT chapter_id,chapter_name,chapter_parent FROM #page_chapters ORDER BY chapter_parent asc");
			while($row = $sql->db_Fetch())
			{
				$cat = $row['chapter_id'];
				
				if($row['chapter_parent'] == 0)
				{
					$this->books[$cat] = $row['chapter_name'];	
				}
				else
				{
					$book = $row['chapter_parent'];
					$this->cats[$cat] = $this->books[$book] . " : ".$row['chapter_name'];	
				}			
			}
			asort($this->cats);			
			
			$this->fields['page_chapter']['writeParms'] = $this->cats;
			
			
			
			
			
			if(varset($_GET['mode'])=='dialog' && varset($_GET['action'])=='dialog') // New Page bbcode in tabs. 
			{
				$this->dialogPage();
			}
			
		
			if($this->getQuery('iframe') == 1)
			{
			
				$this->getResponse()->setIframeMod();			
			}
			
			
			
			//FIXME - mode not retained after using  drop-down 'filter' or 'search'. 
			if($_GET['mode'] =='menu')
			{
				$this->listQry = "SELECT p.*,u.user_id,u.user_name FROM #page AS p LEFT JOIN #user AS u ON p.page_author = u.user_id WHERE p.page_theme != '' "; // without any Order or Limit.	
			}
			
					
			if(isset($_POST['uploadfiles']))
			{
			
				$this->uploadPage();
				$id = intval(varset($_POST['pe_id'], 0));
				$sub_action = ($_POST['pe_id']) ? "edit" : "";
				$this->createPage($_POST['mode']);
			}
			
			if (isset($_POST['saveOptions']))
			{
				$this->saveSettings();
			}
			
			if(isset($_POST['submitPage']))
			{
				$this->submitPage();
			}
					
			if(isset($_POST['submitMenu']))
			{
				$this->submitPage("", TRUE);
			}
			
			if(isset($_POST['updateMenu']))
			{
				$this->submitPage($_POST['pe_id'], TRUE);
			}
			
			if(isset($_POST['updatePage']))
			{
				$this->submitPage($_POST['pe_id']);
			}
			
			
		}
		
		// Create Menu Page. 
		function createemPage()
		{
			if(!getperms("J")){ return; }
			$this->createPage('menu');	
		}
		
		function dialogPage() // FIXME - remove table-rendering when using 'return' ??
		{
			$count = varset($_GET['count']);
			$frm = e107::getForm();
			$text = "<fieldset id='page_{$count}'>\n";
			$text .= "<div>Title: ".$frm->text('page_subtitle[]', '', 250)."</div>\n";
			$text .= $frm->bbarea('data_'.(intval($count)), '', 'page','page','large');
			$text .= "</fieldset>";	
		//	$text .= 'name='.$nm."<br />page=".$page."<br />help_mode=". $help_mod."<br />htlp_tagid=".$help_tagid."<br />size=".$size;
			echo $text;	
			exit;
			// return $text;
			/*
			<div class='bbcode large' >
			 */
		}

		
		// Create Page Page. 
		function createPage($mode=FALSE)
		{
			/* mode: FALSE == page, mode: TRUE == menu */
			if($_GET['mode'] =='menu')
			{
				$mode = TRUE;
			}
				
				
			global $e107, $e_userclass, $e_event;
	
			$frm = e107::getForm();
			$sql = e107::getDb();
			$tp = e107::getParser();
			$ns = e107::getRender();
			$mes = e107::getMessage();
	
			$id = intval($_GET['id']);
			$sub_action = $_GET['action'];
	
			$edit = ($sub_action == 'edit');
			$caption =(!$mode ? ($edit ? CUSLAN_23 : CUSLAN_24) : ($edit ? CUSLAN_25 : CUSLAN_26));
	
			if ($_GET['action'] == "edit" && !isset($_POST['preview']) && !isset($_POST['submit']))
			{
				
				//$url = e107::getUrl()->sc('page/view', $row, 'allow=page_id,page_title,page_sef');
	        	//$query = "SELECT p.*,l.link_name,m.menu_name FROM #page AS p
	        	$query = "SELECT p.* FROM #page AS p
				LEFT JOIN #menus AS m ON m.menu_path='{$id}' WHERE p.page_id ='{$id}' LIMIT 1";
				// FIXME - extremely bad
				//LEFT JOIN #links AS l ON l.link_url='".$url."'
	            if ($sql->db_Select_gen($query))
				{
					$row                          = $sql->db_Fetch();
					$page_class                   = $row['page_class'];
					$page_password                = $row['page_password'];
					$page_title                   = $tp->toForm($row['page_title']);
					$page_rating_flag             = $row['page_rating_flag'];
					$page_comment_flag            = $row['page_comment_flag'];
					$page_display_authordate_flag = $row['page_author'];
					$page_link 					  = varset($row['link_name'],'');
					$data                         = $tp->toForm($row['page_text']);
					$edit                         = TRUE;
	//				$menu_name					  = $tp->toForm($row['menu_name']);
					$menu_name					  = $tp->toForm($row['page_theme']);
					
				}
			}
			else
			{
				$menu_name = '';
				$page_title = '';
				$data = '';
			}
	
			$e_qry = ($mode) ? 'menus=1' : '';
	
			$text = "
				<form method='post' action='".e_SELF."?".$e_qry."' id='dataform' enctype='multipart/form-data'>
					<fieldset id='core-cpage-create-general'>
						<legend".($mode ? " class='e-hideme'" : "").">".CUSLAN_47."</legend>
						<table class='table adminform'>
							<colgroup>
								<col class='col-label' />
								<col class='col-control' />
							</colgroup>
							<tbody>
			";
	
			if($mode)  // menu mode.
			{
				$text .= "
					<tr>
						<td>".CUSLAN_7."</td>
						<td>".$frm->text('menu_name', $menu_name, 50)."</td>
					</tr>
				";
			}
			else
			{
				
				$text .= "
					<tr>
						<td>Chapter</td>
						<td>". $frm->selectbox('page_chapter',$this->cats, $row['page_chapter'])  ."</td>
					</tr>
				";
				
				
				// fixed - last parameter (allinfo) should be false as getLayout method is returning non-usable formatted array
				$templates = e107::getLayouts('', 'page', 'front', '', false, false); 
			//	$templates['menu'] = "Sidebar"; // ie. a MENU item. //TODO 
	
				$text .= "
					<tr>
						<td>Template</td>
						<td>". $frm->selectbox('page_template',$templates,$row['page_template'])  ."</td>
					</tr>
				";
				
				
			}
			$text .= "
					<tr>
						<td>".CUSLAN_8."</td>
						<td>".$frm->text('page_title', $page_title, 250)."</td>
					</tr>
			";
			
			if(!$mode)
			{
				$text .= "
						<tr>
							<td>".CUSLAN_3."</td>
							<td>".$frm->text('page_sef', $row['page_sef'], 250)."</td>
						</tr>
				";
				
				$text .= "
						<tr>
							<td>".CUSLAN_32."</td>
							<td>".$frm->text('page_metakeys', $row['page_metakeys'], 250)."</td>
						</tr>
				";
				
				$text .= "
						<tr>
							<td>".CUSLAN_11."</td>
							<td>".$frm->textarea('page_metadscr', $row['page_metadscr'], 1, 80, array(), 200)."</td>
						</tr>
				";
			}
	
			$text .= "
						<tr>
						<td colspan='2'>
			";
	
		$text .= "<div id='tab-container' class='admintabs e-tabs'>
		<ul>
			<li><a href='#cpage-body-container'>".CUSLAN_9."</a></li>
		</ul>
		<div id='cpage-body-container'>";	
				
		$data = $tp->toForm($data,FALSE,TRUE);	// Make sure we convert HTML tags to entities
	
		$textareaValue = (strstr($data, "[img]http") ? $data : str_replace("[img]../", "[img]", $data));
	
	//	$text .= $this->bbareaMulti('data', $textareaValue, 'page','page','large');
		$text .= $frm->bbarea('data', $textareaValue, 'page','page','large');
		$text .= "</div></div>";
		
	//	$text .= $frm->bbarea('data', $textareaValue, 'page','help','large');
			
	
			
	
		//	$text .= "<textarea class='e-wysiwyg tbox' tabindex='".$frm->getNext()."' id='data' name='data' cols='80'{$insertjs}>".(strstr($data, "[img]http") ? $data : str_replace("[img]../", "[img]", $data))."</textarea>";
		//			<br />".display_help('cpage-help', 'cpage')."
	
	/*
					$text .= "</td>
								</tr>
								<tr>
									<td>".LAN_UPLOAD_IMAGES."</td>
									<td>".$tp->parseTemplate("{UPLOADFILE=".e_IMAGE."custom/}")."</td>
								</tr>
	
	
			";
	*/
			if(!$mode)
			{
				$text .= "
							</tbody>
						</table>
					</fieldset>
					<fieldset id='core-cpage-create-options'>
						<legend>".LAN_OPTIONS."</legend>
						<table class='table adminform options'>
							<colgroup>
								<col class='col-label' />
								<col class='col-control' />
							</colgroup>
							<tbody>
								<tr>
									<td>".CUSLAN_10."</td>
									<td>
										".$frm->radio_switch('page_rating_flag', $page_rating_flag)."
									</td>
								</tr>
								<tr>
									<td>".CUSLAN_13."</td>
									<td>
										".$frm->radio_switch('page_comment_flag', $page_comment_flag)."
									</td>
								</tr>
								<tr>
									<td>".CUSLAN_41."</td>
									<td>
										".$frm->radio_switch('page_display_authordate_flag', $page_display_authordate_flag)."
									</td>
								</tr>
								<tr>
									<td>".CUSLAN_14."</td>
									<td>
										".$frm->text('page_password', $page_password, 50)."
										<div class='field-help'>".CUSLAN_15."</div>
									</td>
								</tr>
	
								<tr>
									<td>".CUSLAN_18."</td>
	
									<td>
										".$e_userclass->uc_dropdown('page_class', $page_class, 'public,guest,nobody,member,main,admin,classes', "tabindex='".$frm->getNext()."'")."
									</td>
								</tr>
				";
	
								/*
								<tr>
									<td>".CUSLAN_16."</td>
									<td>
										".$frm->text('page_link', $page_link, 50)."
										<div class='field-help'>".CUSLAN_17."</div>
									</td>
								</tr>
								 **/
								
				
				//triggerHook
				
				$data = array(
					'method'	=>'form', 
					'table'		=>'page', 
					'id'		=> $id, 
					'plugin'	=> 'page', 
					'function'	=> 'createPage'
				);
				
				
				$text .= $frm->renderHooks($data);

			}
	
	
			$text .= "
							</tbody>
						</table>
						<div class='buttons-bar center'>
							".
							(!$mode ?
							($edit  ? $frm->admin_button('updatePage', CUSLAN_19, 'update')."<input type='hidden' name='pe_id' value='{$id}' />" : $frm->admin_button('submitPage', CUSLAN_20, 'create')) :
							($edit  ? $frm->admin_button('updateMenu', CUSLAN_21, 'update')."<input type='hidden' name='pe_id' value='{$id}' />" : $frm->admin_button('submitMenu', CUSLAN_22, 'create')))
							."
							<input type='hidden' name='mode' value='{$mode}' />
						</div>
					</fieldset>
				</form>
			";
	
		//	$ns->tablerender($caption, $text);
			
			echo $mes->render().$text;
		}
		
	// 	bbarea($name, $value, $help_mod = '', $help_tagid='', $size = 'large', $counter = false)
		function bbareaMulti($name, $textareaValue, $help_mod = '', $help_tagid='', $size = 'large', $counter = false)
		{
			// $name = $name."[]";
			
			$frm = e107::getForm();
			
			if(!$textareaValue)
			{
			//	$textareaValue = "[newpage]	";
			}
			
			
			if(preg_match_all("/\[newpage=?(.*?)\]/si", $textareaValue, $pt))
			{
		
			}
			$pages = preg_split("/\[newpage(.*?)\]/si", $textareaValue, -1, PREG_SPLIT_NO_EMPTY);
						
			$c= 1;
			$titles[0] = ""; 
			
			$text .= "<ul class='e-tabs'>";
			foreach($pages as $page)
			{
				
				$id = "#page_".$c;
				$pageCap = "Page ".($c);
				$text .= "<li><a href='{$id}' >{$pageCap}</a></li>";	
				$c++;
			}
			$text .= "</ul>";
			$c= 1;
			foreach($pages as $curval)
			{
				$titles[] = isset($pt[1][($c-1)]) ? $pt[1][($c-1)] : "";
				$id = "page_".$c;
				$nm = $name."_".$c;
				$text .= "<fieldset id='{$id}'>\n";
				$text .= "<div>Title: ".$frm->text('page_subtitle['.$c.']', $titles[($c)], 250)."</div>\n";
				$text .= $frm->bbarea($nm, $curval, $help_mod,$help_tagid,$size,$counter);
		
				$text .= "</fieldset>";	
			
			//	$text .= 'name='.$nm."<br />page=".$page."<br />help_mode=". $help_mod."<br />htlp_tagid=".$help_tagid."<br />size=".$size;
				$c++;	
			}
			
			$text .= "<button class='e-bb e-tabs-add' data-target='$name' data-url='".e_SELF."?mode=dialog&action=dialog&iframe=1' data-function='add' href='#'  data-bbcode=''><span>New Page</span></button>";
				
			$text .= "<input type='hidden' id='e-tab-count' value='".count($pages)."' />";
			
			
			return $text;
		}

		
		function optionsPage()
		{
			global $e107, $pref;
			
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$frm = e107::getForm();
			$sql = e107::getDb();
			$tp = e107::getParser();
	
			if(!isset($pref['pageCookieExpire'])) $pref['pageCookieExpire'] = 84600;
	
			//XXX Lan - Options
			$text = "
				<form method='post' action='".e_SELF."?".e_QUERY."'>
					<fieldset id='core-cpage-options'>
						<legend class='e-hideme'>".LAN_OPTIONS."</legend>
						<table class='table adminform'>
							<colgroup>
								<col class='col-label' />
								<col class='col-control' />
							</colgroup>
							<tbody>
								<tr>
									<td>".CUSLAN_29."</td>
									<td>
										".$frm->radio_switch('listPages', $pref['listPages'])."
									</td>
								</tr>
	
								<tr>
									<td>".CUSLAN_30."</td>
									<td>
										".$frm->text('pageCookieExpire', $pref['pageCookieExpire'], 10)."
									</td>
								</tr>
							</tbody>
						</table>
						<div class='buttons-bar center'>
							".$frm->admin_button('saveOptions', CUSLAN_40, 'submit')."
						</div>
					</fieldset>
				</form>
			";
	
			//$ns->tablerender(LAN_OPTIONS, $mes->render().$text);
			echo $mes->render().$text;
		}
		
		
		
		function saveSettings()
		{
			global $pref, $admin_log, $emessage;
			$temp['listPages'] = $_POST['listPages'];
			$temp['pageCookieExpire'] = $_POST['pageCookieExpire'];
			if ($admin_log->logArrayDiffs($temp, $pref, 'CPAGE_04'))
			{
				save_prefs();		// Only save if changes
				$emessage->add(LAN_SETSAVED, E_MESSAGE_SUCCESS);
			}
			else
			{
				$emessage->add(LAN_NOCHANGE_NOTSAVED);
			}
		}



		// Write new/edited page/menu to the DB
		// $mode - zero for new page, page id for existing
		// $type = FALSE for page, TRUE for menu
		function submitPage($mode = FALSE, $type=FALSE)
		{
			
			global $e107cache, $admin_log, $e_event;
			
			
			$frm = e107::getForm();
			$sql = e107::getDb();
			$tp = e107::getParser();
			$ns = e107::getRender();
			$mes = e107::getMessage();
	
			$page_title = $tp->toDB($_POST['page_title']);
			
	//	print_a($_POST);
			
			
	//		if(is_array($_POST['data']) && is_array($_POST['subtitle']))
			$newData = array();
			foreach($_POST as $k=>$v)
			{
				if(substr($k,0,4)=='data' && trim($v)!='')
				{
					list($tm,$key) = explode("_",$k);

					if($mode == FALSE) // Pages only, not menus. 
					{
			//			$newData[] = "[newpage=".$_POST['page_subtitle'][$key]."]\n";
					}
					$newData[] = $v;			
				}
				
				// return;	
			}
	
	
		//	return;
			$newData = implode("\n\n", $newData);
	
		// echo nl2br($newData);
			
			$page_text = $tp->toDB($newData);
			$pauthor = ($_POST['page_display_authordate_flag'] ? USERID : 0); // Ideally, this check should be done in the front-end.
			$update = 0;			// Make sure some updates happen
			
			$page_sef = '';
			$page_metad = '';
			$page_metak = '';
			if(!$type)
			{
				
				if(!empty($_POST['page_sef']))
				{
					$page_sef = eHelper::secureSef($_POST['page_sef']);
				}
				
				if(empty($page_sef))
				{
					$page_sef = eHelper::title2sef($_POST['page_title']);
				}
				
				if(!empty($_POST['page_metadscr']))
				{
					$page_metad = $tp->toDB(eHelper::formatMetaDescription($_POST['page_metadscr']));
				}
	
				if(!empty($_POST['page_metakeys']))
				{
					$page_metak = eHelper::formatMetaKeys($_POST['page_metakeys']);
				}
				
			}
	
			if(!$type && (!$page_title || !$page_sef))
			{
				e107::getMessage()->addError(CUSLAN_34, 'default', true);
				
			
				
				e107::getRedirect()->redirect(e_ADMIN_ABS.'cpage.php');
			}
			
			// FIXME Causes false positives on Update.. - what is trying to be achieved with this check?
			/*
			if(!$type && $sql->db_Count('page', '(page_id)', ($mode ? "page_id != {$mode} AND " : '')."page_sef != '{$page_sef}'"))
			{
				e107::getMessage()->addError(CUSLAN_34, 'default', true);
				
				e107::getMessage()->addDebug("type=".$type, 'default', true);
				e107::getMessage()->addDebug("page_title=".$page_title, 'default', true);
				e107::getMessage()->addDebug("page_sef=".$page_sef, 'default', true);
				e107::getMessage()->addDebug("Mode=".$mode, 'default', true);					
				
				e107::getRedirect()->redirect(e_ADMIN_ABS.'cpage.php');
			}
			*/
			
			if($type && empty($_POST['menu_name']))
			{
				e107::getMessage()->addError(CUSLAN_36, 'default', true);
				e107::getRedirect()->redirect(e_ADMIN_ABS.'cpage.php');
			}
	
			if($mode)
			{	// Saving existing page/menu after edit
				// Don't think $_POST['page_ip_restrict'] is ever set.
				
				$menuname = ($type && vartrue($_POST['menu_name']) ? ", page_theme = '".$tp -> toDB($_POST['menu_name'])."'" : "");
				
				$status = $sql -> db_Update("page", "page_title='{$page_title}', page_sef='{$page_sef}', page_chapter='".intval($_POST['page_chapter'])."', page_metakeys='{$page_metak}', page_metadscr='{$page_metad}', page_text='{$page_text}', page_datestamp='".time()."', page_author='{$pauthor}', page_rating_flag='".intval($_POST['page_rating_flag'])."', page_comment_flag='".intval($_POST['page_comment_flag'])."', page_password='".$_POST['page_password']."', page_class='".$_POST['page_class']."', page_ip_restrict='".varset($_POST['page_ip_restrict'],'')."', page_template='".$_POST['page_template']."' {$menuname} WHERE page_id='{$mode}'") ?  E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
				if ($status == E_MESSAGE_SUCCESS) $update++;
	
			
				$mes->add($message, $status);
	
				$admin_log->log_event('CPAGE_02',$mode.'[!br!]'.$page_title.'[!br!]'.$pauthor,E_LOG_INFORMATIVE,'');
				$e107cache->clear("page_{$mode}");
				$e107cache->clear("page-t_{$mode}");
	
				$data = array('method'=>'update', 'table'=>'page', 'id'=>$mode, 'plugin'=>'page', 'function'=>'submitPage');
				$this->message = $e_event->triggerHook($data);
	
				if($type)
				{
					$menu_name = $tp -> toDB($_POST['menu_name']); // not to be confused with menu-caption.
					// Need to check whether menu already in table, else we can't distinguish between a failed update and no update needed
					if ($sql->db_Select('menus', 'menu_name', "`menu_path` = '{$mode}'"))
					{		// Updating existing entry
						if($sql -> db_Update('menus', "menu_name='{$menu_name}' WHERE menu_path='{$mode}' ") !== FALSE)
						{
							$update++;
						}
					}
				}
	
				//$url = e107::getUrl()->sc('page/view', array('name' => $tp->post_toForm($_POST['page_title']), 'id' => $mode));
				/*
				 
				// Prevent links being updated in another language unless the table is present. 
			if((($pref['sitelanguage'] != $sql->mySQLlanguage) && ($sql->mySQLlanguage!='')) && ($sql->db_IsLang("links")=='links'))
			{
				//echo "DISABLED LINK CREATION";
				//echo ' Sitelan='.$pref['sitelanguage'];
				//echo " Dblang=".$sql->mySQLlanguage;
				//echo " Links=".$sql->db_IsLang("links");
			
				return;	
			}
				 
				 
				 
				if ($_POST['page_link'])
				{
					// FIXME extremely ugly, just join on created link ID by new field page_link 
					if ($sql->db_Select("links", "link_id", "link_url='".$url."' && link_name!='".$tp->toDB($_POST['page_link'])."'"))
					{
						$sql->db_Update("links", "link_name='".$tp->toDB($_POST['page_link'])."' WHERE link_url='".$url."'");
						$update++;
						$e107cache->clear("sitelinks");
					}
					else if (!$sql->db_Select("links", "link_id", "link_url='".$url."'"))
					{
						$sql->db_Insert("links", "0, '".$tp->toDB($_POST['page_link'])."', '".$url."', '', '', 1, 0, 0, 0, ".$_POST['page_class']);
						$update++;
						$e107cache->clear("sitelinks");
					}
				} else {
					if ($sql->db_Select("links", "link_id", "link_url='".$url."'"))
					{
						$sql->db_Delete("links", "link_url='".$url."'");
						$update++;
						$e107cache->clear("sitelinks");
					}
				}*/
				
				$mes = e107::getMessage();
				$mes->addAuto($update, 'update', LAN_UPDATED, false, false);		// Display result of update
			}
			else
			{	// New page/menu
				$menuname = ($type ? $tp->toDB($_POST['menu_name']) : "");
				$addMsg = ($type ? CUSLAN_51 : CUSLAN_27);
				
				$info = array(
					'page_title' => $page_title,
					'page_sef' => $page_sef,
					'page_chapter'	=> varset($_POST['page_chapter'],0),
					'page_metakeys' => $page_metak,
					'page_metadscr' => $page_metad,
					'page_text' => $page_text,
					'page_author' => $pauthor,
					'page_datestamp' => time(),
					'page_rating_flag' => varset($_POST['page_rating_flag'],0),
					'page_comment_flag' => varset($_POST['page_comment_flag'], ''),
					'page_password' => varset($_POST['page_password'], ''),
					'page_class' => varset($_POST['page_class'],e_UC_PUBLIC),
					'page_ip_restrict' => '',
					'page_theme' => $menuname,
					'page_template' => varset($_POST['page_template'],'')
					);
				$pid = e107::getMessage()->addAuto($sql->db_Insert('page', $info), 'insert', $addMsg, LAN_CREATED_FAILED, false);
				$admin_log->log_event('CPAGE_01',$menuname.'[!br!]'.$page_title.'[!br!]'.$pauthor,E_LOG_INFORMATIVE,'');
	
				if($type)
				{
					$info = array(
						'menu_name' => $menuname,
						'menu_location' => 0,
						'menu_order' => 0,
						'menu_class' => '0',
						'menu_pages' => '',
						'menu_path' => $pid,
					);
					e107::getMessage()->addAuto($sql->db_Insert('menus', $info), 'insert', CUSLAN_52, LAN_CREATED_FAILED, false);
				}
	
				/*if(vartrue($_POST['page_link']))
				{
					//$link = 'page.php?'.$pid;
					$url = e107::getUrl()->sc('page/view', array('name' => $tp->post_toForm($_POST['page_title']), 'id' => $pid));
					if (!$sql->db_Select("links", "link_id", "link_name='".$tp->toDB($_POST['page_link'])."'"))
					{
						$linkname = $tp->toDB($_POST['page_link']);
						$sql->db_Insert("links", "0, '{$linkname}', '{$url}', '', '', 1, 0, 0, 0, ".$_POST['page_class']);
						$e107cache->clear("sitelinks");
					}
				}*/
	
				$data = array('method'=>'create', 'table'=>'page', 'id'=>$pid, 'plugin'=>'page', 'function'=>'submitPage');
				$this->message = $e_event->triggerHook($data);
			}
		}



		function uploadPage()
		{
			global $pref;
			$pref['upload_storagetype'] = "1";
			require_once(e_HANDLER."upload_handler.php");
			$uploaded = file_upload(e_IMAGE."custom/");
		}

}



new page_admin();
require_once('auth.php');

e107::getAdminUI()->runPage();

require_once(e_ADMIN.'footer.php');


// FIXME - add page link to sitelinks is completely disabled as current implementation is not reliable (+ is obsolete and generates sql error)
class page
{
	var $fields;

	// DEPRECATED
	function page()
	{

 		global $pref,$user_pref, $admin_log;
		if(isset($_POST['etrigger_ecolumns']))
		{
			$user_pref['admin_cpage_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}
        $this->fieldpref = (varset($user_pref['admin_cpage_columns'])) ? $user_pref['admin_cpage_columns'] : array("page_id","page_title","page_theme"); ;

    	$this->fields = array(
			'page_id'			=> array('title'=> 'ID',			'width'=>'5%', 'forced'=> TRUE),
            'page_title'	   	=> array('title'=> LAN_TITLE, 		'type' => 'text', 'width'=>'auto'),
			'page_theme' 		=> array('title'=> CUSLAN_2, 		'type' => 'text', 'width' => 'auto','nolist'=>true),
			'page_template' 	=> array('title'=> 'Template', 		'type' => 'text', 'width' => 'auto'),
         	'page_author' 		=> array('title'=> LAN_AUTHOR, 		'type' => 'text', 'width' => 'auto', 'thclass' => 'left'),
			'page_datestamp' 	=> array('title'=> LAN_DATE, 		'type' => 'datestamp', 'width' => 'auto'),
            'page_class' 		=> array('title'=> LAN_USERCLASS, 	'type' => 'userclass', 'width' => 'auto', 'filter' => true, 'batch' => true,),
			'page_rating_flag' 	=> array('title'=> LAN_RATING, 		'type' => 'boolean', 'width' => '10%', 'thclass' => 'center', 'class' => 'center' ),
			'page_comment_flag' => array('title'=> ADLAN_114,		'type' => 'boolean', 'width' => '10%', 'thclass' => 'center', 'class' => 'center' ),
		//	'page_password' 	=> array('title'=> LAN_USER_05, 	'type' => 'text', 'width' => 'auto'),

	   //	'page_ip_restrict' 		=> array('title'=> LAN_USER_07, 'type' => 'text', 'width' => 'auto'),	 // Avatar

			'options' 	=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center')
		);

       // $this->fieldpref = array("page_id","page_title","page_author","page_class");
	}


// --------------------------------------------------------------------------
	// DEPRECATED
/*
	function menusPage()
	{
		if(!getperms("J")){ return; }
    	return $this->showExistingPages('menus');
	}
 **/

// --------------------------------------------------------------------------
/*
	function showExistingPages($mode=FALSE)
	{
		global $sql, $e107, $emessage, $frm, $pref;

        $text = "<form method='post' action='".e_SELF."?".e_QUERY."'>
                        <fieldset id='core-cpage-list'>
						<legend class='e-hideme'>".CUSLAN_5."</legend>
						<table class='table adminlist'>".
							$frm->colGroup($this->fields,$this->fieldpref).
							$frm->thead($this->fields,$this->fieldpref).

							"<tbody>";

        if($mode=='menus')
		{
			$qry = "page_theme !='' ";
        	$caption = CUSLAN_50;
		}
		else
		{
			if(!getperms("5")){ return; }
        	$qry = "page_theme ='' ";
			$caption = CUSLAN_5;
		}

		if(!$sql->db_Select("page", "*", $qry." ORDER BY page_datestamp DESC"))
		{
			$text .= "
							<tr>
								<td colspan='4' class='center middle'>
									".CUSLAN_42."
								</td>
							</tr>
			";
		}
		else
		{
			$pages = $sql->db_getList('ALL', FALSE, FALSE);

			foreach($pages as $pge)
			{
				$title_text = $pge['page_title'] ? $pge['page_title'] : ($pge['page_theme'] ? CUSLAN_43.$pge['page_theme'] : CUSLAN_44);
				$pge['page_title'] = "<a href='".($pge['page_theme'] ? e_ADMIN."menus.php" : e107::getUrl()->create('page/view', $pge, 'allow=page_id,page_sef'))."'>{$title_text}</a>";
				$authorData = get_user_data($pge['page_author']);
				$pge['page_author'] = varset($authorData['user_name'], '?');

				$text .= $frm->renderTableRow($this->fields,$this->fieldpref,$pge,'page_id');
			}
		}

		$text .= "
						</tbody>
					</table>
				</fieldset>
			</form>
		";

		$e107->ns->tablerender($caption, $emessage->render().$text);
	}

*/






	
	// DEPRECATED
	function delete_page($del_id)
	{
		return; 
		
		
		global $sql, $e107cache, $admin_log, $e_event;
		//if(!$sql->db_Select('page', '*', "page_id={$del_id}")) return;
		//$row = $sql->db_Fetch();
		
		e107::getMessage()->addAuto($sql->db_Delete("page", "page_id='{$del_id}' "), 'delete', CUSLAN_28, false, false);
		$sql->db_Delete('menus', "menu_path='$del_id'");
		$e107cache->clear_sys('menus_');
		$admin_log->log_event('CPAGE_03','ID: '.$del_id,E_LOG_INFORMATIVE,'');
		
		/*$url = e107::getUrl()->sc('page/view', $row, 'allow=page_id,page_title,page_sef');
		if ($row['page_theme'] && $sql->db_Select('links', 'link_id', "link_url='".$url."'"))
		{
			$tmp = $sql->db_Fetch();
			$sql->db_Delete('links', "link_id=".$tmp['link_id']);
			$e107cache->clear('sitelinks');
		}
		*/
		$data = array('method'=>'delete', 'table'=>'page', 'id'=>$del_id, 'plugin'=>'page', 'function'=>'delete_page');
		$this->message = $e_event->triggerHook($data);
	}

	
	/*
	function saveSettings()
	{
		global $pref, $admin_log, $emessage;
		$temp['listPages'] = $_POST['listPages'];
		$temp['pageCookieExpire'] = $_POST['pageCookieExpire'];
		if ($admin_log->logArrayDiffs($temp, $pref, 'CPAGE_04'))
		{
			save_prefs();		// Only save if changes
			$emessage->add(LAN_SETSAVED, E_MESSAGE_SUCCESS);
		}
		else
		{
			$emessage->add(LAN_NOCHANGE_NOTSAVED);
		}
	}


	function show_options($action)
	{
		if ($action == "")
		{
			$action = (getperms('5')) ? "pages" : "menus";
		}

		if(vartrue($_GET['menus']))
		{
			$action = "menus";
		}

		$var['pages']['text'] = CUSLAN_48;
		$var['pages']['link'] = e_SELF;
		$var['pages']['perm'] = 5;

        $var['menus']['text'] = CUSLAN_49;
		$var['menus']['link'] = e_SELF."?menus=1";
		$var['menus']['perm'] = "J";

		$var['create']['text'] = CUSLAN_12;
		$var['create']['link'] = e_SELF."?create";
		$var['create']['perm'] = 5;

		$var['createm']['text'] = CUSLAN_31;
		$var['createm']['link'] = e_SELF."?createm";
		$var['createm']['perm'] = "J";

		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF."?options";
		$var['options']['perm'] = "0";

		e107::getNav()->admin(CUSLAN_33, $action, $var);
	}
	*/
}

/*
function cpage_adminmenu()
{
	global $page;
	global $action;
	// $page->show_options($action);
}
*/

?>