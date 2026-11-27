<?php

declare(strict_types=1);

namespace E107\Rector\Tests\FloorApi\Rector\FunctionLike\HoistLiteralByReferenceArgumentRector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Exception\ShouldNotHappenException;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class HoistLiteralByReferenceArgumentRectorTest extends AbstractRectorTestCase
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
    public function testRefusesASiteItCannotHoistSafely(string $filePath): void
    {
        $this->expectException(ShouldNotHappenException::class);
        $this->expectExceptionMessageMatches('/\.php:\d+ passes something other than a variable to a by-reference parameter/');

        $this->doTestFile($filePath);
    }

    public static function provideRefusedData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Refused');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
