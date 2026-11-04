<?php

declare(strict_types=1);

namespace src\Services;

use src\Dto\V4\AmendDdsRequest;
use src\Dto\V4\DdsModificationResponse;
use src\Dto\V4\DdsOverviewResponse;
use src\Dto\V4\GetDdsByInternalReferenceRequest;
use src\Dto\V4\GetDdsRequest;
use src\Dto\V4\SubmitDdsRequest;
use src\Dto\V4\SubmitDdsResponse;
use src\Dto\V4\WithdrawDdsRequest;
use src\Enum\ModeEnum;

/**
 * EUDRDueDiligenceStatementServiceV4. getDdsByIdentifiers is left out: the V4 schema states it always returns NotFoundException.
 */
class EudrDueDiligenceStatementClient extends BaseSoapService
{
    private ?string $operatorAccessIdentifier = null;

    /**
     * Required when the API user is linked to more than one operator: selects the operator the call acts for.
     */
    public function setOperatorAccessIdentifier(?string $operatorAccessIdentifier): static
    {
        $this->operatorAccessIdentifier = $operatorAccessIdentifier;

        return $this;
    }

    public function submitDds(SubmitDdsRequest $request): SubmitDdsResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('submitDds', [$request->toArray()]);

        return SubmitDdsResponse::fromSoap($raw);
    }

    public function amendDds(AmendDdsRequest $request): DdsModificationResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('amendDds', [$request->toArray()]);

        return DdsModificationResponse::fromSoap($raw);
    }

    public function withdrawDds(WithdrawDdsRequest $request): DdsModificationResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('withdrawDds', [$request->toArray()]);

        return DdsModificationResponse::fromSoap($raw);
    }

    public function getDds(GetDdsRequest $request): DdsOverviewResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('getDds', [$request->toArray()]);

        return DdsOverviewResponse::fromSoap($raw);
    }

    public function getDdsByInternalReference(GetDdsByInternalReferenceRequest $request): DdsOverviewResponse
    {
        $raw = $this->buildSoapClient()->__soapCall('getDdsByInternalReference', [$request->toArray()]);

        return DdsOverviewResponse::fromSoap($raw);
    }

    protected function additionalHeaderXml(): string
    {
        if ($this->operatorAccessIdentifier === null) {
            return '';
        }

        // The body/v4 schema leaves local elements unqualified.
        return '<body:BodyIdentity xmlns:body="http://ec.europa.eu/tracesnt/body/v4"><OperatorAccessIdentifier>'
            . htmlspecialchars($this->operatorAccessIdentifier, \ENT_XML1) . '</OperatorAccessIdentifier></body:BodyIdentity>';
    }

    protected function getMode(): ModeEnum
    {
        return ModeEnum::DUE_DILIGENCE_STATEMENT_V4;
    }
}
