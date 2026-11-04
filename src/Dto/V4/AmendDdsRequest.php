<?php

declare(strict_types=1);

namespace src\Dto\V4;

use src\Dto\V4\Type\DueDiligenceStatementType;
use src\Serializer\JmsSerializationTrait;

class AmendDdsRequest
{
    use JmsSerializationTrait;

    public string $uuid;

    public DueDiligenceStatementType $statement;
}
