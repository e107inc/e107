<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2016 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

// Generated e107 Plugin Admin Area

require_once(__DIR__.'/../../class2.php');
if (!getperms('P') || !e107::isInstalled('newsfeed'))
{
	e107::redirect('admin');
	exit;
}

e107::lan('newsfeed',true);


define('NEWSFEED_LIST_CACHE_TAG', 'newsfeeds'.e_LAN."_");
define('NEWSFEED_NEWS_CACHE_TAG', 'newsfeeds_news_'.e_LAN."_");


class newsfeed_adminArea extends e_admin_dispatcher
{

	protected $modes = array(

		'main'	=> array(
			'controller' 	=> 'newsfeed_ui',
			'path' 			=> null,
			'ui' 			=> 'newsfeed_form_ui',
			'uipath' 		=> null
		),


	);


	protected $adminMenu = array(

		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),

		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'
	);

	protected $menuTitle = LAN_PLUGIN_NEWSFEEDS_NAME;
}





class newsfeed_ui extends e_admin_ui
{

		protected $pluginTitle		= LAN_PLUGIN_NEWSFEEDS_NAME;
		protected $pluginName		= 'newsfeed';
	//	protected $eventName		= 'newsfeed-newsfeed'; // remove comment to enable event triggers in admin.
		protected $table			= 'newsfeed';
		protected $pid				= 'newsfeed_id';
		protected $perPage			= 10;
		protected $batchDelete		= true;
		protected $batchCopy		= true;
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
	//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= 'newsfeed_id DESC';

		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'newsfeed_id' =>			array ( 'title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'newsfeed_name' =>		array ( 'title' => LAN_TITLE, 'type' => 'text', 'data' => 'str', 'required'=>true,  'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'newsfeed_url' =>			array ( 'title' => LAN_URL, 'type' => 'url', 'data' => 'str', 'required'=>true, 'inline'=>true, 'width' => 'auto',  'help' => '', 'readParms' => '', 'writeParms' => array('size'=>'xxlarge'), 'class' => 'left', 'thclass' => 'left',  ),
		  'newsfeed_data' =>		array ( 'title' => LAN_DATA, 'type' => null,  'data' => false, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'newsfeed_description' =>	array ( 'title' => LAN_DESCRIPTION, 'type' => 'textarea', 'data' => 'str', 'width' => '40%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'newsfeed_image' =>		array ( 'title' => NFLAN_11, 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => LAN_OPTIONAL, 'readParms' => 'thumb=80x80', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),

		  'newsfeed_updateint' =>	array ( 'title' => NFLAN_18, 'type' => 'text', 'data' => 'int', 'inline'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => array('default'=>3600), 'class' => 'left', 'thclass' => 'left',  ),
		  'newsfeed_timestamp' =>	array ( 'title' => LAN_LAST_UPDATED, 'type' => 'method', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),

		  'newsfeed_active' =>		array ( 'title' => NFLAN_12, 'type' => 'radio', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => array('default'=>3, 'optArray'=>array(NFLAN_13,NFLAN_14,NFLAN_20,NFLAN_21)), 'class' => 'left', 'thclass' => 'left',  ),

		  'newsfeed_showmenu'	=>	array ( 'title' => NFLAN_45, 'type'=>'method', 'data'=>false, 'class'=>'center', 'thclass'=>'center' ),
		  'newsfeed_showmain'	=>	array ( 'title' => NFLAN_46, 'type'=>'method', 'data'=>false, 'class'=>'center', 'thclass'=>'center'),

		  'options' 			=>	array ( 'title' => LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '8%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);

		protected $fieldpref = array('newsfeed_name', 'newsfeed_url', 'newsfeed_updateint', 'newsfeed_timestamp', 'newsfeed_active', 'newsfeed_showmenu', 'newsfeed_showmain');


	//	protected $preftabs        = array('General', 'Other' );
		protected $prefs = array(
		);


		public function init()
		{
			if($this->getAction() == 'edit' || $this->getAction() == 'create')
			{
				$this->fields['newsfeed_updateint']['type'] = 'number';
			}
			// Set drop-down values (if any).

		}


		// ------- Customize Create --------

		public function beforeCreate($new_data, $old_data)
		{
			if(isset($new_data['newsfeed_showmenu']))
			{
			    $new_data['newsfeed_image'] = e107::getParser()->toDB($new_data['newsfeed_image'])."::".intval($new_data['newsfeed_showmenu'])."::".intval($new_data['newsfeed_showmain']);
			}

			$new_data['newsfeed_timestamp'] = 0;

			return $new_data;
		}

		public function afterCreate($new_data, $old_data, $id)
		{
			// do something
			e107::getCache()->clear(NEWSFEED_LIST_CACHE_TAG);
			e107::getCache()->clear(NEWSFEED_NEWS_CACHE_TAG);
		}

		public function onCreateError($new_data, $old_data)
		{
			// do something
		}


		// ------- Customize Update --------

		public function beforeUpdate($new_data, $old_data, $id)
		{
			if(isset($new_data['newsfeed_showmenu']))
			{
			    $new_data['newsfeed_image'] = e107::getParser()->toDB($new_data['newsfeed_image'])."::".intval($new_data['newsfeed_showmenu'])."::".intval($new_data['newsfeed_showmain']);

			}

			$new_data['newsfeed_timestamp'] = 0; // reset so the feed data refreshes.


			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			// do something
			e107::getCache()->clear(NEWSFEED_LIST_CACHE_TAG);
			e107::getCache()->clear(NEWSFEED_NEWS_CACHE_TAG);
		}

		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something
		}


	/*
		// optional - a custom page.
		public function customPage()
		{
			$text = 'Hello World!';
			return $text;

		}
	*/

}



class newsfeed_form_ui extends e_admin_form_ui
{


	// Custom Method/Function
	function newsfeed_active($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;

			case 'write': // Edit Page
				return $frm->text('newsfeed_active',$curVal, 255, 'size=large');
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}


	function newsfeed_image($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;

			case 'write': // Edit Page

				$tmp = explode('::',$curVal);

				return $frm->text('newsfeed_image',$tmp[0], 255, 'size=large');
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}


	function newsfeed_timestamp($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page
				if($curVal == 0)
				{
					return '-';
				}

				return e107::getParser()->toDate($curVal, 'relative');
			break;

			case 'write': // Edit Page

				// $tmp = explode('::',$curVal);

				return  e107::getParser()->toDate($curVal, 'relative').$this->hidden('newsfeed_timestamp',0);
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}

	function newsfeed_showmain($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page
				$data = $this->getController()->getListModel()->get('newsfeed_image');
				list($image,$menu,$main) = explode('::',$data);

				return intval($main);
			break;

			case 'write': // Edit Page

				$data = $this->getController()->getModel()->get('newsfeed_image');
				list($image,$menu,$main) = explode('::',$data);

				if(empty($main))
				{
					$main = 10;
				}

				return $frm->number('newsfeed_showmain',$main, 3);
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}



	function newsfeed_showmenu($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page
				$data = $this->getController()->getListModel()->get('newsfeed_image');
				list($image,$menu,$main) = explode('::',$data);

				return intval($menu);
			break;

			case 'write': // Edit Page
				$data = $this->getController()->getModel()->get('newsfeed_image');
				list($image,$menu,$main) = explode('::',$data);

				if(empty($menu))
				{
					$menu = 10;
				}

				return $frm->number('newsfeed_showmenu',$menu, 3);
			break;

			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}
}


new newsfeed_adminArea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");

