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
use Symfony\Component\Yaml\Yaml;

// Load fixtures
$fixturesPath = $root.'/tests/fixtures/payloads.yaml';
$fixtures = file_exists($fixturesPath) ? (Yaml::parseFile($fixturesPath) ?? []) : [];

$sections = [];

$line = str_repeat('=', 70);
$env = Environment::ACCEPTANCE;
$clientId = $env->getWebServiceClientId();
printf("%s\nEUDR PHP Migration Demo (ENV=%s, CLIENT_ID=%s)\n%s\n\n", $line, $env->name, $env->getWebServiceClientId(), $line);

$echoClient = Mode::ECHO->getWebServiceClient();
$submissionClient = Mode::SUBMISSION->getWebServiceClient();
$retrievalClient = Mode::RETRIEVAL->getWebServiceClient();

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
                $client = Mode::ECHO->getWebServiceClient();
                if ('testEcho' === $op) {
                    /** @var array{query: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::testEcho($payload);
                    $response = $client->testEcho($env, $submitDto);
                }
            } elseif (Mode::SUBMISSION === Mode::from($section)) {
                $client = Mode::SUBMISSION->getWebServiceClient();

                if ('submitDds' === $op) {
                    /** @var array{operatorType: string, statement: array{internalReferenceNumber?: string, activityType: string, countryOfActivity: string, borderCrossCountry: string, comment: string, geoLocationConfidential?: bool, commodities?: list<array<string, mixed>>, operator?: array<string, mixed>}} $payload */
                    $submitDto = DdsWsdlDtoFactory::submitDds($payload);
                    $response = $client->submitDds($env, $submitDto);
                } elseif ('amendDds' === $op) {
                    /** @var array{ddsIdentifier: string, statement: array{internalReferenceNumber?: string, activityType: string, countryOfActivity: string, borderCrossCountry: string, comment: string, geoLocationConfidential?: bool, commodities?: list<array<string, mixed>>, operator?: array<string, mixed>}} $payload */
                    $submitDto = DdsWsdlDtoFactory::amendDds($payload);
                    $response = $client->amendDds($env, $submitDto);
                } elseif ('retractDds' === $op) {
                    /** @var array{ddsIdentifier: string, reason: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::retractDds($payload);
                    $response = $client->retractDds($env, $submitDto);
                } else {
                    throw new InvalidArgumentException("Unknown DTO type '$op'");
                }
            } elseif (Mode::RETRIEVAL === Mode::from($section)) {
                $client = Mode::RETRIEVAL->getWebServiceClient();

                if ('getDdsInfo' === $op) {
                    /** @var array{identifier: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::getDdsInfo($payload);
                    $response = $client->getDdsInfo($env, $submitDto);
                } elseif ('getDdsInfoByInternalReferenceNumber' === $op) {
                    /** @var array{identifier: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::getDdsInfoByInternalReferenceNumber($payload);
                    $response = $client->getDdsInfoByInternalReferenceNumber($env, $submitDto);
                } elseif ('getStatementByIdentifiers' === $op) {
                    /** @var array{referenceNumber: string, verificationNumber: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::getStatementByIdentifiers($payload);
                    $response = $client->getStatementByIdentifiers($env, $submitDto);
                } elseif ('getReferencedDds' === $op) {
                    /** @var array{referenceNumber: string, referenceDdsVerificationNumber: string} $payload */
                    $submitDto = DdsWsdlDtoFactory::getReferencedDds($payload);
                    $response = $client->getReferencedDds($env, $submitDto);
                } else {
                    throw new InvalidArgumentException("Unknown DTO type '$op'");
                }
            } else {
                throw new InvalidArgumentException("Unknown Mode  '$section'");
            }
        } catch (SoapFault $e) {
            echo "SOAP Fault:\nCode: {$e->faultcode}\nMessage: {$e->faultstring}\n".'Detail: '.print_r($e->detail ?? null, true)."\n";
        }
    }

    echo PHP_EOL; // ligne vide entre les sections
}
