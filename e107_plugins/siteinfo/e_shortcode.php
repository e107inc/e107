<?php
/*
* Copyright (C) 2008-2013 e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
*
* Siteinfo shortcode batch
*/
if (!defined('e107_INIT')) { exit; }

class siteinfo_shortcodes // must match the folder name of the plugin. 
{
	function sc_sitebutton($parm=null)
	{
		
		if(!empty($_POST['sitebutton']) && !empty($_POST['ajax_used']))
		{
			$path = e107::getParser()->replaceConstants($_POST['sitebutton']);
		}

		if(varset($parm['type']) == 'email' || $parm == 'email' || varset($parm['type']) == 'url') // (retain {}  constants )
		{
			$h = !empty($parm['h']) ? $parm['h'] : 100;

			$path = e107::getConfig()->get('sitebutton');

			if(empty($path))
			{
				return false;
			}

			$realPath = e107::getParser()->replaceConstants($path);

			if(defined('e_MEDIA') && is_writable(e_MEDIA."temp/") && ($resized = e107::getMedia()->resizeImage($path, e_MEDIA."temp/".basename($realPath),'h='.$h)))
			{
				$path = e107::getParser()->createConstants($resized,'mix');
			}
		}
		else
		{
			$path = (strpos(SITEBUTTON, 'http:') !== false || strpos(SITEBUTTON, e_IMAGE_ABS) !== false ? SITEBUTTON : e_IMAGE.SITEBUTTON);
		}

		if(varset($parm['type']) == 'url')
		{
		//	return $path;
			return e107::getParser()->replaceConstants($path,'full');
		}

		if(!empty($path))
		{
			return '<a href="'.SITEURL.'" class="sitebutton"><img src="'.$path.'" alt="'.SITENAME.'" /></a>';
		}
	}

	/**
	 * YYYY is automatically replaced with the current year.
	 * @param array $parm 'class' to set the class of links.
	 * @return string
	 */
	function sc_sitedisclaimer($parm=array())
	{
		$class = !empty($parm['class']) ? " class='".$parm['class']."'" : '';

		$default = "Proudly powered by <a".$class." href='https://e107.org'>e107 Bootstrap CMS</a> which is <a".$class." href='".e_HTTP."credits.php'>released</a> under the terms of the GNU GPL License.";

		$text = deftrue('SITEDISCLAIMER',$default);

		$text = str_replace("YYYY", date('Y'), $text);

		return e107::getParser()->toHTML($text, true, 'SUMMARY');
	}

	
	function sc_siteurl($parm='')
	{
		if(strlen(deftrue('SITEURL')) < 3 ) //fixes CLI/cron
		{
			return e107::getPref('siteurl');
		}

		return SITEURL;	
	}
	

	function sc_sitename($parm='')
	{
		return ($parm == 'link') ? "<a href='".SITEURL."' title='".SITENAME."'>".SITENAME."</a>" : SITENAME;
	}

	function sc_sitedescription()
	{
		global $pref;
		return SITEDESCRIPTION.(defined('THEME_DESCRIPTION') && $pref['displaythemeinfo'] ? THEME_DESCRIPTION : '');
	}

	function sc_sitetag()
	{
		return SITETAG;
	}
	
	function sc_sitelogo($parm=null)
	{
		return $this->sc_logo($parm);	
	}

	function sc_logo($parm = array())
	{
		if(is_string($parm))
		{
			parse_str(vartrue($parm),$parm);		// Optional {LOGO=file=file_name} or {LOGO=link=url} or {LOGO=file=file_name&link=url}
		}
		// Paths to image file, link are relative to site base
		$tp = e107::getParser();

		$logopref = e107::getConfig()->get('sitelogo');
		$logop = $tp->replaceConstants($logopref);

		if(isset($parm['login'])) // Login Page. BC fix.
		{

			if(!empty($logopref) && is_readable($logop))
			{

				$logo = $tp->replaceConstants($logopref,'abs');
				$path = $tp->replaceConstants($logopref);
			}
			elseif(is_readable(THEME."images/login_logo.png"))
			{

				$logo = THEME_ABS."images/login_logo.png";	
				$path = THEME."images/login_logo.png";	
			}
			else
			{


				$logo = "{e_IMAGE}logoHD.png";
				$path = e_IMAGE."logoHD.png";
				if(empty($parm['w']))
				{
					$parm['w'] = 330;
				}
			}	
		}
		else 
		{
			
			if(!empty($logopref) && is_readable($logop))
			{
				$logo = $logopref; 
				$path = $tp->replaceConstants($logopref);
			}
			elseif (isset($file) && $file && is_readable($file))
			{
				$logo = e_HTTP.$file;						// HTML path
				$path = e_BASE.$file;						// PHP path
			}
			else if (is_readable(THEME.'images/e_logo.png'))
			{
				$logo = THEME_ABS.'images/e_logo.png';		// HTML path
				$path = THEME.'images/e_logo.png';			// PHP path
			}
			elseif(varset($parm['fallback']) == 'sitename') // fallback to 
			{
				return $this->sc_sitename($parm); 	
			}
			else
			{
				$logo = '{e_IMAGE}logoHD.png';				// HTML path
				$path = e_IMAGE.'logoHD.png';					// PHP path
			}
			
		}

		$dimensions = array();
		
		if((isset($parm['w']) || isset($parm['h'])))
		{

			$dimensions[0] = $parm['w'] ?? 0;
			$dimensions[1] = !empty($parm['h']) ? $parm['h'] : 0;

			if(empty($parm['noresize']) && !empty($logopref)) // resize by default - avoiding large files.
			{
				 $logo = $logopref;
			}
		}
		elseif(!deftrue('BOOTSTRAP'))
		{
			$dimensions = getimagesize($path);
		}

		$opts = array('alt'=>SITENAME, 'class'=>'logo img-responsive img-fluid');

		if(!empty($dimensions[0]))
		{
			$opts['w'] = $dimensions[0];

		}

		if(!empty($dimensions[1]))
		{
			$opts['h'] = $dimensions[1];
		}

		if(varset($parm['type']) == 'url')
		{
			return $tp->replaceConstants($logo, 'full');
		}
		elseif(varset($parm['type']) == 'email')
		{
			return $logo;
		}

		$image = $tp->toImage($logo,$opts);

		if (isset($link) && $link)
		{
			if ($link == 'index')
			{
				$image = "<a href='".e_HTTP."index.php'>".$image."</a>";
			}
			else
			{
				$image = "<a href='".e_HTTP.$link."'>".$image."</a>";
			}
		}

		return $image;
	}

	function sc_theme_disclaimer($parm=null)
	{
		$pref = e107::getPref();
		return (defined('THEME_DISCLAIMER') && $pref['displaythemeinfo'] ? THEME_DISCLAIMER : '');
	}

	function sc_organization()
	{
		$c = e107::getPref('contact_info');

		$text = !empty($c['organization']) ? $c['organization'] : SITENAME;

		return e107::getParser()->toText($text);
	}

}
