<?php

declare(strict_types=1);

namespace src\Services;

use LogicException;
use Soap\EudrSoapClient;
use SoapClient;
use src\Enum\Environment;
use src\Enum\Mode;

class EudrEchoClient extends BaseSoapService
{

    /** Example wrapper for the testEcho operation (signature depends on WSDL). */
    public function testEcho(Environment $environment, array $request): mixed
    {
        $client = $this->getSoapClient($environment, true);
        return $client->__soapCall('testEcho', [$request]);
    }

    protected function getSoapClient(
        Environment $environment = Environment::ACCEPTANCE,
        bool $authentified = true
    ): SoapClient {
        return $this->buildSoapClient($environment, Mode::ECHO, $authentified);
    }
}
