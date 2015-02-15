<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * 
 * Tagwords Class
 *
*/

if(!defined("TAG_TEXTAREA_COLS")){ define("TAG_TEXTAREA_COLS", "70"); }
if(!defined("TAG_TEXTAREA_ROWS")){ define("TAG_TEXTAREA_ROWS", "4"); }

//##### explanation of present $_GET's ----------------------------------------
// $_GET['type']	: the type of tag to show (cloud, list)
// $_GET['sort']	: the sort method for the cloud/list (alpha, freq)
// $_GET['area']	: the area to show cloud/list for (eg news, content)
// $_GET['q']		: the search query to show results for

class tagwords
{
	var $tagwords = array();
	var $mapper = array();
	var $core = array('news', 'page');
	var $table = 'tagwords';
	var $pref;
	var $template;
	var $shortcodes;

	/*
	* constructor (include all e_tagwords.php files)
	*/
	function __construct()
	{
		global $pref, $TEMPLATE_TAGWORDS, $tagwords_shortcodes;

		$this->e107 = e107::getInstance();

		//language
		e107::includeLan(e_PLUGIN."tagwords/languages/".e_LANGUAGE.".php");

		//shortcodes
		//require_once(e_PLUGIN.'tagwords/tagwords_shortcodes.php');
		$this->shortcodes = e107::getScBatch('tagwords',TRUE);
		//$this->shortcodes = $tagwords_shortcodes;

		//template
		if (is_readable(THEME."tagwords_template.php"))
		{
			require_once(THEME."tagwords_template.php");
			}
			else
			{
			require_once(e_PLUGIN."tagwords/tagwords_template.php");
		}
		$this->template = $TEMPLATE_TAGWORDS;

		$this->pref = $this->get_prefs();

		//load plugin and core tagword areas
		$this->loadPlugin();
		$this->loadCore();

		//prepare and assign key'ed tagwords data
		$this->mapper();
	}

	/*
	* get all plugin classes
	*/
	function loadPlugin()
	{
		global $pref;
		$list = e107::getPref('e_tagwords_list');
		if($list && is_array($list))
		{
			foreach($list as $e_tag)
			{
				if(is_readable(e_PLUGIN.$e_tag."/e_tagwords.php"))
				{
					require_once(e_PLUGIN.$e_tag."/e_tagwords.php");
					$name = "e_tagwords_{$e_tag}";
					if(class_exists($name))
					{
						if(!is_object($name))
						{
							$this->$name = new $name;
						}
						$this->tagwords[] = $e_tag;
					}
				}
			}
		}
	}

	/*
	* get all core classes
	*/
	function loadCore()
	{
		foreach($this->core as $s)
		{
			require_once(e_PLUGIN."tagwords/section/e_tagwords_{$s}.php");
			$name = "e_tagwords_{$s}";
			if(class_exists($name))
			{
				if(!is_object($name))
				{
					$this->$name = new $name;
				}
				$this->tagwords[] = $s;
			}
		}
	}

	/*
	* map plugin names to tables names
	*/
	function mapper()
	{
		$ret = array();
		foreach($this->tagwords as $area)
		{
			$ins = "e_tagwords_{$area}";
			$table = $this->$ins->settings['table'];
			$ret[$table] = $area;
		}
		$this->mapper = $ret;
	}

	/*
	* display the form element to provide tag words
	* @param string $tag_type type
	* @param string $tag_itemid itemid
	* @return array $caption, $text form element
	*/
	function tagwords_form($tag_type='', $tag_itemid='')
	{
		$tp = e107::getParser();
		$allowed = $this->getAllowedAreas();
		if(count($allowed)==0)
		{
			return;
		}
		else
		{
			if(!in_array($tag_type, $allowed))
			{
				return;
			}
		}

		$this->word = false;
		if( $ret = $this->getRecords($tag_type, $tag_itemid) )
		{
			$this->word = $tp->toForm($ret);
		}
		$caption = $tp->parseTemplate($this->template['caption'], true, $this->shortcodes);
		$text = $tp->parseTemplate($this->template['form'], true, $this->shortcodes);
		return array('caption'=>$caption, 'html'=>$text);
	}

