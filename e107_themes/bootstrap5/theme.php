<?php

if(!defined('e107_INIT'))
{
	exit();
}



	class theme implements e_theme_render
	{



        public function init()
        {

            e107::lan('theme');

            e107::meta('viewport', 'width=device-width, initial-scale=1.0'); // added to <head>
	        // e107::link('rel="preload" href="{THEME}fonts/myfont.woff2?v=2.2.0" as="font" type="font/woff2" crossorigin');  // added to <head>
            // e107::meta('apple-mobile-web-app-capable','yes');

            $login_iframe  = e107::pref('theme', 'login_iframe', false);

            if(THEME_LAYOUT === "splash" && $login_iframe)
            {
                define('e_IFRAME', '0');
            }

        }


		/**
		 * Override how THEME_STYLE is loaded. Duplicates will be automatically removed.
		 * @return void
		 */
		function css()
		{
			e107::css('theme', THEME_STYLE);
			e107::css('theme', 'style.css'); // always load style.css last.
			e107::css('inline', '#carousel-hero.carousel {   margin-bottom: 80px; }');

		}
		/**
		 * @param string $text
		 * @return string without p tags added always with bbcodes
		 * note: this solves W3C validation issue and CSS style problems
		 * use this carefully, mainly for custom menus, let decision on theme developers
		 */
		function remove_ptags($text = '') // FIXME this is a bug in e107 if this is required.
		{

			$text = str_replace(array("<!-- bbcode-html-start --><p>", "</p><!-- bbcode-html-end -->"), "", $text);

			return $text;
		}


		function tablestyle($caption, $text, $mode='', $options = array())
		{

			$style = varset($options['setStyle'], 'default');

			// Override style based on mode.
			switch($mode)
			{
				case 'wmessage':
				case 'wm':
					$style = 'wmessage';
					break;

				case "login_page":
				case "fpw":
				case "coppa":
				case "signup":
					$style = 'splash';
					break;

				case 'news_months_menu':
					$style = 'listgroup';
				break;
			}

			echo "\n<!-- tablestyle initial:  style=" . $style . "  mode=" . $mode . "  UniqueId=" . varset($options['uniqueId']) . " -->\n\n";

			
			if($style === 'listgroup' && empty($options['list']))
			{
				$style = 'cardmenu';
			}

			if($style === 'cardmenu' && !empty($options['list']))
			{
				$style = 'listgroup';
			}

			/* Changing card look via prefs */
			if(!e107::pref('theme', 'cardmenu_look') && $style == 'cardmenu')
			{
				$style = 'menu';
			}

	//		echo "\n<!-- tablestyle:  style=" . $style . "  mode=" . $mode . "  UniqueId=" . varset($options['uniqueId']) . " -->\n\n";

			if(deftrue('e_DEBUG'))
			{
				echo "\n<!-- \n";
				echo json_encode($options, JSON_PRETTY_PRINT);
				echo "\n-->\n\n";
			}

			switch($style)
			{

				case 'wmessage':
				    echo '<div class="p-5"><div class="container text-center">';
				        if(!empty($caption))
				        {
				          echo '<h1 class="display-4">'.$caption.'</h1>';
				        }

				    echo '<p class="lead">'.$this->remove_ptags($text).'</p>';
				    echo '</div></div>';
	            break;
    
				case 'bare':
					echo $this->remove_ptags($text);
					break;


				case 'nocaption':
				case 'main':
					echo $text;
					break;


				case 'menu':
					echo '<div class=" mb-4">';
					if(!empty($caption))
					{
						echo '<h5>' . $caption . '</h5>';
					}
					echo $text;
					echo '</div>';
					break;


				case 'cardmenu':
					echo '<div class="card mb-4">';
					if(!empty($caption))
					{
						echo '<h5 class="card-header">' . $caption . '</h5>';
					}
					echo '<div class="card-body">';
					echo $text;
					echo '</div>
						</div>';
					break;


				case 'listgroup': 
					echo '<div class="card mb-4">';
					if(!empty($caption))
					{
						echo '<h5 class="card-header">' . $caption . '</h5>';
					}
					echo $text;

					if(!empty($options['footer'])) // XXX @see news-months menu.
			        {
			            echo '<div class="card-footer">
		                      '.$options['footer'].'
		                    </div>';
			        }


					break;
          
			case "splash":	
				if (!empty($caption))
				{
					echo '<h1 class="header-title">' . $caption . '</h1>';
				}
				echo '<p class="text-lg ' . $mode . '">' . $text . '</p>';
				break;               


			   default:

					// default style
					// only if this always work, play with different styles

					if(!empty($caption))
					{
						echo '<h4>' . $caption . '</h4>';
					}
					echo $text;

					return;
			}

		}

	}
