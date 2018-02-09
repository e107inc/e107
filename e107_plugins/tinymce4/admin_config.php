<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
*/
require_once("../../class2.php");
if( !e107::isInstalled('tinymce4'))
{
	e107::redirect('admin');
	exit();
}

e107::lan('tinymce4','admin', true);


	class tinymce4_admin extends e_admin_dispatcher
	{

		protected $modes = array(
			'main'	=> array(
				'controller' 	=> 'tinymce4_ui',
				'path' 			=> null,
				'ui' 			=> 'tinymce4_ui_form',
				'uipath' 		=> null
			),
		);


		protected $adminMenu = array(

			'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),

			// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
		);

		protected $adminMenuAliases = array(
			'main/edit'	=> 'main/list'
		);

		protected $menuTitle = 'TinyMce';
	}



	class tinymce4_ui extends e_admin_ui
	{

		protected $pluginTitle		= 'TinyMce4';
		protected $pluginName		= 'tinymce4';




		protected $prefs = array(
			'paste_as_text'		    => array('title'=> TMCEALAN_1, 'type'=>'boolean', 'data' => 'int','help'=> ''),
			'browser_spellcheck'    => array('title'=> TMCEALAN_2, 'type'=>'boolean', 'data' => 'int','help'=> TMCEALAN_3),
			'visualblocks'          => array('title'=> TMCEALAN_4, 'type'=>'boolean', 'data' => 'int','help'=> TMCEALAN_5),
			'code_highlight_class'  => array('title'=> TMCEALAN_6, 'type'=>'text', 'data' => 'str','help'=> ''),

		);


		public function init()
		{


		}
	}


	class tinymce4_ui_form extends e_admin_form_ui
	{

	}


	new tinymce4_admin();

	require_once(e_ADMIN."auth.php");
	e107::getAdminUI()->runPage();

	require_once(e_ADMIN."footer.php");
	exit;










require_once(e_HANDLER."form_handler.php");
require_once (e_HANDLER.'message_handler.php');

$frm = new e_form(true);

$ef = new tinymce;
//TODO save prefs to separate config row. 
// List all forms of access, and allow the user to choose between simple/advanced or 'custom' settings.


if(varset($_POST['update']) || varset($_POST['create']))
{
	$id = intval($_POST['record_id']);
	$ef->submitPage($id);
}

if(varset($_POST['delete']))
{
	$id = key($_POST['delete']);
	$ef->deleteRecord($id);
	$_GET['mode'] = "list";
}

if(isset($_POST['edit']) || $id) // define after db changes and before header loads. 
{
	$id = (isset($_POST['edit'])) ? key($_POST['edit']) : $id;
	define("TINYMCE_CONFIG",$id);
}
else
{
	define("TINYMCE_CONFIG",FALSE);	
}


require_once(e_ADMIN."auth.php");

if(varset($_GET['mode'])=='create')
{
	$id = varset($_POST['edit']) ? key($_POST['edit']) : "";
	if($_POST['record_id'])
	{
		$id = $_POST['record_id'];
	}
	$ef->createRecord($id);	
}
else
{
	$ef->listRecords();
}

if(isset($_POST['etrigger_ecolumns']))
{
	$user_pref['admin_release_columns'] = $_POST['e-columns'];
	save_prefs('user');
}


require_once(e_ADMIN."footer.php");



class tinymce
{
	var $fields;
	var $fieldpref;
	var $listQry;
	var $table;
	var $primary;