	/*
	* retrieve all tag_words for the provided item (type+itemid)
	* @param string $tag_type type
	* @param string $tag_itemid itemid
	* @param boolean $returnwordsonly return comma seperated list of tagwords
	* @param boolean $link return hyperlink to taglist on words
	* @return array $ret array of tagwords
	*/
	function getRecords($tag_type='', $tag_itemid='', $returnwordsonly=false, $link=true)
	{
		$tp = e107::getParser();
		$sqlgr = new db;

		$qry = "
		SELECT tag_word FROM #".$this->table."
		WHERE tag_type='".$tp->toDB($tag_type)."'
		AND tag_itemid='".intval($tag_itemid)."'
		ORDER BY tag_word ";

		if($sqlgr->gen($qry))
		{
			$ret=array();
			while($row = $sqlgr->fetch())
			{
				$ret[] = $row['tag_word'];
			}
			if($returnwordsonly)
			{
				$arr = array();
				foreach($ret as $word)
				{
					$word = $tp->toHTML($word,true,'no_hook, emotes_off');
					$arr[] = ($link ? trim($this->createTagWordLink($word)) : $word);
				}
				return implode($this->pref['tagwords_word_seperator'], $arr);
			}
			//return a \n seperated list of tagwords for the specific item
			return implode($this->pref['tagwords_word_seperator'], $ret);
		}
		return;
	}

	/*
	* used on tagword search; get only those records that contain ALL tagwords that is searched on
	* @param array $array the array containing all results for each word seperately
	* @return array $combined array of combined tagwords
	*/
	function getUnique($array)
	{
		//combine all arrays
		$combined = array();
		foreach($array as $v)
		{
			foreach($v as $type=>$arr)
			{
				foreach($arr as $id)
				{
					if(!array_key_exists($type,$combined))
					{
						$combined[$type] = array();
					}
					if(!in_array($id, $combined[$type]))
					{
						$combined[$type][] = $id;
					}
				}
			}
		}
		//$combined is combined array of ALL values
		foreach($combined as $ntype=>$narr)
		{
			foreach($narr as $nid)
			{
				//$array is the array containing all results for each word seperately
				foreach($array as $ov)
				{
					//if an id value is not present in the result $array, remove it from the combined array
					if(!in_array($nid, $ov[$ntype]))
					{
						$key = array_search($nid, $combined[$ntype]);
						unset($combined[$ntype][$key]);
					}
				}
			}
		}
		//finally remove empty areas from the array
		foreach($combined as $ntype=>$narr)
		{
			sort($combined[$ntype]);
			if(empty($combined[$ntype]))
			{
				unset($combined[$ntype]);
			}

		}
		return $combined;
	}

	/*
	* create the tag word hyperlink
	* @param string $word word
	* @param string $class if present and int, adds the 'level' of the word to the css item
	* @return string $text hyperlink to tagword
	*/
	function createTagWordLink($word, $class='')
	{
		$word = trim($word);
		/*$qry = $tp->toDB($word);
		$qry = str_replace(' ', '+', $qry);*/
		$url = e107::getUrl()->create('tagwords/search', 'q='.$word);
		$word = e107::getParser()->toHTML($word,true,'no_hook, emotes_off');
		$class = ($class ? "class='tag".intval($class)."'" : "");
		return "<a href='".$url."' ".$class." title=''>".$word."</a>";
	}

	/*
	* db entry tagwords
	* @param string $tag_type type
	* @param string $tag_itemid itemid
	* @param string $tag_word word
	* @return string $text message
	*/
	function dbTagWords($tag_type='', $tag_itemid='', $tag_word='')
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		$mes = e107::getMessage();
		//prepare data
		$tag_type = $tp->toDB($tag_type);
		$tag_itemid = intval($tag_itemid);

		//get existing word records
		$existing_array = $this->getRecords($tag_type, $tag_itemid, false);
		$existing = explode($this->pref['tagwords_word_seperator'], $existing_array);

