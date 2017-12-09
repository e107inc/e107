<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * XXX HIGHLY EXPERIMENTAL AND SUBJECT TO CHANGE WITHOUT NOTICE. 
*/

if (!defined('e107_INIT')) { exit; }


class social_event
{

	/*
	* constructor
	*/
	function __construct()
	{
		
		
	}


	function config()
	{
		$event = array();
		
		$event[] = array(
			'name'     => "system_meta_pre",
			'function' => "ogg_image_add",
		);

		return $event;
		
	}
	
	/**
	 * Callback function to add og:image if there is no any
	 */
	function ogg_image_add()
	{
		$ogImage = e107::pref('social','og_image', false);
		
		if(e_ADMIN_AREA !==true) {
			$ogimgexist = FALSE;
			
			// check if we have og:image defined
			$response = e107::getSingleton('eResponse');
			$data = $response->getMeta();
			foreach($data as $m) {
				if($m['name'] == 'og:image') {
					$ogimgexist = TRUE;
				}
			}
			
			if(!empty($ogImage) && !$ogimgexist) {
				e107::meta('og:image',e107::getParser()->thumbUrl($ogImage,'w=500',false,true));
				unset($ogImage);
			}
		
		}		
		
	}

} //end class

?>
