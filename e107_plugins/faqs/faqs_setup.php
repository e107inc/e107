<?php
/*
* e107 website system
*
* Copyright (c) 2008-2016 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Custom FAQ install/uninstall/update routines
*
*/

class faqs_setup
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
		$sql = e107::getDb();
		$mes = e107::getMessage();
		
		$faqRows = array(
			array(
				'faq_id'        => 1,
				'faq_parent'    => 1,
				'faq_question'  => 'What is FAQs?',
				'faq_answer'    => 'FAQs is a plugin that you can use on your e107 0.8+ website to manage Frequently Asked Questions',
				'faq_comment'   => 0,
				'faq_datestamp' => 1230918231,
				'faq_author'    => 1,
				'faq_order'     => 0,
			),
			array(
				'faq_id'        => 2,
				'faq_parent'    => 1,
				'faq_question'  => 'How can I use e107?',
				'faq_answer'    => "You can use e107 if you have a running server with PHP and MySQL installed. Read more about installation requirements.\r\n\r\ne107 is a Content Management System (CMS). You can use it to make consistent web pages. The advantage is you don't have to write HTML or create CSS files. The programs of e107 take care of all the presentation through the theme. All your entered data is saved into a MySQL database.\r\n\r\ne107 has active plugin and theme resources which grow every day. The software is completely and totally free and always will be, you don't even need to register anywhere to download it. There are hundreds of content management systems to choose from, if you're not sure e107 suits your needs, head over to OpenSourceCMS and try a few out.\r\n\r\nWith e107 you are totally in control with a powerful but easy to understand Admin Area and you can add functionalities to your website by adding plugins. e107 has an easy step-by-step installation procedure to install it on your server. ",
				'faq_comment'   => 0,
				'faq_datestamp' => 0,
				'faq_author'    => 1,
				'faq_order'     => 1,
			),
			array(
				'faq_id'        => 3,
				'faq_parent'    => 1,
				'faq_question'  => 'What is a plugin?',
				'faq_answer'    => "A plugin is an additional program that integrates with the e107 core system.\r\n\r\nActually plugins are enhancements to the existing system. Some other CMS systems call it extensions, components or modules.\r\n\r\nAlready some core plugins are included in the full install package of e107.\r\n\r\nYou can activate them using Admin > Plugin Manager, and click on Install for the ones you want. They will appear in your Admin Area for configuration.\r\n\r\nThere are all kinds of plugins: small and large, core plugins and third party plugins. There are plugins for all kinds of purposes. ",
				'faq_comment'   => 0,
				'faq_datestamp' => 123123123,
				'faq_author'    => 1,
				'faq_order'     => 2,
			),
		);

		$ok = $sql->createQueryBuilder()->insert('faqs')->values($faqRows)->execute();
		$status = ($ok !== false) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes->add(LAN_DEFAULT_TABLE_DATA.": faqs", $status);


		$faqInfoRows = array(
			array(
				'faq_info_id'     => 1,
				'faq_info_title'  => 'General',
				'faq_info_about'  => 'General Faqs',
				'faq_info_parent' => 0,
				'faq_info_class'  => 0,
				'faq_info_order'  => 0,
				'faq_info_icon'   => '',
				'faq_info_metad'  => '',
				'faq_info_metak'  => '',
			),
			array(
				'faq_info_id'     => 2,
				'faq_info_title'  => 'Misc',
				'faq_info_about'  => 'Other FAQs',
				'faq_info_parent' => 0,
				'faq_info_class'  => 0,
				'faq_info_order'  => 1,
				'faq_info_icon'   => '',
				'faq_info_metad'  => '',
				'faq_info_metak'  => '',
			),
		);

		$ok = $sql->createQueryBuilder()->insert('faqs_info')->values($faqInfoRows)->execute();
		$status = ($ok !== false) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
		$mes->add(LAN_DEFAULT_TABLE_DATA.": faqs_info", $status);

	}
/*	
	function uninstall_options()
	{
	
	}


	function uninstall_post($var)
	{
		// print_a($var);
	}

	function upgrade_post($var)
	{
		// $sql = e107::getDb();
	}
*/	
}
