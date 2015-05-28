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


	class faqs_cron // include plugin-folder in the name.
	{
		function config()
		{

			$cron = array();

			$cron[] = array(
				'name'			=> "Unanswered Questions Report", //TODO LAN
				'function'		=> "unanswered",
				'category'		=> "notify",
				'description' 	=> "Mails a report of unanswered questions to ".e107::pref('core','siteadminemail').'.' // TODO LAN
			);

			return $cron;
		}

		function unanswered()
		{

			$sql = e107::getDb();
			$tp = e107::getParser();

			$existing = $sql->retrieve('faqs','faq_question,faq_datestamp',"faq_answer=''  ORDER BY faq_datestamp ", true);

			if(empty($existing))
			{
				return;
			}

			$questions = array();

			foreach($existing as $row)
			{
				$questions[] = "<i>".$row['faq_question']."</i><br /><small>".$tp->toDate($row['faq_datestamp'],'short')."</small>";
			}

			$name = SITENAME . " Automation";

			$email = e107::pref('core','siteadminemail');
			$name = e107::pref('core','siteadmin');

			$link = $tp->replaceConstants("{e_PLUGIN}faqs/admin_config.php?mode=main&action=list&filter=pending", 'full');

			$eml = array(
					'subject' 		=> count($existing)." Unuanswered Question as of ".date('d-M-Y')." ",
				//	'sender_email'	=> $email,
					'sender_name'	=> SITENAME . " Automation",
			//		'replyto'		=> $email,
					'html'			=> true,
					'template'		=> 'default',
					'body'			=> "
							<h2>".count($existing)." Unuanswered Questions at ".SITENAME."</h2>To answer these questions, please login to ".SITENAME." and then <a href='{$link}'>click here</a>.<br />
							<ul><li>".implode("</li><li>",$questions)."</li></ul>"
				);

				e107::getEmail()->sendEmail($email, $name, $eml);

		}




	}



?>