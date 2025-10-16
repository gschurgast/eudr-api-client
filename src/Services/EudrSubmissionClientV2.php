<?php

declare(strict_types=1);

namespace src\Services;

use SoapClient;
use src\Enum\Environment;
use src\Enum\Mode;

class EudrSubmissionClientV2 extends BaseSoapService
{
    private ?SoapClient $client = null;

    protected function getSoapClient(
        Environment $environment = Environment::ACCEPTANCE,
        bool $authentified = true
    ): SoapClient {
        return $this->buildSoapClient($environment, Mode::SUBMISSION, $authentified);
    }

    // Thin wrappers using operation names from v2 WSDL (names may vary)

    public function submitDdsV2(Environment $environment, $statement): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('submitDds', [$statement]);
    }

    public function amendDdsV2(Environment $environment, $statement): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('amendDds', [$statement]);
    }

    public function retractDdsV2(Environment $environment, $statementIdentifier): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('retractDds', [$statementIdentifier]);
    }
}
