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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/import/import_classes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/*
Root classes for import and saving of data. Application-specific classes build on these
*/

class base_import_class
{
	var $ourDB          = null;
	var $DBPrefix       = '';
	var $currentTask    = '';
	var $copyUserInfo   = true;
	protected $arrayData = array();

	/**
	 * Connect to the external DB if not already connected
	 */
	function database($database, $prefix)
	{		
		if ($this->ourDB == NULL)
		{
	  		$this->ourDB = e107::getDb('ourDB');
	  		$result = $this->ourDB->database($database,$prefix,true);
	  		// SQL identifiers cannot be bound; sanitise both components before they
	  		// are spliced into table references (e.g. `db`.prefixtable) by callers.
	  		$safeDatabase = "`".str_replace("`", "``", $database)."`";
	  		$safePrefix   = preg_replace('/[^A-Za-z0-9_]/', '', $prefix);
	  		$this->DBPrefix = $safeDatabase.".".$safePrefix;
	  		if ($result)
	  		{
	  	 		return $result;
	  		}

		}
		
		return TRUE;
	}

	/**
	 * Set up a query for the specified task.  If $blank_user is TRUE, user ID Data in source data is ignored
	 * @return boolean TRUE on success. FALSE on error
	*/
	function setupQuery($task, $blank_user=FALSE)
	{
		return FALSE;
	}


	function saveData($dataRecord)
	{
		switch($this->currentTask)
		{
	  		case 'users' :
	    		return $this->saveUserData($dataRecord);
	    	break;

	  		case 'userclass' :
	    		return $this->saveUserClassData($dataRecord);
	    	break;

			case 'news' :
				return $this->saveNewsData($dataRecord);
			break;

			case 'newscategory' :
				return $this->saveNewsCategoryData($dataRecord);
			break;
			
			case 'page' :
				return $this->savePageData($dataRecord);
			break;

			case 'pagechapter' :
				return $this->savePageChapterData($dataRecord);
			break;

			case 'links' :
				return $this->saveLinksData($dataRecord);
			break;
			
			case 'media' :
				return $this->saveMediaData($dataRecord);
			break;
			
	  		case 'forum' :
	    		return $this->saveForumData($dataRecord);
	    	break;
			
		  	case 'forumthread' :
	    		return $this->saveForumThreadData($dataRecord);
	    	break;		
			
	  		case 'forumpost' :
	    		return $this->saveForumPostData($dataRecord);
	    	break;
	
		  	case 'forumtrack' :
	    		return $this->saveForumTrackData($dataRecord);
	    	break;			
			
	  		case 'polls' :
	    	break;
		}
		
		return FALSE;
  }


  // Return the next record as an array. All data has been converted to the appropriate E107 formats
  // Return FALSE if no more data
  // Its passed a record initialised with the default values
	function getNext($initial,$mode='db')
	{
		if($mode == 'db')
		{
			$result = $this->ourDB->fetch();
		}
		else
		{
			$result = current($this->arrayData);
			next($this->arrayData);
		}
		
		
		if (!$result) return FALSE;
		switch($this->currentTask)
		{
	  		case 'users' :
				return $this->copyUserData($initial, $result);
			break;

	  		case 'userclass' :
				return $this->copyUserClassData($initial, $result);
			break;

			case 'news' :
				return $this->copyNewsData($initial, $result);
	  		break;

			case 'newscategory' :
				return $this->copyNewsCategoryData($initial, $result);
	  		break;

			case 'page' :
				return $this->copyPageData($initial, $result);
	  		break;

			case 'pagechapter' :
				return $this->copyPageChapterData($initial, $result);
	  		break;

			case 'links' :
				return $this->copyLinksData($initial, $result);
	  		break;

			case 'media' :
				return $this->copyMediaData($initial, $result);
	  		break;
						
	  		case 'forum' :
				return $this->copyForumData($initial, $result);
	  		break; 
			
			case 'forumthread' :
				return $this->copyForumThreadData($initial, $result);
	  		break;
				
	  		case 'forumpost' :
				return $this->copyForumPostData($initial, $result);
	  		break;
			
			case 'forumtrack' :
				return $this->copyForumTrackData($initial, $result);
	  		break;
		  
	  		case 'polls' :
	  		break;
		  
	  		
		}

    	return FALSE;
	}


