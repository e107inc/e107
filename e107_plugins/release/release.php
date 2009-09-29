<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Release Plugin
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/release/release.php,v $
 * $Revision: 1.1 $
 * $Date: 2009-09-29 00:03:01 $
 * $Author: e107coders $
 *
*/
require_once("../../class2.php");

echo "<?xml version='1.0' encoding='utf-8' ?>
<e107Release>\n";
$tp = e107::getParser();

e107::getDb()->db_Select_gen("SELECT * FROM #release ORDER BY release_type,release_date DESC");
while ($row = e107::getDb()->db_Fetch(MYSQL_ASSOC))
{
	echo "\t<".$row['release_type']." name='".$tp->toRss($row['release_name'])."' folder='".$tp->toRss($row['release_folder'])."' author='".$tp->toRss($row['release_author'])."' authorURL='".$row['release_authorURL']."' version='".$row['release_version']."' date='".$row['release_date']."' compatibility='".$row['release_compatibility']."' url='".$row['release_url']."' />\n";
}

echo "</e107Release>";
/*
    <theme name='e107.v5' folder='e107v5a' version='3.6' date='2009-06-11' compatibility='0.8' url='http://www.e107.org/edownload.php' />
	<plugin name='Google Sitemap' folder='gsitemap' version='2.0' date='2009-06-11' compatibility='0.8' url='http://www.e107.org/edownload.php?f=".$_GET['folder']."v=".$_GET['version']."' />
	<plugin name='Chatbox' folder='chatbox_menu' version='2.0' date='2009-06-11' compatibility='0.8' url='http://www.e107.org/edownload.php?f=".$_GET['folder']."v=3.0' /> 
	<language name='German' author='Joe Blogs' authorURL='http://mysite.com' folder='German' version='1.0' date='2009-06-11' compatibility='0.8' url='http://www.e107.org/edownload.php?f=".$_GET['folder']."v=3.0' />
	<language name='French' author='marj' authorURL='http://mysites.fr' folder='French' version='1.0' date='2009-06-11' compatibility='0.8' url='http://www.e107.org/edownload.php?f=".$_GET['folder']."v=3.0' />  
*/
?>
