<?php

declare(strict_types=1);

namespace src\Services;

use SoapClient;
use src\Enum\Environment;
use src\Enum\Mode;

class EudrRetrievalClientV2 extends BaseSoapService
{
    protected function getSoapClient(
        Environment $environment = Environment::ACCEPTANCE,
        bool $authentified = true
    ): SoapClient {
        return $this->buildSoapClient($environment, Mode::RETRIEVAL, $authentified);
    }

    // Thin wrappers using operation names from v2 WSDL (names may vary)

    public function retrieveDdsByUuid(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('retrieveDdsByUuid', [$request]);
    }

    public function retrieveDdsByInternalReferenceNumber(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('retrieveDdsByInternalReferenceNumber', [$request]);
    }

    public function retrieveReferenceDds(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('retrieveReferenceDds', [$request]);
    }
}
