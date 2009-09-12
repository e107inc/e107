<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/news_class.php,v $
 * $Revision: 1.20 $
 * $Date: 2009-09-12 18:25:41 $
 * $Author: secretr $
*/

if (!defined('e107_INIT')) { exit; }
require_once(e_HANDLER.'model_class.php');

class e_news_item extends e_model 
{
	protected $_loaded_once = false;
	
	/**
	 * Shortcodes - simple field getter (basic formatting)
	 * THIS IS ONLY TEST, maybe useful for fields requiring simple formatting - it's a way too complicated for designers,
	 * could be inner used inside the rest of news SCs.
	 *
	 * @param string $news_field name without the leading 'news_' prefix
	 * @param mixed $default
	 * @return string field value
	 */
	public function sc_news_field($parm = '')
	{
		$tmp = explode('|', $parm, 2);
		$field = $tmp[0];
		
		if(!is_array($parm))
		{
			parse_str(varset($tmp[1]), $parm);
		}
		$val = $this->get($field, '');
		
		//do more with $parm array, just an example here
		if(varsettrue($parm['format']))
		{
			switch ($parm['format'])
			{
				//USAGE: {NEWS_FIELD=body|format=html&arg=1,BODY} -> callback e107->toHTML($value, true, 'BODY');
				case 'html': 
					$method = 'toHTML';
					$callback = e107::getParser();
					$parm['arg'] = explode(',', varset($parm['arg']));
					$parm['arg'][0] = varsettrue($parm['arg'][0]) ? true : false; //to boolean
					$params = array($val); //value is always the first callback argument
					$params += $parm['arg'];
				break;
				
				//USAGE: {NEWS_FIELD=body|format=html_truncate&arg=200,...} -> callback e107->html_truncate($value, 200, '...');
				case 'html_truncate':
					$val = e107::getParser()->toHTML($val, true);
					
				//USAGE: {NEWS_FIELD=title|format=text_truncate&arg=100,...} -> callback e107->text_truncate($value, 200, '...');
				case 'text_truncate':
					$method = $parm['format'];
					$callback = e107::getParser();
					$params = array($val); //value is always the first callback argument
					$params = array_merge($params, explode(',', $parm['arg']));
				break;
				
				//USAGE: {NEWS_FIELD=title|format=date} -> strftime($pref[shortdate], $value);
				//USAGE: {NEWS_FIELD=title|format=date&arg=%Y} -> strftime('%Y', $value);
				case 'date':
					$method = $parm['format'];
					$callback = e107::getParser();
					$params = array($val); //value is always the first callback argument
					$params = array_merge($params, explode(',', $parm['arg']));
					//should be done with date handler (awaiting for modifications)
					return strftime(varset($parm['arg'], e107::getPref('shortdate')), $val);
				break;
				
				default:
					return $val;
				break;
					
			}
			return call_user_func_array(array($callback, $method), $params);
		}

		return $val;
	}
	
	/**
	 * Shorthand getter for news fields
	 *
	 * @param string $news_field name without the leading 'news_' prefix
	 * @param mixed $default
	 * @return mixed data
	 */
	public function get($news_field, $default = null)
	{
		return parent::get('news_'.$news_field, $default);
	}
	
	/**
	 * Load news item by id
	 *
	 * @param integer $id
	 * @param boolean $force
	 * @return e_news_item
	 */
	public function load($id, $force = false)
	{
		if($force || !$this->_loaded_once)
		{
			$id = intval($id);
			$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
			
		  	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			WHERE n.news_id={$id} AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
			AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")";
			
			if(e107::getDb()->db_Select_gen($query))
			{
				$this->setData(e107::getDb()->db_Fetch());
			}
			$this->_loaded_once = true;
		}
		return $this;
	}
}

class e_news_tree extends e_model 
{
	/**
	 * Current tree news category id
	 *
	 * @var integer
	 */
	protected $_current_category_id;
	
	/**
	 * @var array
	 */
	protected $_tree_db_total = array();
	
	/**
	 * Constructor
	 *
	 * @param unknown_type $category_id
	 */
	public function __construct($category_id = null)
	{
		if(null !== $category_id)
		{
			$this->load($category_id);
		}
	}
	
	/**
	 * Set current category Id
	 *
	 * @param integer $category_id
	 * @return e_news_tree
	 */
	function setCurrentCategoryId($category_id)
	{
		$this->_current_category_id = intval($category_id);
		return $this;
	}
	
