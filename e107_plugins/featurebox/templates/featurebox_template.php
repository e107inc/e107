<?php
/*
* Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Featurebox core item templates
*/

global $sc_style;


// e107 v2.x Defaults. 

$FEATUREBOX_TEMPLATE['bootstrap_carousel_default'] = '{SETIMAGE: w=2205&h=510&crop=1}
		<div class="{FEATUREBOX_ACTIVE} carousel-item item slide{FEATUREBOX_COUNTER}">
			{FEATUREBOX_IMAGE}		
           <div class="container">
            <div class="carousel-caption">
              <h1>{FEATUREBOX_TITLE}</h1>
              <p>{FEATUREBOX_TEXT}</p>
            </div>
          </div>
        </div>';


$FEATUREBOX_TEMPLATE['bootstrap_carousel_left'] = '
   <!-- slide -->			 {SETIMAGE: w=600&h=450&crop=1}
                            <div class="{FEATUREBOX_ACTIVE} carousel-item item slide{FEATUREBOX_COUNTER}">
                                <div class="container">
                                    <div class="featurebox-item-image col-xs-12 col-md-6 col-sm-6 pull-left float-left">
                                    	{FEATUREBOX_IMAGE=placeholder}
                                     </div>
                                    <div class="featurebox-item-text col-xs-12 col-md-6 col-sm-6 span4">
                                        <h1>
                                            {FEATUREBOX_TITLE}
                                        </h1>
                                        {FEATUREBOX_TEXT}
										
										 <p>{FEATUREBOX_BUTTON}</p>
										
                                    </div>
                                </div>
                            </div>
   <!-- -->
   
';

$FEATUREBOX_TEMPLATE['bootstrap_carousel_right'] = '
							{SETIMAGE: w=600&h=450&crop=1}
 							<div class="{FEATUREBOX_ACTIVE} carousel-item item slide{FEATUREBOX_COUNTER}">
                                <div class="container">
                                 <div class="featurebox-item-image pull-right float-right col-xs-12 col-sm-6 col-md-6 span6">
                                         {FEATUREBOX_IMAGE=placeholder}
                                    </div>
                                    <div class="featurebox-item-text col-xs-12 col-md-6 col-sm-6">
                                        <h1>{FEATUREBOX_TITLE}</h1>
                                        {FEATUREBOX_TEXT}
										
                                        <p>{FEATUREBOX_BUTTON}</p>
										
                                    </div>
                                </div>
                            </div>
';


$FEATUREBOX_TEMPLATE['bootstrap_carousel_image'] = '{SETIMAGE: w=1905&h=500&crop=1}

									<div class="{FEATUREBOX_ACTIVE} carousel-item item slide{FEATUREBOX_COUNTER}">			
									{FEATUREBOX_IMAGE=placeholder}		
									</div>
';





// ----------------------------













$sc_style['FEATUREBOX_IMAGE|image_left']['pre'] = '<img class="f-left" src="';
$sc_style['FEATUREBOX_IMAGE|image_left']['post'] = '" alt="" />';

$FEATUREBOX_TEMPLATE['default'] = '<!-- Feature box Item -->
	<div class="featurebox-item" >
		<h3>{FEATUREBOX_TITLE|default}</h3>
		{FEATUREBOX_TEXT|default}
	</div>
';



$FEATUREBOX_TEMPLATE['image_left'] = '
	<div class="featurebox-item">
		{FEATUREBOX_IMAGE|image_left=src}<h3>{FEATUREBOX_TITLE|image_left}</h3>{FEATUREBOX_TEXT|image_left}
		<div class="clear"><!-- --></div>
	</div>
';


$sc_style['FEATUREBOX_IMAGE|image_right']['pre'] = '<img class="f-right" src="';
$sc_style['FEATUREBOX_IMAGE|image_right']['post'] = '" alt="" />';
$FEATUREBOX_TEMPLATE['image_right'] = '
	<div class="featurebox-item">
		{FEATUREBOX_IMAGE|image_right=src}<h3>{FEATUREBOX_TITLE|image_right}</h3>{FEATUREBOX_TEXT|image_right}
		<div class="clear"><!-- --></div>
	</div>
';

/*

$FEATUREBOX_TEMPLATE['camera'] = '
	<div class="featurebox-item" data-thumb="{FEATUREBOX_THUMB=src}" data-src="{FEATUREBOX_IMAGE|camera=src}" data-link="{FEATUREBOX_URL}">
		<div class="featurebox-text camera_effected" style="position:absolute">
			<div class="featurebox-title">{FEATUREBOX_TITLE|camera}</div>
			<div class="featurebox-text">{FEATUREBOX_TEXT|camera}</div>
		</div>
	</div>
';



$FEATUREBOX_TEMPLATE['camera_caption'] = '
	<div class="featurebox-item" data-thumb="{FEATUREBOX_THUMB=src}" data-src="{FEATUREBOX_IMAGE|camera=src}" data-link="{FEATUREBOX_URL}">
		<div class="camera_caption fadeFromBottom">
			<h3>{FEATUREBOX_TITLE|camera}</h3>
			{FEATUREBOX_TEXT|camera}
		</div>
	</div>
';*/

$FEATUREBOX_TEMPLATE['accordion'] = '
	<h3 class="featurebox-title-accordion"><a href="#">{FEATUREBOX_TITLE|accordion}</a></h3>
		<div class="featurebox-text-accordion" >
			{FEATUREBOX_IMAGE|accordion}
			{FEATUREBOX_TEXT|accordion}
			<div class="clear"><!-- --></div>
		</div>
';

$FEATUREBOX_TEMPLATE['tabs'] = '
		<div class="featurebox-text-tabs" >
			{FEATUREBOX_IMAGE|accordion}
			{FEATUREBOX_TEXT|accordion}
			<div class="clear"><!-- --></div>
		</div>
';



$FEATUREBOX_INFO = array(
	
	'bootstrap_carousel_default' 	=> array('title' => 'Bootstrap', 							'description' => 'Title and Description'),
	'bootstrap_carousel_image' 		=> array('title' => 'Bootstrap Carousel (Image-Only)', 		'description' => 'Image Only'),
	'bootstrap_carousel_left' 		=> array('title' => 'Bootstrap Carousel (Image-left)', 		'description' => 'Image aligned left with title and text on the right'),
	'bootstrap_carousel_right' 		=> array('title' => 'Bootstrap Carousel (Image-right)', 	'description' => 'Image aligned right with title and text on the left'),
	
	'default' 						=> array('title' => 'Generic', 								'description' => 'Title and description - no image'),
	'image_left'					=> array('title' => 'Generic - (Image-left)'	, 			'description' => 'Left floated image'),
	'image_right' 					=> array('title' => 'Generic - (Image-right)',				'description' => 'Right floated image'),

	// 'camera'						=> array('title' => 'Camera item',							'description' => 'For use with the "camera" category'),
	// 'camera_caption' 				=> array('title' => 'Camera item with caption',				'description' => 'For use with the "camera" category'),
	'accordion' 					=> array('title' => 'Accordion Item',						'description' => 'For use with accordion'),
	'tabs' 							=> array('title' => 'Tab Item',								'description' => 'For use with tabs')
);
?>