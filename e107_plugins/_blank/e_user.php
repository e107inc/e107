<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

// v2.x Standard 
class _blank_user // plugin-folder + '_user'
{		
		
	function profile($udata)  // display on user profile page.
	{

		$var = array(
			0 => array('label' => "Label", 'text' => "Some text to display", 'url'=> e_PLUGIN_ABS."_blank/blank.php")
		);
		
		return $var;
	}



	function fields()
	{

		$fields = array(



		);


	}


	
}