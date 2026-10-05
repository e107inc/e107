<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database;

class SqlLexerTest extends \Test\Unit
{
	protected function _before()
	{
		require_once(e_HANDLER.'Database/SqlLexer.php');
	}

	public function testTheTokensJoinBackIntoTheOriginalText()
	{
		$sql = "SELECT `a`, 'x''y' AS b, \"q\" -- c\n FROM t /* d */ WHERE a = :p AND b <> ? # e\n;";

		foreach(array(SqlLexer::mysql(), SqlLexer::sqlite()) as $lexer)
		{
			$text = '';
			foreach($lexer->tokenize($sql) as $token)
			{
				$text .= $token['text'];
			}

			$this->assertSame($sql, $text);
		}
	}

	public function testMysqlDecodesBackslashEscapesAndKeepsTheLikeOnes()
	{
		$tokens = SqlLexer::mysql()->significantTokens("'it\\'s' 'a\\nb' 'c\\\\d' 'e\\%f\\_g' 'h''i' \"j\\\"k\"");
		$values = array();

		foreach($tokens as $token)
		{
			$this->assertSame(SqlLexer::T_STRING, $token['type']);
			$values[] = $token['value'];
		}

		$this->assertSame(array("it's", "a\nb", 'c\\d', 'e\\%f\\_g', "h'i", 'j"k'), $values);
	}

	public function testSqliteReadsABackslashLiterallyAndDoubleQuotesAsAnIdentifier()
	{
		$tokens = SqlLexer::sqlite()->significantTokens("'a\\b' \"col\"\"x\" [br] `bt`");

		$this->assertSame(array(SqlLexer::T_STRING, 'a\\b'), array($tokens[0]['type'], $tokens[0]['value']));
		$this->assertSame(array(SqlLexer::T_QUOTED_IDENTIFIER, 'col"x'), array($tokens[1]['type'], $tokens[1]['value']));
		$this->assertSame(array(SqlLexer::T_QUOTED_IDENTIFIER, 'br'), array($tokens[2]['type'], $tokens[2]['value']));
		$this->assertSame(array(SqlLexer::T_QUOTED_IDENTIFIER, 'bt'), array($tokens[3]['type'], $tokens[3]['value']));
	}

	public function testHashStartsACommentOnlyInMysql()
	{
		$mysql = SqlLexer::mysql()->tokenize("a # b\nc");
		$this->assertSame(SqlLexer::T_COMMENT, $mysql[2]['type']);
		$this->assertSame('# b', $mysql[2]['text']);

		$sqlite = SqlLexer::sqlite()->significantTokens('a # b');
		$this->assertSame(array('a', '#', 'b'), array_map(function($t) { return $t['text']; }, $sqlite));
	}

	public function testWordsNumbersParametersAndSymbols()
	{
		$tokens = SqlLexer::mysql()->significantTokens("id>=1.5e3 AND x<=>0x1F OR :name_1 || ? b'01' X'ab'");
		$described = array();

		foreach($tokens as $token)
		{
			$described[] = $token['type'].':'.$token['text'];
		}

		$this->assertSame(array(
			'word:id', 'symbol:>=', 'number:1.5e3', 'word:AND', 'word:x', 'symbol:<=>', 'number:0x1F',
			'word:OR', 'parameter::name_1', 'symbol:||', 'parameter:?', "number:b'01'", "number:X'ab'",
		), $described);
	}

	public function testStatementsSplitOnlyAtTopLevelSemicolons()
	{
		$script = "CREATE TABLE a (v VARCHAR(5) DEFAULT ';');\n-- a; comment\nINSERT INTO a VALUES ('x;y') ; ;\n/* ; */ SELECT 1";

		$this->assertSame(
			array("CREATE TABLE a (v VARCHAR(5) DEFAULT ';')", "-- a; comment\nINSERT INTO a VALUES ('x;y')", '/* ; */ SELECT 1'),
			SqlLexer::mysql()->splitStatements($script)
		);
	}

	public function testADoubleDashStartsAMysqlCommentOnlyBeforeWhitespace()
	{
		$this->assertSame(
			array("INSERT INTO t VALUES (5--1, 'a')", "INSERT INTO t VALUES (6, 'b')"),
			SqlLexer::mysql()->splitStatements("INSERT INTO t VALUES (5--1, 'a'); INSERT INTO t VALUES (6, 'b');")
		);

		$comments = function(SqlLexer $lexer, $sql)
		{
			$found = array();

			foreach($lexer->tokenize($sql) as $token)
			{
				if($token['type'] === SqlLexer::T_COMMENT)
				{
					$found[] = $token['text'];
				}
			}

			return $found;
		};

		$this->assertSame(array('-- b', "--\tc", '--'), $comments(SqlLexer::mysql(), "a --x -- b\n--\tc\nd --"));
		$this->assertSame(array('--x -- b', "--\tc", '--'), $comments(SqlLexer::sqlite(), "a --x -- b\n--\tc\nd --"));
	}

	public function testAMysqlIdentifierMayStartWithADigit()
	{
		$described = function(SqlLexer $lexer, $sql)
		{
			return array_map(function($token) { return $token['type'].':'.$token['text']; }, $lexer->significantTokens($sql));
		};

		$this->assertSame(
			array('word:2fa_secret', 'symbol:,', 'word:t', 'symbol:.', 'word:3d', 'symbol:,', 'number:1e5', 'symbol:,', 'number:1e+5', 'symbol:,', 'number:0x41', 'symbol:,', 'number:0b1', 'symbol:,', 'number:12', 'symbol:,', 'number:.5'),
			$described(SqlLexer::mysql(), '2fa_secret, t.3d, 1e5, 1e+5, 0x41, 0b1, 12, .5')
		);
		$this->assertSame(array('number:2', 'word:fa_secret'), $described(SqlLexer::sqlite(), '2fa_secret'));
	}

	public function testSqliteKeepsATriggerBodyInOneStatement()
	{
		$trigger = 'CREATE TRIGGER x AFTER INSERT ON t BEGIN UPDATE t SET a = 1; UPDATE t SET b = CASE WHEN a THEN 2 END; END';
		$temporary = 'create temp trigger y before delete on t begin select 1; end';

		$this->assertSame(array($trigger, 'SELECT 1', $temporary), SqlLexer::sqlite()->splitStatements($trigger.";\nSELECT 1;\n".$temporary.';'));
		$this->assertCount(3, SqlLexer::mysql()->splitStatements($trigger.';'), 'the mysql client cuts at every semicolon');
	}

	public function testAnUnterminatedStringRunsToTheEnd()
	{
		$tokens = SqlLexer::mysql()->significantTokens("SELECT 'abc");

		$this->assertSame(SqlLexer::T_STRING, $tokens[1]['type']);
		$this->assertSame('abc', $tokens[1]['value']);
	}
}