		//create array of new posted words
		$new = explode($this->pref['tagwords_word_seperator'], $tag_word);

		//delete the differences (delete what has been removed)
		$delete_diff = array_diff($existing, $new);
		foreach($delete_diff as $word)
		{
			$word = trim($word);
			$word = $tp->toDB($word);
			$sql->delete($this->table, "tag_type='".$tag_type."' AND tag_itemid='".$tag_itemid."' AND tag_word='".$word."'");
		}

		//insert the differences (insert what has been added)
		$insert_diff = array_diff($new, $existing);
		$count = 0;
		//print_a($insert_diff);
		//return ("Tagword Insert: ".print_a($new,true)); // debug info
		
		foreach($insert_diff as $word)
		{
			$word = trim($word);
			$word = $tp->toDB($word);
			$args = array();
			$args['tag_id'] = 0;
			$args['tag_type'] = $tag_type;
			$args['tag_itemid'] = $tag_itemid;
			$args['tag_word'] = $word;
			$count += $sql->insert($this->table, $args) ? 1 : 0;
			//return "a Diff was made";
		}
		
		return "<br />".LAN_TAG_3.": ".$count." words."; 
	}

	/*
	* db delete entries
	* @param string $tag_type type
	* @param string $tag_itemid itemid
	*/
	function dbDelete($tag_type='', $tag_itemid='')
	{
		e107::getDb()->delete($this->table, "tag_type='".$tag_type."' AND tag_itemid='".$tag_itemid."' ");
	}

	/*
	* retrieve all tag words (if $_GET['area'], only retrieve tagwords from that area (news, content, ...) )
	* @param string $tagarea tagarea
	* @return array $ret array of word=>number
	*/
	function getAllTagWords($tagarea='')
	{
		$sql = e107::getDb();
		$tag_type='';

		$allowed = $this->getAllowedAreas();
		if(count($allowed)>0)
		{
			$typeqry = " AND tag_type IN ('".implode("','", $this->getAllowedAreas())."') ";
		}

		if($tagarea!='menu')
		{
			//if user is able to manually set a area
			if(vartrue($this->pref['tagwords_view_area'])=='1')
			{
				foreach($this->tagwords as $area)
				{
					//limit data to only one area, if so, which area
					if(vartrue($_GET['area'])==$area)
					{
						$ins = "e_tagwords_{$area}";
						$typeqry = " AND tag_type='".$this->$ins->settings['table']."'";
						break;
					}
				}
			}
		}

		//menu additions
		$menuqry='';
		if($tagarea=='menu')
		{
			if($this->pref['tagwords_menu_min'])
			{
				$menuqry = "ORDER BY number DESC LIMIT 0,".$this->pref['tagwords_menu_min'];
			}
		}

		$qry = "
		SELECT tag_id, tag_itemid, tag_type, tag_word, COUNT(tag_word) as number
		FROM #".$this->table."
		WHERE tag_word!='' ".$typeqry."
		GROUP BY tag_word HAVING COUNT(tag_word)>=".intval($this->pref['tagwords_min'])." ".$menuqry." ";

		if($sql->gen($qry))
		{
			$ret=array();
			while($row = $sql->fetch())
			{
				$word = trim($row['tag_word']);
				$word = e107::getParser()->toHTML($word,true,'no_hook, emotes_off');
				$ret[$word] = $row['number'];
			}
			return $ret;
		}
		return;
	}

	/*
	* Get allowed areas from pref
	* @return $array allowed areas
	*/
	function getAllowedAreas()
	{
		$this->allowed_areas = array();
		foreach($this->tagwords as $area)
		{
			if(array_key_exists($area,$this->pref['tagwords_activeareas']))
			{
				$ins = "e_tagwords_{$area}";
				$this->allowed_areas[] = $this->$ins->settings['table'];
			}
		}
		return $this->allowed_areas;
	}

	/*
	* Sort data array of tag words
	* @param string $array the array of words
	* @param string $tagarea the area (menu,page)
	* @return $array sorted array
	*/
	function TagSort($array, $tagarea='')
	{
		//MENU
		if($tagarea=='menu')
		{
			if(vartrue($this->pref['tagwords_menu_default_sort'])==1)
			{
				//alphabetically
				ksort($array, SORT_STRING);
			}
			else
			{
				//by size
				arsort($array);
			}
			return $array;
		}

		//PAGE
		//user can set own sort
		if(vartrue($this->pref['tagwords_view_sort'])==1)
		{
			$s = varset($_GET['sort'],'');
			switch($s)
			{
				//if user has set sort, and is set to alpha
				case 'alpha':
					ksort($array, SORT_STRING);
					break;
				//if user has set sort, and is set to by size
				case 'freq':
					arsort($array);
					break;
				default:
					if(vartrue($this->pref['tagwords_default_sort'])==1)
					{
						//alphabetically
						ksort($array, SORT_STRING);
					}
					else
					{
						//by size
						arsort($array);
					}
					break;
			}

		//user cannot set sort, so use default sort value
		}
		else
		{
			if(vartrue($this->pref['tagwords_default_sort'])==1)
			{
				//alphabetically
				ksort($array, SORT_STRING);
			}
			else
			{
				//by size
				arsort($array);
			}
		}
		return $array;
	}

	/*
	* Render Message
	* @param string $message message
	* @param string $caption caption
	* @return string $text (ns,echo,return)
	*/
	function show_message($message, $caption='', $type='ns')
	{
		switch($type)
		{
			case 'echo':
				echo "<div style='text-align:center'><b>".$message."</b></div>";
				break;
			case 'return':
				return "<div style='text-align:center'><b>".$message."</b></div>";
				break;
			case 'ns':
			default:
				e107::getRender()->tablerender($caption, "<div style='text-align:center'><b>".$message."</b></div>");
				break;
		}
	}

	//##### show tagcloud/taglist -------------------------------------------------
	/*
	* Show tagcloud/taglist
	* @return tablerender tagcloud/taglist
	*/
	function TagRender()
	{
		$tp = e107::getParser();
		$type = false;

		//decide whether to show the taglist or the tagcloud

		//user can set own tag style
		if(vartrue($this->pref['tagwords_view_style'])=='1')
		{
			$t = varset($_GET['type'],'');
			switch($t)
			{
				case 'cloud':
					$type='cloud';
					break;
				case 'list':
					$type='list';
					break;
				default:
					$type = (vartrue($this->pref['tagwords_default_style'])=='1' ? 'cloud' : 'list');
					break;
			}
		}
		else
		{
			//user cannot set tag style, so use default tag style value
			$type = (vartrue($this->pref['tagwords_default_style'])=='1' ? 'cloud' : 'list');
		}

		//show the taglist or tagcloud
		if($type=='list')
		{
			$text = $tp->parseTemplate($this->template['cloudlist'], true, $this->shortcodes);
			e107::getRender()->tablerender(LAN_TAG_17, $text);
		}
		else
		{
			$text = $tp->parseTemplate($this->template['cloud'], true, $this->shortcodes);
			e107::getRender()->tablerender(LAN_TAG_16, $text);
		}
		return;
	}

	/*
	* retrieve all records based on tag word
	* @param string $word word
	* @param string $caption caption
	* @return array $type=>$id
	*/
	function getRecordsByTag($word='')
	{
		$sql = e107::getDb();
		$typeqry = '';
		$allowed = $this->getAllowedAreas();
		if(count($allowed)>0)
		{
			$typeqry = " AND tag_type IN ('".implode("','", $allowed)."') ";
		}
		if($sql->gen("SELECT tag_type, tag_itemid FROM #".$this->table." WHERE tag_word REGEXP('".e107::getParser()->toDB($word)."') ".$typeqry." "))
		{
			$ret = array();
			while($row = $sql->fetch())
			{
				$ret[$row['tag_type']][] = $row['tag_itemid'];
			}
			return $ret;
		}
		return;
	}

	/*
	* show list of related content based on the word (=$_GET['q'])
	* @param string tablerender
	*/
	function TagSearchResults()
	{
		$tp = e107::getParser();
		global $row;

		//the full search query + every single word in the query will be used to search
		$_GET['q'] = str_replace('+', ' ', $_GET['q']);

		$words=array();

		//add each seperate word to search
		$words = explode(" ", trim($_GET['q']));
		if(count($words)>1)
		{
			//add full query to search
			array_unshift($words, $_GET['q']);
		}

		$results=array();
		foreach($words as $w)
		{
			if($w!='')
			{
				if($t = $this->getRecordsByTag($w))
				{
					$results[] = $t;
				}
			}
		}

		//only combine the array if searching on multiple words
		//else the array is always the [0] key
		if(count($words)==1)
		{
			$records = $results[0];
			ksort($records, SORT_STRING);
		}
		elseif(count($words) > 1)
		{
			$records = $this->getUnique($results);
			ksort($records, SORT_STRING);
		}

		$this->num = count($records, 1) - count($records);

		$text = $tp->parseTemplate($this->template['intro'], true, $this->shortcodes);

		foreach($records as $type=>$ids)
		{
			if(array_key_exists($type, $this->mapper))
			{
				$pluginName = $this->mapper[$type];
				$ins = "e_tagwords_{$pluginName}";
				$this->area = $this->$ins;

				//area (news, content, ...)
				$text .= $tp->parseTemplate($this->template['area'], true, $this->shortcodes);

				//records for found related tagword
				$text .= $this->template['link_start'];
				foreach($ids as $id)
				{
					$this->id = $id;
					if(method_exists($this->area, 'getRecord'))
					{
						$this->area->getRecord($this->id);
						$text .= $tp->parseTemplate($this->template['link_item'], true, $this->shortcodes);
					}
				}
				$text .= $this->template['link_end'];
			}
		}
		e107::getRender()->tablerender(LAN_TAG_1, $text);

		return;
	}

	/*
	* create a tag cloud
	* @return string $text
	*/
	function TagCloud($mode='')
	{
		if($mode=='menu')
		{
			$t_start = $this->template['menu_cloud_start'];
			$t_item = $this->template['menu_cloud_item'];
			$t_end = $this->template['menu_cloud_end'];
		}
		else
		{
			$t_start = $this->template['cloud_start'];
			$t_item = $this->template['cloud_item'];
			$t_end = $this->template['cloud_end'];
		}

		$tags = $this->getAllTagWords($mode);
		$tags = $this->TagSort($tags, $mode);

		if(!is_array($tags))
		{
			return $this->show_message(LAN_TAG_18, '', 'return');
		}

		// tags will be displayed between class 1 to class 5
		$min_size = 1;
		$max_size = 5;

		// get the largest and smallest array values
		$min_qty = min(array_values($tags));
		$max_qty = max(array_values($tags));

		// find the range of values
		$spread = $max_qty - $min_qty;
		// we don't want to divide by zero
		if(0 == $spread)
		{
			$spread = 1;
		}

		// determine the increment, this is the increase per tag quantity (times used)
		$step = ($max_size - $min_size)/($spread);

		$text = $t_start;
		// loop through our tag array
		foreach ($tags as $key => $value)
		{
			$class = ceil($min_size + (($value - $min_qty) * $step));
			$this->word = $this->createTagWordLink($key, $class);
			$this->number = $value;
			$text .= e107::getParser()->parseTemplate($t_item, true, $this->shortcodes);
		}
		$text .= $t_end;
		return $text;
	}

	/*
	* create a tag list
	* @return string $text tagcloudlist
	*/
	function TagCloudList()
	{
		$tags = $this->getAllTagWords();
		$tags = $this->TagSort($tags);
		if(!is_array($tags))
		{
			return $this->show_message(LAN_TAG_18, '', 'return');
		}

		$text = $this->template['cloudlist_start'];
		foreach($tags as $key => $value)
		{
			$this->word = $this->createTagWordLink($key);
			$this->number = $value;
			$text .= e107::getParser()->parseTemplate($this->template['cloudlist_item'], true, $this->shortcodes);
		}
		$text .= $this->template['cloudlist_end'];
		return $text;
	}

