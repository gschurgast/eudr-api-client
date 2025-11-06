<?php

declare(strict_types=1);

namespace src\Services;

use src\Dto\TestEchoRequest;
use src\Dto\TestEchoResponse;
use src\Enum\ModeEnum;

class EudrEchoClient extends BaseSoapService
{
    /** Example wrapper for the testEcho operation (signature depends on WSDL). */
    public function testEcho(TestEchoRequest $request): TestEchoResponse
    {
        $raw = $this->sendRequest('testEcho', [$request]);

        return TestEchoResponse::fromSoap($raw);
    }

    protected function getMode(): ModeEnum
    {
        return ModeEnum::ECHO;
    }
}