	/**
	 * Get news item object from the tree
	 * Preparing for future news SEF string (string $name)
	 * 
	 * @param string|integer $name
	 * @param integer $category_id optional category Id
	 * @return e_news_item
	 */
	function getNode($name, $category_id = null)
	{
		if(null === $category_id)
		{
			$category_id = $this->_current_category_id;
		}
		return $this->getData('__tree/'.$category_id.'/'.$name);
	}
	
	/**
	 * Set news item object
	 *
	 * @param string|integer $name
	 * @param array $data
	 * @param integer $category_id optional category Id
	 * @return e_news_tree
	 */
	function setNode($name, $data, $category_id = null)
	{
		if(null === $category_id)
		{
			$category_id = $this->_current_category_id;
		}
		$this->setData('__tree/'.$category_id.'/'.$name, new e_news_item($data));
		return $this;
	}
	
	/**
	 * Set new category tree
	 *
	 * @param array $tree
	 * @param integer $category_id
	 * @return e_news_tree
	 */
	public function setTree(array $tree, $category_id = null)
	{
		if(null === $category_id)
		{
			$category_id = $this->_current_category_id;
		}
		$this->setData('__tree/'.$category_id, $tree);
		return $this;
	}
	
	/**
	 * Get tree by category id
	 *
	 * @param integer $category_id
	 * @return array
	 */
	public function getTree($category_id = null)
	{
		if(null === $category_id)
		{
			$category_id = $this->_current_category_id;
		}
		return $this->getData('__tree/'.$category_id);
	}
	
	/**
	 * Total records found (DB)
	 * @param integer $category_id [optional]
	 * @return integer
	 */
	function getTreeTotal($category_id = null)
	{
		if(null === $category_id)
		{
			$category_id = $this->_current_category_id;
		}
		return (isset($this->_tree_db_total[$category_id]) ? $this->_tree_db_total[$category_id] : 0);
	}
	
	/**
	 * Load tree by category id
	 *
	 * @param integer $category_id
	 * @param boolean $force
	 * @param array $qry_data limit_from, limit_to, order, date [YYYYMMDD], day[DD], month [MM]
	 * @return e_news_tree
	 */
	public function load($category_id = 0, $force = false, $qry_data = array())
	{
		$category_id = intval($category_id);
		if(is_string($qry_data)) { parse_str($qry_data, $qry_data); }

		$limit_from = varset($qry_data['limit_from'], 0);
		$limit_to = varset($qry_data['limit_to'], e107::getPref('newspost', 15)); 
		$order = varset($qry_data['order'], 'n.news_sticky DESC, n.news_datestamp DESC');
		
		$this->setCurrentCategoryId($category_id);
		
		//TODO - file cache $cacheString = md5($category_id.$limit_from.$order.e_CLASS_REGEXP);
		
		if($force || !$this->isTree())
		{
			var_dump('nocache');
			$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
			if($category_id)
			{
				$where = ' news_category='.$category_id.' AND';
			}
			$query = "SELECT  SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, nc.category_name, nc.category_icon FROM #news AS n
				LEFT JOIN #user AS u ON n.news_author = u.user_id
				LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
				WHERE{$where} n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")
				AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
				ORDER BY ".e107::getParser()->toDB($order)." LIMIT ".intval($limit_from).",".intval($limit_to);
				
			$tree = array();
			if(e107::getDb()->db_Select_gen($query))
			{
				$this->_tree_db_total[$category_id] = (integer) e107::getDb()->total_results;
				
				while (true)
				{
					$row = e107::getDb()->db_Fetch();
					if(!$row)
					{
						break;
					}
					$tree[$row['news_id']] = new e_news_item($row);
				}
			}
			$this->setTree($tree);
			
		}
		
		return $this;
	}
	
	function isTree($category_id = null)
	{
		if(null === $category_id)
		{
			$category_id = $this->_current_category_id;
		}
		
		return $this->isData('__tree/'.$category_id);
	}
	
	function isNode($name, $category_id = null)
	{
		if(null === $category_id)
		{
			$category_id = $this->_current_category_id;
		}
		return $this->isData('__tree/'.$category_id.'/'.$name);
	}
	
	function hasNode($name, $category_id = null)
	{
		if(null === $category_id)
		{
			$category_id = $this->_current_category_id;
		}
		return $this->hasData('__tree/'.$category_id.'/'.$name);
	}
}