	// Called to signal that current task is complete; tidy up as required
	function endQuery()
	{
		$this->currentTask = '';
	}


	// Empty functions which descendants can inherit from

	function init()
	{
		return;
	}
	
		
	function copyUserData(&$target, &$source)
	{
		return $target;
	}

	function copyUserClassData(&$target, &$source)
	{
		return $target;
	}
	
	function copyNewsData(&$target, &$source)
	{
		return $target;
	}

	function copyNewsCategoryData(&$target, &$source)
	{
		return $target;
	}
	
	function copyPageData(&$target, &$source)
	{
		return $target;
	}

	function copyPageChapterData(&$target, &$source)
	{
		return $target;
	}

	function copyLinksData(&$target, &$source)
	{
		return $target;
	}
	
	function copyMediaData(&$target, &$source)
	{
		return $target;
	}
	
	function copyForumData(&$target, &$source)
	{
		return $target;
	}
	
	function copyForumPostData(&$target, &$source)
	{
		return $target;
	}
	
	function copyForumThreadData(&$target, &$source)
	{
		return $target;
	}
	
	function copyForumTrackData(&$target, &$source)
	{
		return $target;
	}

	/**
	 * Extensions this importer is willing to write, keyed by the constant
	 * getimagesize() reports. Anything else is refused rather than stored under
	 * a name nobody verified.
	 *
	 * WEBP is only reported from PHP 7.1, and SVG is never reported at all
	 * because getimagesize() cannot read it. An SVG carries script, so a format
	 * that cannot be verified is a format this does not import.
	 *
	 * BMP and ICO are here because e107's own media tables carry them and
	 * getimagesize() reports both: refusing a format the site already accepts
	 * would narrow the importer for no gain.
	 *
	 * @return array
	 */
	protected function importableImageTypes()
	{
		$types = array(
			IMAGETYPE_GIF  => 'gif',
			IMAGETYPE_JPEG => 'jpg',
			IMAGETYPE_PNG  => 'png',
			IMAGETYPE_BMP  => 'bmp',
			IMAGETYPE_ICO  => 'ico',
		);

		if(defined('IMAGETYPE_WEBP'))
		{
			$types[IMAGETYPE_WEBP] = 'webp';
		}

		return $types;
	}

	/**
	 * The extension a file's own bytes call for.
	 *
	 * @param string $path absolute path of a downloaded file
	 * @return string|false extension without a dot, or false when the bytes are
	 *                      not an image this importer stores
	 */
	protected function verifiedImageExtension($path)
	{
		if(!is_file($path) || filesize($path) < 1)
		{
			return false;
		}

		$info = @getimagesize($path);
		$types = $this->importableImageTypes();

		if(empty($info[2]) || !isset($types[$info[2]]))
		{
			return false;
		}

		return $types[$info[2]];
	}

	/**
	 * The stored name without its extension.
	 *
	 * Everything the remote host chose is dropped except the stem of the path,
	 * which is kept only so an administrator can recognise the file later. A
	 * digest of the whole address follows it, because a stem is not unique: one
	 * feed carrying 2020/01/header.jpg and 2021/05/header.jpg has two images and
	 * has to end up with two files.
	 *
	 * The same address always gives the same answer, which is what lets a second
	 * run of a feed recognise what it already holds.
	 *
	 * @param string $url    address the image came from
	 * @param string $prefix caller's own leader, e.g. the member id an avatar
	 *                       belongs to; sanitised as well, since a name assembled
	 *                       from two sources is only as safe as the looser one
	 * @return string
	 */
	protected function localImageBase($url, $prefix = '')
	{
		$path = parse_url($url, PHP_URL_PATH);
		$name = empty($path) ? '' : basename($path);
		$name = preg_replace('/\.[^.]*$/', '', $name);
		$name = trim(preg_replace('/[^A-Za-z0-9_-]/', '_', $name), '_');
		$name = substr($name, 0, 60);

		if($name === '')
		{
			$name = 'image';
		}

		return preg_replace('/[^A-Za-z0-9_-]/', '', $prefix).$name.'_'.substr(md5($url), 0, 10);
	}

