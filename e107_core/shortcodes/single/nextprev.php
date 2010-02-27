<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Core NEXTPREV shortcode
 *
 * $URL$
 * $Id$
 */

 /**
 * @package e107
 * @subpackage shortcodes
 * @version $Id$
 *
 * Render page navigation bar
 */

/**
 * Core NEXTPREV shortcode
 * Comma separated parameters are now deprecated.
 * Parameter string should be formatted as if it were the query string passed via a URL:
 * <code>$parm = 'total=10&amount=5&current=0&type=...'</code>
 *
 * Parameter list:
 * - total (integer) [required]: total records/pages
 * - amount (integer| string 'all') [required]: Records per page, always 1 when we counting pages (see 'type' parameter), ignored where tmpl_prefix is not set and 'old_np' pref is false
 * - current (integer)[required]: Current record/page
 * - type (string page|record) [optional]: What kind of navigation logic we need, default is 'record' (the old way)
 * - url (rawurlencode'd string) [required]: URL template, will be rawurldecode'd after parameters are parsed to array
 * Preffered 'FROM' template is now '--FROM--' (instead '[FROM]')
 * - caption (rawurlencode'd string) [optional]: Label, rawurldecode'd after parameters are parsed to array, language constants are supported
 * - pagetitle (rawurlencode'd string) [optional]: Page labels, rawurldecode'd after parameters are parsed to array,
 * separated by '|', if present they will be used as lablels instead page numbers; language constants are supported
 * - plugins (string) [optional]: plugin name used for template loading
 * - tmpl_prefix (string) [optional]: template keys prefix; core supported are 'default' and 'dropdown', default depends on 'old_np' pref
 * - navcount (integer) [optional]: number of navigation items to be shown, minimal allowed value is 4, default is 10
 *
 * WARNING: You have to do rawuldecode() on url, caption and title parameter values (before passing them to the shortcode)
 * or you'll break the whole script
 *
 * @param string $parm
 * @return string page navigation bar HTML
 */
