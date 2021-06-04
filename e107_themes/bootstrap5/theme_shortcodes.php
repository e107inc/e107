<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2021 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Bootstrap Theme Shortcodes. 
 *
*/


class theme_shortcodes extends e_shortcode
{

	/**
	 * Special Header Shortcode for dynamic menuarea templates.
	 * @shortcode {---HEADER---}
	 * @return string
	 */
	function sc_header()
	{
		return "<!-- Dynamic Header template -->\n";
	}


	/**
	 * Special Footer Shortcode for dynamic menuarea templates.
	 * @shortcode {---FOOTER---}
	 * @return string
	 */
	function sc_footer()
	{
		return "<!-- Dynamic Footer template -->\n";
/*
		return '
			<footer class="footer py-4 bg-dark text-white">
			<div class="container">       		
				<div class="content">         			
					<div class="row">           				
						<div class="col-md-3">   <h4>Navigation</h4>{NAVIGATION: type=main&layout=alt} 
							{MENUAREA=14}
						</div>
						<div class="col-md-3">   <h4>Follow Us</h4>{XURL_ICONS: template=footer}
							{MENUAREA=15}
						</div>           				
						<div class="col-md-3">  
							{MENUAREA=16}
						</div>           				
						<div class="col-md-3">  
							{MENUAREA=17}
						</div>                 			
					</div>       		
				</div>       		
				<hr>   	 
				<div class="container">    {NAVIGATION: type=main&layout=footer} </div>
				<div class="container">      
					<p class="m-0 text-center text-white">{SITEDISCLAIMER}</p>
				</div>    
				<!-- /.container -->
				</div>
		</footer>';*/


	}

	/**
	 * Optional {---CAPTION---} processing.
	 * @shortcode {---CAPTION---}
	 * @return string
	 */
	function sc_caption($caption)
	{
		return $caption; 
	}

	/**
	 * Optional {---BREADCRUMB---} processing.
	 * @shortcode {---BREADCRUMB---}
	 * @return string
	 */
	 /*
	function sc_breadcrumb($array)
	{
		$route = e107::route();

		if(strpos($route,'news/') === 0)
		{
			$array[0]['text'] = 'Blog';
		}

		return e107::getForm()->breadcrumb($array, true);

	}
	*/

	/**
	 * Will only function on the news page.
	 * @example {THEME_NEWS_BANNER: type=date}
	 * @example, {THEME_NEWS_BANNER: type=image}
	 * @example {THEME_NEWS_BANNER: type=author}
	 * @param null $parm
	 * @return string|null
	 */
	function sc_theme_news_banner($parm=null)
	{
		/** @var news_shortcodes $news */
		$sc = e107::getScBatch('news');
		$news = $sc->getScVar('news_item');

		$ret = '';
		$type = varset($parm['type']);

		switch($type)
		{
			case "title":
				$ret = $sc->sc_news_title();
				break;

			case "date":
				$ret = $sc->sc_news_date();
				break;

			case "comment":
				$ret = $sc->sc_news_comment_count();
				break;

			case "author":
				$ret = $sc->sc_news_author();
				break;

			case "image":
			default:
			if(!empty($news['news_thumbnail']))
			{
				$tmp = explode(',', $news['news_thumbnail']);

				$opts = array(
					'w' => 1800,
					'h' => null,
					'crop' => false,
				);

				$ret = e107::getParser()->toImage($tmp[0], $opts);
			}
			
		}

		return $ret;


	}




}






