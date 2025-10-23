<?php

namespace src\Dto;

use src\Dto\Type\StatementType;
use src\Serializer\JmsSerializationTrait;

class AmendDdsRequest
{
    use JmsSerializationTrait;

    public string $ddsIdentifier;

    public StatementType $statement;
}
