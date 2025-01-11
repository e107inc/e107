<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * News default templates
 */

if (!defined('e107_INIT'))  exit;

global $sc_style;

###### Default list item (temporary) - TODO rewrite news ######
//$NEWS_MENU_TEMPLATE['list']['start']       = '<ul class="nav nav-list news-menu-months">';
//$NEWS_MENU_TEMPLATE['list']['end']         = '</ul>';

$NEWS_MENU_TEMPLATE['list']['start']       = '<div class="thumbnails">';
$NEWS_MENU_TEMPLATE['list']['end']         = '</div>';


// XXX The ListStyle template offers a listed summary of items with a minimum of 10 items per page. 
// As displayed by news.php?cat.1 OR news.php?all 
// {NEWSBODY} should not appear in the LISTSTYLE as it is NOT the same as what would appear on news.php (no query) 

// Template/CSS to be reviewed for best bootstrap implementation 
$NEWS_TEMPLATE['list']['caption']	= '{NEWSCATEGORY}';
$NEWS_TEMPLATE['list']['start']	= '{SETIMAGE: w=400&h=350&crop=1}';
$NEWS_TEMPLATE['list']['end']	= '';
$NEWS_TEMPLATE['list']['item']	= '

		<div class="row row-fluid">
				<div class="span3 col-md-3">
                   <div class="thumbnail">
                        {NEWSTHUMBNAIL=placeholder}
                    </div>
				</div>
				<div class="span9 col-md-9">
                   <h3 class="media-heading">{NEWSTITLELINK}</h3>
                      <p>
                       	{NEWSSUMMARY}
					</p>
                    <p>
                       <a href="{NEWSURL}" class="btn btn-small btn-primary">'.LAN_READ_MORE.'</a>
                   </p>
 				</div>
		</div>
		<hr class="visible-xs" />

';






//$NEWS_MENU_TEMPLATE['list']['separator']   = '<br />';



// XXX As displayed by news.php (no query) or news.php?list.1.1 (ie. regular view of a particular category)
//XXX TODO GEt this looking good in the default Bootstrap theme. 
/*
$NEWS_TEMPLATE['default']['item'] = '
	{SETIMAGE: w=400}
	<div class="view-item">
		<h2>{NEWSTITLE}</h2>
		<small class="muted">
		<span class="date">{NEWSDATE=short} by <span class="author">{NEWSAUTHOR}</span></span>
		</small>

		<div class="body">
			{NEWSIMAGE}
			{NEWSBODY}
			{EXTENDED}
		</div>
		<div class="options">
			<span class="category">{NEWSCATEGORY}</span> {NEWSTAGS} {NEWSCOMMENTS} {EMAILICON} {PRINTICON} {PDFICON} {ADMINOPTIONS}
		</div>
	</div>
';
*/

$NEWS_TEMPLATE['default']['item'] = '
		{SETIMAGE: w=850&h=1200}
		<article class="default-item">
		<div class="news-header">
			<span class="news-category">{NEWSCATEGORY}</span>
			<h2 class="news-title">{NEWSTITLELINK} </h2>
		 </div>
		 	<div class="news-carousel">{NEWSIMAGE: carousel=1&w=800&h=500&crop=1&}</div>
			<div class="news-description">
				{NEWSMETADIZ}
				{ADMINOPTIONS}
				<p><a class="more-link" href="{NEWSURL}">Continue Reading <i class="fa fa-long-arrow-right"></i></a></p>

			</div>


           <div class="options">
        	<div class="col-md-4 text-left news-comments">{NEWSCOMMENTS}</div>
        	<div class="col-md-4">{SOCIALSHARE: class=soci} </div>
        	<div class="col-md-4 text-right news-date">{NEWSDATE=short} by {NEWSAUTHOR}</div>
        	</div>

		</article>
';

//{ADMINOPTIONS: class=btn btn-default}




###### Default view item (temporary)  ######
//$NEWS_MENU_TEMPLATE['view']['start']       = '<ul class="nav nav-list news-menu-months">';
//$NEWS_MENU_TEMPLATE['view']['end']         = '</ul>';

// As displayed by news.php?extend.1



