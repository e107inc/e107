<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Bootstrap 3 Theme for e107 v2.3.x+.
 */

if(!defined('e107_INIT'))
{
	exit;
}



class theme implements e_theme_render
{

    function __construct()
    {

        e107::meta('viewport',"width=device-width, initial-scale=1.0");

        // Load CDN provider for Bootswatch.
        if($bootswatch = e107::pref('theme', 'bootswatch', false))
        {
            e107::css('url', 'https://cdnjs.cloudflare.com/ajax/libs/bootswatch/3.4.0/' . $bootswatch . '/bootstrap.min.css');
        }

        e107::js("footer-inline", 	"$('.e-tip').tooltip({container: 'body'});"); // activate bootstrap tooltips.

        /* @example prefetch  */
        //e107::link(array('rel'=>'prefetch', 'href'=>THEME.'images/browsers.png'));

    }


    /**
     * @param string $caption
     * @param string $text
     * @param string $id : id of the current render
     * @param array $info : current style and other menu data.
     * @return null
     */
    public function tablestyle($caption, $text, $id='', $info=array())
	{


		$style = $info['setStyle']; //	global $style; // no longer needed.

	    echo "<!-- tablestyle: style=".$style." id=".$id." -->\n\n";

	    /*
	    if($id == 'wm') // Example - If rendered from 'welcome message'
	    {
			$style = '';
	    }

	    if($id == 'featurebox') // Example - If rendered from 'featurebox'
	    {
			$style = '';
	    }
	    */

		switch($style)
		{
			case "navdoc":
			case "none":

				echo $text;

				break;

			case "jumbotron":

				echo '<div class="jumbotron">
	            <div class="container">';

	            if(!empty($caption))
	            {
	                echo '<h1>'.$caption.'</h1>';
	            }

		        echo '
		        '.$text.'
		        </div>
	        	</div>';

				break;

			case "col-md-4":
			case "col-md-6":
			case "col-md-8":

				 echo ' <div class="col-xs-12 '.$style.'">';

			     if(!empty($caption))
			     {
			        echo '<h2>'.$caption.'</h2>';
			     }

			     echo '
			     '.$text.'
		         </div>';

				break;


			case "menu":

				echo '<div class="panel panel-default">
			    <div class="panel-heading">'.$caption.'</div>
				    <div class="panel-body">
				    '.$text.'
				    </div>
			    </div>';

				break;


			case "portfolio":

				 echo '
		         <div class="col-lg-4 col-md-4 col-sm-6">
		              '.$text.'
		        </div>';

				break;



			default:

			    if(!empty($caption))
			    {
			        echo '<h2 class="caption">'.$caption.'</h2>';
			    }

			    echo $text;
				// code to be executed if n is different from all labels;
		}

	    return null;

	}

    
}



