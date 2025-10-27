<?php

namespace src\Dto\Type;

use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\VirtualProperty;
use src\Enum\ActivityTypeEnum;

/**
 * @phpstan-type StatementArray array{
 *     internalReferenceNumber?: string,
 *     activityType: ActivityTypeEnum,
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

    #[Exclude]
    public ActivityTypeEnum $activityType = ActivityTypeEnum::IMPORT;

    public ?string $comment = null;

    public ?string $countryOfActivity = null;

    public ?string $borderCrossCountry = null;

    /** @var CommodityType[]|null */
    #[Type('array<src\\Dto\\Type\\CommodityType>')]
    public ?array $commodities = null; // array of CommodityType

    public ?OperatorType $operator = null;

    public bool $geoLocationConfidential = false;

    #[VirtualProperty]
    #[SerializedName('activityType')]
    #[Type('string')]
    public function getActivityTypeValue(): ?string
    {
        return $this->activityType->value;
    }
}
