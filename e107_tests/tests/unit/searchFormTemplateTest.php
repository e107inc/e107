<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * The blocks a shipped search pack may declare. The form.type block sat in
 * both packs for a decade with nothing to parse it, so a theme author reading
 * either pack found a block they could style and never see.
 *
 * @see https://github.com/e107inc/e107/issues/6301
 */
class searchFormTemplateTest extends \Test\Unit
{
	/** Every block search.php reads out of the array the form template resolves to. */
	private static $rendered = array(
		'start',
		'category',
		'message',
		'end',
		'enhanced',
		'advanced',
		'advanced-combo',
	);

	const RULE = 'A pack block that search.php never reads renders nowhere, and the theme author who styles it has no way to find that out.';

	/**
	 * A block a pack declares has to be one the page renders, whether the declaration is live or commented out.
	 */
	public function testShippedFormPacksCarryNoBlockThatNothingRenders()
	{
		$packs = $this->shippedPacks();

		$this->assertGreaterThan(1, count($packs),
			'fewer search packs were found than ship with e107, so the assertions below cover almost nothing');

		foreach($packs as $pack)
		{
			$blocks = $this->declaredBlocks($pack);

			$this->assertGreaterThan(4, count($blocks),
				$pack.' declares almost no form blocks, so the scan below missed them rather than passing on them');

			foreach($blocks as $block)
			{
				$this->assertNotNull($block,
					$pack.' names a form block with something other than a literal, which the scan cannot hold to the list. '.self::RULE);

				$this->assertContains($block, self::$rendered,
					$pack." declares the form block '".$block."'. ".self::RULE);
			}
		}
	}

	/**
	 * The table above is a ceiling, so a name search.php has stopped reading may not sit in it unnoticed.
	 */
	public function testEveryBlockTheTableAllowsIsOneSearchPhpReads()
	{
		$read = $this->templateIndices(e_ROOT.'search.php');

		$this->assertNotEmpty($read, 'no template subscript was found in search.php, so the assertions below are vacuous');

		foreach(self::$rendered as $block)
		{
			$this->assertContains($block, $read,
				"search.php reads no template block called '".$block."', so allowing a pack to declare one holds nothing down. ".self::RULE);
		}
	}

	/**
	 * Every search pack e107 ships, at each of the four places {@see e107::coreTemplatePath()} looks, a theme's own first.
	 *
	 * @return string[] absolute paths
	 */
	private function shippedPacks()
	{
		$patterns = array(
			e_THEME.'*/templates/search_template.php',
			e_THEME.'*/search_template.php',
			e_CORE.'templates/*/search_template.php',
			e_CORE.'templates/search_template.php',
		);

		$found = array();

		foreach($patterns as $pattern)
		{
			$found = array_merge($found, glob($pattern));
		}

		return $found;
	}

	/**
	 * Every name a pack subscripts on $SEARCH_TEMPLATE['form'], in file order, with null for one the scan cannot read.
	 *
	 * @param string $path a pack file
	 * @return array of string|null
	 */
	private function declaredBlocks($path)
	{
		$names = array();
		$tokens = $this->tokensIn(file_get_contents($path), true);

		foreach(array_keys($tokens) as $i)
		{
			if($tokens[$i] !== array('var', '$SEARCH_TEMPLATE'))
			{
				continue;
			}

			$indices = $this->indicesAfter($tokens, $i);

			if(!isset($indices[0]) || $indices[0] !== 'form')
			{
				continue;
			}

			if(isset($indices[1]))
			{
				$names[] = $indices[1];
				continue;
			}

			if(isset($tokens[$i + 4]) && $tokens[$i + 4] === array('punct', '['))
			{
				$names[] = null;
			}
		}

		return $names;
	}

	/**
	 * Every literal index search.php subscripts on a template array, whether the local or the property.
	 *
	 * @param string $path
	 * @return string[] index names, deduplicated
	 */
	private function templateIndices($path)
	{
		$names = array();
		$tokens = $this->tokensIn(file_get_contents($path), false);

		foreach(array_keys($tokens) as $i)
		{
			if($tokens[$i] !== array('var', '$template') && $tokens[$i] !== array('name', 'template'))
			{
				continue;
			}

			foreach($this->indicesAfter($tokens, $i) as $name)
			{
				$names[$name] = $name;
			}
		}

		return array_values($names);
	}

	/**
	 * The literal string indices subscripted on the token at $i, outermost first, stopping at the first subscript that is not one.
	 *
	 * @param array $tokens
	 * @param int $i
	 * @return string[]
	 */
	private function indicesAfter($tokens, $i)
	{
		$indices = array();

		while(isset($tokens[$i + 3])
			&& $tokens[$i + 1] === array('punct', '[')
			&& $tokens[$i + 2][0] === 'str'
			&& $tokens[$i + 3] === array('punct', ']'))
		{
			$indices[] = $tokens[$i + 2][1];
			$i += 3;
		}

		return $indices;
	}

	/**
	 * The tokens a subscript can be read from, each as array(kind, text); everything else is dropped.
	 *
	 * @param string $source whole PHP source, opening tag included
	 * @param bool $inComments whether a comment is scanned as source too, which is how a pack reads to the theme author styling it and how search.php does not read to the parser
	 * @return array
	 */
	private function tokensIn($source, $inComments)
	{
		$kinds = array(T_VARIABLE => 'var', T_STRING => 'name', T_CONSTANT_ENCAPSED_STRING => 'str');
		$found = array();

		foreach(token_get_all($source) as $token)
		{
			if(!is_array($token))
			{
				$found[] = array('punct', $token);
				continue;
			}

			if($inComments && ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT))
			{
				$found = array_merge($found, $this->tokensIn('<?php '.$this->commentBody($token[1]), true));
				continue;
			}

			if(!isset($kinds[$token[0]]))
			{
				continue;
			}

			$text = $token[0] === T_CONSTANT_ENCAPSED_STRING ? (string) substr($token[1], 1, -1) : $token[1];
			$found[] = array($kinds[$token[0]], $text);
		}

		return $found;
	}

	/**
	 * A comment with its delimiters taken off, so what it holds can be tokenized in its turn.
	 *
	 * @param string $comment
	 * @return string
	 */
	private function commentBody($comment)
	{
		$body = ltrim($comment);

		if(strpos($body, '/*') !== 0)
		{
			return ltrim($body, '/#');
		}

		$end = strrpos($body, '*/');

		return $end === false ? (string) substr($body, 2) : (string) substr($body, 2, $end - 2);
	}
}
