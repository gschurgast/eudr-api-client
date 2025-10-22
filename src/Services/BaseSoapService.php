<?php

declare(strict_types=1);

namespace src\Services;

use RobRichards\WsePhp\WSSESoap;
use src\Enum\Environment;
use src\Enum\Mode;
use Webmozart\Assert\Assert;

/**
 * Base class for EUDR SOAP clients.
 *
 * These @method annotations document the operations implemented by concrete
 * clients (EudrEchoClient, EudrSubmissionClient, EudrRetrievalClient) so that
 * static analysers (e.g. PHPStan) can understand dynamic calls like
 * $client->$op($env, $dto) when $client is typed as BaseSoapService.
 *
 * @method mixed testEcho(Environment $environment, \src\Request\TestEchoRequest $request)
 * @method mixed submitDds(Environment $environment, \src\Request\SubmitDdsRequest $request)
 * @method mixed amendDds(Environment $environment, \src\Request\AmendDdsRequest $request)
 * @method mixed retractDds(Environment $environment, \src\Request\RetractDdsRequest $request)
 * @method mixed getDdsInfo(Environment $environment, \src\Request\GetDdsInfoRequest $request)
 * @method mixed getDdsInfoByInternalReferenceNumber(Environment $environment, \src\Request\GetDdsInfoByInternalReferenceNumberRequest $request)
 * @method mixed getStatementByIdentifiers(Environment $environment, \src\Request\GetStatementByIdentifiersRequest $request)
 * @method mixed getReferencedDds(Environment $environment, \src\Request\GetReferenceDdsRequest $request)
 */
abstract class BaseSoapService
{
    private string $username = 'n00hfgop';
    private string $authKey = 'xlOPMeepcGgBnKkDWBbygrPeBi2ajSHpikqzJCsW';

    public function buildSoapClient(Environment $environment = Environment::ACCEPTANCE): \SoapClient
    {
        if (Mode::ECHO === $this->getMode() && Environment::ACCEPTANCE !== $environment) {
            throw new \LogicException('This service is not allowed on PRODUCTION environment.');
        }

        $wsdl = $environment->getUrl($this->getMode()->geturl());

        $defaults = [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        ];

        if (!$environment->getSslVerify()) {
            $ctx = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
                'http' => [
                    'user_agent' => 'EUDR-PHP-SoapClient/1.0 (https://github.com/gschurgast/eudr-api-client)',
                ],
            ]);
            $defaults['stream_context'] = $ctx;
        }

        $client = new \SoapClient($wsdl, $defaults);

        $wsHeader = $this->createHeader($environment);
        $client->__setSoapHeaders($wsHeader);

        return $client;
    }

    /**
     * Crée un header WS-Security conforme aux exigences (nonce, created, digest, expires, WebServiceClientId).
     */
    private function createHeader(Environment $environment): \SoapHeader
    {
        // Build a minimal SOAP envelope header via WSE-PHP to generate WS-Security elements
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $envelope = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');
        $header = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Header');
        $body = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Body');
        $envelope->appendChild($header);
        $envelope->appendChild($body);
        $doc->appendChild($envelope);

        $wsse = new WSSESoap($doc, false);
        // Add Timestamp and UsernameToken (PasswordDigest=true)
        $wsse->addTimestamp(60);
        $wsse->addUserToken($this->username, $this->authKey, true);

        // Extract the generated wsse:Security node as XML
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('wsse', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');

        $securityNodes = $xpath->query('//wsse:Security');
        Assert::isInstanceOf($securityNodes, \DOMNodeList::class);
        $securityNode = $securityNodes->item(0);
        Assert::isInstanceOf($securityNode, \DOMNode::class);

        $xmlSecurity = $doc->saveXML($securityNode);

        // Add the WebServiceClientId header (outside of wsse:Security)
        $clientId = $environment->getWebServiceClientId();
        $xmlClientId = '<v4:WebServiceClientId xmlns:v4="http://ec.europa.eu/sanco/tracesnt/base/v4">'
            .htmlspecialchars($clientId, ENT_XML1).'</v4:WebServiceClientId>';

        $fullXmlHeader = $xmlSecurity.$xmlClientId;

        return new \SoapHeader(
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new \SoapVar($fullXmlHeader, XSD_ANYXML),
            true
        );
    }

    abstract protected function getMode(): Mode;
}
