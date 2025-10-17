<?php

declare(strict_types=1);

namespace src\Services;

use SoapClient;
use SoapHeader;
use SoapVar;
use src\Enum\Environment;
use src\Enum\Mode;

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
        $nonceBytes = random_bytes(16);
        $nonceBase64 = base64_encode($nonceBytes);

        $dt = new \DateTime('now', new \DateTimeZone('UTC'));
        $created = $dt->format('Y-m-d\TH:i:s\Z');

        $digestRaw = sha1($nonceBytes . $created . $this->authKey, true);
        $passwordDigest = base64_encode($digestRaw);

        $maxSeconds = 60;
        $expires = (clone $dt)->add(new \DateInterval('PT' . $maxSeconds . 'S'))
            ->format('Y-m-d\TH:i:s\Z');

        // IDs uniques
        $timestampId = 'TS-' . bin2hex(random_bytes(8));
        $usernameTokenId = 'UsernameToken-' . bin2hex(random_bytes(8));

        // Construire le XML du wsse:Security sans WebServiceClientId
        $xmlSecurity = '
<wsse:Security
    xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"
    xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
  <wsu:Timestamp wsu:Id="' . $timestampId . '">
    <wsu:Created>' . $created . '</wsu:Created>
    <wsu:Expires>' . $expires . '</wsu:Expires>
  </wsu:Timestamp>
  <wsse:UsernameToken wsu:Id="' . $usernameTokenId . '">
    <wsse:Username>' . htmlspecialchars($this->username, ENT_XML1) . '</wsse:Username>
    <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">'
            . $passwordDigest . '</wsse:Password>
    <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">'
            . $nonceBase64 . '</wsse:Nonce>
    <wsu:Created>' . $created . '</wsu:Created>
  </wsse:UsernameToken>
</wsse:Security>';

        // Puis le WebServiceClientId hors du bloc Security
        $clientId = $environment->getWebServiceClientId();
        $xmlClientId = '<v4:WebServiceClientId xmlns:v4="http://ec.europa.eu/sanco/tracesnt/base/v4">'
            . htmlspecialchars($clientId, ENT_XML1) . '</v4:WebServiceClientId>';

        // Combiner les deux morceaux
        $fullXmlHeader = $xmlSecurity . $xmlClientId;

        $soapVar = new SoapVar($fullXmlHeader, XSD_ANYXML);
        $header = new SoapHeader(
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            $soapVar,
            true
        );

        return $header;
    }


    /** Utility to return a functions list (no username/password). */
    public function listFunctions(Environment $environment): array
    {
        $client = $this->getSoapClient($environment, false);
        return $client->__getFunctions();
    }

    abstract protected function getSoapClient(Environment $environment, bool $authentified): SoapClient;
}