//##### ADMIN

	/*
	* default preferences
	*/
	function default_prefs()
	{
		$ret=array();
		$ret['tagwords_min'] = 1;
		$ret['tagwords_class'] = 0;
		$ret['tagwords_default_sort'] = 1;
		$ret['tagwords_default_style'] = 1;
		$ret['tagwords_view_sort'] = 1;
		$ret['tagwords_view_style'] = 1;
		$ret['tagwords_view_area'] = 1;
		$ret['tagwords_view_search'] = 1;
		$ret['tagwords_view_freq'] = 1;
		$ret['tagwords_word_seperator'] = ' / ';

		$ret['tagwords_menu_caption'] = 'LAN_TAG_MENU_2';
		$ret['tagwords_menu_min'] = 5;
		$ret['tagwords_menu_default_sort'] = 1;
		$ret['tagwords_menu_view_search'] = 1;
		$ret['tagwords_menu_view_freq'] = 0;

		//activate core areas by default
		$ret['tagwords_activeareas'] = array();
		foreach($this->core as $area)
		{
			$ret['tagwords_activeareas'][$area] = 1;
		}

		return $ret;
	}

	/*
	* get preferences from db
	*/
	function get_prefs()
	{
		$sql = e107::getDb();
		$num_rows = $sql->gen("SELECT * FROM #core WHERE e107_name='".$this->table."' ");
		if($num_rows == 0)
		{
			$p = $this->default_prefs();
			$tmp = $this->e107->arrayStorage->WriteArray($p);
			$sql->insert("core", "'".$this->table."', '{$tmp}' ");
			$sql->gen("SELECT * FROM #core WHERE e107_name='".$this->table."' ");
		}
		$row = $sql->fetch();
		$p = e107::unserialize($row['e107_value']);

		//validation
		if(!array_key_exists('tagwords_activeareas', $p))
		{
			$p['tagwords_activeareas'] = array();
		}

		return $p;
	}

	/*
	* Update preferences
	*/
	function update_prefs()
	{
		$sql = e107::getDb();
		$num_rows = $sql->gen("SELECT * FROM #core WHERE e107_name='".$this->table."' ");
		if ($num_rows == 0)
		{
			$p = $this->default_prefs();
			$tmp = $this->e107->arrayStorage->WriteArray($p);
			$sql->insert("core", "'".$this->table."', '{$tmp}' ");
		}
		else
		{
			$row = $sql->fetch();

			//assign new preferences
			foreach($_POST as $k => $v)
			{
				if(preg_match("#^tagwords_#",$k))
				{
					$tagwords_pref[$k] = e107::getParser()->toDB($v);
				}
			}
			$this->pref = $tagwords_pref;

			//create new array of preferences
			$tmp = $this->e107->arrayStorage->WriteArray($tagwords_pref);

			$sql->update("core", "e107_value = '{$tmp}' WHERE e107_name = '".$this->table."' ");
		}
		return;
	}

	/*
	* Validate tagword db, removes all non-existing records
	* in case a db entry has been deleted but the tagwords were not
	*/
	function validate()
	{
		$sql = e107::getDb();
		global $sql2;

		if($sql->gen("SELECT * FROM #".$this->table." GROUP BY tag_type, tag_itemid ORDER BY tag_id"))
		{
			while($row= $sql->fetch())
			{
				if(array_key_exists($row['tag_type'], $this->mapper))
				{
					$name = "e_tagwords_{$this->mapper[$row['tag_type']]}";
					if(!$sql2->gen("SELECT * FROM #".$row['tag_type']." WHERE ".$this->$name->settings['db_id']." = '".$row['tag_itemid']."' "))
					{
						$sql2->delete($this->table, "tag_type='".$row['tag_type']."' AND tag_itemid='".$row['tag_itemid']."' ");
					}
				}
			}
		}
	}

	/*
	* Options
	* @return string tablerender
	*/
	function tagwords_options()
	{
		$this->validate();

		$text = e107::getParser()->parseTemplate($this->template['admin_options'], true, $this->shortcodes);
		e107::getRender()->tablerender(LAN_TAG_OPT_1, $text);
	}

} //end class

?>
