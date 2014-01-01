<?php

if (!defined('e107_INIT'))  exit;




// {---} is replaced with the shortcode data. 
$SC_WRAPPER['EMAILICON'] 	= "<small>{---}</small>";
$SC_WRAPPER['PRINTICON'] 	= "<small>{---}</small>";
$SC_WRAPPER['PDFICON'] 		= "<small>{---}</small>";
$SC_WRAPPER['ADMINOPTIONS'] = "<small>{---}</small>";

// XXX As displayed by news.php (no query) or news.php?list.1.1 (ie. regular view of a particular category)
$NEWS_TEMPLATE['default']['item'] = '
	{SETIMAGE: w=600}
	<div class="view-item">
	<div class="page-header">
		<h2>{NEWSTITLELINK}</h2>
		<div class="post-meta">
			<small><i class="icon-calendar"></i> {NEWSDATE}</small>
			<small><i class="icon-user"></i> {NEWSAUTHOR}</small>
			<small><i class="icon-comment"></i> {NEWSCOMMENTS}</small>
			<small><i class="icon-tag"></i> {NEWSTAGS=label}</small>
			{EMAILICON}
			{PRINTICON} 
			{PDFICON}
			{ADMINOPTIONS}
		</div>
	</div>
		<div class="body">
			{NEWSIMAGE}
			{NEWSBODY}
			<div class="text-right">{EXTENDED}</div>
		</div>
	</div>
';




// XXX The ListStyle template offers a listed summary of items with a minimum of 10 items per page. 
// As displayed by news.php?cat.1 OR news.php?all OR news.php?tag=xxx
$NEWS_TEMPLATE['list']['start']	= '{SETIMAGE: w=400&h=300&crop=1}';
$NEWS_TEMPLATE['list']['end']	= '';
$NEWS_TEMPLATE['list']['item']	= '
	<div class="thumbnail">
		<div class="row-fluid">
				<div class="span3">
                   <div class="thumbnail">
                        {NEWSIMAGE}
                    </div>
				</div>
				<div class="span9">
                   <h3>{NEWSTITLELINK}</h3>
                      <p>
                       	{NEWSSUMMARY}
					</p>
                    <p>
                       <a href="{NEWSURL}" class="btn btn-info">'.LAN_READ_MORE.'</a>
                   </p>
 				</div>
		</div>
	</div>
';


//XXX As displayed after clicking on a news item. ie. 'extended' with comments. 
$NEWS_TEMPLATE['view']['item'] = '
{SETIMAGE: w=800}
	<div class="view-item">
	<div class="page-header">
		<h2>{NEWSTITLE}</h2>
		<div class="post-meta">
			<small><i class="icon-calendar"></i> {NEWSDATE}</small>
			<small><i class="icon-user"></i> {NEWSAUTHOR}</small>
			<small><i class="icon-comment"></i> {NEWSCOMMENTS}</small>
			<small><i class="icon-tag"></i> {NEWSTAGS=label}</small>
			{EMAILICON}
			{PRINTICON} 
			{PDFICON}
			{ADMINOPTIONS}
		</div>
	</div>
		<div class="body">
			{NEWSIMAGE}
			{NEWSBODY}
			{EXTENDED}
		</div>
	</div>
';

###### news_categories.sc (temporary) -
$NEWS_TEMPLATE['category']['body'] = '
	<div style="padding:5px"><div style="border-bottom:1px inset black; padding-bottom:1px;margin-bottom:5px">
	{NEWSCATICON}&nbsp;{NEWSCATEGORY}
	</div>
	{NEWSCAT_ITEM}
	</div>
';

$NEWS_TEMPLATE['category']['item'] = '
	<div style="width:100%;padding-bottom:2px">
	<table style="width:100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td style="width:2px;vertical-align:top">&#8226;
	</td>
	<td style="text-align:left;vertical-align:top;padding-left:3px">
	{NEWSTITLELINK}
	<br />
	</td></tr>
	</table>
	</div>
';



$NEWS_MENU_TEMPLATE['list']['start']       = '<div class="thumbnails">';
$NEWS_MENU_TEMPLATE['list']['end']         = '</div>';


?>