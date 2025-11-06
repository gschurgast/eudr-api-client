<?php

declare(strict_types=1);

namespace src\Services;

use src\Dto\AmendDdsRequest;
use src\Dto\AmendDdsResponse;
use src\Dto\RetractDdsRequest;
use src\Dto\RetractDdsResponse;
use src\Dto\SubmitDdsRequest;
use src\Dto\SubmitDdsResponse;
use src\Enum\ModeEnum;

class EudrSubmissionClient extends BaseSoapService
{
    public function submitDds(SubmitDdsRequest $request): SubmitDdsResponse
    {
        $raw = $this->sendRequest('submitDds', [$request->toArray()]);

        return SubmitDdsResponse::fromSoap($raw);
    }

    public function amendDds(AmendDdsRequest $request): AmendDdsResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('amendDds', [$request->toArray()]);

        return AmendDdsResponse::fromSoap($raw);
    }

    public function retractDds(RetractDdsRequest $request): RetractDdsResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('retractDds', [$request]);

        return RetractDdsResponse::fromSoap($raw);
    }

    public function getMode(): ModeEnum
    {
        return ModeEnum::SUBMISSION;
    }
}
