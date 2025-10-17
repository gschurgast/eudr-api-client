<?php

declare(strict_types=1);

namespace src\Services;

use SoapClient;
use src\Enum\Environment;
use src\Enum\Mode;

class EudrSubmissionClient extends BaseSoapService
{
    private ?SoapClient $client = null;

    protected function getSoapClient(
        Environment $environment = Environment::ACCEPTANCE,
        bool $authentified = true
    ): SoapClient {
        return $this->buildSoapClient($environment, Mode::SUBMISSION, $authentified);
    }
    public function submitDds(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('submitDds', [$request]);
    }

    public function amendDds(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('amendDds', [$request]);
    }

    public function retractDds(Environment $environment, array $request): mixed
    {
        return $this->getSoapClient($environment)->__soapCall('retractDds', [$request]);
    }
}
