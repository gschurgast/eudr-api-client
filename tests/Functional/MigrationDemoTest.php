<?php

declare(strict_types=1);

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use src\Dto\GetDdsInfoResponse;
use src\Enum\EnvironmentEnum;
use src\Enum\ModeEnum;
use src\Factory\DdsWsdlDtoFactory;
use src\Services\EudrClient;
use Symfony\Component\Yaml\Yaml;

/**
 * @phpstan-import-type StatementArray from \src\Dto\Type\StatementType
 */
final class MigrationDemoTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Ensure Composer autoload is available when running without phpunit.xml bootstrap
        $autoload = \dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }
    }

    public function testBuildDtosFromFixtures(): void
    {
        $root         = \dirname(__DIR__, 2);
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
                            $built++;
                        }
                        break;
                    case ModeEnum::SUBMISSION:
                        if ($op === 'submitDds') {
                            /** @var array{operatorType: string, statement: StatementArray} $payload */
                            $dto = DdsWsdlDtoFactory::submitDds($payload);
                            $this->assertSame($payload['operatorType'], $dto->operatorType->value);
                            $built++;
                        } elseif ($op === 'amendDds') {
                            /** @var array{ddsIdentifier: string, statement: StatementArray} $payload */
                            $dto = DdsWsdlDtoFactory::amendDds($payload);
                            $this->assertSame($payload['ddsIdentifier'], $dto->ddsIdentifier);
                            $built++;
                        } elseif ($op === 'retractDds') {
                            /** @var array{ddsIdentifier: string, reason: string} $payload */
                            $dto = DdsWsdlDtoFactory::retractDds($payload);
                            $this->assertSame($payload['ddsIdentifier'], $dto->ddsIdentifier);
                            $built++;
                        }
                        break;
                    case ModeEnum::RETRIEVAL:
                        if ($op === 'getDdsInfo') {
                            /** @var array{identifier: string} $payload */
                            $dto = DdsWsdlDtoFactory::getDdsInfo($payload);
                            $this->assertSame($payload['identifier'], $dto->identifier);
                            $built++;
                        } elseif ($op === 'getDdsInfoByInternalReferenceNumber') {
                            /** @var array{identifier: string} $payload */
                            $dto = DdsWsdlDtoFactory::getDdsInfoByInternalReferenceNumber($payload);
                            $this->assertSame($payload['identifier'], $dto->identifier);
                            $built++;
                        } elseif ($op === 'getStatementByIdentifiers') {
                            /** @var array{referenceNumber: string, verificationNumber: string} $payload */
                            $dto = DdsWsdlDtoFactory::getStatementByIdentifiers($payload);
                            $this->assertSame($payload['referenceNumber'], $dto->referenceNumber);
                            $built++;
                        } elseif ($op === 'getReferencedDds') {
                            /** @var array{referenceNumber: string, referenceDdsVerificationNumber: string} $payload */
                            $dto = DdsWsdlDtoFactory::getReferencedDds($payload);
                            $this->assertSame($payload['referenceNumber'], $dto->referenceNumber);
                            $built++;
                        }
                        break;
                }
            }
        }

        $this->assertGreaterThan(0, $built, 'No DTOs were built from fixtures');
    }

    public function testOnlineCallsWithFixturesWhenEnabled(): void
    {
        $root         = \dirname(__DIR__, 2);
        $fixturesPath = $root . '/tests/fixtures/payloads.yaml';
        if (!file_exists($fixturesPath)) {
            $this->markTestSkipped('No fixtures file found at ' . $fixturesPath);
        }

        /** @var array<string, array<string, array<string, mixed>>> $fixtures */
        $fixtures = Yaml::parseFile($fixturesPath) ?? [];

        $env           = EnvironmentEnum::ACCEPTANCE;
        $client        = new EudrClient('n00hfgop', 'xlOPMeepcGgBnKkDWBbygrPeBi2ajSHpikqzJCsW', $env);
        $ddsIdentifier = null;
        foreach ($fixtures as $section => $sectionFixtures) {
            foreach ($sectionFixtures as $op => $payload) {
                switch (ModeEnum::from($section)) {
                    case ModeEnum::ECHO:
                        if ($op === 'testEcho') {
                            /** @var array{query: string} $payload */
                            $dto  = DdsWsdlDtoFactory::testEcho($payload);
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
                            $dto  = DdsWsdlDtoFactory::submitDds($payload);
                            $resp = $client->getClient(ModeEnum::SUBMISSION)->submitDds($dto);
                            $this->assertIsString($resp->ddsIdentifier, 'Le champ ddsIdentifier doit être une chaîne de caractères');
                            $this->assertMatchesRegularExpression(
                                '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
                                $resp->ddsIdentifier,
                                'Le champ ddsIdentifier ne correspond pas au format UUID attendu (ex: 0542e3a8-7b89-4866-b00a-d8766b9ec74a)'
                            );

                            $ddsIdentifier = $resp->ddsIdentifier;
                        } elseif ($op === 'amendDds') {
                            /** @var array{ddsIdentifier: string, statement: StatementArray} $payload */
                            $dto  = DdsWsdlDtoFactory::amendDds($payload);
                            $resp = $client->getClient(ModeEnum::SUBMISSION)->amendDds($dto);
                            $this->assertIsString($resp->status, 'Le champ status doit être une chaîne de caractères');
                            $this->assertEquals(
                                'SC_200_OK',
                                $resp->status,
                                'Le champ status n\'est pas SC_200_OK'
                            );
                        } elseif ($op === 'retractDds') {
                            $payload['ddsIdentifier'] = $ddsIdentifier; // on recupere le ddsIdentifier de la premiere requete
                            /** @var array{ddsIdentifier: string, reason: string} $payload */
                            $dto  = DdsWsdlDtoFactory::retractDds($payload);
                            $resp = $client->getClient(ModeEnum::SUBMISSION)->retractDds($dto);
                            $this->assertIsString($resp->status, 'Le champ status doit être une chaîne de caractères');
                            $this->assertEquals(
                                'SC_200_OK',
                                $resp->status,
                                'Le champ status n\'est pas SC_200_OK'
                            );
                        }
                        break;
                    case ModeEnum::RETRIEVAL:
                        if ($op === 'getDdsInfo') {
                            /** @var array{identifier: string} $payload */
                            $dto  = DdsWsdlDtoFactory::getDdsInfo($payload);
                            $resp = $client->getClient(ModeEnum::RETRIEVAL)->getDdsInfo($dto);
                            $this->assertNotNull($resp);
                            $this->assertIsString($resp->identifier, 'Le champ identifier doit être une chaîne de caractères');

                            $this->assertEquals(
                                $dto->identifier,
                                $resp->identifier,
                                \sprintf('Le champ identifier n\'est pas %s ', $dto->identifier)
                            );

                            $this->assertIsString($resp->referenceNumber, 'Le champ referenceNumber doit être une chaîne de caractères');
                            $this->assertMatchesRegularExpression(
                                '/^[0-9A-Z]{14}$/i',
                                $resp->referenceNumber,
                                'Le champ referenceNumber ne correspond pas au format attendu (14 caractères alphanumériques en majuscules)'
                            );

                            $this->assertIsString($resp->verificationNumber, 'Le champ verificationNumber doit être une chaîne de caractères');
                            $this->assertMatchesRegularExpression(
                                '/^[0-9A-Z]{8}$/i',
                                $resp->verificationNumber,
                                'Le champ verificationNumber ne correspond pas au format attendu (8 caractères alphanumériques en majuscules)'
                            );

                            $this->assertIsString($resp->status, 'Le champ status doit être une chaîne de caractères');

                            $this->assertIsString($resp->updatedBy, 'Le champ updatedBy doit être une chaîne de caractères');
                        } elseif ($op === 'getDdsInfoByInternalReferenceNumber') {
                            /** @var array{identifier: string} $payload */
                            $dto  = DdsWsdlDtoFactory::getDdsInfoByInternalReferenceNumber($payload);
                            $resp = $client->getClient(ModeEnum::RETRIEVAL)->getDdsInfoByInternalReferenceNumber($dto);
                            $this->assertIsArray($resp->statementInfo, 'La réponse doit être un tableau de GetDdsInfoResponse');
                            if (\count($resp->statementInfo) > 0) {
                                $this->assertInstanceOf(GetDdsInfoResponse::class, $resp->statementInfo[0]);
                            }
                        } elseif ($op === 'getStatementByIdentifiers') {
                            /** @var array{referenceNumber: string, verificationNumber: string} $payload */
                            $dto  = DdsWsdlDtoFactory::getStatementByIdentifiers($payload);
                            $resp = $client->getClient(ModeEnum::RETRIEVAL)->getStatementByIdentifiers($dto);
                            $this->assertNotNull($resp);

                            $this->assertIsString($resp->referenceNumber, 'Le champ referenceNumber doit être une chaîne de caractères');
                            $this->assertMatchesRegularExpression(
                                '/^[0-9A-Z]{14}$/i',
                                $resp->referenceNumber,
                                'Le champ referenceNumber ne correspond pas au format attendu (14 caractères alphanumériques en majuscules)'
                            );
                            $this->assertIsString($resp->activityType, 'Le champ activityType doit être une chaîne de caractères');
                        } elseif ($op === 'getReferencedDds') {
                            /** @var array{referenceNumber: string, referenceDdsVerificationNumber: string} $payload */
                            $dto = DdsWsdlDtoFactory::getReferencedDds($payload);
                            // Je commente cette ligne car nous n'avons pas de cas d'usage
                            // $resp = $client->getClient(ModeEnum::RETRIEVAL)->getReferencedDds($dto);
                            // $this->assertNotNull($resp);
                        }
                        break;
                }
            }
        }

        $this->addToAssertionCount(1); // mark test as having assertions if none executed above
    }
}
