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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/userclass_class.php,v $
|     $Revision: 1.35 $
|     $Date: 2009-07-08 06:58:00 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/


/*
This class handles everything a user needs. Admin functions inherit from it.
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER.'arraystorage_class.php');

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_userclass.php');


/*
For info:
define("e_UC_PUBLIC", 0);
define("e_UC_MAINADMIN", 250);
define("e_UC_READONLY", 251);
define("e_UC_GUEST", 252);
define("e_UC_MEMBER", 253);
define("e_UC_ADMIN", 254);
define("e_UC_NOBODY", 255);
*/
define('e_UC_ADMINMOD',249);
define('e_UC_MODS',248);
define('e_UC_NEWUSER',247);					// Users in 'probationary' period
define('e_UC_SPECIAL_BASE',245);			// Assign class IDs 245 and above for fixed/special purposes
define('e_UC_SPECIAL_END',255);				// Highest 'special' class

define('UC_CLASS_ICON_DIR','userclasses/');		// Directory for userclass icons
define('UC_ICON_DIR',e_IMAGE.'generic/');		// Directory for the icons used in the admin tree displays

define('e_UC_BLANK','-1');
define('UC_TYPE_STD', '0');
define('UC_TYPE_GROUP', '1');

define('UC_CACHE_TAG', 'nomd5_classtree');

class user_class
{
	var $class_tree;					// Simple array, filled with current tree. Additional field class_children is an array of child user classes (by ID)
	var $class_parents;					// Array of class IDs of 'parent' (i.e. top level) classes

	var  $fixed_classes = array();		// The 'predefined' classes of 0.7
	var  $text_class_link = array();	// List of 'core' user classes and the related constants

	var $sql_r;						// We'll use our own DB to avoid interactions
	var $isAdmin;						// Set true if we're an instance of user_class_admin

	// Constructor
	function user_class()
	{

	  $this->sql_r = new db;
	  $this->isAdmin = FALSE;

	  $this->fixed_classes = array(e_UC_PUBLIC => UC_LAN_0,
							e_UC_GUEST => UC_LAN_1,
							e_UC_NOBODY => UC_LAN_2,
							e_UC_MEMBER => UC_LAN_3,
							e_UC_ADMIN => UC_LAN_5,
							e_UC_MAINADMIN => UC_LAN_6,
							e_UC_READONLY => UC_LAN_4,
							e_UC_NEWUSER => UC_LAN_9
							);

	  $this->text_class_link = array('public' => e_UC_PUBLIC, 'guest' => e_UC_GUEST, 'nobody' => e_UC_NOBODY, 'member' => e_UC_MEMBER,
									'admin' => e_UC_ADMIN, 'main' => e_UC_MAINADMIN, 'readonly' => e_UC_READONLY, 'new' => e_UC_NEWUSER);

      $this->readTree(TRUE);			// Initialise the classes on entry
	}


	/*
	  Ensure the tree of userclass data is stored in our object.
	  Only read if its either not present, or the $force flag is set
	*/
  function readTree($force = FALSE)
  {
    if (isset($this->class_tree) && count($this->class_tree) && !$force) return $this->class_tree;

	global $e107;
	$this->class_tree = array();
	$this->class_parents = array();

	$array = new ArrayData;
	if ($temp = $e107->ecache->retrieve_sys(UC_CACHE_TAG))
	{
		$this->class_tree = $array->ReadArray($temp);
		unset($temp);
	}
	else
	{
		$this->sql_r->db_Select("userclass_classes", '*', "ORDER BY userclass_parent", 'nowhere');		// The order statement should give a consistent return

		while ($row = $this->sql_r->db_Fetch(MYSQL_ASSOC))
		{
			$this->class_tree[$row['userclass_id']] = $row;
			$this->class_tree[$row['userclass_id']]['class_children'] = array();		// Create the child array in case needed
		}


		// Add in any fixed classes that aren't already defined
		foreach ($this->fixed_classes as $c => $d)
		{
//			if (!isset($this->class_tree[$c]) && ($c != e_UC_PUBLIC))
			if (!isset($this->class_tree[$c]))
			{
				switch ($c)
				{
					case e_UC_ADMIN :
					case e_UC_MAINADMIN :
						$this->class_tree[$c]['userclass_parent'] = e_UC_NOBODY;
						break;
					case e_UC_NEWUSER :
						$this->class_tree[$c]['userclass_parent'] = e_UC_MEMBER;
						break;
					default :
						$this->class_tree[$c]['userclass_parent'] = e_UC_PUBLIC;
				}
				$this->class_tree[$c]['userclass_id'] = $c;
				$this->class_tree[$c]['userclass_name'] = $d;
				$this->class_tree[$c]['userclass_description'] = 'Fixed class';
				$this->class_tree[$c]['userclass_visibility'] = e_UC_PUBLIC;
				$this->class_tree[$c]['userclass_editclass'] = e_UC_MAINADMIN;
				$this->class_tree[$c]['userclass_accum'] = $c;
				$this->class_tree[$c]['userclass_type'] = UC_TYPE_STD;
			}
		}

		$userCache = $array->WriteArray($this->class_tree, FALSE);
		$e107->ecache->set_sys(UC_CACHE_TAG,$userCache);
		unset($userCache);
	}


	// Now build the tree.
	// There are just two top-level classes - 'Everybody' and 'Nobody'
	$this->class_parents[e_UC_PUBLIC] = e_UC_PUBLIC;
	$this->class_parents[e_UC_NOBODY] = e_UC_NOBODY;
	foreach ($this->class_tree as $uc)
	{
/*
		if ($uc['userclass_parent'] == e_UC_PUBLIC)
		{	// Note parent (top level) classes
			$this->class_parents[$uc['userclass_id']] = $uc['userclass_id'];
		}
		else
*/
		if (($uc['userclass_id'] != e_UC_PUBLIC) && ($uc['userclass_id'] != e_UC_NOBODY))
		{
//			if (!array_key_exists($uc['userclass_parent'],$this->class_tree))
			if (!isset($this->class_tree[$uc['userclass_parent']]))
			{
				echo "Orphaned class record: ID=".$uc['userclass_id']." Name=".$uc['userclass_name']."  Parent=".$uc['userclass_parent']."<br />";
			}
			else
			{	// Add to array
				$this->class_tree[$uc['userclass_parent']]['class_children'][] = $uc['userclass_id'];
			}
		}
	}
  }



