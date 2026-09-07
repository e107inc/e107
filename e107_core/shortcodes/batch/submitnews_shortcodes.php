<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Submit news shortcodes
 */

if(!defined('e107_INIT')) { exit; }

e107::coreLan('submitnews');

/**
 * Fields of the submit-news form, for submitnews_template.php.
 *
 * The category list and the minimum attachment dimensions are handed over by
 * {@see submitNews::form()} through setVars(); nothing here queries.
 */
class submitnews_shortcodes extends e_shortcode
{
	/**
	 * The bbcode the site prints above the form, from the news_subheader preference.
	 *
	 * @return string
	 */
	function sc_submitnews_subheader($parm = null)
	{
		$subheader = e107::pref('core', 'news_subheader');

		return !empty($subheader) ? e107::getParser()->toHTML($subheader, true, 'BODY') : '';
	}

	/**
	 * @return string empty for a logged-in user, whose name comes from their account
	 */
	function sc_submitnews_name($parm = null)
	{
		if(!e107::getUser()->isGuest())
		{
			return '';
		}

		$value = e107::getParser()->toAttribute(varset($_POST['submitnews_name'], ''), true);

		return "<input class='tbox' type='text' name='submitnews_name' size='60' value='".$value."' maxlength='100' required />";
	}

	/**
	 * @return string empty for a logged-in user, whose address comes from their account
	 */
	function sc_submitnews_email($parm = null)
	{
		if(!e107::getUser()->isGuest())
		{
			return '';
		}

		$value = e107::getParser()->toAttribute(varset($_POST['submitnews_email'], ''), true);

		return "<input class='tbox' type='text' name='submitnews_email' size='60' value='".$value."' maxlength='100' required />";
	}

	/**
	 * @return string the news categories, or the notice that there are none
	 */
	function sc_submitnews_category($parm = null)
	{
		$categories = varset($this->var['categories']);

		if(empty($categories))
		{
			return NWSLAN_10;
		}

		$tp = e107::getParser();
		$text = "<select name='cat_id' class='tbox form-control'>";

		foreach($categories as $cat)
		{
			$selected = ((string) varset($_POST['cat_id'], '') === (string) $cat['category_id']) ? "selected='selected'" : "";
			$text .= "<option value='".$cat['category_id']."' ".$selected.">".$tp->toHTML($cat['category_name'], false, 'defs')."</option>";
		}

		return $text."</select>";
	}

	function sc_submitnews_title($parm = null)
	{
		return e107::getForm()->text('submitnews_title', vartrue($_POST['submitnews_title']), 200, array('required' => 1));
	}

	function sc_submitnews_body($parm = null)
	{
		$value = e107::getParser()->toForm(vartrue($_POST['submitnews_item']));

		return e107::getForm()->bbarea('submitnews_item', $value, null, null, 'large');
	}

	function sc_submitnews_keywords($parm = null)
	{
		return $this->element('submitnews_keywords');
	}

	function sc_submitnews_summary($parm = null)
	{
		return $this->element('submitnews_summary');
	}

	function sc_submitnews_description($parm = null)
	{
		return $this->element('submitnews_description');
	}

	/**
	 * The eight media URL fields, also reached through {@see submitNewsForm::submitnews_media()}.
	 *
	 * @return string
	 */
	function sc_submitnews_media($parm = null)
	{
		$placeholders = array(
			'eg. http://www.youtube.com/watch?v=Mxhn11_fzJQ',
			'eg. http://path-to-image/image.jpg',
			'eg. http://path-to-audio/file.mp3'
		);

		$frm = e107::getForm();
		$text = '';

		for($i = 0; $i < 8; $i++)
		{
			$help = isset($placeholders[$i]) ? $placeholders[$i] : '';
			$text .= "<div class='form-group'>";
			$text .= $frm->text('submitnews_media['.$i.']', varset($_POST['submitnews_media'][$i]), 255, array('placeholder' => $help));
			$text .= "</div>";
		}

		return $text;
	}

	/**
	 * @return string empty unless the site takes attachments with submissions
	 */
	function sc_submitnews_attach($parm = null)
	{
		$pref = e107::pref('core');

		if(empty($pref['subnews_attach']) || empty($pref['upload_enabled']) || !check_class($pref['upload_class']) || !deftrue('FILE_UPLOADS'))
		{
			return '';
		}

		$text = "<input class='tbox' type='file' name='file_userfile[]' multiple='multiple' />";

		if(!empty($this->var['min_width']))
		{
			$notice = defset('SUBNEWSLAN_ATTACH_MIN_DIMENSIONS', 'Minimum dimensions: [x]px × [y]px');
			$dimensions = str_replace(array('[x]', '[y]'), array($this->var['min_width'], $this->var['min_height']), $notice);
			$text .= "<div class='alert alert-warning'>".$dimensions."</div>";
		}

		return $text;
	}

	function sc_submitnews_submit($parm = null)
	{
		return "<input class='btn btn-success button' type='submit' name='submitnews_submit' value='".LAN_136."' />";
	}

	/**
	 * One of the fields e107's news table already describes to {@see e_form::renderElement()}.
	 *
	 * @param string $key
	 * @return string
	 */
	private function element($key)
	{
		$fields = array(
			'submitnews_keywords'    => array('type' => 'tags'),
			'submitnews_summary'     => array('type' => 'text', 'writeParms' => array('maxlength' => 255, 'size' => 'xxlarge')),
			'submitnews_description' => array('type' => 'textarea', 'writeParms' => array('placeholder' => SUBNEWSLAN_12)),
		);

		return isset($fields[$key]) ? e107::getForm()->renderElement($key, '', $fields[$key]) : '';
	}
}
