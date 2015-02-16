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
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/userclass2.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$caption = "User Class Help";

if (!($action = vartrue($_GET['action'])))
{
	if (e_QUERY)
	{
	  $qs = explode(".", e_QUERY);
	}
	$action = varset($qs[0],'display');
}
switch ($action)
{
  case 'initial' :
	$text = "Set the classes to which a new member of the site is assigned initially. 
	If you have verification enabled, this assignment may take place either when the user signs up, or when the user is verified.<br /><br />
	  And remember that if you are using hierarchical user classes, a user is automatically a member of all classes 'above' each selected class in the tree";
	break;
  case 'options' :
	$text = "The Setup options allow you to create and remove the default class hierarchy. You can see the effect by looking at the user tree.<br />
	This won't destroy your other class information, and you can modify or remove the hierarchy later.<br /><br />
	You should only need to rebuild the class hierarchy if database corruption occurs";
	break;
  case 'membs' :
    $text = "Now on user admin page";
	break;
  case 'debug' :
	$text = "For advanced users only - shows the hierarchy of the classes, plus the assigned classes and the classes to which the first 20 site members have access.<br />
	The number in front of the class name is its unique ID (reference number). The 'Everyone' class has an ID of 0 (zero). e107 uses these IDs throughout to refer to classes.<br />
	After the class name is the class visibility and editability - [vis:253, edit: 27] for example. This means that the class will be visible in most selectors only if the current user is a member of class 253, 
	and the user may edit their class membership only if they are a member of class 27.<br />
	Finally, after the '=', is a list of all classes either above or below each class in the tree, plus the ID of that class. Thus a user who is a member of a particular class will
	 be a member of all the classes in this list.<br /><br />
	To help with understanding, the class membership of the first 20 members is shown. The first entry on each line shows the classes of which the user is a member. The 
	 second entry lists all the classes where the user is a member once inheritance takes effect. The third entry shows which class memberships they can edit";
	break;
  case 'test' :
  case 'special' :
    $text = "Don't use this!!! For the devs only!!!";
	break;
  case 'edit' :
  case 'config' :
	$text = "You can create classes, or edit existing classes from this page.<br />
         This is useful for restricting users to certain parts of your site. For example, you could create a class called TEST, 
		 then create a forum which only allowed users in the TEST class to access it.<br /><br />
		 The class name is displayed in drop-down lists and the like; in some places the more detailed description is also displayed.<br /><br />
		 The class icon may be displayed in various places on the site, if one is set.<br /><br />
		 To allow users to determine whether they can be a member of a class, allow them to manage it. If you set 'no-one' here, only the admins
		 can manage membership of the class<br /><br />
		 The 'visibility' field allows you to hide the class from most members - applies in some of the drop-down lists and checkboxes.<br /><br />
		 The 'class parent' allows you to set a hierarchy of classes. If the 'top' of the hierarchy is the 'Everybody/Public' or 'Member' classes, the 
		 classes lower down the hierarchy also have the rights of their parent class, and that classes' parent, and so on. If the 'top' of the hierarchy is
		 the 'No One/Nobody' class, then rights are accumulated in the opposite direction - a class accumlates all the rights of a class <b>below</b> them in the 
		 tree. The resulting tree is shown in the lower part of the page; you can expand and contract branches by clicking on the '+' and '-' boxes.";
	break;
  case 'display' :
  default :
	$text = "You can select classes for editing, and also delete existing classes, from this page.";
}
e107::getRender() -> tablerender($caption, $text);
