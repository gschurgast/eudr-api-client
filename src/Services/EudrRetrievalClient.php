<?php

declare(strict_types=1);

namespace src\Services;

use src\Enum\Environment;
use src\Enum\Mode;
use src\Request\GetDdsInfoByInternalReferenceNumberRequest;
use src\Request\GetDdsInfoRequest;
use src\Request\GetReferenceDdsRequest;
use src\Request\GetStatementByIdentifiersRequest;

class EudrRetrievalClient extends BaseSoapService
{
    public function getDdsInfo(Environment $environment, GetDdsInfoRequest $request): mixed
    {
        return $this->buildSoapClient($environment)->__soapCall('getDdsInfo', [$request]);
    }

    public function getDdsInfoByInternalReferenceNumber(Environment $environment, GetDdsInfoByInternalReferenceNumberRequest $request): mixed
    {
        return $this->buildSoapClient($environment)->__soapCall('getDdsInfoByInternalReferenceNumber', [$request->identifier]);
    }

    public function getStatementByIdentifiers(Environment $environment, GetStatementByIdentifiersRequest $request): mixed
    {
        return $this->buildSoapClient($environment)->__soapCall('getStatementByIdentifiers', [$request]);
    }

    public function getReferencedDds(Environment $environment, GetReferenceDdsRequest $request): mixed
    {
        return $this->buildSoapClient($environment)->__soapCall('getReferencedDds', [$request]);
    }

    protected function getMode(): Mode
    {
        return Mode::RETRIEVAL;
    }
}
