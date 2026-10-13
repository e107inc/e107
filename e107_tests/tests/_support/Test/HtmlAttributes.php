<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

use DOMDocument;
use DOMXPath;

/**
 * Reads the attributes of a fragment of HTML the way a browser would build its elements.
 */
trait HtmlAttributes
{
	/**
	 * @param string $html
	 * @return array the names of the event handler attributes in $html
	 */
	protected function handlerAttributes($html)
	{
		return $this->attributesOf($html, '//@*[starts-with(translate(name(), "ON", "on"), "on")]', true);
	}

	/**
	 * @param string $html
	 * @param string $query an XPath query for attributes
	 * @param bool   $names true for the attributes' names rather than their values
	 * @return array
	 */
	protected function attributesOf($html, $query, $names = false)
	{
		$dom = new DOMDocument();
		$errors = libxml_use_internal_errors(true);
		$dom->loadHTML('<?xml encoding="utf-8"?><div>'.$html.'</div>');
		libxml_clear_errors();
		libxml_use_internal_errors($errors);

		$found = array();

		foreach ((new DOMXPath($dom))->query($query) as $attribute)
		{
			$found[] = $names ? $attribute->nodeName : $attribute->nodeValue;
		}

		return $found;
	}
}