	/**
	 * The name a downloaded image is stored under. The extension is the
	 * caller's, taken from the bytes.
	 *
	 * @param string $url    address the image came from
	 * @param string $ext    verified extension, without a dot
	 * @param string $prefix caller's own leader
	 * @return string
	 */
	protected function localImageName($url, $ext, $prefix = '')
	{
		return $this->localImageBase($url, $prefix).'.'.$ext;
	}

	/**
	 * The name this address is already stored under, if it is.
	 *
	 * Asked before the download, so a feed read twice does not fetch every
	 * image it already holds a second time. The extension is not known until
	 * the bytes arrive, so each one this importer writes is tried in turn.
	 *
	 * @param string $dir    absolute directory, with a trailing slash
	 * @param string $url    address the image came from
	 * @param string $prefix caller's own leader
	 * @return string|false
	 */
	protected function storedImageName($dir, $url, $prefix = '')
	{
		$base = $this->localImageBase($url, $prefix);

		foreach($this->importableImageTypes() as $ext)
		{
			if(is_file($dir.$base.'.'.$ext))
			{
				return $base.'.'.$ext;
			}
		}

		return false;
	}

	/**
	 * Download a remote file into the temporary directory.
	 *
	 * Its own method so a test can put bytes in place without a network, and so
	 * every importer fetches through the one call that {@see e_file::isUrlSafe()}
	 * guards.
	 *
	 * @param string $url
	 * @param string $localName file name relative to e_TEMP
	 * @return boolean
	 */
	protected function fetchRemoteFile($url, $localName)
	{
		return (bool) e107::getFile()->getRemoteFile($url, $localName, 'temp');
	}

	/**
	 * Fetch an image named by a feed and store it, under a name and an extension
	 * this side decided.
	 *
	 * The remote host chooses the URL, the Content-Type header and the payload.
	 * None of the three names the file: an extension a feed asks for is an
	 * extension the web server may hand to an interpreter, and e_MEDIA is inside
	 * the document root. The bytes are staged in e_TEMP, which is not served,
	 * and moved into place only once getimagesize() has said what they are.
	 *
	 * @param string $url    image address taken from the feed
	 * @param string $dir    absolute directory to store into, with a trailing slash
	 * @param string $prefix leader for the stored name, chosen by the caller
	 * @return string|false the stored file name, or false when nothing was stored
	 */
	protected function importRemoteImage($url, $dir, $prefix = '')
	{
		$stored = $this->storedImageName($dir, $url, $prefix);

		if($stored !== false)
		{
			return $stored;
		}

		$staged = 'import_'.md5($url.'.'.microtime()).'.tmp';

		if(!$this->fetchRemoteFile($url, $staged))
		{
			@unlink(e_TEMP.$staged);

			return false;
		}

		$ext = $this->verifiedImageExtension(e_TEMP.$staged);

		if($ext === false)
		{
			@unlink(e_TEMP.$staged);

			return false;
		}

		$name = $this->localImageName($url, $ext, $prefix);

		if(file_exists($dir.$name))
		{
			@unlink(e_TEMP.$staged);

			return $name;
		}

		if(!rename(e_TEMP.$staged, $dir.$name))
		{
			@unlink(e_TEMP.$staged);

			return false;
		}

		return $name;
	}

	/**
	 * @param $source
	 * @param $target
	 */
	public function debug($source,$target)
	{
		echo "<table style='width:100%'>
			<tr><th>Source CMS</th><th>Target e107</th></tr>
				<tr>
				<td style='vertical-align:top'>".$this->renderTable($source)."</td>
				<td style='vertical-align:top'>".$this->renderTable($target)."</td>
				</tr>
			</table>";

	}

