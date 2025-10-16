<?php

declare(strict_types=1);

// Demo: PHP equivalents of Node services usage (updated for new service API)
// Run via Docker + Composer:
//   docker compose run --rm php composer install --no-interaction --prefer-dist
//   docker compose run --rm -e ENV=acceptance php composer run demo:migration

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';

use src\Enum\Environment;
use src\Services\EudrEchoClient;
use src\Services\EudrRetrievalClientV2;
use src\Services\EudrSubmissionClientV2;

$envVar = getenv('ENV') ?: null;
$environment = null;

if ($envVar) {
    $environment = match (strtolower((string)$envVar)) {
        'production', 'prod' => Environment::PRODUCTION,
        default => Environment::ACCEPTANCE,
    };
}

$options = [
    'username' => getenv('EUDR_USERNAME') ?: null,
    'password' => getenv('EUDR_PASSWORD') ?: 'null',
];
if ($environment) {
    $options['environment'] = $environment;
}

$effectiveEnv = $environment ?? Environment::ACCEPTANCE;
$sections = [];

// Echo service: list functions and perform a simple connection test (non-production only)
if ($effectiveEnv !== Environment::PRODUCTION) {
    try {
        $echo = new EudrEchoClient();
        $sections[] = ['label' => 'EudrEchoService', 'ok' => true, 'functions' => $echo->listFunctions($effectiveEnv)];

        // Connection test: try invoking testEcho with a minimal payload
        try {
            $response = $echo->testEcho($effectiveEnv, ['query' => 'ma teub']);
            $sections[] = ['label' => 'EudrEchoService connection test', 'ok' => true, 'response' => is_scalar($response) ? (string)$response : json_encode($response)];
        } catch (Throwable $ce) {
            $sections[] = ['label' => 'EudrEchoService connection test', 'ok' => false, 'error' => $ce->getMessage()];
        }
    } catch (Throwable $e) {
        $sections[] = ['label' => 'EudrEchoService', 'ok' => false, 'error' => $e->getMessage()];
    }
} else {
    $sections[] = ['label' => 'EudrEchoService', 'ok' => false, 'error' => 'Skipped: Echo forbidden on PRODUCTION'];
}

try {
    $retrieval = new EudrRetrievalClientV2();
    $sections[] = ['label' => 'EUDRRetrievalServiceV2', 'ok' => true, 'functions' => $retrieval->listFunctions($effectiveEnv)];
} catch (Throwable $e) {
    $sections[] = ['label' => 'EUDRRetrievalServiceV2', 'ok' => false, 'error' => $e->getMessage()];
}

try {
    $submission = new EudrSubmissionClientV2();
    $sections[] = ['label' => 'EUDRSubmissionServiceV2', 'ok' => true, 'functions' => $submission->listFunctions($effectiveEnv)];
} catch (Throwable $e) {
    $sections[] = ['label' => 'EUDRSubmissionServiceV2', 'ok' => false, 'error' => $e->getMessage()];
}

$line = str_repeat('=', 70);
$envName = $effectiveEnv->name;
$clientId = $effectiveEnv->getWebServiceClientId();
printf("%s\nEUDR PHP Migration Demo (ENV=%s, CLIENT_ID=%s)\n%s\n\n", $line, $envName, $clientId, $line);

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
