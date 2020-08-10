<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

//$import_class_names['html_import'] 		= 'HTML';
//$import_class_comment['html_import'] 	= 'Import content from an html website. eg. created with Frontpage, Dreamweaver or Notepad etc. ';
//$import_class_support['html_import'] 	= array('news','page');
//$import_default_prefix['html_import'] 	= '';

require_once('import_classes.php');

class html_import extends base_import_class
{
	public $title		= 'HTML';
	public $description	= 'Import content from an html website. eg. created with Frontpage, Dreamweaver or Notepad etc. ';
	public $supported	= array('news','page');
	public $mprefix		= false;
	
	
	public $override 	= true;
	var $sourceType 	= 'rss';
	var $feedUrl		= null;
	var $defaultClass 	= false;
	var $useTidy		= true;
	var	$action			= 'preview'; // default action after setup page; 
	private $localPath	= '';
	private $content 	= array();
	private $contentArray = array();


	function init()
	{
		$this->feedUrl	= vartrue($_POST['siteUrl'],false);
		$this->feedUrl 	= rtrim($this->feedUrl,"/");

		if(!extension_loaded("tidy")) 
		{
			$this->useTidy = false;
			e107::getMessage()->addWarning("PHP Tidy extension is NOT loaded!");
		}
		
		if($_POST['preview'])
		{
			$this->previewContent();
			return false;	
		}
		
		if($_POST['do_conversion'])
		{
			$import = $this->sortSelection();	
			$this->doConversion($import);
		}
	
	}


	function sortSelection()
	{
		$import = array();
		foreach($_POST as $k=>$v)
		{
		
			if($v == 'news' || $v=='page')
			{
				$file = str_replace("add__","",$k);
				$import[$v][]	 = 	$file;
				
			}
			
		}		
		
		return $import;
	}
	
	
	function doConversion($data)
	{
		print_a($data);
	}
	
	
	function config()
	{
		$var[0]['caption']	= "Website Home-page URL";
		$var[0]['html'] 	= "<input class='tbox' type='text' name='siteUrl' size='80' value='{$_POST['rss_feed']}' maxlength='250' />";

		return $var;
	}
	

