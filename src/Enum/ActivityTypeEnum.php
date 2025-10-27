<?php

declare(strict_types=1);

namespace src\Enum;

/**
 * Represents the target environment for the EUDR SOAP services.
 */
enum ActivityTypeEnum: string
{
    case IMPORT   = 'IMPORT';
    case EXPORT   = 'EXPORT';
    case DOMESTIC = 'DOMESTIC';
}