	// Given the list of 'base' classes a user belongs to, returns a comma separated list including ancestors. Duplicates stripped
	function get_all_user_classes($start_list)
	{
		$is = array();
		$start_array = explode(',', $start_list);
		foreach ($start_array as $sa)
		{	// Merge in latest values - should eliminate duplicates as it goes
			if (isset($this->class_tree[$sa]))
			{
				$is = array_merge($is,explode(',',$this->class_tree[$sa]['userclass_accum']));
			}
		}
		return implode(',',array_unique($is));
	}


	// Returns a list of user classes which can be edited by the specified classlist (defaults to current user's classes)
	function get_editable_classes($class_list = USERCLASS_LIST, $asArray = FALSE)
	{
		$ret = array();
		$blockers = array(e_UC_PUBLIC => 1, e_UC_READONLY => 1, e_UC_MEMBER => 1, e_UC_NOBODY => 1, e_UC_GUEST => 1, e_UC_NEWUSER => 1);
		$possibles = array_flip(explode(',',$class_list));
		unset($possibles[e_UC_READONLY]);
		foreach ($this->class_tree as $uc => $uv)
		{
			if (!isset($blockers[$uc]))
			{
				$ec = $uv['userclass_editclass'];
				if (isset($possibles[$ec]))
				{
//					echo $uc."  {$ec}  {$uv['userclass_description']}<br />";
					$ret[] = $uc;
				}
			}
		}
		if ($asArray) { return $ret; }
		return implode(',',$ret);
	}



	// Combines the selected editable classes into the main class list for a user.
	// $combined - the complete list of current class memberships
	// $possible - the classes which are being edited
	// $actual - the actual membership of the editable classes
	// All classes may be passed as comma-separated lists or arrays
	function mergeClassLists($combined, $possible, $actual, $asArray = FALSE)
	{
		if (!is_array($combined)) { $combined = explode(',',$combined);  }
		if (!is_array($possible)) { $possible = explode(',',$possible);  }
		if (!is_array($actual)) 	{ $actual = explode(',',$actual);  }
		$combined = array_flip($combined);
		foreach ($possible as $p)
		{
			if (in_array($p,$actual))
			{	// Class must be in final array
				$combined[$p] = 1;
			}
			else
			{
				unset($combined[$p]);
			}
		}
		$combined = array_keys($combined);
		if ($asArray) { return $combined; }
		return implode(',', $combined);
	}


	function stripFixedClasses($inClasses)
	{
		$asArray = TRUE;
		if (!is_array($inClasses))
		{
			$asArray = FALSE;
			$inClasses = explode(',',$inClasses);
		}
		$inClasses = array_flip($inClasses);
		foreach ($this->fixed_classes as $k => $v)
		{
			if (isset($inClasses[$k])) { unset($inClasses[$k]); }
		}
		$inClasses = array_keys($inClasses);
		if ($asArray) { return ($inClasses); }
		return implode(',',$inClasses);
	}


  // Given a comma separated list, returns the minimum number of class memberships required to achieve this (i.e. strips classes 'above' another in the tree)
  // Requires the class tree to have been initialised
  function normalise_classes($class_list)
  {
    $drop_classes = array();
	$old_classes = explode(',',$class_list);
	foreach ($old_classes as $c)
	{  // Look at our parents (which are in 'userclass_accum') - if any of them are contained in old_classes, we can drop them.
	  $tc = array_flip(explode(',',$this->class_tree[$c]['userclass_accum']));
	  unset($tc[$c]);		// Current class should be in $tc anyway
	  foreach ($tc as $tc_c => $v)
	  {
	    if (in_array($tc_c,$old_classes))
		{
		  $drop_classes[] = $tc_c;
		}
	  }
	}
	$new_classes = array_diff($old_classes,$drop_classes);
	return implode(',',$new_classes);
  }




	/* Generate a dropdown list of user classes from which to select - virtually as r_userclass()
		$optlist allows selection of the classes to be shown in the dropdown. All or none can be included, separated by comma. Valid options are:
			public
			guest
			nobody
			member
			readonly
			admin
			main - main admin
			new - new users
			classes - shows all classes
			matchclass - if 'classes' is set, this option will only show the classes that the user is a member of
			language - list of languages.
			blank - puts an empty option at the top of select dropdowns

			filter - only show those classes where member is in a class permitted to view them - i.e. as the new 'visible to' field - added for 0.8
			force  - show all classes (subject to the other options, including matchclass) - added for 0.8

		$extra_js - can add JS handlers (e.g. 'onclick', 'onchange') if required

		[ $mode parameter of r_userclass() removed - $optlist is more flexible) ]
*/
	function uc_dropdown($fieldname, $curval = 0, $optlist = "", $extra_js = '')
	{
		global $pref;

		$show_classes = $this->uc_required_class_list($optlist);

	  $text = '';
	  foreach ($show_classes as $k => $v)
	  {
		if ($k == e_UC_BLANK)
		{
		  $text .= "<option value=''>&nbsp;</option>\n";
		}
		else
		{
		  $s = ($curval == $k && $curval !== '') ?  "selected='selected'" : "";
		  $text .= "<option  value='".$k."' ".$s.">".$v."</option>\n";
		}
	  }

	  if (strpos($optlist, "language") !== FALSE && $pref['multilanguage'])
	  {
		$text .= "<optgroup label=' ------ ' />\n";
		$tmpl = explode(",",e_LANLIST);
        foreach($tmpl as $lang)
		{
		  $s = ($curval == $lang) ?  " selected='selected'" : "";
          $text .= "<option  value='$lang' ".$s.">".$lang."</option>\n";
		}
	  }

	  // Only return the select box if we've ended up with some options
	  if ($text) $text = "<select class='tbox select' name='{$fieldname}' {$extra_js}>\n".$text."</select>\n";
	  return $text;
	}