class news {

	//FIXME - LANs
	//TODO - synch WIKI docs, add rewrite data to the event data
	function submit_item($news, $smessages = false)
	{
		global $e107cache, $e_event, $pref, $admin_log;
		
		$tp = e107::getParser();
		$sql = e107::getDb();
		
		require_once (e_HANDLER."message_handler.php");
		$emessage = eMessage::getInstance();
		
		$error = false;
		if(empty($news['news_title']))
		{
			$error = true;
			$emessage->add('Validation error: News title can\'t be empty!', E_MESSAGE_ERROR, $smessages);
		}
		
		if(empty($news['news_category']))
		{
			$error = true;
			$emessage->add('Validation error: News category can\'t be empty!', E_MESSAGE_ERROR, $smessages);
		}
		

		$data = array();
		//DB Array
		$data['data']['news_title'] = $news['news_title'];
		$data['_FIELD_TYPES']['news_title'] = 'todb';
		
		$data['data']['news_body'] = $news['news_body'];
		$data['_FIELD_TYPES']['news_body'] = 'todb';
		
		$data['data']['news_extended'] = $news['news_extended'];
		$data['_FIELD_TYPES']['news_extended'] = 'todb';
		
		$data['data']['news_datestamp'] = $news['news_datestamp'];
		$data['_FIELD_TYPES']['news_datestamp'] = 'int';
		
		$data['data']['news_author'] = $news['news_author'] ? $news['news_author'] : USERID;
		$data['_FIELD_TYPES']['news_author'] = 'int';
		
		$data['data']['news_category'] = $news['news_category'];
		$data['_FIELD_TYPES']['news_category'] = 'int';
		
		$data['data']['news_allow_comments'] = $news['news_allow_comments'];
		$data['_FIELD_TYPES']['news_allow_comments'] = 'int';
		
		$data['data']['news_start'] = $news['news_start'];
		$data['_FIELD_TYPES']['news_start'] = 'int';
		
		$data['data']['news_end'] = $news['news_end'];
		$data['_FIELD_TYPES']['news_end'] = 'int';
		
		$data['data']['news_class'] = $news['news_class'];
		$data['_FIELD_TYPES']['news_class'] = 'todb';
		
		$data['data']['news_render_type'] = $news['news_render_type'];
		$data['_FIELD_TYPES']['news_render_type'] = 'int';
		
		$data['data']['news_render_type'] = $news['news_render_type'];
		$data['_FIELD_TYPES']['news_render_type'] = 'int';
		
		//news_comment_total
		
		$data['data']['news_summary'] = $news['news_summary'];
		$data['_FIELD_TYPES']['news_summary'] = 'todb';
		
		$data['data']['news_thumbnail'] = $news['news_thumbnail'];
		$data['_FIELD_TYPES']['news_thumbnail'] = 'todb';
		
		$data['data']['news_sticky'] = $news['news_sticky'];
		$data['_FIELD_TYPES']['news_sticky'] = 'int';
		
		$data['data']['news_summary'] = $news['news_summary'];
		$data['_FIELD_TYPES']['news_summary'] = 'todb';
		
		$data['data']['news_thumbnail'] = $news['news_thumbnail'];
		$data['_FIELD_TYPES']['news_thumbnail'] = 'todb';
		
		$data['data']['news_sticky'] = $news['news_sticky'];
		$data['_FIELD_TYPES']['news_sticky'] = 'int';
		
		$data['data']['news_meta_keywords'] = $news['news_meta_keywords'];
		$data['_FIELD_TYPES']['news_meta_keywords'] = 'todb';
		
		$data['data']['news_meta_description'] = strip_tags($tp->toHTML($news['news_meta_description'], true)); //handle bbcodes
		$data['_FIELD_TYPES']['news_meta_description'] = 'todb';
		
		$datarw = array();
		$datarw['data']['news_rewrite_id'] = intval($news['news_rewrite_id']);
		$datarw['_FIELD_TYPES']['news_rewrite_id'] = 'int';
		$datarw['data']['news_rewrite_string'] = trim($news['news_rewrite_string']);
		$datarw['_FIELD_TYPES']['news_rewrite_string'] = 'todb';
		$datarw['data']['news_rewrite_type'] = 1;
		$datarw['_FIELD_TYPES']['news_rewrite_type'] = 'int';
		
		if($error)
		{
			$data['error'] = true;
			return $data;
		}
		
		//XXX - Now hooks are executed only if no mysql error is found. Should it stay so?
		if ($news['news_id'])
		{	
			// Updating existing item
			$data['WHERE'] = 'news_id='.intval($news['news_id']);
			
			//$vals = "news_datestamp = '".intval($news['news_datestamp'])."', ".$author_insert." news_title='".$news['news_title']."', news_body='".$news['news_body']."', news_extended='".$news['news_extended']."', news_category='".intval($news['cat_id'])."', news_allow_comments='".intval($news['news_allow_comments'])."', news_start='".intval($news['news_start'])."', news_end='".intval($news['news_end'])."', news_class='".$tp->toDB($news['news_class'])."', news_render_type='".intval($news['news_rendertype'])."' , news_summary='".$news['news_summary']."', news_thumbnail='".$tp->toDB($news['news_thumbnail'])."', news_sticky='".intval($news['news_sticky'])."' WHERE news_id='".intval($news['news_id'])."' ";
			if ($sql->db_Update('news', $data))
			{
				e107::getAdminLog()->logArrayAll('NEWS_09', $data['data']);
				
				//manage rewrites
				$data['data']['news_id'] = $news['news_id'];
				if('error' === $this->handleRewriteSubmit('update', $data['data'], $datarw, $smessages))
				{
					$error = true;
				}
				
				e107::getEvent()->trigger('newsupd', $data['data']);
				$message = LAN_NEWS_21;
				$emessage->add(LAN_NEWS_21, E_MESSAGE_SUCCESS, $smessages);
				e107::getCache()->clear('news.php');
				

				
				//FIXME - triggerHook should return array(message, message_type)
				$evdata = array('method'=>'update', 'table'=>'news', 'id'=>$news['news_id'], 'plugin'=>'news', 'function'=>'submit_item');
				$emessage->add(e107::getEvent()->triggerHook($evdata), E_MESSAGE_INFO, $smessages);
			}
			else
			{
				if($sql->getLastErrorNumber())
				{
					$error = true;
					$emessage->add(LAN_NEWS_5, E_MESSAGE_ERROR, $smessages);
					$message = "<strong>".LAN_NEWS_5."</strong>";
				}
				else
				{
					$data['data']['news_id'] = $news['news_id'];
					$check = $this->handleRewriteSubmit('update', $data['data'], $datarw, $smessages);
					if ($check === true)
					{
						$message = LAN_NEWS_21;
						$emessage->add(LAN_NEWS_21, E_MESSAGE_SUCCESS, $smessages);
					}
					elseif ($check === 'error')
					{
						$error = true;
					}
					else
					{
						$emessage->add(LAN_NEWS_46, E_MESSAGE_INFO, $smessages);
						$message = "<strong>".LAN_NEWS_46."</strong>";
					}
					
					//FIXME - triggerHook should return array(message, message_type)
					$evdata = array('method'=>'update', 'table'=>'news', 'id'=>$news['news_id'], 'plugin'=>'news', 'function'=>'submit_item');
					$emessage->add(e107::getEvent()->triggerHook($evdata), E_MESSAGE_INFO, $smessages);
				}

			}
		}
		else
		{	
			// Adding item
			$data['data']['news_id'] = $sql->db_Insert('news', $data);
			$news['news_id'] = $data['data']['news_id'];
			//$news['news_id'] = $sql ->db_Insert('news', "0, '".$news['news_title']."', '".$news['news_body']."', '".$news['news_extended']."', ".intval($news['news_datestamp']).", ".intval($news['news_author']).", '".intval($news['cat_id'])."', '".intval($news['news_allow_comments'])."', '".intval($news['news_start'])."', '".intval($news['news_end'])."', '".$tp->toDB($news['news_class'])."', '".intval($news['news_rendertype'])."', '0' , '".$news['news_summary']."', '".$tp->toDB($news['news_thumbnail'])."', '".intval($news['news_sticky'])."' ")
			if ($data['data']['news_id'])
			{
				//
				$message = LAN_NEWS_6;
				$emessage->add(LAN_NEWS_6, E_MESSAGE_SUCCESS, $smessages);
				e107::getCache()->clear('news.php');
				
				//moved down - prevent wrong mysql_insert_id
				e107::getAdminLog()->logArrayAll('NEWS_08', $data['data']);
				
				//manage rewrites
				if('error' === $this->handleRewriteSubmit('insert', $data['data'], $datarw, $smessages))
				{
					$error = true;
				}
				
				e107::getEvent()->trigger('newspost', $data['data']);
				
				//XXX - triggetHook after trigger?
				$evdata = array('method'=>'create', 'table'=>'news', 'id'=>$data['data']['news_id'], 'plugin'=>'news', 'function'=>'submit_item');
				$emessage->add($e_event->triggerHook($evdata), E_MESSAGE_INFO, $smessages);
			}
			else
			{
				$error = true;
				$message = "<strong>".LAN_NEWS_7."</strong>";
				$emessage->add(LAN_NEWS_7, E_MESSAGE_ERROR, $smessages);
			}
		}

		/* FIXME - trackback should be hooked!	*/
		if($news['news_id'] && $pref['trackbackEnabled'])
		{
			
			$excerpt = e107::getParser()->text_truncate(strip_tags(e107::getParser()->post_toHTML($news['news_body'])), 100, '...');
			
//			$id=mysql_insert_id();
			$permLink = $e107->base_path."comment.php?comment.news.".intval($news['news_id']);

			require_once(e_PLUGIN."trackback/trackbackClass.php");
			$trackback = new trackbackClass();

			if($_POST['trackback_urls'])
			{
				$urlArray = explode("\n", $_POST['trackback_urls']);
				foreach($urlArray as $pingurl) 
				{
					if(!$terror = $trackback->sendTrackback($permLink, $pingurl, $news['news_title'], $excerpt))
					{
						$message .= "<br />successfully pinged {$pingurl}.";
						$emessage->add("Successfully pinged {$pingurl}.", E_MESSAGE_SUCCESS, $smessages);
					} 
					else 
					{
						$message .= "<br />was unable to ping {$pingurl}<br />[ Error message returned was : '{$terror}'. ]";
						$emessage->add("was unable to ping {$pingurl}<br />[ Error message returned was : '{$terror}'. ]", E_MESSAGE_ERROR, $smessages);
					}
				}
			}

			if(isset($_POST['pingback_urls']))
			{
				if ($urlArray = $trackback->getPingUrls($news['news_body'])) //FIXME - missing method!!!
				{
					foreach($urlArray as $pingurl)
					{

						if ($trackback -> sendTrackback($permLink, $pingurl, $news['news_title'], $excerpt))
						{
	 						$message .= "<br />successfully pinged {$pingurl}.";
	 						$emessage->add("Successfully pinged {$pingurl}.", E_MESSAGE_SUCCESS, $smessages);
						}
						else
						{
							$message .= "Pingback to {$pingurl} failed ...";
							$emessage->add("Pingback to {$pingurl} failed ...", E_MESSAGE_ERROR, $smessages);
						}
					}
				}
				else
				{
					$message .= "<br />No pingback addresses were discovered";
					$emessage->add("No pingback addresses were discovered", E_MESSAGE_INFO, $smessages);
				}
			}
		}

		/* end trackback */

		//return $message;
		$data['message'] = $message;
		$data['error'] = $error;
		return $data;
	}
	