	function __construct()
	{
	
    	$this->fields = array(
			'tinymce_id'		=> array('title'=> ID, 'width'=>'5%', 'forced'=> TRUE, 'primary'=>TRUE),
			'tinymce_name'	   	=> array('title'=> 'name', 'width'=>'auto','type'=>'text'),
			'tinymce_userclass' => array('title'=> 'class', 'type' => 'array', 'method'=>'tinymce_class', 'width' => 'auto'),	
			'tinymce_plugins' 	=> array('title'=> 'plugins', 'type' => 'array', 'method'=>'tinymce_plugins', 'width' => 'auto'),	
			'tinymce_buttons1' 	=> array('title'=> 'buttons1', 'type' => 'text', 'method'=>'tinymce_buttons', 'methodparms'=>1, 'width' => 'auto'),
			'tinymce_buttons2' 	=> array('title'=> 'buttons2', 'type' => 'text', 'method'=>'tinymce_buttons', 'methodparms'=>2, 'width' => 'auto'),
			'tinymce_buttons3' 	=> array('title'=> 'buttons3', 'type' => 'text', 'method'=>'tinymce_buttons', 'methodparms'=>3, 'width' => 'auto', 'thclass' => 'left first'), 
         	'tinymce_buttons4' 	=> array('title'=> 'buttons4', 'type' => 'text', 'method'=>'tinymce_buttons', 'methodparms'=>4, 'width' => 'auto', 'thclass' => 'left first'), 
            'tinymce_custom' 	=> array('title'=> 'custom', 'type' => 'text', 'width' => 'auto'),	 	
			'tinymce_prefs' 	=> array('title'=> 'prefs', 'type' => 'text', 'width' => '10%', 'thclass' => 'center' ),	 
			'options' 			=> array('title'=> LAN_OPTIONS, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last')
		);
		
		$this->fieldpref = (varset($user_pref['admin_tinymce_columns'])) ? $user_pref['admin_tinymce_columns'] : array_keys($this->fields);
		$this->table = "tinymce";
		$this->listQry = "SELECT * FROM #tinymce ORDER BY tinymce_id";
		$this->editQry = "SELECT * FROM #tinymce WHERE tinymce_id = {ID}";
		$this->primary = "tinymce_id";
		$this->pluginTitle = "Tinymce";
		
		$this->listCaption = "Tinymce Configs";
		$this->createCaption = LAN_CREATE."/".LAN_EDIT;
		
	}


// --------------------------------------------------------------------------
	/**
	 * Generic DB Record Listing Function. 
	 *
	 * @param object $mode [optional] - reserved
	 * @return void
	 */
	function listRecords($mode = FALSE)
	{
		$ns = e107::getRender();
		$sql = e107::getDb();
		$frm = e107::getForm();
		
		
		global $pref;
		
		$emessage = eMessage::getInstance();

        $text = "<form method='post' action='".e_SELF."?mode=create'>
                        <fieldset id='core-release-list'>
						<legend class='e-hideme'>".$this->pluginTitle."</legend>
						<table class='adminlist'>".
							$frm->colGroup($this->fields,$this->fieldpref).
							$frm->thead($this->fields,$this->fieldpref).

							"<tbody>";


		if(!$sql->db_Select_gen($this->listQry))
		{
			$text .= "\n<tr><td colspan='".count($this->fields)."' class='center middle'>".CUSLAN_42."</td></tr>\n";
		}
		else
		{
			$row = $sql->db_getList('ALL', FALSE, FALSE);

			foreach($row as $field)
			{
				$text .= "<tr>\n";
				foreach($this->fields as $key=>$att)
				{	
					$class = vartrue($this->fields[$key]['thclass']) ? "class='".$this->fields[$key]['thclass']."'" : "";		
					$text .= (in_array($key,$this->fieldpref) || $att['forced']==TRUE) ? "\t<td ".$class.">".$this->renderValue($key,$field)."</td>\n" : "";						
				}
				$text .= "</tr>\n";				
			}
		}

		$text .= "
						</tbody>
					</table>
				</fieldset>
			</form>
		";

		$ns->tablerender($this->pluginTitle." :: ".$this->listCaption, $emessage->render().$text);
	}

	/**
	 * Render Field value (listing page)
	 *
	 * @param array $key
	 * @param array $row
	 * @return string
	 */
	function renderValue($key, $row)
	{
		$att = $this->fields[$key];	
		$frm = e107::getForm();	
		
		if($key == "options")
		{
			$id = $this->primary;
			$text = "<input type='image' class='action edit' name='edit[{$row[$id]}]' src='".ADMIN_EDIT_ICON_PATH."' title='".LAN_EDIT."' />";
			$text .= "<input type='image' class='action delete' name='delete[{$row[$id]}]' src='".ADMIN_DELETE_ICON_PATH."' title='".LAN_DELETE." [ ID: {$row[$id]} ]' />";
			return $text;
		}
		
		if($key == "tinymce_userclass")
		{
			return $frm->uc_label($row[$key]);	
		}
		
		if($key == "tinymce_plugins")
		{
			return str_replace(",","<br />",$row[$key]);	
		}
		
		switch($att['type'])
		{
			case 'url':
				return "<a href='".$row[$key]."'>".$row[$key]."</a>";
			break;
					
			default:
				return $row[$key];
			break;
		}	
		return $row[$key] .$att['type'];	
	}
	
	/**
	 * Render Form Element (edit page)
	 *
	 * @param array $key
	 * @param array $row
	 * @return string method's value or HTML input
	 */
	function renderElement($key, $row)
	{
		$frm = e107::getForm();
		$att = $this->fields[$key];
		$value = $row[$key];	
		
		if($att['method'])
		{
			$meth = $att['method'];
			if(isset($att['methodparms']))
			{
				return $this->$meth($value, $att['methodparms']);
			}
			return $this->$meth($value);
		}
		
	
		return $frm->text($key, $row[$key], 50);
			
	}



	function createRecord($id=FALSE)
	{
		global $frm, $e_userclass, $e_event;

		$tp = e107::getParser();
		$ns = e107::getRender();
		$sql = e107::getDb();
		$mes = eMessage::getInstance();

		if($id)
		{
			$query = str_replace("{ID}",$id,$this->editQry);
			$sql->db_Select_gen($query);
			$row = $sql->db_Fetch();
		}
		else
		{
			$row = array();
		}

		$text = "
			<form method='post' action='".e_SELF."?mode=create' id='dataform' enctype='multipart/form-data'>
				<fieldset id='core-cpage-create-general'>
					<legend class='e-hideme'>".$this->pluginTitle."</legend>
					<table class='adminedit'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
			<tr>
			<td>Preview<div style='padding:20px'>[<a href='javascript:start_tinyMce();'>Refresh Preview</a>]
				<br /><br />[<a href='#' onclick=\"tinyMCE.execCommand('mceToggleEditor',false,'content');\">Toggle WYSIWYG</a>]
				</div>
			</td>
			<td>".$this->tinymce_preview()."</td>
			</tr>";
						
		
			
		foreach($this->fields as $key=>$att)
		{
			if($att['forced']!==TRUE)
			{
				$text .= "
					<tr>
						<td>".$att['title']."</td>
						<td>".$this->renderElement($key,$row)."</td>
					</tr>";
			}
							
		}

		$text .= "
			</tbody>
			</table>	
		<div class='buttons-bar center'>";
					
					if($id)
					{
						$text .= $frm->admin_button('update', LAN_UPDATE, 'update');
						$text .= "<input type='hidden' name='record_id' value='".$id."' />";						
					}	
					else
					{
						$text .= $frm->admin_button('create', LAN_CREATE, 'create');	
					}
					
		$text .= "
			</div>
			</fieldset>
		</form>";	
		
		$ns->tablerender($this->pluginTitle." :: ".$this->createCaption,$mes->render(). $text);
	}
	
	
	function tinymce_buttons($curVal,$id)
	{
		return "<input class='tbox' style='width:97%' type='text' name='tinymce_buttons".$id."' value='".$curVal."' />\n";	
	}
	
	
	function tinymce_preview()
	{
		return "<textarea id='content' class='e-wysiwyg tbox' rows='10' cols='10' name='preview'  style='width:80%'>     </textarea>";
		
	}
	
	function tinymce_plugins($curVal)
	{
		$fl = e107::getFile();
		
		$curArray = explode(",",$curVal);
	
		if($plug_array = $fl->get_dirs(e_PLUGIN."tinymce/plugins/"))
	    {
	    	sort($plug_array);
	    }	
		
		$text = "<div style='width:80%'>";

	    foreach($plug_array as $mce_plg)
		{
			$checked = (in_array($mce_plg,$curArray)) ? "checked='checked'" : "";
	    	$text .= "<div style='width:25%;float:left'><input type='checkbox' name='tinymce_plugins[]' value='".$mce_plg."' $checked /> $mce_plg </div>";
		}
		
		$text .= "</div>";		
		return $text;	
	}


	function tinymce_class($curVal)
	{
		$frm = e107::getForm();
	//	$cur = explode(",",$curVal);
		$uc_options = "guest,member,admin,main,classes";
		return $frm->uc_checkbox('tinymce_userclass', $curVal, $uc_options);
	}



	/**
	 * Generic Save DB Record Function.
	 * Insert or Update a table row.
	 *
	 * @param mixed $id [optional] if set, $id correspond to the primary key of the table
	 * @return void
	 */
	function submitPage($id = FALSE)
	{
		global $sql, $tp, $e107cache, $admin_log, $e_event;
		$emessage = eMessage::getInstance();
		
		$insert_array = array();
		
		foreach($this->fields as $key=>$att)
		{
			if($att['forced']!=TRUE)
			{
				$insert_array[$key] = $_POST[$key];
			}
			
			if($att['type']=='array')
			{
				$insert_array[$key] = implode(",",$_POST[$key]);	
			}
		}
			
		$xml = new SimpleXMLElement('<tinymce/>');
		$insertXml = array_flip($insert_array);
		array_walk_recursive($insertXml, array ($xml, 'addChild'));
		$save =  $xml->asXML();

		file_put_contents(e_SYSTEM."admin.xml",$save);
		
	//	echo htmlentities($save);
			
			
		if($id)
		{
			$insert_array['WHERE'] = $this->primary." = ".$id;
			$status = $sql->db_Update($this->table,$insert_array) ? E_MESSAGE_SUCCESS : E_MESSAGE_FAILED;
			$message = LAN_UPDATED;	
			
			
			

		}
		else
		{
			$status = $sql->db_Insert($this->table,$insert_array) ? E_MESSAGE_SUCCESS : E_MESSAGE_FAILED;
			$message = LAN_CREATED;	
		}
		

		$emessage->add($message, $status);		
	}

	function deleteRecord($id)
	{
		if(!$id || !$this->primary || !$this->table)
		{
			return;
		}
		
		$emessage = eMessage::getInstance();
		$sql = e107::getDb();
		
		$query = $this->primary." = ".$id;
		$status = $sql->db_Delete($this->table,$query) ? E_MESSAGE_SUCCESS : E_MESSAGE_FAILED;
		$message = LAN_DELETED;
		$emessage->add($message, $status);		
	}

	function optionsPage()
	{
		global $e107, $pref, $frm, $emessage;

		if(!isset($pref['pageCookieExpire'])) $pref['pageCookieExpire'] = 84600;

		//XXX Lan - Options
		$text = "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
				<fieldset id='core-cpage-options'>
					<legend class='e-hideme'>".LAN_OPTIONS."</legend>
					<table class='adminform'>
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

		$e107->ns->tablerender(LAN_OPTIONS, $emessage->render().$text);
	}


	function saveSettings()
	{
		global $pref, $admin_log, $emessage;
		$temp['listPages'] = $_POST['listPages'];
		$temp['pageCookieExpire'] = $_POST['pageCookieExpire'];
		if ($admin_log->logArrayDiffs($temp, $pref, 'CPAGE_04'))
		{
			save_prefs();		// Only save if changes
			$emessage->add(CUSLAN_45, E_MESSAGE_SUCCESS);
		}
		else
		{
			$emessage->add(CUSLAN_46);
		}
	}


	function show_options($action)
	{
		$action = varset($_GET['mode'],'list');

		$var['list']['text'] = $this->listCaption;
		$var['list']['link'] = e_SELF."?mode=list";
		$var['list']['perm'] = "0";

		$var['create']['text'] = $this->createCaption;
		$var['create']['link'] = e_SELF."?mode=create";
		$var['create']['perm'] = 0;

/*
		$var['options']['text'] = LAN_OPTIONS;
		$var['options']['link'] = e_SELF."?options";
		$var['options']['perm'] = "0";*/

		e107::getNav()->admin($this->pluginTitle, $action, $var);
	}
}

function admin_config_adminmenu()
{
	global $ef;
	global $action;
	$ef->show_options($action);
}


if($_POST['save_settings'])   // Needs to be saved before e_meta.php is loaded by auth.php.
{
    $tpref['customjs'] = $_POST['customjs'];
    $tpref['theme_advanced_buttons1'] = $_POST['theme_advanced_buttons1'];
    $tpref['theme_advanced_buttons2'] = $_POST['theme_advanced_buttons2'];
	$tpref['theme_advanced_buttons3'] = $_POST['theme_advanced_buttons3'];
	$tpref['theme_advanced_buttons4'] = $_POST['theme_advanced_buttons4'];
	$tpref['plugins'] = $_POST['mce_plugins'];

	e107::getPlugConfig('tinymce')->setPref($tpref);
	e107::getPlugConfig('tinymce')->save(); 
}

	$tpref = e107::getPlugConfig('tinymce')->getPref(); 



if($_POST['save_settings']) // is there an if $emessage?   $emessage->hasMessage doesn't return TRUE.
{
	$emessage->add(LAN_UPDATED, E_MESSAGE_SUCCESS);
	e107::getRender()->tablerender(LAN_UPDATED, $emessage->render());
}


 	if(!$tpref['theme_advanced_buttons1'])
	{
    	$tpref['theme_advanced_buttons1'] = "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect";
	}

	if(!$tpref['theme_advanced_buttons2'])
	{
    	$tpref['theme_advanced_buttons2'] = "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor";
	}

	if(!$tpref['theme_advanced_buttons3'])
	{
		$tpref['theme_advanced_buttons3'] = "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen,emoticons,ibrowser";
	}

	if(!$tpref['theme_advanced_buttons4'])
	{
		$tpref['theme_advanced_buttons4'] = "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage";
	}


function edit_theme()
{
	$ns = e107::getRender();

	
	
 $text = "<div style='text-align:center'>
    <form method='post' action='".e_SELF."'>
	<fieldset id='plugin-tinymce-config'>
     <table class='adminform'>
     	<colgroup>
     		<col class='col-label' />
     		<col class='col-control' />
     	</colgroup>
    <tr>
    <td>Preview<div style='padding:20px'>[<a href='javascript:start_tinyMce();'>Refresh Preview</a>]
	<br /><br />[<a href='#' onclick=\"tinyMCE.execCommand('mceToggleEditor',false,'content');\">Toggle WYSIWYG</a>]
	</div>
	</td>
    <td>
    <textarea id='content' class='e-wysiwyg tbox' rows='10' cols='10' name='name3'  style='width:80%'>     </textarea>
    </td>
    </tr>

    <tr>
    <td>Installed Plugins</td>
    <td><div style='width:80%'>
    ";

    foreach($plug_array as $mce_plg)
	{
		$checked = (in_array($mce_plg,$tpref['plugins'])) ? "checked='checked'" : "";
    	$text .= "<div style='width:25%;float:left'><input type='checkbox' name='mce_plugins[]' value='".$mce_plg."' $checked /> $mce_plg </div>";
	}



	$text .= "</div>
    </td>
    </tr>

	<tr>
    <td>Button Layout</td>
    <td style='width:80%' class='forumheader3'>";
    for ($i=1; $i<=4; $i++)
	{
		$rowNm = "theme_advanced_buttons".$i;
    	$text .= "\t<input class='tbox' style='width:97%' type='text' name='".$rowNm."' value='".$tpref[$rowNm]."' />\n";
    }

	$text .= "
	</td>
	</tr>

	<tr>
    <td>Custom TinyMce Javascript</td>
    <td>
    <textarea rows='5' cols='10' name='customjs' class='tbox' style='width:80%'>".$tpref['customjs']."</textarea>
    </td>
    </tr>
	</table>
	<div class='buttons-bar center'>";
    $text .= "<input class='btn btn-default btn-secondary button' type='submit' name='save_settings' value='".LAN_SAVE."' />";
    $text .= "
    </div>
	</fieldset>
    </form>
    </div>";

    $ns -> tablerender("TinyMCE Configuration", $text);
}





require_once(e_ADMIN."footer.php");


?>