/*
$NEWS_TEMPLATE['view']['item'] = '
{SETIMAGE: w=900&h=600}
	<div class="view-item">
		<h2 class="news-title">{NEWSTITLELINK}</h2>
		  <p class="lead">{NEWSSUMMARY}</p>
        <hr class="news-heading-sep">
         	<div class="row">
        		<div class="col-md-6"><small>{GLYPH=user} &nbsp;{NEWSAUTHOR} &nbsp; {GLYPH=time} &nbsp;{NEWSDATE=short} </small></div>
        		<div class="col-md-6 text-right options"><small>{GLYPH=tags} &nbsp;{NEWSTAGS} &nbsp; {GLYPH=folder-open} &nbsp;{NEWSCATEGORY} </small></div>
        	</div>
        <hr>


		<div class="body">
			{NEWSIMAGE: item=1}
			{NEWSBODY=body}
			<div class="news-videos-1">
			{NEWSVIDEO: item=1}
		 	{NEWSVIDEO: item=2}
		 	{NEWSVIDEO: item=3}
			</div>


			<br />
			{SETIMAGE: w=400&h=400}
			
			<div class="row  news-images-1">
        		<div class="col-md-6">{NEWSIMAGE: item=2}</div>
        		<div class="col-md-6">{NEWSIMAGE: item=3}</div>
        	</div>
        	<div class="row news-images-2">
        		<div class="col-md-6">{NEWSIMAGE: item=4}</div>
        		<div class="col-md-6">{NEWSIMAGE: item=5}</div>
            </div>
            
            {NEWSVIDEO: item=4}
			{NEWSVIDEO: item=5}
			
           <div class="body-extended">
				{NEWSBODY=extended}
			</div>
			
			
		</div>
		<hr>
		
		<div class="options hidden-print ">
			<div class="btn-group">{NEWSCOMMENTLINK: glyph=comments&class=btn btn-default}{PRINTICON: class=btn btn-default}{ADMINOPTIONS: class=btn btn-default}{SOCIALSHARE}</div>
		</div>
			
	</div>
	{NEWSRELATED}
	<hr>
	{NEWSNAVLINK}
';*/
//$NEWS_MENU_TEMPLATE['view']['separator']   = '<br />';
//$NEWS_WRAPPER['view']['item']['NEWSIMAGE: item=1'] = '<span class="news-images-main col-xs-12 col-md-12">{---}</span>';

$NEWS_TEMPLATE['view']['item'] = '
		{SETIMAGE: w=850&h=1200}
		<article class="view-item">
		<div class="news-header">
			<span class="news-category">{NEWSCATEGORY}</span>
			<h2 class="news-title">{NEWSTITLELINK}</h2>
			<div class="news-date-full">{NEWSDATE=long}</div>
		 </div>
		 	{NEWSIMAGE: item=1}	{NEWSBODY=body}
				<div class="news-videos-1">
			{NEWSVIDEO: item=1}
		 	{NEWSVIDEO: item=2}
		 	{NEWSVIDEO: item=3}
			</div>


			<br />
			{SETIMAGE: w=400&h=400}

			<div class="row  news-images-1">
        		<div class="col-md-6">{NEWSIMAGE: item=2}</div>
        		<div class="col-md-6">{NEWSIMAGE: item=3}</div>
        	</div>
        	<div class="row news-images-2">
        		<div class="col-md-6">{NEWSIMAGE: item=4}</div>
        		<div class="col-md-6">{NEWSIMAGE: item=5}</div>
            </div>

            {NEWSVIDEO: item=4}
			{NEWSVIDEO: item=5}

           <div class="body-extended">
				{NEWSBODY=extended}
			</div>
		</article>

		<hr />
		<div class="share-this-story">
			<h2 class="caption">SHARE THIS STORY</h2>
			<div>
			{SOCIALSHARE: type=facebook-share,twitter&class=soci}
			<small>{GLYPH=tags} TAGS: &nbsp;{NEWSTAGS}</small>
			</div>
		</div>
		<hr />
		{NEWSRELATED: limit=3}
	<hr>
	{NEWSNAVLINK}
';



###### news_categories.sc 
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



$NEWS_TEMPLATE['related']['start'] = '{SETIMAGE: w=350&h=350&crop=1}<h2 class="caption">YOU MIGHT ALSO LIKE</h2><div class="row">';
$NEWS_TEMPLATE['related']['item'] = '<div class="col-md-4"><a href="{RELATED_URL}">{RELATED_IMAGE}</a><h3><a href="{RELATED_URL}">{RELATED_TITLE}</a></h3></div>';
$NEWS_TEMPLATE['related']['end'] = '</div>';