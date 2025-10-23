<?php

namespace src\Dto;

use src\Dto\Type\StatementType;
use src\Enum\OperatorTypeEnum;

/**
 * @phpstan-import-type StatementArray from Type\StatementType
 */
class SubmitDdsRequest
{
    public OperatorTypeEnum $operatorType;

    public StatementType $statement;

    /**
     * @return array{
     *      operatorType: 'OPERATOR'|'TRADER',
     *      statement: StatementArray
     * }
     */
    public function toArray(): array
    {
        return [
            'operatorType' => $this->operatorType->value,
            'statement'    => $this->statement->toArray(),
        ];
    }
}