	/*
	  Generate an ordered array  classid=>classname - used for dropdown and check box lists
	  If $just_ids is TRUE, array value is just '1'
	*/
	function uc_required_class_list($optlist = '', $just_ids = FALSE)
	{
	  $ret = array();
	  if (!$optlist) $optlist = 'public,guest,nobody,member,classes';		// Set defaults to simplify ongoing processing

	  if ($optlist == 'editable')
	  {
		$temp = array_flip(explode(',',$this->get_editable_classes()));
		if ($just_ids) return $temp;
		foreach ($temp as $c => $t)
		{
		  $temp[$c] = $this->class_tree[$c]['userclass_name'];
		}
		return $temp;
	  }

	  $opt_arr = explode(',',$optlist);
	  foreach ($opt_arr as $k => $v)
	  {
	    $opt_arr[$k] = trim($v);
	  }

	  $opt_arr = array_flip($opt_arr);		// This also eliminates duplicates which could arise from applying the other options, although shouldn't matter

	  if (isset($opt_arr['force'])) unset($opt_arr['filter']);

	  if (isset($opt_arr['blank']))
	  {
		$ret[e_UC_BLANK] = 1;
	  }

	  // Do the 'fixed' classes next
	  foreach ($this->text_class_link as $k => $v)
	  {
//		if (isset($opt_arr[$k]) || isset($opt_arr['force']))
		if (isset($opt_arr[$k]))
		{
			$ret[$v] = $just_ids ? '1' : $this->fixed_classes[$v];
	    }
	  }

	  // Now do the user-defined classes
	  if (isset($opt_arr['classes']) || isset($opt_arr['force']))
	  {	// Display those classes the user is allowed to:
		//	Main admin always sees the lot
		//	a) Mask the 'fixed' user classes which have already been processed
		//  b) Apply the visibility option field ('userclass_visibility')
		//  c) Apply the matchclass option if appropriate
		foreach($this->class_tree as $uc_id => $row)
		{
			if (!array_key_exists($uc_id,$this->fixed_classes)
			&& (   getperms("0")
				|| (
					(!isset($optlist["matchclass"]) || check_class($uc_id))
					&& (!isset($optlist["filter"]) || check_class($row['userclass_visibility']))
				   )
				)
				)
			{
			  $ret[$uc_id] = $just_ids ? '1' : $this->class_tree[$uc_id]['userclass_name'];
			}
		}
	  }
/* Above loop slightly changes the display order of earlier code versions.
	If readonly must be last (after language), delete it from the $text_class_link array, and uncomment the following code

	if (isset($opt_arr['readonly']))
	{
	  $ret[e_UC_READONLY] = $this->class_tree[e_UC_READONLY]['userclass_description'];
	}
*/
		return $ret;
	}



	/*
	Very similar to r_userclass, but returns a list of check boxes. Doesn't encapsulate it.
	$fieldname is the name for the array of checkboxes
	$curval is a comma separated list of class IDs for boxes which are checked.
	$optlist as for uc_dropdown
	if $showdescription is TRUE, appends the class description in brackets
	*/
	function uc_checkboxes($fieldname, $curval='', $optlist = '', $showdescription = FALSE)
	{
	  global $pref;
	  $show_classes = $this->uc_required_class_list($optlist);

	  $curArray = explode(",", $curval);				// Array of current values
	  $ret = "";

	  foreach ($show_classes as $k => $v)
	  {
		if ($k != e_UC_BLANK)
		{
		  $c = (in_array($k,$curArray)) ?  " checked='checked'" : "";
		  if ($showdescription) $v .= " (".$this->uc_get_classdescription($k).")";
		  $ret .= "<div class='field-spacer'><input type='checkbox' class='checkbox' name='{$fieldname}[{$k}]' id='{$fieldname}-{$k}' value='{$k}'{$c} /><label for='{$fieldname}-{$k}'>".$v."</label></div>\n";
		}
	  }

	  if (strpos($optlist, "language") !== FALSE && $pref['multilanguage'])
	  {
		$ret .= "<div class='separator'><!-- --></div>\n";
		$tmpl = explode(",",e_LANLIST);
        foreach($tmpl as $lang)
		{
		  $c = (in_array($lang, $curArray)) ? " checked='checked' " : "";
          $ret .= "<div class='field-spacer'><input type='checkbox' class='checkbox' name='{$fieldname}[{$lang}]' id='{$fieldname}-{$lang}'  value='1'{$c} /><label for='{$fieldname}-{$lang}'>{$lang}</label></div>";
		}
	  }
	  return $ret;
	}




	/*
	Next two routines create an indented tree - for example within a select box or a list of check boxes.

	For each displayed element, the callback routine is called
	$treename is the name given to the elements where required
	$callback is a routine used to generate each element; there are two implemented within this class:
		select (the default) - generates the option list. Text requires to be encapsulated in a <select......./select> tag set
				- can also be used with multi-select boxes
		checkbox - generates a set of checkboxes
		Alternative callbacks can be used to achieve different layouts/styles
	$current_value is a single class number for single-select dropdown; comma separated array of class numbers for checkbox list or multi-select
	$optlist works the same as for other class displays
	*/
	function vetted_sub_tree($treename, $callback,$listnum,$nest_level,$current_value, $perms, $opt_options)
	{
		$ret = '';
		$nest_level++;
		if(isset($this->class_tree[$listnum]['class_children']))
		{
			foreach ($this->class_tree[$listnum]['class_children'] as $p)
			{
				// Looks like we don't need to differentiate between function and class calls
				if (isset($perms[$p]))
				{
					$ret .= call_user_func($callback,$treename, $p,$current_value,$nest_level, $opt_options);
				}
				$ret .= $this->vetted_sub_tree($treename, $callback,$p,$nest_level,$current_value, $perms, $opt_options);
			}
		}
		return $ret;
	}


	function vetted_tree($treename, $callback='', $current_value='', $optlist = '',$opt_options = '')
	{
		$ret = '';
		if (!$callback) $callback=array($this,'select');
		$current_value = str_replace(' ','',$current_value);				// Simplifies parameter passing for the tidy-minded

		$perms = $this->uc_required_class_list($optlist,TRUE);				// List of classes which we can display
		if (isset($perms[e_UC_BLANK]))
		{
			$ret .= call_user_func($callback,$treename, e_UC_BLANK, $current_value,0, $opt_options);
		}
		foreach ($this->class_parents as $p)
		{
			if (isset($perms[$p]))
			{
				$ret .= call_user_func($callback,$treename, $p,$current_value,0, $opt_options);
			}
			$ret .= $this->vetted_sub_tree($treename, $callback,$p,0, $current_value, $perms, $opt_options);
		}
		return $ret;
	}


