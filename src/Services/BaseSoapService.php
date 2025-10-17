<?php

declare(strict_types=1);

namespace src\Services;

use DOMDocument;
use DOMXPath;
use SoapClient;
use SoapHeader;
use SoapVar;
use src\Enum\Environment;
use src\Enum\Mode;
use RobRichards\WsePhp\WSSESoap;

abstract class BaseSoapService
{
    private string $username = 'n00hfgop';
    private string $authKey = 'xlOPMeepcGgBnKkDWBbygrPeBi2ajSHpikqzJCsW';

    public function buildSoapClient(
        Environment $environment = Environment::ACCEPTANCE,
        Mode $mode = Mode::ECHO,
        bool $authentified = true
    ): SoapClient {
        if ($mode === Mode::ECHO && $environment !== Environment::ACCEPTANCE) {
            throw new \LogicException('This service is not allowed on PRODUCTION environment.');
        }

        $wsdl = $environment->getUrl($mode->geturl());

        $defaults = [
            'trace'      => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
        ];

        if (!$environment->getSslVerify()) {
            $ctx                        = stream_context_create([
                'ssl'  => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
                'http' => [
                    'user_agent' => 'EUDR-PHP-SoapClient/1.0 (https://github.com/gschurgast/eudr-api-client)',
                ],
            ]);
            $defaults['stream_context'] = $ctx;
        }

        $client = new SoapClient($wsdl, $defaults);

        if ($authentified === true) {
            $wsHeader = $this->createWsSecurityHeader($environment);

            $client->__setSoapHeaders($wsHeader);
        }
        return $client;
    }

    /**
     * Crée un header WS-Security conforme aux exigences (nonce, created, digest, expires, WebServiceClientId)
     */
    private function createWsSecurityHeader(Environment $environment): SoapHeader
    {
        // Build a minimal SOAP envelope header via WSE-PHP to generate WS-Security elements
        $doc      = new DOMDocument('1.0', 'UTF-8');
        $envelope = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Envelope');
        $header   = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Header');
        $body     = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soapenv:Body');
        $envelope->appendChild($header);
        $envelope->appendChild($body);
        $doc->appendChild($envelope);

        $wsse = new WSSESoap($doc, false);
        // Add Timestamp and UsernameToken (PasswordDigest=true)
        $wsse->addTimestamp(60);
        $wsse->addUserToken($this->username, $this->authKey, true);

        // Extract the generated wsse:Security node as XML
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('wsse', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd');
        $securityNode = $xpath->query('//wsse:Security')->item(0);
        $xmlSecurity  = $securityNode ? $doc->saveXML($securityNode) : '';

        // Add the WebServiceClientId header (outside of wsse:Security)
        $clientId    = $environment->getWebServiceClientId();
        $xmlClientId = '<v4:WebServiceClientId xmlns:v4="http://ec.europa.eu/sanco/tracesnt/base/v4">'
            . htmlspecialchars($clientId, ENT_XML1) . '</v4:WebServiceClientId>';

        $fullXmlHeader = $xmlSecurity . $xmlClientId;

        return new SoapHeader(
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new SoapVar($fullXmlHeader, XSD_ANYXML),
            true
        );
    }


    /** Utility to return a functions list (no username/password). */
    public function listFunctions(Environment $environment): array
    {
        $client = $this->getSoapClient($environment, false);
        return $client->__getFunctions();
    }

    abstract protected function getSoapClient(Environment $environment, bool $authentified): SoapClient;
}
