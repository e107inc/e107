<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * List Class
 *
 *
*/
if (!defined('e107_INIT')) { exit; }

/**
 *	Base class for list_new plugin
 *
 *	@package e107_plugins
 *	@subpackage list_new
 */

/**
 * class listclass
 * The base class
 */
class listclass
{
	var $defaultArray;
	var $sections;
	var $titles;
	var $content_types;
	var $content_name;
	var $list_pref;
	var $mode;
	var $shortcodes = FALSE;

	/**
	 * constructor
	 *
	 * @param string $mode the mode of the caller (default, admin)
	 * @return void
	 *
	 */
	function __construct($mode='')
	{
		global $TEMPLATE_LIST_NEW, $list_shortcodes;

		$this->plugin_dir = e_PLUGIN."list_new/";
		$this->e107 = e107::getInstance();

		//language
		e107::includeLan($this->plugin_dir."languages/".e_LANGUAGE.".php");

		//template
		if (is_readable(THEME."list_template.php"))
		{
			require_once(THEME."list_template.php");
		}
		else
		{
			require_once($this->plugin_dir."list_template.php");
		}
		$this->template = $TEMPLATE_LIST_NEW;

		//shortcodes
		require_once($this->plugin_dir."list_shortcodes.php");
//		$this->shortcodes = $list_shortcodes;
		$this->shortcodes = new list_shortcodes();
		$this->shortcodes->rc = $this;


		if($mode=='admin')
		{
			require_once($this->plugin_dir."list_admin_class.php");
			$this->admin = new list_admin($this);
		}

		//default sections (present in this list plugin)
		$this->defaultArray = array("news", "comment", "members");
	}

	/**
	 * helper method, parse the template
	 *
	 * @param string $template the template to parse
	 * @return string
	 *
	 */
	function parseTemplate($template)
	{
		//for each call to the template, provide the correct data set through load_globals
		//list_shortcodes::load_globals();
		return e107::getParser()->parseTemplate($this->template[$template], true, $this->shortcodes);
	}

	/**
	 * get preferences, retrieve all preferences from core table
	 *
	 * @return array
	 *
	 */
	function getListPrefs()
	{
		$listPrefs = e107::pref('list_new'); //TODO Convert from old format to new.   
		
		//insert default preferences
		if (empty(	$listPrefs))
		{
       		$listPrefs = $this->list_pref = $this->getDefaultPrefs();
       		e107::getPlugConfig('list_new')->reset()->setPref($listPrefs)->save(true);
    	}
    	return $listPrefs;
		/*
		$sql = e107::getDb();
		//check preferences from database
		$num_rows = $sql->gen("SELECT * FROM #core WHERE e107_name='list' ");
		$row = $sql->fetch();

		//insert default preferences
		if (empty($row['e107_value']))
		{
			$this->getSections();
			$this->list_pref = $this->getDefaultPrefs();
			$tmp = $this->e107->arrayStorage->WriteArray($this->list_pref);

			$sql->insert("core", "'list', '$tmp' ");
			$sql->gen("SELECT * FROM #core WHERE e107_name='list' ");
		}

		$this->list_pref = $this->e107->arrayStorage->ReadArray($row['e107_value']);
		return $this->list_pref;
		*/
	}

	/**
	 * prepareSection checks if the sections should be displayed
	 *
	 * @param string $mode the mode of the area (menu/page - new/recent)
	 * @return array
	 *
	 */
	function prepareSection($mode)
	{
		$len = strlen($mode) + 9;
		$sections = array();

		//get all sections to use
		foreach($this->list_pref as $key=>$value)
		{
			if(substr($key,-$len) == "_{$mode}_display" && $value == "1")
			{
				$sections[] = substr($key,0,-$len);
			}
		}
		return $sections;
	}

