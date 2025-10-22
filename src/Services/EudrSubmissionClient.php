<?php

declare(strict_types=1);

namespace src\Services;

use src\Enum\Mode;
use src\Request\AmendDdsRequest;
use src\Request\RetractDdsRequest;
use src\Request\SubmitDdsRequest;

class EudrSubmissionClient extends BaseSoapService
{
    public function submitDds(SubmitDdsRequest $request): mixed
    {
        return $this->buildSoapClient()->__soapCall('submitDds', [$request]);
    }

    public function amendDds(AmendDdsRequest $request): mixed
    {
        return $this->buildSoapClient()->__soapCall('amendDds', [$request]);
    }

    public function retractDds(RetractDdsRequest $request): mixed
    {
        return $this->buildSoapClient()->__soapCall('retractDds', [$request]);
    }

    public function getMode(): Mode
    {
        return Mode::SUBMISSION;
    }
}