  // Callback for vetted_tree - Creates the option list for a selection box
  function select($treename, $classnum, $current_value, $nest_level)
  {
	if ($classnum == e_UC_BLANK)  return "<option value=''>&nbsp;</option>\n";
//	echo "Display: {$classnum}, {$current_value}, {$nest_level}<br />";
	$tmp = explode(',',$current_value);
    $sel = in_array($classnum,$tmp) ? " selected='selected'" : '';
    if ($nest_level == 0)
	{
	  $prefix = '';
	  $style = " style='font-weight:bold; font-style: italic;'";
	}
	elseif ($nest_level == 1)
	{
	  $prefix = '&nbsp;&nbsp;';
	  $style = " style='font-weight:bold'";
	}
	else
	{
	  $prefix = '&nbsp;&nbsp;'.str_repeat('--',$nest_level-1).'>';
	  $style = '';
	}
    return "<option value='{$classnum}'{$sel}{$style}>".$prefix.$this->class_tree[$classnum]['userclass_name']."</option>\n";
  }


	// Callback for vetted_tree - displays indented checkboxes with class name only
  function checkbox($treename, $classnum, $current_value, $nest_level)
  {
	if ($classnum == e_UC_BLANK)  return '';
	$tmp = explode(',',$current_value);
	$chk = in_array($classnum, $tmp) ? " checked='checked'" : '';
    if ($nest_level == 0)
	{
	  $style = " style='font-weight:bold'";
	}
	else
	{
	  $style = " style='text-indent:".(1.2*$nest_level)."em'";
	}
    return "<div {$style}><input type='checkbox' class='checkbox' name='{$treename}[]' id='{$treename}_{$classnum}' value='{$classnum}'{$chk} />".$this->class_tree[$classnum]['userclass_name']."</div>\n";
  }


	// Callback for vetted_tree - displays indented checkboxes with class name, and description in brackets
  function checkbox_desc($treename, $classnum, $current_value, $nest_level)
  {
	if ($classnum == e_UC_BLANK)  return '';
	$tmp = explode(',',$current_value);
	$chk = in_array($classnum, $tmp) ? " checked='checked'" : '';
    if ($nest_level == 0)
	{
	  $style = " style='font-weight:bold'";
	}
	else
	{
	  $style = " style='text-indent:".(1.2*$nest_level)."em'";
	}
    return "<div {$style}><input type='checkbox' class='checkbox' name='{$treename}[]' id='{$treename}_{$classnum}' value='{$classnum}'{$chk} />".$this->class_tree[$classnum]['userclass_name'].'  ('.$this->class_tree[$classnum]['userclass_description'].")</div>\n";
  }




	/*
	Return array of all classes, limited according to membership of the userclass_visibility field if $filter is set.
		Index field - userclass_id
		Data fields - userclass_name, userclass_description, userclass_editclass
	*/
	function uc_get_classlist($filter = FALSE)
	{
	  $ret = array();
	  $this->readTree(FALSE);				// Make sure we have data
	  foreach ($this->class_tree as $k => $v)
	  {
	    if (!$filter || check_class($filter))
		{
		  $ret[$k] = array('userclass_name' => $v, 'userclass_description' => $v['userclass_description'], 'userclass_editclass' => $v['userclass_editclass']);
		}
	  }
	  return $ret;
	}


	function uc_get_classname($id)
	{
	  if (isset($this->class_tree[$id]))
	  {
	    return $this->class_tree[$id]['userclass_name'];
	  }
	  if (isset($this->fixed_classes[$id]))
	  {
	    return $this->fixed_classes[$id];
	  }
	  return '';
	}


	function uc_get_classdescription($id)
	{
	  if (isset($this->class_tree[$id]))
	  {
	    return $this->class_tree[$id]['userclass_description'];
	  }
	  if (isset($this->fixed_classes[$id]))
	  {
	    return $this->fixed_classes[$id];	// Name and description the same for fixed classes
	  }
	  return '';
	}

	function uc_get_classicon($id)
	{
	  if (isset($this->class_tree[$id]))
	  {
	    return $this->class_tree[$id]['userclass_icon'];
	  }
	  return '';
	}

	function ucGetClassIDFromName($name)
	{
		$this->readTree();
		// We have all the info - can just search the array
		foreach ($this->class_tree as $uc => $info)
		{
			if ($info['userclass_name'] == $name)
			{
				return $uc;
			}
		}
		return FALSE;		// not found
	}


	// Utility to remove a specified class ID from the default comma-separated list
	function ucRemove($classID, $from, $asArray = FALSE)
	{
		$tmp = array_flip(explode(',',$from));
		if (isset($tmp[$classID]))
		{
			unset($tmp[$classID]);
		}
		$tmp = array_keys($tmp);
		if ($asArray) { return $tmp; }
		if (count($tmp) == 0) { return ''; }
		return implode(',',$tmp);
	}


	// Utility to add a specified class ID to the default comma-separated list
	function ucAdd($classID, $to, $asArray = FALSE)
	{
		$tmp = array_flip(explode(',',$to));
		$tmp[$classID] = 1;
		$tmp = array_keys($tmp);
		if ($asArray) { return $tmp; }
		return implode(',',$tmp);
	}


	/*
	Return all users in a particular class or set of classes.
	$classlist is a comma separated list of classes - if the 'predefined' classes are required, they must be included. No spaces allowed
	$field_list is used to select the returned fields ($user_id is the primary index)

	****** Can be verrrrryyyy slow - has to scan the whole user database at present ******

	********* NOT TESTED **********

	***** NOT SURE WHETHER THIS IS REALLY A USER OR A USER CLASS FUNCTION *****
	*/
	function get_users_in_class($classlist, $field_list = 'user_name, user_loginname', $include_ancestors = FALSE, $order_by = 'user_id')
	{
	  $ret = array();
	  if ($include_ancestors) $classlist = $this->get_all_user_classes($classlist);
	  $class_regex = "(^|,)(".str_replace(' ','',str_replace(",", "|", $classlist)).")(,|$)";
	  $qry = "SELECT 'user_id,{$field_list}' FROM `user` WHERE user_class REGEXP '{$class_regex}' ORDER BY '{$order_by}'";
	  if ($this->sql_r->db_Select_gen($qry))
	  {
	    while ($row = $this->sql_r->db_Fetch(MYSQL_ASSOC))
		{
		  $ret[$row['user_id']] = $row;
		}
	  }
	  return $ret;
	}
}


//========================================================================
//			Functions from previous userclass_class handler
//========================================================================
// Implemented for backwards compatibility/convenience

