<?php

namespace src\Factory;

use src\Dto\AmendDdsRequest;
use src\Dto\GetDdsInfoByInternalReferenceNumberRequest;
use src\Dto\GetDdsInfoRequest;
use src\Dto\GetReferenceDdsRequest;
use src\Dto\GetStatementByIdentifiersRequest;
use src\Dto\RetractDdsRequest;
use src\Dto\SubmitDdsRequest;
use src\Dto\TestEchoRequest;
use src\Dto\Type\CommodityType;
use src\Dto\Type\DescriptorsType;
use src\Dto\Type\GoodsMeasureType;
use src\Dto\Type\OperatorNameAndAddressType;
use src\Dto\Type\OperatorType;
use src\Dto\Type\ProducerType;
use src\Dto\Type\SpeciesInfoType;
use src\Dto\Type\StatementType;
use src\Enum\OperatorTypeEnum;
use src\Enum\WoodHeadingEnum;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type StatementArray from \src\Dto\Type\StatementType
 */
class DdsWsdlDtoFactory
{
    /**
     * @phpstan-param array{query: string} $data
     */
    public static function testEcho(array $data): TestEchoRequest
    {
        $dto        = new TestEchoRequest();
        $dto->query = $data['query'];

        return $dto;
    }

    /**
     * @phpstan-param array{
     *     operatorType: string,
     *     statement: StatementArray
     * } $data
     */
    public static function submitDds(array $data): SubmitDdsRequest
    {
        $dto               = new SubmitDdsRequest();
        $dto->operatorType = OperatorTypeEnum::tryFrom($data['operatorType']) ?? throw new \InvalidArgumentException('Invalid operatorType');
        $dto->statement    = self::buildStatementType($data['statement']);

        return $dto;
    }

    /**
     * @phpstan-param array{
     *     ddsIdentifier: string,
     *     statement: StatementArray
     * } $data
     */
    public static function amendDds(array $data): AmendDdsRequest
    {
        $dto                = new AmendDdsRequest();
        $dto->ddsIdentifier = $data['ddsIdentifier'];
        $dto->statement     = self::buildStatementType($data['statement']);

        return $dto;
    }

    /**
     * @phpstan-param array{ddsIdentifier: string,reason: string} $data
     */
    public static function retractDds(array $data): RetractDdsRequest
    {
        $dto                = new RetractDdsRequest();
        $dto->ddsIdentifier = $data['ddsIdentifier'];
        $dto->reason        = $data['reason'];

        return $dto;
    }

    /**
     * @phpstan-param array{identifier: string} $data
     */
    public static function getDdsInfo(array $data): GetDdsInfoRequest
    {
        $dto             = new GetDdsInfoRequest();
        $dto->identifier = $data['identifier'];

        return $dto;
    }

    /**
     * @phpstan-param array{identifier: string} $data
     */
    public static function getDdsInfoByInternalReferenceNumber(array $data): GetDdsInfoByInternalReferenceNumberRequest
    {
        $dto             = new GetDdsInfoByInternalReferenceNumberRequest();
        $dto->identifier = $data['identifier'];

        return $dto;
    }

    /**
     * @phpstan-param array{referenceNumber: string,verificationNumber:string } $data
     */
    public static function getStatementByIdentifiers(array $data): GetStatementByIdentifiersRequest
    {
        $dto                     = new GetStatementByIdentifiersRequest();
        $dto->referenceNumber    = $data['referenceNumber'];
        $dto->verificationNumber = $data['verificationNumber'];

        return $dto;
    }

    /**
     * @phpstan-param array{referenceNumber: string,referenceDdsVerificationNumber:string } $data
     */
    public static function getReferencedDds(array $data): GetReferenceDdsRequest
    {
        $dto                                 = new GetReferenceDdsRequest();
        $dto->referenceNumber                = $data['referenceNumber'];
        $dto->referenceDdsVerificationNumber = $data['referenceDdsVerificationNumber'];

        return $dto;
    }

