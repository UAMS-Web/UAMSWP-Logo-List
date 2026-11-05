<?php

/**
 * First unit test pinning harness load for UAMSWP Logo List.
 */

declare(strict_types=1);

it('loads the plugin surface', function (): void {
    expect(dirname(__DIR__, 2).'/uamswp-logo-list.php')->toBeFile();
});
