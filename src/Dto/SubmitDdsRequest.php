<?php

namespace src\Dto;

use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\VirtualProperty;
use src\Dto\Type\StatementType;
use src\Enum\OperatorTypeEnum;
use src\Serializer\JmsSerializationTrait;

class SubmitDdsRequest
{
    use JmsSerializationTrait;

    #[Exclude]
    public OperatorTypeEnum $operatorType;

    public StatementType $statement;

    #[VirtualProperty]
    #[SerializedName('operatorType')]
    #[Type('string')]
    public function getOperatorTypeValue(): string
    {
        return $this->operatorType->value;
    }
}
