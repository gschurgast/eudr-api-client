<?php

namespace Tests\Functional;

use JMS\Serializer\Serializer;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use src\Dto\TestEchoRequest;
use src\Dto\V4\GetDdsRequest;
use src\Dto\V4\SubmitDdsRequest as SubmitDdsRequestV4;
use src\Dto\V4\WithdrawDdsRequest;
use src\Enum\EnvironmentEnum;
use src\Enum\ModeEnum;
use src\Serializer\SerializerFactory;
use src\Services\EudrClient;
use Symfony\Component\Yaml\Yaml;

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

        $operatorAccessIdentifier = getenv('EUDR_OPERATOR_ACCESS_IDENTIFIER') ?: ($_ENV['EUDR_OPERATOR_ACCESS_IDENTIFIER'] ?? null);

        return new EudrClient($username, $password, $env, $operatorAccessIdentifier ?: null);
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
     * @return string uuid of the submitted DDS
     */
    public function testSubmitDdsV4Online(): string
    {
        $fixtures = $this->loadFixturesOrSkip();
        $client   = $this->makeClientOrSkip();

        $payload                                 = $this->getPayloadOrSkip($fixtures, 'dueDiligenceStatementV4', 'submitDds');
        $dto                                     = $this->serializer->fromArray($payload, SubmitDdsRequestV4::class);
        $dto->statement->internalReferenceNumber = 'TEST-V4-' . date('YmdHis');

        $resp = $client->getClient(ModeEnum::DUE_DILIGENCE_STATEMENT_V4)->submitDds($dto);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $resp->uuid);

        return $resp->uuid;
    }

    #[Depends('testSubmitDdsV4Online')]
    public function testGetDdsV4Online(string $uuid): void
    {
        $client = $this->makeClientOrSkip();

        $dto           = new GetDdsRequest();
        $dto->uuidList = [$uuid];
        $resp          = $client->getClient(ModeEnum::DUE_DILIGENCE_STATEMENT_V4)->getDds($dto);

        $this->assertCount(1, $resp->ddsOverviewList);
        $this->assertSame($uuid, $resp->ddsOverviewList[0]->uuid);
        $this->assertIsString($resp->ddsOverviewList[0]->status);
    }

    #[Depends('testSubmitDdsV4Online')]
    #[Depends('testGetDdsV4Online')]
    public function testWithdrawDdsV4Online(string $uuid): void
    {
        $client = $this->makeClientOrSkip();

        $dto       = new WithdrawDdsRequest();
        $dto->uuid = $uuid;
        $resp      = $client->getClient(ModeEnum::DUE_DILIGENCE_STATEMENT_V4)->withdrawDds($dto);

        $this->assertSame($uuid, $resp->uuid);
        $this->assertSame('WITHDRAWN', $resp->status);
    }
}
