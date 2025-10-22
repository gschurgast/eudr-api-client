<?php

declare(strict_types=1);

namespace src\Services;

use src\Enum\Environment;
use src\Enum\Mode;
use src\Request\TestEchoRequest;

class EudrEchoClient extends BaseSoapService
{
    /** Example wrapper for the testEcho operation (signature depends on WSDL). */
    public function testEcho(Environment $environment, TestEchoRequest $request): mixed
    {
        return $this->buildSoapClient($environment)->__soapCall('testEcho', [$request]);
    }

    protected function getMode(): Mode
    {
        return Mode::ECHO;
    }
}