/*
With $optlist you can now specify which classes are shown in the dropdown.
All or none can be included, separated by comma (or whatever).
Valid options are:
public
guest
nobody
member
readonly
admin
main - main admin
classes - shows all classes
matchclass - if 'classes' is set, this option will only show the classes that the user is a member of
language - list of languages.

filter - only show those classes where member is in a class permitted to view them - i.e. as the new 'visible to' field
force  - show all classes (subject to the other options, including matchclass)

$mode = 'off' turns off listing of admin/main admin classes unless enabled in $optlist (can probably be deprecated - barely used)

*/


function r_userclass($fieldname, $curval = 0, $mode = "off", $optlist = "")
{
//  echo "Call r_userclass{$fieldname}, CV: {$curval}  opts: {$optlist}<br />";
  global $e_userclass;
  if ($mode != 'off')
  {	// Handle legacy code
	if ($optlist) $optlist .= ',';
	$optlist .= 'admin,main';
	if ($mode != 'admin') $optlist .= ',readonly';
  }
  if (!is_object($e_userclass)) $e_userclass = new user_class;
  return $e_userclass->uc_dropdown($fieldname,$curval,$optlist);
}


// Very similar to r_userclass, but returns a list of check boxes. (currently only used in newspost.php)
// $curval is a comma separated list of class IDs for boxes which are checked.
function r_userclass_check($fieldname, $curval = '', $optlist = "")
{
//  echo "Call r_userclass_check: {$curval}<br />";
  global $e_userclass;
  if (!is_object($e_userclass)) $e_userclass = new user_class;
  $ret = $e_userclass->uc_checkboxes($fieldname,$curval,$optlist);
  if ($ret) $ret = "<div class='check-block'>".$ret."</div>";
  return $ret;
}



function get_userclass_list()
{
//  echo "Call get_userclass_list<br />";
  global $e_userclass;
  if (!is_object($e_userclass)) $e_userclass = new user_class;
  return $e_userclass->uc_get_classlist();
}



function r_userclass_name($id)
{
//  echo "Call r_userclass_name<br />";
  global $e_userclass;
  if (!is_object($e_userclass)) $e_userclass = new user_class;
  return $e_userclass->uc_get_classname($id);
}





// Deprecated functions to hopefully be removed
function r_userclass_radio($fieldname, $curval = '')
{
  echo "Deprecated function r_userclass_radio not used in core - mutter if you'd like it implemented<br />";
}

//========================================================================
//			Admin Class handler - could go into separate file later
//========================================================================

class user_class_admin extends user_class
{
	var $field_list = array('userclass_name' => "varchar(100) NOT NULL default ''",
							'userclass_description' => "varchar(250) NOT NULL default ''",
							'userclass_editclass' => "tinyint(3) unsigned NOT NULL default '0'",
							'userclass_parent' => "tinyint(3) unsigned NOT NULL default '0'",
							'userclass_accum' => "varchar(250) NOT NULL default ''",
							'userclass_visibility' => "tinyint(3) unsigned NOT NULL default '0'",
							'userclass_type'		=>"tinyint(1) unsigned NOT NULL default '0'",
							'userclass_icon' => "varchar(250) NOT NULL default ''"
							);		// Note - 'userclass_id' intentionally not in this list


	// Icons to use for graphical tree display
	// First index - no children, children
	// Second index - not last item, last item
	// Third index - closed tree, open tree
	var $tree_icons  = array(	);
	var $graph_debug = FALSE;			// Shows extra info on graphical tree when TRUE


	function user_class_admin()
	{
		$this->user_class();			// Call constructor from ancestor class
		$this->isAdmin = TRUE;

	// Have to initialise the images this way - PHP4 won't take a nested array assignment in the variable list
		$this->tree_icons  = array(
						FALSE => array(			// No children
							FALSE => array(			// Not last item
							  FALSE => '',		// Closed tree - don't display
							  TRUE  => 'branch.gif'
							  )
							,
							TRUE => array(			// Last item
							  FALSE => '',		// Closed tree - don't display
							  TRUE  => 'branchbottom.gif'
						    )
						),
						TRUE => array(			// children
							FALSE => array(			// Not last item
							  FALSE => 'plus.gif',		// Closed tree - option to expand
							  TRUE  => 'minus.gif'
							  )
							,
							TRUE => array(			// Last item
							  FALSE => 'plusbottom.gif',		// Closed tree - option to expand
							  TRUE  => 'minusbottom.gif'
							))
						);
	}



	/*
	Next three routines are used to update the database after adding/deleting a class
	*/
	function calc_tree()
	{
//    echo "Calc Tree<br />";
		$this->readTree(TRUE);			// Make sure we have accurate data
		foreach ($this->class_parents as $cp)
		{
			$rights = array();
			$this->rebuild_tree($cp,$rights);		// increasing rights going down the tree
		}
	}


	// Internal function, called recursively to rebuild the permissions tree where rights increase going down the tree
	// $parent is the class number being processed.
	// $rights is the array of rights accumulated so far in the walk down the tree
	function rebuild_tree($parent, $rights)
	{
		if ($this->class_tree[$parent]['userclass_parent'] == e_UC_NOBODY)
		{
			$this->topdown_tree($parent);
			return;
		}
//		echo "Bottom up: {$parent}<br />";
		if ($this->class_tree[$parent]['userclass_type'] == UC_TYPE_GROUP)
		{
//			echo "Bottom up - skip: {$parent}<br />";
			return;			// Probably just stop here for a group class
		}
		$rights[]  = $parent;
		$imp_rights = implode(',',$rights);
		if ($this->class_tree[$parent]['userclass_accum'] != $imp_rights)
		{
			$this->class_tree[$parent]['userclass_accum'] = $imp_rights;
			if (!isset($this->class_tree[$cp]['change_flag'])) $this->class_tree[$parent]['change_flag'] = 'UPDATE';
		}
		foreach ($this->class_tree[$parent]['class_children'] as $cc)
		{
			$this->rebuild_tree($cc,$rights);		// Recursive call
		}
	}


