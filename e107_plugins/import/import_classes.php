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
	  		$this->DBPrefix = "`".$database."`.".$prefix;
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


