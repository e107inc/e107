<?php
/*
* e107 website system
*
* Copyright (C) 2008-2015 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Linkwords plugin - admin page
*
*/

require_once('../../class2.php');

if (!getperms('P') || !e107::isInstalled('linkwords'))
{
	e107::redirect('admin');
	 exit ;
}

e107::lan('linkwords', true); 

define('LW_CACHE_TAG', 'nomd5_linkwords');


class linkwords_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'	=> array(
			'controller' 	=> 'linkwords_ui',
			'path' 			=> null,
			'ui' 			=> 'linkwords_form_ui',
			'uipath' 		=> null
		),
	);


	protected $adminMenu = array(
		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),
		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'
	);

	protected $menuTitle = LAN_PLUGIN_LINKWORDS_NAME;
}


class linkwords_ui extends e_admin_ui
{
	protected $pluginTitle		= LAN_PLUGIN_LINKWORDS_NAME;
	protected $pluginName		= 'linkwords';
	//	protected $eventName		= 'linkwords-linkwords'; // remove comment to enable event triggers in admin.
	protected $table			= 'linkwords';
	protected $pid				= 'linkword_id';
	protected $perPage			= 10;
	protected $batchDelete		= true;
	//	protected $batchCopy		= true;
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

	protected $listOrder		= 'linkword_id DESC';

