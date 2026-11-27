<?php

declare(strict_types=1);

namespace E107\Rector\Tests\Downgrade;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Exception\ShouldNotHappenException;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Runs the shipping rector.php over the shape each vendored hand patch had before it.
 */
final class DowngradeConfigTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    #[DataProvider('provideRefusedData')]
    public function testRefusesALiteralAnotherRuleBindsToAByReferenceParameter(string $filePath): void
    {
        $this->expectException(ShouldNotHappenException::class);
        $this->expectExceptionMessageMatches('/\.php, in code another downgrade rule wrote, passes something other than a variable/');

        $this->doTestFile($filePath);
    }

    public static function provideRefusedData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Refused');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../rector.php';
    }
}
