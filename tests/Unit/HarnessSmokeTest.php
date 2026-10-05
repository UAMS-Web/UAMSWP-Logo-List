<?php

/**
 * First unit test pinning harness load for UAMSWP Logo List.
 */

declare(strict_types=1);

namespace UamswpLogoList\Tests\Unit;

use UamswpLogoList\Tests\Support\UnitTestCase;

final class HarnessSmokeTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function test_harness_loads_plugin_surface(): void
    {
        $this->assertFileExists(dirname(__DIR__, 2).'/uamswp-logo-list.php');
        $this->assertTrue(true);
    }
}
