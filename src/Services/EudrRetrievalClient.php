<?php

declare(strict_types=1);

namespace src\Services;

use SoapClient;
use src\Enum\Environment;
use src\Enum\Mode;

class EudrRetrievalClient extends BaseSoapService
{
    protected function getSoapClient(
        Environment $environment = Environment::ACCEPTANCE,
        bool $authentified = true
    ): SoapClient {
        return $this->buildSoapClient($environment, Mode::RETRIEVAL, $authentified);
    }
    public function getDdsInfo(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('getDdsInfo', [$request]);
    }

    public function getDdsInfoByInternalReferenceNumber(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('getDdsInfoByInternalReferenceNumber', [$request]);
    }

    public function getStatementByIdentifiers(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('getStatementByIdentifiers', [$request]);
    }

    public function getReferencedDds(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('getReferencedDds', [$request]);
    }
}
