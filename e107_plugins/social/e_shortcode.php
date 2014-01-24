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
		
		
	/**
	 * {XURL_ICONS: size=2x}
	 */	
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
 			
		
	
		$class = (vartrue($parm['size'])) ?  'fa-'.$parm['size'] : '';

		$text = '';

		foreach($social as $id => $data)
		{

			if($data['href'] != '')
			{

				 $text .= '<a rel="external" href="'.$data['href'].'" class="e-tip social-icon social-'.$id.'">
				 	<span class="fa fa-'.$id.' '.$class.'"></span>
				 </a>';
				 
				 $text .= "\n";	
			}
		}

		if($text !='')
		{
			return 	'<p class="xurl-social-icons">'.$text.'</p>';
		}

	}	







	/**
	 * {SOCIALSHARE: url=x&title=y}
	 */
	function sc_socialshare($parm='') // Designed so that no additional JS required. 
	{
					
		$tp 			= e107::getParser();
		$url 			= varset($parm['url'], e_REQUEST_URL);
		$title 			= varset($parm['title'], deftrue('e_PAGETITLE'). " | ". SITENAME ) ;
		$description 	= varset($parm['title'], e107::getUrl()->response()->getMetaDescription());
		$media 			= "";
		$label 			= varset($parm['label'], $tp->toGlyph('icon-share')." ".defset('LAN_SHARE',"Share"));
		
		$size			= varset($parm['size'],'md');
		
		//TODO LANS ie. "Share on [x]" 
		
		$providers = array(
			'email'				=> array('icon'	=>	'fa-envelope-o',	'title'=>"Email to someone",	'url' => "mailto:EMAIL_RECIPIENT?subject=[t]&body=[u]"),
			'facebook-like'		=> array('icon' => 'fa-thumbs-o-up',	'title'=>"Like on Facebook",	'url' => "http://www.facebook.com/plugins/like.php?href=[u]"),
			'facebook-share'	=> array('icon' => 'fa-facebook',		'title'=>"Share on Facebook",	'url' => "http://www.facebook.com/sharer.php?u=[u]&t=[t]"),
			'twitter'			=> array('icon' => 'fa-twitter',		'title'=>"Share on Twitter",	'url' => "http://twitter.com/share?url=[u]&text=[t]"),
			'google-plus1'		=> array('icon' => 'fa-google-plus',	'title'=>"+1 on Google",		'url' => "https://apis.google.com/_/+1/fastbutton?usegapi=1&size=large&hl=en&url=[u]"),
		
		//	'google-plus'		=> array('icon' => 'fa-google-plus',	'title'=>"On Google Plus",		'url' => "https://plusone.google.com/_/+1/confirm?hl=en&url=[u]"),
			'linkedin'			=> array('icon' => 'fa-linkedin',		'title'=>"Share on LinkedIn",	'url' => "http://www.linkedin.com/shareArticle?mini=true&url=[u]"),
			'pinterest'			=> array('icon'	=> 'fa-pinterest',		'title'=>"Share on Pinterest",	'url' => "http://www.pinterest.com/pin/create/button/?url=[u]&description=[t]&media=[m]"),
		//	'thumblr'			=> array('icon'	=>	'fa-tumblr',		'title'=>"On Tumblr",			'url' => "http://www.tumblr.com/share/link?url=[u]&name=[t]&description=[d]"),
		//	'stumbleupon'		=> array('icon'	=>	'fa-stumbleupon',	'title'=>"On Tumblr",			'url' => "http://www.stumbleupon.com/submit?url=[u]&title=[t]"), // no fa icon available. 
			
			//http://reddit.com/submit?url=http%3A%2F%2Fwebsite.com&title=Website%20Title  // no fa icon available
			//http://www.digg.com/submit?url=http%3A%2F%2Fwebsite.com	  // no fa icon available		
		);
	
	
	
		$data = array('u'=> rawurlencode($url), 't'=> rawurlencode($title), 'd'	=> rawurlencode($description), 'm' => rawurlencode($media));
		
		if(!vartrue($parm['dropdown']))
		{
			$butSize 	= ($size == 'lg' || $size == 'sm' || $size == 'xs') ? 'btn-'.$size : '';
		}
		else 
		{
			$butSize = 'btn-lg';
		}
		

		$opt = array();
		
		foreach($providers as $val)
		{
			$pUrl = str_replace("&","&amp;",$val['url']);
			
			$shareUrl = $tp->lanVars($pUrl,$data);
			
			$opt[] = "<a class='e-tip btn ".$butSize." btn-default social-share'  target='_blank' title='".$val["title"]."' href='".$shareUrl."'>".$tp->toIcon($val["icon"])."</a>";	
		}
		
		
		if(vartrue($parm['dropdown']))
		{
			$dir = ($parm['dropdown'] == 'right') ? 'pull-right' : '';
	
			$text = '<div class="btn-group '.$dir.'">
				  <a class="e-tip btn btn-dropdown btn-default btn-'.$size.' dropdown-toggle" data-toggle="dropdown" href="#" title="Share">'.$label.' <b class="caret"></b></a>
				 
				  <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel"  style="min-width:355px">
				  
				    <li><div class="btn-group" style="padding-left: 7px;">'.implode("\n",$opt).'</div></li>
				  </ul>
				</div>';
		
			return $text;
		}
		else
		{
			
			return '<div class="btn-group text-center"><button class="btn btn-sm btn-default disabled">'.$label.'</button>'.implode("\n",$opt)."</div>";
		
		}	
		
	}





}

?>