	/**
	 * prepareSectionArray parses the preferences for each section
	 *
	 * @param string $mode the mode of the area (menu/page - new/recent)
	 * @return array
	 *
	 */
	function prepareSectionArray($mode)
	{
		//section reference
		for($i=0;$i<count($this->sections);$i++)
		{
			$s = $this->sections[$i];
			if(vartrue($this->list_pref[$s."_".$mode."_display"]) == '1')
			{
				$arr[$s]['caption'] 	= vartrue($this->list_pref[$s."_".$mode."_caption"]);
				$arr[$s]['display'] 	= vartrue($this->list_pref[$s."_".$mode."_display"]);
				$arr[$s]['open'] 		= vartrue($this->list_pref[$s."_".$mode."_open"]);
				$arr[$s]['author'] 		= vartrue($this->list_pref[$s."_".$mode."_author"]);
				$arr[$s]['category'] 	= vartrue($this->list_pref[$s."_".$mode."_category"]);
				$arr[$s]['date'] 		= vartrue($this->list_pref[$s."_".$mode."_date"]);
				$arr[$s]['icon'] 		= vartrue($this->list_pref[$s."_".$mode."_icon"]);
				$arr[$s]['amount'] 		= vartrue($this->list_pref[$s."_".$mode."_amount"]);
				$arr[$s]['order'] 		= vartrue($this->list_pref[$s."_".$mode."_order"]);
				$arr[$s]['section'] 	= $s;
			}
		}
		//sort array on order values set in preferences
		usort($arr, create_function('$e,$f','return $e["order"]==$f["order"]?0:($e["order"]>$f["order"]?1:-1);'));

		return $arr;
	}

	/**
	 * getDefaultSections loads all default 'core' sections from the constructor
	 *
	 * @return void
	 *
	 */
	function getDefaultSections()
	{
		//default always present sections
		for($i=0;$i<count($this->defaultArray);$i++)
		{
			$this->sections[] = $this->defaultArray[$i];
			$this->titles[] = $this->defaultArray[$i];
		}
		return;
	}

	//content needs this to split each main parent into separate sections
	/**
	 * getContentSections loads all top level content categories
	 *
	 * @param string $mode (default, add)
	 * @return void
	 *
	 */
	function getContentSections($mode='')
	{
		$sql = e107::getDb();
		global $pref;

		if (!$content_install = isset($pref['plug_installed']['content']))
		{
			return;
		}

		$content_types = array();

		//get top level categories
		if($mainparents = $sql->gen("SELECT content_id, content_heading FROM #pcontent WHERE content_parent = '0' AND (content_datestamp=0 || content_datestamp < ".time().") AND (content_enddate=0 || content_enddate>".time().") ORDER BY content_heading"))
		{
			$content_name = 'content';
			while($row = $sql->fetch())
			{
				$content_types[] = "content_".$row['content_id'];
				if(vartrue($mode) == "add")
				{
					$this->sections[] = "content_".$row['content_id'];
					$this->titles[] = $content_name." : ".$row['content_heading'];
				}
			}
		}
		$this->content_types = array_unique($content_types);
		$this->content_name = $content_name;

		return;
	}

	/**
	 * getSections loads all sections
	 *
	 * @return void
	 *
	 */
	function getSections()
	{
		global $pref;

		$this->getDefaultSections();

		if(is_array($pref['e_list_list']))
		{
			foreach($pref['e_list_list'] as $file)
			{
				if ($plugin_installed = isset($pref['plug_installed'][$file]))
				{
					if($file == "content")
					{
						$this->getContentSections("add");
					}
					else
					{
						$this->sections[] = $file;
						$this->titles[] = $file;
					}
				}
			}
		}
		return;
	}

