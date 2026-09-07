<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	use E107\SqliScan\MethodCatalog;
	use E107\SqliScan\SafetyClassifier;
	use E107\SqliScan\SafetyRules;
	use E107\SqliScan\TaintAnalyzer;
	use PhpParser\Node\Expr;
	use PhpParser\ParserFactory;

	/**
	 * The SQLi scanner admits a fragment's getSql() into execute() only beside
	 * the same variable's getParameters(); every other splice keeps its unsafe
	 * verdict.
	 *
	 * _before() loads e107_tests/_tools/src (PHP 8.1-only), so never a legacy cell.
	 *
	 * @group requires-modern-php
	 */
	class safetyClassifierTest extends \Test\Unit
	{

		protected function _before()
		{
			foreach(array('Ast', 'CallSite', 'DynamicPart', 'SafetyResult', 'SafetyRules', 'TaintAnalyzer', 'MethodCatalog', 'SafetyClassifier') as $class)
			{
				require_once(e_BASE.'e107_tests/_tools/src/'.$class.'.php');
			}
		}

		/**
		 * @dataProvider splices
		 * @param string $expected
		 * @param string $code
		 */
		public function testAFragmentIsBoundOnlyBesideItsOwnParameters($expected, $code)
		{
			$this->assertSame($expected, $this->classify($code)->safety, $code);
		}

		public function splices()
		{
			$sql = '"SELECT * FROM #news WHERE ".$rule->getSql()';

			return array(
				'its own binds'          => array('bound-safe', '$sql->execute('.$sql.', $rule->getParameters());'),
				'its own binds merged'   => array('bound-safe', '$sql->execute('.$sql.'." AND n.news_id = :id", $rule->getParameters() + array("id" => 1));'),
				'array_merge'            => array('bound-safe', '$sql->execute('.$sql.', array_merge(array("id" => 1), $rule->getParameters()));'),
				'no binds'               => array('unsafe-concat', '$sql->execute('.$sql.');'),
				'another variable'       => array('unsafe-concat', '$sql->execute('.$sql.', $other->getParameters());'),
				'a property'             => array('unsafe-concat', '$sql->execute("SELECT * FROM #news WHERE ".$this->rule->getSql(), $this->rule->getParameters());'),
				'a call'                 => array('unsafe-concat', '$sql->execute("SELECT * FROM #news WHERE ".$this->rule()->getSql(), $this->rule()->getParameters());'),
				'a dead branch'          => array('unsafe-concat', '$sql->execute('.$sql.', $flag ? array() : $rule->getParameters());'),
				'a nested array'         => array('unsafe-concat', '$sql->execute('.$sql.', array("nested" => $rule->getParameters()));'),
				'an identifier position' => array('unsafe-concat', '$sql->execute("SELECT * FROM `#".$rule->getSql()."` WHERE 1", $rule->getParameters());'),
				'select() has no binds'  => array('unsafe-concat', '$sql->select("news", "*", "news_class = ".$rule->getSql());'),
			);
		}

		/**
		 * @param string $code one e_db call
		 * @return \E107\SqliScan\SafetyResult
		 */
		private function classify($code)
		{
			$statements = (new ParserFactory())->createForNewestSupportedVersion()->parse('<?php '.$code);
			$call = $statements[0]->expr;
			$this->assertInstanceOf(Expr\MethodCall::class, $call);
			$classifier = new SafetyClassifier(new MethodCatalog(), new TaintAnalyzer(new SafetyRules()));

			return $classifier->classify($call->name->toString(), $call->args);
		}
	}
