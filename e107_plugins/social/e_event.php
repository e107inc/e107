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


class e_event_social //FIXME should be social_event
{
	/*
	* 	all event methods have a single parameter
	* 	@param array $data array containing
	*	@param string $method form,insert,update,delete
	*	@param string $table the table name of the calling plugin
	*	@param int $id item id of the record
	*	@param string $plugin identifier for the calling plugin
	*	@param string $function identifier for the calling function
	*/

	/*
	* constructor
	*/
	function __construct()
	{
		
		
	}
	/*
	* add form field
	* @param array $data
	*/
	function event_form($data)
	{
		
		if($data['table'] == 'news' || $data['table'] == 'page')
		{			
			return $this->socialForm($data);	
		}
		
	}

	/*
	* handle db create
	* @param array $data
	*/
	function event_create($data)
	{
		//print_a($data);
		$this->socialPost($data);
	}

	/*
	* handle db update
	* @param array $data
	*/
	function event_update($data)
	{
		$this->socialPost($data);
	}

	/*
	* handle db delete
	* @param array $data
	*/
	function event_delete($data)
	{
		// N/A 
	}
	
	
	/**
	 * TODO - Make functional.. using hybridAuth class. 
	 * ie. Admin enters a message, and the message + the newly generated link are submitted to Twitter or FB. 
	 */
	
	function socialForm($data='')
	{
		
		//TODO Check Social Logins Pref for presence of FB and Twitter details.. if not found, return nothing.(see admin->preferences)
		
		$frm = e107::getForm();
				
		$input[0]['caption'] 	= "Post to Twitter";
		$input[0]['html']		= $frm->text('twitterPost','', 150); // Text to post..  Link will automatically be appended to message.
		$input[0]['help']		= "Enter a message to post a link to this item on Twitter"; // Text to post..  Link will automatically be appended to message.  

		
		$input[1]['caption'] 	= "Post to Facebook";
		$input[1]['html'] 		= $frm->text('FacebookPost');
			
		return $input;	
		
	}
	
	//TODO Function to Post Data to Twitter or FB. 
	//  using hybridAuth class. 
	function socialPost($data)
	{
		
		
	}

} //end class

?>