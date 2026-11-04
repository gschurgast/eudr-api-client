<?php

declare(strict_types=1);

namespace Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use src\Dto\V4\AmendDdsRequest;
use src\Dto\V4\GetDdsByInternalReferenceRequest;
use src\Dto\V4\GetDdsRequest;
use src\Dto\V4\SubmitDdsRequest;
use src\Dto\V4\WithdrawDdsRequest;
use src\Enum\EnvironmentEnum;
use src\Serializer\SerializerFactory;
use src\Services\EudrDueDiligenceStatementClient;
use Symfony\Component\Yaml\Yaml;

/**
 * Sends each V4 request to acceptance with dummy credentials, then validates what left the client
 * against the official V4 schemas (tests/fixtures/xsd/v4). The server rejects the credentials; no account is needed.
 */
final class DueDiligenceStatementV4RequestTest extends TestCase
{
    private const XSD_DIR = __DIR__ . '/../fixtures/xsd/v4/';

    private const UUID = '7ba034e0-23d2-4fb8-9803-c5af67d2adde';

    /**
     * @return iterable<string, array{string, object}>
     */
    public static function requests(): iterable
    {
        /** @var array{dueDiligenceStatementV4: array{submitDds: array{statement: array{commodities: list<array<string, mixed>>}}}} $fixtures */
        $fixtures   = Yaml::parseFile(__DIR__ . '/../fixtures/payloads.yaml');
        $submitDds  = $fixtures['dueDiligenceStatementV4']['submitDds'];
        $serializer = SerializerFactory::get();

        yield 'submitDds' => ['submitDds', $serializer->fromArray($submitDds, SubmitDdsRequest::class)];

        $furniture                                             = $submitDds;
        $furniture['statement']['commodities'][0]['hsHeading'] = '940360';
        yield 'submitDds with a non-wood HS heading' => ['submitDds', $serializer->fromArray($furniture, SubmitDdsRequest::class)];

        yield 'amendDds' => ['amendDds', $serializer->fromArray(['uuid' => self::UUID, 'statement' => $submitDds['statement']], AmendDdsRequest::class)];

        $withdraw       = new WithdrawDdsRequest();
        $withdraw->uuid = self::UUID;
        yield 'withdrawDds' => ['withdrawDds', $withdraw];

        $get           = new GetDdsRequest();
        $get->uuidList = [self::UUID, '9f0ebccd-5fc0-4321-b93a-c732efda7a46'];
        yield 'getDds' => ['getDds', $get];

        $byReference                    = new GetDdsByInternalReferenceRequest();
        $byReference->internalReference = 'TEST-V4';
        yield 'getDdsByInternalReference' => ['getDdsByInternalReference', $byReference];
    }

    #[DataProvider('requests')]
    public function testRequestBodyMatchesSchema(string $operation, object $request): void
    {
        $sent = $this->send($this->makeClient(), $operation, $request);

        $body = $sent->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Body')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $body);
        $this->assertInstanceOf(\DOMElement::class, $body->firstElementChild);

        $this->assertMatchesSchema($body->firstElementChild, 'due-diligence-statement-v4.xsd');
    }

    public function testBodyIdentityHeaderMatchesSchema(): void
    {
        $withdraw       = new WithdrawDdsRequest();
        $withdraw->uuid = self::UUID;

        $sent = $this->send($this->makeClient()->setOperatorAccessIdentifier('OPAI-TEST-1'), 'withdrawDds', $withdraw);

        $bodyIdentity = $sent->getElementsByTagNameNS('http://ec.europa.eu/tracesnt/body/v4', 'BodyIdentity')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $bodyIdentity);
        $this->assertSame('OPAI-TEST-1', $bodyIdentity->textContent);

        $this->assertMatchesSchema($bodyIdentity, 'body-v4.xsd');
    }

    public function testBodyIdentityHeaderIsOmittedByDefault(): void
    {
        $withdraw       = new WithdrawDdsRequest();
        $withdraw->uuid = self::UUID;

        $sent = $this->send($this->makeClient(), 'withdrawDds', $withdraw);

        $this->assertSame(0, $sent->getElementsByTagNameNS('http://ec.europa.eu/tracesnt/body/v4', 'BodyIdentity')->length);
    }

    private function makeClient(): EudrDueDiligenceStatementClient
    {
        return (new EudrDueDiligenceStatementClient())
            ->setEnvironment(EnvironmentEnum::ACCEPTANCE)
            ->setUsername('dummy-user')
            ->setPassword('dummy-password');
    }

    private function send(EudrDueDiligenceStatementClient $client, string $operation, object $request): \DOMDocument
    {
        try {
            $client->$operation($request);
        } catch (\SoapFault) {
        }

        $sent = new \DOMDocument();
        $sent->loadXML((string) $client->buildSoapClient()->__getLastRequest());

        return $sent;
    }

    private function assertMatchesSchema(\DOMElement $element, string $schema): void
    {
        $document = new \DOMDocument();
        $document->appendChild($document->importNode($element, true));

        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        $valid  = $document->schemaValidate(self::XSD_DIR . $schema);
        $errors = array_map(static fn (\LibXMLError $error): string => trim($error->message), libxml_get_errors());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $this->assertTrue($valid, implode("\n", $errors));
    }
}