	/**
	 * getDefaultPrefs retrieve all default preferences (if none present)
	 *
	 * @return array
	 *
	 */
	function getDefaultPrefs()
	{
		global $pref;

		$prf = array();
		//section preferences
		for($i=0;$i<count($this->sections);$i++)
		{
			$s = $this->sections[$i];
			if(!in_array($this->sections[$i], $this->defaultArray))
			{
				if(!in_array($s, $this->content_types))
				{
					if ($plugin_installed = isset($pref['plug_installed'][e107::getParser()->toDB($s, true)]))
					{
						$prf["$s_recent_menu_caption"] = $s;
						$prf["$s_recent_page_caption"] = $s;
						$prf["$s_new_menu_caption"] = $s;
						$prf["$s_new_page_caption"] = $s;
					}
				}
				else
				{
					$prf["$s_recent_menu_caption"] = $this->titles[$i];
					$prf["$s_recent_page_caption"] = $this->titles[$i];
					$prf["$s_new_menu_caption"] = $this->titles[$i];
					$prf["$s_new_page_caption"] = $this->titles[$i];
				}
			}
			else
			{
				$prf["$s_recent_menu_caption"] = $s;
				$prf["$s_recent_page_caption"] = $s;
				$prf["$s_new_menu_caption"] = $s;
				$prf["$s_new_page_caption"] = $s;
			}

			$prf["$s_recent_menu_display"] = "1";
			$prf["$s_recent_menu_open"] = "0";
			$prf["$s_recent_menu_author"] = "0";
			$prf["$s_recent_menu_category"] = "0";
			$prf["$s_recent_menu_date"] = "1";
			$prf["$s_recent_menu_amount"] = "5";
			$prf["$s_recent_menu_order"] = ($i+1);
			$prf["$s_recent_menu_icon"] = '';

			$prf["$s_recent_page_display"] = "1";
			$prf["$s_recent_page_open"] = "1";
			$prf["$s_recent_page_author"] = "1";
			$prf["$s_recent_page_category"] = "1";
			$prf["$s_recent_page_date"] = "1";
			$prf["$s_recent_page_amount"] = "10";
			$prf["$s_recent_page_order"] = ($i+1);
			$prf["$s_recent_page_icon"] = "1";

			$prf["$s_new_menu_display"] = "1";
			$prf["$s_new_menu_open"] = "0";
			$prf["$s_new_menu_author"] = "0";
			$prf["$s_new_menu_category"] = "0";
			$prf["$s_new_menu_date"] = "1";
			$prf["$s_new_menu_amount"] = "5";
			$prf["$s_new_menu_order"] = ($i+1);
			$prf["$s_new_menu_icon"] = "1";

			$prf["$s_new_page_display"] = "1";
			$prf["$s_new_page_open"] = "1";
			$prf["$s_new_page_author"] = "1";
			$prf["$s_new_page_category"] = "1";
			$prf["$s_new_page_date"] = "1";
			$prf["$s_new_page_amount"] = "10";
			$prf["$s_new_page_order"] = ($i+1);
			$prf["$s_new_page_icon"] = "1";
		}

		//new menu preferences
		$prf['new_menu_caption'] = LIST_ADMIN_15;
		$prf['new_menu_icon_use'] = "1";
		$prf['new_menu_icon_default'] = "1";
		$prf['new_menu_char_heading'] = "20";
		$prf['new_menu_char_postfix'] = "...";
		$prf['new_menu_datestyle'] = "%d %b";
		$prf['new_menu_datestyletoday'] = "%H:%M";
		$prf['new_menu_showempty'] = "1";
		$prf['new_menu_openifrecords'] = '';

		//new page preferences
		$prf['new_page_caption'] = LIST_ADMIN_15;
		$prf['new_page_icon_use'] = "1";
		$prf['new_page_icon_default'] = "1";
		$prf['new_page_char_heading'] = '';
		$prf['new_page_char_postfix'] = '';
		$prf['new_page_datestyle'] = "%d %b";
		$prf['new_page_datestyletoday'] = "%H:%M";
		$prf['new_page_showempty'] = "1";
		$prf['new_page_colomn'] = "1";
		$prf['new_page_welcometext'] = LIST_ADMIN_16;
		$prf['new_page_timelapse'] = "1";
		$prf['new_page_timelapse_days'] = "30";
		$prf['new_page_openifrecords'] = '';

		//recent menu preferences
		$prf['recent_menu_caption'] = LIST_ADMIN_14;
		$prf['recent_menu_icon_use'] = "1";
		$prf['recent_menu_icon_default'] = "1";
		$prf['recent_menu_char_heading'] = "20";
		$prf['recent_menu_char_postfix'] = "...";
		$prf['recent_menu_datestyle'] = "%d %b";
		$prf['recent_menu_datestyletoday'] = "%H:%M";
		$prf['recent_menu_showempty'] = '';
		$prf['recent_menu_openifrecords'] = '';

		//recent page preferences
		$prf['recent_page_caption'] = LIST_ADMIN_14;
		$prf['recent_page_icon_use'] = "1";
		$prf['recent_page_icon_default'] = "1";
		$prf['recent_page_char_heading'] = '';
		$prf['recent_page_char_postfix'] = '';
		$prf['recent_page_datestyle'] = "%d %b";
		$prf['recent_page_datestyletoday'] = "%H:%M";
		$prf['recent_page_showempty'] = '';
		$prf['recent_page_colomn'] = "1";
		$prf['recent_page_welcometext'] = LIST_ADMIN_13;
		$prf['recent_page_openifrecords'] = '';

		return $prf;
	}

