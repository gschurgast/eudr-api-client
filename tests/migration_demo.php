<?php

declare(strict_types=1);

// Optimized demo: iterate over Mode enum, list functions, and attempt calls using YAML fixtures
// Run via Docker + Composer:
//   docker compose run --rm php composer install --no-interaction --prefer-dist
//   docker compose run --rm -e ENV=acceptance php composer run demo:migration

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

use src\Enum\Environment;
use src\Enum\Mode;
use Symfony\Component\Yaml\Yaml;

// Load fixtures
$fixturesPath = $root . '/tests/fixtures/payloads.yaml';
$fixtures = file_exists($fixturesPath) ? (Yaml::parseFile($fixturesPath) ?? []) : [];



$sections = [];

$line     = str_repeat('=', 70);
$env  = Environment::ACCEPTANCE;
$clientId = $env->getWebServiceClientId();
printf("%s\nEUDR PHP Migration Demo (ENV=%s, CLIENT_ID=%s)\n%s\n\n", $line, $env->name, $env->getWebServiceClientId(), $line);

foreach (Mode::cases() as $mode) {

    $client = $mode->getWebServiceClient();

    // List functions (unauthenticated)
    try {
        $functions = $client->listFunctions($env) ?? [];
        $sections[] = ['label' => $mode->name, 'ok' => true, 'functions' => $functions];
    } catch (\Throwable $e) {
        $sections[] = ['label' => $mode->name, 'ok' => false, 'error' => $e->getMessage()];
        // If we can’t read functions, skip calls
        continue;
    }

    // Attempt calling each available fixture payload (iterate over fixtures instead of WSDL functions)
    $modeFixtures = $fixtures[$mode->value] ?? [];

    if (empty($modeFixtures)) {
        $sections[] = ['label' => $mode->name . ' fixtures', 'ok' => false, 'error' => 'No fixtures defined for mode, skipped'];
        continue;
    }

    foreach ($modeFixtures as $op => $payload) {
        $labelOp = $mode->name . '::' . $op;
        try {
            // Use generic authenticated call helper
            $response = $client->$op($env, $payload);
            $sections[] = [
                'label' => $labelOp,
                'ok' => true,
                'response' => is_scalar($response) ? (string)$response : json_encode($response),
            ];
        } catch (\Throwable $e) {
            $sections[] = ['label' => $labelOp, 'ok' => false, 'error' => $e->getMessage()];
        }
    }
}

foreach ($sections as $section) {
    printf("-- %s --\n", $section['label']);
    if ($section['ok']) {
        if (isset($section['functions'])) {
            $fns = $section['functions'];
            if (empty($fns)) {
                echo "(no functions reported by WSDL)\n\n";
                continue;
            }
            foreach ($fns as $fn) {
                echo " - " . $fn . "\n";
            }
        } elseif (isset($section['response'])) {
            echo "Response: " . ($section['response'] ?? '(none)') . "\n";
        }
    } else {
        echo "[error] " . $section['error'] . "\n";
    }
    echo "\n";
}
