<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Helper functions for "gallery" plugin.
 */

/**
 * Helper function to load prettyPhoto library's settings and files.
 */
function gallery_load_prettyphoto() // @lonalore FIXME Stop loading this on every page.
{

	// Re-use the statically cached value to save memory. Load settings and files only once!!!
	static $gallery_load_prettyphoto;

	if(!isset($gallery_load_prettyphoto['loaded']) || !$gallery_load_prettyphoto['loaded'])
	{
		$tp = e107::getParser();
		$plugPref = e107::getPlugConfig('gallery')->getPref();
		$template = e107::getTemplate('gallery');

		// Load prettyPhoto library.
		e107::library('load', 'jquery.prettyPhoto');

		$settings = array(
			'prettyphoto' => array(
				'hook'                    => $tp->toText(varset($plugPref['pp_hook'], 'data-gal')),
				'animation_speed'         => $tp->toText(varset($plugPref['pp_animation_speed'], 'fast')),
				'slideshow'               => (int) varset($plugPref['pp_slideshow'], 5000),
				'autoplay_slideshow'      => (bool) varset($plugPref['pp_autoplay_slideshow'], false),
				'opacity'                 => (float) varset($plugPref['pp_opacity'], 0.80),
				'show_title'              => (bool) varset($plugPref['pp_show_title'], true),
				'allow_resize'            => (bool) varset($plugPref['pp_allow_resize'], true),
				'default_width'           => (int) varset($plugPref['pp_default_width'], 500),
				'default_height'          => (int) varset($plugPref['pp_default_height'], 344),
				'counter_separator_label' => $tp->toText(varset($plugPref['pp_counter_separator_label'], '/')),
				'theme'                   => $tp->toText(varset($plugPref['pp_theme'], 'pp_default')),
				'horizontal_padding'      => (int) varset($plugPref['pp_horizontal_padding'], 20),
				'hideflash'               => (bool) varset($plugPref['pp_hideflash'], false),
				'wmode'                   => $tp->toText(varset($plugPref['pp_wmode'], 'opaque')),
				'autoplay'                => (bool) varset($plugPref['pp_autoplay'], true),
				'modal'                   => (bool) varset($plugPref['pp_modal'], false),
				'deeplinking'             => (bool) varset($plugPref['pp_deeplinking'], false),
				'overlay_gallery'         => (bool) varset($plugPref['pp_overlay_gallery'], true),
				'keyboard_shortcuts'      => (bool) varset($plugPref['pp_keyboard_shortcuts'], true),
				'ie6_fallback'            => (bool) varset($plugPref['pp_ie6_fallback'], true),
				'markup'                  => $template['prettyphoto']['content'],
				'gallery_markup'          => $template['prettyphoto']['gallery_item'],
				'image_markup'            => $template['prettyphoto']['image_item'],
				'flash_markup'            => $template['prettyphoto']['flash_item'],
				'quicktime_markup'        => $template['prettyphoto']['quicktime_item'],
				'iframe_markup'           => $template['prettyphoto']['iframe_item'],
				'inline_markup'           => $template['prettyphoto']['inline_item'],
				'custom_markup'           => $template['prettyphoto']['custom_item'],
				'social_tools'            => $template['prettyphoto']['social_item'],
			),
		);

		if(vartrue($plugPref['downloadable'], false))
		{
			$settings['prettyphoto']['image_markup'] .= '<span class="download-btn">';
			$settings['prettyphoto']['image_markup'] .= '<a class="btn btn-default btn-secondary btn-xs" href="{path}">' . LAN_DOWNLOAD . '</a>';
			$settings['prettyphoto']['image_markup'] .= '</span>';
		}

		e107::js('settings', array('gallery' => $settings));
		e107::js('gallery', 'js/gallery.js');

		$gallery_load_prettyphoto['loaded'] = true;
	}
}
