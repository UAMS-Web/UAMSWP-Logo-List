<?php

/**
 * Emit controlled subprocess outcomes for the temporary mutation runner repair.
 */

declare(strict_types=1);

// Publish the actual report location so the parent can verify cleanup.
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--log-junit=')) {
        $report = substr($argument, \strlen('--log-junit='));
        file_put_contents($argv[2], $report);
        break;
    }
}

// Exercise cancellation and the runner's real five-second minimum timeout.
if ($argv[1] === 'abandon' || $argv[1] === 'timeout') {
    sleep(30);
    exit(0);
}

// Crash without a report to distinguish infrastructure failure from a kill.
if ($argv[1] === 'crash') {
    throw new RuntimeException('Deliberate mutation pre-test failure');
}

// Supply malformed and zero-test reports as separate negative controls.
file_put_contents($report, match ($argv[1]) {
    'malformed' => '<not-xml',
    'zero tests' => '<testsuites><testsuite tests="0"/></testsuites>',
    'passing test' => '<testsuites><testsuite><testcase name="control" assertions="1"/></testsuite></testsuites>',
    'failing test' => '<testsuites><testsuite><testcase name="control" assertions="1"><failure>expected failure</failure></testcase></testsuite></testsuites>',
});
exit($argv[1] === 'passing test' ? 0 : 1);
