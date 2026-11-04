<?php

declare(strict_types=1);

namespace src\Dto\V4\Type;

use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use src\Enum\ActivityTypeEnum;

class DueDiligenceStatementType
{
    public ?string $internalReferenceNumber = null;

    #[SerializedName('activityType')]
    #[Type('string')]
    #[Accessor(getter: 'getActivityTypeValue', setter: 'setActivityTypeValue')]
    public ActivityTypeEnum $activityType = ActivityTypeEnum::IMPORT;

    public ?string $countryOfActivity = null;

    public ?string $borderCrossCountry = null;

    public ?string $comment = null;

    /** @var DdsCommodityType[] */
    #[Type('array<src\\Dto\\V4\\Type\\DdsCommodityType>')]
    public array $commodities = [];

    public bool $geoLocationConfidential = false;

    public function getActivityTypeValue(): string
    {
        return $this->activityType->value;
    }

    public function setActivityTypeValue(string $value): void
    {
        $this->activityType = ActivityTypeEnum::from($value);
    }
}
