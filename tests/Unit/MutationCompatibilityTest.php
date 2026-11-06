<?php

/**
 * Temporary installation guards for the development repairs tracked in #82.
 * Remove with the corresponding patches when #85 retires them.
 *
 * @command  vendor/bin/pest tests/Unit/MutationCompatibilityTest.php
 */

declare(strict_types=1);

use Pest\Mutate\Mutation;
use Pest\Mutate\MutationTest;
use Pest\Mutate\Repositories\ConfigurationRepository;
use Pest\Mutate\Repositories\TelemetryRepository;
use Pest\Support\Container;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

it('loads the mutation bootstrap through Composer before test startup', function (): void {
    expect(get_included_files())->toContain(
        realpath(dirname(__DIR__, 2).'/vendor/pestphp/pest-plugin-mutate/src/Autoload.php')
    );
});

it('retains the installed temporary mutation compatibility repairs', function (
    string $file,
    string $signature,
): void {
    expect(file_get_contents(dirname(__DIR__, 2).'/vendor/'.$file))->toContain($signature);
})->with([
    'delegated directory object' => [
        'antecedent/patchwork/src/CodeManipulation/Stream.php',
        'if ($shouldReturnResource && is_object($resource))',
    ],
    'directory wrapper retention' => [
        'antecedent/patchwork/src/CodeManipulation/Stream.php',
        "static::alternate('opendir', null, __FUNCTION__, [\$path], [\$options], \$this->context, true)",
    ],
    'child report argument' => [
        'pestphp/pest-plugin-mutate/src/MutationTest.php',
        "'--log-junit='.\$this->reportPath",
    ],
    'missing report rejection' => [
        'pestphp/pest-plugin-mutate/src/MutationTest.php',
        'Mutation test process did not report any executed tests',
    ],
]);

it('propagates mutation source into isolated children with stacked directory wrappers', function (
    string $replacement,
    int $exitCode,
    int $failures,
): void {
    // Allocate a separate report for each control and each parallel test process.
    $root = dirname(__DIR__, 2);
    $report = tempnam(sys_get_temp_dir(), 'logo-mutation-control-');
    expect($report)->not->toBeFalse();

    try {
        // Start Pest normally and let PHPUnit launch its own isolated child.
        $process = new Process(
            [PHP_BINARY, $root.'/vendor/bin/pest', 'tests/Fixtures/MutationProbeTest.php',
                '--do-not-record-test-run-history', '--log-junit='.$report],
            $root,
            [
                'PEST_MUTATION_TESTING' => $root.'/tests/Fixtures/MutationProbe.php',
                'PEST_MUTATION_FILE' => $root.'/tests/Fixtures/'.$replacement,
            ],
            timeout: 30,
        );
        $process->run();

        // Require an executed test and assertion outcome, not merely a failed exit.
        expect($process->getExitCode())->toBe($exitCode, $process->getOutput().$process->getErrorOutput());
        $document = new DOMDocument;
        expect($document->load($report))->toBeTrue();
        expect($document->getElementsByTagName('testcase')->length)->toBe(1);
        expect($document->getElementsByTagName('failure')->length)->toBe($failures);
        expect($document->getElementsByTagName('error')->length)->toBe(0);
    } finally {
        // Remove temporary reports even when an assertion fails.
        unlink($report);
    }
})->with([
    'unchanged source' => ['MutationProbe.php', 0, 0],
    'deliberate source replacement' => ['MutationProbeMutant.php', 1, 1],
]);

it('validates subprocess reports and cleans them on every exit path', function (
    string $mode,
    ?string $result,
): void {
    // Keep the subprocess marker separate from the runner-owned report.
    $root = dirname(__DIR__, 2);
    $marker = tempnam(sys_get_temp_dir(), 'logo-report-marker-');
    expect($marker)->not->toBeFalse();
    $source = $root.'/tests/Fixtures/MutationProbe.php';
    $test = new MutationTest(new Mutation(
        new SplFileInfo($source, '', ''),
        'report-control',
        'report-control',
        15,
        15,
        '',
        $source,
    ));
    $report = '';

    try {
        // Exercise the installed runner with an actual child process.
        Container::getInstance()->get(TelemetryRepository::class)->initialTestSuiteDuration(0.0);
        expect($test->start(
            [$source => [15 => ['Control\\ProbeTest::test_probe']]],
            (new ConfigurationRepository)->mergedConfiguration(),
            [PHP_BINARY, $root.'/tests/Fixtures/MutationReportProcess.php', $mode, $marker],
        ))->toBeTrue();

        if ($mode === 'abandon') {
            // Observe startup before abandoning the unpolled mutant object.
            $deadline = microtime(true) + 5;
            while (file_get_contents($marker) === '' && microtime(true) < $deadline) {
                usleep(1000);
            }

            $report = file_get_contents($marker);
            expect($report)->not->toBe('');
            expect(file_exists($report))->toBeTrue();
            unset($test);
        } else {
            // Require the expected classification, or rejection before scoring.
            try {
                while (! $test->hasFinished()) {
                    usleep(1000);
                }

                expect($result)->not->toBeNull();
                expect($test->result()->value)->toBe($result);
            } catch (RuntimeException $exception) {
                expect($result)->toBeNull();
                expect($exception->getMessage())->toContain('did not report any executed tests');
                expect($test->result()->value)->toBe('none');
            }

            $report = file_get_contents($marker);
        }

        // Check the exact allocated file, not a potentially blind directory scan.
        expect($report)->not->toBe('');
        expect(file_exists($report))->toBeFalse();
    } finally {
        // Stop any remaining child before deleting its marker.
        unset($test);
        unlink($marker);
    }
})->with([
    'crash' => ['crash', null],
    'malformed' => ['malformed', null],
    'zero tests' => ['zero tests', null],
    'passing test' => ['passing test', 'untested'],
    'failing test' => ['failing test', 'tested'],
    'timeout' => ['timeout', 'timeout'],
    'abandon' => ['abandon', null],
]);
