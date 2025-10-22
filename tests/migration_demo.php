<?php

declare(strict_types=1);

// Optimized demo: iterate over Mode enum, list functions, and attempt calls using YAML fixtures
// Run via Docker + Composer:
//   docker compose run --rm php composer install --no-interaction --prefer-dist
//   docker compose run --rm -e ENV=acceptance php composer run demo:migration

$root = dirname(__DIR__);
require_once $root.'/vendor/autoload.php';

use src\Enum\Environment;
use src\Enum\Mode;
use src\Factory\DdsWsdlDtoFactory;
use src\Services\EudrClient;
use Symfony\Component\Yaml\Yaml;

// Load fixtures
$fixturesPath = $root.'/tests/fixtures/payloads.yaml';
$fixtures = file_exists($fixturesPath) ? (Yaml::parseFile($fixturesPath) ?? []) : [];

$sections = [];

$line = str_repeat('=', 70);
$env = Environment::ACCEPTANCE;
$clientId = $env->getWebServiceClientId();
printf("%s\nEUDR PHP Migration Demo (ENV=%s, CLIENT_ID=%s)\n%s\n\n", $line, $env->name, $env->getWebServiceClientId(), $line);

$eudrClient = new EudrClient('n00hfgop', 'xlOPMeepcGgBnKkDWBbygrPeBi2ajSHpikqzJCsW', $env);
$response = null;

foreach ($fixtures as $section => $sectionFixtures) {
    $lineWidth = 80;
    echo str_repeat('=', $lineWidth).PHP_EOL;
    $title = strtoupper($section);
    $titleLine = '====='.str_pad($title, $lineWidth - 10, ' ', STR_PAD_BOTH).'=====';
    echo $titleLine.PHP_EOL;
    echo str_repeat('=', $lineWidth).PHP_EOL;

    foreach ($sectionFixtures as $op => $payload) {
        $opLine = '-----'.str_pad($op, $lineWidth - 10, ' ', STR_PAD_BOTH).'-----';
        echo $opLine.PHP_EOL;
        try {
            if (Mode::ECHO === Mode::from($section)) {
                $client = $eudrClient->getClient(Mode::ECHO);
                if ('testEcho' === $op) {
                    /** @var array{query: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::testEcho($payload);
                    $response = $client->testEcho($submitDto);
                }
            } elseif (Mode::SUBMISSION === Mode::from($section)) {
                $client = $eudrClient->getClient(Mode::SUBMISSION);

                if ('submitDds' === $op) {
                    /** @var array{operatorType: string, statement: array{internalReferenceNumber?: string, activityType: string, countryOfActivity: string, borderCrossCountry: string, comment: string, geoLocationConfidential?: bool, commodities?: list<array<string, mixed>>, operator?: array<string, mixed>}} $payload */
                    $submitDto = DdsWsdlDtoFactory::submitDds($payload);
                    $response = $client->submitDds($submitDto);
                } elseif ('amendDds' === $op) {
                    /** @var array{ddsIdentifier: string, statement: array{internalReferenceNumber?: string, activityType: string, countryOfActivity: string, borderCrossCountry: string, comment: string, geoLocationConfidential?: bool, commodities?: list<array<string, mixed>>, operator?: array<string, mixed>}} $payload */
                    $submitDto = DdsWsdlDtoFactory::amendDds($payload);
                    $response = $client->amendDds($submitDto);
                } elseif ('retractDds' === $op) {
                    /** @var array{ddsIdentifier: string, reason: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::retractDds($payload);
                    $response = $client->retractDds($submitDto);
                } else {
                    throw new InvalidArgumentException("Unknown DTO type '$op'");
                }
            } elseif (Mode::RETRIEVAL === Mode::from($section)) {
                $client = $eudrClient->getClient(Mode::RETRIEVAL);
                if ('getDdsInfo' === $op) {
                    /** @var array{identifier: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::getDdsInfo($payload);
                    $response = $client->getDdsInfo($submitDto);
                } elseif ('getDdsInfoByInternalReferenceNumber' === $op) {
                    /** @var array{identifier: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::getDdsInfoByInternalReferenceNumber($payload);
                    $response = $client->getDdsInfoByInternalReferenceNumber($submitDto);
                } elseif ('getStatementByIdentifiers' === $op) {
                    /** @var array{referenceNumber: string, verificationNumber: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::getStatementByIdentifiers($payload);
                    $response = $client->getStatementByIdentifiers($submitDto);
                } elseif ('getReferencedDds' === $op) {
                    /** @var array{referenceNumber: string, referenceDdsVerificationNumber: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::getReferencedDds($payload);
                    $response = $client->getReferencedDds($submitDto);
                } else {
                    throw new InvalidArgumentException("Unknown DTO type '$op'");
                }
            } else {
                throw new InvalidArgumentException("Unknown Mode  '$section'");
            }
            var_dump($response);
        } catch (SoapFault $e) {
            echo "SOAP Fault:\nCode: {$e->faultcode}\nMessage: {$e->faultstring}\n".'Detail: '.print_r($e->detail ?? null, true)."\n";
        }
    }

    echo PHP_EOL; // ligne vide entre les sections
}
