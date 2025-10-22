<?php

namespace src\Request\Type;

class OperatorType
{
    public ?OperatorNameAndAddressType $nameAndAddress = null;
    public ?string $email = null;
    public ?string $phone = null;
}
