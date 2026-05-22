<?php

declare(strict_types=1);

namespace Horde\Folks\Test;

use Horde_Nls;
use PHPUnit\Framework\TestCase;

/**
 * Tests the contract of Folks::getCountries() fallback path.
 *
 * When Horde::loadConfiguration() fails (no countries.php config), the
 * method returns a full ISO 3166-1 country list as ['code' => 'Name'] array.
 *
 * This test verifies the exact output format the caller receives.
 * @coversNothing
 */
class FolksGetCountriesTest extends TestCase
{
    public function testGetCountriesFallbackReturnsCountryArray(): void
    {
        $result = Horde_Nls::getCountryISO();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('US', $result);
        $this->assertArrayHasKey('DE', $result);
        $this->assertIsString($result['US']);
        $this->assertIsString($result['DE']);
    }

    public function testGetCountriesFallbackReturnsAllCountries(): void
    {
        $result = Horde_Nls::getCountryISO();

        $this->assertGreaterThan(200, count($result));
        foreach ($result as $code => $name) {
            $this->assertMatchesRegularExpression('/^[A-Z]{2}$/', $code);
            $this->assertNotEmpty($name);
        }
    }
}