	// Internal function, called recursively to rebuild the permissions tree where rights increase going up the tree
	// Returns an array
  function topdown_tree($our_class)
  {
//	echo "Top down: {$our_class}, Children: ".implode(',',$this->class_tree[$our_class]['class_children'])."<br />";
    $rights  = array($our_class);				// Accumulator always has rights to its own class

	if ($this->class_tree[$our_class]['userclass_type'] == UC_TYPE_GROUP) return array_merge($rights, explode(',',$this->class_tree[$our_class]['userclass_accum']));					// Stop rights accumulation at a group

    foreach ($this->class_tree[$our_class]['class_children'] as $cc)
	{
		$rights = array_merge($rights,$this->topdown_tree($cc));				// Recursive call
    }
	$rights = array_unique($rights);
	$imp_rights = implode(',',$rights);
//	echo "Class: {$our_class}  Rights: {$imp_rights}<br />";
	if ($this->class_tree[$our_class]['userclass_accum'] != $imp_rights)
	{
		$this->class_tree[$our_class]['userclass_accum'] = $imp_rights;
		$this->class_tree[$our_class]['change_flag'] = 'UPDATE';
	}
	return $rights;
  }


  function save_tree()
  {
//    echo "Save Tree<br />";
    foreach ($this->class_tree as $tree)
	{
	  if (isset($tree['change_flag']))
	  {
	    switch ($tree['change_flag'])
		{
		  case 'INSERT' :
		    $this->add_new_class($tree);
		    break;
		  case 'UPDATE' :
		    $this->save_edited_class($tree);
		    break;
		  default :
		    continue;
		}
	  }
	}
  }



	/*
	Next two routines show a text-based tree with markers to indicate the hierarchy.
	*/
  function show_sub_tree($listnum,$marker, $add_class = FALSE)
  {
    $ret = '';
    $marker = '--'.$marker;
	foreach ($this->class_tree[$listnum]['class_children'] as $p)
	{
	  $ret .= $marker.$this->class_tree[$p]['userclass_id'].':'.$this->class_tree[$p]['userclass_name'];
	  if ($add_class) $ret .= " (".$this->class_tree[$p]['userclass_accum'].")";
	  $ret .= "  Children: ".count($this->class_tree[$p]['class_children']);
	  $ret .= "<br />";
	  $ret .= $this->show_sub_tree($p,$marker, $add_class);
	}
	return $ret;
  }

  function show_tree($add_class = FALSE)
  {
    $ret = '';
    foreach ($this->class_parents as $p)
	{
	  $ret .= $this->class_tree[$p]['userclass_id'].':'.$this->class_tree[$p]['userclass_name'];
	  if ($add_class) $ret .= " (".$this->class_tree[$p]['userclass_accum'].")";
	  $ret .= "  Children: ".count($this->class_tree[$p]['class_children']);
	  $ret .= "<br />";
	  $ret .= $this->show_sub_tree($p,'>', $add_class);
	}
	return $ret;
  }




	/*
	Next two routines generate a graphical tree, including option to open/close branches
	*/
  function show_graphical_subtree($listnum, $indent_images, $is_last = FALSE)
  {
    $num_children = count($this->class_tree[$listnum]['class_children']);
	$is_open = TRUE;
	$tag_name = 'uclass_tree_'.$listnum;

	$ret = "<div class='uclass_tree' style='height: 20px'>\n";

	foreach ($indent_images as $im)
	{
	  $ret .= "<img src='".UC_ICON_DIR.$im."' alt='class icon' />";
	}
	// If this link has children, wrap the next image in a link and an expand/hide option
	if ($num_children)
	{
	  $ret .= "<span onclick=\"javascript: expandit('{$tag_name}'); expandit('{$tag_name}_p'); expandit('{$tag_name}_m')\"><img src='".UC_ICON_DIR.$this->tree_icons[TRUE][$is_last][TRUE]."' alt='class icon' id='{$tag_name}_m' />";
	  $ret .= "<img src='".UC_ICON_DIR.$this->tree_icons[TRUE][$is_last][FALSE]."' style='display:none' id='{$tag_name}_p' alt='class icon' /></span>\n";
	}
	else
	{
	  $ret .= "<img src='".UC_ICON_DIR.$this->tree_icons[FALSE][$is_last][$is_open]."' alt='class icon' />\n";
	}
	$name_line = '';
	if ($this->graph_debug) { $name_line = $this->class_tree[$listnum]['userclass_id'].":";  }
//	if ($this->graph_debug) { $name_line = varset($this->class_tree[$listnum]['userclass_id'], 'XXX').":";  }

	if ($this->class_tree[$listnum]['userclass_type'] == UC_TYPE_GROUP)
	{
		$name_line .= '<b>'.$this->class_tree[$listnum]['userclass_name'].'</b> '.UCSLAN_84;	// Highlight groups
	}
	else
	{
		$name_line .= $this->class_tree[$listnum]['userclass_name'];
	}
	if ($this->graph_debug) $name_line .= "[vis:".$this->class_tree[$listnum]['userclass_visibility'].", edit:".$this->class_tree[$listnum]['userclass_editclass']."] = ".$this->class_tree[$listnum]['userclass_accum']." Children: ".implode(',',$this->class_tree[$listnum]['class_children']);
	// Next (commented out) line gives a 'conventional' link
    $ret .= "<img src='".UC_ICON_DIR."topicon.png' alt='class icon' /><a style='text-decoration: none' class='userclass_edit' href='".e_ADMIN_ABS."userclass2.php?config.edit.{$this->class_tree[$listnum]['userclass_id']}'>".$name_line."</a></div>";
//    $ret .= "<img src='".UC_ICON_DIR."topicon.png' alt='class icon' /><a style='text-decoration: none' class='userclass_edit' href='".e_ADMIN_ABS."userclass2.php?config.edit.{$this->class_tree[$listnum]['userclass_id']}'>".$this->class_tree[$listnum]['userclass_name']."</a></div>";
	//$ret .= "<img src='".UC_ICON_DIR."topicon.png' alt='class icon' />
		//<span style='cursor:pointer; vertical-align: bottom' onclick=\"javascript: document.location.href='".e_ADMIN."userclass2.php?config.edit.{$this->class_tree[$listnum]['userclass_id']}'\">".$name_line."</span></div>";
    // vertical-align: middle doesn't work! Nor does text-top

	if ($num_children)
	{
	  $ret .= "<div class='uclass_tree' id='{$tag_name}'>\n";
	  $image_level = count($indent_images);
	  if ($is_last)
	  {
	    $indent_images[] = 'linebottom.gif';
	  }
	  else
	  {
	    $indent_images[] = 'line.gif';
	  }
	  foreach ($this->class_tree[$listnum]['class_children'] as $p)
	  {
		$num_children--;
		if ($num_children)
	    {	// Use icon indicating more below
	      $ret .= $this->show_graphical_subtree($p, $indent_images, FALSE);
	    }
	    else
		{ // else last entry on this tree
	      $ret .= $this->show_graphical_subtree($p, $indent_images, TRUE);
		}
	  }
	  $ret .= "</div>";
	}
	return $ret;
  }



