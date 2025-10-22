<?php

declare(strict_types=1);

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use src\Enum\EnvironmentEnum;
use src\Enum\ModeEnum;
use src\Factory\DdsWsdlDtoFactory;
use src\Services\EudrClient;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-import-type StatementArray from \src\Request\Type\StatementType
 */
final class MigrationDemoTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Ensure Composer autoload is available when running without phpunit.xml bootstrap
        $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }
    }

    public function testBuildDtosFromFixtures(): void
    {
        $root = dirname(__DIR__, 2);
        $fixturesPath = $root . '/tests/fixtures/payloads.yaml';
        if (!file_exists($fixturesPath)) {
            $this->markTestSkipped('No fixtures file found at ' . $fixturesPath);
        }

        /** @var array<string, array<string, array<string, mixed>>> $fixtures */
        $fixtures = Yaml::parseFile($fixturesPath) ?? [];

        $built = 0;

        foreach ($fixtures as $section => $sectionFixtures) {
            foreach ($sectionFixtures as $op => $payload) {
                switch (ModeEnum::from($section)) {
                    case ModeEnum::ECHO:
                        if ($op === 'testEcho') {
                            /** @var array{query: string} $payload */
                            $dto = DdsWsdlDtoFactory::testEcho($payload);
                            $this->assertSame($payload['query'], $dto->query);
                            ++$built;
                        }
                        break;
                    case ModeEnum::SUBMISSION:
                        if ($op === 'submitDds') {
                            /** @var array{operatorType: string, statement: StatementArray} $payload */
                            $dto = DdsWsdlDtoFactory::submitDds($payload);
                            $this->assertSame($payload['operatorType'], $dto->operatorType->value);
                            ++$built;
                        } elseif ($op === 'amendDds') {
                            /** @var array{ddsIdentifier: string, statement: StatementArray} $payload */
                            $dto = DdsWsdlDtoFactory::amendDds($payload);
                            $this->assertSame($payload['ddsIdentifier'], $dto->ddsIdentifier);
                            ++$built;
                        } elseif ($op === 'retractDds') {
                            /** @var array{ddsIdentifier: string, reason: string} $payload */
                            $dto = DdsWsdlDtoFactory::retractDds($payload);
                            $this->assertSame($payload['ddsIdentifier'], $dto->ddsIdentifier);
                            ++$built;
                        }
                        break;
                    case ModeEnum::RETRIEVAL:
                        if ($op === 'getDdsInfo') {
                            /** @var array{identifier: string} $payload */
                            $dto = DdsWsdlDtoFactory::getDdsInfo($payload);
                            $this->assertSame($payload['identifier'], $dto->identifier);
                            ++$built;
                        } elseif ($op === 'getDdsInfoByInternalReferenceNumber') {
                            /** @var array{identifier: string} $payload */
                            $dto = DdsWsdlDtoFactory::getDdsInfoByInternalReferenceNumber($payload);
                            $this->assertSame($payload['identifier'], $dto->identifier);
                            ++$built;
                        } elseif ($op === 'getStatementByIdentifiers') {
                            /** @var array{referenceNumber: string, verificationNumber: string} $payload */
                            $dto = DdsWsdlDtoFactory::getStatementByIdentifiers($payload);
                            $this->assertSame($payload['referenceNumber'], $dto->referenceNumber);
                            ++$built;
                        } elseif ($op === 'getReferencedDds') {
                            /** @var array{referenceNumber: string, referenceDdsVerificationNumber: string} $payload */
                            $dto = DdsWsdlDtoFactory::getReferencedDds($payload);
                            $this->assertSame($payload['referenceNumber'], $dto->referenceNumber);
                            ++$built;
                        }
                        break;
                }
            }
        }

        $this->assertGreaterThan(0, $built, 'No DTOs were built from fixtures');
    }

    public function testOnlineCallsWithFixturesWhenEnabled(): void
    {

        $root = dirname(__DIR__, 2);
        $fixturesPath = $root . '/tests/fixtures/payloads.yaml';
        if (!file_exists($fixturesPath)) {
            $this->markTestSkipped('No fixtures file found at ' . $fixturesPath);
        }

        /** @var array<string, array<string, array<string, mixed>>> $fixtures */
        $fixtures = Yaml::parseFile($fixturesPath) ?? [];

        $env = EnvironmentEnum::ACCEPTANCE;
        $client = new EudrClient('n00hfgop', 'xlOPMeepcGgBnKkDWBbygrPeBi2ajSHpikqzJCsW', $env);
        $ddsIdentifier = null;
        foreach ($fixtures as $section => $sectionFixtures) {
            foreach ($sectionFixtures as $op => $payload) {
                switch (ModeEnum::from($section)) {
                    case ModeEnum::ECHO:
                        if ($op === 'testEcho') {
                            /** @var array{query: string} $payload */
                            $dto = DdsWsdlDtoFactory::testEcho($payload);
                            $resp = $client->getClient(ModeEnum::ECHO)->testEcho($dto);
                            // -- Vérification --
                            $this->assertIsString($resp->status, 'Le champ status doit être une chaîne de caractères');
                            $this->assertStringContainsString(
                                'hello from demo',
                                $resp->status,
                                "La réponse ne contient pas 'hello from demo'"
                            );
                        }
                        break;
                    case ModeEnum::SUBMISSION:
                        if ($op === 'submitDds') {
                            /** @var array{operatorType: string, statement: StatementArray} $payload */
                            $dto = DdsWsdlDtoFactory::submitDds($payload);
                            $resp = $client->getClient(ModeEnum::SUBMISSION)->submitDds($dto);
                            $this->assertNotNull($resp);
                            $ddsIdentifier = $resp->ddsIdentifier;
                        } elseif ($op === 'amendDds') {
                            /** @var array{ddsIdentifier: string, statement: StatementArray} $payload */
                            $dto = DdsWsdlDtoFactory::amendDds($payload);
                            $resp = $client->getClient(ModeEnum::SUBMISSION)->amendDds($dto);
                            $this->assertNotNull($resp);
                        } elseif ($op === 'retractDds') {
                            /** @var array{ddsIdentifier: string, reason: string} $payload */
                            $payload['ddsIdentifier'] = $ddsIdentifier;
                            $dto = DdsWsdlDtoFactory::retractDds($payload);
                            $resp = $client->getClient(ModeEnum::SUBMISSION)->retractDds($dto);
                            $this->assertNotNull($resp);
                        }
                        break;
                    case ModeEnum::RETRIEVAL:
                        if ($op === 'getDdsInfo') {
                            /** @var array{identifier: string} $payload */
                            $dto = DdsWsdlDtoFactory::getDdsInfo($payload);
                            $resp = $client->getClient(ModeEnum::RETRIEVAL)->getDdsInfo($dto);
                            $this->assertNotNull($resp);
                        } elseif ($op === 'getDdsInfoByInternalReferenceNumber') {
                            /** @var array{identifier: string} $payload */
                            $dto = DdsWsdlDtoFactory::getDdsInfoByInternalReferenceNumber($payload);
                            $resp = $client->getClient(ModeEnum::RETRIEVAL)->getDdsInfoByInternalReferenceNumber($dto);
                            $this->assertNotNull($resp);
                        } elseif ($op === 'getStatementByIdentifiers') {
                            /** @var array{referenceNumber: string, verificationNumber: string} $payload */
                            $dto = DdsWsdlDtoFactory::getStatementByIdentifiers($payload);
                            $resp = $client->getClient(ModeEnum::RETRIEVAL)->getStatementByIdentifiers($dto);
                            $this->assertNotNull($resp);
                        } elseif ($op === 'getReferencedDds') {
                            /** @var array{referenceNumber: string, referenceDdsVerificationNumber: string} $payload */
                            $dto = DdsWsdlDtoFactory::getReferencedDds($payload);
                            //Je commente cette ligne car nous n'avons pas de cas d'usage
                            //$resp = $client->getClient(ModeEnum::RETRIEVAL)->getReferencedDds($dto);
                            //$this->assertNotNull($resp);
                        }
                        break;
                }
            }
        }

        $this->addToAssertionCount(1); // mark test as having assertions if none executed above
    }
}
