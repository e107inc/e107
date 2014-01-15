<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if (!defined('e107_INIT')) { exit; }

class social_shortcodes extends e_shortcode
{
	function sc_xurl_icons($parm='')
	{
							
		$social = array(

			'rss'			=> array('href'=> (e107::isInstalled('rss_menu') ? e_PLUGIN_ABS."rss_menu/rss.php?news.2" : ''), 'title'=>'Feed'),
			'facebook'		=> array('href'=> deftrue('XURL_FACEBOOK'), 	'title'=>'Facebook'),
			'twitter'		=> array('href'=> deftrue('XURL_TWITTER'),		'title'=>'Twitter'),
			'googleplus'	=> array('href'=> deftrue('XURL_GOOGLE'),		'title'=>'Google Plus'),
			'linkedin'		=> array('href'=> deftrue('XURL_LINKEDIN'),		'title'=>'LinkedIn'),
			'pinterest'		=> array('href'=> deftrue('XURL_PINTEREST'),	'title'=>'Pinterest'),
			'instagram'		=> array('href'=> deftrue('XURL_INSTAGRAM'),	'title'=>'Instagram'),
			'youtube'		=> array('href'=> deftrue('XURL_YOUTUBE'),		'title'=>'YouTube'),
			'vimeo'			=> array('href'=> deftrue('XURL_VIMEO'),		'title'=>'Vimeo')
		);
 
		parse_str($parms,$parm);
		
		$class = (vartrue($parms['size'])) ?  'fa-'.$parm['size'] : '';


		$text = '';

		foreach($social as $id => $data)
		{

			if($data['href'] != '')
			{

				 $text .= '<a rel="external" href="'.$data['href'].'" class="social-icon social-'.$id.' '.$class.'">
				 	<span class="fa fa-'.$id.'"></span>
				 </a>';
				 
				 $text .= "\n";	
			}
		}

		if($text !='')
		{
			return 	'<p class="xurl-social-icons">'.$text.'</p>';
		}

	}	


}

?>
