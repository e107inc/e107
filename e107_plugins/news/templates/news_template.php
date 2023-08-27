<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * News default templates
 */

if (!defined('e107_INIT'))  exit;


$NEWS_TEMPLATE = array();


$NEWS_MENU_TEMPLATE['list']['start']       = '<div class="thumbnails">';
$NEWS_MENU_TEMPLATE['list']['end']         = '</div>';


$NEWS_INFO = array(
	'default' 	=> array('title' => LAN_DEFAULT, 	'description' => 'unused'),
	'list' 	    => array('title' => LAN_LIST, 		'description' => 'unused'),
	'2-column'  => array('title' => "2 Column (experimental)",     'description' => 'unused'), //@todo more default listing options.
);


// XXX The ListStyle template offers a listed summary of items with a minimum of 10 items per page. 
// As displayed by news.php?cat.1 OR news.php?all 
// {NEWSBODY} should not appear in the LISTSTYLE as it is NOT the same as what would appear on news.php (no query) 

// Template/CSS to be reviewed for best bootstrap implementation 
$NEWS_TEMPLATE['list']['caption']	= '{NEWSCATEGORY}';
$NEWS_TEMPLATE['list']['start']	= '{SETIMAGE: w=400&h=350&crop=1}';
/*
 // (optional)
$NEWS_TEMPLATE['list']['first'] = '
		{SETIMAGE: w=800&h=400}
		<div class="default-item">

          {NEWSIMAGE: item=1}
			<h2 class="news-title">{NEWS_TITLE: link=1}</h2>
          <p class="lead">{NEWS_SUMMARY}</p>
          {NEWSVIDEO: item=1}
          <div class="text-justify">
       
          </div>
           <div class="text-right text-end">
			<a href="{NEWS_URL}" class="btn btn-primary">{LAN=LAN_READ_MORE}</a>
			</div>
        <hr>
		
			</div>
		{SETIMAGE: w=400&h=350&crop=1}
';
*/

$NEWS_TEMPLATE['list']['end']	= '';
$NEWS_TEMPLATE['list']['item']	= '

		<div class="row row-fluid">
				<div class="span3 col-md-3">
                   <div class="thumbnail">
                        {NEWSTHUMBNAIL=placeholder}
                    </div>
				</div>
				<div class="span9 col-md-9">
                   <h3 class="media-heading">{NEWS_TITLE: link=1}</h3>
                      <p>
                       	{NEWS_SUMMARY}
					</p>
                    <p>
                       <a href="{NEWSURL}" class="btn btn-small btn-primary">{LAN=READ_MORE}</a>
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





$NEWS_WRAPPER['default']['item']['NEWSIMAGE: item=1'] = '<span class="news-images-main pull-left float-left col-xs-12 col-sm-6 col-md-6">{---}</span>';

$NEWS_TEMPLATE['default']['caption'] = null; // add a value to user tablerender()
$NEWS_TEMPLATE['default']['start']	= '<!-- Default News Template -->';
$NEWS_TEMPLATE['default']['item'] = '
		{SETIMAGE: w=400&h=400}
		<div class="default-item">
		<h2 class="news-title">{NEWS_TITLE: link=1}</h2>

        <hr class="news-heading-sep">
         	<div class="row">
        		<div class="col-md-6"><small>{GLYPH=user} &nbsp;{NEWSAUTHOR} &nbsp; {GLYPH=time} &nbsp;{NEWSDATE=short} </small></div>
        		<div class="col-md-6 text-right text-end options"><small>{GLYPH=tags} &nbsp;{NEWSTAGS} &nbsp; {GLYPH=folder-open} &nbsp;{NEWSCATEGORY} </small></div>
        	</div>
        <hr>
          {NEWSIMAGE: item=1}

          <p class="lead">{NEWS_SUMMARY}</p>
          {NEWSVIDEO: item=1}
          <div class="text-justify">
          {NEWS_BODY}
          </div>
          <div class="text-right text-end">
          {EXTENDED}
          </div>
		  <hr>
			<div class="options">
			<div class="btn-group hidden-print">{NEWSCOMMENTLINK: glyph=comments&class=btn btn-default btn-secondary}{PRINTICON: class=btn btn-default btn-secondary}{PDFICON}{SOCIALSHARE}{ADMINOPTIONS: class=btn btn-default btn-secondary}</div>
			</div>
		</div>
';

$NEWS_TEMPLATE['default']['end']	= '';

$NEWS_TEMPLATE['category']          = $NEWS_TEMPLATE['default'];
$NEWS_TEMPLATE['category']['start']	= '<!-- Category News Template -->';

/**
 * @todo (experimental)
 */
$NEWS_TEMPLATE['2-column']['caption']  = '{NEWS_CATEGORY_NAME}';
$NEWS_TEMPLATE['2-column']['start']    = '<div class="row">';
$NEWS_TEMPLATE['2-column']['item']     = '<div class="item col-md-6">
											{SETIMAGE: w=400&h=400&crop=1}
											{NEWSTHUMBNAIL=placeholder}
	                                            <h3>{NEWS_TITLE}</h3>
	                                            <p>{NEWS_SUMMARY}</p>
	                                         	<p class="text-right text-end"><a class="btn btn-primary btn-othernews" href="{NEWSURL}">' . LAN_READ_MORE . '</a></p>
            							  </div>';
$NEWS_TEMPLATE['2-column']['end']      = '</div>';


