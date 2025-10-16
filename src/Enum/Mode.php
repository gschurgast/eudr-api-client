<?php

declare(strict_types=1);

namespace src\Enum;

/**
 * Represents the target environment for the EUDR SOAP services.
 */
enum Mode: string
{
    case ECHO = 'echo';
    case RETRIEVAL = 'retrieval';
    case SUBMISSION = 'submission';

    /**
     * Returns the url for the selected mode.
     */
    public function geturl(): string
    {
        return match ($this) {
            self::ECHO => '/tracesnt/ws/EudrEchoService?wsdl',
            self::RETRIEVAL => '/tracesnt/ws/EUDRRetrievalServiceV2?wsdl',
            self::SUBMISSION => '/tracesnt/ws/EUDRSubmissionServiceV2?wsdl',
        };
    }
}
