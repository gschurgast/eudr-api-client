<?php

declare(strict_types=1);

namespace src\Services;

use src\Enum\Environment;
use src\Enum\Mode;
use src\Request\AmendDdsRequest;
use src\Request\RetractDdsRequest;
use src\Request\SubmitDdsRequest;

class EudrSubmissionClient extends BaseSoapService
{
    public function submitDds(Environment $environment, SubmitDdsRequest $request): mixed
    {
        return $this->buildSoapClient($environment)->__soapCall('submitDds', [$request]);
    }

    public function amendDds(Environment $environment, AmendDdsRequest $request): mixed
    {
        return $this->buildSoapClient($environment)->__soapCall('amendDds', [$request]);
    }

    public function retractDds(Environment $environment, RetractDdsRequest $request): mixed
    {
        return $this->buildSoapClient($environment)->__soapCall('retractDds', [$request]);
    }

    public function getMode(): Mode
    {
        return Mode::SUBMISSION;
    }
}
