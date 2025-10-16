<?php

declare(strict_types=1);

namespace Soap;

use SoapClient;
use SoapFault;
use src\Enum\Environment;
use src\Enum\Mode;

/**
 * Minimal SOAP client wrapper for EUDR services.
 *
 * Note: Full WS-Security (UsernameToken, signatures) is not implemented here.
 * This class focuses on constructing a native SoapClient pointed to the
 * correct environment endpoint and WSDL. Extend as needed for security.
 */
class EudrSoapClient
{
    protected ?string $username;

    protected ?string $password;

    

    /**
     * Internal helper to build a SoapClient for a given WSDL path, with common defaults.
     * Optionally forbids usage on PRODUCTION.
     *
     * @return SoapClient
     * @throws SoapFault
     */
    public function buildServiceClient(
        Environment $environment = Environment::ACCEPTANCE,
        Mode $mode = Mode::ECHO,
        bool $auth = true,
    ): SoapClient
    {
        
    }
}
