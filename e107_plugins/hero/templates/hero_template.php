<?php

// Template File
// hero Template file

if (!defined('e107_INIT')) { exit; }

$HERO_TEMPLATE = array();

$HERO_TEMPLATE['default']['header'] 	= '<!-- Hero Menu: header -->{SETIMAGE: w=400&h=400}
											<div id="carousel-hero" class="carousel carousel-fade slide" data-bs-ride="carousel" data-ride="carousel" data-interval="{HERO_SLIDE_INTERVAL}" data-bs-interval="{HERO_SLIDE_INTERVAL}">
							                <div class="carousel-inner" role="listbox">';


$HERO_TEMPLATE['default']['footer'] 	= '</div><div class="carousel-controls">
						                  <!-- Controls -->
						                  <a class="left carousel-left carousel-control animated zoomIn animation-delay-30" href="#carousel-hero" role="button" data-slide="prev" data-bs-slide="prev">
						                    <i class="fa fa-chevron-left fa-fw"></i>
						                    <span class="sr-only">Previous</span>
						                  </a>
						                  <a class="right carousel-right carousel-control animated zoomIn animation-delay-30" href="#carousel-hero" role="button" data-slide="next" data-bs-slide="next">
						                    <i class="fa fa-chevron-right fa-fw"></i>
						                    <span class="sr-only">Next</span>
						                  </a>
						                  <!-- Indicators -->
						                  {HERO_CAROUSEL_INDICATORS: target=carousel-hero&class=animated fadeInUpBig}
						
						                  </ol>
						                </div>
						              </div>';


$HERO_TEMPLATE['default']['start'] 	    = '<div class="carousel-item item {HERO_SLIDE_ACTIVE}" style="background-image:url({HERO_BGIMAGE})">
						                  <div class="container">
						                  
						                  <div class="carousel-caption">
						                    <div class="hero-text-container">
						                      <header class="hero-title animated slideInLeft animation-delay-5">
						                        <h1 class="animated fadeInLeft animation-delay-10 font-smoothing">{HERO_TITLE: enwrap=strong}</h1>
						                        <h2 class="animated fadeInLeft animation-delay-12">{HERO_DESCRIPTION: enwrap=span&class=text-bold}</h2>
						                      </header>
						          				<div class="row">
							                      <div class="col-md-6">
							                      <ul class="hero-list list-unstyled">';

$HERO_TEMPLATE['default']['end'] 	            = '</ul>
	                                            	</div>
	                                            	<div class="col-md-6 ">
		                                            <div class="pull-right animated fadeInRight animation-delay-10">
		                                            {HERO_MEDIA}
		                                            </div>
	                                            </div>
 											</div>
					                      <div class="hero-buttons text-right">
					     
					                          <a href="{HERO_BUTTON1_URL}" class="btn btn-{HERO_BUTTON1_CLASS} btn-raised animated fadeInRight animation-delay-28">
					                            {HERO_BUTTON1_ICON} {HERO_BUTTON1_LABEL}
					                          </a>
					                        
					                      </div>
					                    </div>
					                    </div>
					                    </div>
					                    </div>';



$HERO_TEMPLATE['default']['item'] 	    = '<li>
										
			                            <div class="hero-list-icon animated zoomInUp {HERO_ANIMATION_DELAY}">
			                            <span class="hero-icon hero-icon-circle hero-icon-xlg label-{HERO_ICON_STYLE} badge-{HERO_ICON_STYLE} shadow-3dp">
			                              {HERO_ICON}
			                            </span>
			                            </div>
			                            <div class="hero-list-text animated {HERO_ANIMATION} {HERO_ANIMATION_DELAY}">{HERO_TEXT}</div>
			                        </li>';











