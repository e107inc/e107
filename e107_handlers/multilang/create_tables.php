<?php
// Using an original code from the marvellous PhpMyAdmin
// And based on a function from the online php help, comment from frankm at mhdsolutions dot com (Thanks :) )
/*
*
* Code to create tables for multilanguage
*/
// $Id: create_tables.php,v 1.1 2004-10-10 21:20:07 loloirie Exp $ 

function e107_sqldupli($table_to_copy,$lang_to_create,$drop_if_exist=0,$drop=0){
	global $yy;
  $html_query = "";
	if(DEBUG_ML){
		echo "<br /><br />".(count($table_to_copy,COUNT_RECURSIVE)-1)." table_to_copy: ".$table_to_copy[$lang_to_create[0]][0];
		echo "<br />";
		echo "lang_to_create: ".$lang_to_create[0];
	}
	
	// $table_to_copy = array(MPREFIX."news", MPREFIX."news_category");
	// $lang_to_create = array("French_".MPREFIX, "German_".MPREFIX);
		
	global $result, $mySQLdefaultdb;
	$content_table = 1; // Add content
	$tmp_sql_file = "temp.sql"; // Temp File name
	
	$nb_lang = array();
	$yy = 0;
	foreach($lang_to_create as $lg){
		if(!in_array($lg,$nb_lang)){$nb_lang[] = $lg;}
		$tmp_tb = explode("^",$lg);
		if($tmp_tb){$yy += (count($tmp_tb)+1);}else{$yy++;}
		
	}
	$nbr_language = count($nb_lang);
	
	unset ($tmp_nbrtable);
	if($table_to_copy != "*"){
		$tmp_nbrtable = 0;
		$nbr_table = count($table_to_copy);	
	}
	
	for($l=0;$l<$yy;$l++){
		//echo "<br><br>L".$l." - ".$table_to_copy[$lang_to_create[$l]]."<br><br>";
		// List tables
		$result = @mysql_list_tables($mySQLdefaultdb);
		if(DEBUG_ML){echo "<hr /><br />lang:{$lang_to_create[$l]} - ".count($table_to_copy[$lang_to_create[$l]]);}
		// Get all table entries
		$tmp_query3 = "";
		while ($row = mysql_fetch_row($result)){
			$lowerow0 = strtolower($row[0]);
			$tmp_query = "";
			if(DEBUG_ML){echo "<br />Table: {$lowerow0}";}
			// Check if required
			if((isset($tmp_nbrtable) && in_array($lowerow0,$table_to_copy[$lang_to_create[$l]])) || !isset($tmp_nbrtable)){
			if(DEBUG_ML){echo "<b><br>Value to check: {$lowerow0} - {$table_to_copy[$lang_to_create[$l]][1]} - {$tmp_nbrtable}</b>";}
				$tmp_query .= "<br />###################################<br /># Table ".$lowerow0."<br />###################################<br />";
				
				// Add DROP TABLE IF EXISTS
				if($drop_if_exist == 1 || $drop == 1)	{
					$tmp_query .= "<br />DROP TABLE IF EXISTS ".$lowerow0.";<br />";
				}else{
					$tmp_query .= "<br />";
				}
				
				// Continue sql query to create table, stop to delete tables.
				
				if($drop !=1){
					mysql_select_db($lowerow0);
				
					/* Enregistre sa structure */
					$req = mysql_query("SHOW CREATE TABLE $lowerow0");
					$res = mysql_fetch_array($req);
					$tmp_query .= $res[1].";<br /><br />";
		
					if($content_table==1){
						$requete = mysql_query("SELECT * FROM ".$lowerow0.";");
						$nb = mysql_num_fields($requete);
						$j = 0;
				
						While($res = mysql_fetch_array($requete))
						{ 
							$i = 0;
							$tmp_query .= "INSERT INTO ".$lowerow0." VALUES(";
							while($i<$nb)
							{
								$tmp_ligne = mysql_escape_string($res[$i]);
								$tmp_query .= "\"$tmp_ligne\", ";
								$i++;
							}
				
							$tmp_query .= "<br />";
						}
					}
				}
				if(isset($tmp_nbrtable)){$tmp_nbrtable++;}
			}
			$tmp_query3 .= $tmp_query;
			$html_query .= $tmp_query;
		}
		$tmp_query = ereg_replace(", <br />",");<br />",$tmp_query3);
		$tmp_query = eregi_replace("<br />","\n",$tmp_query);
		
		//echo $lang_to_create[$i];
		$tmp_query2 = "";
		$tmp_query2 = eregi_replace(MPREFIX,$lang_to_create[$l],$tmp_query);
		if(DEBUG_ML){echo "<br /><br />tmp_query:<br /><br />".$tmp_query."<br /><br /><br />tmp_query2:<br /><br />".$tmp_query2."<br /><br /><br />";}
		// Write the temp file
		
		$fp = @fopen(e_PLUGIN."custom/".md5($lang_to_create[$l])."_".$tmp_sql_file, "w+") or die("ERROR: \"<b>".e_PLUGIN."custom/".md5($lang_to_create[$l])."_".$tmp_sql_file."</b>\" was not created !&nbsp;");
		@fputs($fp,$tmp_query2,strlen($tmp_query2));
		@fclose($fp);
	}

	////////////////////////
	// Execute SQL query using PHPMyAdmin class (2002-04-21)
	///////////////////////
	@require_once(e_HANDLER."multilang/read_dump.php");
	
	////////////////////////
	// Delete file
	///////////////////////
	for ($i=0;$i<$nbr_language;$i++){
		// Comment next line for tests
		@unlink(e_PLUGIN."custom/".md5($lang_to_create[$i])."_".$tmp_sql_file);
	}
	
	////////////////////////
	// Display messages
	///////////////////////
	$tmp_query = eregi_replace("\n","<br />",$html_query);
	($ml_install_error == "" ? $dupli_message .= $tmp_query : $dupli_message .= $ml_install_error);
	
	return $dupli_message;
}
?>
