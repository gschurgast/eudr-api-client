<?php

declare(strict_types=1);

namespace src\Dto;

use JMS\Serializer\Annotation\Type;
use src\Dto\Type\CommodityType;
use src\Dto\Type\OperatorResponseType;
use src\Dto\Type\StatusType;
use src\Serializer\JmsSerializationTrait;

final class GetStatementByIdentifiersResponse
{
    use JmsSerializationTrait;

    public ?string $referenceNumber = null;

    public ?string $activityType = null;

    public ?StatusType $status = null;

    public ?OperatorResponseType $operator = null;

    /** @var CommodityType[] */
    #[Type('array<src\\Dto\\Type\\CommodityType>')]
    public ?array $commodities = [];
}
