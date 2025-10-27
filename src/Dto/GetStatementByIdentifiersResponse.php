<?php

declare(strict_types=1);

namespace src\Dto;

use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use src\Dto\Type\CommodityType;
use src\Dto\Type\OperatorResponseType;
use src\Dto\Type\StatusType;
use src\Enum\ActivityTypeEnum;
use src\Serializer\JmsSerializationTrait;

final class GetStatementByIdentifiersResponse
{
    use JmsSerializationTrait;

    public ?string $referenceNumber = null;

    #[SerializedName('activityType')]
    #[Type('string')]
    #[Accessor(getter: 'getActivityTypeValue', setter: 'setActivityTypeValue')]
    public ?ActivityTypeEnum $activityType = null;

    public ?StatusType $status = null;

    public ?OperatorResponseType $operator = null;

    /** @var CommodityType[] */
    #[Type('array<src\\Dto\\Type\\CommodityType>')]
    public ?array $commodities = [];

    public function getActivityTypeValue(): ?string
    {
        return $this->activityType?->value;
    }

    public function setActivityTypeValue(?string $value): void
    {
        $this->activityType = $value !== null ? ActivityTypeEnum::tryFrom($value) : null;
    }
}
