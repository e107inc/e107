<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News handler
 *
 * $URL$
 * $Id$
*/

/**
 *
 * @package     e107
 * @subpackage	e107_handlers
 * @version     $Id$
 * @author      e107inc
 *
 * Classes:
 * news - old news class
 * e_news_item - news data model - the future
 * e_news_tree - news items collection
 * e_news_category_item - news category data model
 * e_news_category_tree - news category items collection
 */

if (!defined('e107_INIT')) { exit; }

class news {

	protected static $_rewrite_data = array();
	protected static $_rewrite_map = null;

	//FIXME - LANs
	//TODO - synch WIKI docs, add rewrite data to the event data
	function submit_item($news, $smessages = false)
	{
		global $e107cache, $e_event, $pref, $admin_log;

		$tp = e107::getParser();
		$sql = e107::getDb();

		$emessage = e107::getMessage();

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


		//news_comment_total

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
		$datarw['data']['news_rewrite_id'] = $news['news_rewrite_id'];
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

		// Calculate short strings for admin logging - no need to clog up the log with potentially long items
		$logData = $data['data'];
		if (isset($logData['news_body'])) $logData['news_body'] = $tp->text_truncate($tp->toDB($logData['news_body']),300,'...');
		if (isset($logData['news_extended'])) $logData['news_extended'] = $tp->text_truncate($tp->toDB($logData['news_extended']),300,'...');

		//XXX - Now hooks are executed only if no mysql error is found. Should it stay so? Seems sensible to me!
		if ($news['news_id'])
		{
			// Updating existing item
			$data['WHERE'] = 'news_id='.intval($news['news_id']);

			//$vals = "news_datestamp = '".intval($news['news_datestamp'])."', ".$author_insert." news_title='".$news['news_title']."', news_body='".$news['news_body']."', news_extended='".$news['news_extended']."', news_category='".intval($news['cat_id'])."', news_allow_comments='".intval($news['news_allow_comments'])."', news_start='".intval($news['news_start'])."', news_end='".intval($news['news_end'])."', news_class='".$tp->toDB($news['news_class'])."', news_render_type='".intval($news['news_rendertype'])."' , news_summary='".$news['news_summary']."', news_thumbnail='".$tp->toDB($news['news_thumbnail'])."', news_sticky='".intval($news['news_sticky'])."' WHERE news_id='".intval($news['news_id'])."' ";
			if ($sql->db_Update('news', $data))
			{
				e107::getAdminLog()->logArrayAll('NEWS_09', $logData);

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
				$data['news_id'] = $news['news_id'];
				$message = LAN_NEWS_6;
				$emessage->add(LAN_NEWS_6, E_MESSAGE_SUCCESS, $smessages);
				e107::getCache()->clear('news.php');

				//moved down - prevent wrong mysql_insert_id
				e107::getAdminLog()->logArrayAll('NEWS_08', $logData);

				//manage rewrites
				if('error' === $this->handleRewriteSubmit('insert', $data['data'], $datarw, $smessages))
				{
					$error = true;
				}

				e107::getEvent()->trigger('newspost', $data['data']);

				//XXX - triggerHook after trigger?
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

		$old_rewrite_data = array();
		if(e107::getDb()->db_Select('news_rewrite', '*', 'news_rewrite_source='.intval($rewrite_data['data']['news_rewrite_source']).' AND news_rewrite_type='.intval($rewrite_data['data']['news_rewrite_type'])))
		{
			$old_rewrite_data = e107::getDb()->db_Fetch();
		}

		//Delete if required
		if (empty($rewrite_data['data']['news_rewrite_string']))
		{
			if($old_rewrite_data)
			{
				self::clearRewriteCache($old_rewrite_data['news_rewrite_string']);
				e107::getDb()->db_Delete('news_rewrite', 'news_rewrite_id='.$old_rewrite_data['news_rewrite_id']);
				e107::getAdminLog()->logArrayAll('NEWS_13', $old_rewrite_data);
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
					if($old_rewrite_data) self::clearRewriteCache($old_rewrite_data['news_rewrite_string']);
					self::setRewriteCache($rewrite_data['data']['news_rewrite_string'], $rewrite_data['data']);
					e107::getAdminLog()->logArrayAll('NEWS_12', $rewrite_data['data']);
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
						if($old_rewrite_data) self::clearRewriteCache($old_rewrite_data['news_rewrite_string']);
						self::setRewriteCache($rewrite_data['data']['news_rewrite_string'], $rewrite_data['data']);
						e107::getAdminLog()->logArrayAll('NEWS_12', $rewrite_data['data']);
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

					$rewrite_data['data']['news_rewrite_id'] = $id;
					if($old_rewrite_data) self::clearRewriteCache($old_rewrite_data['news_rewrite_string']);
					self::setRewriteCache($rewrite_data['data']['news_rewrite_string'], $rewrite_data['data']);

					return false;
				}

				$rewrite_data['data']['news_rewrite_id'] = 0;
				if($rewrite_data['data']['news_rewrite_id'] = e107::getDb()->db_Insert('news_rewrite', $rewrite_data))
				{
					if($old_rewrite_data) self::clearRewriteCache($old_rewrite_data['news_rewrite_string']);
					self::setRewriteCache($rewrite_data['data']['news_rewrite_string'], $rewrite_data['data']);
					e107::getAdminLog()->logArrayAll('NEWS_12', $rewrite_data['data']);
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

	public static function retrieveRewriteString($news_id, $type = 1)
	{
		//XXX - Best way we have now, discuss
		if(null === self::$_rewrite_map)
		{
			$tmp = e107::getCache()->retrieve_sys('nomd5_news_rewrite_map');
			if(false !== $tmp && ($tmp = e107::getArrayStorage()->ReadArray($tmp)))
			{
				self::$_rewrite_map = $tmp;
			}
			else
			{
				self::$_rewrite_map = array();
				if(e107::getDb()->db_Select('news_rewrite'))
				{
					while ($tmp = e107::getDb()->db_Fetch())
					{
						self::$_rewrite_map[$tmp['news_rewrite_type']][$tmp['news_rewrite_source']] = $tmp['news_rewrite_string'];
					}
				}
				e107::getCache()->set_sys('nomd5_news_rewrite_map', e107::getArrayStorage()->WriteArray(self::$_rewrite_map, false));
			}
			unset($tmp);
		}

		//convert type if needed
		if(is_string($type))
		{
			switch($type)
			{
				case 'item':
				case 'extend':
					$type = 1;
				break;

				default:
					$type = 2;
				break;
			}
		}

		return (isset(self::$_rewrite_map[$type][$news_id]) ? self::$_rewrite_map[$type][$news_id] : '');
	}

	public static function retrieveRewriteData($sefstr, $force = true)
	{
		//check runtime cache
		if(isset(self::$_rewrite_data[$sefstr]))
		{
			return self::$_rewrite_data[$sefstr];
		}

		//check server cache if allowed
		if(!$force && ($ret = self::getRewriteCache($sefstr, true)))
		{
			self::$_rewrite_data[$sefstr] = $ret;
			return self::$_rewrite_data[$sefstr];
		}

		//search DB
		$ret = array();
		if(e107::getDb()->db_Select('news_rewrite', '*', "news_rewrite_string='".e107::getParser()->toDB($sefstr)."'"))
		{
			$ret = e107::getDb()->db_Fetch();
		}

		//set runtime cache
		self::$_rewrite_data[$sefstr] = $ret;

		//set server cache
		if($ret)
		{
			self::setRewriteCache($sefstr, $ret);
		}

		return self::$_rewrite_data[$sefstr];
	}

	public static function getRewriteCache($sefstr, $toArray = true)
	{
		$sefstr = md5($sefstr);

		$ret = ecache::retrieve_sys('news_sefurl'.$sefstr, false, true);

		if($ret && $toArray)
		{
			return e107::getArrayStorage()->ReadArray($ret);
		}
		return $ret;
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

	function render_newsitem($news, $mode = 'default', $n_restrict = '', $NEWS_TEMPLATE = '', $param = array())
	{
		global $NEWSSTYLE, $NEWSLISTSTYLE;
		
		if ($override_newsitem = e107::getSingleton('override', true)->override_check('render_newsitem')) {
			$result = call_user_func($override_newsitem, $news, $mode, $n_restrict, $NEWS_TEMPLATE, $param);
			if ($result == 'return')
			{
				return;
			}
		}

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

		$tmp = array();
		$tmp['caticon'] = ICONSTYLE;
		$tmp['commentoffstring'] = COMMENTOFFSTRING;
		$tmp['commentlink'] = COMMENTLINK;
		$tmp['trackbackstring'] = (defined("TRACKBACKSTRING") ? TRACKBACKSTRING : "");
		$tmp['trackbackbeforestring'] = (defined("TRACKBACKBEFORESTRING") ? TRACKBACKBEFORESTRING : "");
		$tmp['trackbackafterstring'] = (defined("TRACKBACKAFTERSTRING") ? TRACKBACKAFTERSTRING : "");
		$tmp['itemlink'] = (defined("NEWSLIST_ITEMLINK")) ? NEWSLIST_ITEMLINK : "";
		$tmp['thumbnail'] =(defined("NEWSLIST_THUMB")) ? NEWSLIST_THUMB : "border:0px";
		$tmp['catlink']  = (defined("NEWSLIST_CATLINK")) ? NEWSLIST_CATLINK : "";
		$tmp['caticon'] =  (defined("NEWSLIST_CATICON")) ? NEWSLIST_CATICON : ICONSTYLE;

		if(!$param) $param = array();
		$param = array_merge($tmp, $param);


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
		//$loop_uid = $news['news_author']; - no references found

		//require_once(e_CORE.'shortcodes/batch/news_shortcodes.php');
		/* DEPRECATED
		setScVar('news_shortcodes', 'news_item', $news);
		setScVar('news_shortcodes', 'param', $param);
		*/
		// Retrieve batch sc object, set required vars
		e107::getScBatch('news')
			->setScVar('news_item', $news)
			->setScVar('param', $param);
			
		$text = e107::getParser()->parseTemplate($NEWS_PARSE, true);

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
		$original = e107::getParser()->toHTML($original, TRUE);
		$original = str_replace('&pound', '&amp;#163;', $original);
		$original = str_replace('&copy;', '(c)', $original);
		return htmlspecialchars($original, ENT_COMPAT, CHARSET);
	}
}


//EXPERIMENTAL

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

class e_news_category_item extends e_model
{
	/**
	 * Shorthand getter for news category fields
	 *
	 * @param string $category_field name without the leading 'category_' prefix
	 * @param mixed $default
	 * @return mixed data
	 */
	public function get($category_field, $default = null)
	{
		return parent::get('category_'.$category_field, $default);
	}

	public function sc_news_category_title($parm = '')
	{
		if('attribute' == $parm) return e107::getParser()->toAttribute($this->get('name'));
		return $this->get('name');
	}

	public function sc_news_category_url($parm = '')
	{

		$url = e107::getUrl()->create('core:news', 'main', 'action=list&id='.$this->getId().'&sef='.$this->get('rewrite_string'));
		switch($parm)
		{
			case 'link':
				return '<a href="'.$url.'" class="news-category">'.$this->sc_news_category_title().'</a>';
			break;

			case 'link_icon':
				return '<a href="'.$url.'" class="news-category">'.$this->sc_news_category_icon().'&nbsp;'.$this->sc_news_category_title().'</a>';
			break;

			default:
				return $url;
			break;
		}
	}

	public function sc_news_category_link()
	{
		return $this->sc_news_category_url('link');
	}

	public function sc_news_category_icon($parm = '')
	{
		if(!$this->get('icon'))
		{
			return '';
		}
		if(strpos($this->get('icon'), '{') === 0)
		{
			$src = e107::getParser()->replaceConstants($this->get('icon'));
		}
		else
		{
			$src = e_IMAGE_ABS.'icons/'.$this->get('icon');
		}
		switch($parm)
		{
			case 'src':
				return $src;
			break;
			case 'link':
				return '<a href="'.$this->sc_news_category_url().'" class="news-category" title="'.$this->sc_news_category_title('attribute').'"><img src="'.$src.'" class="icon news-category" alt="'.$this->sc_news_category_title('attribute').'" /></a>';
			break;

			default:
				return '<img src="'.$src.'" class="icon news-category" alt="'.$this->sc_news_category_title('attribute').'" />';
			break;
		}
	}

	public function sc_news_category_news_count($parm = '')
	{
		if(!$this->is('category_news_count'))
		{
			return '';
		}
		return (string) $this->get('news_count');
	}
}


class e_news_category_tree extends e_model
{
	/**
	 * @var array
	 */
	protected $_tree_db_total = array();

	/**
	 * Get category news item object from the tree
	 * If $force_empty is true and corresponding category object can't be found,
	 * empty object will be set/returned if
	 *
	 * @param integer $category_id
	 * @param boolean $force_empty
	 * @return e_news_category_item
	 */
	function getNode($category_id, $force_empty = false)
	{
		$default = null;
		if($force_empty && $this->isData('__tree/'.$category_id))
		{
			$default = new e_news_category();
			$this->setNode($category_id, $default);
		}

		return $this->getData('__tree/'.$category_id, $default);
	}

	/**
	 * Set category news item object
	 *
	 * @param integer $category_id
	 * @param e_news_category_item $category_object
	 * @return e_news_category_tree
	 */
	function setNode($category_id, $category_object)
	{
		if(!$category_id || !($category_object instanceof e_news_category_item))
		{
			return $this;
		}
		$this->_tree_total = null;
		$this->setData('__tree/'.$category_id, $category_object);

		return $this;
	}

	/**
	 * Set news category tree array
	 *
	 * @param array $tree
	 * @return e_news_category_tree
	 */
	public function setTree(array $tree)
	{
		$this->_tree_total = null;
		$this->setData('__tree', $tree);

		return $this;
	}

	/**
	 * Get news category tree array
	 *
	 * @return array
	 */
	public function getTree()
	{
		return $this->getData('__tree', array());
	}

	/**
	 * Total records found (DB)
	 *
	 * @return integer
	 */
	function getTreeTotal()
	{
		if(null === $this->_tree_total)
		{
			$this->_tree_total = count($this->getTree());
		}
		return $this->_tree_total;
	}

	/**
	 * Load category data from the DB
	 *
	 * @param boolean $force
	 * @return e_news_category_tree
	 */
	public function load($force = false)
	{
		if(!$force && $this->is($key))
		{
			return $this;
		}

		$sef = (e107::getUrl()->getProfileId('news') !== 'main');
		if($sef)
		{
			$qry = "
			SELECT nc.*, ncr.news_rewrite_string AS category_rewrite_string, ncr.news_rewrite_id AS category_rewrite_id FROM #news_category AS nc
			LEFT JOIN #news_rewrite AS ncr ON nc.category_id=ncr.news_rewrite_source AND ncr.news_rewrite_type=2
			ORDER BY nc.category_order ASC
			";
		}
		else
		{
			$qry = "SELECT * FROM #news_category ORDER BY category_order ASC";
		}

		$tree = array();
		$sql = e107::getDb();
		$sql->db_Mark_Time('news_category_tree');

		if($sql->db_Select_gen($qry))
		{
			while($row = $sql->db_Fetch())
			{
				$tree[$row['category_id']] = new e_news_category_item($row);
			}
		}
		$this->setTree($tree);

		return $this;
	}

	/**
	 * Load active categories only (containing active news items)
	 *
	 * @param boolean $force
	 * @return e_news_category_tree
	 */
	public function loadActive($force = false)
	{
		if(!$force && $this->is($key))
		{
			return $this;
		}

		$sef = (e107::getUrl()->getProfileId('news') !== 'main');
		$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
		$time = time();
		if($sef)
		{
			$qry = "
			SELECT COUNT(n.news_id) AS category_news_count, nc.*, ncr.news_rewrite_string AS category_rewrite_string, ncr.news_rewrite_id AS category_rewrite_id FROM #news_category AS nc
			LEFT JOIN #news_rewrite AS ncr ON nc.category_id=ncr.news_rewrite_source AND ncr.news_rewrite_type=2
			LEFT JOIN #news AS n ON n.news_category=nc.category_id
			WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
				AND n.news_start < ".$time." AND (n.news_end=0 || n.news_end>".$time.")
			GROUP BY nc.category_id
			ORDER BY nc.category_order ASC
			";
		}
		else
		{
			$qry = "
			SELECT COUNT(n.news_id) AS category_news_count, nc.* FROM #news_category AS nc
			LEFT JOIN #news AS n ON n.news_category=nc.category_id
			WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
				AND n.news_start < ".$time." AND (n.news_end=0 || n.news_end>".$time.")
			GROUP BY nc.category_id
			ORDER BY nc.category_order ASC
			";
		}

		$tree = array();
		$sql = e107::getDb();
		$sql->db_Mark_Time('news_category_tree');

		if($sql->db_Select_gen($qry))
		{
			while($row = $sql->db_Fetch())
			{
				$tree[$row['category_id']] = new e_news_category_item($row);
			}
		}
		$this->setTree($tree);

		return $this;
	}

	/**
	 * Render Category tree
	 *
	 * @param array $parms [return, parsesc=>1|0, mode=>string]
	 * @param boolean $tablerender
	 * @param array $force_template template override
	 * @return string
	 */
	function render($parms = array(), $tablerender = true, $force_template = array())
	{
		if(!$this->getTreeTotal())
		{
			return '';
		}

		$template = $force_template; //TODO template search, more template freedom, tree shortcodes
		$ret = array();
		$tp = e107::getParser();

		if(!isset($parms['parsesc'])) $parms['parsesc'] = true;
		$parsesc = $parms['parsesc'] ? true : false;

		foreach ($this->getTree() as $cat)
		{
			$ret[] = $tp->parseTemplate($template['item'], $parsesc, $cat);
		}

		if($ret)
		{
			$separator = varset($template['separator'], '');
			$ret = implode($separator, $ret);
			$return = isset($parms['return']) ? true : false;

			if($tablerender)
			{
				$caption = vartrue($parms['caption']) ? defset($parms['caption'], $parms['caption']) : LAN_NEWSCAT_MENU_TITLE; // found in plugins/news/languages/English.php
				$mod = true === $tablerender ? 'news_categories_menu' : $tablerender;
				return e107::getRender()->tablerender($caption, $ret, varset($parms['mode'], $mod), $return);
			}

			if($return) return $ret;
			echo $ret;
		}

		return '';
	}
}

