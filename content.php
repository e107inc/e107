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
|     $Source: /cvs_backup/e107_0.8/content.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:02 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
	
require_once("class2.php");

//##### REDIRECTION MANAGEMENT -----------------------------------------------------------------------------
if($content_install = $sql -> db_Select("plugin", "*", "plugin_path = 'content' AND plugin_installflag = '1' ")){
	//require_once($plugindir."handlers/content_class.php");
	//$aa = new content;
	
	$tmp = explode(".", e_QUERY);
	if($tmp[0]){
		//get type_id from the row with heading content, article or review
		//this will only work if the three main parents are not renamed !
		if(!$sql -> db_Select("pcontent", "content_id", "content_heading='".$tp -> toDB($tmp[0])."'")){
			header("location:".e_PLUGIN."content/content.php");
		}else{
			$row = $sql -> db_Fetch();
		}
	}
	if ($tmp[0] == "content") {
		if (is_numeric($tmp[1])) {						//content view
			
			$tmp[1] = intval($tmp[1]);
			header("location:".e_PLUGIN."content/content.php?content.".$tmp[1]);

		}else{											//content recent page
			header("location:".e_PLUGIN."content/content.php?recent.".$row['content_id']);
		}

	}elseif ($tmp[0] == "article" || $tmp[0] == "review") {

		if (is_numeric($tmp[1])) {						//item view
			$tmp[1] = intval($tmp[1]);
			header("location:".e_PLUGIN."content/content.php?content.".$tmp[1]);
		
		}elseif($tmp[1] == "cat" ) {					//category page
			
			if(!$tmp[2] || $tmp[2] == "0") {			//all categories
				//$mainparent = $aa -> getMainParent($tmp[2]);
				//header("location:".e_PLUGIN."content/content.php?cat.list.".$mainparent."");
				header("location:".e_PLUGIN."content/content.php");
			
			}else{										//view category
				header("location:".e_PLUGIN."content/content.php?cat.".$tmp[2]);
			}
		
		} else {										//recent page
			header("location:".e_PLUGIN."content/content.php?recent.".$row['content_id']);
		}
	}else{												//redirect to new content main page
		header("location:".e_PLUGIN."content/content.php");
	}
}
//##### END REDIRECTION MANAGEMENT -------------------------------------------------------------------------

?>