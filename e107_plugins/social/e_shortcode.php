<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if (!defined('e107_INIT')) { exit; }
e107::lan('social',false, true);



class social_shortcodes extends e_shortcode
{

	public $var;



	public function getProviders()
	{

		$emailMessage = LAN_SOCIAL_005;

		$tp = e107::getParser();

			
		$providers = array( 
			'email'				=> array('icon'	=> 'e-social-mail',			'title'=> LAN_SOCIAL_002,	                            'url' => "mailto:EMAIL_RECIPIENT?subject=[t]&body=".rawurlencode($emailMessage)."[u]"),
			'facebook-like'		=> array('icon' => 'e-social-thumbs-up',	'title'=> $tp->lanVars(LAN_SOCIAL_001, "Facebook"),	    'url' => "http://www.facebook.com/plugins/like.php?href=[u]"),
			'facebook-share'	=> array('icon' => 'e-social-facebook',		'title'=> $tp->lanVars(LAN_SOCIAL_000, "Facebook"),	    'url' => "http://www.facebook.com/sharer.php?u=[u]&t=[t]"),
			'twitter'			=> array('icon' => 'e-social-twitter',		'title'=> $tp->lanVars(LAN_SOCIAL_000, "Twitter"),	    'url' => "http://twitter.com/share?url=[u]&text=[t]"),
		//	'google-plus1'		=> array('icon' => 'e-social-gplus',		'title'=> LAN_SOCIAL_003,		                        'url' => "https://apis.google.com/_/+1/fastbutton?usegapi=1&size=large&hl=en&url=[u]"),

			//	'google-plus'		=> array('icon' => 'fa-google-plus',		'title'=>"On Google Plus",		'url' => "https://plusone.google.com/_/+1/confirm?hl=en&url=[u]"),
			'linkedin'			=> array('icon' => 'e-social-linkedin',		'title'=> $tp->lanVars(LAN_SOCIAL_000, "LinkedIn"),	    'url' => "http://www.linkedin.com/shareArticle?mini=true&url=[u]"),
			'pinterest'			=> array('icon'	=> 'e-social-pinterest',	'title'=> $tp->lanVars(LAN_SOCIAL_000, "Pinterest"),	'url' => "http://www.pinterest.com/pin/create/button/?url=[u]&description=[t]&media=[m]"),
			'stumbleupon'		=> array('icon'	=> 'e-social-stumbleupon',	'title'=> $tp->lanVars(LAN_SOCIAL_000, "StumbleUpon"),  'url' => "http://www.stumbleupon.com/submit?url=[u]&title=[t]"),
			'reddit'			=> array('icon'	=> 'e-social-reddit',		'title'=> $tp->lanVars(LAN_SOCIAL_000, "Reddit"),		'url' => "http://reddit.com/submit?url=[u]&title=[t]"),
			'digg'				=> array('icon'	=> 'e-social-digg',			'title'=> $tp->lanVars(LAN_SOCIAL_000, "Digg"),		    'url' => "http://www.digg.com/submit?url=[u]"),

			'tumblr'			=> array('icon'	=> 'e-social-tumblr',		'title'=> $tp->lanVars(LAN_SOCIAL_000, "Tumblr"),		'url' => "http://www.tumblr.com/share?v=3&u=[u]&t=[t]&s="),
			'pocket'            => array('icon' => 'e-social-pocket',       'title'=> $tp->lanVars(LAN_SOCIAL_004, "Pocket"),       'url' => "https://getpocket.com/save?url=[u]&title=[t]"),
			'wordpress'         => array('icon' => 'e-social-wordpress',    'title'=> $tp->lanVars(LAN_SOCIAL_000, "Wordpress"),    'url' => "http://wordpress.com/press-this.php?u=[u]&t=[t]&s=[t]"),
			'pinboard'          => array('icon' => 'e-social-pinboard',     'title'=> $tp->lanVars(LAN_SOCIAL_004, "Pinboard"),     'url' => "https://pinboard.in/popup_login/?url=[u]&title=[t]&description=[t]"),

		//	'whatsapp'          =>array('icon'  => 'e-social-whatsapp',    'mobile'=>true,  'title'=> $tp->lanVars(LAN_SOCIAL_000, "WhatsApp"),	    'url'=> "whatsapp://send?text=[u]", 'data-action' =>"share/whatsapp/share"),
		//	'sms'               => array('icon' => 'e-social-sms',         'mobile'=>true,  'title'=>'sms', 'url'=> "sms://&body=[u]"),
		//	'viber'             => array('icon' => 'e-social-viber',       'mobile'=>true,  'title'=>'viber',   'url'=>"viber://forward?text=[u]")
		);

		return $providers;
	}



