<?php

declare(strict_types=1);

namespace PhpTatr\Tests;

use PhpTatr\Core\Huid;
use PHPUnit\Framework\TestCase;

final class HuidTest extends TestCase
{
    public function testGenerateWithoutSuffix(): void
    {
        $huid = Huid::generate();

        $this->assertMatchesRegularExpression('/^\d{8}-\d{6}$/', $huid);
    }

    public function testGenerateWithSuffix(): void
    {
        $huid = Huid::generate('bugfix');

        $this->assertMatchesRegularExpression('/^\d{8}-\d{6}-bugfix$/', $huid);
    }

    public function testIsValidShortFormat(): void
    {
        $this->assertTrue(Huid::isValid('20260115-143022'));
    }

    public function testIsValidExtendedFormat(): void
    {
        $this->assertTrue(Huid::isValid('20260115-143022-bugfix'));
        $this->assertTrue(Huid::isValid('20260115-143022-01'));
    }

    public function testIsInvalidMissingHyphen(): void
    {
        $this->assertFalse(Huid::isValid('20260115143022'));
    }

    public function testIsInvalidBadChars(): void
    {
        $this->assertFalse(Huid::isValid('2026ABCD-143022'));
    }

    public function testIsInvalidEmpty(): void
    {
        $this->assertFalse(Huid::isValid(''));
    }

    public function testExtractFirstFindsHuid(): void
    {
        $result = Huid::extractFirst('see task 20260115-143022-bugfix for details');

        $this->assertNotNull($result);
        $this->assertSame('20260115-143022-bugfix', $result[0]);
        $this->assertSame(' for details', $result[1]);
    }

    public function testExtractFirstReturnsNullWhenNone(): void
    {
        $this->assertNull(Huid::extractFirst('no ids here'));
    }
}
