<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Type;

/**
 * @phpstan-type StatementArray array{
 *     internalReferenceNumber?: string,
 *     activityType: string,
 *     countryOfActivity: string,
 *     borderCrossCountry: string,
 *     comment: string,
 *     geoLocationConfidential?: bool,
 *     commodities?: list<array<string, mixed>>,
 *     operator?: array<string, mixed>
 * }
 */
class StatementType
{
    public string $internalReferenceNumber;

    public string $activityType;

    public ?string $comment = null;

    public ?string $countryOfActivity = null;

    public ?string $borderCrossCountry = null;

    /** @var CommodityType[]|null */
    #[Type('array<src\\Dto\\Type\\CommodityType>')]
    public ?array $commodities = null; // array of CommodityType

    public ?OperatorType $operator = null;

    public bool $geoLocationConfidential = false;
}
