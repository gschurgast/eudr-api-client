<?php

namespace src\Dto\V4;

use src\Dto\V4\Type\DueDiligenceStatementType;
use src\Serializer\JmsSerializationTrait;

/**
 * Only the OPERATOR role is supported: REPRESENTATIVE_OPERATOR also needs a representedOperator block.
 */
class SubmitDdsRequest
{
    use JmsSerializationTrait;

    public string $operatorRole = 'OPERATOR';

    public DueDiligenceStatementType $statement;
}