	/**
	 * displaySection, prepare and render a section
	 *
	 * @param array $arr the array of preferences for this section
	 * @return string
	 *
	 */
	function displaySection($arr)
	{
		//set settings
		$this->settings = $arr;

		//get content sections
		$this->getContentSections();

		//load e_list file
		$this->data = $this->load_elist();
		
		//$this->shortcodes->rc->data = $this->data;


		//set record variables
		$this->row = array();
		$this->row['caption'] = '';
		$this->row['icon'] = '';
		$this->row['date'] = '';
		$this->row['heading'] = '';
		$this->row['author'] = '';
		$this->row['category'] = '';
		$this->row['info'] = '';

		$text = '';

		switch($this->mode)
		{
			case 'recent_menu':
				$text .= $this->parseRecord('MENU_RECENT');
				break;
			case 'new_menu':
				$text .= $this->parseRecord('MENU_NEW');
				break;
			case 'recent_page':
				$text .= $this->parseRecord('PAGE_RECENT');
				break;
			case 'new_page':
				$text .= $this->parseRecord('PAGE_NEW');
				break;
		}

		return $text;
	}

	/**
	 * parseRecord renders the items within a section
	 *
	 * @param string $area the area for display
	 * @return string
	 *
	 */
	function parseRecord($area)
	{
		if(!in_array($area, array('MENU_RECENT', 'MENU_NEW', 'PAGE_RECENT', 'PAGE_NEW')))
		{
			return;
		}

		//echo "parse: ".$area."_START<br />";
		$text = $this->parseTemplate($area.'_START');
		if(is_array($this->data['records']))
		{
			foreach($this->data['records'] as $this->row)
			{
				$this->shortcodes->row = $this->row;
				//echo "parse: ".$area."<br />";
				$text .= $this->parseTemplate($area);
			}
		}
		elseif(!is_array($this->data['records']) && $this->data['records'] != "")
		{
			if($this->list_pref[$this->mode."_showempty"])
			{
//				$this->row['heading'] = $this->data['records'];
				$this->shortcodes->row['heading'] = $this->data['records'];
				//echo "parse: ".$area."<br />";
				$text .= $this->parseTemplate($area);
			}
		}
		//echo "parse: ".$area."_END<br />";
		$text .= $this->parseTemplate($area.'_END');
		return $text;
	}

	/**
	 * load_elist loads and checks all e_list.php files
	 *
	 * @return array
	 *
	 */
	function load_elist()
	{
		$listArray = '';

		//require is needed here instead of require_once, since both the menu and the page could be visible at the same time
		if(is_array($this->content_types) && in_array($this->settings['section'], $this->content_types))
		{
			$file = $this->content_name;
			if(is_readable(e_PLUGIN.$file."/e_list.php"))
			{
				$this->mode_content = $this->settings['section'];
				//echo "require: ".e_PLUGIN.$file."/e_list.php<br />";
				require_once(e_PLUGIN.$file."/e_list.php");
				$listArray = $this->load_data($file);
			}
		}
		else
		{
			$file = $this->settings['section'];
			if(in_array($file, $this->defaultArray))
			{
				//echo "require: ".$this->plugin_dir."section/list_".$file.".php<br />";
				require_once($this->plugin_dir."section/list_".$file.".php");
				$listArray = $this->load_data($file);
			}
			else
			{
				if (e107::isInstalled($file))
				{
					if(is_readable(e_PLUGIN.$file."/e_list.php"))
					{
						//echo "require: ".e_PLUGIN.$file."/e_list.php<br />";
						require_once(e_PLUGIN.$file."/e_list.php");
						$listArray = $this->load_data($file);
					}
				}
			}
		}
		return $listArray;
	}

