<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2017 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */


	$NEWS_GRID_TEMPLATE['col-md-6']['start'] = '<div class="row news-grid-default news-menu-grid gx-3">';

	$NEWS_GRID_TEMPLATE['col-md-6']['featured'] = '<div class="row featured">
													<div class="col-sm-12">
													<div class="item col-sm-6" >
														{SETIMAGE: w=600&h=400&crop=1}
														{NEWSTHUMBNAIL=placeholder}
													</div>
													<div class="item col-sm-6">
		                                                <h3>{NEWSTITLE}</h3>
		                                                <p>{NEWSMETADIZ: limit=100}</p>
		                                                <p class="text-right text-end"><a class="btn btn-primary btn-othernews" href="{NEWSURL}">{LAN=READ_MORE}</a></p>
	                                                </div>
	                                               </div>
	                                               </div>
            							          ';

	$NEWS_GRID_TEMPLATE['col-md-6']['item'] = '<div class="item col-md-6">
												{SETIMAGE: w=400&h=400&crop=1}
												{NEWSTHUMBNAIL=placeholder}
              									<h3>{NEWS_TITLE}</h3>
              									<p>{NEWS_SUMMARY}</p>
              									<p class="text-right text-end"><a class="btn btn-primary btn-othernews" href="{NEWSURL}">{LAN=READ_MORE}</a></p>
            							   </div>';

	$NEWS_GRID_TEMPLATE['col-md-6']['end'] = '</div>';


// ------------------ col-md-4 -----------------

	$NEWS_GRID_TEMPLATE['col-md-4']['start']    = $NEWS_GRID_TEMPLATE['col-md-6']['start'];
	$NEWS_GRID_TEMPLATE['col-md-4']['featured'] = $NEWS_GRID_TEMPLATE['col-md-6']['featured'];
    $NEWS_GRID_TEMPLATE['col-md-4']['item']     = '<div class="item col-md-4">
													{SETIMAGE: w=400&h=400&crop=1}
													{NEWSTHUMBNAIL=placeholder}
	                                                <h3>{NEWS_TITLE}</h3>
	                                                <p>{NEWS_SUMMARY}</p>
	                                                <p class="text-right text-end"><a class="btn btn-primary btn-othernews" href="{NEWSURL}">{LAN=READ_MORE}</a></p>
            							        </div>';
	$NEWS_GRID_TEMPLATE['col-md-4']['end']      = $NEWS_GRID_TEMPLATE['col-md-6']['end'];



// ------------------ col-md-3 -----------------


	$NEWS_GRID_TEMPLATE['col-md-3']['start']    = $NEWS_GRID_TEMPLATE['col-md-6']['start'];
	$NEWS_GRID_TEMPLATE['col-md-3']['featured'] = $NEWS_GRID_TEMPLATE['col-md-6']['featured'];
    $NEWS_GRID_TEMPLATE['col-md-3']['item']     = '<div class="item col-md-3">
													{SETIMAGE: w=400&h=400&crop=1}
													{NEWSTHUMBNAIL=placeholder}
	                                                <h3>{NEWS_TITLE}</h3>
	                                                <p>{NEWS_SUMMARY}</p>
	                                                <p class="text-right text-end"><a class="btn btn-primary btn-othernews" href="{NEWSURL}">{LAN=READ_MORE}</a></p>
            							        </div>';
	$NEWS_GRID_TEMPLATE['col-md-3']['end']      = $NEWS_GRID_TEMPLATE['col-md-6']['end'];


//  ---------------- col-lg-4 Bootstrap 5 only ---------------

	$NEWS_GRID_TEMPLATE['col-lg-4']['start']    = $NEWS_GRID_TEMPLATE['col-md-6']['start'];
	$NEWS_GRID_TEMPLATE['col-lg-4']['featured'] = $NEWS_GRID_TEMPLATE['col-md-6']['featured'];
	$NEWS_GRID_TEMPLATE['col-lg-4']['end']      = $NEWS_GRID_TEMPLATE['col-md-6']['end'];
	$NEWS_GRID_TEMPLATE['col-lg-4']['item']     = '{SETIMAGE: w=412&h=250&crop=1}
						<div class="item col-lg-4 mb-5">
                            <div class="card h-100 shadow border-0">
                                 {NEWS_IMAGE: type=tag&class=card-img-top&placeholder=1}
                                <div class="card-body p-4">
                                    <div class="badge bg-primary bg-gradient rounded-pill mb-2">{NEWS_CATEGORY_NAME}</div>
                                    <a class="text-decoration-none link-dark stretched-link" href="{NEWS_URL}">
                                    <h5 class="card-title mb-3">{NEWS_TITLE}</h5></a>
                                    <p class="card-text mb-0">{NEWS_SUMMARY}</p>
                                </div>
                                <div class="card-footer p-4 pt-0 bg-transparent border-top-0">
                                    <div class="d-flex align-items-end justify-content-between">
                                        <div class="d-flex align-items-center">
                                        	{NEWS_AUTHOR_AVATAR: class=rounded-circle me-3&w=40&h=40&crop=1&placeholder=1}
                                            <div class="small">
                                                <div class="fw-bold">{NEWS_AUTHOR}</div>
                                                <div class="text-muted">{NEWS_DATE=short}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
	';

// ------------------ media-list -----------------



	$NEWS_GRID_TEMPLATE['media-list']['start'] = '<div class="row news-grid-default">';

	$NEWS_GRID_TEMPLATE['media-list']['featured'] = '<div class="featured item col-sm-6" >
														{SETIMAGE: w=600&h=400&crop=1}
														{NEWSTHUMBNAIL=placeholder}
														 <h3><a href="{NEWS_URL}">{NEWS_TITLE}</a></h3>
														 <p>{NEWS_SUMMARY: limit=60}</p>
													</div>


            							          ';


	$NEWS_GRID_TEMPLATE['media-list']['item'] = '<div class="item col-sm-6">
												{SETIMAGE: w=120&h=120&crop=1}
												<ul class="media-list">
													<li class="media">
													  <div class="media-left media-top">
													    <a href="{NEWS_URL}">
													      {NEWS_IMAGE: type=tag&class=media-object img-rounded&placeholder=1}
													    </a>
													  </div>
													  <div class="media-body">
													    <h4 class="media-heading"><a href="{NEWS_URL}">{NEWS_TITLE}</a></h4>
													    <p>{NEWS_SUMMARY: limit=60}</p>
													  </div>
													  </li>

												</ul>
            							    </div>';


	$NEWS_GRID_TEMPLATE['media-list']['end'] = '</div>';