    /**
     * @phpstan-param StatementArray $statementData
     */
    private static function buildStatementType(array $statementData): StatementType
    {
        $statement = new StatementType();

        $statement->internalReferenceNumber = $statementData['internalReferenceNumber'] ?? '';
        $statement->activityType            = $statementData['activityType'];
        $statement->countryOfActivity       = $statementData['countryOfActivity'];
        $statement->borderCrossCountry      = $statementData['borderCrossCountry'];
        $statement->comment                 = $statementData['comment'];
        $statement->geoLocationConfidential = (bool) ($statementData['geoLocationConfidential'] ?? false);

        // 3️⃣ Commodities (liste)
        foreach ($statementData['commodities'] ?? [] as $commodityData) {
            $commodity = new CommodityType();

            // --- Descriptors
            if (isset($commodityData['descriptors'])) {
                $desc                     = new DescriptorsType();
                $desc->descriptionOfGoods = $commodityData['descriptors']['descriptionOfGoods'] ?? null;

                if (isset($commodityData['descriptors']['goodsMeasure'])) {
                    $gm            = new GoodsMeasureType();
                    $gm->netWeight = isset($commodityData['descriptors']['goodsMeasure']['netWeight'])
                        ? (float) $commodityData['descriptors']['goodsMeasure']['netWeight']
                        : null;
                    $gm->volume = isset($commodityData['descriptors']['goodsMeasure']['volume'])
                        ? (float) $commodityData['descriptors']['goodsMeasure']['volume']
                        : null;
                    $desc->goodsMeasure = $gm;
                }

                $commodity->descriptors = $desc;
            }

            // --- HS heading
            $commodity->hsHeading = WoodHeadingEnum::tryFrom($commodityData['hsHeading']) ?? null;

            // --- Species Info (peut être un seul élément ou une liste)
            $speciesList = $commodityData['speciesInfo'] ?? [];
            if (isset($speciesList['scientificName'])) {
                // Cas d’un seul élément
                $speciesList = [$speciesList];
            }

            foreach ($speciesList as $speciesData) {
                $species                  = new SpeciesInfoType();
                $species->scientificName  = $speciesData['scientificName'] ?? null;
                $species->commonName      = $speciesData['commonName'] ?? null;
                $commodity->speciesInfo[] = $species;
            }

            // --- Producers (peut être un seul élément ou une liste)
            $producerList = $commodityData['producers'] ?? [];
            if (isset($producerList['name'])) {
                $producerList = [$producerList];
            }

            foreach ($producerList as $producerData) {
                $producer          = new ProducerType();
                $producer->country = $producerData['country'] ?? null;
                $producer->name    = $producerData['name'] ?? null;

                if (!empty($producerData['geometryGeojson'])) {
                    $geo = $producerData['geometryGeojson'];

                    // Si c’est un tableau, on l’encode en JSON purCom
                    if (\is_array($geo)) {
                        $geo = json_encode($geo, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);
                    } // Si c’est du JSON brut valide, on le garde tel quel
                    elseif (\is_string($geo) && json_decode($geo) !== null) {
                        // ne rien faire
                    } else {
                        throw new \RuntimeException('Invalid geometryGeojson format');
                    }
                    Assert::string($geo);

                    // On affecte directement le JSON, sans base64
                    $producer->geometryGeojson = $geo;
                }

                $commodity->producers[] = $producer;
            }

            $statement->commodities[] = $commodity;
        }

        if (isset($statementData['operator'])) {
            $operatorData = $statementData['operator'];

            $operator        = new OperatorType();
            $operator->email = $operatorData['email'] ?? null;
            $operator->phone = $operatorData['phone'] ?? null;

            if (isset($operatorData['nameAndAddress'])) {
                $addr                     = new OperatorNameAndAddressType();
                $addr->name               = $operatorData['nameAndAddress']['name'] ?? '';
                $addr->country            = $operatorData['nameAndAddress']['country'] ?? '';
                $addr->address            = $operatorData['nameAndAddress']['address'] ?? '';
                $operator->nameAndAddress = $addr;
            }

            $statement->operator = $operator;
        }

        return $statement;
    }
}
