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
|     $Source: /cvs_backup/e107_0.7/backend.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:09:30 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");


        $pubdate = strftime("%a, %d %b %Y %I:%M:00 GMT", time());
        $rss = "<?xml version=\"1.0\"?>
<rss version=\"2.0\">
<channel>
  <title>".SITENAME."</title>
  <link>".SITEURL."</link>
  <description>".SITEDESCRIPTION."</description>
  <language>en-gb</language>
  <copyright>".SITEDISCLAIMER."</copyright>
  <managingEditor>".SITEADMIN."</managingEditor>
  <webMaster>".SITEADMINEMAIL."</webMaster>
  <pubDate>$pubdate</pubDate>
  <lastBuildDate>$pubdate</lastBuildDate>
  <docs>http://backend.userland.com/rss</docs>
  <skipDays><day></day></skipDays>
  <skipHours><hour></hour></skipHours>
  <generator>e107 website system (http://e107.org)</generator>
  <ttl>60<ttl>

  <image>
    <title>".SITENAME."</title>
    <url>".SITEBUTTON."</url>
    <link>".SITEURL."</link>
    <width>88</width>
    <height>31</height>
    <description>".SITETAG."</description>
  </image>

  <textInput>
    <title>Search</title>
    <description>Search ".SITENAME."</description>
    <name>query</name>
    <link>".SITEURL."search.php</link>
  </textInput>
  ";

        $sql2 = new db;

        $sql -> db_Select("news", "*", "news_class=0 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY news_datestamp DESC LIMIT 0, 10");
        while($row = $sql -> db_Fetch()){
                extract($row);

                $sql2 -> db_Select("news_category", "*",  "category_id='$news_category' ");
                $row = $sql -> db_Fetch(); extract($row);

                $sql2 -> db_Select("user", "user_name", "user_id='".$news_author."' ");
                $row = $sql -> db_Fetch(); extract($row);


                $tmp = explode(" ", $news_body);
                unset($nb);
                for($a=0; $a<=100; $a++){
                        $nb .= $tmp[$a]." ";
                }
                  $nb = htmlspecialchars($nb);
                $wlog .= $news_title."\n".SITEURL."/comment.php?comment.news.".$news_id."\n\n";

                $itemdate = strftime("%a, %d %b %Y %I:%M:00 GMT", $news_datestamp);




  $rss .= "<item>
    <title>$news_title</title>
    <link>http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.news.".$news_id."</link>
    <description>$nb</description>
    <category domain=\"".SITEURL."\">$category_name</category>
    <comments>http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.news.".$news_id."</comments>
    <author>$user_name</author>
    <pubDate>$itemdate</pubDate>
    <guid isPermaLink=\"true\">http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.news.".$news_id."</guid>
  </item>
  ";

        }


        $rss .= "</channel>
</rss>";


        echo $rss;










?>