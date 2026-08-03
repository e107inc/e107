<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/wordpress_import_class.php,v $
 * $Revision: 11315 $
 * $Date: 2010-02-10 10:18:01 -0800 (Wed, 10 Feb 2010) $
 * $Author: secretr $
 */

// This must be an incredibly pointless file! But it does allow testing of the basic plugin structure.

// Each import file has an identifier which must be the same for:
//		a) This file name - add '_class.php' to get the file name
//		b) The array index of certain variables
// Array element key defines the function prefix and the class name; value is displayed in drop-down selection box



require_once(__DIR__.'/../import_classes.php');

class rss_import extends base_import_class
{
	
	public $title			= 'RSS';
	public $description		= 'Import content via RSS v2.0 feeds from virtually any website.';
	public $supported		= array('news','page','links');
	public $mprefix			= false;
	public $sourceType 		= 'rss';	
	
	

	var $feedUrl			= null;
	var $defaultClass 		= false;

	/**
	 * Whether the feed's images are downloaded into the media tree.
	 *
	 * Not named for the method below it. A property and a method may share a
	 * name, and one that does is a preference nothing reads.
	 *
	 * @var boolean
	 */
	public $importImages = false;

	private $foundImages = array();


	function init()
	{
		$this->feedUrl		= vartrue($_POST['rss_feed'],false);
		$this->importImages	= !empty($_POST['rss_saveimages']);
	}
	
	
	
	function config()
	{
		$feed = e107::getParser()->toAttribute(varset($_POST['rss_feed'], ''));

		$var[0]['caption']	= "Feed URL";
		$var[0]['html'] 	= "<input class='tbox span7' type='text' name='rss_feed' size='180' value='{$feed}' maxlength='250' />";

		$var[1] = $this->imageOptionField();


		return $var;
	}


	/**
	 * The row that offers the administrator the image download.
	 *
	 * Shared, because every provider extending this one inherits saveImages()
	 * and the preference it reads. A provider that renders its own fields and
	 * not this one leaves that preference with no way of being set.
	 *
	 * @return array
	 */
	protected function imageOptionField()
	{
		return array(
			'caption' => "Save Images Locally",
			'html'    => e107::getForm()->checkbox('rss_saveimages', 1),
		);
	}
	

  // Set up a query for the specified task.
  // Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
		$mes = e107::getMessage();
		
		$this->arrayData = array();
		
		$xml = e107::getXml();	
		$file = $this->feedUrl;
			
		$mes->addDebug("rss_import::setupQuery - \$task:  ".$task);	
		$mes->addDebug("rss_import::setupQuery - \$file:  ".$file);	
					
    	switch ($task)
		{		
			case 'news' :
			case 'page' :
			case 'links' :
				
				// $rawData = $xml->getRemoteFile($file);
				//	print_a($rawData);
				$array = $xml->loadXMLfile($file,'advanced');
				
			//	$mes->addDebug("rss - setupQuery - RSS array:  ".print_a($array,true));	
				
				if ($array === FALSE || $file === FALSE) 
				{
					$mes->addError("No data returned from : ".$file);
					return FALSE;
				}
					
				
				foreach($array['channel']['item'] as $val)
				{
					$this->arrayData[] = $val;
				}
				
				$this->arrayData = array_reverse($this->arrayData); // most recent last. 
				reset($this->arrayData);
				
			break;

			default :
			return FALSE;
		}

