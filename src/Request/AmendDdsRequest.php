<?php

namespace src\Request;

use src\Request\Type\StatementType;

class AmendDdsRequest
{
    public string $ddsIdentifier;
    public StatementType $statement;
}
