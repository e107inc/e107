<?php
/*
* Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Featurebox core item templates
*/

global $sc_style;


// e107 v2.x Defaults. 

$FEATUREBOX_TEMPLATE['landingzero_homepage'] = '{SETIMAGE: w=1080&h=720&crop=1}
                <div class="col-lg-4 col-sm-6">
                    <a href="#galleryModal" class="gallery-box" data-toggle="modal" data-src="{FEATUREBOX_IMAGE|landingzero_homepage=src} ">
                        <img src="{FEATUREBOX_IMAGE|landingzero_homepage=src}" class="img-responsive" alt="{FEATUREBOX_TITLE}">
                        <div class="gallery-box-caption">
                            <div class="gallery-box-content">
                                <div>
                                    <i class="icon-lg ion-ios-search"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
  ';
                
$FEATUREBOX_TEMPLATE['landingzero_homepage_modal'] = '{SETIMAGE: w=900&h=650&crop=1}              
   <div class="portfolio-modal modal fade" id="portfolioModal{FEATUREBOX_COUNTER}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-content">
            <div class="close-modal" data-dismiss="modal">
                <div class="lr">
                    <div class="rl">
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-lg-offset-2">
                        <div class="modal-body">
                            <h2>{FEATUREBOX_TITLE}</h2>
                            <hr class="star-primary">
                            {FEATUREBOX_IMAGE}
                            {FEATUREBOX_TEXT}
                            <button type="button" class="btn btn-default btn-secondary" data-dismiss="modal"><i class="fa fa-times"></i> ' . LAN_CLOSE . '</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>                
                
 ';

 
?>
