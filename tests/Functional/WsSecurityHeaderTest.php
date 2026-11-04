<?php

declare(strict_types=1);

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use src\Dto\TestEchoRequest;
use src\Enum\EnvironmentEnum;
use src\Services\EudrEchoClient;

/**
 * Calls the acceptance Echo service with dummy credentials: the server rejects them,
 * but the request that left the client is still readable through the SOAP trace.
 */
final class WsSecurityHeaderTest extends TestCase
{
    public function testEachCallSendsAFreshNonce(): void
    {
        $client = (new EudrEchoClient())
            ->setEnvironment(EnvironmentEnum::ACCEPTANCE)
            ->setUsername('dummy-user')
            ->setPassword('dummy-password');

        $this->assertNotSame($this->sentNonce($client), $this->sentNonce($client));
    }

    private function sentNonce(EudrEchoClient $client): string
    {
        $request        = new TestEchoRequest();
        $request->query = 'nonce check';

        try {
            $client->testEcho($request);
        } catch (\SoapFault) {
        }

        $this->assertSame(1, preg_match('#<wsse:Nonce[^>]*>([^<]+)</wsse:Nonce>#', (string) $client->buildSoapClient()->__getLastRequest(), $matches));

        return $matches[1];
    }
}
