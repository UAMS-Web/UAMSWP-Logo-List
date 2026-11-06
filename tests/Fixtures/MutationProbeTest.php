<?php

declare(strict_types=1);

namespace UamswpLogoList\Tests\Fixtures;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Exercise source replacement in a native PHPUnit isolated child.
 *
 * @command  vendor/bin/pest tests/Fixtures/MutationProbeTest.php
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class MutationProbeTest extends TestCase
{
    /**
     * Require the original path and reject the deliberate replacement value.
     */
    public function test_it_reads_the_original_control_value(): void
    {
        // Traverse a real directory through both installed stream wrappers.
        $directory = opendir(__DIR__);
        self::assertNotFalse($directory);
        $entries = [];
        while (($entry = readdir($directory)) !== false) {
            $entries[] = $entry;
        }

        closedir($directory);
        self::assertContains('MutationProbe.php', $entries);

        // Load through the original path so only source replacement can change it.
        require __DIR__.'/MutationProbe.php';

        // Distinguish a genuine assertion failure from a child startup crash.
        self::assertSame(7, mutation_compatibility_probe());
    }
}
