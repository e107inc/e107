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

require_once(__DIR__.'/../../class2.php');

if (!getperms('P') || !e107::isInstalled('linkwords'))
{
	e107::redirect('admin');
	 exit ;
}

e107::lan('linkwords', true); 
if(!defined('LW_CACHE_TAG'))
{
	define('LW_CACHE_TAG', 'nomd5_linkwords');
}

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
		'main/test'		=> array('caption'=> LAN_TEST, 'perm' => 'P')
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
    	'linkword_id'           =>   array ( 'title' => LAN_ID, 'type'=>'number', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
	    'linkword_word'         =>   array ( 'title' => LWLAN_21, 'type' => 'tags', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'validate' => true, 'help' => LAN_LW_HELP_11, 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
	    'linkword_link'         =>   array ( 'title' => LWLAN_6, 'type' => 'text', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'validate' => true, 'help' => LAN_LW_HELP_12, 'readParms' => '', 'writeParms' => 'size=xxlarge', 'class' => 'left', 'thclass' => 'left',  ),
	    'linkword_active'       =>   array ( 'title' => LAN_ACTIVE, 'type' => 'dropdown', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' =>  LAN_LW_HELP_13, 'readParms' => '', 'writeParms' => array(), 'left' => 'center', 'thclass' => 'left',  ),
	    'linkword_tooltip'      =>   array ( 'title' => LWLAN_50, 'type' => 'textarea', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' =>'', 'readParms' => '', 'writeParms' => array('size'=>'xxlarge'), 'class' => 'left', 'thclass' => 'left',  ),
	    'linkword_limit'       =>   array ( 'title' => LWLAN_67, 'type' => 'number', 'data' => 'int', 'width' => '10%', 'help' => LAN_LW_HELP_15, 'readParms' => '', 'writeParms' => array('default'=>3), 'class' => 'right', 'thclass' => 'right',  ),
	    'linkword_tip_id'       =>   array ( 'title' => LAN_ID, 'type' => 'number', 'data' => 'int', 'width' => '5%', 'help' => LAN_LW_HELP_16, 'readParms' => '', 'writeParms' => '', 'class' => 'right', 'thclass' => 'right',  ),
	    'linkword_newwindow'    =>   array ( 'title' => LWLAN_55, 'type' => 'boolean', 'data' => 'int', 'width' => 'auto', 'inline' => true, 'help' => LAN_LW_HELP_17, 'filter'=>true, 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		'linkword_rel'    =>   array ( 'title' => LAN_RELATIONSHIP, 'type' => 'tags', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => LAN_RELATIONSHIP_HELP, 'filter'=>false, 'readParms' => '', 'writeParms'=>array('placeholder'=>'eg.nofollow,noreferrer','size'=>'xlarge'), 'class' => 'center', 'thclass' => 'center',  ),

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

	public function testPage()
	{
		$text = '';

		if(!empty($_POST['runLinkwordTest']))
		{
		//	$text .= "<strong>Result:</strong><br />";
			$result = e107::getParser()->toHTML($_POST['test_body'], false, 'BODY');

			$text .= "<div class='well' style='padding:30px'>".$result."</div>";
			$text .= "<div class='well' style='padding:30px; margin-bottom:30px'>".htmlentities($result)."</div>";
		}

		$frm = $this->getUI();
		$text .= $frm->open('linkwordsTest');
		$text .= "<div style='width:800px'>";
		$text .= $frm->textarea('test_body', varset($_POST['test_body']), 10, 80, ['class'=>'form-control','placeholder'=>'Start writing...']);

		$text .= "<div class='buttons-bar center'><p>";
		$text .= $frm->submit('runLinkwordTest', LAN_TEST);
		$text .= "</p></div>";
		$text .= "</div>";
		$text .= $frm->close();

		return $text;


	}

}



class linkwords_form_ui extends e_admin_form_ui
{

}

new linkwords_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");