		$this->copyUserInfo = !$blank_user;
		$this->currentTask = $task;
		return TRUE;
  }


  //------------------------------------
  //	Internal functions below here
  //------------------------------------
  
	/**
	 * Align source data to e107 User Table 
	 * @param $target array - default e107 target values for e107_user table. 
	 * @param $source array - WordPress table data
	 */
	function copyUserData(&$target, &$source)
	{

	}

	/**
	 * Align source data with e107 News Table 
	 * @param $target array - default e107 target values for e107_news table. 
	 * @param $source array - RSS data
	 */
	function copyNewsData(&$target, &$source)
	{
		$this->foundImages = array();
		
		if(!$content = $this->process('content_encoded',$source))
		{
			$body = $this->process('description',$source);	
		}
		else
		{
			$body = $content;
		}
				
		$body 			= $this->saveImages($body,'news');
		$keywords 		= $this->process('category',$source);
		$sef            = $this->process('sef',$source);
							
		if(!vartrue($source['title'][0]))
		{
			list($title,$newbody) = explode("<br />",$body,2);
			$title = strip_tags($title);
			if(trim($newbody)!='')
			{
				$body = $newbody;	
			}	
		}
		else 
		{
			$title = $source['title'][0];
		}
		
		$target['news_title']					= $title;
		$target['news_sef']					    = $sef;
		$target['news_body']					= "[html]".$body."[/html]";
		//	$target['news_extended']			= '';
		$target['news_meta_keywords']			= implode(",",$keywords);
		//	$target['news_meta_description']	= '';
			$target['news_datestamp']			= strtotime($source['pubDate'][0]);
		//	$target['news_author']				= $source['post_author'];
		//	$target['news_category']			= '';
		//	$target['news_allow_comments']		= ($source['comment_status']=='open') ? 1 : 0;
		//	$target['news_start']				= '';
		//	$target['news_end']					= '';
		///	$target['news_class']				= '';
		//	$target['news_render_type']			= '';
		//	$target['news_comment_total']		= $source['comment_count'];
		//	$target['news_summary']				= $source['post_excerpt'];
			$target['news_thumbnail']			= !empty($this->foundImages[0]) ? $this->foundImages[0] : '';
		//	$target['news_sticky']				= '';

		
		
		return $target;  // comment out to debug 
		
	//	$this->renderDebug($source,$target);
		
		// DEBUG INFO BELOW. 		
		
	}


	function process($type,$source)
	{
		switch ($type) 
		{			
			case 'category':
				$keywords = array();
				if(is_array(varset($source['category'][0])))
				{
					foreach($source['category'] as $val)
					{
						if(varset($val['@value']))
						{
							$keywords[] = $val['@value'];
						}
					}
					return $keywords;
				}
				elseif(is_array(varset($source['category'])))
				{
					foreach($source['category'] as $val)
					{
						if(varset($val) && is_string($val))
						{
							$keywords[] = $val;
						}
					}
					return $keywords;		
				}
			break;

			case 'sef':
				return '';
			break;
			
			default:
				return varset($source[$type][0]);
			break;
		}	
	}

	/**
	 * Align source data to e107 Page Table 
	 * @param $target array - default e107 target values for e107_page table. 
	 * @param $source array - WordPress table data
	 */
	function copyPageData(&$target, &$source)
	{
		$body 							= $this->saveImages($source['description'][0],'page');
	// 	$target['page_id']				= $source['ID']; //  auto increment
		$target['page_title']			= $source['title'][0];
	//	$target['page_sef']				= $source['post_name'];
		$target['page_text']			= "[html]".$body."[/html]";
	//	$target['page_metakeys']		= '';
	//	$target['page_metadscr']		= '';
		$target['page_datestamp']		= strtotime($source['pubDate'][0]);
	//	$target['page_author']			= $source['post_author'];
	//	$target['page_category']		= '',
	//	$target['page_comment_flag']	= ($source['comment_status']=='open') ? 1 : 0;
	//	$target['page_password']		= $source['post_password'];
		
		return $target;  // comment out to debug 
		
		// DEBUG INFO BELOW. 
		$this->renderDebug($source,$target);
		
	}
	

	/**
	 * Align source data to e107 Links Table 
	 * @param $target array - default e107 target values for e107_links table. 
	 * @param $source array - WordPress table data
	 */
	function copyLinksData(&$target, &$source)
	{
		$tp = e107::getParser();
			 		
	// 	$target['page_id']				= $source['ID']; //  auto increment
		$target['link_name']			= $source['title'][0];
		$target['link_url']				= $source['link'][0];
	//	$target['link_description']		= "[html]".$source['post_content']."[/html]";
	//	$target['link_button']			= '';
	//	$target['link_category']		= '';
	//	$target['link_order']			= strtotime($source['post_date']);
	//	$target['link_parent']			= $source['post_author'];
	//	$target['link_open']			= '';
	//	$target['link_class']			= '';
	//	$target['link_sefurl']			= $source['post_password'];
		
		
		
		return $target;  // comment out to debug 
			
		$this->renderDebug($source,$target);
		
	}
	
	
	/** Download and Import remote images and update body text with local relative-links. eg. {e_MEDIA}
	 *
	 * Does nothing at all unless the administrator asked for it. What it fetches
	 * is named by the feed, so the bytes are verified and the local extension is
	 * taken from them; see base_import_class::importRemoteImage().
	 *
	 * @param string $body text body carrying the feed's img tags
	 * @param string $cat  media category the stored images are imported into
	 * @return string body with remote links replaced by local ones
	 */
	function saveImages($body,$cat='news')
	{
		if(empty($this->importImages))
		{
			return $body;
		}

		$mes = e107::getMessage();
		$med = e107::getMedia();
		$tp = e107::getParser();
		$search = array();
		$replace = array();

		$result = $tp->getTags($body, 'img');

		if(empty($result['img']))
		{
			$mes->addDebug("No Images Found: ".print_a($result,true));

			return $body;
		}

		$relPath = 'images/'. substr(md5($this->feedUrl),0,10);
		$dir = e_MEDIA.$relPath.'/';

		if(!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir))
		{
			$mes->addError("Could not create ".$dir);

			return $body;
		}

		foreach($result['img'] as $att)
		{
			$filename = $this->importRemoteImage($att['src'], $dir);

			if($filename === false)
			{
				$mes->addDebug("Not an image, or could not be fetched: ".$att['src']);
				continue;
			}

			$src = $tp->createConstants($dir.$filename,1);
			$search[] = $att['src'];
			$this->foundImages[] = $src;
			$replace[] = $src;
		}

		if(count($search))
		{
			$mes->addDebug("Found: ".print_a($search,true));
			$mes->addDebug("Replaced: ".print_a($replace,true));
			$med->import($cat,e_MEDIA.$relPath);
		}

		return str_replace($search,$replace,$body);
	}
	

	
	
	
	
	
	function renderDebug($source,$target)
	{
		
	//	echo print_a($target);
	//	return;
				
		echo "
		<div style='width:1000px'>
			<table style='width:100%'>
				<tr>
					<td style='width:500px;padding:10px'>".print_a($source,TRUE)."</td>
					<td style='border-left:1px solid black;padding:10px'>".print_a($target,TRUE)."</td>
				</tr>
			</table>
		</div>";
	}

}


