<?php


	/**
	 * @param null $parm
	 * @param string ['type'] main|side|footer|alt|alt5|alt6 (the data)
	 * @param string ['layout'] main|side|footer|alt|alt5|alt6| or custom template key.  (the template)
	 * @return string
	 */
	function navigation_shortcode($parm=null)
{
	$types = array(
		'main'		=> 1,
		'side'		=> 2,
		'footer'	=> 3,
		'alt'		=> 4,
		'alt5'		=> 5,
		'alt6'		=> 6,
	);

	$category = 1;
	$tmpl = 'main';
	if (!is_array($parm))
	{
		$category = isset($types[$parm]) ? $types[$parm] : 1;
		$tmpl = $parm ?: 'main';
	}
	elseif (!empty($parm))
	{
		$category = 1;
		$tmpl = 'main';

		if (!empty($parm['type']))
		{
			$cat = $parm['type'];
			$category = varset($types[$cat], 1);
		}

		if (!empty($parm['layout']))
		{
			$tmpl = $parm['layout'];
		}
	}

	$nav			= e107::getNav();

	$template		= e107::getCoreTemplate('navigation', $tmpl);	
	$data 			= $nav->initData($category,$parm);

	return $nav->render($data, $template);

}
	