<?php

declare(strict_types=1);

namespace src\Services;

use src\Dto\GetDdsInfoByInternalReferenceNumberRequest;
use src\Dto\GetDdsInfoByInternalReferenceNumberResponse;
use src\Dto\GetDdsInfoRequest;
use src\Dto\GetDdsInfoResponse;
use src\Dto\GetReferencedDdsResponse;
use src\Dto\GetReferenceDdsRequest;
use src\Dto\GetStatementByIdentifiersRequest;
use src\Dto\GetStatementByIdentifiersResponse;
use src\Enum\ModeEnum;

class EudrRetrievalClient extends BaseSoapService
{
    public function getDdsInfo(GetDdsInfoRequest $request): GetDdsInfoResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('getDdsInfo', [$request]);

        return GetDdsInfoResponse::fromSoap($raw);
    }

    public function getDdsInfoByInternalReferenceNumber(GetDdsInfoByInternalReferenceNumberRequest $request): GetDdsInfoByInternalReferenceNumberResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('getDdsInfoByInternalReferenceNumber', [$request->identifier]);

        return GetDdsInfoByInternalReferenceNumberResponse::fromSoap($raw);
    }

    public function getStatementByIdentifiers(GetStatementByIdentifiersRequest $request): GetStatementByIdentifiersResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('getStatementByIdentifiers', [$request]);

        return GetStatementByIdentifiersResponse::fromSoap($raw);
    }

    public function getReferencedDds(GetReferenceDdsRequest $request): GetReferencedDdsResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('getReferencedDds', [$request]);

        return GetReferencedDdsResponse::fromSoap($raw);
    }

    protected function getMode(): ModeEnum
    {
        return ModeEnum::RETRIEVAL;
    }
}
