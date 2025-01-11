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
			'function' => "addFallbackMeta",
		);

		return $event;
		
	}



	/**
	 * Callback function to add og:image if there is no any
	 */
	function addFallbackMeta($meta)
	{

		if(e_ADMIN_AREA === true)
		{
			return null;
		}

		/** @note TITLE */
		if($title = e107::getSingleton('eResponse')->getMetaTitle())
		{
			e107::meta('og:title', $title); // will only populate if not already defined.
			e107::meta('twitter:title', $title);
		}
		elseif(deftrue('e_FRONTPAGE'))
		{
			e107::meta('og:title', SITENAME);
			e107::meta('twitter:title', SITENAME);
		}

		/** @note TYPE */
		if(empty($meta['og:type']))
		{
			e107::meta('og:type', 'website');
		}

		/** @note DESCRIPTION */
		if(empty($meta['og:description']))
		{
			$description = e107::getSingleton('eResponse')->getMetaDescription();

			if(empty($description))
			{
				if(deftrue('META_DESCRIPTION'))
				{
					$description = META_DESCRIPTION;
				}
				else
				{
					$tmp = e107::pref('core', 'meta_description');
					if(!empty($tmp[e_LANGUAGE]))
					{
						$description = $tmp[e_LANGUAGE];
					}
				}
			}

			if(!empty($description))
			{
				e107::meta('og:description', $description);
				e107::meta('twitter:description', $description);
			}
		}

		/** @note IMAGE */
		if(!empty($meta['og:image']))
		{
			// e107::getDebug()->log("Skipping Social plugin og:image fallback");
			return null;
		}

		$pref = e107::getConfig()->getPref();

		if($ogImage = e107::pref('social', 'og_image', false))
		{
			$metaImg = e107::getParser()->thumbUrl($ogImage, 'w=800', false, true);
			e107::meta('og:image', $metaImg);
			e107::meta('twitter:image', $metaImg);
		}
		elseif(!empty($pref['sitebutton']))
		{
			$siteButton = (strpos($pref['sitebutton'],'{e_MEDIA') !== false) ? e107::getParser()->thumbUrl($pref['sitebutton'],'w=800',false, true) : e107::getParser()->replaceConstants($pref['sitebutton'],'full');
			e107::meta('og:image',$siteButton);
			e107::meta('twitter:image', $siteButton);
		}
		elseif(!empty($pref['sitelogo'])) // fallback to sitelogo
		{
			$siteLogo = (strpos($pref['sitelogo'],'{e_MEDIA') !== false) ? e107::getParser()->thumbUrl($pref['sitelogo'],'w=800',false, true) : e107::getParser()->replaceConstants($pref['sitelogo'],'full');
			e107::meta('og:image',$siteLogo);
			e107::meta('twitter:image', $siteLogo);
		}

	}

} //end class



