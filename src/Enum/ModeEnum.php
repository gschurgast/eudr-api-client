<?php

declare(strict_types=1);

namespace src\Enum;

use src\Services\BaseSoapService;
use src\Services\EudrEchoClient;
use src\Services\EudrRetrievalClient;
use src\Services\EudrSubmissionClient;

/**
 * Represents the target environment for the EUDR SOAP services.
 */
enum ModeEnum: string
{
    case ECHO       = 'echo';
    case SUBMISSION = 'submission';
    case RETRIEVAL  = 'retrieval';

    /**
     * Returns the url for the selected mode.
     */
    public function geturl(): string
    {
        return match ($this) {
            self::ECHO       => '/tracesnt/ws/EudrEchoService?wsdl',
            self::SUBMISSION => '/tracesnt/ws/EUDRSubmissionServiceV2?wsdl',
            self::RETRIEVAL  => '/tracesnt/ws/EUDRRetrievalServiceV2?wsdl',
        };
    }

    public function getWebServiceClient(): BaseSoapService
    {
        return match ($this) {
            self::ECHO       => new EudrEchoClient(),
            self::SUBMISSION => new EudrSubmissionClient(),
            self::RETRIEVAL  => new EudrRetrievalClient(),
        };
    }
}