	function show_graphical_tree($show_debug=FALSE)
	{
		$this->graph_debug = $show_debug;
		$indent_images = array();

		$ret = "<div class='uclass_tree' style='height:16px'>
			<img src='".UC_ICON_DIR."topicon.png' alt='class icon' style='vertical-align: bottom' />
			<span style='top:3px'></span>
		</div>";		// Just a generic icon here to provide a visual anchor

		$num_parents = count($this->class_parents);
		foreach ($this->class_parents as $p)
		{
			$num_parents--;
			$ret .= $this->show_graphical_subtree($p, $indent_images, ($num_parents == 0));
		}
		return $ret;
	}



  // Creates an array which contains only DB fields (i.e. strips the added status)
  function copy_rec($classrec, $inc_id = FALSE)
  {
	$ret = array();
	if ($inc_id && isset($classrec['userclass_id'])) $ret['userclass_id'] = $classrec['userclass_id'];
	foreach ($this->field_list as $fl => $val)
	{
	  if (isset($classrec[$fl])) $ret[$fl] = $classrec[$fl];
	}
	return $ret;
  }


	// Return an unused class ID - FALSE if none spare. Misses the predefined classes.
	function findNewClassID()
	{
		$i = 1;
		// Start by allocating a new class with a number higher than any previously allocated
		foreach ($this->class_tree as $id => $r)
		{
			if ($id < e_UC_SPECIAL_BASE)
			{
				$i = max($i,$id);
			}
		}
		$i++;
		if ($i < e_UC_SPECIAL_BASE) return $i;

		// Looks like we've had a lot of activity in classes - try and find a gap.
		for ($i = 1; ($i < e_UC_SPECIAL_BASE); $i++)
		{
			if (!isset($this->class_tree[$i])) return $i;
		}
		// Big system!! Assign a class in the 0.8-only block above 255
		for ($i = e_UC_SPECIAL_END+1; ($i < 32767); $i++)
		{
			if (!isset($this->class_tree[$i])) return $i;
		}

		return FALSE;			// Just in case all classes assigned!
	}


	// Add new class. Class ID must be in the passed record.
	// Return TRUE on success, FALSE on failure
	function add_new_class($classrec)
	{
//    echo "Add new class<br />";
		if (!isset($classrec['userclass_id']))
		{
			return FALSE;
		}
		if ($classrec['userclass_type'] == UC_TYPE_GROUP)
		{	// Need to make sure our ID is in the accumulation array
			$temp = explode(',',$classrec['userclass_accum']);
			if (!in_array($classrec['userclass_id'], $temp))
			{
				$temp[] = $classrec['userclass_id'];
				$classrec['userclass_accum'] = implode(',',$temp);
			}
		}
		if ($this->sql_r->db_Insert('userclass_classes',$this->copy_rec($classrec, TRUE)) === FALSE)
		{
			return FALSE;
		}
		$this->clearCache();
		return TRUE;
	}


	function save_edited_class($classrec)
	{
//    echo "Save edited class: ".implode(',', $classrec)."<br />";
		if (!$classrec['userclass_id'])
		{
			echo "Programming bungle on save<br />";
			return FALSE;
		}
		$qry = '';
		$spacer = '';
		if ($classrec['userclass_type'] == UC_TYPE_GROUP)
		{	// Need to make sure our ID is in the accumulation array
			$temp = explode(',',$classrec['userclass_accum']);
			if (!in_array($classrec['userclass_id'], $temp))
			{
				$temp[] = $classrec['userclass_id'];
				$classrec['userclass_accum'] = implode(',',$temp);
			}
		}

		foreach ($this->field_list as $fl => $val)
		{
			if (isset($classrec[$fl]))
			{
				$qry .= $spacer."`".$fl."` = '".$classrec[$fl]."'";
				$spacer = ", ";
			}
		}
		if ($this->sql_r->db_Update('userclass_classes', $qry." WHERE `userclass_id`='{$classrec['userclass_id']}'") === FALSE)
		{
			return FALSE;
		}
		$this->clearCache();
		return TRUE;
	}



	function delete_class($class_id)
	{
		if (isset($this->fixed_classes[$class_id])) return FALSE;			// Some classes can't be deleted
	//	echo "Delete class {$class_id}<br />";
		if (!isset($this->class_tree[$class_id])) return FALSE;
		if (count($this->class_tree[$class_id]['class_children'])) return FALSE;		// Can't delete class with descendants
		foreach ($this->class_tree as $c)
		{
		  if ($c['userclass_editclass'] == $class_id) return FALSE;
		  if ($c['userclass_visibility'] == $class_id) return FALSE;
		}
		if ($this->sql_r->db_Delete('userclass_classes', "`userclass_id`='{$class_id}'") === FALSE) return FALSE;
		$this->clearCache();
		$this->readTree(TRUE);			// Re-read the class tree
		return TRUE;
	}


	function deleteClassAndUsers($classID)
	{
		if ($this->delete_class($classID))
		{
			if ($this->sql_r->db_Select('user', 'user_id, user_class', "user_class REGEXP '(^|,){$classID}(,|$)'"))
			{
				$sql2 = new db;
				while ($row = $this->sql_r->db_Fetch())
				{
					$newClass = $this->ucRemove($classID,$row['user_class']);
					$sql2->db_Update('user', "user_class = '{$newClass}' WHERE user_id = {$row['user_id']}");
				}
			}
		}
	}