	/**
	 * {XURL_ICONS: size=2x}
	 * {XURL_ICONS: type=facebook,twitter,vimeo}
	 */	
	function sc_xurl_icons($parm=null)
	{
		$tp = e107::getParser();
		$tmpl = !empty($parm['template']) ? $parm['template'] : 'default';

		$template = e107::getTemplate('social','social_xurl',$tmpl);

		$social = array(
			'rss'			=> array('href'=> (e107::isInstalled('rss_menu') ? e107::url('rss_menu', 'index', array('rss_url'=>'news')) : ''), 'title'=>'RSS/Atom Feed'),
			'facebook'		=> array('href'=> deftrue('XURL_FACEBOOK'), 	'title'=>'Facebook'),
			'twitter'		=> array('href'=> deftrue('XURL_TWITTER'),		'title'=>'Twitter'),
			'google-plus'	=> array('href'=> deftrue('XURL_GOOGLE'),		'title'=>'Google Plus'),
			'linkedin'		=> array('href'=> deftrue('XURL_LINKEDIN'),		'title'=>'LinkedIn'),
			'github'		=> array('href'=> deftrue('XURL_GITHUB'),		'title'=>'Github'),
			'pinterest'		=> array('href'=> deftrue('XURL_PINTEREST'),	'title'=>'Pinterest'),
			'flickr'		=> array('href'=> deftrue('XURL_FLICKR'),		'title'=>'Flickr'),
			'instagram'		=> array('href'=> deftrue('XURL_INSTAGRAM'),	'title'=>'Instagram'),
			'youtube'		=> array('href'=> deftrue('XURL_YOUTUBE'),		'title'=>'YouTube'),
			'steam'			=> array('href'=> deftrue('XURL_STEAM'),		'title'=>'Steam'),
			'vimeo'			=> array('href'=> deftrue('XURL_VIMEO'),		'title'=>'Vimeo'),
			'twitch'		=> array('href'=> deftrue('XURL_TWITCH'),		'title'=>'Twitch'),
			'vk'			=> array('href'=> deftrue('XURL_VK'),			'title'=>'VK (Vkontakte)')
		);
 			
		// print_a($social);
	
		$class      = (vartrue($parm['size'])) ?  'fa-'.$parm['size'] : '';

		// @deprecated - use template.
		/*
		$tooltipPos = vartrue($parm['tip-pos'], 'top');

		if(isset($parm['tip']))
		{
			$tooltip = ($parm['tip'] == 'false' || empty($parm['tooltip'])) ? '' : 'e-tip';
		}
		else
		{
			$tooltip = 'e-tip';
		}

	*/	if(!empty($parm['type']))
		{
			$newList = array();
			$tmp = explode(",",$parm['type']);
			foreach($tmp as $v)
			{
				$newList[$v] = $social[$v];

			}

			$social = $newList;
		}

		$text = '';

		foreach($social as $id => $data)
		{

			if(!empty($data['href']))
			{
				$data['id'] = $id;
				$data['class'] = $class;

				$this->setVars($data);
			//	 $text .= '<a rel="external" href="'.$data['href'].'" data-tooltip-position="'.$tooltipPos.'" class="'.$tooltip.' social-icon social-'.$id.'" title="'.$data['title'].'"><span class="fa fa-fw fa-'.$id.' '.$class.'"></span></a>';

				$text .= $tp->parseTemplate($template['item'],true, $this);
				$text .= "\n";
			}
		}

		if(!empty($text))
		{
			return $tp->parseTemplate($template['start'],true). $text.$tp->parseTemplate($template['end'],true);
		}

		return null;

	}

	// ----------- Internal Use only by sc_xurl_icons() ------------------

	function sc_xurl_icons_href($parm=null)
	{
		return $this->var['href'];
	}

	function sc_xurl_icons_id($parm=null)
	{
		return $this->var['id'];
	}

	function sc_xurl_icons_title($parm=null)
	{
		return $this->var['title'];
	}

	function sc_xurl_icons_class($parm=null)
	{
		return $this->var['class'];
	}

// ------------------------------------------------

	function sc_social_login($parm=null)
	{
		$pref = e107::getUserProvider()->isSocialLoginEnabled();



		if(empty($pref))
		{
			return null;
		}
		
		$sc = e107::getScBatch('signup');

		$text = '';

		if(!empty($parm['label']))
		{
			$text .= "<p>".LAN_PLUGIN_SOCIAL_SIGNIN."</p>";
		}

		$text .= $sc->sc_signup_xup_login($parm);
		$text .= "
		<div class='clearfix'></div><hr class='clearfix' />";
		
		return $text; 	
	}


