<?php

declare(strict_types=1);

namespace UamswpLogoList\Tests\Fixtures;

/**
 * Deliberately replace the control value without changing the original file.
 *
 * @return int A value the isolated test must reject.
 */
function mutation_compatibility_probe(): int
{
    return 13;
}
