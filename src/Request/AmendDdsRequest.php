<?php

namespace src\Request;

use src\Request\Type\StatementType;

/**
 * @phpstan-import-type StatementArray from Type\StatementType
 */
class AmendDdsRequest
{
    public string $ddsIdentifier;
    public StatementType $statement;

    /**
     * @return array{
     *      ddsIdentifier: string,
     *      statement: StatementArray
     * }
     */
    public function toArray(): array
    {
        return [
            'ddsIdentifier' => $this->ddsIdentifier,
            'statement'     => $this->statement->toArray(),
        ];
    }
}
