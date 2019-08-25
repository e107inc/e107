<?php
/*
* e107 website system
*
* Copyright (c) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom Featurebox install/uninstall/update routines
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/featurebox_setup.php,v $
* $Revision$
* $Date$
* $Author$
*
*/

if (!defined('e107_INIT')) { exit; }

class featurebox_setup
{
/*	
 	function install_pre($var)
	{
		// print_a($var);
		// echo "custom install 'pre' function<br /><br />";
	}
*/
	function install_post($var)
	{
		e107::includeLan(e_PLUGIN.'featurebox/languages/'.e_LANGUAGE.'_admin_featurebox.php');
		$mes = e107::getMessage();
		
		$e107_featurebox_category = array(
 			array('fb_category_id'=> 1,'fb_category_title'=>FBLAN_35,'fb_category_icon'=>'','fb_category_template'=>'bootstrap3_carousel','fb_category_random'=>'0','fb_category_class'=>'0','fb_category_limit'=>'0','fb_category_parms'=>''),
			array('fb_category_id'=> 2,'fb_category_title'=>FBLAN_36,'fb_category_icon'=>'','fb_category_template'=>'bootstrap_tabs','fb_category_random'=>'0','fb_category_class'=>'0','fb_category_limit'=>'0','fb_category_parms'=>''),
			array('fb_category_id'=> 3,'fb_category_title'=>FBLAN_34,'fb_category_icon'=>'','fb_category_template'=>'unassigned','fb_category_random'=>'0','fb_category_class'=>'255','fb_category_limit'=>'0','fb_category_parms'=>'')
		);
		
		$count = 0;
		foreach($e107_featurebox_category as $insert)
		{
			$count = e107::getDb()->insert('featurebox_category', $insert) ?  $count + 1 : $count;	
		}
		
	
		$status = ($count == 3) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR; 
		
		$mes->add(LAN_DEFAULT_TABLE_DATA." <strong>featurebox_category</strong>", $status);
		
		if($status)
		{
			$e107_featurebox = array(
			  array('fb_id'=>'4','fb_title'=>'Slide 1','fb_text'=>'Default Message - this is the default','fb_mode'=>'0','fb_class'=>'0','fb_rendertype'=>'0','fb_template'=>'bootstrap_carousel_default','fb_order'=>'3','fb_image'=>'','fb_imageurl'=>'','fb_category'=>'1'),
			  array('fb_id'=>'6','fb_title'=>'Slide 2','fb_text'=>'Suspendisse ac dui purus. Cras eleifend, velit sed dapibus pharetra, elit dolor mattis tellus, ac luctus nisi massa at ligula. Ut sagittis, neque consequat elementum convallis, lorem nulla molestie arcu, eu rutrum velit quam at metus! Nullam eu eleifend magna. Praesent eget leo felis, vel euismod nibh. Morbi sem eros, pellentesque eu scelerisque id, pretium id enim. Mauris aliquet molestie dui vel ultricies. Etiam consequat quam sed tellus facilisis sollicitudin. Vivamus malesuada iaculis metus.
			','fb_mode'=>'0','fb_class'=>'0','fb_rendertype'=>'0','fb_template'=>'bootstrap_carousel_left','fb_order'=>'4','fb_image'=>'','fb_imageurl'=>'','fb_category'=>'1'),
			  array('fb_id'=>'10','fb_title'=>'Slide 3','fb_text'=>'Suspendisse ac dui purus. Cras eleifend, velit sed dapibus pharetra, elit dolor mattis tellus, ac luctus nisi massa at ligula. Ut sagittis, neque consequat elementum convallis, lorem nulla molestie arcu, eu rutrum velit quam at metus! Nullam eu eleifend magna. Praesent eget leo felis, vel euismod nibh. Morbi sem eros, pellentesque eu scelerisque id, pretium id enim. Mauris aliquet molestie dui vel ultricies. Etiam consequat quam sed tellus facilisis sollicitudin. Vivamus malesuada iaculis metus.
			','fb_mode'=>'0','fb_class'=>'0','fb_rendertype'=>'0','fb_template'=>'bootstrap_carousel_right','fb_order'=>'4','fb_image'=>'','fb_imageurl'=>'','fb_category'=>'1')
			);
			
			foreach($e107_featurebox as $qry)
			{
				$status = e107::getDb('sql2')->insert('featurebox', $qry) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR; 	
			}

			$mes->add(LAN_DEFAULT_TABLE_DATA." <strong>featurebox</strong>", $status);
		}
		else 
		{
			$mes->addError(LAN_DEFAULT_TABLE_DATA);
		}
		
	}
/*	
	function uninstall_options()
	{
	
	}


	function uninstall_post($var)
	{
		// print_a($var);
	}
*/	

	function upgrade_required()
	{	
		if(!e107::getDb()->isTable('featurebox_category'))
		{
			return true; // true to trigger an upgrade alert, and false to not. 	
		}
		
	}
	

	function upgrade_pre($var)
	{
		e107::getDb()->gen("CREATE TABLE #featurebox_category (
		  `fb_category_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
		  `fb_category_title` varchar(200) NOT NULL DEFAULT '',
		  `fb_category_icon` varchar(255) NOT NULL DEFAULT '',
		  `fb_category_template` varchar(50) NOT NULL DEFAULT 'default',
		  `fb_category_random` tinyint(1) unsigned NOT NULL DEFAULT '0',
		  `fb_category_class` smallint(5) NOT NULL DEFAULT '0',
		  `fb_category_limit` tinyint(3) unsigned NOT NULL DEFAULT '1',
		  `fb_category_parms` text NOT NULL,
		  PRIMARY KEY (`fb_category_id`),
		  UNIQUE KEY `fb_category_template` (`fb_category_template`)
		) ENGINE=MyISAM;");
	}




	function upgrade_post($var)
	{
		$sql = e107::getDb();
		$currentVersion = $var->current_plug['plugin_version'];
		//$newVersion = $var->plug_vars['@attributes']['version'];
		if($currentVersion == '1.0')
		{
			$query = array();
			$query['fb_category_id'] = 0;
			$query['fb_category_title'] = FBLAN_INSTALL_03;
			$query['fb_category_template'] = 'unassigned';
			$query['fb_category_random'] = 0;
			$query['fb_category_class'] = e_UC_NOBODY;
			$query['fb_category_limit'] = 0;
			
			$inserted = $sql->insert('featurebox_category', $query);
			$status = $inserted ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR; 
			e107::getMessage()->add(FBLAN_INSTALL_01, $status);
			if($sql->getLastErrorNumber())
			{
				e107::getMessage()->addDebug($sql->getLastErrorText().'<br /><pre>'.$sql->getLastQuery().'</pre>');
			}
		}
	}
}
