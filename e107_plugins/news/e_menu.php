<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
*/

if (!defined('e107_INIT')) { exit; }

//v2.x Standard for extending menu configuration within Menu Manager. (replacement for v1.x config.php)


class news_menu
{

	public $tabs = array();

	function __construct()
	{
		e107::lan('news', true);

	}

	/**
	 * Configuration Fields.
	 * @return array
	 */
	public function config($menu='') 
	{
		$fields = array();
		$categories = array();

		$sources = array('latest'=> LAN_NEWS_ADMIN_00, 'sticky' => LAN_NEWS_ADMIN_01, 'template'=> LAN_NEWS_ADMIN_02);

		$tmp =  e107::getDb()->retrieve('news_category','category_id,category_name',null, true);

		$templates = e107::getLayouts('news','news_grid', 'front', null, false, false);

		foreach($tmp as $val)
		{
			$id = $val['category_id'];
			$categories[$id] = $val['category_name'];
		}

		switch($menu)
		{
			case "latestnews":

					$fields['caption']      = array('title'=> LAN_CAPTION, 'tab'=>0, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
					$fields['count']        = array('title'=> LAN_LIMIT, 'tab'=>1, 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*', 'size'=>'mini'));
					$fields['category']     = array('title'=> LAN_CATEGORY, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$categories, 'default'=>'blank'), 'help'=>'');
			break;

			case "news_grid":
					$this->tabs = array(0 => LAN_PREFS, 1 => LAN_NEWS_ADMIN_09);

					$fields['caption']      = array('title'=> LAN_CAPTION, 'type'=>'text', 'tab'=>0, 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'), 'help'=>LAN_OPTIONAL);
					$fields['category']     = array('title'=> LAN_CATEGORY, 'type'=>'dropdown', 'tab'=>0, 'writeParms'=>array('optArray'=>$categories, 'default'=>"(".LAN_ALL.")"), 'help'=> LAN_NEWS_ADMIN_03);
					$fields['source']       = array('title'=> LAN_SOURCE, 'type'=>'dropdown','tab'=>0, 'writeParms'=>array('optArray'=>$sources), 'help'=> LAN_NEWS_ADMIN_04);
					$fields['layout']     = array('title'=> LAN_TEMPLATE, 'type'=>'dropdown', 'tab'=>0, 'writeParms'=>array('optArray'=>$templates));
				//	$fields['layout']       = array('title'=> "Layout", 'type'=>'method',  'tab'=>0,'writeParms'=>'');
					$fields['count']        = array('title'=> LAN_NEWS_ADMIN_05, 'tab'=>1, 'type'=>'number', 'writeParms'=>array('pattern'=>'[0-9]*', 'default'=>4));
					$fields['feature']      = array('title'=> LAN_NEWS_ADMIN_10, 'tab'=>1, 'type'=>'number', 'writeParms'=>array('pattern'=>'[0-9]*', 'default'=>0));
					$fields['titleLimit']   = array('title'=> LAN_NEWS_ADMIN_06, 'tab'=>1,  'type'=>'number', 'writeParms'=>'');
					$fields['summaryLimit'] = array('title'=> LAN_NEWS_ADMIN_07,'tab'=>1,  'type'=>'number', 'writeParms'=>'');

			break;

			case "news_carousel":
					$fields['caption']      = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'), 'help'=>LAN_OPTIONAL);
					$fields['category']     = array('title'=> LAN_CATEGORY, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$categories, 'default'=>"(".LAN_ALL.")"), 'help'=> LAN_NEWS_ADMIN_03);
					$fields['source']       = array('title'=> LAN_SOURCE, 'type'=>'dropdown','writeParms'=>array('optArray'=>$sources), 'help'=> LAN_NEWS_ADMIN_11);
					$fields['count']        = array('title'=> LAN_NEWS_ADMIN_05, 'type'=>'number', 'writeParms'=>array('pattern'=>'[0-9]*', 'default'=>4));
			break;


			case "news_categories":
					$fields['caption']      = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
					$fields['count']        = array('title'=> LAN_LIMIT, 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*'));
				break;

			case "news_months":
					$fields['showarchive']  = array('title'=> LAN_NEWS_ADMIN_08, 'type'=>'boolean');
					$fields['year']         = array('title'=> "Year", 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*', 'size'=>'mini'));
				break;

			case "other_news":
			case "other_news2":
					$fields['caption']   = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
				break;

			case "news_archive":
					$fields['caption']   = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
			//		$fields['badges']   = array('title'=> "Display Badges", 'type'=>'boolean');

					break;

		}

		 return $fields;




	}


}

// optional
class news_menu_form extends e_form
{

	public function layout($curVal)
	{

		// class='alert alert-info'

		$arr = array(
		"col-md-6" => "<div class='row'><div class='col-md-6'><div {STYLE}>1/2</div></div><div class='col-md-6'><div {STYLE}>1/2</div></div></div>",
		"col-md-4" => "<div class='row'><div class='col-md-4'><div {STYLE}>1/3</div></div><div class='col-md-4'><div {STYLE}>1/3</div></div><div class='col-md-4'><div {STYLE}>1/3</div></div></div>",
		"col-md-3" => "<div class='row'><div class='col-md-3 '><div {STYLE}>1/4</div></div><div class='col-md-3'><div {STYLE}>1/4</div></div><div class='col-md-3'><div {STYLE}>1/4</div></div><div class='col-md-3'><div {STYLE}>1/4</div></div></div>",
		);

		$text = '<table class="table news-menu-shade">';

		foreach($arr as $k=>$v)
		{

			$text .= "<tr><td>".$this->radio('layout', $k, ($curVal == $k), array('label'=>$k))."</td><td>".str_replace('{STYLE}',"class='alert alert-info' style='margin-bottom:0;text-align:center' ",$v)."</td></tr>";
		}

		$text .= "</table>";

		return $text;
	}

}
