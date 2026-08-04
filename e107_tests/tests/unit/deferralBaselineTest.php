<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2026 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	use E107\SqliScan\CallSite;
	use E107\SqliScan\DeferralBaseline;

	/**
	 * Unit tests for the SQLi-scanner deferral baseline (e107_tests/_tools): the
	 * mechanism that lets the CI gate fail only on NET-NEW unsafe-concat sites
	 * while tolerating the reviewed, documented residue. Pure logic - it needs
	 * only the CallSite value object and DeferralBaseline, not the AST parser.
	 */
	class deferralBaselineTest extends \Codeception\Test\Unit
	{
		protected function _before()
		{
			require_once(e_BASE.'e107_tests/_tools/src/CallSite.php');
			require_once(e_BASE.'e107_tests/_tools/src/DeferralBaseline.php');
		}

		private function unsafe(string $file, int $line, string $method, string $excerpt): CallSite
		{
			return new CallSite($file, $line, $method, '$sql', CallSite::SAFETY_UNSAFE,
				CallSite::TIER_EXECUTE_BINDS, $excerpt, 'test', false);
		}

		private function safeSite(string $file, int $line, string $method, string $excerpt): CallSite
		{
			return new CallSite($file, $line, $method, '$sql', CallSite::SAFETY_STATIC,
				CallSite::TIER_STATIC_EXECUTE, $excerpt, 'test', false);
		}

		public function testFromSitesCountsOnlyUnsafe()
		{
			$baseline = DeferralBaseline::fromSites(array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
				$this->safeSite('a.php', 20, 'execute', 'SELECT 1'),
				$this->unsafe('b.php', 30, 'gen', '{$q}'),
			));

			// Only the two unsafe sites are baselined; the static one is ignored.
			$this->assertSame(2, $baseline->size());
		}

		public function testNoNewWhenSitesAreBaselined()
		{
			$sites = array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
				$this->unsafe('b.php', 30, 'gen', '{$q}'),
			);
			$baseline = DeferralBaseline::fromSites($sites);

			// The same sites at shifted line numbers still match (line is not in the key).
			$shifted = array(
				$this->unsafe('a.php', 999, 'execute', '{$query}'),
				$this->unsafe('b.php', 7, 'gen', '{$q}'),
			);
			$this->assertSame(array(), $baseline->newUnsafe($shifted));
		}

		public function testNewKeyIsReported()
		{
			$baseline = DeferralBaseline::fromSites(array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
			));

			$sites = array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
				$this->unsafe('c.php', 50, 'gen', '{$new}'),
			);
			$new = $baseline->newUnsafe($sites);

			$this->assertCount(1, $new);
			$this->assertSame('c.php', $new[0]->file);
		}

		public function testExcessOccurrenceOfBaselinedKeyIsNew()
		{
			// Baseline records ONE execute('{$query}') in a.php.
			$baseline = DeferralBaseline::fromSites(array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
			));

			// A second identical-looking site in the same file is a real new site.
			$sites = array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
				$this->unsafe('a.php', 88, 'execute', '{$query}'),
			);
			$new = $baseline->newUnsafe($sites);

			$this->assertCount(1, $new);
			$this->assertSame(88, $new[0]->line);
		}

		public function testStaleEntriesDetected()
		{
			$baseline = DeferralBaseline::fromSites(array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
				$this->unsafe('b.php', 30, 'gen', '{$q}'),
			));

			// b.php's site was migrated away; only a.php remains.
			$stale = $baseline->staleEntries(array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
			));

			$this->assertCount(1, $stale);
			$this->assertSame('b.php', $stale[0]['file']);
		}

		public function testGatePassesWhenSitesMatchBaseline()
		{
			$baseline = DeferralBaseline::fromSites(array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
				$this->unsafe('b.php', 30, 'gen', '{$q}'),
			));

			// Same sites at shifted line numbers: no net-new, no stale, gate passes.
			$this->assertTrue($baseline->passes(array(
				$this->unsafe('a.php', 999, 'execute', '{$query}'),
				$this->unsafe('b.php', 7, 'gen', '{$q}'),
			)));
		}

		public function testGateFailsOnNewUnsafe()
		{
			$baseline = DeferralBaseline::fromSites(array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
			));

			// A brand-new unsafe-concat site fails the gate.
			$this->assertFalse($baseline->passes(array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
				$this->unsafe('c.php', 50, 'gen', '{$new}'),
			)));
		}

		public function testGateFailsOnStaleEntry()
		{
			// Baseline freezes two sites; the code now keeps only one, so the other
			// is a stale entry. Even with zero net-new unsafe-concat, the gate must
			// fail so the drift is re-baselined on purpose.
			$baseline = DeferralBaseline::fromSites(array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
				$this->unsafe('b.php', 30, 'gen', '{$q}'),
			));

			$current = array($this->unsafe('a.php', 10, 'execute', '{$query}'));

			$this->assertSame(array(), $baseline->newUnsafe($current), 'no net-new site');
			$this->assertNotEmpty($baseline->staleEntries($current), 'b.php entry is stale');
			$this->assertFalse($baseline->passes($current), 'a stale entry must fail the gate');
		}

		public function testWriteLoadRoundTrip()
		{
			$sites = array(
				$this->unsafe('a.php', 10, 'execute', '{$query}'),
				$this->unsafe('a.php', 11, 'execute', '{$query}'),
				$this->unsafe('b.php', 30, 'gen', "SELECT ... ".'{$x}'),
			);
			$baseline = DeferralBaseline::fromSites($sites);

			$path = tempnam(sys_get_temp_dir(), 'baseline');
			$baseline->write($path);
			$loaded = DeferralBaseline::load($path);
			@unlink($path);

			// Reloaded baseline tolerates exactly the original sites and nothing more.
			$this->assertSame(3, $loaded->size());
			$this->assertSame(array(), $loaded->newUnsafe($sites));
		}

		public function testLoadRejectsMalformedFile()
		{
			$path = tempnam(sys_get_temp_dir(), 'baseline');
			file_put_contents($path, '{"not_entries": true}');

			try
			{
				DeferralBaseline::load($path);
				$this->fail('Expected RuntimeException on malformed baseline');
			}
			catch(\RuntimeException $e)
			{
				$this->assertStringContainsString('malformed', $e->getMessage());
			}
			finally
			{
				@unlink($path);
			}
		}
	}
