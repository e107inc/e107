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



// require_once('import_classes.php');
require_once('rss_import_class.php');


class blogger_import extends rss_import
{	
	public $title		= "Blogger";
	public $description	= 'Import up to 500 items from yourblog.blogspot.com';
	public $supported	=  array('news');
	public $mprefix		= false;
	
	public $cleanupHtml = false;
	public $defaultClass = false;

	
	/*
	 If the first 500 posts of your blog feed are here:

    http://YOURBLOG.blogspot.com/feeds/posts/default?max-results=999

	the second 500 posts are here:

    http://YOURBLOG.blogspot.com/feeds/posts/default?max-results=999&start-index=501
	 */
	function init()
	{
		$mes = e107::getMessage();

		if(E107_DEBUG_LEVEL > 0)
		{
			$this->action = 'preview'; // changes default action to 'preview' method below. (for testing)
		}


		if(vartrue($_POST['bloggerUrl']))
		{			
			$this->feedUrl = rtrim($_POST['bloggerUrl'],"/")."/feeds/posts/default?max-results=999&alt=rss";	
		}
		
		if(vartrue($_POST['bloggerCleanup']))
		{
			$this->cleanupHtml = true;
		}

		$mes->addDebug("Blogger Feed:".$this->feedUrl);

		if(!empty($_POST['preview']))
		{
			$this->preview();
			return;
		}
	}
		
	
	function config()
	{
		$var[0]['caption']	= "Blogger URL";
		$var[0]['html'] 	= e107::getForm()->text('bloggerUrl', $_POST['bloggerUrl'],255, 'size=xxlarge'); //  "<input class='tbox' type='text' name='bloggerUrl' size='120' value='{$_POST['bloggerUrl']}' maxlength='250' />";
		$var[0]['help']		= "eg. http://blogname.blogspot.com";
		
		$var[1]['caption']	= "Cleanup HTML in content";
		$var[1]['html'] 	= e107::getForm()->checkbox('bloggerCleanup',1, $_POST['bloggerCleanup']); // "<input class='tbox' type='checkbox' name='bloggerCleanup' value='1' />";
		$var[1]['help']		= "Tick to enable";
		
		return $var;
	}

	function process($type,$source)
	{

		$tp = e107::getParser();

		$allowedTags        = array('html', 'body', 'a','img','table','tr', 'td', 'th', 'tbody', 'thead', 'colgroup', 'b',
		'i', 'pre','code', 'strong', 'u', 'em','ul', 'ol', 'li','img','h1','h2','h3','h4','h5','h6','p','iframe',
		'div','pre','section','article', 'blockquote','hgroup','aside','figure', 'video', 'span', 'br',
		'small', 'caption', 'noscript'
		);

		$allowedAttributes  = array(
			'default'   => array('id'),
			'img'       => array('id', 'src', 'style', 'class', 'alt', 'title', 'width', 'height'),
			'a'         => array('id', 'href', 'class', 'title', 'target'),
			'script'	=> array('type', 'src', 'language'),
			'iframe'	=> array('id', 'src', 'frameborder', 'class', 'width', 'height', 'style')
		);

		$tp->setAllowedTags($allowedTags);
		$tp->setAllowedAttributes($allowedAttributes);

		switch ($type) 
		{
			case 'description':
				$body = $source[$type][0];

				if($this->cleanupHtml == true)
				{
				//	$body = preg_replace("/font-family: [\w]*;/i","", $body);
			//		$body = preg_replace('/class="[\w]*" /i',"", $body);
					$body = str_replace("<br>","<br />",$body);

					$body = $tp->cleanHtml($body,false);

					$patterns = array();
					$patterns[0] = '/<div[^>]*>/';
					$patterns[1] = '/<\/div>/';

					$replacements = array();
					$replacements[2] = '';
					$replacements[1] = '';

					$body = preg_replace($patterns, $replacements, $body);

					$srch = array('<span>', '</span>', '<br /><br /><br /><br />');
					$repl = array("", "", "<br /><br />");

					$body = str_replace($srch, $repl, $body);


					return $body;
				}
				else 
				{
					return $body;
				}		
			break;

			case 'sef':

				if(!empty($source['link'][0]))
				{
					return str_replace(".html","", basename($source['link'][0]));
				}

				return "";
			break;
			
			default:
				return $source[$type][0];
			break;
		}		
		
		
	}


	function preview()
	{
		$file = $this->feedUrl;
		$array = e107::getXml()->loadXMLfile($file,'advanced');

	//	print_a($array); // raw xml data;


		foreach($array['channel']['item'] as $src)
		{
		//	$data = $this->process('description', $src);
		//	$this->process('sef',$src);
			$data = $this->copyNewsData($target,$src);
			print_a($data);

		}


	}


	//TODO Comment Import: 
	//http://blogname.blogspot.com/feeds/comments/default?alt=rss
	
}