	/**
	 * load_data calls the class from the e_list file and retrieves the data
	 *
	 * @param string $file the section to load (class name)
	 * @return array
	 *
	 */
	function load_data($file)
	{
		$name = "list_".$file;

		$listArray = '';

		//instantiate the class with this as parm
		if(!class_exists($name))
		{
			//echo "class $name doesn't exist<br />";
		}
		else
		{
			$class = new $name($this);
			//call method
			if(!method_exists($class, 'getListData'))
			{
				//echo "method getListData doesn't exist in class $class<br />";
			}
			else
			{
				$listArray = $class->getListData();
				if (e107::getPref('profanity_filter'))
				{
					$tp = e107::getParser();
					if (!is_object($parser->e_pf))
					{
					//	require_once(e_HANDLER.'profanity_filter.php');
						$parser->e_pf = new e_profanityFilter;
					}
					foreach ($listArray as $k => $v)
					{
						if (isset($v['heading']))
						{
							$listArray[$k]['heading'] = $tp->e_pf->filterProfanities($v['heading']);
						}
					}
				}
			}
		}
		return $listArray;
	}

	/**
	 * get datestamp last visit
	 *
	 * @return int datestamp
	 *
	 */
	function getlvisit()
	{
		global $qs;

		$lvisit = defined('USERLV') ? USERLV : time() + 1000;			// Set default value
		if(!empty($qs[0]) &&  $qs[0] === "new")
		{
			if(!empty($this->list_pref['new_page_timelapse']))
			{
				if(!empty($this->list_pref['new_page_timelapse_days']) && is_numeric($this->list_pref['new_page_timelapse_days']))
				{
					$days = $this->list_pref['new_page_timelapse_days'];
				}
				else
				{
					$days = "30";
				}
				if(isset($qs[1]) && is_numeric($qs[1]) && $qs[1] <= $days)
				{
					$lvisit = time()-$qs[1]*86400;
				}
			}
		}
		return $lvisit;
	}

	/**
	 * get bullet icon, either use the icon set in admin or the default theme bullet
	 *
	 * @param string $icon the icon to use as set in admin
	 * @return string $bullet
	 *
	 */
	function getBullet($icon)
	{
		$default_bullet = '';

		if($this->list_pref[$this->mode."_icon_default"])
		{
			if(defined('BULLET'))
			{
				$default_bullet = '<img src="'.THEME.'images/'.BULLET.'" alt="" class="icon" />';
			}
			elseif(file_exists(THEME.'images/bullet2.gif'))
			{
				$default_bullet = '<img src="'.THEME.'images/bullet2.gif" alt="" class="icon" />';
			}
		}

		$icon_width = '8';
		$icon_height = '8';
		$style_pre = '';

		if($this->list_pref[$this->mode."_icon_use"])
		{
			if($icon)
			{
				if(is_readable($this->plugin_dir."images/".$icon))
				{
					$bullet = "<img src='".$this->plugin_dir."images/".$icon."' alt='' />";
				}
			}
		}
		$bullet = vartrue($bullet, $default_bullet);

		return $bullet;
	}

	/**
	 * helper method, parse heading to specific length with postfix
	 *
	 * @param string $heading the heading from the item record
	 * @return string $heading the parsed heading
	 *
	 */
	function parse_heading($heading)
	{
		if($this->list_pref[$this->mode."_char_heading"] && strlen($heading) > $this->list_pref[$this->mode."_char_heading"])
		{
			$heading = substr($heading, 0, $this->list_pref[$this->mode."_char_heading"]).$this->list_pref[$this->mode."_char_postfix"];
		}
		return $heading;
	}