	private function renderTable($source)
	{
		$text = "<table class='table table-striped table-bordered'>
			<tr><th>Field</th><th>Data</th></tr>";

		foreach($source as $k=>$v)
		{
				$text .= "<tr>
					<td style='width:50%;'>".$k."</td>
					<td>".htmlentities($v)."</td>
				</tr>";


		}

		$text .= "
			</table>
		";

		return $text;

	}
	
	//===========================================================
	//				UTILITY ROUTINES
	//===========================================================
	
	// Process all bbcodes in the passed value; return the processed string.
	// Works recursively
	// Start by assembling matched pairs. Then map and otherwise process as required.
	// Divide the value into five bits:
	//      Preamble - up to the identified bbcode (won't contain bbcode)
	//		BBCode start code
	//		Inner - text between the two bbcodes (may contain another bbcode)
	//		BBCode end code
	//		Trailer - remaining unprocessed text (may contain more bbcodes)
	// (Note: preg_split might seem obvious, but doesn't pick out the actual codes
	function proc_bb($value, $options = "", $maptable = null)
	{
	  $bblower = (strpos($options,'bblower') !== FALSE) ? TRUE : FALSE;		// Convert bbcode to lower case
	  $bbphpbb = (strpos($options,'phpbb') !== FALSE) ? TRUE : FALSE;		// Strip values as phpbb
	  $nextchar = 0;
	  $loopcount = 0;
	 
	  while ($nextchar < strlen($value))
	  {
	    $firstbit = '';
	    $middlebit = '';
	    $lastbit = '';
	    $loopcount++;
		if ($loopcount > 10) return 'Max depth exceeded';
	    unset($bbword);
	    $firstcode = strpos($value,'[',$nextchar);
	    if ($firstcode === FALSE) return $value;   	// Done if no square brackets
	    $firstend = strpos($value,']',$firstcode);
	    if ($firstend === FALSE) return $value;		// Done if no closing bracket
	    $bbword = substr($value,$firstcode+1,$firstend - $firstcode - 1);	// May need to process this more if parameter follows
		$bbparam = '';
		$temp = strpos($bbword,'=');
		if ($temp !== FALSE)
		{
		  $bbparam = substr($bbword,$temp);
		  $bbword  = substr($bbword,0,-strlen($bbparam));
		}
	    if (($bbword) && ($bbword == trim($bbword)))
	    {
	      $laststart = strpos($value,'[/'.$bbword,$firstend);    // Find matching end
		  $lastend   = strpos($value,']',$laststart);
		  if (($laststart === FALSE) || ($lastend === FALSE))
		  {   //  No matching end character
		    $nextchar = $firstend;	// Just move scan pointer along 
		  }
		  else
		  {  // Got a valid bbcode pair here
		    $firstbit = '';
		    if ($firstcode > 0) $firstbit = substr($value,0,$firstcode);
		    $middlebit = substr($value,$firstend+1,$laststart - $firstend-1);
		    $lastbit = substr($value,$lastend+1,strlen($value) - $lastend);
		    // Process bbcodes here
			if ($bblower) $bbword = strtolower($bbword);
			if ($bbphpbb && (strpos($bbword,':') !== FALSE)) $bbword = substr($bbword,0,strpos($bbword,':'));
			if ($maptable)
			{   // Do mapping
			  if (array_key_exists($bbword,$maptable)) $bbword = $maptable[$bbword];
			}
		    $bbbegin = '['.$bbword.$bbparam.']';
		    $bbend   = '[/'.$bbword.']';
		    return $firstbit.$bbbegin.$this->proc_bb($middlebit,$options,$maptable).$bbend.$this->proc_bb($lastbit,$options,$maptable);
		  }
	    }
		else
		{
		  $nextchar = $firstend+1;
		}
	  }  //endwhile;
	  
	}

}


