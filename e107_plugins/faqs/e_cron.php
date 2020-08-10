<?php
	/*
	 * e107 website system
	 *
	 * Copyright (C) 2008-2015 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	*/

	if (!defined('e107_INIT')) { exit; }

    e107::lan('faqs', 'admin',true);

	class faqs_cron // include plugin-folder in the name.
	{
		function config()
		{
			$tp = e107::getParser();
			$cron = array();
			
			$siteadminemail  = e107::pref('core','siteadminemail'); 
			
			$cron[] = array(
				'name'			=> LANA_FAQ_CRON_1,
				'function'		=> "unanswered",
				'category'		=> "notify",
				'description' 	=> $tp->lanVars(LANA_FAQ_CRON_2, $siteadminemail)
			);

			return $cron;
		}

		function unanswered()
		{

			$sql = e107::getDb();
			$tp = e107::getParser();
			$limit = 25;

			$count = $sql->retrieve('faqs','faq_id',"faq_answer=''  ", true);

			$existing = $sql->retrieve('faqs','faq_id,faq_question,faq_datestamp',"faq_answer=''  ORDER BY faq_datestamp DESC LIMIT ".$limit, true);

			if(empty($existing))
			{
				return;
			}

			$questions = array();

			foreach($existing as $row)
			{
				$questions[] = "<i>".$row['faq_question']."</i><br /><small>".$tp->toDate($row['faq_datestamp'],'short')."</small>\n";
			//	$questions[] = $row['faq_question'];
			}


			//
		//	$questions = array( "<i>Test Question</i><br /><small>".$tp->toDate(time(),'short')."</small>");


			$name = SITENAME." ".LAN_AUTOMATION;

			$email = e107::pref('core','siteadminemail');
			$name = e107::pref('core','siteadmin');

			$link = $tp->replaceConstants("{e_PLUGIN}faqs/admin_config.php?mode=main&action=list&filter=pending", 'full');

			$body  = "<h2>".$tp->lanVars(LANA_FAQ_CRON_3, count($count), SITENAME)."</h2>";// Unanswered Questions at
			$body .= LANA_FAQ_CRON_4."<br />"; //To answer these qus.
			$body .= "<a href='{$link}'>".LAN_CLICK_HERE."</a><br />";
			$body .= $tp->lanVars(LANA_FAQ_CRON_5,$limit);//The limit
			$body .= "<ul><li>".implode("</li><li>",$questions)."</li></ul>";


			$eml = array(
					'subject' 		=> $tp->lanVars(LANA_FAQ_CRON_6, array('x'=>count($count), 'y'=> date('d-M-Y'))),
				//	'sender_email'	=> $email,
					'sender_name'	=> SITENAME." ".LAN_AUTOMATION,
			//		'replyto'		=> $email,
					'html'			=> true,
					'template'		=> 'default',
					'body'			=> $body
				);

				e107::getEmail()->sendEmail($email, $name, $eml);

		}




	}