function nextprev_shortcode($parm = '')
{
	$e107 = e107::getInstance();
	$pref = e107::getPref();

	include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_np.php');

	/**
	 * The NEW way.
	 * New parameter requirements formatted as a GET string.
	 * Template support.
	 */
	if(strpos($parm, 'total=') !== false)
	{
		// Calculate
		parse_str($parm, $parm);

		$total_items = intval($parm['total']);
		// search for template keys - default_start, default_end etc.
		if(isset($parm['tmpl_prefix']))
		{
			// forced
			$tprefix = vartrue($parm['tmpl_prefix'], 'default');
			$perpage = $parm['amount'] !== 'all' ? intval($parm['amount']) : $total_items;
		}
		// default, based on prefs
		elseif($pref['old_np'])
		{

			$tprefix = 'default';
			$perpage = $parm['amount'] !== 'all' ? intval($parm['amount']) : $total_items;
		}
		else
		{
			$tprefix = 'dropdown';
			$perpage = $total_items; // amount is ignored
		}
		$tprefix .= '_';
		// TODO - rename old_np to something more meaningful

		$current_start = intval($parm['current']);
		$nptype  = varset($parm['type'], 'record');
		switch ($nptype)
		{
			case 'page':
				$perpage = 1;
				$current_page = $current_start;
				$first_page = 1;
				$next_page = $current_page + 1;
				$prev_page = $current_page - 1;
				$total_pages = $total_items;
				$index_add = 1;
			break;

			default:
				$total_pages = ceil($total_items/$perpage);
				$current_page = ($current_start/$perpage) + 1;
				$next_page = $current_page*$perpage;
				$prev_page = $current_start/$perpage;
				$first_page = 0;
				$index_add = 0;
			break;
		}

		if($total_pages <= 1) {	return ''; }

		// urldecoded by parse_str()
		$url = str_replace('--FROM--', '[FROM]', $parm['url']);

		// Simple parser vars
		$e_vars = new e_vars(array(
			'total_pages' => $total_pages,
			'current_page' => $current_page
		));

		// urldecoded by parse_str()
		if(!varset($parm['caption']))
		{
			$e_vars->caption = 'LAN_NP_CAPTION';
		}
		// Advanced multilingual support: 'Page %1$d of %2$d' -> match the exact argument, result would be 'Page 1 of 20'
		$e_vars->caption = sprintf(defset($e_vars->caption, $e_vars->caption), $current_page, $total_pages);

		// urldecoded by parse_str()
		$pagetitle = explode('|',$parm['pagetitle']);

		// navigation number settings
		$navcount = abs(intval(varset($parm['navcount'], 10))); // prevent infinite loop!
		if($navcount < 4) $navcount = 4;
		$navmid = floor($navcount/2);

		// get template - nextprev_template.php, support for plugin template locations - myplug/templates/nextprev_template.php
		$tmpl = e107::getTemplate(varset($parm['plugin'], null), 'nextprev');

		// init advanced navigation visibility
		$show_first = $show_prev = ($current_page != 1);
		$show_last = $show_next = ($current_page != $total_pages);

		// Render
		// XXX - parseTemplate vs simpleParse ??? Currently can't find a reason why we should parse via parseTemplate
		$tp = e107::getParser();

		// Nextprev navigation start
		$ret = $tp->simpleParse($tmpl[$tprefix.'start'], $e_vars);

		// caption, e.g. 'Page 1 of 20' box
		if($e_vars->caption)
		{
			$ret .= $tp->simpleParse($tmpl[$tprefix.'nav_caption'], $e_vars);
		}

		$ret_array = array();

		// Show from 1 to $navcount || $total_pages
		if($current_page <= $navmid || $total_pages <= $navcount)
		{
			$loop_start = 0;
			$loop_end = $navcount;
			$show_first = false;
			if($navcount >= $total_pages)
			{
				$loop_end = $total_pages;
				$show_last = false;
			}
		}
		// Calculate without producing infinite loop ;)
		else
		{
			if($current_page + $navmid >= $total_pages)
			{
				$loop_start = $total_pages - $navcount;
				if($loop_start < 0) $loop_start = 0;
				$loop_end = $total_pages;
				$show_last = false;
			}
			else
			{
				$loop_start = $current_page - $navmid;
				$loop_end = $current_page + ($navcount - $navmid); // odd/even $navcount support
				if($loop_start < 0)
				{
					$loop_start = 0;
				}
				elseif($loop_end > $total_pages)
				{
					$loop_end = $total_pages;
					$show_last = false;
				}
			}
		}

		// Add 'first', 'previous' navigation
		if($show_prev)
		{
			if($show_first && !empty($tmpl[$tprefix.'nav_first']))
			{
				$e_vars->url = str_replace('[FROM]', $first_page, $url);
				$e_vars->label = LAN_NP_FIRST;
				$e_vars->url_label = LAN_NP_URLFIRST;
				$ret_array[] = $tp->simpleParse($tmpl[$tprefix.'nav_first'], $e_vars);
			}

			if(!empty($tmpl[$tprefix.'nav_prev']))
			{
				$e_vars->url = str_replace('[FROM]', $prev_page, $url);
				$e_vars->label = LAN_NP_PREVIOUS;
				$e_vars->url_label = LAN_NP_URLPREVIOUS;
				$ret_array[] = $tp->simpleParse($tmpl[$tprefix.'nav_prev'], $e_vars);
			}
		}

		$e_vars_loop = new e_vars();
		$ret_items = array();
		for($c = $loop_start; $c < $loop_end; $c++)
		{
			$label = '';
			if(varset($pagetitle[$c]))
			{
				$label = defset($pagetitle[$c], $pagetitle[$c]);
			}
			$e_vars_loop->url = str_replace('[FROM]', ($perpage * ($c + $index_add)), $url);
			$e_vars_loop->label = $label ? $tp->toHTML($label, false, 'TITLE') : $c + 1;

			if($c + 1 == $current_page)
			{
				$e_vars_loop->url_label = $label ? $tp->toAttribute($label) : LAN_NP_URLCURRENT;
				$ret_items[] = $tp->simpleParse($tmpl[$tprefix.'item_current'], $e_vars_loop);
			}
			else
			{
				$e_vars_loop->url_label = $label ? $tp->toAttribute($label) : LAN_NP_GOTO;
				$e_vars_loop->url_label = sprintf($e_vars_loop->url_label, ($c + 1));
				$ret_items[] = $tp->simpleParse($tmpl[$tprefix.'item'], $e_vars_loop);
			}
		}
		$ret_array[] = $tp->simpleParse($tmpl[$tprefix.'items_start'], $e_vars).implode($tmpl[$tprefix.'separator'], $ret_items).$tp->simpleParse($tmpl[$tprefix.'items_end'], $e_vars);
		unset($ret_items, $e_vars_loop);

		if($show_next)
		{
			if(!empty($tmpl[$tprefix.'nav_next']))
			{
				$e_vars->url = str_replace('[FROM]', $next_page, $url);
				$e_vars->label = LAN_NP_NEXT;
				$e_vars->url_label = LAN_NP_URLNEXT;
				$ret_array[] = $tp->simpleParse($tmpl[$tprefix.'nav_next'], $e_vars);
			}

			if($show_last && !empty($tmpl[$tprefix.'nav_last']))
			{
				$e_vars->url = str_replace('[FROM]', $last_page, $url);
				$e_vars->label = LAN_NP_LAST;
				$e_vars->url_label = LAN_NP_URLLAST;
				$ret_array[] = $tp->simpleParse($tmpl[$tprefix.'nav_last'], $e_vars);
			}
		}

		$ret .= implode($tmpl[$tprefix.'separator'], $ret_array);

		// Nextprev navigation end
		$ret .= $tp->simpleParse($tmpl[$tprefix.'end'], $e_vars);
		unset($e_vars, $ret_array);

		return $ret;
	}
	/**
	 * The old way, ALL BELOW IS DEPRECATED
	 */
	else
	{
		$parm_count = substr_count($parm, ',');
		while($parm_count < 5)
		{
			$parm .= ',';
			$parm_count++;
		}

		$p = explode(',', $parm, 6);

		$total_items = intval($p[0]);
		$perpage = intval($p[1]);

		// page number instead record start now supported
		if(is_numeric($p[2]))
		{
			$current_start = intval($p[2]);
			$current_page = ($current_start/$perpage) + 1;
			$total_pages = ceil($total_items/$perpage);
			$index_add = 0;
		}
		else // new - page support in format 'p:1'
		{
			$perpage = 1;
			$current_start = intval(array_pop(explode(':', $p[2], 2)));
			$current_page = $current_start;
			$total_pages = $total_items;
			$index_add = 1;
		}

		if($total_items < $perpage) {	return ''; }

		$url = trim($p[3]);
		$caption = trim($p[4]);
		$pagetitle = explode('|',trim($p[5]));

		$caption = (!$caption || $caption == 'off') ? NP_3.'&nbsp;' : $caption;

		while(substr($url, -1) == '.')
		{
			$url=substr($url, 0, -1);
		}

	}

	if($total_pages > 1)
	{
		if(varsettrue($pref['old_np']))
		{

			$NP_PRE_ACTIVE = '';
			$NP_POST_ACTIVE = '';
			$NP_STYLE = '';

	        if(!defined('NEXTPREV_NOSTYLE') || NEXTPREV_NOSTYLE==FALSE){
	        	$NP_PRE_ACTIVE = '[';
	            $NP_POST_ACTIVE = '] ';
				$NP_STYLE = "style='text-decoration:underline'";
			}


			//	Use OLD nextprev method
			$nppage = '';
			$nppage .= "\n\n<!-- Start of Next/Prev -->\n\n";
			if ($total_pages > 10)
			{
				//$current_page = ($current_start/$perpage)+1;

				for($c = 0; $c <= 2; $c++)
				{
					if($perpage * ($c + $index_add) == $current_start)
					{
						$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
					}
					else
					{
						$link = str_replace("[FROM]", ($perpage * ($c + $index_add)), $url);
						$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";

					}
				}

				if ($current_page >= 3 && $current_page <= 5)
				{
					for($c = 3; $c <= $current_page; $c++)
					{
						if($perpage * ($c + $index_add) == $current_start)
						{
							$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
						}
						else
						{
							$link = str_replace("[FROM]", ($perpage * ($c + $index_add)), $url);
							$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";
						}
					}
				}
				else if($current_page >= 6 && $current_page <= ($total_pages-5))
				{
					$nppage .= " ... ";
					for($c = ($current_page-2); $c <= $current_page; $c++)
					{
						if($perpage * ($c + $index_add) == $current_start)
						{
							$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
						}
						else
						{
							$link = str_replace("[FROM]", ($perpage * ($c + $index_add)), $url);
							$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";
						}
					}
				}
				$nppage .= " ... ";

				if (($current_page+5) > $total_pages && $current_page != $total_pages)
				{
					$tmp = ($current_page-2);
				}
				else
				{
					$tmp = $total_pages-3;
				}

				for($c = $tmp; $c <= ($total_pages-1); $c++)
				{
					if($perpage * ($c + $index_add) == $current_start)
					{
						$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
					}
					else
					{
						$link = str_replace("[FROM]", ($perpage * ($c + $index_add)), $url);
						$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";
					}
				}

			}
			else
			{
				for($c = 0; $c < $total_pages; $c++)
				{
					if($perpage * ($c + $index_add) == $current_start)
					{
						$nppage .= $NP_PRE_ACTIVE."<span class='nextprev_current' {$NP_STYLE} >".($c+1)."</span>".$NP_POST_ACTIVE."\n";
					}
					else
					{
						$link = str_replace("[FROM]", ($perpage * ($c + $index_add)), $url);
						$nppage .= "<a class='nextprev_link' href='{$link}'>".($c+1)."</a> \n";
					}
				}
			}
	        $nppage .= "\n\n<!-- End of Next/Prev -->\n\n";
			return $caption.$nppage;
		}

		// Use NEW nextprev method
		$np_parm['template'] = "[PREV]&nbsp;&nbsp;[DROPDOWN]&nbsp;&nbsp;[NEXT]";
		$np_parms['prev'] = '&nbsp;&nbsp;&lt;&lt;&nbsp;&nbsp;';
		$np_parms['next'] = '&nbsp;&nbsp;&gt;&gt;&nbsp;&nbsp;';
		$np_parms['np_class'] = 'tbox npbutton';
		$np_parms['dropdown_class'] = 'tbox npdropdown';

		if($cached_parms = getcachedvars('nextprev'))
		{
			$tmp = $cached_parms;
			foreach($tmp as $key => $val)
			{
				$np_parms[$key]=$val;
			}
		}

		$prev='';
		$next='';
		if($current_page > 1)
		{
			$prevstart = ($current_start - $perpage);

			if(substr($url, 0, 5) == 'url::')
			{
				$urlParms = explode('::', $url);
				$urlParms[3] = str_replace('[FROM]', $prevstart, $urlParms[3]);
				$link = $e107->url->getUrl($urlParms[1], $urlParms[2], $urlParms[3]);
			}
			else
			{
				$link = str_replace('[FROM]', $prevstart, $url);
			}
			$prev = "<a class='{$np_parms['np_class']}' style='text-decoration:none' href='{$link}'>{$np_parms['prev']}</a>";
		}
		if($current_page < $total_pages)
		{
			$nextstart = ($current_start + $perpage);
			if(substr($url, 0, 5) == 'url::')
			{
				$urlParms = explode('::', $url);
				$urlParms[3] = str_replace('[FROM]', $nextstart, $urlParms[3]);
				$link = $e107->url->getUrl($urlParms[1], $urlParms[2], $urlParms[3]);
			}
			else
			{
				$link = str_replace('[FROM]', $nextstart, $url);
			}
			$next = "<a class='{$np_parms['np_class']}' style='text-decoration:none' href='{$link}'>{$np_parms['next']}</a>";
		}
		$dropdown = "<select class='{$np_parms['dropdown_class']}' name='pageSelect' onchange='location.href=this.options[selectedIndex].value'>";
		for($i = 1; $i <= $total_pages; $i++)
		{
			$sel = '';
			if($current_page == $i)
			{
				$sel = " selected='selected' ";
			}
			$newstart = ($i-1 + $index_add)*$perpage;
			if(substr($url, 0, 5) == 'url::')
			{
				$urlParms = explode('::', $url);
				$urlParms[3] = str_replace('[FROM]', $newstart, $urlParms[3]);
				$link = $e107->url->getUrl($urlParms[1], $urlParms[2], $urlParms[3]);
			}
			else
			{
				$link = str_replace('[FROM]', $newstart, $url);
			}
	        $c = $i-1 + $index_add;
	        $title = ($pagetitle[$c]) ? $pagetitle[$c] : $i;
	        $dropdown .= "<option value='{$link}' {$sel}>{$title}</option>\n";
		}
		$dropdown .= '</select>';
		$ret = $np_parm['template'];
		$ret = str_replace('[DROPDOWN]', $dropdown, $ret);
		$ret = str_replace('[PREV]', $prev, $ret);
		$ret = str_replace('[NEXT]', $next, $ret);
		return $caption.$ret;
	}
}
return '';