	/**
	 * helper method, format the date
	 *
	 * @param int $datestamp the datestamp of the item record
	 * @return string the formatted date
	 *
	 */
	function getListDate($datestamp)
	{
		$datestamp += TIMEOFFSET;

		$todayarray = getdate();
		$current_day = $todayarray['mday'];
		$current_month = $todayarray['mon'];
		$current_year = $todayarray['year'];

		$thisday = date("d", $datestamp);
		$thismonth = date("m", $datestamp);
		$thisyear = date("Y", $datestamp);

		//check and use the today date style if day is today
		if($thisyear == $current_year)
		{
			if($thismonth == $current_month)
			{
				if($thisday == $current_day)
				{
					$datepreftoday = $this->list_pref[$this->mode."_datestyletoday"];
					return strftime($datepreftoday, $datestamp);
				}
			}
		}

		//else use default date style
		$datepref = $this->list_pref[$this->mode."_datestyle"];
		return strftime($datepref, $datestamp);
	}

	/**
	 * display timelapse element (on newpage)
	 *
	 * @return string the timelapse element
	 *
	 */
	function displayTimelapse()
	{
		global $rs; //FIXME $frm

		if(isset($this->list_pref['new_page_timelapse']) && $this->list_pref['new_page_timelapse'])
		{
			if(isset($this->list_pref['new_page_timelapse_days']) && is_numeric($this->list_pref['new_page_timelapse_days']))
			{
				$days = $this->list_pref['new_page_timelapse_days'];
			}
			else
			{
				$days = '30';
			}
			$timelapse = 0;
			if(isset($qs[1]) && is_numeric($qs[1]) && $qs[1] <= $days)
			{
				$timelapse = $qs[1];
			}
			$url = $this->plugin_dir."list.php?new";
			$selectjs = "onchange=\"if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }\"";

			$this->row['timelapse'] = LIST_MENU_6;
			$this->row['timelapse'] .= $rs->form_select_open("timelapse", $selectjs).$rs->form_option(LIST_MENU_5, 0, $url);
			for($a=1; $a<=$days; $a++)
			{
				$this->row['timelapse'] .= $rs->form_option($a, ($timelapse == $a ? '1' : '0'), $url.".".$a);
			}
			$this->row['timelapse'] .= $rs->form_select_close();

			return $this->parseTemplate('TIMELAPSE_TABLE');
		}
		return;
	}

	/**
	 * display the page (either recent or new)
	 *
	 * @return string
	 *
	 */
	function displayPage()
	{
		global $qs;

		//get preferences
		if(!isset($this->list_pref))
		{
			$this->list_pref = $this->getListPrefs();
			$this->shortcodes->list_pref = $this->list_pref;
		}

		//get sections
		$this->sections = $this->prepareSection($this->mode);
		$arr = $this->prepareSectionArray($this->mode);

		//timelapse
		if(vartrue($qs[0]) == "new")
		{
			$text .= $this->displayTimelapse();
		}

		$text .= $this->parseTemplate('COL_START');

		//welcometext
		if($this->list_pref[$this->mode."_welcometext"])
		{
			$text .= $this->parseTemplate('COL_WELCOME');
		}

		//display the sections
		$k=0;

	//	print_a($arr);

		foreach($arr as $sect)
		{

			$this->shortcodes->plugin = $sect['section'];

			if($sect['display'] == '1')
			{
				$sectiontext = $this->displaySection($sect);
				if($sectiontext != '')
				{
					$v = $k/$this->list_pref[$this->mode."_colomn"];
					if( intval($v) == $v )
					{
						$text .= $this->parseTemplate('COL_ROWSWITCH');
					}
					$text .= $this->parseTemplate('COL_CELL_START');
					$text .= $sectiontext;
					$text .= $this->parseTemplate('COL_CELL_END');
					$k++;
				}
			}
		}
		$text .= $this->parseTemplate('COL_END');
		return $text;
	}

	/**
	 * display the menu (either recent or new)
	 *
	 * @return string
	 *
	 */
	function displayMenu()
	{
		//get preferences
		if(!isset($this->list_pref))
		{
			$this->list_pref = $this->getListPrefs();
			$this->shortcodes->list_pref = $this->list_pref;
		}

		//get sections
		$this->sections = $this->prepareSection($this->mode);
		$arr = $this->prepareSectionArray($this->mode);
		

		//display the sections
		$text = '';
		foreach($arr as $sect)
		{
			if($sect['display'] == '1')
			{
				$sectiontext = $this->displaySection($sect);
				if($sectiontext != '')
				{
					$text .= $sectiontext;
				}
			}
		}
		return $text;
	}
}

?>