	// Certain fields on admin records have constraints on their values.
	// Checks the passed array, and updates any values which are unacceptable.
	// Returns TRUE if nothing changed, FALSE if changes made
	function checkAdminInfo(&$data, $id)
	{
		$ret = TRUE;
		if (($id < e_UC_SPECIAL_BASE) || ($id > e_UC_SPECIAL_END)) return TRUE;
		if (isset($data['userclass_parent']))
		{
			if (($data['userclass_parent'] < e_UC_SPECIAL_BASE) || ($data['userclass_parent'] > e_UC_SPECIAL_END))
			{
				$data['userclass_parent'] = e_UC_NOBODY;
				$ret = FALSE;
			}
		}
		if (isset($data['userclass_editclass']))
		{
			if ($id == e_UC_MAINADMIN)
			{
				if ($data['userclass_editclass'] < e_UC_MAINADMIN)
				{
					$data['userclass_editclass'] = e_UC_MAINADMIN;
					$ret = FALSE;
				}
			}
			elseif (($data['userclass_editclass'] < e_UC_SPECIAL_BASE) || ($data['userclass_editclass'] > e_UC_SPECIAL_END))
			{
				$data['userclass_editclass'] = e_UC_MAINADMIN;
				$ret = FALSE;
			}
		}
		return $ret;
	}


  // Set default tree structure
  function set_default_structure()
  {
    // If they don't exist, we need to create class records for the 'standard' user classes
    $init_list = array(
					array('userclass_id' => e_UC_MEMBER, 'userclass_name' => UC_LAN_3,
						'userclass_description' => UCSLAN_75,
						'userclass_editclass' => e_UC_MAINADMIN,
						'userclass_parent' => e_UC_PUBLIC,
						'userclass_visibility' => e_UC_MEMBER
						),
					array('userclass_id' => e_UC_ADMINMOD, 'userclass_name' => UC_LAN_8,
						'userclass_description' => UCSLAN_74,
						'userclass_editclass' => e_UC_MAINADMIN,
						'userclass_parent' => e_UC_MAINADMIN,
						'userclass_visibility' => e_UC_MEMBER
						),
					array('userclass_id' => e_UC_ADMIN, 'userclass_name' => UC_LAN_5,
						'userclass_description' => UCSLAN_76,
						'userclass_editclass' => e_UC_MAINADMIN,
						'userclass_parent' => e_UC_ADMINMOD,
						'userclass_visibility' => e_UC_MEMBER
						),
					array('userclass_id' => e_UC_MAINADMIN, 'userclass_name' => UC_LAN_6,
						'userclass_description' => UCSLAN_77,
						'userclass_editclass' => e_UC_MAINADMIN,
						'userclass_parent' => e_UC_NOBODY,
						'userclass_visibility' => e_UC_MEMBER
						),
					array('userclass_id' => e_UC_MODS, 'userclass_name' => UC_LAN_7,
						'userclass_description' => UCSLAN_78,
						'userclass_editclass' => e_UC_MAINADMIN,
						'userclass_parent' => e_UC_ADMINMOD,
						'userclass_visibility' => e_UC_MEMBER
						),
					array('userclass_id' => e_UC_NEWUSER, 'userclass_name' => UC_LAN_9,
						'userclass_description' => UCSLAN_87,
						'userclass_editclass' => e_UC_MAINADMIN,
						'userclass_parent' => e_UC_MEMBER,
						'userclass_visibility' => e_UC_ADMIN
						)
					);

	foreach ($init_list as $entry)
	{
	  if ($this->sql_r->db_Select('userclass_classes','*',"userclass_id='".$entry['userclass_id']."' "))
	  {
	    $this->sql_r->db_Update('userclass_classes', "userclass_parent='".$entry['userclass_parent']."', userclass_visibility='".$entry['userclass_visibility']."' WHERE userclass_id='".$entry['userclass_id']."'");
	  }
	  else
	  {
	    $this->add_new_class($entry);
	  }
	}
  }

	function clearCache()
	{
		global $e107;
		$e107->ecache->clear_sys(UC_CACHE_TAG);
	}
}





//========================================================================
//			Legacy Admin Class handler - maybe add to admin class
//========================================================================

// class_add() - called only from userclass2.php
// class_remove() - called only from userclass2.php
// class_create() - called only from forum update routines - could probably go


class e_userclass
{
	function class_add($cid, $uinfoArray)
	{
		global $tp;
		$sql2 = new db;
		foreach($uinfoArray as $uid => $curclass)
		{
			if ($curclass)
			{
				$newarray = array_unique(array_merge(explode(',', $curclass), array($cid)));
				$new_userclass = implode(',', $newarray);
			}
			else
			{
				$new_userclass = $cid;
			}
			$sql2->db_Update('user', "user_class='".$tp -> toDB($new_userclass, true)."' WHERE user_id=".intval($uid));
		}
	}

	function class_remove($cid, $uinfoArray)
	{
		global $tp;
		$sql2 = new db;
		foreach($uinfoArray as $uid => $curclass)
		{
			$newarray = array_diff(explode(',', $curclass), array('', $cid));
			$new_userclass = implode(',', $newarray);
			$sql2->db_Update('user', "user_class='".$tp -> toDB($new_userclass, true)."' WHERE user_id=".intval($uid));
		}
	}


// Mostly for upgrades?
// Create a new user class, with a specified prefix to the name
// $ulist - comma separated list of user names to be added
	function class_create($ulist, $class_prefix = "NEW_CLASS_", $num = 0)
	{
		global $sql;
		$varname = "uc_".$ulist;
		if($ret = getcachedvars($varname))
		{
			return $ret;
		}
		$ul = explode(",", $ulist);
		array_walk($ul, array($this, 'munge'));
		$qry = "
		SELECT user_id, user_class from #user AS u
		WHERE user_name = ".implode(" OR user_name = ", $ul);
		if($sql->db_Select_gen($qry))
		{
			while($row = $sql->db_Fetch())
			{
				$idList[$row['user_id']] = $row['user_class'];

			}
			while($sql->db_Count("userclass_classes","(*)","WHERE userclass_name = '".strtoupper($class_prefix.$num)."'"))
			{
				$num++;
			}
			$newname = strtoupper($class_prefix.$num);
			$i = 1;
			while ($sql->db_Select('userclass_classes', '*', "userclass_id='".intval($i)."' ") && $i < 240)
			{
				$i++;
			}
			if ($i < 240)		// Give a bit of headroom - we're allocating 'system' classes downwards from 255
			{
				$sql->db_Insert("userclass_classes", "{$i}, '{$newname}', 'Auto_created_class', 254");
				$this->class_add($i, $idList);		// Add users
				cachevars($varname, $i);
				return $i;
			}
		}

	}

	function munge(&$value, &$key)
	{
		$value = "'".trim($value)."'";
	}
}



?>