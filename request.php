<?
require_once("class2.php");
$id = $_SERVER['QUERY_STRING'];
$sql -> db_Select("download", "*", "download_id= '$id' ");
$row = $sql -> db_Fetch();
$sql -> db_Update ("download", "download_requested=download_requested+1 WHERE download_id='$id' ");
if(eregi("http", $row['download_url'])){
	header("location:".$row['download_url']);
}else{
	header("location:files/downloads/".$row['download_url']);
}
?>