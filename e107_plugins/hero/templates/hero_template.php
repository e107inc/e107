<?php

// Template File
// hero Template file

if (!defined('e107_INIT')) { exit; }

$HERO_TEMPLATE = array();

$HERO_TEMPLATE['menu']['header'] 	= '<!-- Hero Menu: header -->{SETIMAGE: w=400&h=400}
											<div id="carousel-hero" class="carousel carousel-fade slide" data-ride="carousel" data-interval="{HERO_SLIDE_INTERVAL}">
							                <div class="carousel-inner" role="listbox">';


$HERO_TEMPLATE['menu']['footer'] 	= '</div><div class="carousel-controls">
						                  <!-- Controls -->
						                  <a class="left carousel-left carousel-control animated zoomIn animation-delay-30" href="#carousel-hero" role="button" data-slide="prev">
						                    <i class="fa fa-chevron-left fa-fw"></i>
						                    <span class="sr-only">Previous</span>
						                  </a>
						                  <a class="right carousel-right carousel-control animated zoomIn animation-delay-30" href="#carousel-hero" role="button" data-slide="next">
						                    <i class="fa fa-chevron-right fa-fw"></i>
						                    <span class="sr-only">Next</span>
						                  </a>
						                  <!-- Indicators -->
						                  {HERO_CAROUSEL_INDICATORS: target=carousel-hero&class=animated fadeInUpBig}
						                 <!-- <ol class="carousel-indicators">
						                    <li data-target="#carousel-hero" data-slide-to="0" class="animated fadeInUpBig animation-delay-27 active"></li>
						                    <li data-target="#carousel-hero" data-slide-to="1" class="animated fadeInUpBig animation-delay-28"></li>
						                    <li data-target="#carousel-hero" data-slide-to="2" class="animated fadeInUpBig animation-delay-29"></li>
						                  </ol>-->
						                </div>
						              </div>';


$HERO_TEMPLATE['menu']['start'] 	    = '<div class="carousel-item item {HERO_SLIDE_ACTIVE}" style="background-image:url({HERO_IMAGE})">
						                  <div class="carousel-caption">
						                    <div class="hero-text-container">
						                      <header class="hero-title animated slideInLeft animation-delay-5">
						                        <h1 class="animated fadeInLeft animation-delay-10 font-smoothing">{HERO_TITLE: enwrap=strong}</h1>
						                        <h2 class="animated fadeInLeft animation-delay-12">{HERO_DESCRIPTION: enwrap=span&class=text-bold}</h2>
						                      </header>
						                      <ul class="hero-list list-unstyled">';

$HERO_TEMPLATE['menu']['end'] 	    = ' </ul>
					                      <div class="hero-buttons text-right">
					                        
					                          <a href="{HERO_BUTTON1_URL}" class="btn btn-{HERO_BUTTON1_CLASS} btn-raised animated fadeInRight animation-delay-28">
					                            {HERO_BUTTON1_ICON} {HERO_BUTTON1_LABEL}
					                          </a>
					                        
					                      </div>
					                    </div>
					                    </div>
					                    </div>';



$HERO_TEMPLATE['menu']['item'] 	    = '<li>
			                            <div class="hero-list-icon animated zoomInUp {HERO_ANIMATION_DELAY}">
			                            <span class="hero-icon hero-icon-circle hero-icon-xlg label-{HERO_ICON_STYLE} badge-{HERO_ICON_STYLE} shadow-3dp">
			                              {HERO_ICON}
			                            </span>
			                            </div>
			                            <div class="hero-list-text animated {HERO_ANIMATION} {HERO_ANIMATION_DELAY}">{HERO_TEXT}</div>
			                        </li>';











