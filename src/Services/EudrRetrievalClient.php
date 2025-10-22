<?php

declare(strict_types=1);

namespace src\Services;

use src\Enum\ModeEnum;
use src\Request\GetDdsInfoByInternalReferenceNumberRequest;
use src\Request\GetDdsInfoRequest;
use src\Request\GetReferenceDdsRequest;
use src\Request\GetStatementByIdentifiersRequest;

class EudrRetrievalClient extends BaseSoapService
{
    public function getDdsInfo(GetDdsInfoRequest $request): mixed
    {
        return $this->buildSoapClient()->__soapCall('getDdsInfo', [$request]);
    }

    public function getDdsInfoByInternalReferenceNumber(GetDdsInfoByInternalReferenceNumberRequest $request): mixed
    {
        return $this->buildSoapClient()->__soapCall('getDdsInfoByInternalReferenceNumber', [$request->identifier]);
    }

    public function getStatementByIdentifiers(GetStatementByIdentifiersRequest $request): mixed
    {
        return $this->buildSoapClient()->__soapCall('getStatementByIdentifiers', [$request]);
    }

    public function getReferencedDds(GetReferenceDdsRequest $request): mixed
    {
        return $this->buildSoapClient()->__soapCall('getReferencedDds', [$request]);
    }

    protected function getMode(): ModeEnum
    {
        return ModeEnum::RETRIEVAL;
    }
}
