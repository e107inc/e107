<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2017 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	$NEWS_GRID_TEMPLATE['default']['start'] = '<div class="row news-grid-default news-menu-grid">';

	$NEWS_GRID_TEMPLATE['default']['featured'] = '<div class="row featured">
													<div class="col-sm-12">
													<div class="item col-sm-6" >
														{SETIMAGE: w=600&h=400&crop=1}
														{NEWSTHUMBNAIL=placeholder}
													</div>
													<div class="item col-sm-6">
		                                                <h3>Featured {NEWSTITLE: limit=_titleLimit_}</h3>
		                                                <p>{NEWSMETADIZ: limit=100}</p>
		                                                <p class="text-right"><a class="btn btn-primary btn-othernews" href="{NEWSURL}">' . LAN_READ_MORE . '</a></p>
	                                                </div>
	                                               </div>
	                                               </div>
            							          ';

	$NEWS_GRID_TEMPLATE['default']['item'] = '<div class="item {NEWSGRID}">
												{SETIMAGE: w=400&h=400&crop=1}
												{NEWSTHUMBNAIL=placeholder}
              									<h3>{NEWSTITLE: limit=_titleLimit_}</h3>
              									<p>{NEWSSUMMARY: limit=_summaryLimit_}</p>
              									<p class="text-right"><a class="btn btn-primary btn-othernews" href="{NEWSURL}">' . LAN_READ_MORE . '</a></p>
            							   </div>';

	$NEWS_GRID_TEMPLATE['default']['end'] = '</div>';



	//@todo find a better name than 'other'
	$NEWS_GRID_TEMPLATE['other']['start'] = '<div class="row news-grid-other">';

	$NEWS_GRID_TEMPLATE['other']['featured'] = '<div class="featured item col-sm-6" >
														{SETIMAGE: w=600&h=400&crop=1}
														{NEWSTHUMBNAIL=placeholder}
														 <h3>Featured {NEWSTITLE: limit=_titleLimit_}</h3>
														 <p>{NEWSSUMMARY}</p>
													</div>


            							          ';


	$NEWS_GRID_TEMPLATE['other']['item'] = '<div class="item col-sm-6">
												{SETIMAGE: w=120&h=120&crop=1}
												<ul class="media-list">
													<li class="media">
													  <div class="media-left media-top">
													    <a href="#">
													      {NEWS_IMAGE: class=media-object img-rounded}
													    </a>
													  </div>
													  <div class="media-body">
													    <h4 class="media-heading">{NEWSTITLE: limit=_titleLimit_}</h4>
													    <p>{NEWSSUMMARY: limit=_summaryLimit_}</p>
													  </div>
													  </li>
													</ul>
												</ul>
            							    </div>';




	$NEWS_GRID_TEMPLATE['other']['end'] = '</div>';

