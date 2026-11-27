<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The *_DIRECTORY overrides class2.php takes from e107_config.php.
 *
 * @see https://github.com/e107inc/e107/issues/6815
 */
class class2FolderOverridesTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	public function testADocsFolderTheConfigSetsIsTheOneTheSiteUses()
	{
		$php = "\$DOCS_DIRECTORY = 'e107help_docs/'; ".$this->cliBoot(array('cli' => true));
		$php .= "echo \"\\n@@DOCS=\", e107::getFolder('docs'), \"@@\\n\";";

		list($output) = $this->runInCli($php);
		$printed = implode("\n", $output);

		self::assertStringContainsString('@@DOCS=e107help_docs/@@', $printed,
			"class2.php did not pass the config's \$DOCS_DIRECTORY on:\n".$printed);
	}
}