	private function getHashtags($extraTags='')
	{
		$hashtags = e107::pref('social','sharing_hashtags','');

		$hashtags = str_replace(array(" ",'#'),"", $hashtags);

		$ret = explode(',',$hashtags);

		if(!empty($extraTags))
		{
			$extraTags = str_replace(array(" ",'#'),"", $extraTags);
			$tmp = explode(',',$extraTags);
			foreach($tmp as $v)
			{
				$ret[] = $v;
			}
		}

		if(!empty($ret))
		{
			return implode(',',$ret);
		}

	}


	/**
	 * @param string $type provider key. eg. facebook, twitter etc.
	 * @param string $urlScheme The URL scheme. @see getProviders 'url'
	 * @param array  $data
	 * @param string $data['title'] Title for the URL
	 * @param string $data['description'] Description for the URL
	 * @param string $data['media']
	 * @param array $options Currently 'twitterAccount' and 'hashtags' are supported.
	 * @return string
	 */
	public function getShareUrl($type, $urlScheme, $data=array(), $options=array())
	{
		$data = array('u'=> rawurlencode($data['url']), 't'=> rawurlencode($data['title']), 'd'	=> rawurlencode($data['description']), 'm' => rawurlencode($data['media']));

		return $this->parseShareUrlScheme($type, $urlScheme, $data, $options);
	}


	/**
	 * @param string $type
	 * @param string $providerUrlScheme
	 * @param array $data Array containing keys: 'u' (URL), 't' (Title), 'd' (Description)', 'm' (Media)
	 * @param array $options (optional) 'hashtags' and 'twitterAccount'
	 * @return string
	 */
	private function parseShareUrlScheme($type, $providerUrlScheme, $data=array(), $options=array())
	{
		$pUrl = str_replace("&","&amp;",$providerUrlScheme);

		$shareUrl = e107::getParser()->lanVars($pUrl,$data);

		if($type === 'twitter')
		{
			if(!empty($options['hashtags']))
			{
				$shareUrl .= "&amp;hashtags=".rawurlencode($options['hashtags']);
			}

			if(!empty($options['twitterAccount']))
			{
				$shareUrl .= "&amp;via=".$options['twitterAccount'];
			}

		}

		return $shareUrl;

	}

