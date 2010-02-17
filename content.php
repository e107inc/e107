<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/content.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
	
require_once("class2.php");

//##### REDIRECTION MANAGEMENT -----------------------------------------------------------------------------
if($content_install = varset($pref['plug_installed']['content']))
{
	//require_once($plugindir."handlers/content_class.php");
	//$aa = new content;
	
	$tmp = explode(".", e_QUERY);
	if($tmp[0]){
		//get type_id from the row with heading content, article or review
		//this will only work if the three main parents are not renamed !
		if(!$sql -> db_Select("pcontent", "content_id", "content_heading='".$tp -> toDB($tmp[0])."'")){
			header("location:".e_PLUGIN."content/content.php");
			exit;
		}else{
			$row = $sql -> db_Fetch();
		}
	}
	if ($tmp[0] == "content") {
		if (is_numeric($tmp[1])) {						//content view
			
			$tmp[1] = intval($tmp[1]);
			header("location:".e_PLUGIN."content/content.php?content.".$tmp[1]);
			exit;

		}
		else
		{											//content recent page
			header("location:".e_PLUGIN."content/content.php?recent.".$row['content_id']);
			exit;
		}

	}elseif ($tmp[0] == "article" || $tmp[0] == "review") {

		if (is_numeric($tmp[1])) {						//item view
			$tmp[1] = intval($tmp[1]);
			header("location:".e_PLUGIN."content/content.php?content.".$tmp[1]);
			exit;
		}
		elseif($tmp[1] == "cat" ) {					//category page
			
			if(!$tmp[2] || $tmp[2] == "0") {			//all categories
				//$mainparent = $aa -> getMainParent($tmp[2]);
				//header("location:".e_PLUGIN."content/content.php?cat.list.".$mainparent."");
				header("location:".e_PLUGIN."content/content.php");
				exit;
			
			}else{										//view category
				header("location:".e_PLUGIN."content/content.php?cat.".$tmp[2]);
				exit;
			}
		
		}
		else
		{										//recent page
			header("location:".e_PLUGIN."content/content.php?recent.".$row['content_id']);
			exit;
		}
	}
	else
	{												//redirect to new content main page
		header("location:".e_PLUGIN."content/content.php");
		exit;
	}
}
header("location:".e_BASE."index.php");
exit;
//##### END REDIRECTION MANAGEMENT -------------------------------------------------------------------------
?>