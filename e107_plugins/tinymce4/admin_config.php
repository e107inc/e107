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
require_once(__DIR__.'/../../class2.php');
if( !e107::isInstalled('tinymce4'))
{
	e107::redirect('admin');
	exit();
}

$result = e107::lan('tinymce4', true);


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
			 'main/preview'		=> array('caption'=> LAN_PREVIEW, 'perm' => 'P', 'icon'=>'fa-eye')
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
			'paste_as_text'		    => array('title' => TMCEALAN_1, 'type'=>'boolean', 'data' => 'int','help'=> ''),
			'browser_spellcheck'    => array('title' => TMCEALAN_2, 'type'=>'boolean', 'data' => 'int','help'=> TMCEALAN_3),
			'visualblocks'          => array('title' => TMCEALAN_4, 'type'=>'boolean', 'data' => 'int','help'=> TMCEALAN_5),
			'use_theme_style'       => array('title' => TMCEALAN_7, 'type'=>'boolean', 'data' => 'int','help'=> TMCEALAN_8),
			'code_highlight_class'  => array('title' => TMCEALAN_6, 'type'=>'text', 'data' => 'str','help'=> ''),
		);


		function previewPage()
		{
			e107::wysiwyg(true);
			return e107::getForm()->bbarea('preview');

		}
	}


	class tinymce4_ui_form extends e_admin_form_ui
	{

	}


new tinymce4_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();
require_once(e_ADMIN."footer.php");


