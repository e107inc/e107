<?php
include('../../class2.php');
require_once(e_PLUGIN.'forum/forum_class.php');
$timestart = microtime();
$ttab = MPREFIX.'forum_t';
$forum = new e107forum;

forum_stage1();
forum_stage2();
forum_stage3();
forum_stage4();

function forum_stage1() {
	global $ttab, $sql;
	echo date('r',time()). " Updating table structure <br />";
	$sql->db_Select_gen("ALTER TABLE {$ttab} ADD thread_anon VARCHAR( 250 ) NOT NULL");
	$sql->db_Select_gen("ALTER TABLE {$ttab} ADD thread_edit_datestamp INT( 10 ) UNSIGNED NOT NULL");
	$sql->db_Select_gen("ALTER TABLE {$ttab} ADD thread_lastuser VARCHAR( 30 ) NOT NULL");
	$sql->db_Select_gen("ALTER TABLE {$ttab} ADD thread_lastuser VARCHAR( 30 ) NOT NULL");
	$sql->db_Select_gen("ALTER TABLE {$ttab} ADD thread_total_replies INT UNSIGNED NOT NULL");
}

function forum_stage2() {
	global $ttab, $sql;
	$numrows = $sql->db_Update('forum_t',"thread_anon = SUBSTRING(thread_user,3) WHERE thread_user LIKE '0.%'",TRUE);
	echo "Updated $numrows rows <br />";
}

function forum_stage3() {
	global $ttab, $sql;
	$sql->db_Select_gen("ALTER TABLE {$ttab} CHANGE thread_user thread_user INT( 10 ) UNSIGNED NOT NULL"); 	
}


function forum_stage4() {
	global $forum;
	echo date('r',time()). " Updating for lastpost info <br />";
	set_time_limit(180);
	$forum->update_lastpost('forum','all');
}


$timeend = microtime();
$diff = number_format(((substr($timeend,0,9)) + (substr($timeend,-10)) - (substr($timestart,0,9)) - (substr($timestart,-10))),4);
echo "<br />script generation took $diff s";

/*
1) Add new fields
	forum_t = thread_anon, thread_edit_datestamp, thread_lastuser

2) move anonymous post info into thread_anon

3) change thread_user to int(10) UNSIGNED

4) Update ALL lastpost info (forum_t and forum)

5) Remove all 'edited' info from posts (possible regex to move it)
*/

?>
