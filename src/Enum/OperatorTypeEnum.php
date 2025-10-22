<?php

declare(strict_types=1);

namespace src\Enum;

/**
 * Represents the target environment for the EUDR SOAP services.
 */
enum OperatorTypeEnum: string
{
    case OPERATOR = 'OPERATOR';
    case TRADER = 'TRADER';

}
