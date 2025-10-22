<?php

namespace src\Request;

use src\Enum\OperatorTypeEnum;
use src\Request\Type\StatementType;

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
