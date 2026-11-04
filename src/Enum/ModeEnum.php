<?php

declare(strict_types=1);

namespace src\Enum;

use src\Services\BaseSoapService;
use src\Services\EudrDueDiligenceStatementClient;
use src\Services\EudrEchoClient;

/**
 * Represents the target environment for the EUDR SOAP services.
 */
enum ModeEnum: string
{
    case ECHO                       = 'echo';
    case DUE_DILIGENCE_STATEMENT_V4 = 'due_diligence_statement_v4';

    /**
     * Returns the url for the selected mode.
     */
    public function geturl(): string
    {
        return match ($this) {
            self::ECHO                       => '/tracesnt/ws/EudrEchoService?wsdl',
            self::DUE_DILIGENCE_STATEMENT_V4 => '/tracesnt/ws/EUDRDueDiligenceStatementServiceV4?wsdl',
        };
    }

    public function getWebServiceClient(): BaseSoapService
    {
        return match ($this) {
            self::ECHO                       => new EudrEchoClient(),
            self::DUE_DILIGENCE_STATEMENT_V4 => new EudrDueDiligenceStatementClient(),
        };
    }
}
