<?php

declare(strict_types=1);

namespace Tests\Functional;

use JMS\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use src\Dto\AmendDdsRequest;
use src\Dto\GetDdsInfoByInternalReferenceNumberRequest;
use src\Dto\GetDdsInfoRequest;
use src\Dto\GetDdsInfoResponse;
use src\Dto\GetStatementByIdentifiersRequest;
use src\Dto\RetractDdsRequest;
use src\Dto\SubmitDdsRequest;
use src\Dto\TestEchoRequest;
use src\Enum\EnvironmentEnum;
use src\Enum\ModeEnum;
use src\Serializer\SerializerFactory;
use src\Services\EudrClient;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-import-type StatementArray from \src\Dto\Type\StatementType
 */
final class DemoTest extends TestCase
{
    private Serializer $serializer;

    public static function setUpBeforeClass(): void
    {
        // existing code will follow
        // Ensure Composer autoload is available when running without phpunit.xml bootstrap
        $root     = \dirname(__DIR__, 2);
        $autoload = $root . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        // Load .env credentials for functional online calls, if present
        $envFile = $root . '/.env';
        if (file_exists($envFile) && is_readable($envFile)) {
            $lines = file($envFile, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                // Strip comments
                if (str_starts_with(trim($line), '#')) {
                    continue;
                }
                // Match KEY=VALUE (supports quoted values)
                if (preg_match('/^([A-Z0-9_]+)\s*=\s*(.*)$/', $line, $m)) {
                    $key = $m[1];
                    $val = trim($m[2]);
                    if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                        $val = substr($val, 1, -1);
                    }
                    $_ENV[$key]    = $val;
                    $_SERVER[$key] = $val;
                    putenv($key . '=' . $val);
                }
            }
        }
    }

    protected function setUp(): void
    {
        $this->serializer = SerializerFactory::get();
    }

    /**
     * @return array<string, mixed>
     */
    private function loadFixturesOrSkip(): array
    {
        $root         = \dirname(__DIR__, 2);
        $fixturesPath = $root . '/tests/fixtures/payloads.yaml';
        if (!file_exists($fixturesPath)) {
            $this->markTestSkipped('No fixtures file found at ' . $fixturesPath);
        }
        /** @var array<string, array<string, array<string, mixed>>> $fixtures */
        $fixtures = Yaml::parseFile($fixturesPath) ?? [];

        return $fixtures;
    }

    private function makeClientOrSkip(): EudrClient
    {
        $env      = EnvironmentEnum::ACCEPTANCE;
        $username = getenv('EUDR_USERNAME') ?: ($_ENV['EUDR_USERNAME'] ?? null);
        $password = getenv('EUDR_PASSWORD') ?: ($_ENV['EUDR_PASSWORD'] ?? null);
        if (!$username || !$password) {
            $this->markTestSkipped('EUDR_USERNAME and/or EUDR_PASSWORD are not set. Define them in .env or environment to run online functional calls.');
        }

        return new EudrClient($username, $password, $env);
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $fixtures
     *
     * @return array<string, mixed>
     */
    private function getPayloadOrSkip(array $fixtures, string $section, string $op): array
    {
        if (!isset($fixtures[$section][$op])) {
            $this->markTestSkipped(\sprintf('No payload for %s/%s in fixtures', $section, $op));
        }
        /** @var array<string, mixed> */
        $payload = $fixtures[$section][$op];

        return $payload;
    }

    public function testEchoOnline(): void
    {
        $fixtures = $this->loadFixturesOrSkip();
        $client   = $this->makeClientOrSkip();

        /** @var array{query: string} $payload */
        $payload = $this->getPayloadOrSkip($fixtures, 'echo', 'testEcho');
        $dto     = $this->serializer->fromArray($payload, TestEchoRequest::class);
        $resp    = $client->getClient(ModeEnum::ECHO)->testEcho($dto);

        $this->assertIsString($resp->status, 'Le champ status doit être une chaîne de caractères');
        $this->assertStringContainsString('hello from demo', $resp->status, "La réponse ne contient pas 'hello from demo'");
    }

    /**
     * @return string ddsIdentifier
     */
    public function testSubmitDdsOnline(): string
    {
        $fixtures = $this->loadFixturesOrSkip();
        $client   = $this->makeClientOrSkip();

        /** @var array{operatorType: string, statement: StatementArray} $payload */
        $payload = $this->getPayloadOrSkip($fixtures, 'submission', 'submitDds');

        $dto = $this->serializer->fromArray($payload, SubmitDdsRequest::class);

        $resp = $client->getClient(ModeEnum::SUBMISSION)->submitDds($dto);

        $this->assertIsString($resp->ddsIdentifier, 'Le champ ddsIdentifier doit être une chaîne de caractères');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $resp->ddsIdentifier, 'Le champ ddsIdentifier ne correspond pas au format UUID attendu (ex: 0542e3a8-7b89-4866-b00a-d8766b9ec74a)');

        return $resp->ddsIdentifier;
    }

    public function testAmendDdsOnline(): void
    {
        $fixtures = $this->loadFixturesOrSkip();
        $client   = $this->makeClientOrSkip();

        /** @var array{ddsIdentifier: string, statement: StatementArray} $payload */
        $payload = $this->getPayloadOrSkip($fixtures, 'submission', 'amendDds');
        $dto     = $this->serializer->fromArray($payload, AmendDdsRequest::class);

        $resp = $client->getClient(ModeEnum::SUBMISSION)->amendDds($dto);

        $this->assertIsString($resp->status, 'Le champ status doit être une chaîne de caractères');
        $this->assertEquals('SC_200_OK', $resp->status, 'Le champ status n\'est pas SC_200_OK');
    }

    /**
     * @depends testSubmitDdsOnline
     */
    public function testRetractDdsOnline(string $ddsIdentifier): void
    {
        $fixtures = $this->loadFixturesOrSkip();
        $client   = $this->makeClientOrSkip();

        /** @var array{ddsIdentifier: string, reason: string} $payload */
        $payload                  = $this->getPayloadOrSkip($fixtures, 'submission', 'retractDds');
        $payload['ddsIdentifier'] = $ddsIdentifier; // override with created identifier from submit

        $dto  = $this->serializer->fromArray($payload, RetractDdsRequest::class);
        $resp = $client->getClient(ModeEnum::SUBMISSION)->retractDds($dto);

        $this->assertIsString($resp->status, 'Le champ status doit être une chaîne de caractères');
        $this->assertEquals('SC_200_OK', $resp->status, 'Le champ status n\'est pas SC_200_OK');
    }

    public function testGetDdsInfoOnline(): void
    {
        $fixtures = $this->loadFixturesOrSkip();
        $client   = $this->makeClientOrSkip();

        /** @var array{identifier: string} $payload */
        $payload = $this->getPayloadOrSkip($fixtures, 'retrieval', 'getDdsInfo');
        $dto     = $this->serializer->fromArray($payload, GetDdsInfoRequest::class);
        $resp    = $client->getClient(ModeEnum::RETRIEVAL)->getDdsInfo($dto);

        $this->assertNotNull($resp);
        $this->assertIsString($resp->identifier, 'Le champ identifier doit être une chaîne de caractères');
        $this->assertEquals($dto->identifier, $resp->identifier, \sprintf('Le champ identifier n\'est pas %s ', $dto->identifier));
        $this->assertIsString($resp->referenceNumber, 'Le champ referenceNumber doit être une chaîne de caractères');
        $this->assertMatchesRegularExpression('/^[0-9A-Z]{14}$/i', $resp->referenceNumber, 'Le champ referenceNumber ne correspond pas au format attendu (14 caractères alphanumériques en majuscules)');
        $this->assertIsString($resp->verificationNumber, 'Le champ verificationNumber doit être une chaîne de caractères');
        $this->assertMatchesRegularExpression('/^[0-9A-Z]{8}$/i', $resp->verificationNumber, 'Le champ verificationNumber ne correspond pas au format attendu (8 caractères alphanumériques en majuscules)');
        $this->assertIsString($resp->status, 'Le champ status doit être une chaîne de caractères');
        $this->assertIsString($resp->updatedBy, 'Le champ updatedBy doit être une chaîne de caractères');
    }

    public function testGetDdsInfoByInternalReferenceNumberOnline(): void
    {
        $fixtures = $this->loadFixturesOrSkip();
        $client   = $this->makeClientOrSkip();

        /** @var array{identifier: string} $payload */
        $payload = $this->getPayloadOrSkip($fixtures, 'retrieval', 'getDdsInfoByInternalReferenceNumber');
        $dto     = $this->serializer->fromArray($payload, GetDdsInfoByInternalReferenceNumberRequest::class);
        $resp    = $client->getClient(ModeEnum::RETRIEVAL)->getDdsInfoByInternalReferenceNumber($dto);

        $this->assertIsArray($resp->statementInfo, 'La réponse doit être un tableau de GetDdsInfoResponse');
        if (\count($resp->statementInfo) > 0) {
            $this->assertInstanceOf(GetDdsInfoResponse::class, $resp->statementInfo[0]);
        }
    }

    public function testGetStatementByIdentifiersOnline(): void
    {
        $fixtures = $this->loadFixturesOrSkip();
        $client   = $this->makeClientOrSkip();

        /** @var array{referenceNumber: string, verificationNumber: string} $payload */
        $payload = $this->getPayloadOrSkip($fixtures, 'retrieval', 'getStatementByIdentifiers');
        $dto     = $this->serializer->fromArray($payload, GetStatementByIdentifiersRequest::class);
        $resp    = $client->getClient(ModeEnum::RETRIEVAL)->getStatementByIdentifiers($dto);

        $this->assertNotNull($resp);
        $this->assertIsString($resp->referenceNumber, 'Le champ referenceNumber doit être une chaîne de caractères');
        $this->assertMatchesRegularExpression('/^[0-9A-Z]{14}$/i', $resp->referenceNumber, 'Le champ referenceNumber ne correspond pas au format attendu (14 caractères alphanumériques en majuscules)');
        $this->assertIsString($resp->activityType->value, 'Le champ activityType doit être une chaîne de caractères');
    }

    public function testGetReferencedDdsOnline(): void
    {
        /*$fixtures = $this->loadFixturesOrSkip();
        //$client   = $this->makeClientOrSkip();

         @var array{referenceNumber: string, referenceDdsVerificationNumber: string} $payload
        //$payload = $this->getPayloadOrSkip($fixtures, 'retrieval', 'getReferencedDds');
        //$dto = DdsWsdlDtoFactory::getReferencedDds($payload);
        //$resp    = $client->getClient(ModeEnum::RETRIEVAL)->getStatementByIdentifiers($dto);

        //$this->assertNotNull($dto);
        //$this->markTestSkipped('No use case to call getReferencedDds online; DTO building validated.'); */
    }
}