	protected $fields 		= array (  
		'checkboxes'             =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
    	'linkword_id'           =>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
	    'linkword_word'         =>   array ( 'title' => LWLAN_21, 'type' => 'tags', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'validate' => true, 'help' => LAN_LW_HELP_11, 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
	    'linkword_link'         =>   array ( 'title' => LWLAN_6, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'validate' => true, 'help' => LAN_LW_HELP_12, 'readParms' => '', 'writeParms' => 'size=xxlarge', 'class' => 'left', 'thclass' => 'left',  ),
	    'linkword_active'       =>   array ( 'title' => LAN_ACTIVE, 'type' => 'dropdown', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => array(), 'left' => 'center', 'thclass' => 'left',  ),
	    'linkword_tooltip'      =>   array ( 'title' => LWLAN_50, 'type' => 'textarea', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => LAN_LW_HELP_13, 'readParms' => '', 'writeParms' => array('size'=>'xxlarge'), 'class' => 'left', 'thclass' => 'left',  ),
	    'linkword_limit'       =>   array ( 'title' => LWLAN_67, 'type' => 'number', 'data' => 'int', 'width' => '10%', 'help' => LAN_LW_HELP_15, 'readParms' => '', 'writeParms' => array('default'=>3), 'class' => 'right', 'thclass' => 'right',  ),
	    'linkword_tip_id'       =>   array ( 'title' => LAN_ID, 'type' => 'number', 'data' => 'int', 'width' => '5%', 'help' => LAN_LW_HELP_16, 'readParms' => '', 'writeParms' => '', 'class' => 'right', 'thclass' => 'right',  ),
	    'linkword_newwindow'    =>   array ( 'title' => LWLAN_55, 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'inline' => true, 'help' => LAN_LW_HELP_17, 'filter'=>true, 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		'options'               =>   array ( 'title' => LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
	);

	protected $fieldpref = array();

	protected $prefs = array(
		'lw_context_visibility'	=> array('title' => LWLAN_26, 'type' => 'checkboxes', 'help' => LAN_LW_HELP_01),
		'lw_ajax_enable'		=> array('title' => LWLAN_59, 'type' => 'boolean', 	'data' => 'string', 'help' => LAN_LW_HELP_02),
		'lw_notsamepage'		=> array('title' => LWLAN_64, 'type' => 'boolean', 	'data' => 'string', 'help' => LAN_LW_HELP_03),
		'linkword_omit_pages'	=> array('title' => LWLAN_28, 'type' => 'textarea',	'data' => 'string', 'help' => LAN_LW_HELP_04),
		'lw_custom_class'	    => array('title' => LWLAN_66, 'type' => 'text', 	'writeParms' => array('placeholder' => LAN_OPTIONAL), 'data' => 'string', 'help' => LAN_LW_HELP_05),
	);

	public function init()
	{

		if($this->getAction() == 'list')
		{
			$this->fields['linkword_word']['title'] = LWLAN_5;
		}

		// Set drop-down values (if any).
		$this->fields['linkword_active']['writeParms']['optArray'] =  array(
				1 => LAN_INACTIVE, 
				0 => LWLAN_52, 
				2 => LWLAN_53, 
				3 => LWLAN_54
		);

		$this->prefs['lw_context_visibility']['writeParms']['optArray'] = array(
			'TITLE' 		=> LWLAN_33,
			'SUMMARY' 		=> LWLAN_34,	
			'BODY' 			=> LWLAN_35,	
			'DESCRIPTION' 	=> LWLAN_36,
			'USER_TITLE' 	=> LWLAN_40,	
			'USER_BODY'  	=> LWLAN_41
		);

		if(!empty($_POST['etrigger_save']))
		{
			e107::getCache()->clear_sys(LW_CACHE_TAG);
		}
	}

	public function renderHelp()
	{
		if($this->getAction() == 'create')
		{
			return array('caption' => LAN_HELP, 'text' => LAN_LW_HELP_10);	
		}
	}


	// ------- Customize Create --------

	public function beforeCreate($new_data, $old_data)
	{
		return $new_data;
	}

	public function afterCreate($new_data, $old_data, $id)
	{
		e107::getCache()->clear_sys(LW_CACHE_TAG);
		// do something
	}

	public function onCreateError($new_data, $old_data)
	{
		// do something
	}


	// ------- Customize Update --------

	public function beforeUpdate($new_data, $old_data, $id)
	{
		return $new_data;
	}

	public function afterUpdate($new_data, $old_data, $id)
	{
		e107::getCache()->clear_sys(LW_CACHE_TAG);
		// do something
	}

	public function onUpdateError($new_data, $old_data, $id)
	{
		// do something
	}


	public function afterDelete($deleted_data, $id, $deleted_check)
	{
		e107::getCache()->clear_sys(LW_CACHE_TAG);

	}

}



class linkwords_form_ui extends e_admin_form_ui
{

}

new linkwords_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;



/*


require_once(e_ADMIN.'auth.php');

e107::lan('linkwords', true); // e_PLUGIN.'linkwords/languages/'.e_LANGUAGE.'_admin.php'

define('LW_CACHE_TAG', 'nomd5_linkwords');

$pref = e107::getConfig()->getPref();

//	print_a($pref['lw_context_visibility']);


$mes = e107::getMessage();
$tp = e107::getParser();
$frm = e107::getForm();

$lw_context_areas = array(
		'TITLE' => LWLAN_33,
		'SUMMARY' => LWLAN_34,
		'BODY' => LWLAN_35,
		'DESCRIPTION' => LWLAN_36,
		'USER_TITLE' => LWLAN_40,
		'USER_BODY'  => LWLAN_41
		// Don't do the next three - linkwords are meaningless on them
//			'olddefault' => LWLAN_37,
//			'linktext' => LWLAN_38,
//			'rawtext' => LWLAN_39'
	);

// Yes, I know its a silly order - but that's history!
$lwaction_vals = array(1=>LAN_INACTIVE, 0=>LWLAN_52, 2=>LWLAN_53, 3=>LWLAN_54);
$frm = e107::getForm();
// Generate dropdown for possible actions on finding a linkword
function lw_act_opts($curval)
{
global $lwaction_vals;
$ret = '';
foreach ($lwaction_vals as $opt => $val)
{
$selected = ($curval == $opt ? "selected='selected'" : '');
$ret .= "<option value='{$opt}' {$selected}>{$val}</option>\n";
}
return $ret;
}

	
$deltest = array_flip($_POST);

if(isset($deltest[LAN_DELETE]))
{
$delete_id = intval(str_replace('delete_', '', $deltest[LAN_DELETE]));

if ($sql->db_Count('linkwords', '(*)', "WHERE linkword_id = ".$delete_id))
{
	$sql->db_Delete('linkwords', 'linkword_id='.$delete_id);
	e107::getLog()->add('LINKWD_03','ID: '.$delete_id,'');
	$e107->ecache->clear_sys(LW_CACHE_TAG);
	//$message = LWLAN_19;
	$mes->addSuccess(LAN_DELETED);
}
}

if(e_QUERY)
{
$lw_qs = explode('.', e_QUERY);
if (!isset($lw_qs[0])) $lw_qs[0] = 'words';
if (!isset($lw_qs[1])) $lw_qs[1] = -1;
$action = $lw_qs[0];
$id = intval($lw_qs[1]);
}
if (!isset($action)) $action = 'words';

if (isset($_POST['saveopts_linkword']))
{  // Save options page
// Array of context flags
$pref['lw_context_visibility'] = array(			
		'OLDDEFAULT' => FALSE,
		'TITLE' => FALSE,
		'USER_TITLE' => FALSE,
		'SUMMARY' => FALSE,
		'BODY' => FALSE,
		'USER_BODY' => FALSE,
		'DESCRIPTION' => FALSE,
		'LINKTEXT' => FALSE,
		'RAWTEXT' => FALSE
		);
foreach ($_POST['lw_visibility_area'] as $can_see)
{
	if (key_exists($can_see,$lw_context_areas))
	{
		$pref['lw_context_visibility'][$can_see] = TRUE;
	}
}
// Text area for 'exclude' pages - use same method as for menus
$pagelist = explode("\r\n", $_POST['linkword_omit_pages']);
for ($i = 0 ; $i < count($pagelist) ; $i++) 
{
	$pagelist[$i] = trim($pagelist[$i]);
}
$pref['lw_page_visibility'] = '2-'.implode("|", $pagelist);		// '2' for 'hide on specified pages'
$pref['lw_ajax_enable'] = isset($_POST['lw_ajax_enable']);
$pref['lw_notsamepage'] = isset($_POST['lw_notsamepage']);
save_prefs();
$logString = implode(', ',$pref['lw_context_visibility']).'[!br!]'.$pref['lw_page_visibility'].'[!br!]'.$pref['lw_ajax_enable'].'[!br!]'.$pref['lw_notsamepage'];
e107::getCache()->clear_sys(LW_CACHE_TAG);
e107::getLog()->add('LINKWD_04',$logString,'');
}


if (isset($_POST['submit_linkword']) || isset($_POST['update_linkword']))
{
if(!$_POST['linkwords_word'] && $_POST['linkwords_url'])
{	// Key fields empty
	$mes->addError(LAN_REQUIRED_BLANK);
}
else
{
	$data['linkword_word'] = $tp->toDB($_POST['linkword_word']);
	$data['linkword_link'] = $tp->toDB($_POST['linkword_link']);
	$data['linkword_tooltip'] = $tp->toDB($_POST['linkword_tooltip']);
	$data['linkword_tip_id'] = intval($_POST['linkword_tip_id']);
	$data['linkword_active'] = intval($_POST['linkword_active']);
	$data['linkword_newwindow'] = isset($_POST['linkword_newwindow']) ? 1 : 0;

	$logString = implode('[!br!]',$data);
	if (isset($_POST['submit_linkword']))
	{
		if ($sql->db_Insert('linkwords', $data))
		{
			e107::getLog()->add('LINKWD_01',$logString,'');
			$mes->addSuccess(LAN_CREATED);
		}
		else
		{
			//$message = LWLAN_57;
			$mes->addError(LAN_CREATED_FAILED);
		}
	}
	elseif (isset($_POST['update_linkword']))
	{
		$id = intval(varset($_POST['lw_edit_id'],0));
		if (($id > 0) && $sql->db_UpdateArray('linkwords', $data, ' WHERE `linkword_id`='.$id))
		{
			$mes->addSuccess(LAN_UPDATED);
			$logString = 'ID: '.$id.'[!br!]'.$logString;
			e107::getLog()->add('LINKWD_02',$logString,'');
		}
		else
		{
			$mes->addError(LAN_UPDATED_FAILED);
		}
	}

	e107::getCache()->clear_sys(LW_CACHE_TAG);
}
}

$ns->tablerender($caption, $mes->render() . $text);


$chkNewWindow = " checked='checked'";			// Open links in new window by default
if($action == "edit")
{
if($sql -> db_Select("linkwords", "*", "linkword_id=".$id))
{
$row = $sql -> db_Fetch();
extract($row);
$chkNewWindow = $row['linkword_newwindow'] ? " checked='checked'" : '';			// Open links in new window by default
define("LW_EDIT", TRUE);
}
}
else
{
$linkword_word = '';
$linkword_link = '';
$linkword_active = '';
$linkword_tooltip = '';
$linkword_tip_id = '';
}



if (($action == 'words') || ($action == 'edit'))
{
	
$text = "
<form method='post' action='".e_SELF."?words'>
<table class='table adminform'>
<colgroup>
	<col class='col-label' />
	<col class='col-control' />
</colgroup>
<tr>
<td>".LWLAN_21."</td>
<td>
	<input class='tbox' type='text' name='linkword_word' size='40' value='".$linkword_word."' maxlength='100' />
</td>
</tr>

<tr>
<td>".LWLAN_6."</td>
<td>
	<input class='tbox' type='text' name='linkword_link' size='60' value='".$linkword_link."' maxlength='250' /><br />
	<input type='checkbox' name='linkword_newwindow' value='1'{$chkNewWindow} /> ".LWLAN_55."
</td>
</tr>

<tr>
<td>".LWLAN_50."</td>
<td>
	<textarea rows='3' cols='80' class='tbox' name='linkword_tooltip'>".$linkword_tooltip."</textarea>
</td>
</tr>

<tr>
<td>".LWLAN_62."</td>
<td>
	<input class='tbox' type='text' name='linkword_tip_id' size='10' value='".$linkword_tip_id."' maxlength='10' /><span class='field-help'>".LWLAN_63."</span>
</td>
</tr>

<tr>
<td>".LWLAN_22."</td>
<td>
	<select class='tbox' name='linkword_active'>".lw_act_opts($linkword_active)."</select>
</td>
</tr>
</table>
<div class='buttons-bar center'>
".
(defined("LW_EDIT") ? $frm->admin_button('update_linkword','no-value','update',LAN_UPDATE) .  "<input type='hidden' name='lw_edit_id' value='{$id}' />" : $frm->admin_button('submit_linkword','no-value','submit',LAN_CREATE))."
</div>
</form>\n";


$ns -> tablerender(LWLAN_31, $text);
}

if (($action == 'words') || ($action == 'edit'))
{


$text = "<div class='center'>\n";
if(!$sql -> db_Select("linkwords"))
{
//$text .= LWLAN_4;
$mes->addInfo(LWLAN_4);
}
else
{
$text = "
<table class='table adminlist'>
	<colgroup>
  	<col style='width:  5%; vertical-align:top;' />
  	<col style='width: 15%; vertical-align:top;' />
  	<col style='width: 20%; vertical-align:top;' />
  	<col style='width: 10%; vertical-align:top;' />
  	<col style='width: 25%; vertical-align:top;' />
  	<col style='width: 5%; vertical-align:top;' />
  	<col style='width: 10%; vertical-align:top; text-align: center;' />
  	<col style='width: 15%; vertical-align:top; text-align: center;' />
	</colgroup>	
<tr>
	<td>".LAN_ID."</td>
	<td>".LWLAN_5."</td>
	<td>".LWLAN_6."</td>
	<td>".LWLAN_56."</td>
	<td>".LWLAN_50."</td>
	<td>".LWLAN_60."</td>
	<td>".LWLAN_7."</td>
	<td>".LAN_OPTIONS."</td>
</tr>\n";

while($row = $sql->db_Fetch())
{
	$text .= "
	<tr>
		<td>{$row['linkword_id']}</td>
		<td>{$row['linkword_word']}</td>
		<td>{$row['linkword_link']}</td>
		<td>".($row['linkword_newwindow'] ? LAN_YES : LAN_NO)."</td>
		<td>{$row['linkword_tooltip']}</td>
		<td>".($row['linkword_tip_id'] > 0 ? $row['linkword_tip_id'] : '')."</td>
		<td>".$lwaction_vals[$row['linkword_active']]."</td>
		<td>
			<form action='".e_SELF."' method='post' id='myform_{$row['linkword_id']}'  onsubmit=\"return jsconfirm('".LWLAN_18." [ID: {$row['linkword_id']} ]')\">
			<div>
				<input class='btn btn-default button' type='button' onclick=\"document.location='".e_SELF."?edit.{$row['linkword_id']}'\" value='".LAN_EDIT."' id='edit_{$row['linkword_id']}' name='edit_linkword_id' />
				<input class='btn btn-default button' type='submit' value='".LAN_DELETE."' id='delete_{$row['linkword_id']}' name='delete_{$row['linkword_id']}' />
			</div>
			</form>\n
		</td>
	</tr>
	";
}
$text .= "</table>";
}

$ns->tablerender(LWLAN_11, $mes->render() . $text);
}



if ($action=='options')
{
$menu_pages = substr($pref['lw_page_visibility'],2);    // Knock off the 'show/hide' flag
$menu_pages = str_replace("|", "\n", $menu_pages);
$AjaxEnable = varset($pref['lw_ajax_enable'],0);
$text = "
<div>
<form method='post' action='".e_SELF."?options'>
<table class='table adminform'>
<colgroup>
<col style='width: 30%; />
<col style='width: 70%; />
</colgroup>
<tr>
<td>".LWLAN_26."</td>
<td>";
foreach ($lw_context_areas as $lw_key=>$lw_desc)
{
$checked = $pref['lw_context_visibility'][$lw_key] ? "checked='checked'" : '';
$text .= "<input type='checkbox' name='lw_visibility_area[]' value='{$lw_key}' {$checked} /> {$lw_desc}<br />";
}
$text .= "<span class='field-help'>".LWLAN_27."</span></td>
</tr>

<tr>
<td>".LWLAN_28."</td>
<td><textarea rows='5' cols='60' class='tbox' name='linkword_omit_pages' >".$menu_pages."</textarea><span class='field-help'>".LWLAN_29."</span>
</td>
</tr>";

$checked = varset($pref['lw_ajax_enable'],0) ? 'checked=checked' : '';
$text .= "
<tr>
	 <td>".LWLAN_59."</td>
	 <td><input type='checkbox' name='lw_ajax_enable' {$checked} /></td>
</tr>";

$checked = varset($pref['lw_notsamepage'],0) ? 'checked=checked' : '';
$text .= "
<tr>
	<td>".LWLAN_64."</td>
	<td><input type='checkbox' name='lw_notsamepage' {$checked} /><span class='field-help'>".LWLAN_65."</span></td>
</tr>

</table>
<div class='buttons-bar center'>
".$frm->admin_button('saveopts_linkword','no-value','submit', LAN_UPDATE)."
</div>
</form>
</div>\n";

$ns -> tablerender(LAN_OPTIONS, $text);
}



function admin_config_adminmenu()
{
if (e_QUERY) 
{
$tmp = explode(".", e_QUERY);
$action = $tmp[0];
}
if (!isset($action) || ($action == ""))
{
$action = "words";
}
$var['words']['text'] = LWLAN_24;
$var['words']['link'] = "admin_config.php";

$var['options']['text'] = LAN_OPTIONS;
$var['options']['link'] ="admin_config.php?options";

show_admin_menu(LWLAN_23, $action, $var);
}


require_once(e_ADMIN."footer.php");
*/
?>