	/**
	 * {SOCIALSHARE: url=x&title=y}
	 * @example {SOCIALSHARE: type=basic} - Show only Email, Facebook, Twitter and Google. 
	 * @example {SOCIALSHARE: dropdown=1&type=basic} - Show only Email, Facebook, Twitter and Google in a drop-down button
	 * @example {SOCIALSHARE: providers=twitter,pinterest&tip=false} - override provider preferences and disable tooltips.
	 * @example for plugin developers:  send 'var' values for use by the social shortcode. (useful for loops where the value must change regularly) 
	 * 	$socialArray = array('url'=>'your-url-here', 'title'=>'your-title-here');
		e107::getScBatch('social')->setVars($socialArray);
	 */
	function sc_socialshare($parm=array()) // Designed so that no additional JS required.
	{
		$pref = e107::pref('social');

		if(varset($pref['sharing_mode']) == 'off')
		{
			return '';
		}

	//	$hashtags       = vartrue($pref['sharing_hashtags']);

		$defaultUrl 	= vartrue($this->var['url'], e_REQUEST_URL);
		$defaultTitle	= vartrue($this->var['title'], deftrue('e_PAGETITLE'). " | ". SITENAME);
	//	$defaultDiz		= vartrue($this->var['description'], e107::getUrl()->response()->getMetaDescription());
		$defaultDiz		= vartrue($this->var['description'], e107::getSingleton('eResponse')->getMetaDescription());
		$defaultTags    = vartrue($this->var['tags'],'');
		
		$tp 			= e107::getParser();

		$providers = $this->getProviders();

		if(empty($parm['providers'])) // No parms so use prefs instead.
		{
			$defaultProviders = array('email' ,'facebook-like', 'facebook-share', 'twitter',  'google-plus1',  'pinterest' ,  'stumbleupon', 'reddit', 'digg' );
			$parm['providers']  = !empty($pref['sharing_providers']) ? array_keys($pref['sharing_providers']) : $defaultProviders;
		}
		else
		{
			$parm['providers']  = explode(",",$parm['providers']);
		}

		if(empty($parm['dropdown']))
		{
			$parm['dropdown'] = ($pref['sharing_mode'] == 'dropdown') ? 1 : 0;
		}


		$url 			= varset($parm['url'], 		$defaultUrl);
		$title 			= varset($parm['title'], 	$defaultTitle) ;
		$description 	= varset($parm['title'], 	$defaultDiz);
		$tags           = varset($parm['tags'],     $defaultTags);
		$media 			= "";
		$label 			= varset($parm['label'], 	$tp->toGlyph('e-social-spread'));
		
		$size			= varset($parm['size'],		'md');


		$data = array('u'=> rawurlencode($url), 't'=> rawurlencode($title), 'd'	=> rawurlencode($description), 'm' => rawurlencode($media));
		
		if(!vartrue($parm['dropdown']))
		{
			$butSize 	= ($size == 'lg' || $size == 'sm' || $size == 'xs') ? 'btn-'.$size : '';
		}
		else 
		{
			$butSize = 'btn-social';
		}


		$opt = array();

	//	$hashtags = '';

	//	$hashtags .= str_replace(array(" ",'#'),"", $hashtags); // "#mytweet";

		$hashtags = $this->getHashtags($tags);

		if(isset($parm['tip']))
		{
			$tooltip = ($parm['tip'] == 'false' || empty($parm['tooltip'])) ? '' : 'e-tip';
		}
		else
		{
			$tooltip = 'e-tip';
		}



		$twitterAccount = basename(XURL_TWITTER);

		$btnClass = varset($parm['btnClass'], 'btn btn-default btn-secondary social-share');

	//	return print_a($hashtags,true);
		foreach($providers as $k=>$val)
		{

			if(!in_array($k,$parm['providers']))
			{
				continue;
			}

			$shareUrl = $this->parseShareUrlScheme($k, $val['url'], $data, array('twitterAccount'=>$twitterAccount, 'hashtags'=>$hashtags));

			if(!empty($val['mobile']))
			{
				$btnClass .= ' social-share-mobile';
			}
			
			$opt[$k] = "<a class='".$btnClass." ".$tooltip." ".$butSize." social-share-".$k."'  target='_blank' title='".$val["title"]."' href='".$shareUrl."'>".$tp->toIcon($val["icon"], array('fw'=>1))."</a>";
		}
		
		// Show only Email, Facebook, Twitter and Google. 
		if(varset($parm['type']) == 'basic')
		{
			$remove = array('linkedi','pinterest', 'stumbleupon', 'digg', 'reddit', 'linkedin', 'tumblr','pocket','wordpress','pinboard');
			foreach($remove as $v)
			{
				unset($opt[$v]);	
			}	
		}
		elseif(!empty($parm['type']))
		{
			$newlist = array();
			$tmp = explode(",",$parm['type']);
			foreach($tmp as $v)
			{
				$newlist[$v] = $opt[$v];
			}

			$opt = $newlist;

		//	print_a($opt);
		}
		
		if(vartrue($parm['dropdown']))
		{
			$dir = ($parm['dropdown'] == 'right') ? 'pull-right float-right' : '';
			$class = varset($parm['class'],'btn-group');


			$text = '<div class="social-share btn-group hidden-print '.$dir.'">
				  <a class="'.$tooltip.' btn btn-dropdown btn-default btn-secondary btn-'.$size.' dropdown-toggle" data-toggle="dropdown" href="#" title="'.LAN_SOCIAL_204.'">'.$label.'</a>
				 
				  <ul class="dropdown-menu" role="menu" >
				  
				    <li><div class="'.$class.'">'.implode("\n",$opt).'</div></li>
				  </ul>
				</div>';
		
			return $text;
		}
		else
		{
			
			$class = varset($parm['class'],'text-center btn-group social-share');

			return '<div class="'.$class.'  hidden-print">'.implode("\n",$opt)."</div>";
		
		}	
		
	}

	/**
	 * @example {TWITTER_TIMELINE: id=xxxxxxx&theme=light}
	 */
	function sc_twitter_timeline($parm)
	{
		$ns = e107::getRender();
		
		$account = basename(XURL_TWITTER);
		//data-related="twitterapi,twitter"
		$text = '<a class="twitter-timeline" href="'.XURL_TWITTER.'" data-widget-id="'.varset($parm['id']).'" data-theme="'.varset($parm['theme'],'light').'" data-link-color="#cc0000"   data-aria-polite="assertive" width="100%" height="'.varset($parm['height'],300).'" lang="'.e_LAN.'">'.LAN_SOCIAL_201.'@'.$account.'</a>';

		$text .= <<<TMPL
		
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
TMPL;
		return (vartrue($parm['render'])) ? $ns->tablerender('',$text,'twitter-timeline',true) : $text;
	}


}

?>