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
//TODO Configure for news menus. 

class news_menu
{

	function __construct()
	{
		// e107::lan('news','admin', 'true');

	}

	/**
	 * Configuration Fields.
	 * @return array
	 */
	public function config($menu='') //TODO LAN
	{
		$fields = array();
		$categories = array();

		$sources = array('latest'=> "Latest News Items", 'sticky' => "Sticky News Items", 'template'=>"Assigned News items");

		$tmp =  e107::getDb()->retrieve('news_category','category_id,category_name',null, true);

		foreach($tmp as $val)
		{
			$id = $val['category_id'];
			$categories[$id] = $val['category_name'];
		}

		switch($menu)
		{
			case "latestnews":
					$fields['caption']      = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
					$fields['count']        = array('title'=> LAN_LIMIT, 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*', 'size'=>'mini'));
					$fields['category']     = array('title'=> LAN_CATEGORY, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$categories, 'default'=>'blank'));
			break;

			case "news_grid":
					$fields['caption']      = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'), 'help'=>LAN_OPTIONAL);
					$fields['category']     = array('title'=> LAN_CATEGORY, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$categories, 'default'=>"(".LAN_ALL.")"), 'help'=>"Limit news items to a specific category");
					$fields['source']       = array('title'=> "Source", 'type'=>'dropdown','writeParms'=>array('optArray'=>$sources), 'help'=>"Assigned items are those with a template assigned to 'News Grid Menu' ");
					$fields['layout']       = array('title'=> "Layout", 'type'=>'method', 'writeParms'=>'');
					$fields['count']        = array('title'=> "Number of Items to Display", 'type'=>'number', 'writeParms'=>array('pattern'=>'[0-9]*', 'default'=>4));
					$fields['titleLimit']   = array('title'=> "Title Character Limit", 'type'=>'number', 'writeParms'=>'');
					$fields['summaryLimit'] = array('title'=> "Summary Character Limit", 'type'=>'number', 'writeParms'=>'');

			break;

			case "news_carousel":
					$fields['caption']      = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'), 'help'=>LAN_OPTIONAL);
					$fields['category']     = array('title'=> LAN_CATEGORY, 'type'=>'dropdown', 'writeParms'=>array('optArray'=>$categories, 'default'=>"(".LAN_ALL.")"), 'help'=>"Limit news items to a specific category");
					$fields['source']       = array('title'=> "Source", 'type'=>'dropdown','writeParms'=>array('optArray'=>$sources), 'help'=>"Assigned items are those with a template assigned to 'News Carousel' ");
					$fields['count']        = array('title'=> "Number of Items to Display", 'type'=>'number', 'writeParms'=>array('pattern'=>'[0-9]*', 'default'=>4));
			break;


			case "news_categories":
					$fields['caption']      = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
					$fields['count']        = array('title'=> LAN_LIMIT, 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*'));
				break;

			case "news_months":
					$fields['showarchive']  = array('title'=> "Display Archive Link", 'type'=>'boolean');
					$fields['year']         = array('title'=> "Year", 'type'=>'text', 'writeParms'=>array('pattern'=>'[0-9]*', 'size'=>'mini'));
				break;

			case "other_news":
			case "other_news2":
					$fields['caption']   = array('title'=> LAN_CAPTION, 'type'=>'text', 'multilan'=>true, 'writeParms'=>array('size'=>'xxlarge'));
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