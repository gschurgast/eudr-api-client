<?php

declare(strict_types=1);

namespace src\Services;

use RobRichards\WsePhp\WSSESoap;
use src\Enum\EnvironmentEnum;
use src\Enum\ModeEnum;
use Webmozart\Assert\Assert;

/**
 * Base class for EUDR SOAP clients.
 *
 * These method annotations document the operations implemented by concrete
 * clients (EudrEchoClient, EudrSubmissionClient, EudrRetrievalClient,
 * EudrDueDiligenceStatementClient) so that static analysers (e.g. PHPStan)
 * can understand dynamic calls like $client->$op($env, $dto) when $client is
 * typed as BaseSoapService.
 *
 * @method mixed testEcho(\src\Dto\TestEchoRequest $request)
 * @method mixed submitDds(\src\Dto\SubmitDdsRequest|\src\Dto\V4\SubmitDdsRequest $request)
 * @method mixed amendDds(\src\Dto\AmendDdsRequest|\src\Dto\V4\AmendDdsRequest $request)
 * @method mixed retractDds(\src\Dto\RetractDdsRequest $request)
 * @method mixed withdrawDds(\src\Dto\V4\WithdrawDdsRequest $request)
 * @method mixed getDds(\src\Dto\V4\GetDdsRequest $request)
 * @method mixed getDdsByInternalReference(\src\Dto\V4\GetDdsByInternalReferenceRequest $request)
 * @method mixed getDdsInfo(\src\Dto\GetDdsInfoRequest $request)
 * @method mixed getDdsInfoByInternalReferenceNumber(\src\Dto\GetDdsInfoByInternalReferenceNumberRequest $request)
 * @method mixed getStatementByIdentifiers(\src\Dto\GetStatementByIdentifiersRequest $request)
 * @method mixed getReferencedDds(\src\Dto\GetReferenceDdsRequest $request)
 */
abstract class BaseSoapService
{
    protected ?string $username = null;

    protected ?string $password = null;

    protected EnvironmentEnum $environment = EnvironmentEnum::ACCEPTANCE;

    /** @var \SoapClient|null Cached SOAP client for reuse between calls. */
    private ?\SoapClient $soapClient = null;

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function setEnvironment(EnvironmentEnum $environment): static
    {
        $this->environment = $environment;
        $this->resetSoapClient();

        return $this;
    }

    /**
     * Returns the cached client with a new WS-Security header: the nonce is single-use and the timestamp expires.
     */
    public function buildSoapClient(): \SoapClient
    {
        if (!$this->soapClient instanceof \SoapClient) {
            if ($this->getMode() === ModeEnum::ECHO && $this->environment !== EnvironmentEnum::ACCEPTANCE) {
                throw new \LogicException('This service is not allowed on PRODUCTION environment.');
            }

            $this->soapClient = new \SoapClient($this->environment->getUrl($this->getMode()->geturl()), [
                'trace'          => true,
                'exceptions'     => true,
                'cache_wsdl'     => \WSDL_CACHE_NONE,
                'features'       => \SOAP_SINGLE_ELEMENT_ARRAYS,
                'stream_context' => stream_context_create([
                    'http' => [
                        'user_agent' => 'EUDR-PHP-SoapClient/1.0 (https://github.com/gschurgast/eudr-api-client)',
                    ],
                ]),
            ]);
        }

        $this->soapClient->__setSoapHeaders($this->createHeader());

        return $this->soapClient;
    }

    /**
     * Crée un header WS-Security conforme aux exigences (nonce, created, digest, expires, WebServiceClientId).
     */
    private function createHeader(): \SoapHeader
    {
        // Build a minimal SOAP envelope header via WSE-PHP to generate WS-Security elements
        $doc      = new \DOMDocument('1.0', 'UTF-8');
        $envelope = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');
        $header   = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Header');
        $body     = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Body');
        $envelope->appendChild($header);
        $envelope->appendChild($body);
        $doc->appendChild($envelope);

        $wsse = new WSSESoap($doc, false);
        // Add Timestamp and UsernameToken (PasswordDigest=true)
        $wsse->addTimestamp(60);

        if ($this->username === null || $this->password === null) {
            throw new \LogicException('Missing EUDR credentials. Please call setUsername() and setPassword() before making requests.');
        }
        $wsse->addUserToken($this->username, $this->password, true);

        // Extract the generated wsse:Security node as XML
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('wsse', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');

        $securityNodes = $xpath->query('//wsse:Security');
        Assert::isInstanceOf($securityNodes, \DOMNodeList::class);
        $securityNode = $securityNodes->item(0);
        Assert::isInstanceOf($securityNode, \DOMNode::class);

        $xmlSecurity = $doc->saveXML($securityNode);

        // Add the WebServiceClientId header (outside of wsse:Security)
        $clientId    = $this->environment->getWebServiceClientId();
        $xmlClientId = '<v4:WebServiceClientId xmlns:v4="http://ec.europa.eu/sanco/tracesnt/base/v4">'
            . htmlspecialchars($clientId, \ENT_XML1) . '</v4:WebServiceClientId>';

        $fullXmlHeader = $xmlSecurity . $xmlClientId . $this->additionalHeaderXml();

        return new \SoapHeader(
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new \SoapVar($fullXmlHeader, \XSD_ANYXML),
            true
        );
    }

    /**
     * Extra SOAP headers declared by the service WSDL, as raw XML.
     */
    protected function additionalHeaderXml(): string
    {
        return '';
    }

    private function resetSoapClient(): void
    {
        $this->soapClient = null;
    }

    abstract protected function getMode(): ModeEnum;
}
