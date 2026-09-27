<?php

/**
 * Issue #6491: the comment form's {COMMENT_AVATAR}, rendered for a guest, hands e_parse::toAvatar() no user_name.
 */
class CommentFormGuestAvatarCest
{
	const PROBE_FILE = 'e107_tests_comment_form_guest_avatar_probe.php';

	public function _before(AcceptanceTester $I)
	{
		$I->haveProbe(self::PROBE_FILE, $this->probeSource());
	}

	public function aGuestsCommentFormAvatarRendersWithoutAWarning(AcceptanceTester $I)
	{
		$result = $I->grabProbeJson();

		$I->assertSame(0, $result['userid']);
		$I->assertSame(0, $result['admin']);
		$I->assertStringContainsString("alt=''", $result['avatar']);
		$I->assertSame(array(), $result['warnings']);
	}

	private function probeSource()
	{
		return <<<'PHP'
<?php
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

error_reporting(E_ALL);
$warnings = array();
set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$warnings)
{
	if(error_reporting() & $errno)
	{
		$warnings[] = $errstr.' at '.basename($errfile).':'.$errline;
	}

	return true;
});

$sc = e107::getScBatch('comment');
$sc->setVars(array(
	'action'  => 'comment',
	'subject' => 'Guest avatar',
	'table'   => 'news',
	'comval'  => '',
	'itemid'  => 1,
	'pid'     => 0,
	'eaction' => '',
	'rate'    => 0,
));
$sc->setMode('edit');
$avatar = $sc->sc_comment_avatar();

restore_error_handler();

echo "PROBE_OK\n";
echo json_encode(array(
	'userid'   => (int) USERID,
	'admin'    => ADMIN ? 1 : 0,
	'avatar'   => $avatar,
	'warnings' => $warnings,
));
PHP;
	}
}