	/**
	 * Manage SEF URL string for current news
	 * FIXME - news rewrites should go to different handler
	 * 
	 * @param string $action insert|update
	 * @param array $news_data XXX - could be changed to news_id only (integer)
	 * @param array $rewrite_data
	 * @param boolean $session_message [optional] default false
	 * @return mixed true|false for data has been[not] changed; 'error' for DB error
	 */
	function handleRewriteSubmit($action, $news_data, $rewrite_data, $session_message = false)
	{
		$rewrite_data['data']['news_rewrite_source'] = $news_data['news_id'];
		$rewrite_data['_FIELD_TYPES']['news_rewrite_source'] = 'int';
		
		//Delete if required
		if (empty($rewrite_data['data']['news_rewrite_string']))
		{
			if(e107::getDb()->db_Select('news_rewrite', 'news_rewrite_id,news_rewrite_string', 'news_rewrite_source='.intval($rewrite_data['data']['news_rewrite_source']).' AND news_rewrite_type='.intval($rewrite_data['data']['news_rewrite_type'])))
			{
				$check = e107::getDb()->db_Fetch();
				self::clearRewriteCache($check['news_rewrite_string']);
				e107::getDb()->db_Delete('news_rewrite', 'news_rewrite_id='.$check['news_rewrite_id']);
				return true;
			}
			
			return false;
		}

		switch($action)
		{
			case 'insert':
				$rewrite_data['data']['news_rewrite_id'] = 0;
				if($rewrite_data['data']['news_rewrite_id'] = e107::getDb()->db_Insert('news_rewrite', $rewrite_data))
				{
					self::setRewriteCache($rewrite_data['data']['news_rewrite_string'], $rewrite_data['data']);
					return true;
				}
				eMessage::getInstance()->add('Friendly URL string related problem detected!', E_MESSAGE_ERROR, $session_message);
				if(1062 == e107::getDb()->getLastErrorNumber()) //detect duplicate mysql errnum
				{
					eMessage::getInstance()->add('Friendly URL should be unique! ', E_MESSAGE_ERROR, $session_message);
				}
				eMessage::getInstance()->add('mySQL error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText(), E_MESSAGE_DEBUG, $session_message);
				return 'error';
			break;
		
			case 'update':
				$id = intval($rewrite_data['data']['news_rewrite_id']);
				unset($rewrite_data['data']['news_rewrite_id']); 
				if($id)
				{
					$rewrite_data['WHERE'] = 'news_rewrite_id='.$id;
					if(e107::getDb()->db_Update('news_rewrite', $rewrite_data))
					{
						$rewrite_data['data']['news_rewrite_id'] = $id;
						self::setRewriteCache($rewrite_data['data']['news_rewrite_string'], $rewrite_data['data']);
						return true;
					}
					elseif (e107::getDb()->getLastErrorNumber())
					{ 
						eMessage::getInstance()->add('Friendly URL string related problem detected!', E_MESSAGE_ERROR, $session_message);
						if(1062 == e107::getDb()->getLastErrorNumber()) //detect duplicate mysql errnum
						{
							eMessage::getInstance()->add('Friendly URL string should be unique! ', E_MESSAGE_ERROR, $session_message);
						}
						eMessage::getInstance()->add('mySQL error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText(), E_MESSAGE_DEBUG, $session_message);
						return 'error';
					}
					
					return false;
				}
				
				$rewrite_data['data']['news_rewrite_id'] = 0;
				if($rewrite_data['data']['news_rewrite_id'] = e107::getDb()->db_Insert('news_rewrite', $rewrite_data))
				{
					self::setRewriteCache($rewrite_data['data']['news_rewrite_string'], $rewrite_data['data']);
					return true;
				}
				
				eMessage::getInstance()->add('Friendly URL string related problem detected!', E_MESSAGE_ERROR, $session_message);
				if(1062 == e107::getDb()->getLastErrorNumber()) //detect duplicate mysql errnum
				{
					eMessage::getInstance()->add('Friendly URL string should be unique! ', E_MESSAGE_ERROR, $session_message);
				}
				eMessage::getInstance()->add('mySQL error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText(), E_MESSAGE_DEBUG, $session_message);
				return 'error';
			break;
		}
		
		return false;
	}
	
	public static function clearRewriteCache($sefstr = '')
	{
		if($sefstr) $sefstr = md5($sefstr);
		ecache::clear_sys("news_sefurl".$sefstr);
	}
	
	public static function setRewriteCache($sefstr, $data)
	{
		$sefstr = md5($sefstr); 
		if(is_array($data)) $data = e107::getArrayStorage()->WriteArray($data, false);
		ecache::set_sys("news_sefurl".$sefstr, $data, true);
	}

	function render_newsitem($news, $mode = 'default', $n_restrict = '', $NEWS_TEMPLATE = '', $param='')
	{
		global $e107, $tp, $sql, $override, $pref, $ns, $NEWSSTYLE, $NEWSLISTSTYLE, $news_shortcodes, $loop_uid;
		if ($override_newsitem = $override -> override_check('render_newsitem')) {
			$result = call_user_func($override_newsitem, $news, $mode, $n_restrict, $NEWS_TEMPLATE, $param);
			if ($result == 'return')
			{
				return;
			}
		}
		if (!is_object($e107->tp)) $e107->tp = new e_parse;

		if ($n_restrict == 'userclass')
		{
			$news['news_id'] = 0;
			$news['news_title'] = LAN_NEWS_1;
			$news['data'] = LAN_NEWS_2;
			$news['news_extended'] = "";
			$news['news_allow_comments'] = 1;
			$news['news_start'] = 0;
			$news['news_end'] = 0;
			$news['news_render_type'] = 0;
			$news['comment_total'] = 0;
		}

		if (!$param)
		{
			$param['caticon'] = ICONSTYLE;
			$param['commentoffstring'] = COMMENTOFFSTRING;
			$param['commentlink'] = COMMENTLINK;
			$param['trackbackstring'] = (defined("TRACKBACKSTRING") ? TRACKBACKSTRING : "");
			$param['trackbackbeforestring'] = (defined("TRACKBACKBEFORESTRING") ? TRACKBACKBEFORESTRING : "");
			$param['trackbackafterstring'] = (defined("TRACKBACKAFTERSTRING") ? TRACKBACKAFTERSTRING : "");
			$param['itemlink'] = (defined("NEWSLIST_ITEMLINK")) ? NEWSLIST_ITEMLINK : "";
			$param['thumbnail'] =(defined("NEWSLIST_THUMB")) ? NEWSLIST_THUMB : "border:0px";
			$param['catlink']  = (defined("NEWSLIST_CATLINK")) ? NEWSLIST_CATLINK : "";
			$param['caticon'] =  (defined("NEWSLIST_CATICON")) ? NEWSLIST_CATICON : ICONSTYLE;
		}

// Next three images aren't always defined by the caller, even if most of $param is.
		if (!isset($param['image_nonew_small']))
		{
		  if (!defined("IMAGE_nonew_small"))
		  {
			define("IMAGE_nonew_small", (file_exists(THEME."images/nonew_comments.png") ? "<img src='".THEME_ABS."images/nonew_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/nonew_comments.png' alt=''  />"));
		  }
		  $param['image_nonew_small'] = IMAGE_nonew_small;
		}

		if (!isset($param['image_new_small']))
		{
		  if (!defined("IMAGE_new_small"))
		  {
			define("IMAGE_new_small", (file_exists(THEME."images/new_comments.png") ? "<img src='".THEME_ABS."images/new_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/new_comments.png' alt=''  /> "));
		  }
		  $param['image_new_small'] = IMAGE_new_small;
		}

		if (!isset($param['image_sticky']))
		{
		  if (!defined("IMAGE_sticky"))
		  {
			define("IMAGE_sticky", (file_exists(THEME."images/sticky.png") ? "<img src='".THEME_ABS."images/sticky.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/sticky.png' alt='' style='width: 14px; height: 14px; vertical-align: bottom' /> "));
		  }
		  $param['image_sticky'] = IMAGE_sticky;
		}

		cachevars('current_news_item', $news);
		cachevars('current_news_param', $param);

		if ($news['news_render_type'] == 1 && $mode != "extend") {
			if (function_exists("news_list")) {
				$NEWS_PARSE = news_list($news);
			} else if ($NEWSLISTSTYLE) {
				$NEWS_PARSE = $NEWSLISTSTYLE;
			} else {
				$NEWS_PARSE = "{NEWSICON}&nbsp;<b>{NEWSTITLELINK}</b><div class='smalltext'>{NEWSAUTHOR} ".LAN_NEWS_100." {NEWSDATE} | {NEWSCOMMENTS}</div>";
			}
		} else {
			if ($NEWS_TEMPLATE) {
				$NEWS_PARSE = $NEWS_TEMPLATE;
			} else {
				if (function_exists("news_style")) {
					$NEWS_PARSE = news_style($news);
				} else {
					$NEWS_PARSE = $NEWSSTYLE;
				}
			}
		}
		$loop_uid = $news['news_author'];

		require_once(e_FILE.'shortcode/batch/news_shortcodes.php');
		setScVar('news_shortcodes', 'news_item', $news);
		setScVar('news_shortcodes', 'param', $param);
		$text = $e107->tp->parseTemplate($NEWS_PARSE, true);

		if ($mode == 'return')
		{
			return $text;
		}
		else
		{
			echo $text;
			return TRUE;
		}
	}

	//@TDODO deprecated?
	function make_xml_compatible($original)
	{
		global $e107;
		if (!is_object($e107->tp)) $e107->tp = new e_parse;
		$original = $e107->tp->toHTML($original, TRUE);
		$original = str_replace('&pound', '&amp;#163;', $original);
		$original = str_replace('&copy;', '(c)', $original);
		return htmlspecialchars($original, ENT_COMPAT, CHARSET);
	}
}

?>