<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/emailprint_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/lan_print.php");
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/lan_email.php");


/**
 *
 */
class emailprint
{

	/**
	 * @param $mode
	 * @param $id
	 * @param $look
	 * @param $parm
	 * @return string
	 */
	static function render_emailprint($mode, $id, $look = 0, $parm=array())
	{
		//currently only used in news_shortcodes -> sc_emailicon & sc_printicon
		// failsafe for output single button data, if you request all buttons parm can't be used....
		$parm = ($look == 0)?null:$parm;

		// $look = 0  --->display all icons
		// $look = 1  --->display email icon only
		// $look = 2  --->display print icon only

		// $parm['url'] --->output url only
		// no $parm or $parm['class']--> output full button render
			// $parm['class'] --->used to inject class in output, redundant if using templates
		$tp = e107::getParser();

		$text_emailprint = "";

		//new method emailprint_class : (only news is core, rest is plugin: searched for e_emailprint.php which should hold $email and $print values)
		if($mode == "news")
		{
			$email = "news";
			$print = "news";
		}
		else
		{
			//load the others from plugins
			$handle = opendir(e_PLUGIN);
			while (false !== ($file = readdir($handle))) 
			{
				if ($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)) 
				{
					$plugin_handle = opendir(e_PLUGIN.$file."/");
					while (false !== ($file2 = readdir($plugin_handle))) 
					{
						if ($file2 == "e_emailprint.php") 
						{
							require_once(e_PLUGIN.$file."/".$file2);
						}
					}
				}
			}
		}
		
		$TEMPLATE = e107::getCoreTemplate('emailprint');
		$sc = e107::getScBatch($mode);

		$genericMail = $tp->parseTemplate($TEMPLATE['ICON_EMAIL'], true, $sc);
		$genericPrint = $tp->parseTemplate($TEMPLATE['ICON_PRINT'], true, $sc);	
// Default icons if there's no templated icons		
		if(deftrue('BOOTSTRAP'))
		{
//			$genericMail = $tp->toGlyph('fa-envelope',false); // "<i class='icon-envelope'></i>";
//			$genericPrint = $tp->toGlyph('fa-print',false); // "<i class='icon-print'></i>"; 
			$genericMail = $genericMail?:$tp->toGlyph('fa-envelope',false); // "<i class='icon-envelope'></i>";
			$genericPrint = $genericPrint?:$tp->toGlyph('fa-print',false); // "<i class='icon-print'></i>"; 
			// Probably redundant if using templates. Left here for legacy purposes?
			$class = !empty($parm['class']) ? $parm['class'] : "btn btn-default btn-secondary";
		}
		else // BC
		{
//			$genericMail = "<img src='".e_IMAGE_ABS."generic/email.png'  alt='".LAN_EMAIL_7."'  />";
//			$genericPrint = "<img src='".e_IMAGE_ABS."generic/printer.png'  alt='".LAN_PRINT_1."'  />";	
			$genericMail = $genericMail?:"<img src='".e_IMAGE_ABS."generic/email.png'  alt='".LAN_EMAIL_7."'  />";
			$genericPrint = $genericPrint?:"<img src='".e_IMAGE_ABS."generic/printer.png'  alt='".LAN_PRINT_1."'  />";	
			$class = "";
		}
		
		if ($look == 0 || $look == 1) 
		{
			// Probably redundant if using templates. Left here for legacy purposes?
			$ico_mail = (defined("ICONMAIL") && file_exists(THEME."images/".ICONMAIL) ? "<img src='".THEME_ABS."images/".ICONMAIL."'  alt='".LAN_EMAIL_7."'  />" : $genericMail);
			$url_mail = e_HTTP."email.php?".$email.".".$id;
			if (!$parm || !empty($parm['class'])){
				// Default output without templates. Left here for legacy purposes?
				//TODO CSS class
				//				$text_emailprint .= "<a class='e-tip hidden-print ".$class."' href='".$url_mail."' title='".LAN_EMAIL_7."'>".$ico_mail."</a> ";

				$text_emailprint .= $tp->parseTemplate($TEMPLATE['email'], true, $sc)?:"<a class='e-tip hidden-print ".$class."' href='".$url_mail."' title='".LAN_EMAIL_7."'>".$ico_mail."</a> ";

			} else if (!empty($parm['url'])) {
				$text_emailprint .= $url_mail;
			}
		}
		if ($look == 0 || $look == 2) 
		{
			// Probably redundant if using templates. Left here for legacy purposes?
			$ico_print = (defined("ICONPRINT") && file_exists(THEME."images/".ICONPRINT) ? "<img src='".THEME_ABS."images/".ICONPRINT."' alt='".LAN_PRINT_1."'  />" : $genericPrint);
			$url_print = e_HTTP."print.php?".$print.".".$id;
			if (!$parm || !empty($parm['class'])){
				// Default output without templates. Left here for legacy purposes?
				//TODO CSS class
				//	$text_emailprint .= "<a rel='alternate' class='e-tip ".$class." hidden-print' href='".e_HTTP."print.php?".$print.".".$id."' title='".LAN_PRINT_1."'>".$ico_print."</a>";

				$text_emailprint .= $tp->parseTemplate($TEMPLATE['print'], true, $sc)?:"<a rel='alternate' class='e-tip ".$class." hidden-print' href='".$url_print."' title='".LAN_PRINT_1."'>".$ico_print."</a>";

			} else if (!empty($parm['url'])) {
				$text_emailprint .= $url_print;
			}
		}
		return $text_emailprint;
	}
}

