<?php
$sql2=new db;
$text = "";
$sql2 -> db_Select("userclass_users", "userclass_class", "userclass_user='".USERID."' ");
list($class_list) = $sql2-> db_Fetch();
$sql2 -> db_Select("userclass_classes", "userclass_id,userclass_name");
while(list($class_id,$class_name) = $sql2-> db_Fetch()){
	if(strstr($class_list,".".$class_id.".")){
		define("USERCLASS_".strtoupper($class_name),TRUE);
	} else {
		define("USERCLASS_".strtoupper($class_name),FALSE);
	}
}
?>