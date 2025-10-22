<?php

namespace src\Request;

use src\Request\Type\StatementType;

class SubmitDdsRequest
{
    public string $operatorType;
    public StatementType $statement;
}