  	// Set up a query for the specified task.
  	// Returns TRUE on success. FALSE on error
	function setupQuery($task, $blank_user=FALSE)
	{
		$this->arrayData = array();
		
		print_a($_POST);


		$file = $this->feedUrl;

    	switch ($task)
		{		
			case 'news' :
			case 'page' :
			case 'links' :
				
				// $rawData = $xml->getRemoteFile($file);
				//	print_a($rawData);
				//$content = $this->getAll();
		
				if ($array === FALSE || $file === FALSE) return FALSE;
				
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

  
	private function getAll($root = '')
	{
		$html = $this->getRawHtml($root);
		$pages = $this->findLinks($html);
		$c = 0;

		foreach($pages as $url=>$p)
		{
			// echo "url=".$url;
			$html = $this->getAll($url);	
			
			$html = str_replace("\n","",$html); // strip line-breaks. 
			$html = preg_replace("/<title>([^<]*)<\/title>/i","",$html);
			$html = trim($html,"\n");
			
			$body = trim(strip_tags($html,"<b><i><u><strong><em><br><img><object><embed><a>"));
		
			$this->content[$url] = array(
				'title'	=> str_replace("\n","",$p['title']),
				// 'raw'	=> $html,
				'body'	=> $body
			);
			
			$c++;
			
			if($c == 15)
			{
				break;	
			}
			
		}
			
		return $this->content;
		
	}
		
				
	private function previewContent()
	{
		$frm = e107::getForm();
		$ns = e107::getRender();
		$tp = e107::getParser();
		
		$content = $this->getAll();	
		
		$text = "
		<form method='post' action='".e_SELF."?import_type=html_import' id='core-import-form'>
		<fieldset id='core-import-select-type'>
		<legend class='e-hideme'>".DBLAN_10."</legend>
            <table class='table adminlist'>
			<colgroup>
			<col style='width:40%' />
			<col />
			<col />
			<col />
			</colgroup>
			<thead>
			<tr>
            	<th>".LAN_TITLE."</th>
            	<th>Sample</th>
            	<th>".LAN_URL."</th>
                <th class='center'>".LAN_OPTIONS."</th>

			</tr>
			</thead>
			<tbody>\n";


        foreach($content as $key=>$data)
		{
          	$text .= "<tr>
				
			<td>".$data['title']."</td>\n
			<td>".$tp->text_truncate($data['body'],150)."</td>\n
			<td>
			 		<a class='e-dialog' href='".$this->localPath.$key."'>".$key."</a>
			 	</td>
			
			";

             $text .= "
			 	<td>
			 		".$frm->select('add__'.$key,array('news'=>'News','page'=>'Page','0'=>'Ignore'))."
			 	</td>
			 </tr>";
		}

		$text .= "
				</tbody>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('do_conversion',LAN_CONTINUE, 'execute').
				$frm->admin_button('back',LAN_CANCEL, 'cancel')."
				<input type='hidden' name='db_import_type' value='html_import' />
				<input type='hidden' name='import_type' value='html_import' />
				<input type='hidden' name='import_source' value='".$this->sourceType."' />
				<input type='hidden' name='import_block_news' value='1' />
				<input type='hidden' name='siteUrl' value='".$this->feedUrl."' />	
			</div>
		</fieldset>
		</form>";

		$ns->tablerender(LAN_PLUGIN_IMPORT_NAME.SEP.$this->feedUrl,$text);
	
	}		
		

	private function getRawHtml($file='')
	{
		$url 		= $this->feedUrl."/".$file; 
		
		if($file == '')	{ $file = "index.html";	} // just for local file, not url. 
			
		$path		= md5($this->feedUrl);
		$local_file = $path."/".$file; 
		$this->localPath = e_TEMP.$path."/"; 
		
		if(!is_dir(e_TEMP.$path))
		{
			mkdir(e_TEMP.$path,0755);
		}
				
		if(!file_exists(e_TEMP.$local_file))
		{
			 e107::getFile()->getRemoteFile($url, $local_file); // downloads to e107_system/.../temp
		}		
		
		if($this->useTidy)
		{
			$tidy 		= new tidy();
			$options 	= array("output-xhtml" => true, "clean" => true);
			$parsed 	= tidy_parse_file(e_TEMP.$local_file,$options);

			return $parsed->value;
		}
		elseif(!$html = file_get_contents(e_TEMP.$local_file))
		{
			return "Couldn't read file";
		}	
			
		return $html;
	}
	
			
	private function findLinks($content,$type='html')
	{	
		$doc = new DOMDocument(); 
		$doc->loadHTML($content);
		
		$urls 	= $doc->getElementsByTagName('a');	
		$pages 	= array();
						
		foreach ($urls as $u) 
		{
			$title 	= str_replace("\n","",$u->nodeValue);
			$href 	= $u->attributes->getNamedItem('href')->value;
			$href 	= ltrim(str_replace($this->feedUrl,"",$href),"/");	
			
			if($type == 'html' && (substr($href,-5,5)=='.html' || substr($href,-4,4)=='.htm'))
			{	
				$pages[$href] = array('title'=>$title, 'href'=>$href);
			}
		}	
		
		return $pages; 	
		
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
		
		if(!$content = $this->process('content_encoded',$source))
		{
			$body = $this->process('description',$source);	
		}
		else
		{
			$body = $content;
		}
				
		$body 								= $this->saveImages($body,'news');
		$keywords 							= $this->process('category',$source);
			
				
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
		//	$target['news_sef']					= $source['post_name'];
		$target['news_body']					= "[html]".$body."[/html]";
		//	$target['news_extended']			= '';
		$target['news_meta_keywords']			= implode(",",$keywords);
		//	$target['news_meta_description']	= '';
		$target['news_datestamp']				= strtotime($source['pubDate'][0]);
		//	$target['news_author']				= $source['post_author'];
		//	$target['news_category']			= '';
		//	$target['news_allow_comments']		= ($source['comment_status']=='open') ? 1 : 0;
		//	$target['news_start']				= '';
		//	$target['news_end']					= '';
		///	$target['news_class']				= '';
		//	$target['news_render_type']			= '';
		//	$target['news_comment_total']		= $source['comment_count'];
		//	$target['news_summary']				= $source['post_excerpt'];
		//	$target['news_thumbnail']			= '';
		//	$target['news_sticky']				= '';

		
		return $target;  // comment out to debug 
		
		$this->renderDebug($source,$target);
		
		// DEBUG INFO BELOW. 		
		
	}


	function process($type='description',$source)
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
	 * @param returns text-body with remote links replaced with local ones for the images downloaded. 
	 */
	function saveImages($body,$cat='news')
	{
		$mes = e107::getMessage();
		$med = e107::getMedia();
		$tp = e107::getParser();
		$search = array();
		$replace = array();
		
		
	//	echo htmlentities($body);
		preg_match_all("/(((http:\/\/www)|(http:\/\/)|(www))[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)\.(jpg|jpeg|gif|png|svg)/im",$body,$matches);
		$fl = e107::getFile();
			
		if(is_array($matches[0]))
		{
			$relPath = 'images/'.md5($this->feedUrl);
			
			if(!is_dir(e_MEDIA.$relPath))
			{
				mkdir(e_MEDIA.$relPath,'0755');	
			}
			
			foreach($matches[0] as $link)
			{
				if(file_exists($relPath."/".$filename))
				{
					continue;
				}
				
				$filename = basename($link);
				$fl->getRemoteFile($link,$relPath."/".$filename);
				$search[] = $link;
				$replace[] = $tp->createConstants(e_MEDIA.$relPath."/".$filename,1);
			}	
		}
		
		if(count($search))
		{
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


