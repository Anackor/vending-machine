#!/usr/bin/env php
<?php

declare(strict_types=1);

const DEFAULT_THRESHOLD = 90.0;
const FULLY_COVERED_RATE = 0.999999;

$reportPath = $argv[1] ?? 'var/coverage/cobertura.xml';
$threshold = isset($argv[2]) ? (float) $argv[2] : DEFAULT_THRESHOLD;

if (!is_file($reportPath)) {
    fwrite(STDERR, sprintf("Coverage report not found: %s\n", $reportPath));

    exit(1);
}

libxml_use_internal_errors(true);
$coverage = simplexml_load_file($reportPath);

if ($coverage === false) {
    fwrite(STDERR, sprintf("Unable to parse coverage report: %s\n", $reportPath));

    foreach (libxml_get_errors() as $error) {
        fwrite(STDERR, trim($error->message) . PHP_EOL);
    }

    exit(1);
}

$lineCoverage = ((float) $coverage['line-rate']) * 100;
$classMetrics = collectClassMetrics($coverage);
$methodMetrics = collectMethodMetrics($coverage);

printf(
    "Coverage threshold check (minimum %.2f%%)\n- classes: %.2f%%\n- methods: %.2f%%\n- lines: %.2f%%\n",
    $threshold,
    $classMetrics['rate'],
    $methodMetrics['rate'],
    $lineCoverage,
);

$failingMetrics = [];

if ($classMetrics['rate'] < $threshold) {
    $failingMetrics[] = 'classes';
}

if ($methodMetrics['rate'] < $threshold) {
    $failingMetrics[] = 'methods';
}

if ($lineCoverage < $threshold) {
    $failingMetrics[] = 'lines';
}

if ($failingMetrics !== []) {
    fwrite(
        STDERR,
        sprintf(
            "Coverage threshold failed for: %s\n",
            implode(', ', $failingMetrics),
        ),
    );

    exit(1);
}

exit(0);

/**
 * @return array{covered: int, total: int, rate: float}
 */
function collectClassMetrics(SimpleXMLElement $coverage): array
{
    $total = 0;
    $covered = 0;

    foreach ($coverage->packages->package as $package) {
        foreach ($package->classes->class as $class) {
            ++$total;

            if ((float) $class['line-rate'] >= FULLY_COVERED_RATE) {
                ++$covered;
            }
        }
    }

    return [
        'covered' => $covered,
        'total' => $total,
        'rate' => percentage($covered, $total),
    ];
}

/**
 * @return array{covered: int, total: int, rate: float}
 */
function collectMethodMetrics(SimpleXMLElement $coverage): array
{
    $total = 0;
    $covered = 0;

    foreach ($coverage->packages->package as $package) {
        foreach ($package->classes->class as $class) {
            foreach ($class->methods->method as $method) {
                ++$total;

                if ((float) $method['line-rate'] >= FULLY_COVERED_RATE) {
                    ++$covered;
                }
            }
        }
    }

    return [
        'covered' => $covered,
        'total' => $total,
        'rate' => percentage($covered, $total),
    ];
}

function percentage(int $covered, int $total): float
{
    if ($total === 0) {
        return 0.0;
    }

    return ($covered / $total) * 100;
}
