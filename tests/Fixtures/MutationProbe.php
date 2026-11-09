<?php

declare(strict_types=1);

namespace UamswpLogoList\Tests\Fixtures;

/**
 * Return the control value used to distinguish original and mutated source.
 *
 * @return int The original control value.
 */
function mutation_compatibility_probe(): int
{
    return 7;
}
