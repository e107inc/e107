<?php

/*
Please note this menu only serves as an example of how to set up a class restricted menu and doesn't actually perform any useful operation apart from that.
*/


if($sql -> db_Select("userclass_classes", "*", "userclass_name='EXAMPLEPRIVATEMENU' ")){		// checks class list for a class called EXAMPLEPRIVATEMENU
	$row = $sql -> db_Fetch(); extract($row);																			//	if it finds the class grab the classes' details from the database
	if(check_class($userclass_id)){																							//	this function checks if site visitor is part of the EXAMPLEPRIVATEMENU class
		$text = "Success -  you're class settings show you are granted access to this menu item!";	//	and if he/she is continue with the menu, else do nothing.
		$ns -> tablerender("Example Restricted Forum Item", $text);
	}
}

?>