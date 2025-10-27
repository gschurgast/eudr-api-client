<?php

namespace src\Dto;

use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use src\Dto\Type\StatementType;
use src\Enum\OperatorTypeEnum;
use src\Serializer\JmsSerializationTrait;

class SubmitDdsRequest
{
    use JmsSerializationTrait;

    #[SerializedName('operatorType')]
    #[Type('string')]
    #[Accessor(getter: 'getOperatorTypeValue', setter: 'setOperatorTypeValue')]
    public ?OperatorTypeEnum $operatorType = OperatorTypeEnum::OPERATOR;

    public StatementType $statement;

    public function getOperatorTypeValue(): ?string
    {
        return $this->operatorType?->value;
    }

    public function setOperatorTypeValue(?string $value): void
    {
        $this->operatorType = $value !== null ? (OperatorTypeEnum::tryFrom($value) ?? OperatorTypeEnum::OPERATOR) : null;
    }
}
